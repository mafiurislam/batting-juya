<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class SportmonksService
{
    public function getToken(): string
    {
        $dbToken = trim((string) SiteSetting::getValue('sportmonks_api_token', ''));
        if ($dbToken !== '') {
            return $dbToken;
        }
        return trim((string) config('services.sportmonks.token', ''));
    }

    public function getFootballUrl(): string
    {
        $dbUrl = trim((string) SiteSetting::getValue('sportmonks_football_url', ''));
        return rtrim($dbUrl ?: (string) config('services.sportmonks.football_url', 'https://api.sportmonks.com/v3/football'), '/');
    }

    public function getCricketUrl(): string
    {
        $dbUrl = trim((string) SiteSetting::getValue('sportmonks_cricket_url', ''));
        return rtrim($dbUrl ?: (string) config('services.sportmonks.cricket_url', 'https://cricket.sportmonks.com/api/v2.0'), '/');
    }

    public function getF1Url(): string
    {
        $dbUrl = trim((string) SiteSetting::getValue('sportmonks_f1_url', ''));
        return rtrim($dbUrl ?: (string) config('services.sportmonks.f1_url', 'https://f1.sportmonks.com/api/v1.0'), '/');
    }

    public function isConfigured(): bool
    {
        $token = $this->getToken();
        return $token !== '' && $token !== 'your_actual_token';
    }

    /**
     * Football API v3 - Live Inplay Matches with Events, Teams (participants) & Statistics.
     * GET https://api.sportmonks.com/v3/football/livescores/inplay?api_token=YOUR_TOKEN&include=events;participants;statistics
     */
    public function getFootballInplay(string $include = 'events;participants;statistics'): array
    {
        if (!$this->isConfigured()) {
            return [
                'status' => false,
                'configured' => false,
                'provider' => 'sportmonks_football_v3',
                'message' => 'SPORTMONKS_API_TOKEN is not set in .env or Admin Settings.',
                'data' => [],
            ];
        }

        return Cache::remember('sportmonks_football_inplay_' . md5($include), 5, function () use ($include) {
            try {
                $response = Http::withoutVerifying()->timeout(4)->get(
                    $this->getFootballUrl() . '/livescores/inplay',
                    [
                        'api_token' => $this->getToken(),
                        'include' => $include,
                    ]
                );

                $json = $response->json() ?? [];
                return [
                    'status' => $response->successful(),
                    'configured' => true,
                    'provider' => 'sportmonks_football_v3',
                    'http_code' => $response->status(),
                    'data' => $json['data'] ?? [],
                    'subscription' => $json['subscription'] ?? null,
                    'rate_limit' => $json['rate_limit'] ?? null,
                    'message' => $json['message'] ?? ($response->successful() ? 'OK' : 'Sportmonks Football API error'),
                ];
            } catch (\Throwable $e) {
                return [
                    'status' => false,
                    'configured' => true,
                    'provider' => 'sportmonks_football_v3',
                    'message' => $e->getMessage(),
                    'data' => [],
                ];
            }
        });
    }

    /**
     * Cricket API v2 - Live Scores with Batting, Bowling & Runs.
     * GET https://cricket.sportmonks.com/api/v2.0/livescores?api_token=YOUR_TOKEN&include=batting,bowling,runs
     */
    public function getCricketLivescores(string $include = 'batting,bowling,runs,localteam,visitorteam'): array
    {
        if (!$this->isConfigured()) {
            return [
                'status' => false,
                'configured' => false,
                'provider' => 'sportmonks_cricket_v2',
                'message' => 'SPORTMONKS_API_TOKEN is not set in .env or Admin Settings.',
                'data' => [],
            ];
        }

        return Cache::remember('sportmonks_cricket_livescores_' . md5($include), 5, function () use ($include) {
            try {
                $response = Http::withoutVerifying()->timeout(4)->get(
                    $this->getCricketUrl() . '/livescores',
                    [
                        'api_token' => $this->getToken(),
                        'include' => $include,
                    ]
                );

                $json = $response->json() ?? [];
                return [
                    'status' => $response->successful(),
                    'configured' => true,
                    'provider' => 'sportmonks_cricket_v2',
                    'http_code' => $response->status(),
                    'data' => $json['data'] ?? [],
                    'message' => $json['message']['text'] ?? ($json['message'] ?? ($response->successful() ? 'OK' : 'Sportmonks Cricket API error')),
                ];
            } catch (\Throwable $e) {
                return [
                    'status' => false,
                    'configured' => true,
                    'provider' => 'sportmonks_cricket_v2',
                    'message' => $e->getMessage(),
                    'data' => [],
                ];
            }
        });
    }

    /**
     * Cricket API v2 - Ball-by-Ball Live Data.
     * GET https://cricket.sportmonks.com/api/v2.0/livescores?api_token=YOUR_TOKEN&include=balls
     */
    public function getCricketBallByBall(string $include = 'balls,batting,bowling,runs,localteam,visitorteam'): array
    {
        return $this->getCricketLivescores($include);
    }

    /**
     * Formula 1 API v1 - Live Data Endpoint.
     * GET https://f1.sportmonks.com/api/v1.0/{endpoint}?api_token=YOUR_TOKEN
     */
    public function getF1Live(string $endpoint = 'tracks', string $include = ''): array
    {
        if (!$this->isConfigured()) {
            return [
                'status' => false,
                'configured' => false,
                'provider' => 'sportmonks_f1_v1',
                'message' => 'SPORTMONKS_API_TOKEN is not set in .env or Admin Settings.',
                'data' => [],
            ];
        }

        $endpoint = ltrim($endpoint, '/');
        return Cache::remember('sportmonks_f1_' . md5($endpoint . '_' . $include), 10, function () use ($endpoint, $include) {
            try {
                $query = ['api_token' => $this->getToken()];
                if ($include !== '') {
                    $query['include'] = $include;
                }
                $response = Http::withoutVerifying()->timeout(4)->get(
                    $this->getF1Url() . '/' . $endpoint,
                    $query
                );

                $json = $response->json() ?? [];
                return [
                    'status' => $response->successful(),
                    'configured' => true,
                    'provider' => 'sportmonks_f1_v1',
                    'http_code' => $response->status(),
                    'data' => $json['data'] ?? [],
                    'message' => $json['message'] ?? ($response->successful() ? 'OK' : 'Sportmonks F1 API error'),
                ];
            } catch (\Throwable $e) {
                return [
                    'status' => false,
                    'configured' => true,
                    'provider' => 'sportmonks_f1_v1',
                    'message' => $e->getMessage(),
                    'data' => [],
                ];
            }
        });
    }

    /**
     * Match an Exchange Event (Cricket sportId=4 or Football sportId=1) to live Sportmonks data
     * and normalize into the UniverseScore structure used by full-market.blade.php.
     */
    public function matchLiveScoreForEvent(string $sportId, string $eventName): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $parts = array_map(fn ($p) => strtolower(trim($p)), explode(' v ', $eventName));
        $teamA = $parts[0] ?? '';
        $teamB = $parts[1] ?? '';

        if ($sportId === '4') {
            $res = $this->getCricketLivescores('batting,bowling,runs,localteam,visitorteam');
            $fixtures = $res['data'] ?? [];
            if (empty($fixtures) || !is_array($fixtures)) {
                return null;
            }

            foreach ($fixtures as $fix) {
                $localName = $fix['localteam']['name'] ?? ($fix['localteam_name'] ?? 'Team 1');
                $visitorName = $fix['visitorteam']['name'] ?? ($fix['visitorteam_name'] ?? 'Team 2');
                $lLower = strtolower($localName);
                $vLower = strtolower($visitorName);

                $matched = ($teamA && (str_contains($lLower, $teamA) || str_contains($teamA, $lLower) || str_contains($vLower, $teamA)))
                    || ($teamB && (str_contains($lLower, $teamB) || str_contains($vLower, $teamB) || str_contains($teamB, $vLower)));

                if ($matched) {
                    return $this->normalizeCricketFixture($fix, $localName, $visitorName);
                }
            }
        } elseif ($sportId === '1') {
            $res = $this->getFootballInplay('events;participants;statistics');
            $fixtures = $res['data'] ?? [];
            if (empty($fixtures) || !is_array($fixtures)) {
                return null;
            }

            foreach ($fixtures as $fix) {
                $participants = $fix['participants'] ?? [];
                $home = $participants[0] ?? [];
                $away = $participants[1] ?? [];
                foreach ($participants as $p) {
                    $loc = $p['meta']['location'] ?? '';
                    if ($loc === 'home') {
                        $home = $p;
                    } elseif ($loc === 'away') {
                        $away = $p;
                    }
                }
                $hName = $home['name'] ?? 'Home';
                $aName = $away['name'] ?? 'Away';
                $hLower = strtolower($hName);
                $aLower = strtolower($aName);

                $matched = ($teamA && (str_contains($hLower, $teamA) || str_contains($teamA, $hLower) || str_contains($aLower, $teamA)))
                    || ($teamB && (str_contains($hLower, $teamB) || str_contains($aLower, $teamB) || str_contains($teamB, $aLower)));

                if ($matched) {
                    return $this->normalizeFootballFixture($fix, $home, $away);
                }
            }
        }

        return null;
    }

    private function normalizeCricketFixture(array $fix, string $localName, string $visitorName): array
    {
        $runs = $fix['runs'] ?? [];
        $localId = $fix['localteam_id'] ?? ($fix['localteam']['id'] ?? 1);
        $visitorId = $fix['visitorteam_id'] ?? ($fix['visitorteam']['id'] ?? 2);

        $r1 = collect($runs)->where('team_id', $localId)->last() ?? ($runs[0] ?? []);
        $r2 = collect($runs)->where('team_id', $visitorId)->last() ?? ($runs[1] ?? []);

        $score1 = isset($r1['score']) ? ($r1['score'] . '/' . ($r1['wickets'] ?? 0)) : '-';
        $overs1 = isset($r1['overs']) ? (string) $r1['overs'] : '-';
        $rr1 = (isset($r1['score'], $r1['overs']) && (float) $r1['overs'] > 0)
            ? number_format((float) $r1['score'] / (float) $r1['overs'], 2)
            : '-';

        $score2 = isset($r2['score']) ? ($r2['score'] . '/' . ($r2['wickets'] ?? 0)) : '-';
        $overs2 = isset($r2['overs']) ? (string) $r2['overs'] : '-';
        $rr2 = (isset($r2['score'], $r2['overs']) && (float) $r2['overs'] > 0)
            ? number_format((float) $r2['score'] / (float) $r2['overs'], 2)
            : '-';

        return [
            'isScore' => true,
            'sportId' => '4',
            'isTestMatch' => str_contains(strtolower((string) ($fix['type'] ?? '')), 'test'),
            'team1Name' => $localName,
            'team2Name' => $visitorName,
            'team1Icon' => $fix['localteam']['image_path'] ?? null,
            'team2Icon' => $fix['visitorteam']['image_path'] ?? null,
            'team1Flag' => true,
            'team2Flag' => false,
            'score1' => $score1,
            'overs1' => $overs1,
            'rr1' => $rr1,
            'totalFours1' => '-',
            'totalSixes1' => '-',
            'totalWides1' => '-',
            'score2' => $score2,
            'overs2' => $overs2,
            'rr2' => $rr2,
            'totalFours2' => '-',
            'totalSixes2' => '-',
            'totalWides2' => '-',
            'runRate' => $rr1 !== '-' ? $rr1 : ($rr2 !== '-' ? $rr2 : '0.00'),
            'batsmenText' => '',
            'bowlerText' => '',
            'Commentary' => $fix['note'] ?? ($fix['status'] ?? 'LIVE'),
        ];
    }

    private function normalizeFootballFixture(array $fix, array $home, array $away): array
    {
        $scores = $fix['scores'] ?? [];
        $hScore = 0;
        $aScore = 0;
        $htH = 0;
        $htA = 0;

        foreach ($scores as $s) {
            $desc = strtoupper((string) ($s['description'] ?? ''));
            $part = $s['score']['participant'] ?? '';
            $goals = (int) ($s['score']['goals'] ?? 0);
            if ($desc === 'CURRENT') {
                if ($part === 'home') {
                    $hScore = $goals;
                } elseif ($part === 'away') {
                    $aScore = $goals;
                }
            } elseif ($desc === '1ST_HALF') {
                if ($part === 'home') {
                    $htH = $goals;
                } elseif ($part === 'away') {
                    $htA = $goals;
                }
            }
        }

        return [
            'isScore' => true,
            'sportId' => '1',
            'team1Name' => $home['name'] ?? 'Home',
            'team2Name' => $away['name'] ?? 'Away',
            'team1Icon' => $home['image_path'] ?? null,
            'team2Icon' => $away['image_path'] ?? null,
            'score1' => (string) $hScore,
            'htScore1' => (string) $htH,
            'corner1' => '0',
            'yellowCard1' => '0',
            'redCard1' => '0',
            'goalInfo1' => '',
            'score2' => (string) $aScore,
            'htScore2' => (string) $htA,
            'corner2' => '0',
            'yellowCard2' => '0',
            'redCard2' => '0',
            'goalInfo2' => '',
            'status' => $fix['name'] ?? 'IN PLAY',
        ];
    }
}

