<?php

namespace App\Services;

use App\Models\Bet;
use App\Models\ExchangeEvent;
use App\Models\User;
use Illuminate\Support\Collection;

class ExchangeBookService
{
    /**
     * Known 2-outcome head-to-head casino market pairs where backing/laying
     * opposite sides in the same round hedges exposure.
     */
    private const CASINO_BINARY_PAIRS = [
        ['PLAYER_A', 'PLAYER_B'],
        ['PLAYER A', 'PLAYER B'],
        ['DRAGON', 'TIGER'],
        ['LOW_CARD', 'HIGH_CARD'],
        ['LOW CARD (A - 6)', 'HIGH CARD (8 - K)'],
        ['HEADS', 'TAILS'],
        ['HEADS (GOLD CROWN)', 'TAILS (LUCKY CLOVER)'],
    ];

    /**
     * Calculate Profit & Liability for a candidate bet payload.
     */
    public function calculateProfitAndLiability(string $category, string $betType, float $odds, float $stake): array
    {
        $category = strtolower(trim($category ?: 'exchange'));
        $betType = strtoupper(trim($betType));
        $stake = round($stake, 2);
        $odds = round($odds, 2);

        if ($category === 'bookmaker') {
            $profit = $betType === 'BACK' ? round($stake * ($odds / 100), 2) : $stake;
            $liability = $betType === 'BACK' ? $stake : round($stake * ($odds / 100), 2);
        } elseif ($category === 'fancy' || in_array($betType, ['YES', 'NO'], true)) {
            $rate = $odds / 100;
            $profit = $betType === 'YES' ? round($stake * $rate, 2) : $stake;
            $liability = $betType === 'YES' ? $stake : round($stake * $rate, 2);
        } else {
            $profit = $betType === 'BACK' ? round($stake * ($odds - 1), 2) : $stake;
            $liability = $betType === 'BACK' ? $stake : round($stake * ($odds - 1), 2);
        }

        return [
            'profit' => round($profit, 2),
            'liability' => round($liability, 2),
        ];
    }

    /**
     * Normalize a runner/selection name for reliable matching.
     */
    public function normalizeRunnerName(?string $name): string
    {
        $norm = strtolower(trim((string) $name));
        return preg_replace('/\s+/', ' ', $norm) ?: '';
    }

    /**
     * Check if a runner name represents "The Draw".
     */
    public function isDrawRunner(?string $name): bool
    {
        $norm = $this->normalizeRunnerName($name);
        return in_array($norm, ['the draw', 'draw', 'tie'], true);
    }

    /**
     * Build a unique market group key for a bet (or candidate bet array).
     */
    public function getMarketKey(Bet|array $bet): string
    {
        $data = $bet instanceof Bet ? $bet->toArray() : $bet;
        $category = strtolower(trim((string) ($data['category'] ?? 'exchange')));
        $betType = strtoupper(trim((string) ($data['bet_type'] ?? 'BACK')));
        $eventId = trim((string) ($data['event_id'] ?? '0'));
        $marketId = trim((string) ($data['market_id'] ?? ''));
        $marketName = $this->normalizeRunnerName((string) ($data['market_name'] ?? 'match odds'));
        $selectionName = $this->normalizeRunnerName((string) ($data['selection_name'] ?? ''));
        $runLine = isset($data['run_line']) && $data['run_line'] !== null && $data['run_line'] !== ''
            ? number_format((float) $data['run_line'], 2, '.', '')
            : '';

        if ($category === 'fancy' || in_array($betType, ['YES', 'NO'], true)) {
            return "fancy|{$eventId}|{$marketName}|{$selectionName}|{$runLine}";
        }

        if ($category === 'casino') {
            $selUpper = strtoupper(trim((string) ($data['selection_name'] ?? '')));
            $selIdUpper = strtoupper(trim((string) ($data['selection_id'] ?? '')));
            foreach (self::CASINO_BINARY_PAIRS as $idx => $pair) {
                if (in_array($selUpper, $pair, true) || in_array($selIdUpper, $pair, true)) {
                    return "casino_h2h|{$eventId}|{$marketId}|{$idx}";
                }
            }
            $uniqueId = $data['id'] ?? spl_object_id((object) $data);
            return "casino_single|{$eventId}|{$marketId}|{$selUpper}|{$uniqueId}";
        }

        if ($category === 'lottery') {
            $uniqueId = $data['id'] ?? spl_object_id((object) $data);
            return "lottery|{$eventId}|{$marketId}|{$selectionName}|{$uniqueId}";
        }

        return "market|{$eventId}|{$marketName}";
    }

    /**
     * Compute the full portfolio summary across all open MATCHED bets (plus optional candidate bet).
     */
    public function computePortfolio(int $userId, ?array $candidateBet = null, ?Collection $preloadedBets = null): array
    {
        $bets = $preloadedBets
            ? $preloadedBets->map(fn ($b) => $b instanceof Bet ? $b->toArray() : (array) $b)->values()
            : Bet::where('user_id', $userId)
                ->where('status', 'MATCHED')
                ->orderBy('id')
                ->get()
                ->map(fn (Bet $b) => $b->toArray())
                ->values();

        if ($candidateBet !== null) {
            $bets = $bets->concat([$candidateBet])->values();
        }

        if ($bets->isEmpty()) {
            return [
                'total_exposure' => 0.0,
                'total_locked_profit' => 0.0,
                'total_gross_stake' => 0.0,
                'markets' => [],
            ];
        }

        $eventIds = $bets->pluck('event_id')->filter()->unique()->values()->all();
        $eventsById = ExchangeEvent::whereIn('event_id', $eventIds)
            ->get()
            ->keyBy('event_id');

        $grouped = $bets->groupBy(fn (array $b) => $this->getMarketKey($b));

        $totalExposure = 0.0;
        $totalLockedProfit = 0.0;
        $totalGrossStake = 0.0;
        $markets = [];

        foreach ($grouped as $key => $marketBets) {
            $book = $this->evaluateMarketGroup($key, $marketBets->all(), $eventsById);
            $totalExposure += $book['exposure'];
            $totalLockedProfit += $book['locked_profit'];
            $totalGrossStake += $book['gross_stake'];
            $markets[$key] = $book;
        }

        return [
            'total_exposure' => round($totalExposure, 2),
            'total_locked_profit' => round($totalLockedProfit, 2),
            'total_gross_stake' => round($totalGrossStake, 2),
            'markets' => $markets,
        ];
    }

    /**
     * Evaluate a single market group's runner P/L book, net exposure, locked profit, and cashout value.
     */
    public function evaluateMarketGroup(string $marketKey, array $marketBets, Collection $eventsById): array
    {
        $first = $marketBets[0];
        $eventId = (string) ($first['event_id'] ?? '');
        $marketName = (string) ($first['market_name'] ?? 'Match Odds');
        $category = strtolower(trim((string) ($first['category'] ?? 'exchange')));
        $grossStake = 0.0;
        $grossLiability = 0.0;

        foreach ($marketBets as $b) {
            $grossStake += (float) ($b['stake'] ?? 0);
            $grossLiability += (float) ($b['liability'] ?? 0);
        }

        if (str_starts_with($marketKey, 'fancy|')) {
            $yesPl = 0.0;
            $noPl = 0.0;
            foreach ($marketBets as $b) {
                $bt = strtoupper(trim((string) ($b['bet_type'] ?? 'YES')));
                $prof = (float) ($b['potential_profit'] ?? 0);
                $liab = (float) ($b['liability'] ?? 0);
                if ($bt === 'YES' || $bt === 'BACK') {
                    $yesPl += $prof;
                    $noPl -= $liab;
                } else {
                    $yesPl -= $liab;
                    $noPl += $prof;
                }
            }
            $runnerPl = [
                'YES' => round($yesPl, 2),
                'NO' => round($noPl, 2),
            ];
            $minPl = min($runnerPl);
            $maxPl = max($runnerPl);
            $exposure = round(max(0.0, -$minPl), 2);
            $lockedProfit = round(max(0.0, $minPl), 2);

            return [
                'market_key' => $marketKey,
                'event_id' => $eventId,
                'market_name' => $marketName,
                'category' => $category,
                'runners' => $runnerPl,
                'runner_odds' => [],
                'min_pl' => round($minPl, 2),
                'max_pl' => round($maxPl, 2),
                'exposure' => $exposure,
                'locked_profit' => $lockedProfit,
                'gross_stake' => round($grossStake, 2),
                'gross_liability' => round($grossLiability, 2),
                'stake_returned' => round(max(0.0, $grossLiability - $exposure), 2),
                'cashout_value' => $this->calculateFairCashout($runnerPl, [], $grossStake),
                'bet_count' => count($marketBets),
            ];
        }

        if (str_starts_with($marketKey, 'casino_single|') || str_starts_with($marketKey, 'lottery|')) {
            $b = $marketBets[0];
            $selName = (string) ($b['selection_name'] ?? 'Selection');
            $prof = round((float) ($b['potential_profit'] ?? 0), 2);
            $liab = round((float) ($b['liability'] ?? 0), 2);
            $runnerPl = [
                $selName => $prof,
                '__OTHER__' => -$liab,
            ];

            return [
                'market_key' => $marketKey,
                'event_id' => $eventId,
                'market_name' => $marketName,
                'category' => $category,
                'runners' => $runnerPl,
                'runner_odds' => [],
                'min_pl' => -$liab,
                'max_pl' => $prof,
                'exposure' => $liab,
                'locked_profit' => 0.0,
                'gross_stake' => round($grossStake, 2),
                'gross_liability' => $liab,
                'stake_returned' => 0.0,
                'cashout_value' => round($grossStake * 0.15, 2),
                'bet_count' => count($marketBets),
            ];
        }

        // Standard Exchange / Bookmaker / Sportsbook / Binary Casino Market
        $event = $eventsById->get($eventId);
        $outcomes = $this->resolveMarketOutcomes($marketKey, $marketBets, $event, $marketName, $category);

        $runnerPl = [];
        $runnerOdds = [];
        foreach ($outcomes as $outcome) {
            $oName = $outcome['name'];
            $runnerOdds[$oName] = $outcome['back_odds'] ?? null;
            $pl = 0.0;

            foreach ($marketBets as $b) {
                $matches = $this->betMatchesOutcome($b, $outcome);
                $bt = strtoupper(trim((string) ($b['bet_type'] ?? 'BACK')));
                $prof = (float) ($b['potential_profit'] ?? 0);
                $liab = (float) ($b['liability'] ?? 0);

                if ($matches) {
                    $pl += ($bt === 'BACK' || $bt === 'YES') ? $prof : -$liab;
                } else {
                    $pl += ($bt === 'BACK' || $bt === 'YES') ? -$liab : $prof;
                }
            }

            $runnerPl[$oName] = round($pl, 2);
        }

        $minPl = !empty($runnerPl) ? min($runnerPl) : 0.0;
        $maxPl = !empty($runnerPl) ? max($runnerPl) : 0.0;
        $exposure = round(max(0.0, -$minPl), 2);
        $lockedProfit = round(max(0.0, $minPl), 2);

        $visibleRunners = [];
        foreach ($runnerPl as $rName => $val) {
            if ($rName !== '__OTHER__') {
                $visibleRunners[$rName] = $val;
            }
        }

        $wbCashout = $this->calculateWinbookCashout($outcomes, $runnerPl, $category);
        $cashoutVal = $wbCashout !== null
            ? (float) $wbCashout['cashout_value']
            : $this->calculateFairCashout($runnerPl, $runnerOdds, $grossStake);

        return [
            'market_key' => $marketKey,
            'event_id' => $eventId,
            'market_name' => $marketName,
            'category' => $category,
            'runners' => $visibleRunners ?: $runnerPl,
            'all_outcomes' => $runnerPl,
            'runner_odds' => $runnerOdds,
            'min_pl' => round($minPl, 2),
            'max_pl' => round($maxPl, 2),
            'exposure' => $exposure,
            'locked_profit' => $lockedProfit,
            'gross_stake' => round($grossStake, 2),
            'gross_liability' => round($grossLiability, 2),
            'stake_returned' => round(max(0.0, $grossLiability - $exposure), 2),
            'cashout_value' => round($cashoutVal, 2),
            'winbook_cashout' => $wbCashout,
            'bet_count' => count($marketBets),
        ];
    }

    /**
     * Resolve the list of mutually exclusive outcomes for a market.
     */
    private function resolveMarketOutcomes(
        string $marketKey,
        array $marketBets,
        ?ExchangeEvent $event,
        string $marketName,
        string $category
    ): array {
        $outcomes = [];
        $seenNorm = [];

        $addOutcome = function (string $name, ?string $id = null, ?float $backOdds = null, ?float $rawBack = null, ?float $rawLay = null) use (&$outcomes, &$seenNorm) {
            $norm = $this->normalizeRunnerName($name);
            if ($norm === '' || isset($seenNorm[$norm])) {
                return;
            }
            $seenNorm[$norm] = true;
            $outcomes[] = [
                'name' => trim($name),
                'norm' => $norm,
                'id' => ($id !== null && $id !== '' && $id !== '1') ? (string) $id : null,
                'back_odds' => $backOdds,
                'raw_back' => $rawBack,
                'raw_lay' => $rawLay,
            ];
        };

        if (str_starts_with($marketKey, 'casino_h2h|')) {
            $parts = explode('|', $marketKey);
            $pairIdx = (int) ($parts[3] ?? 0);
            $pair = self::CASINO_BINARY_PAIRS[$pairIdx] ?? ['SIDE_A', 'SIDE_B'];
            foreach ($pair as $pName) {
                $addOutcome($pName, $pName, 1.98, 1.98, 2.02);
            }
        } elseif ($event) {
            $normMkt = $this->normalizeRunnerName($marketName);
            $rawRunners = [];

            if ($category === 'bookmaker' || str_contains($normMkt, 'bookmaker')) {
                $rawRunners = is_array($event->bookmaker_json) && !empty($event->bookmaker_json)
                    ? $event->bookmaker_json
                    : (is_array($event->runners_json) ? $event->runners_json : []);
            } elseif ($category === 'sportsbook' && is_array($event->sportsbook_json)) {
                foreach ($event->sportsbook_json as $sbMarket) {
                    $sbTitle = $this->normalizeRunnerName((string) ($sbMarket['title'] ?? $sbMarket['marketName'] ?? ''));
                    if ($sbTitle !== '' && $sbTitle === $normMkt && !empty($sbMarket['runners'])) {
                        $rawRunners = $sbMarket['runners'];
                        break;
                    }
                }
                if (empty($rawRunners) && is_array($event->runners_json)) {
                    $rawRunners = $event->runners_json;
                }
            } else {
                $rawRunners = is_array($event->runners_json) ? $event->runners_json : [];
            }

            foreach ($rawRunners as $r) {
                if (!is_array($r)) {
                    continue;
                }
                $rName = (string) ($r['runnerName'] ?? $r['name'] ?? $r['label'] ?? '');
                $rId = isset($r['selectionId']) ? (string) $r['selectionId'] : null;
                $backOdds = null;
                $rawBack = null;
                $rawLay = null;
                if (isset($r['back'][0]['price'])) {
                    $rawPrice = (float) $r['back'][0]['price'];
                    $rawBack = $rawPrice;
                    $backOdds = ($category === 'bookmaker' || str_contains($normMkt, 'bookmaker'))
                        ? round(1 + ($rawPrice / 100), 4)
                        : $rawPrice;
                } elseif (isset($r['odds'])) {
                    $backOdds = (float) $r['odds'];
                    $rawBack = $backOdds;
                }
                if (isset($r['lay'][0]['price'])) {
                    $rawLay = (float) $r['lay'][0]['price'];
                }
                if ($rName !== '') {
                    $addOutcome($rName, $rId, $backOdds, $rawBack, $rawLay);
                }
            }
        }

        // Also include any runner names explicitly bet on by the user
        $userBetOnDraw = false;
        foreach ($marketBets as $b) {
            $selName = trim((string) ($b['selection_name'] ?? ''));
            $selId = isset($b['selection_id']) ? (string) $b['selection_id'] : null;
            if ($this->isDrawRunner($selName)) {
                $userBetOnDraw = true;
            }
            if ($selName !== '') {
                $addOutcome($selName, $selId, null);
            }
        }

        // In 3-runner events where "The Draw" exists, if the user has not bet on "The Draw"
        // and there are at least 2 primary team runners, exclude "The Draw" so backing both teams
        // hedges their head-to-head book cleanly.
        if (!$userBetOnDraw && count($outcomes) >= 3) {
            $nonDraw = array_values(array_filter($outcomes, fn (array $o) => !$this->isDrawRunner($o['name'])));
            if (count($nonDraw) >= 2) {
                $outcomes = $nonDraw;
            }
        }

        // Ensure at least 2 mutually exclusive outcomes exist so single-runner bets have an opposing outcome
        if (count($outcomes) < 2) {
            $addOutcome('__OTHER__', '__OTHER__', null);
        }

        return $outcomes;
    }

    /**
     * Determine whether a bet matches a specific market outcome.
     */
    private function betMatchesOutcome(array $bet, array $outcome): bool
    {
        if ($outcome['name'] === '__OTHER__') {
            return false;
        }

        $betNameNorm = $this->normalizeRunnerName((string) ($bet['selection_name'] ?? ''));
        if ($betNameNorm !== '' && $betNameNorm === $outcome['norm']) {
            return true;
        }

        $betSelId = isset($bet['selection_id']) ? trim((string) $bet['selection_id']) : '';
        if ($betSelId !== '' && $betSelId !== '1' && $outcome['id'] !== null && $betSelId === $outcome['id']) {
            return true;
        }

        return false;
    }

    /**
     * Exact 1:1 Winbook24 Cash Out formula (matching `cashOutOnInterval` & `cashOutOnIntervalBookmaker2` in chunk933.js).
     * Given 2 runners, finds the favorite runner `c = minBy(runners, 'backOdds')`, computes the exact
     * offsetting BACK/LAY stake `m` and price `_`, and computes `cashoutValue` `M`.
     */
    public function calculateWinbookCashout(array $outcomes, array $runnerPl, string $category): ?array
    {
        $realOutcomes = array_values(array_filter($outcomes, fn (array $o) => $o['name'] !== '__OTHER__'));
        if (count($realOutcomes) !== 2) {
            return null;
        }

        $r0 = $realOutcomes[0];
        $r1 = $realOutcomes[1];
        $b0 = (float) ($r0['raw_back'] ?? 0);
        $l0 = (float) ($r0['raw_lay'] ?? 0);
        $b1 = (float) ($r1['raw_back'] ?? 0);
        $l1 = (float) ($r1['raw_lay'] ?? 0);

        if ($b0 <= 0 || $b1 <= 0) {
            return null;
        }

        // Favorite runner c = minBy(runners, 'backOdds')
        $fav = ($b0 <= $b1) ? $r0 : $r1;
        $other = ($b0 <= $b1) ? $r1 : $r0;

        $cBack = (float) ($fav['raw_back'] ?? 0);
        $cLay = (float) ($fav['raw_lay'] ?? 0);
        if ($cBack <= 0 || $cLay <= 0) {
            return null;
        }

        $f = (float) ($runnerPl[$fav['name']] ?? 0.0);
        $sV = (float) ($runnerPl[$other['name']] ?? 0.0);

        $negCount = ($f < 0 ? 1 : 0) + ($sV < 0 ? 1 : 0);
        $isBookmaker = ($category === 'bookmaker');

        $stake = 0.0;
        $price = 0.0;
        $side = 'LAY';

        if ($negCount === 2 || $negCount === 0) {
            $O = abs($f - $sV);
            if ($f > $sV) {
                $divisor = $isBookmaker ? (($cLay / 100.0) + 1.0) : $cLay;
                $stake = round($O / $divisor, 2);
                $price = $cLay;
                $side = 'LAY';
            } else {
                $divisor = $isBookmaker ? (($cBack / 100.0) + 1.0) : $cBack;
                $stake = round($O / $divisor, 2);
                $price = $cBack;
                $side = 'BACK';
            }
        } else {
            $p = abs($f) + abs($sV);
            if ($f > 0) {
                $divisor = $isBookmaker ? (($cLay / 100.0) + 1.0) : $cLay;
                $stake = round($p / $divisor, 2);
                $price = $cLay;
                $side = 'LAY';
            } else {
                $divisor = $isBookmaker ? (($cBack / 100.0) + 1.0) : $cBack;
                $stake = round($p / $divisor, 2);
                $price = $cBack;
                $side = 'BACK';
            }
        }

        if ($isBookmaker) {
            $M = $side === 'BACK'
                ? round($f + ($price * ($stake / 100.0)), 2)
                : round($f - ($price * ($stake / 100.0)), 2);
        } else {
            $delta = ceil((((100.0 * $price) - 100.0) / 100.0) * $stake);
            $M = $side === 'BACK'
                ? round($f + $delta, 2)
                : round($f - $delta, 2);
        }

        return [
            'cashout_value' => $M,
            'selection_id' => $fav['id'] ?? '1',
            'selection_name' => $fav['name'],
            'bet_type' => $side,
            'odds' => $price,
            'stake' => $stake,
            'category' => $isBookmaker ? 'bookmaker' : 'exchange',
        ];
    }

    /**
     * Calculate fair Cash Out / Trade Out value for a market book.
     * - If the user has hedged / greened up (minPL > 0), Cash Out is at least minPL
     *   (or the live implied-probability-weighted value across runners).
     * - If the user has a single-side bet or unhedged position, Cash Out returns the full
     *   staked amount back to the wallet plus a fair positive trade-out profit when odds/position allow.
     */
    private function calculateFairCashout(array $runnerPl, array $runnerOdds, float $grossStake): float
    {
        if (empty($runnerPl)) {
            return 0.0;
        }

        $minPl = min($runnerPl);
        $maxPl = max($runnerPl);

        // Try odds-weighted expectation if live decimal back odds are available for all real runners
        $realRunners = array_filter($runnerPl, fn ($v, $k) => $k !== '__OTHER__', ARRAY_FILTER_USE_BOTH);
        $probSum = 0.0;
        $weightedPl = 0.0;
        $allHaveOdds = count($realRunners) >= 2;

        if ($allHaveOdds) {
            foreach ($realRunners as $rName => $pl) {
                $odds = $runnerOdds[$rName] ?? null;
                if ($odds === null || $odds <= 1.0) {
                    $allHaveOdds = false;
                    break;
                }
                $prob = 1.0 / $odds;
                $probSum += $prob;
                $weightedPl += $prob * $pl;
            }
        }

        if ($minPl > 0) {
            // Green book! Guarantee at least minPl, or the fair odds-weighted value if higher
            if ($allHaveOdds && $probSum > 0) {
                $fair = round($weightedPl / $probSum, 2);
                return round(max($minPl, $fair), 2);
            }
            return round($minPl, 2);
        }

        // Partially hedged book (more than 1 runner has different P/L and maxPl > 0)
        if ($allHaveOdds && $probSum > 0) {
            $fair = round($weightedPl / $probSum, 2);
            if ($fair > $minPl) {
                return $fair;
            }
        }

        return round(($minPl + $maxPl) / 2, 2);
    }

    /**
     * Preview the exact wallet & market book impact of placing a candidate bet BEFORE saving it.
     */
    public function previewBetImpact(User $user, array $candidateBet): array
    {
        $openBets = Bet::where('user_id', $user->id)
            ->where('status', 'MATCHED')
            ->orderBy('id')
            ->get();

        $before = $this->computePortfolio($user->id, null, $openBets);
        $after = $this->computePortfolio($user->id, $candidateBet, $openBets);

        $balance = round((float) $user->balance, 2);

        $exposureBefore = $before['total_exposure'];
        $lockedProfitBefore = $before['total_locked_profit'];
        $availableBefore = round($balance - $exposureBefore + $lockedProfitBefore, 2);

        $exposureAfter = $after['total_exposure'];
        $lockedProfitAfter = $after['total_locked_profit'];
        $availableAfter = round($balance - $exposureAfter + $lockedProfitAfter, 2);

        $marketKey = $this->getMarketKey($candidateBet);
        $marketBefore = $before['markets'][$marketKey] ?? [
            'exposure' => 0.0,
            'locked_profit' => 0.0,
            'min_pl' => 0.0,
            'max_pl' => 0.0,
            'runners' => [],
        ];
        $marketAfter = $after['markets'][$marketKey] ?? [
            'exposure' => 0.0,
            'locked_profit' => 0.0,
            'min_pl' => 0.0,
            'max_pl' => 0.0,
            'runners' => [],
        ];

        $marketExposureBefore = (float) ($marketBefore['exposure'] ?? 0.0);
        $marketExposureAfter = (float) ($marketAfter['exposure'] ?? 0.0);
        $stakeReturnedToWallet = round(max(0.0, $marketExposureBefore - $marketExposureAfter), 2);

        // Net additional requirement from wallet:
        // Positive means this bet requires additional wallet coverage;
        // Negative or zero means the bet is funded by existing runner profit and/or releases exposure back to wallet!
        $netWalletRequirement = round(
            ($exposureAfter - $lockedProfitAfter) - ($exposureBefore - $lockedProfitBefore),
            2
        );

        // A bet is allowed if:
        // 1. Resulting available wallet is >= 0 (within 0.01 rounding tolerance), OR
        // 2. The bet does not increase net exposure (it is a hedge funded by runner profit!)
        $allowed = ($availableAfter >= -0.01) || ($netWalletRequirement <= 0.01);

        $usedProfitAmount = 0.0;
        $candidateLiability = round((float) ($candidateBet['liability'] ?? 0.0), 2);
        if ($netWalletRequirement < $candidateLiability) {
            $usedProfitAmount = round(max(0.0, $candidateLiability - max(0.0, $netWalletRequirement)), 2);
        }

        return [
            'allowed' => $allowed,
            'balance' => $balance,
            'exposure_before' => $exposureBefore,
            'exposure_after' => $exposureAfter,
            'locked_profit_before' => $lockedProfitBefore,
            'locked_profit_after' => $lockedProfitAfter,
            'available_before' => $availableBefore,
            'available_after' => $availableAfter,
            'net_wallet_requirement' => max(0.0, $netWalletRequirement),
            'stake_returned_to_wallet' => $stakeReturnedToWallet,
            'used_profit_amount' => $usedProfitAmount,
            'market_key' => $marketKey,
            'market_before' => $marketBefore,
            'market_after' => $marketAfter,
        ];
    }

    /**
     * Synchronize a user's stored `exposure` column with their true market-book exposure,
     * and return the complete wallet & market book state.
     */
    public function syncUserWallet(User $user): array
    {
        $portfolio = $this->computePortfolio($user->id);
        $trueExposure = $portfolio['total_exposure'];
        $lockedProfit = $portfolio['total_locked_profit'];

        if (abs((float) $user->exposure - $trueExposure) >= 0.01) {
            $user->forceFill(['exposure' => $trueExposure])->saveQuietly();
        }

        $balance = round((float) $user->balance, 2);
        $available = round($balance - $trueExposure + $lockedProfit, 2);

        return [
            'balance' => $balance,
            'exposure' => $trueExposure,
            'locked_profit' => $lockedProfit,
            'available' => $available,
            'markets' => $portfolio['markets'],
        ];
    }
}
