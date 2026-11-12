<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CricbuzzService
{
    /**
     * Fetch and parse all live & active cricket matches from Cricbuzz.
     */
    public function getMatchesList(bool $forceRefresh = false): array
    {
        $cacheKey = 'wb24_cricbuzz_matches_list';
        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        if (app()->runningUnitTests()) {
            return $this->loadFallbackMatches();
        }

        return Cache::remember($cacheKey, 15, function () {
            $matches = [];

            $urls = [
                'https://www.cricbuzz.com/cricket-match/live-scores',
                'https://www.cricbuzz.com/',
            ];

            foreach ($urls as $url) {
                try {
                    $resp = Http::withoutVerifying()
                        ->withHeaders([
                            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36',
                            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                            'Accept-Language' => 'en-US,en;q=0.9',
                        ])
                        ->timeout(3.5)
                        ->get($url);

                    if ($resp->successful()) {
                        $decoded = $this->decodeNextFlightStream($resp->body());
                        $parsed = $this->extractMatchesFromDecodedStream($decoded);
                        foreach ($parsed as $mId => $mData) {
                            if (!isset($matches[$mId]) || (!empty($mData['matchScore']) && empty($matches[$mId]['matchScore']))) {
                                $matches[$mId] = $mData;
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    // Continue to next URL or fallback
                }
            }

            if (empty($matches)) {
                return $this->loadFallbackMatches();
            }

            return $matches;
        });
    }

    /**
     * Return only matches that are currently LIVE / In-Play on Cricbuzz.
     */
    public function getLiveMatches(bool $forceRefresh = false): array
    {
        $all = $this->getMatchesList($forceRefresh);
        return array_values(array_filter($all, fn (array $m) => !empty($m['isLive'])));
    }

    /**
     * Match an ExchangeEvent name (e.g., "Pakistan W v Bangladesh W") to a Cricbuzz match.
     */
    public function matchEventToCricbuzz(string $eventName, ?array $matches = null): ?array
    {
        $cleanEvent = trim($eventName);
        if ($cleanEvent === '' || strcasecmp($cleanEvent, 'Ball By Ball') === 0) {
            return null;
        }

        $parts = preg_split('/\s+(?:v|vs|versus)\s+/i', $cleanEvent);
        if (!$parts || count($parts) < 2) {
            return null;
        }

        $targetA = $this->normalizeTeamName($parts[0]);
        $targetB = $this->normalizeTeamName($parts[1]);

        $matches = $matches ?? $this->getMatchesList();
        $candidates = [];

        foreach ($matches as $m) {
            $t1 = $this->normalizeTeamName($m['team1']['name'] ?? '');
            $t2 = $this->normalizeTeamName($m['team2']['name'] ?? '');
            $s1 = strtolower(trim((string) ($m['team1']['shortName'] ?? '')));
            $s2 = strtolower(trim((string) ($m['team2']['shortName'] ?? '')));

            $directMatch = ($this->teamsMatch($targetA, $t1, $s1) && $this->teamsMatch($targetB, $t2, $s2));
            $reverseMatch = ($this->teamsMatch($targetA, $t2, $s2) && $this->teamsMatch($targetB, $t1, $s1));

            if ($directMatch || $reverseMatch) {
                $m['reversedTeams'] = $reverseMatch && !$directMatch;
                $candidates[] = $m;
            }
        }

        if (empty($candidates)) {
            return null;
        }

        // Prioritize: 1. Currently Live (isLive=true), 2. Not Complete, 3. Highest matchId
        usort($candidates, function (array $a, array $b) {
            $aLive = !empty($a['isLive']) ? 1 : 0;
            $bLive = !empty($b['isLive']) ? 1 : 0;
            if ($aLive !== $bLive) {
                return $bLive <=> $aLive;
            }
            $aComp = (strcasecmp((string) ($a['state'] ?? ''), 'Complete') === 0) ? 1 : 0;
            $bComp = (strcasecmp((string) ($b['state'] ?? ''), 'Complete') === 0) ? 1 : 0;
            if ($aComp !== $bComp) {
                return $aComp <=> $bComp;
            }
            return ((int) ($b['matchId'] ?? 0)) <=> ((int) ($a['matchId'] ?? 0));
        });

        return $candidates[0];
    }

    /**
     * Fetch detailed live miniscore (batsmen, bowlers, overs, recent balls, last wicket, commentary)
     * from https://www.cricbuzz.com/live-cricket-scores/{matchId}/live
     */
    public function fetchMatchMiniscore(int|string $matchId): ?array
    {
        $matchId = (string) $matchId;
        if ($matchId === '' || $matchId === '0') {
            return null;
        }

        if (app()->runningUnitTests()) {
            return $this->loadFallbackMiniscore($matchId);
        }

        return Cache::remember("wb24_cricbuzz_mini_{$matchId}", 10, function () use ($matchId) {
            try {
                $resp = Http::withoutVerifying()
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36',
                        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    ])
                    ->timeout(3.0)
                    ->get("https://www.cricbuzz.com/live-cricket-scores/{$matchId}/live");

                if ($resp->successful()) {
                    $decoded = $this->decodeNextFlightStream($resp->body());
                    $miniscore = $this->extractSingleJsonObjectByKey($decoded, '"miniscore":{');
                    $matchHeader = $this->extractSingleJsonObjectByKey($decoded, '"matchHeader":{');
                    if (is_array($miniscore)) {
                        return [
                            'miniscore' => $miniscore,
                            'matchHeader' => is_array($matchHeader) ? $matchHeader : [],
                        ];
                    }
                }
            } catch (\Throwable $e) {
                // Fallback below
            }

            return $this->loadFallbackMiniscore($matchId);
        });
    }

    /**
     * Build a normalized Universe-Score-compatible + Cricbuzz-enriched live score payload
     * for `/fullMarket/4/{eventId}` and `/api/exchange/market/live/4/{eventId}`.
     */
    public function buildUniverseScoreForEvent(string $eventName, ?array $cricbuzzMatch = null): ?array
    {
        $match = $cricbuzzMatch ?? $this->matchEventToCricbuzz($eventName);
        if (!$match) {
            return null;
        }

        $matchId = (int) ($match['matchId'] ?? 0);
        $miniWrap = $matchId > 0 ? $this->fetchMatchMiniscore($matchId) : null;
        $mini = $miniWrap['miniscore'] ?? [];
        $header = $miniWrap['matchHeader'] ?? [];

        $format = strtoupper((string) ($match['matchFormat'] ?? ($header['matchFormat'] ?? 'T20')));
        $isTest = ($format === 'TEST');

        $t1 = $match['team1'] ?? ['id' => 1, 'name' => 'Team 1', 'shortName' => 'T1'];
        $t2 = $match['team2'] ?? ['id' => 2, 'name' => 'Team 2', 'shortName' => 'T2'];

        $t1ScoreObj = $match['matchScore']['team1Score'] ?? [];
        $t2ScoreObj = $match['matchScore']['team2Score'] ?? [];

        // Also enrich from miniscore inningsScoreList if available
        $inningsList = $mini['matchScoreDetails']['inningsScoreList'] ?? [];
        if (is_array($inningsList) && !empty($inningsList)) {
            foreach ($inningsList as $inn) {
                $batTeamId = (int) ($inn['batTeamId'] ?? 0);
                $batTeamName = strtoupper(trim((string) ($inn['batTeamName'] ?? '')));
                $innObj = [
                    'inningsId' => $inn['inningsId'] ?? 1,
                    'runs' => $inn['score'] ?? 0,
                    'wickets' => $inn['wickets'] ?? 0,
                    'overs' => $inn['overs'] ?? 0,
                    'isDeclared' => !empty($inn['isDeclared']),
                ];
                if ($batTeamId === (int) ($t1['id'] ?? -1) || $batTeamName === strtoupper((string) ($t1['shortName'] ?? ''))) {
                    if (empty($t1ScoreObj['inngs1']) || ($t1ScoreObj['inngs1']['inningsId'] ?? 1) == ($inn['inningsId'] ?? 1)) {
                        $t1ScoreObj['inngs1'] = $innObj;
                    } else {
                        $t1ScoreObj['inngs2'] = $innObj;
                    }
                } elseif ($batTeamId === (int) ($t2['id'] ?? -1) || $batTeamName === strtoupper((string) ($t2['shortName'] ?? ''))) {
                    if (empty($t2ScoreObj['inngs1']) || ($t2ScoreObj['inngs1']['inningsId'] ?? 2) == ($inn['inningsId'] ?? 2)) {
                        $t2ScoreObj['inngs1'] = $innObj;
                    } else {
                        $t2ScoreObj['inngs2'] = $innObj;
                    }
                }
            }
        }

        $batTeamId = (int) ($mini['batTeam']['teamId'] ?? ($match['currBatTeamId'] ?? 0));
        $t1Bat = ($batTeamId > 0 && $batTeamId === (int) ($t1['id'] ?? -1));
        $t2Bat = ($batTeamId > 0 && $batTeamId === (int) ($t2['id'] ?? -1));
        if (!$t1Bat && !$t2Bat) {
            if (!empty($t2ScoreObj['inngs1'])) {
                $t2Bat = true;
            } else {
                $t1Bat = true;
            }
        }

        $formatInnings = function (?array $inngs): string {
            if (empty($inngs) || !isset($inngs['runs'])) {
                return '-';
            }
            $runs = (int) $inngs['runs'];
            $wkts = (int) ($inngs['wickets'] ?? 0);
            $decl = !empty($inngs['isDeclared']) ? 'd' : '';
            return "{$runs}/{$wkts}{$decl}";
        };

        $calcRr = function (?array $inngs): string {
            if (empty($inngs) || empty($inngs['overs']) || (float) $inngs['overs'] <= 0) {
                return '0.00';
            }
            $ov = (float) $inngs['overs'];
            $wholeOvers = floor($ov);
            $balls = round(($ov - $wholeOvers) * 10);
            $totalOvers = $wholeOvers + ($balls / 6);
            if ($totalOvers <= 0) {
                return '0.00';
            }
            return number_format(((float) ($inngs['runs'] ?? 0)) / $totalOvers, 2);
        };

        $score1 = $formatInnings($t1ScoreObj['inngs1'] ?? null);
        $overs1 = isset($t1ScoreObj['inngs1']['overs']) ? (string) $t1ScoreObj['inngs1']['overs'] : '-';
        $rr1 = $calcRr($t1ScoreObj['inngs1'] ?? null);
        $score3 = $formatInnings($t1ScoreObj['inngs2'] ?? null);
        $overs3 = isset($t1ScoreObj['inngs2']['overs']) ? (string) $t1ScoreObj['inngs2']['overs'] : '';

        $score2 = $formatInnings($t2ScoreObj['inngs1'] ?? null);
        $overs2 = isset($t2ScoreObj['inngs1']['overs']) ? (string) $t2ScoreObj['inngs1']['overs'] : '-';
        $rr2 = $calcRr($t2ScoreObj['inngs1'] ?? null);
        $score4 = $formatInnings($t2ScoreObj['inngs2'] ?? null);
        $overs4 = isset($t2ScoreObj['inngs2']['overs']) ? (string) $t2ScoreObj['inngs2']['overs'] : '';

        $crr = !empty($mini['currentRunRate'])
            ? number_format((float) $mini['currentRunRate'], 2)
            : ($t1Bat ? ($rr1 !== '0.00' ? $rr1 : $calcRr($t1ScoreObj['inngs2'] ?? null)) : ($rr2 !== '0.00' ? $rr2 : $calcRr($t2ScoreObj['inngs2'] ?? null)));

        // Batsmen & Bowler details from Cricbuzz miniscore
        $striker = $mini['batsmanStriker'] ?? [];
        $nonStriker = $mini['batsmanNonStriker'] ?? [];
        $bowler = $mini['bowlerStriker'] ?? [];
        $bowlerNon = $mini['bowlerNonStriker'] ?? [];

        $batsmenParts = [];
        if (!empty($striker['name'])) {
            $batsmenParts[] = sprintf(
                '%s* %d (%d)',
                $striker['name'],
                (int) ($striker['runs'] ?? 0),
                (int) ($striker['balls'] ?? 0)
            );
        }
        if (!empty($nonStriker['name'])) {
            $batsmenParts[] = sprintf(
                '%s %d (%d)',
                $nonStriker['name'],
                (int) ($nonStriker['runs'] ?? 0),
                (int) ($nonStriker['balls'] ?? 0)
            );
        }
        $batsmenText = !empty($batsmenParts) ? implode(' | ', $batsmenParts) : '';

        $bowlerText = '';
        if (!empty($bowler['name'])) {
            $bowlerText = sprintf(
                '%s %s-%d-%d-%d',
                $bowler['name'],
                (string) ($bowler['overs'] ?? '0'),
                (int) ($bowler['maidens'] ?? 0),
                (int) ($bowler['runs'] ?? 0),
                (int) ($bowler['wickets'] ?? 0)
            );
        } elseif (!empty($bowlerNon['name'])) {
            $bowlerText = sprintf(
                '%s %s-%d-%d-%d',
                $bowlerNon['name'],
                (string) ($bowlerNon['overs'] ?? '0'),
                (int) ($bowlerNon['maidens'] ?? 0),
                (int) ($bowlerNon['runs'] ?? 0),
                (int) ($bowlerNon['wickets'] ?? 0)
            );
        }

        $activeFours = (int) ($striker['fours'] ?? 0) + (int) ($nonStriker['fours'] ?? 0);
        $activeSixes = (int) ($striker['sixes'] ?? 0) + (int) ($nonStriker['sixes'] ?? 0);

        $recentOvs = trim((string) ($mini['recentOvsStats'] ?? ''));
        $balls = [];
        if ($recentOvs !== '') {
            $tokens = preg_split('/[\s|]+/', $recentOvs);
            $tokens = array_values(array_filter($tokens, fn ($tok) => $tok !== '' && $tok !== '...'));
            $balls = array_slice($tokens, -6);
        }

        $statusText = trim((string) (
            $mini['status']
            ?? $mini['matchScoreDetails']['customStatus']
            ?? $match['status']
            ?? 'Match In Progress'
        ));

        $lastWicket = trim((string) ($mini['lastWicket'] ?? ''));
        $partnership = '';
        if (isset($mini['partnerShip']['runs'])) {
            $partnership = $mini['partnerShip']['runs'] . ' (' . ($mini['partnerShip']['balls'] ?? 0) . ')';
        }

        return [
            'isScore' => true,
            'provider' => 'Cricbuzz Live',
            'cricbuzzMatchId' => $matchId,
            'cricbuzzUrl' => "https://www.cricbuzz.com/live-cricket-scores/{$matchId}",
            'seriesName' => $match['seriesName'] ?? '',
            'matchDesc' => $match['matchDesc'] ?? '',
            'matchFormat' => $format,
            'venue' => $match['venue'] ?? '',
            'state' => $match['state'] ?? 'In Progress',
            'isLive' => !empty($match['isLive']),
            'sportId' => '4',
            'isTestMatch' => $isTest,
            'team1Name' => $t1['name'] ?? 'Team 1',
            'team2Name' => $t2['name'] ?? 'Team 2',
            'team1Short' => $t1['shortName'] ?? 'T1',
            'team2Short' => $t2['shortName'] ?? 'T2',
            'team1Flag' => $t1Bat,
            'team2Flag' => $t2Bat,
            'score1' => $score1,
            'overs1' => $overs1,
            'rr1' => $rr1,
            'totalFours1' => $t1Bat ? $activeFours : '-',
            'totalSixes1' => $t1Bat ? $activeSixes : '-',
            'totalWides1' => 0,
            'score3' => $score3 !== '-' ? $score3 : '',
            'overs3' => $overs3,
            'score2' => $score2,
            'overs2' => $overs2,
            'rr2' => $rr2,
            'totalFours2' => $t2Bat ? $activeFours : '-',
            'totalSixes2' => $t2Bat ? $activeSixes : '-',
            'totalWides2' => 0,
            'score4' => $score4 !== '-' ? $score4 : '',
            'overs4' => $overs4,
            'runRate' => $crr ?: '0.00',
            'reqRunRate' => !empty($mini['requiredRunRate']) ? number_format((float) $mini['requiredRunRate'], 2) : '',
            'batsmenText' => $batsmenText,
            'bowlerText' => $bowlerText,
            'batsmanStriker' => $striker,
            'batsmanNonStriker' => $nonStriker,
            'bowlerStriker' => $bowler,
            'bowlerNonStriker' => $bowlerNon,
            'lastWicket' => $lastWicket,
            'partnership' => $partnership,
            'recentOvsStats' => $recentOvs,
            'Commentary' => $statusText,
            'balls' => $balls,
            'shortSummary' => $match['shortScoreSummary'] ?? $this->formatShortSummary($t1, $t2, $t1ScoreObj, $t2ScoreObj),
        ];
    }

    /**
     * Decode Next.js Flight payload chunks `self.__next_f.push([1,"..."])` into a single JSON string stream.
     */
    public function decodeNextFlightStream(string $html): string
    {
        if (!preg_match_all('/self\.__next_f\.push\(\[1,"(.*?)"\]\)/s', $html, $matches)) {
            return '';
        }

        $combined = '';
        foreach ($matches[1] as $rawChunk) {
            $decoded = json_decode('"' . $rawChunk . '"');
            if (is_string($decoded)) {
                $combined .= $decoded;
            }
        }
        return $combined;
    }

    /**
     * Extract all `{"matchInfo":{...},"matchScore":{...}}` objects from the decoded Next.js Flight stream.
     */
    public function extractMatchesFromDecodedStream(string $stream): array
    {
        $results = [];
        $offset = 0;
        $needle = '"matchInfo":{';
        $len = strlen($stream);

        while (($pos = strpos($stream, $needle, $offset)) !== false) {
            $braceStart = $pos + strlen('"matchInfo":');
            [$matchInfoJson, $endPos] = $this->extractBalancedBraces($stream, $braceStart);
            if ($matchInfoJson === null) {
                $offset = $braceStart + 1;
                continue;
            }

            $offset = $endPos;
            $matchInfo = json_decode($matchInfoJson, true);
            if (!is_array($matchInfo) || empty($matchInfo['matchId']) || empty($matchInfo['team1']['teamName'])) {
                continue;
            }

            $matchScore = [];
            $afterInfo = substr($stream, $endPos, 20);
            if (str_starts_with($afterInfo, ',"matchScore":{')) {
                $scoreStart = $endPos + strlen(',"matchScore":');
                [$scoreJson, $scoreEnd] = $this->extractBalancedBraces($stream, $scoreStart);
                if ($scoreJson !== null) {
                    $decodedScore = json_decode($scoreJson, true);
                    if (is_array($decodedScore)) {
                        $matchScore = $decodedScore;
                    }
                    $offset = $scoreEnd;
                }
            }

            $mId = (int) $matchInfo['matchId'];
            $normalized = $this->normalizeCricbuzzMatch($matchInfo, $matchScore);

            if (!isset($results[$mId]) || (!empty($matchScore) && empty($results[$mId]['matchScore']))) {
                $results[$mId] = $normalized;
            }
        }

        return $results;
    }

    /**
     * Normalize a raw Cricbuzz matchInfo + matchScore pair.
     */
    public function normalizeCricbuzzMatch(array $matchInfo, array $matchScore = []): array
    {
        $state = trim((string) ($matchInfo['state'] ?? 'Preview'));
        $stateLower = strtolower($state);

        $liveStates = [
            'in progress',
            'live',
            'innings break',
            'toss',
            'stumps',
            'lunch',
            'tea',
            'drinks',
            'rain',
            'delay',
            'wet outfield',
            'bad light',
        ];
        $isLive = in_array($stateLower, $liveStates, true);

        $t1 = [
            'id' => $matchInfo['team1']['teamId'] ?? 1,
            'name' => $matchInfo['team1']['teamName'] ?? 'Team 1',
            'shortName' => $matchInfo['team1']['teamSName'] ?? 'T1',
        ];
        $t2 = [
            'id' => $matchInfo['team2']['teamId'] ?? 2,
            'name' => $matchInfo['team2']['teamName'] ?? 'Team 2',
            'shortName' => $matchInfo['team2']['teamSName'] ?? 'T2',
        ];

        $startMs = (int) ($matchInfo['startDate'] ?? 0);
        $startIso = $startMs > 0
            ? Carbon::createFromTimestampMs($startMs)->toIso8601String()
            : now()->toIso8601String();

        $venueGround = $matchInfo['venueInfo']['ground'] ?? '';
        $venueCity = $matchInfo['venueInfo']['city'] ?? '';
        $venue = trim($venueGround . ($venueCity ? ", {$venueCity}" : ''), ', ');

        $t1ScoreObj = $matchScore['team1Score'] ?? [];
        $t2ScoreObj = $matchScore['team2Score'] ?? [];

        return [
            'matchId' => (int) $matchInfo['matchId'],
            'seriesId' => (int) ($matchInfo['seriesId'] ?? 0),
            'seriesName' => $matchInfo['seriesName'] ?? 'Cricket Series',
            'matchDesc' => $matchInfo['matchDesc'] ?? '',
            'matchFormat' => $matchInfo['matchFormat'] ?? 'T20',
            'state' => $state,
            'stateTitle' => $matchInfo['stateTitle'] ?? $state,
            'status' => $matchInfo['status'] ?? $state,
            'isLive' => $isLive,
            'startDate' => $startIso,
            'team1' => $t1,
            'team2' => $t2,
            'venue' => $venue,
            'currBatTeamId' => $matchInfo['currBatTeamId'] ?? null,
            'matchScore' => $matchScore,
            'shortScoreSummary' => $this->formatShortSummary($t1, $t2, $t1ScoreObj, $t2ScoreObj),
        ];
    }

    private function formatShortSummary(array $t1, array $t2, array $t1ScoreObj, array $t2ScoreObj): string
    {
        $parts = [];
        if (!empty($t1ScoreObj['inngs1']) && isset($t1ScoreObj['inngs1']['runs'])) {
            $in1 = $t1ScoreObj['inngs1'];
            $s = ($t1['shortName'] ?? 'T1') . ' ' . $in1['runs'] . '/' . ($in1['wickets'] ?? 0);
            if (!empty($t1ScoreObj['inngs2']) && isset($t1ScoreObj['inngs2']['runs'])) {
                $in2 = $t1ScoreObj['inngs2'];
                $s .= ' & ' . $in2['runs'] . '/' . ($in2['wickets'] ?? 0) . ' (' . ($in2['overs'] ?? 0) . ')';
            } elseif (isset($in1['overs'])) {
                $s .= ' (' . $in1['overs'] . ')';
            }
            $parts[] = $s;
        }
        if (!empty($t2ScoreObj['inngs1']) && isset($t2ScoreObj['inngs1']['runs'])) {
            $in1 = $t2ScoreObj['inngs1'];
            $s = ($t2['shortName'] ?? 'T2') . ' ' . $in1['runs'] . '/' . ($in1['wickets'] ?? 0);
            if (!empty($t2ScoreObj['inngs2']) && isset($t2ScoreObj['inngs2']['runs'])) {
                $in2 = $t2ScoreObj['inngs2'];
                $s .= ' & ' . $in2['runs'] . '/' . ($in2['wickets'] ?? 0) . ' (' . ($in2['overs'] ?? 0) . ')';
            } elseif (isset($in1['overs'])) {
                $s .= ' (' . $in1['overs'] . ')';
            }
            $parts[] = $s;
        }
        return implode(' vs ', $parts);
    }

    private function extractSingleJsonObjectByKey(string $stream, string $keyPrefix): ?array
    {
        $pos = strpos($stream, $keyPrefix);
        if ($pos === false) {
            return null;
        }
        $braceStart = $pos + strlen($keyPrefix) - 1;
        [$jsonStr] = $this->extractBalancedBraces($stream, $braceStart);
        if ($jsonStr === null) {
            return null;
        }
        // Replace Next.js "$undefined" sentinel with null
        $cleanJson = str_replace('"$undefined"', 'null', $jsonStr);
        $decoded = json_decode($cleanJson, true);
        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Extract a balanced `{ ... }` JSON substring starting at `$braceStart`.
     *
     * @return array{0: ?string, 1: int}
     */
    private function extractBalancedBraces(string $str, int $braceStart): array
    {
        $len = strlen($str);
        if ($braceStart >= $len || $str[$braceStart] !== '{') {
            return [null, $braceStart + 1];
        }

        $depth = 0;
        $inString = false;
        $escape = false;

        for ($i = $braceStart; $i < $len; $i++) {
            $ch = $str[$i];
            if ($escape) {
                $escape = false;
                continue;
            }
            if ($ch === '\\' && $inString) {
                $escape = true;
                continue;
            }
            if ($ch === '"') {
                $inString = !$inString;
                continue;
            }
            if (!$inString) {
                if ($ch === '{') {
                    $depth++;
                } elseif ($ch === '}') {
                    $depth--;
                    if ($depth === 0) {
                        return [substr($str, $braceStart, $i - $braceStart + 1), $i + 1];
                    }
                }
            }
        }

        return [null, $len];
    }

    public function normalizeTeamName(string $name): string
    {
        $n = strtolower(trim($name));
        // Convert trailing " w" or " wmn" to " women"
        $n = preg_replace('/\bw\b/', 'women', $n);
        $n = preg_replace('/\bwomen\'?s\b/', 'women', $n);
        $n = preg_replace('/\bu-?19\b/', 'u19', $n);
        $n = preg_replace('/[^a-z0-9\s]/', ' ', $n);
        $n = preg_replace('/\s+/', ' ', $n);
        return trim($n);
    }

    private function teamsMatch(string $normTarget, string $normCricbuzz, string $shortCricbuzz): bool
    {
        if ($normTarget === '' || $normCricbuzz === '') {
            return false;
        }
        if ($normTarget === $normCricbuzz || $normTarget === $shortCricbuzz) {
            return true;
        }

        // Prevent Women's vs Men's or U19 vs Senior false matches
        $targetHasWomen = str_contains($normTarget, 'women');
        $cbHasWomen = str_contains($normCricbuzz, 'women');
        if ($targetHasWomen !== $cbHasWomen) {
            return false;
        }

        $targetHasU19 = str_contains($normTarget, 'u19');
        $cbHasU19 = str_contains($normCricbuzz, 'u19');
        if ($targetHasU19 !== $cbHasU19) {
            return false;
        }

        $targetHasA = (bool) preg_match('/\ba\b/', $normTarget);
        $cbHasA = (bool) preg_match('/\ba\b/', $normCricbuzz);
        if ($targetHasA !== $cbHasA) {
            return false;
        }

        if (str_contains($normCricbuzz, $normTarget) || str_contains($normTarget, $normCricbuzz)) {
            return true;
        }

        return false;
    }

    private function loadFallbackMatches(): array
    {
        $path = database_path('data/cricbuzzLiveMatches.json');
        if (!file_exists($path)) {
            return [];
        }
        $raw = @file_get_contents($path);
        $data = $raw ? json_decode($raw, true) : null;
        return is_array($data['matches'] ?? null) ? $data['matches'] : [];
    }

    private function loadFallbackMiniscore(string $matchId): ?array
    {
        $path = database_path('data/cricbuzzLiveMatches.json');
        if (!file_exists($path)) {
            return null;
        }
        $raw = @file_get_contents($path);
        $data = $raw ? json_decode($raw, true) : null;
        return $data['miniscores'][$matchId] ?? null;
    }
}

