<?php

namespace App\Http\Controllers;

use App\Models\AccountStatement;
use App\Models\Bet;
use App\Models\CasinoGame;
use App\Models\SiteSetting;
use App\Models\Sport;
use App\Services\CasinoEngineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CasinoController extends Controller
{
    public function __construct(private CasinoEngineService $engine)
    {
    }

    private function sharedData(): array
    {
        return [
            'sports' => Sport::where('status', true)->orderBy('sequence')->get(),
            'openBets' => Auth::check()
                ? Bet::where('user_id', Auth::id())->where('status', 'MATCHED')->latest()->take(20)->get()
                : collect(),
            'supportWhatsapp' => SiteSetting::getValue('support_whatsapp', '+919831391470'),
            'newsMarquee' => SiteSetting::getValue('news_marquee', 'Minimum deposit 500 and minimum withdrawal 1000'),
        ];
    }

    /**
     * /livecasino - Matches screenshot 08_live_casino_page.png (Popular, Universe Live, Universe Original, International, Slot)
     */
    public function liveCasino(Request $request)
    {
        $activeTab = $request->query('tab', 'Popular');
        $games = CasinoGame::where('status', true)->orderBy('sequence')->get();

        $tabs = [
            'Popular' => $games->where('popular', true)->values(),
            'Universe Live' => $games->where('menu_name', 'Universe Live')->values(),
            'Universe Original' => $games->where('menu_name', 'Universe Original')->values(),
            'International' => $games->where('menu_name', 'International')->values(),
            'Slot' => $games->where('menu_name', 'Slot')->values(),
        ];

        return view('casino.live-casino', array_merge($this->sharedData(), [
            'tabs' => $tabs,
            'activeTab' => array_key_exists($activeTab, $tabs) ? $activeTab : 'Popular',
            'allCount' => $games->count(),
        ]));
    }

    /**
     * /newCasino - Matches screenshot 26_newCasino.png (ALL, OUR CASINO, OUR VIRTUAL, EVOLUTION, EZUGI, TVBET, BETGAME, PLATINGAMES)
     */
    public function newCasino(Request $request)
    {
        $activeTab = $request->query('tab', 'ALL');
        $games = CasinoGame::where('status', true)->orderBy('sequence')->get();

        $tabs = [
            'ALL' => $games->where('menu_name', '!=', 'Slot')->take(48)->values(),
            'OUR CASINO' => $games->where('category_tab', 'OUR CASINO')->values(),
            'OUR VIRTUAL' => $games->where('category_tab', 'OUR VIRTUAL')->values(),
            'EVOLUTION' => $games->where('category_tab', 'EVOLUTION')->values(),
            'EZUGI' => $games->where('category_tab', 'EZUGI')->values(),
            'TVBET' => $games->whereIn('category_tab', ['TVBET', 'OUR VIRTUAL'])->values(),
            'BETGAME' => $games->whereIn('category_tab', ['BETGAME', 'OUR CASINO'])->take(24)->values(),
            'PLATINGAMES' => $games->where('category_tab', 'PLATINGAMES')->values(),
        ];

        return view('casino.new-casino', array_merge($this->sharedData(), [
            'tabs' => $tabs,
            'activeTab' => array_key_exists($activeTab, $tabs) ? $activeTab : 'ALL',
        ]));
    }

    /**
     * /vimaan shortcut
     */
    public function vimaan()
    {
        return $this->playGame('88.0022');
    }

    /**
     * /balloon/{eventId?} - Exact 1:1 BALLOON (88.0023) Crash Game Room
     */
    public function balloon(string $eventId = '88.0023')
    {
        return $this->playGame($eventId ?: '88.0023');
    }

    /**
     * /lucky0To9/{eventId?} - Exact 1:1 LUCKY 0 TO 9 (88.0019) Wheel Game Room
     */
    public function lucky0To9(string $eventId = '88.0019')
    {
        return $this->playGame($eventId ?: '88.0019');
    }

    /**
     * /headNtails/{eventId?} - Exact 1:1 HEADS & TAILS (88.0021) Coin Flip Game Room
     */
    public function headNtails(string $eventId = '88.0021')
    {
        return $this->playGame($eventId ?: '88.0021');
    }

    /**
     * /casino3Patti/{eventId?} - Exact 1:1 20-20 TEENPATTI (99.0010) & 20-20 DRAGON TIGER (99.0019) Game Room
     */
    public function teenPatti2020(string $eventId = '99.0010')
    {
        return $this->playGame($eventId ?: '99.0010');
    }

    /**
     * /dragontiger/{eventId?} - Exact 1:1 20-20 DRAGON TIGER (99.0019) Game Room
     */
    public function dragonTiger2020(string $eventId = '99.0019')
    {
        return $this->playGame($eventId ?: '99.0019');
    }

    /**
     * /lucky7/{eventId?} - Exact 1:1 LUCKY 7 (99.0030) Game Room
     */
    public function lucky7(string $eventId = '99.0030')
    {
        return $this->playGame($eventId ?: '99.0030');
    }

    /**
     * /casino/play/{eventId} - Interactive Playable Casino Game Room for ANY of the 217+ games!
     */
    public function playGame(string $eventId)
    {
        $game = CasinoGame::where('event_id', $eventId)
            ->orWhere('slug', $eventId)
            ->first();

        if (!$game) {
            $game = CasinoGame::where('event_id', '88.0022')->first() ?? CasinoGame::firstOrFail();
        }

        $tableDef = $this->engine->getTableDefinition($game);
        $relatedGames = CasinoGame::where('status', true)
            ->where('id', '!=', $game->id)
            ->where(function ($q) use ($game) {
                $q->where('game_type', $game->game_type)->orWhere('popular', true);
            })
            ->orderByDesc('popular')
            ->take(12)
            ->get();

        $recentRounds = Auth::check()
            ? Bet::where('user_id', Auth::id())
                ->where('category', 'casino')
                ->where('event_id', $game->event_id)
                ->latest()
                ->take(25)
                ->get()
            : collect();

        if ($game->event_id === '88.0022' || strtolower($game->event_name) === 'vimaan') {
            return view('casino.vimaan', array_merge($this->sharedData(), [
                'game' => $game,
                'tableDef' => $tableDef,
                'recentRounds' => $recentRounds,
            ]));
        }

        if ($game->event_id === '88.0023' || strtolower($game->event_name) === 'balloon') {
            return view('casino.balloon', array_merge($this->sharedData(), [
                'game' => $game,
                'tableDef' => $tableDef,
                'recentRounds' => $recentRounds,
            ]));
        }

        if ($game->event_id === '88.0019' || stripos($game->event_name, 'lucky 0 to 9') !== false) {
            return view('casino.lucky0to9', array_merge($this->sharedData(), [
                'game' => $game,
                'tableDef' => $tableDef,
                'relatedGames' => $relatedGames,
                'recentRounds' => $recentRounds,
            ]));
        }

        if ($game->event_id === '88.0021' || stripos($game->event_name, 'head') !== false) {
            return view('casino.headntails', array_merge($this->sharedData(), [
                'game' => $game,
                'tableDef' => $tableDef,
                'relatedGames' => $relatedGames,
                'recentRounds' => $recentRounds,
            ]));
        }

        if (in_array($game->event_id, ['99.0030', 'oc_lucky7_a', 'oc_fast_lucky7', 'oc_lucky7_b'], true)
            || $game->game_type === 'lucky7') {
            return view('casino.lucky7', array_merge($this->sharedData(), [
                'game' => $game,
                'tableDef' => $tableDef,
                'relatedGames' => $relatedGames,
                'recentRounds' => $recentRounds,
            ]));
        }

        if (in_array($game->event_id, ['99.0018', '99.0020'], true)
            || in_array(strtoupper((string) $game->event_name), ['DRAGON TIGER', '1 DAY DRAGON TIGER'], true)) {
            return view('casino.dragontiger_990018', array_merge($this->sharedData(), [
                'game' => $game,
                'tableDef' => $tableDef,
                'relatedGames' => $relatedGames,
                'recentRounds' => $recentRounds,
            ]));
        }

        if (in_array($game->event_id, ['99.0019', 'oc_fast_dt'], true)
            || $game->game_type === 'dragontiger'
            || stripos((string) $game->event_name, 'dragon tiger') !== false) {
            return view('casino.dragontiger2020', array_merge($this->sharedData(), [
                'game' => $game,
                'tableDef' => $tableDef,
                'relatedGames' => $relatedGames,
                'recentRounds' => $recentRounds,
            ]));
        }

        if (in_array($game->event_id, ['99.0010', '99.0013', '99.0014', '99.0016', 'oc_point_tp', 'oc_dtl_tp'], true)
            || stripos((string) $game->event_name, 'teenpatti') !== false
            || stripos((string) $game->event_name, 'teen patti') !== false) {
            return view('casino.teenpatti2020', array_merge($this->sharedData(), [
                'game' => $game,
                'tableDef' => $tableDef,
                'relatedGames' => $relatedGames,
                'recentRounds' => $recentRounds,
            ]));
        }

        return view('casino.play', array_merge($this->sharedData(), [
            'game' => $game,
            'tableDef' => $tableDef,
            'relatedGames' => $relatedGames,
            'recentRounds' => $recentRounds,
        ]));
    }

    /**
     * POST /api/vimaan/place-bet - Places a real-time VIMAAN bet in SQLite (locks exposure)
     */
    public function vimaanPlaceBet(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'status' => false,
                'message' => 'Please login (or click Login with Demo ID) to play VIMAAN.',
                'redirect' => route('login'),
            ], 401);
        }

        $request->validate([
            'stake' => 'required|numeric|min:10|max:100000',
            'slot' => 'required|integer|min:1|max:4',
            'round_id' => 'nullable|string',
            'auto_cashout' => 'nullable|numeric|min:1.01|max:500',
        ]);

        $user = Auth::user();
        $stake = round((float) $request->input('stake'), 2);
        $slot = (int) $request->input('slot', 1);
        $roundId = $request->input('round_id') ?: ('VM' . now()->format('ymdHis') . random_int(10, 99));

        if ($user->available_balance < $stake) {
            return response()->json([
                'status' => false,
                'message' => sprintf('Insufficient PTI Balance! Available: %.2f PTI', $user->available_balance),
            ], 422);
        }

        $bet = DB::transaction(function () use ($user, $stake, $slot, $roundId, $request) {
            $user->increment('exposure', $stake);

            $isBalloon = $request->input('event_id') === '88.0023' || str_contains($request->path(), 'balloon');
            $evId = $isBalloon ? '88.0023' : ($request->input('event_id') ?: '88.0022');
            $evName = $isBalloon ? 'BALLOON' : ($request->input('event_name') ?: 'VIMAAN');

            return Bet::create([
                'bet_ref' => $roundId . '-S' . $slot,
                'user_id' => $user->id,
                'category' => 'casino',
                'sport_id' => '66102',
                'sport_name' => 'Casino',
                'event_id' => $evId,
                'event_name' => $evName,
                'market_id' => $roundId,
                'market_name' => 'Universe Original',
                'selection_id' => 'CASH_IN_' . $slot,
                'selection_name' => $evName . ' Slot #' . $slot,
                'bet_type' => 'BACK',
                'odds' => (float) ($request->input('auto_cashout') ?: 1.00),
                'stake' => $stake,
                'potential_profit' => 0,
                'liability' => $stake,
                'profit_loss' => 0,
                'status' => 'MATCHED',
                'result_summary' => 'In Flight',
                'game_details_json' => [
                    'slot' => $slot,
                    'round_id' => $roundId,
                    'auto_cashout' => $request->input('auto_cashout'),
                ],
                'ip_address' => $request->ip() ?? '127.0.0.1',
            ]);
        });

        $walletState = app(\App\Services\ExchangeBookService::class)->syncUserWallet($user->fresh());

        return response()->json([
            'status' => true,
            'bet' => [
                'id' => $bet->id,
                'bet_ref' => $bet->bet_ref,
                'slot' => $slot,
                'stake' => $bet->stake,
                'created_at' => $bet->created_at->format('H:i'),
            ],
            'wallet' => [
                'balance' => $walletState['balance'],
                'exposure' => $walletState['exposure'],
                'available' => $walletState['available'],
            ],
        ]);
    }

    /**
     * POST /api/vimaan/cancel-bet - Cancels a queued VIMAAN bet before takeoff
     */
    public function vimaanCancelBet(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'bet_id' => 'required|integer',
        ]);

        $user = Auth::user();
        $bet = Bet::where('id', $request->input('bet_id'))
            ->where('user_id', $user->id)
            ->where('status', 'MATCHED')
            ->first();

        if ($bet) {
            DB::transaction(function () use ($user, $bet) {
                $newExp = max(0, (float) $user->exposure - (float) $bet->liability);
                $user->update(['exposure' => $newExp]);
                $bet->update([
                    'status' => 'CANCELLED',
                    'result_summary' => 'Cancelled before takeoff',
                    'settled_at' => now(),
                ]);
            });
        }

        $walletState = app(\App\Services\ExchangeBookService::class)->syncUserWallet($user->fresh());

        return response()->json([
            'status' => true,
            'wallet' => [
                'balance' => $walletState['balance'],
                'exposure' => $walletState['exposure'],
                'available' => $walletState['available'],
            ],
        ]);
    }

    /**
     * POST /api/vimaan/cashout - Cashes out an active VIMAAN bet at the live multiplier
     */
    public function vimaanCashout(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'bet_id' => 'required|integer',
            'multiplier' => 'required|numeric|min:1.01|max:1000',
        ]);

        $user = Auth::user();
        $bet = Bet::where('id', $request->input('bet_id'))
            ->where('user_id', $user->id)
            ->where('status', 'MATCHED')
            ->first();

        if (!$bet) {
            return response()->json(['status' => false, 'message' => 'Bet already settled or not found.'], 422);
        }

        $mult = round((float) $request->input('multiplier'), 2);
        $payout = round((float) $bet->stake * $mult, 2);
        $netProfit = round($payout - (float) $bet->stake, 2);

        DB::transaction(function () use ($user, $bet, $mult, $payout, $netProfit) {
            $newExp = max(0, (float) $user->exposure - (float) $bet->liability);
            $user->update([
                'exposure' => $newExp,
                'balance' => round((float) $user->balance + $netProfit, 2),
            ]);

            $bet->update([
                'odds' => $mult,
                'potential_profit' => $netProfit,
                'profit_loss' => $netProfit,
                'status' => 'WON',
                'result_summary' => "Cashed out @ {$mult}x (Won {$payout})",
                'game_details_json' => array_merge($bet->game_details_json ?? [], [
                    'cashout_multiplier' => $mult,
                    'cashout_amount' => $payout,
                ]),
                'settled_at' => now(),
            ]);

            AccountStatement::create([
                'user_id' => $user->id,
                'credit' => $netProfit,
                'debit' => 0,
                'balance' => $user->fresh()->balance,
                'remark' => "Casino [VIMAAN] #{$bet->bet_ref}: Cashed out @ {$mult}x (Payout: {$payout})",
                'from_to' => "VIMAAN -> {$user->username}",
                'ref_type' => 'casino',
                'ref_id' => $bet->bet_ref,
            ]);
        });

        $walletState = app(\App\Services\ExchangeBookService::class)->syncUserWallet($user->fresh());

        return response()->json([
            'status' => true,
            'cashout' => [
                'bet_id' => $bet->id,
                'stake' => (float) $bet->stake,
                'multiplier' => $mult,
                'payout' => $payout,
                'net_profit' => $netProfit,
                'time' => now()->format('H:i'),
            ],
            'wallet' => [
                'balance' => $walletState['balance'],
                'exposure' => $walletState['exposure'],
                'available' => $walletState['available'],
            ],
        ]);
    }

    /**
     * POST /api/vimaan/crash - Settles un-cashed VIMAAN bets when the plane flies away
     */
    public function vimaanCrash(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['status' => false], 401);
        }

        $request->validate([
            'bet_ids' => 'required|array',
            'crash_multiplier' => 'required|numeric|min:1.00',
        ]);

        $user = Auth::user();
        $crashMult = round((float) $request->input('crash_multiplier'), 2);
        $betIds = array_map('intval', $request->input('bet_ids', []));

        $bets = Bet::whereIn('id', $betIds)
            ->where('user_id', $user->id)
            ->where('status', 'MATCHED')
            ->get();

        $settled = [];
        if ($bets->isNotEmpty()) {
            DB::transaction(function () use ($user, $bets, $crashMult, &$settled) {
                foreach ($bets as $bet) {
                    $loss = (float) $bet->stake;
                    $newExp = max(0, (float) $user->fresh()->exposure - (float) $bet->liability);
                    $user->update([
                        'exposure' => $newExp,
                        'balance' => round((float) $user->fresh()->balance - $loss, 2),
                    ]);

                    $bet->update([
                        'odds' => $crashMult,
                        'profit_loss' => -$loss,
                        'status' => 'LOST',
                        'result_summary' => "Flew away @ {$crashMult}x",
                        'game_details_json' => array_merge($bet->game_details_json ?? [], [
                            'crash_multiplier' => $crashMult,
                            'cashout_amount' => 0,
                        ]),
                        'settled_at' => now(),
                    ]);

                    AccountStatement::create([
                        'user_id' => $user->id,
                        'credit' => 0,
                        'debit' => $loss,
                        'balance' => $user->fresh()->balance,
                        'remark' => "Casino [VIMAAN] #{$bet->bet_ref}: Flew away @ {$crashMult}x",
                        'from_to' => "{$user->username} -> VIMAAN",
                        'ref_type' => 'casino',
                        'ref_id' => $bet->bet_ref,
                    ]);

                    $settled[] = [
                        'bet_id' => $bet->id,
                        'stake' => $loss,
                        'crash_multiplier' => $crashMult,
                        'time' => now()->format('H:i'),
                    ];
                }
            });
        }

        $walletState = app(\App\Services\ExchangeBookService::class)->syncUserWallet($user->fresh());

        return response()->json([
            'status' => true,
            'settled' => $settled,
            'wallet' => [
                'balance' => $walletState['balance'],
                'exposure' => $walletState['exposure'],
                'available' => $walletState['available'],
            ],
        ]);
    }

    /**
     * POST /api/casino/play-round - Real-money casino round settlement in SQLite
     */
    public function playRoundApi(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'status' => false,
                'message' => 'Please login (or click Login with Demo ID) to play casino games.',
                'redirect' => route('login'),
            ], 401);
        }

        $request->validate([
            'event_id' => 'required|string',
            'selection_key' => 'required|string',
            'stake' => 'required|numeric|min:10|max:500000',
            'side' => 'nullable|string|in:BACK,LAY',
            'target_multiplier' => 'nullable|numeric|min:1.05|max:500',
        ]);

        $user = Auth::user();
        $game = CasinoGame::where('event_id', $request->input('event_id'))->first();
        if (!$game) {
            $game = CasinoGame::firstOrFail();
        }

        $stake = round((float) $request->input('stake'), 2);
        $side = strtoupper($request->input('side', 'BACK'));
        $selectionKey = $request->input('selection_key');
        $targetMultiplier = $request->filled('target_multiplier') ? (float) $request->input('target_multiplier') : null;

        if ($user->available_balance < $stake) {
            return response()->json([
                'status' => false,
                'message' => sprintf('Insufficient PTI Balance! Stake: %.2f PTI, Available: %.2f PTI', $stake, $user->available_balance),
            ], 422);
        }

        $outcome = $this->engine->playRound($game, $selectionKey, $stake, $side, $targetMultiplier);

        if ($user->available_balance < $outcome['liability']) {
            return response()->json([
                'status' => false,
                'message' => sprintf('Insufficient PTI Balance for Lay Liability (%.2f PTI).', $outcome['liability']),
            ], 422);
        }

        $bet = DB::transaction(function () use ($user, $game, $selectionKey, $side, $outcome, $request) {
            $netPl = (float) $outcome['net_pl'];
            $user->increment('balance', $netPl);

            $bet = Bet::create([
                'bet_ref' => $outcome['round_id'],
                'user_id' => $user->id,
                'category' => 'casino',
                'sport_id' => '66102',
                'sport_name' => 'Casino',
                'event_id' => $game->event_id,
                'event_name' => $game->event_name,
                'market_id' => $game->event_id,
                'market_name' => $game->menu_name ?: 'Live Casino',
                'selection_id' => $selectionKey,
                'selection_name' => $selectionKey . ($side === 'LAY' ? ' (LAY)' : ''),
                'bet_type' => $side,
                'odds' => (float) $outcome['odds'],
                'stake' => (float) $outcome['stake'],
                'potential_profit' => $outcome['won'] ? $netPl : round($outcome['stake'] * max(0.5, $outcome['odds'] - 1), 2),
                'liability' => (float) $outcome['liability'],
                'profit_loss' => $netPl,
                'status' => $outcome['won'] ? 'WON' : 'LOST',
                'result_summary' => $outcome['summary'],
                'game_details_json' => $outcome['visual'],
                'ip_address' => $request->ip() ?? '127.0.0.1',
                'settled_at' => now(),
            ]);

            AccountStatement::create([
                'user_id' => $user->id,
                'credit' => $netPl > 0 ? $netPl : 0,
                'debit' => $netPl < 0 ? abs($netPl) : 0,
                'balance' => $user->fresh()->balance,
                'remark' => "Casino [{$game->event_name}] #{$outcome['round_id']}: {$outcome['summary']}",
                'from_to' => $netPl >= 0 ? "Casino -> {$user->username}" : "{$user->username} -> Casino",
                'ref_type' => 'casino',
                'ref_id' => $outcome['round_id'],
            ]);

            return $bet;
        });

        $fresh = $user->fresh();

        return response()->json([
            'status' => true,
            'outcome' => $outcome,
            'bet' => $bet,
            'wallet' => [
                'balance' => round($fresh->balance, 2),
                'exposure' => round($fresh->exposure, 2),
                'available' => round($fresh->available_balance, 2),
            ],
        ]);
    }

    /**
     * POST /api/casino/teenpatti/place-bet - Places a bet on any of the 11 markets in 20-20 TEENPATTI (99.0010)
     */
    public function teenpattiPlaceBet(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'status' => false,
                'message' => 'Please login (or click Login with Demo ID) to play 20-20 TEENPATTI.',
                'redirect' => route('login'),
            ], 401);
        }

        $request->validate([
            'event_id' => 'nullable|string',
            'round_id' => 'nullable|string',
            'market_key' => 'required|string',
            'market_name' => 'required|string',
            'selection_key' => 'required|string',
            'selection_name' => 'required|string',
            'side' => 'nullable|string|in:BACK,LAY',
            'odds' => 'required|numeric|min:1.01|max:500',
            'stake' => 'required|numeric|min:10|max:500000',
        ]);

        $user = Auth::user();
        $eventId = $request->input('event_id') ?: '99.0010';
        $game = CasinoGame::where('event_id', $eventId)->first();
        $eventName = $game ? $game->event_name : '20-20 TEENPATTI';
        $roundId = $request->input('round_id') ?: ('91010' . random_int(11075100, 99999999));
        $side = strtoupper((string) $request->input('side', 'BACK'));
        $stake = round((float) $request->input('stake'), 2);
        $odds = round((float) $request->input('odds'), 2);
        $potentialProfit = $side === 'LAY' ? $stake : round($stake * ($odds - 1), 2);
        $liability = $side === 'LAY' ? round($stake * ($odds - 1), 2) : $stake;

        $bookSvc = app(\App\Services\ExchangeBookService::class);
        $walletBefore = $bookSvc->syncUserWallet($user->fresh());

        if ($walletBefore['available'] < $liability) {
            return response()->json([
                'status' => false,
                'message' => sprintf('Insufficient PTI Balance! Required: %.2f PTI, Available: %.2f PTI', $liability, $walletBefore['available']),
            ], 422);
        }

        $bet = DB::transaction(function () use ($eventId, $eventName, $roundId, $side, $stake, $odds, $potentialProfit, $liability, $user, $request) {
            return Bet::create([
                'bet_ref' => 'CS-' . $roundId . '-' . random_int(100, 999),
                'user_id' => $user->id,
                'category' => 'casino',
                'sport_id' => '66102',
                'sport_name' => 'Casino',
                'event_id' => $eventId,
                'event_name' => $eventName,
                'market_id' => $roundId,
                'market_name' => $request->input('market_name'),
                'selection_id' => $request->input('selection_key'),
                'selection_name' => $request->input('selection_name'),
                'bet_type' => $side,
                'odds' => $odds,
                'stake' => $stake,
                'potential_profit' => $potentialProfit,
                'liability' => $liability,
                'profit_loss' => 0,
                'status' => 'MATCHED',
                'result_summary' => 'Round #' . $roundId . ' In Progress',
                'game_details_json' => [
                    'round_id' => $roundId,
                    'market_key' => $request->input('market_key'),
                ],
                'ip_address' => $request->ip() ?? '127.0.0.1',
            ]);
        });

        $walletState = $bookSvc->syncUserWallet($user->fresh());

        return response()->json([
            'status' => true,
            'bet' => $bet,
            'wallet' => [
                'balance' => $walletState['balance'],
                'exposure' => $walletState['exposure'],
                'available' => $walletState['available'],
            ],
        ]);
    }

    /**
     * POST /api/casino/table/settle-round - Settles all queued bets for ANY generic live casino table
     * ONLY after the countdown timer finishes (0s), dealing ONE fair visual outcome for the round.
     */
    public function tableSettleRound(Request $request)
    {
        $eventId = (string) $request->input('event_id', '');
        $roundId = (string) $request->input('round_id', 'R' . now()->format('ymdHis'));
        $game = CasinoGame::where('event_id', $eventId)->first() ?? CasinoGame::firstOrFail();

        $def = $this->engine->getTableDefinition($game);
        $defaultSelection = $def['markets'][0]['key'] ?? 'PLAYER_A';

        $openBets = Auth::check()
            ? Bet::where('user_id', Auth::id())
                ->where('category', 'casino')
                ->where('event_id', $game->event_id)
                ->where('status', 'MATCHED')
                ->get()
            : collect();

        // Deal ONE fair round outcome for this timer cycle
        $firstBet = $openBets->first();
        $probeSel = $firstBet ? (string) $firstBet->selection_id : $defaultSelection;
        $probeStake = $firstBet ? (float) $firstBet->stake : 100.0;
        $probeSide = $firstBet ? strtoupper((string) $firstBet->bet_type) : 'BACK';

        $outcome = $this->engine->playRound($game, $probeSel, $probeStake, $probeSide);
        $outcome['round_id'] = $roundId;

        $settledBets = [];
        $totalNetPl = 0.0;
        $wallet = null;

        if (Auth::check()) {
            $user = Auth::user();
            if ($openBets->isNotEmpty()) {
                DB::transaction(function () use ($user, $game, $openBets, $firstBet, $outcome, &$settledBets, &$totalNetPl) {
                    foreach ($openBets as $idx => $bet) {
                        $side = strtoupper((string) $bet->bet_type);
                        $stake = round((float) $bet->stake, 2);
                        $odds = round((float) $bet->odds, 2);

                        if ($firstBet && $bet->id === $firstBet->id) {
                            $won = (bool) $outcome['won'];
                        } else {
                            $hit = $this->engine->didSelectionHitOutcome($outcome, (string) $bet->selection_id);
                            $won = ($side === 'LAY') ? !$hit : $hit;
                        }

                        if ($side === 'LAY') {
                            $netPl = $won ? $stake : -round($stake * ($odds - 1), 2);
                        } else {
                            $netPl = $won ? round($stake * ($odds - 1), 2) : -$stake;
                        }

                        $totalNetPl = round($totalNetPl + $netPl, 2);
                        $user->increment('balance', $netPl);

                        $bet->update([
                            'profit_loss' => $netPl,
                            'status' => $won ? 'WON' : 'LOST',
                            'result_summary' => $outcome['summary'],
                            'game_details_json' => $outcome['visual'],
                            'settled_at' => now(),
                        ]);

                        AccountStatement::create([
                            'user_id' => $user->id,
                            'credit' => $netPl > 0 ? $netPl : 0,
                            'debit' => $netPl < 0 ? abs($netPl) : 0,
                            'balance' => $user->fresh()->balance,
                            'remark' => "Casino [{$game->event_name}] #{$bet->market_id} ({$bet->selection_name}): {$outcome['summary']}",
                            'from_to' => $netPl >= 0 ? "Casino -> {$user->username}" : "{$user->username} -> Casino",
                            'ref_type' => 'casino',
                            'ref_id' => $bet->bet_ref,
                        ]);

                        $settledBets[] = [
                            'id' => $bet->id,
                            'selection_name' => $bet->selection_name,
                            'bet_type' => $side,
                            'stake' => $stake,
                            'odds' => $odds,
                            'status' => $won ? 'WON' : 'LOST',
                            'net_pl' => $netPl,
                            'result_summary' => $outcome['summary'],
                        ];
                    }
                });
            }

            $walletState = app(\App\Services\ExchangeBookService::class)->syncUserWallet($user->fresh());
            $wallet = [
                'balance' => $walletState['balance'],
                'exposure' => $walletState['exposure'],
                'available' => $walletState['available'],
            ];
        }

        return response()->json([
            'status' => true,
            'round_id' => $roundId,
            'outcome' => $outcome,
            'settled_bets' => $settledBets,
            'total_net_pl' => round($totalNetPl, 2),
            'wallet' => $wallet,
        ]);
    }

    /**
     * POST /api/casino/teenpatti/settle-round - Deals 6 authentic cards, evaluates all 11 Teenpatti markets & settles SQLite bets
     */
    public function teenpattiSettleRound(Request $request)
    {
        $eventId = $request->input('event_id') ?: '99.0010';
        $roundId = $request->input('round_id') ?: ('91010' . random_int(11075100, 99999999));

        $suits = ['S', 'H', 'D', 'C'];
        $ranks = [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14];
        $deck = [];
        foreach ($suits as $s) {
            foreach ($ranks as $r) {
                $rCode = match ($r) {
                    11 => 'J',
                    12 => 'Q',
                    13 => 'K',
                    14 => 'A',
                    default => (string) $r,
                };
                $deck[] = [
                    'suit' => $s,
                    'rank' => $r,
                    'code' => "{$s}{$rCode}_",
                ];
            }
        }
        shuffle($deck);

        $handA = array_slice($deck, 0, 3);
        $handB = array_slice($deck, 3, 3);

        $evalA = $this->evaluateTeenPattiHand($handA);
        $evalB = $this->evaluateTeenPattiHand($handB);

        $winner = $evalA['score'] >= $evalB['score'] ? 'A' : 'B';
        $winnerLabel = $winner === 'A' ? 'PLAYER A' : 'PLAYER B';

        $winningKeys = [$winner === 'A' ? 'PLAYER_A' : 'PLAYER_B'];

        // Evaluate side markets for Player A
        if ($evalA['is_pair'] || $evalA['is_trio']) {
            $winningKeys[] = 'PLAYER_A_PAIR';
        }
        if ($evalA['is_flush']) {
            $winningKeys[] = 'PLAYER_A_FLUSH';
        }
        if ($evalA['is_straight']) {
            $winningKeys[] = 'PLAYER_A_STRAIGHT';
        }
        if ($evalA['is_straight_flush']) {
            $winningKeys[] = 'PLAYER_A_SF';
        }
        if ($evalA['is_trio']) {
            $winningKeys[] = 'PLAYER_A_TRIO';
        }
        if ($evalA['pictures'] >= 1) {
            $winningKeys[] = 'PLAYER_A_PUTLA1';
        }
        if ($evalA['pictures'] >= 2) {
            $winningKeys[] = 'PLAYER_A_PUTLA2';
        }
        if ($evalA['pictures'] === 3) {
            $winningKeys[] = 'PLAYER_A_PUTLA3';
        }
        if ($evalA['love_marriage']) {
            $winningKeys[] = 'PLAYER_A_QK';
        }
        if ($evalA['bf_love_gf']) {
            $winningKeys[] = 'PLAYER_A_JQ';
        }

        // Evaluate side markets for Player B
        if ($evalB['is_pair'] || $evalB['is_trio']) {
            $winningKeys[] = 'PLAYER_B_PAIR';
        }
        if ($evalB['is_flush']) {
            $winningKeys[] = 'PLAYER_B_FLUSH';
        }
        if ($evalB['is_straight']) {
            $winningKeys[] = 'PLAYER_B_STRAIGHT';
        }
        if ($evalB['is_straight_flush']) {
            $winningKeys[] = 'PLAYER_B_SF';
        }
        if ($evalB['is_trio']) {
            $winningKeys[] = 'PLAYER_B_TRIO';
        }
        if ($evalB['pictures'] >= 1) {
            $winningKeys[] = 'PLAYER_B_PUTLA1';
        }
        if ($evalB['pictures'] >= 2) {
            $winningKeys[] = 'PLAYER_B_PUTLA2';
        }
        if ($evalB['pictures'] === 3) {
            $winningKeys[] = 'PLAYER_B_PUTLA3';
        }
        if ($evalB['love_marriage']) {
            $winningKeys[] = 'PLAYER_B_QK';
        }
        if ($evalB['bf_love_gf']) {
            $winningKeys[] = 'PLAYER_B_JQ';
        }

        $cardsACodes = array_column($handA, 'code');
        $cardsBCodes = array_column($handB, 'code');
        $settledBets = [];
        $wallet = null;

        if (Auth::check()) {
            $user = Auth::user();
            $openBets = Bet::where('user_id', $user->id)
                ->where('category', 'casino')
                ->where('event_id', $eventId)
                ->where('status', 'MATCHED')
                ->get();

            if ($openBets->isNotEmpty()) {
                DB::transaction(function () use ($user, $openBets, $winningKeys, $winnerLabel, $evalA, $evalB, $cardsACodes, $cardsBCodes, &$settledBets) {
                    foreach ($openBets as $bet) {
                        $won = in_array($bet->selection_id, $winningKeys, true);
                        $netPl = $won ? round((float) $bet->stake * ((float) $bet->odds - 1), 2) : -round((float) $bet->stake, 2);

                        $newExp = max(0, (float) $user->fresh()->exposure - (float) $bet->liability);
                        $user->update([
                            'exposure' => $newExp,
                            'balance' => round((float) $user->fresh()->balance + $netPl, 2),
                        ]);

                        $summary = sprintf(
                            '%s Won (A: %s vs B: %s)',
                            $winnerLabel,
                            $evalA['desc'],
                            $evalB['desc']
                        );

                        $bet->update([
                            'profit_loss' => $netPl,
                            'status' => $won ? 'WON' : 'LOST',
                            'result_summary' => $summary,
                            'game_details_json' => array_merge($bet->game_details_json ?? [], [
                                'cards_a' => $cardsACodes,
                                'cards_b' => $cardsBCodes,
                                'winner' => $winnerLabel,
                                'winning_keys' => $winningKeys,
                            ]),
                            'settled_at' => now(),
                        ]);

                        AccountStatement::create([
                            'user_id' => $user->id,
                            'credit' => $netPl > 0 ? $netPl : 0,
                            'debit' => $netPl < 0 ? abs($netPl) : 0,
                            'balance' => $user->fresh()->balance,
                            'remark' => "Casino [{$bet->event_name}] #{$bet->market_id} ({$bet->selection_name}): {$summary}",
                            'from_to' => $netPl >= 0 ? "20-20 TEENPATTI -> {$user->username}" : "{$user->username} -> 20-20 TEENPATTI",
                            'ref_type' => 'casino',
                            'ref_id' => $bet->bet_ref,
                        ]);

                        $settledBets[] = [
                            'id' => $bet->id,
                            'selection_name' => $bet->selection_name,
                            'status' => $won ? 'WON' : 'LOST',
                            'net_pl' => $netPl,
                            'result_summary' => $summary,
                        ];
                    }
                });
            }

            $walletState = app(\App\Services\ExchangeBookService::class)->syncUserWallet($user->fresh());
            $wallet = [
                'balance' => $walletState['balance'],
                'exposure' => $walletState['exposure'],
                'available' => $walletState['available'],
            ];
        }

        return response()->json([
            'status' => true,
            'round_id' => $roundId,
            'cards_a' => $cardsACodes,
            'cards_b' => $cardsBCodes,
            'hand_a_desc' => $evalA['desc'],
            'hand_b_desc' => $evalB['desc'],
            'winner' => $winner,
            'winner_label' => $winnerLabel,
            'winning_keys' => $winningKeys,
            'settled_bets' => $settledBets,
            'wallet' => $wallet,
        ]);
    }

    /**
     * Evaluates a 3-card Teenpatti hand for main hand rank and all side markets
     */
    private function evaluateTeenPattiHand(array $cards): array
    {
        $ranks = array_column($cards, 'rank');
        rsort($ranks);
        $suits = array_column($cards, 'suit');

        $isFlush = ($suits[0] === $suits[1] && $suits[1] === $suits[2]);
        $isTrio = ($ranks[0] === $ranks[1] && $ranks[1] === $ranks[2]);

        // Check straight (including A-2-3 which is [14, 3, 2])
        $isStraight = false;
        $straightTop = $ranks[0];
        if ($ranks[0] - $ranks[1] === 1 && $ranks[1] - $ranks[2] === 1) {
            $isStraight = true;
        } elseif ($ranks === [14, 3, 2]) {
            $isStraight = true;
            $straightTop = 13.5; // A-2-3 ranks just below A-K-Q in traditional Teen Patti
        }

        $isStraightFlush = $isFlush && $isStraight;
        $isPair = !$isTrio && ($ranks[0] === $ranks[1] || $ranks[1] === $ranks[2]);

        $pairRank = 0;
        $kickerRank = 0;
        if ($isPair) {
            if ($ranks[0] === $ranks[1]) {
                $pairRank = $ranks[0];
                $kickerRank = $ranks[2];
            } else {
                $pairRank = $ranks[1];
                $kickerRank = $ranks[0];
            }
        }

        // Suit tiebreaker so there is never an unresolved tie
        $suitOrder = ['S' => 4, 'H' => 3, 'D' => 2, 'C' => 1];
        $suitBonus = ($suitOrder[$cards[0]['suit']] ?? 1) * 0.01;

        if ($isTrio) {
            $category = 6;
            $score = 600000 + $ranks[0] * 1000 + $suitBonus;
            $desc = 'Trio (' . $this->rankName($ranks[0]) . 's)';
        } elseif ($isStraightFlush) {
            $category = 5;
            $score = 500000 + $straightTop * 1000 + $suitBonus;
            $desc = 'Straight Flush';
        } elseif ($isStraight) {
            $category = 4;
            $score = 400000 + $straightTop * 1000 + $suitBonus;
            $desc = 'Straight';
        } elseif ($isFlush) {
            $category = 3;
            $score = 300000 + $ranks[0] * 400 + $ranks[1] * 20 + $ranks[2] + $suitBonus;
            $desc = 'Flush';
        } elseif ($isPair) {
            $category = 2;
            $score = 200000 + $pairRank * 400 + $kickerRank + $suitBonus;
            $desc = 'Pair (' . $this->rankName($pairRank) . 's)';
        } else {
            $category = 1;
            $score = 100000 + $ranks[0] * 400 + $ranks[1] * 20 + $ranks[2] + $suitBonus;
            $desc = 'High Card (' . $this->rankName($ranks[0]) . ')';
        }

        // Picture cards count (J=11, Q=12, K=13)
        $pictures = 0;
        foreach ($ranks as $r) {
            if (in_array($r, [11, 12, 13], true)) {
                $pictures++;
            }
        }

        // Love Marriage (Q & K with same suit) & BF Love GF (J & Q with same suit)
        $loveMarriage = false;
        $bfLoveGf = false;
        foreach (['S', 'H', 'D', 'C'] as $st) {
            $suitRanks = [];
            foreach ($cards as $c) {
                if ($c['suit'] === $st) {
                    $suitRanks[] = $c['rank'];
                }
            }
            if (in_array(12, $suitRanks, true) && in_array(13, $suitRanks, true)) {
                $loveMarriage = true;
            }
            if (in_array(11, $suitRanks, true) && in_array(12, $suitRanks, true)) {
                $bfLoveGf = true;
            }
        }

        return [
            'category' => $category,
            'score' => $score,
            'desc' => $desc,
            'is_trio' => $isTrio,
            'is_straight_flush' => $isStraightFlush,
            'is_straight' => $isStraight,
            'is_flush' => $isFlush,
            'is_pair' => $isPair,
            'pictures' => $pictures,
            'love_marriage' => $loveMarriage,
            'bf_love_gf' => $bfLoveGf,
        ];
    }

    private function rankName(int $r): string
    {
        return match ($r) {
            14 => 'A',
            13 => 'K',
            12 => 'Q',
            11 => 'J',
            default => (string) $r,
        };
    }

    /**
     * POST /api/casino/lucky7/settle-round - Deals 1 authentic card, evaluates all 5 Lucky 7 markets & settles SQLite bets
     */
    public function lucky7SettleRound(Request $request)
    {
        $eventId = $request->input('event_id') ?: '99.0030';
        $roundId = $request->input('round_id') ?: ('93010' . random_int(11081932, 99999999));

        $suits = ['S', 'H', 'D', 'C'];
        $suit = $suits[random_int(0, 3)];
        // In Lucky 7, Ace is rank 1 (Low Card A to 6), 7 is Lucky 7, 8 to K(13) is High Card
        $rank = random_int(1, 13);
        $rankCode = match ($rank) {
            1 => 'A',
            11 => 'J',
            12 => 'Q',
            13 => 'K',
            default => (string) $rank,
        };
        $cardCode = "{$suit}{$rankCode}_";

        $winningKeys = [];

        // 1. WINNER (LOW_CARD vs HIGH_CARD)
        if ($rank >= 1 && $rank <= 6) {
            $winningKeys[] = 'LOW_CARD';
            $badge = 'L';
            $outcomeLabel = 'LOW CARD ( A to 6 )';
        } elseif ($rank >= 8 && $rank <= 13) {
            $winningKeys[] = 'HIGH_CARD';
            $badge = 'H';
            $outcomeLabel = 'HIGH CARD ( 8 to K )';
        } else {
            $badge = 'T';
            $outcomeLabel = 'LUCKY 7 ( TIE )';
        }

        // 2. LUCKY ODD/EVEN
        $isOdd = ($rank % 2 === 1);
        $winningKeys[] = $isOdd ? 'ODD' : 'EVEN';

        // 3. LUCKY COLOR (RED vs BLACK)
        $isRed = in_array($suit, ['H', 'D'], true);
        $winningKeys[] = $isRed ? 'RED' : 'BLACK';

        // 4. LUCKY CARD SUIT
        $winningKeys[] = 'SUIT_' . $suit;
        $suitName = match ($suit) {
            'S' => 'SPADES',
            'H' => 'HEARTS',
            'D' => 'DIAMONDS',
            'C' => 'CLUBS',
        };

        // 5. LUCKY CARD (A..K)
        $winningKeys[] = 'CARD_' . $rankCode;

        $subSummary = sprintf(
            '%s • %s • %s • CARD %s',
            $isOdd ? 'ODD' : 'EVEN',
            $isRed ? 'RED' : 'BLACK',
            $suitName,
            $rankCode
        );

        $settledBets = [];
        $wallet = null;

        if (Auth::check()) {
            $user = Auth::user();
            $openBets = Bet::where('user_id', $user->id)
                ->where('category', 'casino')
                ->where('event_id', $eventId)
                ->where('status', 'MATCHED')
                ->get();

            if ($openBets->isNotEmpty()) {
                DB::transaction(function () use ($user, $openBets, $winningKeys, $outcomeLabel, $subSummary, $cardCode, &$settledBets) {
                    foreach ($openBets as $bet) {
                        $won = in_array($bet->selection_id, $winningKeys, true);
                        $netPl = $won ? round((float) $bet->stake * ((float) $bet->odds - 1), 2) : -round((float) $bet->stake, 2);

                        $newExp = max(0, (float) $user->fresh()->exposure - (float) $bet->liability);
                        $user->update([
                            'exposure' => $newExp,
                            'balance' => round((float) $user->fresh()->balance + $netPl, 2),
                        ]);

                        $summary = "{$outcomeLabel} ({$subSummary})";

                        $bet->update([
                            'profit_loss' => $netPl,
                            'status' => $won ? 'WON' : 'LOST',
                            'result_summary' => $summary,
                            'game_details_json' => array_merge($bet->game_details_json ?? [], [
                                'card_code' => $cardCode,
                                'outcome' => $outcomeLabel,
                                'winning_keys' => $winningKeys,
                            ]),
                            'settled_at' => now(),
                        ]);

                        AccountStatement::create([
                            'user_id' => $user->id,
                            'credit' => $netPl > 0 ? $netPl : 0,
                            'debit' => $netPl < 0 ? abs($netPl) : 0,
                            'balance' => $user->fresh()->balance,
                            'remark' => "Casino [{$bet->event_name}] #{$bet->market_id} ({$bet->selection_name}): {$summary}",
                            'from_to' => $netPl >= 0 ? "LUCKY 7 -> {$user->username}" : "{$user->username} -> LUCKY 7",
                            'ref_type' => 'casino',
                            'ref_id' => $bet->bet_ref,
                        ]);

                        $settledBets[] = [
                            'id' => $bet->id,
                            'selection_name' => $bet->selection_name,
                            'status' => $won ? 'WON' : 'LOST',
                            'net_pl' => $netPl,
                            'result_summary' => $summary,
                        ];
                    }
                });
            }

            $walletState = app(\App\Services\ExchangeBookService::class)->syncUserWallet($user->fresh());
            $wallet = [
                'balance' => $walletState['balance'],
                'exposure' => $walletState['exposure'],
                'available' => $walletState['available'],
            ];
        }

        return response()->json([
            'status' => true,
            'round_id' => $roundId,
            'card_code' => $cardCode,
            'badge' => $badge,
            'outcome_label' => $outcomeLabel,
            'sub_summary' => $subSummary,
            'winning_keys' => $winningKeys,
            'settled_bets' => $settledBets,
            'wallet' => $wallet,
        ]);
    }

    /**
     * POST /api/casino/dragontiger/settle-round - Deals 2 authentic cards (Dragon & Tiger), evaluates WINNER (DRAGON, TIGER, TIE) & settles SQLite bets
     */
    public function dragonTigerSettleRound(Request $request)
    {
        $eventId = $request->input('event_id') ?: '99.0019';
        $roundId = $request->input('round_id') ?: ('91910' . random_int(11100501, 99999999));

        $suits = ['S', 'H', 'D', 'C'];
        // In Dragon Tiger: A = 1 (lowest), 2..10, J = 11, Q = 12, K = 13 (highest)
        $ranks = range(1, 13);
        $deck = [];
        foreach ($suits as $s) {
            foreach ($ranks as $r) {
                $rCode = match ($r) {
                    1 => 'A',
                    11 => 'J',
                    12 => 'Q',
                    13 => 'K',
                    default => (string) $r,
                };
                $deck[] = [
                    'suit' => $s,
                    'rank' => $r,
                    'rank_code' => $rCode,
                    'code' => "{$s}{$rCode}_",
                ];
            }
        }
        shuffle($deck);

        $dragonCard = $deck[0];
        $tigerCard = $deck[1];

        $winningKeys = [];
        if ($dragonCard['rank'] > $tigerCard['rank']) {
            $winner = 'DRAGON';
            $badge = 'D';
            $winningKeys[] = 'DRAGON';
        } elseif ($tigerCard['rank'] > $dragonCard['rank']) {
            $winner = 'TIGER';
            $badge = 'T';
            $winningKeys[] = 'TIGER';
        } else {
            $winner = 'TIE';
            $badge = 'Tie';
            $winningKeys[] = 'TIE';
        }

        // Additional Dragon Tiger side market keys for completeness
        $winningKeys[] = ($dragonCard['rank'] % 2 === 1) ? 'DRAGON_ODD' : 'DRAGON_EVEN';
        $winningKeys[] = ($tigerCard['rank'] % 2 === 1) ? 'TIGER_ODD' : 'TIGER_EVEN';
        $winningKeys[] = in_array($dragonCard['suit'], ['H', 'D'], true) ? 'DRAGON_RED' : 'DRAGON_BLACK';
        $winningKeys[] = in_array($tigerCard['suit'], ['H', 'D'], true) ? 'TIGER_RED' : 'TIGER_BLACK';
        $winningKeys[] = 'DRAGON_CARD_' . $dragonCard['rank_code'];
        $winningKeys[] = 'TIGER_CARD_' . $tigerCard['rank_code'];

        $suitSym = ['S' => '♠', 'H' => '♥', 'D' => '♦', 'C' => '♣'];
        $summary = sprintf(
            '%s (Dragon: %s%s vs Tiger: %s%s)',
            $winner,
            $dragonCard['rank_code'],
            $suitSym[$dragonCard['suit']] ?? '',
            $tigerCard['rank_code'],
            $suitSym[$tigerCard['suit']] ?? ''
        );

        $settledBets = [];
        $wallet = null;

        if (Auth::check()) {
            $user = Auth::user();
            $openBets = Bet::where('user_id', $user->id)
                ->where('category', 'casino')
                ->where('event_id', $eventId)
                ->where('status', 'MATCHED')
                ->get();

            if ($openBets->isNotEmpty()) {
                DB::transaction(function () use ($user, $openBets, $winningKeys, $winner, $summary, $dragonCard, $tigerCard, &$settledBets) {
                    foreach ($openBets as $bet) {
                        $won = in_array($bet->selection_id, $winningKeys, true);
                        $netPl = $won ? round((float) $bet->stake * ((float) $bet->odds - 1), 2) : -round((float) $bet->stake, 2);

                        $newExp = max(0, (float) $user->fresh()->exposure - (float) $bet->liability);
                        $user->update([
                            'exposure' => $newExp,
                            'balance' => round((float) $user->fresh()->balance + $netPl, 2),
                        ]);

                        $bet->update([
                            'profit_loss' => $netPl,
                            'status' => $won ? 'WON' : 'LOST',
                            'result_summary' => $summary,
                            'game_details_json' => array_merge($bet->game_details_json ?? [], [
                                'card_dragon' => $dragonCard['code'],
                                'card_tiger' => $tigerCard['code'],
                                'winner' => $winner,
                                'winning_keys' => $winningKeys,
                            ]),
                            'settled_at' => now(),
                        ]);

                        AccountStatement::create([
                            'user_id' => $user->id,
                            'credit' => $netPl > 0 ? $netPl : 0,
                            'debit' => $netPl < 0 ? abs($netPl) : 0,
                            'balance' => $user->fresh()->balance,
                            'remark' => "Casino [{$bet->event_name}] #{$bet->market_id} ({$bet->selection_name}): {$summary}",
                            'from_to' => $netPl >= 0 ? "20-20 DRAGON TIGER -> {$user->username}" : "{$user->username} -> 20-20 DRAGON TIGER",
                            'ref_type' => 'casino',
                            'ref_id' => $bet->bet_ref,
                        ]);

                        $settledBets[] = [
                            'id' => $bet->id,
                            'selection_name' => $bet->selection_name,
                            'status' => $won ? 'WON' : 'LOST',
                            'net_pl' => $netPl,
                            'result_summary' => $summary,
                        ];
                    }
                });
            }

            $walletState = app(\App\Services\ExchangeBookService::class)->syncUserWallet($user->fresh());
            $wallet = [
                'balance' => $walletState['balance'],
                'exposure' => $walletState['exposure'],
                'available' => $walletState['available'],
            ];
        }

        return response()->json([
            'status' => true,
            'round_id' => $roundId,
            'card_dragon' => $dragonCard['code'],
            'card_tiger' => $tigerCard['code'],
            'winner' => $winner,
            'badge' => $badge,
            'summary' => $summary,
            'winning_keys' => $winningKeys,
            'settled_bets' => $settledBets,
            'wallet' => $wallet,
        ]);
    }

    /**
     * POST /api/casino/lucky0to9/settle-round - Spins & settles a round of LUCKY 0 TO 9 (88.0019)
     */
    public function lucky0To9SettleRound(Request $request)
    {
        $roundId = (string) $request->input('round_id', '819' . now()->format('dmHis'));
        $eventId = (string) $request->input('event_id', '88.0019');

        $winnerNum = $request->filled('winner_number')
            ? max(0, min(9, (int) $request->input('winner_number')))
            : random_int(0, 9);

        $winningKeys = [(string) $winnerNum, 'NUM_' . $winnerNum];

        // In Winbook24 Lucky 0 to 9: 0,2,4,6,8 -> EVEN; 1,3,5,7,9 -> ODD
        if ($winnerNum % 2 === 0) {
            $winningKeys[] = 'EVEN';
            $oddEvenWinner = 'EVEN';
        } else {
            $winningKeys[] = 'ODD';
            $oddEvenWinner = 'ODD';
        }

        // In Winbook24 Lucky 0 to 9: 0 -> Green (neither RED nor BLACK wins); 1,3,5,7,9 -> RED; 2,4,6,8 -> BLACK
        if ($winnerNum === 0) {
            $colorClass = 'greenNumber';
            $colorWinner = 'GREEN (0)';
        } elseif ($winnerNum % 2 === 1) {
            $winningKeys[] = 'RED';
            $colorClass = 'redNumber';
            $colorWinner = 'RED';
        } else {
            $winningKeys[] = 'BLACK';
            $colorClass = 'blackNumber';
            $colorWinner = 'BLACK';
        }

        $summary = sprintf('WINNER: %d (%s, %s)', $winnerNum, $oddEvenWinner, $colorWinner);

        $settledBets = [];
        $wallet = null;

        if (Auth::check()) {
            $user = Auth::user();
            $openBets = Bet::where('user_id', $user->id)
                ->where('category', 'casino')
                ->where('event_id', $eventId)
                ->where('status', 'MATCHED')
                ->get();

            if ($openBets->isNotEmpty()) {
                DB::transaction(function () use ($user, $openBets, $winningKeys, $winnerNum, $oddEvenWinner, $colorWinner, $summary, &$settledBets) {
                    foreach ($openBets as $bet) {
                        $won = in_array((string) $bet->selection_id, $winningKeys, true)
                            || in_array((string) $bet->selection_name, $winningKeys, true);
                        $netPl = $won ? round((float) $bet->stake * ((float) $bet->odds - 1), 2) : -round((float) $bet->stake, 2);

                        $newExp = max(0, (float) $user->fresh()->exposure - (float) $bet->liability);
                        $user->update([
                            'exposure' => $newExp,
                            'balance' => round((float) $user->fresh()->balance + $netPl, 2),
                        ]);

                        $bet->update([
                            'profit_loss' => $netPl,
                            'status' => $won ? 'WON' : 'LOST',
                            'result_summary' => $summary,
                            'game_details_json' => array_merge($bet->game_details_json ?? [], [
                                'winner' => (string) $winnerNum,
                                'odd_even' => $oddEvenWinner,
                                'color' => $colorWinner,
                                'winning_keys' => $winningKeys,
                            ]),
                            'settled_at' => now(),
                        ]);

                        AccountStatement::create([
                            'user_id' => $user->id,
                            'credit' => $netPl > 0 ? $netPl : 0,
                            'debit' => $netPl < 0 ? abs($netPl) : 0,
                            'balance' => $user->fresh()->balance,
                            'remark' => "Casino [LUCKY 0 TO 9] #{$bet->market_id} ({$bet->selection_name}): {$summary}",
                            'from_to' => $netPl >= 0 ? "LUCKY 0 TO 9 -> {$user->username}" : "{$user->username} -> LUCKY 0 TO 9",
                            'ref_type' => 'casino',
                            'ref_id' => $bet->bet_ref,
                        ]);

                        $settledBets[] = [
                            'id' => $bet->id,
                            'selection_name' => $bet->selection_name,
                            'status' => $won ? 'WON' : 'LOST',
                            'net_pl' => $netPl,
                            'result_summary' => $summary,
                        ];
                    }
                });
            }

            $walletState = app(\App\Services\ExchangeBookService::class)->syncUserWallet($user->fresh());
            $wallet = [
                'balance' => $walletState['balance'],
                'exposure' => $walletState['exposure'],
                'available' => $walletState['available'],
            ];
        }

        return response()->json([
            'status' => true,
            'round_id' => $roundId,
            'winner' => (string) $winnerNum,
            'winner_num' => $winnerNum,
            'color_class' => $colorClass,
            'odd_even' => $oddEvenWinner,
            'color' => $colorWinner,
            'summary' => $summary,
            'winning_keys' => $winningKeys,
            'settled_bets' => $settledBets,
            'wallet' => $wallet,
        ]);
    }

    /**
     * POST /api/casino/headntails/settle-round - Flips the coin & settles a round of HEADS & TAILS (88.0021)
     */
    public function headNtailsSettleRound(Request $request)
    {
        $roundId = (string) $request->input('round_id', '821' . now()->format('dmHis'));
        $eventId = (string) $request->input('event_id', '88.0021');

        $forced = strtoupper((string) $request->input('winner', $request->input('forced_winner', '')));
        if (in_array($forced, ['HEADS', 'TAILS'], true)) {
            $winner = $forced;
        } else {
            $winner = random_int(0, 1) === 0 ? 'HEADS' : 'TAILS';
        }

        $badge = $winner === 'HEADS' ? 'H' : 'T';
        $winningKeys = [$winner, $badge];
        $summary = sprintf('WINNER: %s (%s)', $winner, $badge);

        $settledBets = [];
        $wallet = null;

        if (Auth::check()) {
            $user = Auth::user();
            $openBets = Bet::where('user_id', $user->id)
                ->where('category', 'casino')
                ->where('event_id', $eventId)
                ->where('status', 'MATCHED')
                ->get();

            if ($openBets->isNotEmpty()) {
                DB::transaction(function () use ($user, $openBets, $winningKeys, $winner, $badge, $summary, &$settledBets) {
                    foreach ($openBets as $bet) {
                        $won = in_array(strtoupper((string) $bet->selection_id), $winningKeys, true)
                            || in_array(strtoupper((string) $bet->selection_name), $winningKeys, true);
                        $netPl = $won ? round((float) $bet->stake * ((float) $bet->odds - 1), 2) : -round((float) $bet->stake, 2);

                        $newExp = max(0, (float) $user->fresh()->exposure - (float) $bet->liability);
                        $user->update([
                            'exposure' => $newExp,
                            'balance' => round((float) $user->fresh()->balance + $netPl, 2),
                        ]);

                        $bet->update([
                            'profit_loss' => $netPl,
                            'status' => $won ? 'WON' : 'LOST',
                            'result_summary' => $summary,
                            'game_details_json' => array_merge($bet->game_details_json ?? [], [
                                'winner' => $winner,
                                'badge' => $badge,
                                'winning_keys' => $winningKeys,
                            ]),
                            'settled_at' => now(),
                        ]);

                        AccountStatement::create([
                            'user_id' => $user->id,
                            'credit' => $netPl > 0 ? $netPl : 0,
                            'debit' => $netPl < 0 ? abs($netPl) : 0,
                            'balance' => $user->fresh()->balance,
                            'remark' => "Casino [HEADS & TAILS] #{$bet->market_id} ({$bet->selection_name}): {$summary}",
                            'from_to' => $netPl >= 0 ? "HEADS & TAILS -> {$user->username}" : "{$user->username} -> HEADS & TAILS",
                            'ref_type' => 'casino',
                            'ref_id' => $bet->bet_ref,
                        ]);

                        $settledBets[] = [
                            'id' => $bet->id,
                            'selection_name' => $bet->selection_name,
                            'status' => $won ? 'WON' : 'LOST',
                            'net_pl' => $netPl,
                            'result_summary' => $summary,
                        ];
                    }
                });
            }

            $walletState = app(\App\Services\ExchangeBookService::class)->syncUserWallet($user->fresh());
            $wallet = [
                'balance' => $walletState['balance'],
                'exposure' => $walletState['exposure'],
                'available' => $walletState['available'],
            ];
        }

        return response()->json([
            'status' => true,
            'round_id' => $roundId,
            'winner' => $winner,
            'badge' => $badge,
            'summary' => $summary,
            'winning_keys' => $winningKeys,
            'settled_bets' => $settledBets,
            'wallet' => $wallet,
        ]);
    }
}

