<?php

namespace Database\Seeders;

use App\Models\AccountStatement;
use App\Models\ActivityLog;
use App\Models\CasinoGame;
use App\Models\ExchangeEvent;
use App\Models\PasswordHistory;
use App\Models\SiteSetting;
use App\Models\Sport;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $defaultRolling = [
            'fancy' => 0,
            'matka' => 0,
            'casino' => 0,
            'binary' => 0,
            'sportbook' => 0,
            'bookmaker' => 0,
        ];

        $defaultStakes = [
            ['label' => '100', 'value' => 100],
            ['label' => '500', 'value' => 500],
            ['label' => '1000', 'value' => 1000],
            ['label' => '5000', 'value' => 5000],
            ['label' => '10000', 'value' => 10000],
            ['label' => '25000', 'value' => 25000],
            ['label' => '50000', 'value' => 50000],
            ['label' => '100000', 'value' => 100000],
        ];

        // 1. Seed Users (Admin, Custom Admin admin@12345, Demo, Player)
        $admin = User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Master Admin',
                'email' => 'admin@winbook24.com',
                'country_code' => '+91',
                'phone' => '9831391470',
                'phone_verified_at' => now(),
                'password' => Hash::make('Admin@123'),
                'balance' => 500000.00,
                'exposure' => 0.00,
                'referral_code' => 'WB24ADMIN',
                'is_admin' => true,
                'is_demo' => false,
                'status' => 'active',
                'stake_settings' => $defaultStakes,
                'rolling_commission' => $defaultRolling,
            ]
        );

        User::updateOrCreate(
            ['username' => 'admin@12345'],
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@cricexperts.com',
                'country_code' => '+91',
                'phone' => '9831391471',
                'phone_verified_at' => now(),
                'password' => Hash::make('admin123'),
                'balance' => 1000000.00,
                'exposure' => 0.00,
                'referral_code' => 'CRICADMIN',
                'is_admin' => true,
                'is_demo' => false,
                'status' => 'active',
                'two_factor_enabled' => true,
                'two_factor_code' => 'CRIC2FA99887',
                'stake_settings' => $defaultStakes,
                'rolling_commission' => [
                    'fancy' => 2.0,
                    'matka' => 1.5,
                    'casino' => 2.0,
                    'binary' => 1.0,
                    'sportbook' => 1.5,
                    'bookmaker' => 1.0,
                ],
            ]
        );

        $demo = User::updateOrCreate(
            ['username' => 'demo'],
            [
                'name' => 'Demo User',
                'email' => 'demo@winbook24.com',
                'country_code' => '+91',
                'phone' => '9000000000',
                'phone_verified_at' => now(),
                'password' => Hash::make('Demo@123'),
                'balance' => 10000.00,
                'exposure' => 0.00,
                'referral_code' => 'WB24DEMO',
                'is_admin' => false,
                'is_demo' => true,
                'status' => 'active',
                'stake_settings' => $defaultStakes,
                'rolling_commission' => $defaultRolling,
            ]
        );

        $player = User::updateOrCreate(
            ['username' => 'player1'],
            [
                'name' => 'Rahul Sharma',
                'email' => 'player1@winbook24.com',
                'country_code' => '+91',
                'phone' => '9876543210',
                'phone_verified_at' => now(),
                'password' => Hash::make('Player@123'),
                'balance' => 5000.00,
                'exposure' => 0.00,
                'referral_code' => 'WB24RAHUL',
                'is_admin' => false,
                'is_demo' => false,
                'status' => 'active',
                'stake_settings' => $defaultStakes,
                'rolling_commission' => $defaultRolling,
            ]
        );

        foreach ([$admin, $demo, $player] as $u) {
            AccountStatement::firstOrCreate(
                ['user_id' => $u->id, 'ref_type' => 'opening'],
                [
                    'credit' => $u->balance,
                    'debit' => 0,
                    'balance' => $u->balance,
                    'remark' => 'Opening Account Balance PTI',
                    'from_to' => 'Upline -> ' . $u->username,
                ]
            );
            ActivityLog::firstOrCreate(
                ['user_id' => $u->id],
                [
                    'login_status' => 'Login Success',
                    'ip_address' => '127.0.0.1',
                    'isp' => 'Reliance Jio Infocomm',
                    'city_state_country' => 'Kolkata / West Bengal / India',
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/131.0.0.0',
                ]
            );
            PasswordHistory::firstOrCreate(
                ['user_id' => $u->id],
                [
                    'changed_by' => 'System',
                    'remark' => 'Account Created & Password Initialized',
                    'ip_address' => '127.0.0.1',
                ]
            );
        }

        // 2. Seed Site Settings
        $themeJson = @file_get_contents(database_path('data/themeConfig.json'));
        $themeData = $themeJson ? (json_decode($themeJson, true)['data'] ?? []) : [];
        $tipsJson = @file_get_contents(database_path('data/tipsPreviewList.json'));
        $tipsData = $tipsJson ? (json_decode($tipsJson, true)['data']['preview'] ?? []) : [];

        SiteSetting::setValue('exchange_name', 'winbook24.com', 'general');
        SiteSetting::setValue('exchange_title', 'winbook24', 'general');
        SiteSetting::setValue('news_marquee', 'Minimum deposit 500 and minimum withdrawal 1000 | Welcome to winbook24.com - Instant Deposit & 24x7 Fast Withdrawal!', 'general');
        SiteSetting::setValue('support_whatsapp', '+919831391470', 'general');
        SiteSetting::setValue('support_telegram', 'https://t.me/winbook24official', 'general');
        SiteSetting::setValue('welcome_bonus_pti', '1000.00', 'limits');
        SiteSetting::setValue('min_deposit', '100', 'banking');
        SiteSetting::setValue('min_withdraw', '1000', 'banking');
        SiteSetting::setValue('deposit_upi_id', 'winbook24.pay@axis', 'banking');
        SiteSetting::setValue('deposit_account_name', 'WINBOOK24 ENTERPRISES', 'banking');
        SiteSetting::setValue('deposit_bank_name', 'HDFC Bank', 'banking');
        SiteSetting::setValue('deposit_account_number', '50200089412580', 'banking');
        SiteSetting::setValue('deposit_ifsc', 'HDFC0001245', 'banking');
        SiteSetting::setValue('msg91_auth_key', env('MSG91_AUTH_KEY', ''), 'msg91');
        SiteSetting::setValue('msg91_template_id', env('MSG91_TEMPLATE_ID', ''), 'msg91');
        SiteSetting::setValue('msg91_sender_id', env('MSG91_SENDER_ID', 'WINB24'), 'msg91');
        SiteSetting::setValue('msg91_sandbox_show_otp', env('MSG91_SANDBOX_SHOW_OTP', true) ? '1' : '0', 'msg91');
        SiteSetting::setValue('banners', ['/assets/brand/banner-1.jpg'], 'general');
        SiteSetting::setValue('theme_config', $themeData, 'theme');
        SiteSetting::setValue('tips_previews', array_slice($tipsData, 0, 18), 'general');

        // 3. Seed Sports
        $sportsJson = @file_get_contents(database_path('data/sportsList.json'));
        $sportsList = $sportsJson ? (json_decode($sportsJson, true)['data'] ?? []) : [];
        foreach ($sportsList as $s) {
            Sport::updateOrCreate(
                ['sport_id' => (string) $s['sportId']],
                [
                    'sport_name' => $s['sportName'],
                    'sequence' => (int) ($s['sequence'] ?? 10),
                    'popular' => (bool) ($s['popular'] ?? true),
                    'highlight' => (bool) ($s['highlight'] ?? false),
                    'others' => (bool) ($s['others'] ?? false),
                    'status' => (bool) ($s['status'] ?? true),
                ]
            );
        }

        // 4. Seed Exchange Events
        $eventsJson = @file_get_contents(database_path('data/allEventsList.json'));
        $eventsBySport = $eventsJson ? (json_decode($eventsJson, true)['data'] ?? []) : [];

        $marketsListJson = @file_get_contents(database_path('data/marketsEventList.json'));
        $marketsListRaw = $marketsListJson ? (json_decode($marketsListJson, true) ?: []) : [];
        $realMarketsByEventId = [];
        foreach ($marketsListRaw as $mKey => $mEntry) {
            $mData = $mEntry['data'] ?? $mEntry;
            $firstMo = $mData['matchOddsData'][0] ?? $mData['bookmakersData'][0] ?? null;
            $evIdKey = !empty($firstMo['exEventId']) ? (string) $firstMo['exEventId'] : (string) $mKey;
            if (!empty($mData['matchOddsData']) || !empty($mData['bookmakersData']) || !empty($mData['fancyData']) || !empty($mData['sportsbookData'])) {
                $realMarketsByEventId[$evIdKey] = $mData;
            }
        }

        $liveScoresJson = @file_get_contents(database_path('data/liveScores.json'));
        $liveScoresByEventId = $liveScoresJson ? (json_decode($liveScoresJson, true) ?: []) : [];

        ExchangeEvent::query()->delete();

        foreach ($eventsBySport as $sportKey => $events) {
            // Keep up to 22 tennis events so DB stays super snappy while keeping ALL cricket, soccer, basketball, racing
            $slice = ($sportKey == '2') ? array_slice($events, 0, 22) : (($sportKey == '7') ? array_slice($events, 0, 18) : $events);
            foreach ($slice as $ev) {
                $eventId = (string) ($ev['exEventId'] ?? $ev['_id'] ?? Str::random(12));
                $eventName = $ev['eventName'] ?? 'Match';
                $sportId = (string) ($ev['sportId'] ?? $sportKey);
                $sportName = $ev['sportName'] ?? 'Cricket';
                $inPlay = !empty($ev['oddsData']['inPlay']);
                $preBet = !empty($ev['oddsData']['preBet']);
                $runnersData = $ev['runnersData'] ?? [];
                $rawRunners = $ev['oddsData']['runners'] ?? [];

                $normalizedRunners = [];
                foreach ($rawRunners as $idx => $r) {
                    $selId = (string) ($r['selectionId'] ?? ($idx + 1));
                    $rName = $runnersData[$selId] ?? ($idx === 0 ? explode(' v ', $eventName)[0] : ($idx === 1 ? (explode(' v ', $eventName)[1] ?? 'Runner 2') : 'The Draw'));
                    $backs = $r['price']['back'] ?? [];
                    $lays = $r['price']['lay'] ?? [];
                    for ($i = 0; $i < 3; $i++) {
                        if (!isset($backs[$i])) {
                            $backs[$i] = ['price' => 0, 'size' => 0];
                        }
                        if (!isset($lays[$i])) {
                            $lays[$i] = ['price' => 0, 'size' => 0];
                        }
                    }
                    $normalizedRunners[] = [
                        'selectionId' => $selId,
                        'runnerName' => trim($rName),
                        'status' => $r['status'] ?? 'ACTIVE',
                        'back' => $backs,
                        'lay' => $lays,
                    ];
                }

                if (empty($normalizedRunners)) {
                    $parts = explode(' v ', $eventName);
                    $normalizedRunners = [
                        [
                            'selectionId' => '101',
                            'runnerName' => trim($parts[0] ?? 'Team A'),
                            'status' => 'SUSPENDED',
                            'back' => [['price' => 0, 'size' => 0], ['price' => 0, 'size' => 0], ['price' => 0, 'size' => 0]],
                            'lay' => [['price' => 0, 'size' => 0], ['price' => 0, 'size' => 0], ['price' => 0, 'size' => 0]],
                        ],
                        [
                            'selectionId' => '102',
                            'runnerName' => trim($parts[1] ?? 'Team B'),
                            'status' => 'SUSPENDED',
                            'back' => [['price' => 0, 'size' => 0], ['price' => 0, 'size' => 0], ['price' => 0, 'size' => 0]],
                            'lay' => [['price' => 0, 'size' => 0], ['price' => 0, 'size' => 0], ['price' => 0, 'size' => 0]],
                        ],
                    ];
                }

                $bookmaker = $this->buildBookmakerMarket($normalizedRunners, $inPlay);
                $fancy = (!empty($ev['isFancy'])) ? $this->buildFancyMarkets($eventName, $normalizedRunners) : [];
                $sportsbook = (!empty($ev['isSportsbook'])) ? $this->buildSportsbookMarkets($eventName, $normalizedRunners, $sportId) : [];
                $score = $this->buildLiveScore($eventName, $normalizedRunners, $sportId, $inPlay);
                $score['preBet'] = $preBet;
                if (isset($realMarketsByEventId[$eventId])) {
                    $score['liveMarketsPayload'] = $realMarketsByEventId[$eventId];
                }
                if (isset($liveScoresByEventId[$eventId])) {
                    $score['universeScore'] = $liveScoresByEventId[$eventId];
                }

                ExchangeEvent::updateOrCreate(
                    ['event_id' => $eventId],
                    [
                        'market_id' => (string) ($ev['marketId'] ?? ('1.' . substr(abs(crc32($eventId)), 0, 8))),
                        'market_name' => $ev['marketName'] ?? 'Match Odds',
                        'sport_id' => $sportId,
                        'sport_name' => $sportName,
                        'tournament_id' => (string) ($ev['tournamentId'] ?? '1001'),
                        'tournament_name' => $ev['tournamentName'] ?? ($sportName . ' League'),
                        'event_name' => $eventName,
                        'event_time' => $ev['eventTime'] ?? now()->addHours(2)->toIso8601String(),
                        'in_play' => $inPlay,
                        'status' => $ev['oddsData']['status'] ?? 'OPEN',
                        'is_fancy' => !empty($ev['isFancy']),
                        'is_bookmakers' => !empty($ev['isBookmakers']),
                        'is_sportsbook' => !empty($ev['isSportsbook']),
                        'is_streaming' => !empty($ev['isStreaming']) || $inPlay,
                        'is_virtual' => !empty($ev['isVirtual']) || $eventId === '3544687543453' || str_contains(strtolower($eventName), 'ball by ball') || str_contains(strtolower($eventName), 'virtual'),
                        'total_matched' => (float) ($ev['oddsData']['totalMatched'] ?? rand(150000, 14500000)),
                        'runners_json' => $normalizedRunners,
                        'bookmaker_json' => $bookmaker,
                        'fancy_json' => $fancy,
                        'sportsbook_json' => $sportsbook,
                        'score_json' => $score,
                    ]
                );
            }
        }

        // Add featured In-Play Soccer & Lottery & Politics events so every tab has rich live action
        $extraEvents = [
            [
                'event_id' => '10020269901',
                'sport_id' => '1',
                'sport_name' => 'Soccer',
                'tournament_name' => 'English Premier League',
                'event_name' => 'Arsenal v Manchester City',
                'in_play' => true,
                'r1' => ['Arsenal', 2.24, 2.26],
                'r2' => ['Manchester City', 3.15, 3.20],
                'draw' => ['The Draw', 3.45, 3.50],
            ],
            [
                'event_id' => '10020269902',
                'sport_id' => '1',
                'sport_name' => 'Soccer',
                'tournament_name' => 'UEFA Champions League',
                'event_name' => 'Real Madrid v Bayern Munich',
                'in_play' => true,
                'r1' => ['Real Madrid', 1.92, 1.94],
                'r2' => ['Bayern Munich', 3.85, 3.95],
                'draw' => ['The Draw', 3.70, 3.80],
            ],
            [
                'event_id' => '10020269903',
                'sport_id' => '1',
                'sport_name' => 'Soccer',
                'tournament_name' => 'Spanish La Liga',
                'event_name' => 'Barcelona v Atletico Madrid',
                'in_play' => false,
                'r1' => ['Barcelona', 1.76, 1.78],
                'r2' => ['Atletico Madrid', 4.60, 4.70],
                'draw' => ['The Draw', 3.90, 4.00],
            ],
            [
                'event_id' => '66104001',
                'sport_id' => '66104',
                'sport_name' => 'Lottery',
                'tournament_name' => 'Indian Matka & Daily Lottery',
                'event_name' => 'Kalyan Main Bazar Night Lottery',
                'in_play' => true,
                'r1' => ['Ank Open (0-4)', 1.95, 2.00],
                'r2' => ['Ank Close (5-9)', 1.95, 2.00],
                'draw' => ['Jodi Special', 9.50, 10.00],
            ],
            [
                'event_id' => '66104002',
                'sport_id' => '66104',
                'sport_name' => 'Lottery',
                'tournament_name' => 'Indian Matka & Daily Lottery',
                'event_name' => 'Milan Day Matka Official',
                'in_play' => true,
                'r1' => ['Single Ank Low', 1.96, 2.02],
                'r2' => ['Single Ank High', 1.96, 2.02],
                'draw' => ['Pana Jackpot', 11.00, 12.00],
            ],
            [
                'event_id' => '237896101',
                'sport_id' => '2378961',
                'sport_name' => 'Politics',
                'tournament_name' => 'General Elections Special',
                'event_name' => 'Next Assembly Majority Winner',
                'in_play' => true,
                'r1' => ['Ruling Alliance', 1.62, 1.65],
                'r2' => ['Opposition Front', 2.45, 2.52],
                'draw' => ['Hung Assembly', 12.00, 14.00],
            ],
        ];

        foreach ($extraEvents as $ex) {
            $runners = [
                [
                    'selectionId' => '501',
                    'runnerName' => $ex['r1'][0],
                    'status' => 'ACTIVE',
                    'back' => [['price' => $ex['r1'][1], 'size' => 8400], ['price' => round($ex['r1'][1] - 0.02, 2), 'size' => 5200], ['price' => round($ex['r1'][1] - 0.04, 2), 'size' => 9100]],
                    'lay' => [['price' => $ex['r1'][2], 'size' => 7600], ['price' => round($ex['r1'][2] + 0.02, 2), 'size' => 6400], ['price' => round($ex['r1'][2] + 0.04, 2), 'size' => 11200]],
                ],
                [
                    'selectionId' => '502',
                    'runnerName' => $ex['r2'][0],
                    'status' => 'ACTIVE',
                    'back' => [['price' => $ex['r2'][1], 'size' => 6300], ['price' => round($ex['r2'][1] - 0.02, 2), 'size' => 4100], ['price' => round($ex['r2'][1] - 0.05, 2), 'size' => 7500]],
                    'lay' => [['price' => $ex['r2'][2], 'size' => 5900], ['price' => round($ex['r2'][2] + 0.03, 2), 'size' => 4800], ['price' => round($ex['r2'][2] + 0.06, 2), 'size' => 8300]],
                ],
                [
                    'selectionId' => '503',
                    'runnerName' => $ex['draw'][0],
                    'status' => 'ACTIVE',
                    'back' => [['price' => $ex['draw'][1], 'size' => 3200], ['price' => round($ex['draw'][1] - 0.05, 2), 'size' => 2800], ['price' => round($ex['draw'][1] - 0.10, 2), 'size' => 4500]],
                    'lay' => [['price' => $ex['draw'][2], 'size' => 3100], ['price' => round($ex['draw'][2] + 0.05, 2), 'size' => 2900], ['price' => round($ex['draw'][2] + 0.10, 2), 'size' => 5100]],
                ],
            ];
            ExchangeEvent::updateOrCreate(
                ['event_id' => $ex['event_id']],
                [
                    'market_id' => '1.' . substr(abs(crc32($ex['event_id'])), 0, 8),
                    'market_name' => 'Match Odds',
                    'sport_id' => $ex['sport_id'],
                    'sport_name' => $ex['sport_name'],
                    'tournament_id' => '9001',
                    'tournament_name' => $ex['tournament_name'],
                    'event_name' => $ex['event_name'],
                    'event_time' => now()->format('Y-m-d H:i:s'),
                    'in_play' => $ex['in_play'],
                    'status' => 'OPEN',
                    'is_fancy' => true,
                    'is_bookmakers' => true,
                    'is_sportsbook' => true,
                    'is_streaming' => true,
                    'is_virtual' => false,
                    'total_matched' => rand(850000, 9500000),
                    'runners_json' => $runners,
                    'bookmaker_json' => $this->buildBookmakerMarket($runners, $ex['in_play']),
                    'fancy_json' => $this->buildFancyMarkets($ex['event_name'], $runners),
                    'sportsbook_json' => $this->buildSportsbookMarkets($ex['event_name'], $runners, $ex['sport_id']),
                    'score_json' => $this->buildLiveScore($ex['event_name'], $runners, $ex['sport_id'], $ex['in_play']),
                ]
            );
        }

        // 5. Seed All Casino Games (194 from casinoEvents.json + Our Casino virtual/live tables)
        $casinoJson = @file_get_contents(database_path('data/casinoEvents.json'));
        $lobby = $casinoJson ? (json_decode($casinoJson, true)['data']['lobby'] ?? []) : [];

        foreach ($lobby as $idx => $item) {
            $eventId = (string) ($item['eventId'] ?? ('cg_' . $idx));
            $eventName = trim($item['eventName'] ?? $eventId);
            if (empty($eventName)) {
                continue;
            }
            $menuId = (string) ($item['menuId'] ?? '6');
            $menuName = $item['menuName'] ?? 'Universe Live';
            if ($menuName === 'SLOT GAMES') {
                $menuName = 'Slot';
                $menuId = '946';
            }

            $safeId = preg_replace('/[^a-zA-Z0-9_-]/', '_', $eventId);
            $localImg = null;
            foreach (['webp', 'png', 'gif', 'jpg'] as $ext) {
                $candidate = "/assets/casino/{$safeId}.{$ext}";
                if (file_exists(public_path(ltrim($candidate, '/')))) {
                    $localImg = $candidate;
                    break;
                }
            }

            $gameType = $this->detectCasinoGameType($eventId, $eventName, $menuName);
            $categoryTab = $this->detectCategoryTab($menuName, $item['companyName'] ?? '', $item['groupEventName'] ?? '');

            CasinoGame::updateOrCreate(
                ['event_id' => $eventId],
                [
                    'event_name' => $eventName,
                    'slug' => Str::slug($eventName . '-' . $eventId),
                    'menu_id' => $menuId,
                    'menu_name' => $menuName,
                    'category_tab' => $categoryTab,
                    'company_name' => $item['companyName'] ?? 'UNIVERSE',
                    'group_event_name' => $item['groupEventName'] ?? null,
                    'room' => $item['room'] ?? 'asian',
                    'game_type' => $gameType,
                    'image_url' => $item['url'] ?: '/assets/casino/99_0027.webp',
                    'local_image' => $localImg ?: '/assets/casino/99_0027.webp',
                    'popular' => (bool) ($item['popular'] ?? false),
                    'sequence' => (int) ($item['sequence'] ?? ($idx + 1)),
                    'min_bet' => 10,
                    'max_bet' => 100000,
                    'rtp' => 97.20,
                    'status' => true,
                    'config_json' => $this->buildCasinoGameConfig($gameType, $eventName),
                ]
            );
        }

        // Also seed the additional "OUR CASINO" / "OUR VIRTUAL" tables seen on /newCasino (screenshot 26)
        $ourCasinoExtras = [
            ['id' => 'oc_point_tp', 'name' => 'POINT TEEN PATTI', 'type' => 'teenpatti', 'tab' => 'OUR CASINO', 'img' => '/assets/casino/99_0010.webp'],
            ['id' => 'oc_dtl_tp', 'name' => 'DTL TEENPATTI', 'type' => 'teenpatti', 'tab' => 'OUR CASINO', 'img' => '/assets/casino/99_0013.webp'],
            ['id' => 'oc_poker_a', 'name' => '20-20 POKER - A', 'type' => 'poker', 'tab' => 'OUR CASINO', 'img' => '/assets/casino/99_0007.webp'],
            ['id' => 'oc_poker_b', 'name' => '20-20 POKER - B', 'type' => 'poker', 'tab' => 'OUR CASINO', 'img' => '/assets/casino/99_0007.webp'],
            ['id' => 'oc_29_bacc', 'name' => '29 CARD BACCARAT', 'type' => 'baccarat', 'tab' => 'OUR CASINO', 'img' => '/assets/casino/99_0001.webp'],
            ['id' => 'oc_bacc_a', 'name' => 'BACCARAT - A', 'type' => 'baccarat', 'tab' => 'OUR CASINO', 'img' => '/assets/casino/99_0001.webp'],
            ['id' => 'oc_bacc_c', 'name' => 'BACCARAT - C', 'type' => 'baccarat', 'tab' => 'OUR CASINO', 'img' => '/assets/casino/99_0001.webp'],
            ['id' => 'oc_fast_dt', 'name' => 'FAST DRAGON TIGER', 'type' => 'dragontiger', 'tab' => 'OUR CASINO', 'img' => '/assets/casino/99_0019.webp'],
            ['id' => 'oc_dtl_a', 'name' => 'DTL - A', 'type' => 'dtl', 'tab' => 'OUR CASINO', 'img' => '/assets/casino/99_0041.webp'],
            ['id' => 'oc_32cards_a', 'name' => '32 CARDS - A', 'type' => 'cards32', 'tab' => 'OUR CASINO', 'img' => '/assets/casino/99_0022.webp'],
            ['id' => 'oc_32cards_b', 'name' => '32 CARDS - B', 'type' => 'cards32', 'tab' => 'OUR CASINO', 'img' => '/assets/casino/99_0022.webp'],
            ['id' => 'oc_ab_a', 'name' => 'ANDAR BAHAR - A', 'type' => 'andarbahar', 'tab' => 'OUR CASINO', 'img' => '/assets/casino/99_0025.webp'],
            ['id' => 'oc_ab_c', 'name' => 'ANDAR BAHAR - C', 'type' => 'andarbahar', 'tab' => 'OUR CASINO', 'img' => '/assets/casino/99_0025.webp'],
            ['id' => 'oc_lucky7_a', 'name' => 'LUCKY 7 - A', 'type' => 'lucky7', 'tab' => 'OUR CASINO', 'img' => '/assets/casino/99_0030.webp'],
            ['id' => 'oc_fast_lucky7', 'name' => 'FAST LUCKY - 7', 'type' => 'lucky7', 'tab' => 'OUR VIRTUAL', 'img' => '/assets/casino/99_0030.webp'],
            ['id' => 'oc_lucky7_b', 'name' => 'LUCKY 7 - B', 'type' => 'lucky7', 'tab' => 'OUR CASINO', 'img' => '/assets/casino/99_0030.webp'],
            ['id' => 'oc_worli', 'name' => 'INSTANT WORLI', 'type' => 'worli', 'tab' => 'OUR CASINO', 'img' => '/assets/casino/88_0019.webp'],
            ['id' => 'oc_bollywood', 'name' => 'BOLLYWOOD CASINO', 'type' => 'bollywood', 'tab' => 'OUR CASINO', 'img' => '/assets/casino/99_0005.webp'],
            ['id' => 'oc_inaminadika', 'name' => 'INA MINA DIKA', 'type' => 'bollywood', 'tab' => 'OUR VIRTUAL', 'img' => '/assets/casino/99_0005.webp'],
            ['id' => 'oc_1card_meter', 'name' => '1 CARD METER', 'type' => 'meter', 'tab' => 'OUR VIRTUAL', 'img' => '/assets/casino/99_0040.webp'],
            ['id' => 'oc_1card_2020', 'name' => '1 CARD 20-20', 'type' => 'meter', 'tab' => 'OUR VIRTUAL', 'img' => '/assets/casino/99_0040.webp'],
            ['id' => 'oc_trio', 'name' => 'TRIO', 'type' => 'teenpatti', 'tab' => 'OUR CASINO', 'img' => '/assets/casino/99_0016.webp'],
            ['id' => 'oc_kbc', 'name' => 'KBC', 'type' => 'kbc', 'tab' => 'OUR CASINO', 'img' => '/assets/casino/88_0020.png'],
            ['id' => 'oc_casino_meter', 'name' => 'CASINO METER', 'type' => 'meter', 'tab' => 'OUR CASINO', 'img' => '/assets/casino/99_0027.webp'],
        ];

        foreach ($ourCasinoExtras as $idx => $oc) {
            CasinoGame::updateOrCreate(
                ['event_id' => $oc['id']],
                [
                    'event_name' => $oc['name'],
                    'slug' => Str::slug($oc['name']),
                    'menu_id' => '6',
                    'menu_name' => 'Universe Live',
                    'category_tab' => $oc['tab'],
                    'company_name' => 'UNIVERSE',
                    'group_event_name' => 'ourcasino',
                    'room' => 'asian',
                    'game_type' => $oc['type'],
                    'image_url' => $oc['img'],
                    'local_image' => $oc['img'],
                    'popular' => false,
                    'sequence' => 30 + $idx,
                    'min_bet' => 10,
                    'max_bet' => 100000,
                    'rtp' => 97.50,
                    'status' => true,
                    'config_json' => $this->buildCasinoGameConfig($oc['type'], $oc['name']),
                ]
            );
        }
    }

    private function buildBookmakerMarket(array $runners, bool $inPlay): array
    {
        $bmRunners = [];
        foreach ($runners as $idx => $r) {
            $baseBack = (float) ($r['back'][0]['price'] ?? 0);
            $bmBack = $baseBack > 1 ? round(($baseBack - 1) * 100) : ($idx === 0 ? 85 : ($idx === 1 ? 110 : 350));
            $bmLay = $bmBack + 3;
            $bmRunners[] = [
                'selectionId' => 'bm_' . $r['selectionId'],
                'runnerName' => $r['runnerName'],
                'status' => ($baseBack > 0 || $inPlay) ? 'ACTIVE' : 'SUSPENDED',
                'back' => [
                    ['price' => $bmBack, 'size' => '5M'],
                    ['price' => max(1, $bmBack - 1), 'size' => '10M'],
                    ['price' => max(1, $bmBack - 2), 'size' => '15M'],
                ],
                'lay' => [
                    ['price' => $bmLay, 'size' => '5M'],
                    ['price' => $bmLay + 1, 'size' => '10M'],
                    ['price' => $bmLay + 2, 'size' => '15M'],
                ],
            ];
        }
        return [
            'marketName' => 'Bookmaker',
            'min' => 10,
            'max' => 500000,
            'matched' => '1.2M',
            'runners' => $bmRunners,
        ];
    }

    private function buildFancyMarkets(string $eventName, array $runners): array
    {
        $team1 = $runners[0]['runnerName'] ?? 'Team A';
        $short1 = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $team1), 0, 3) ?: 'TM1');
        return [
            [
                'fancyId' => 'f_1',
                'runnerName' => "{$short1} Only 6th Over Runs",
                'category' => 'Fancy',
                'noRun' => 7,
                'noRate' => 100,
                'yesRun' => 8,
                'yesRate' => 100,
                'min' => 10,
                'max' => 25000,
                'status' => 'ACTIVE',
            ],
            [
                'fancyId' => 'f_2',
                'runnerName' => "{$short1} 10 Over Runs",
                'category' => 'Fancy',
                'noRun' => 74,
                'noRate' => 100,
                'yesRun' => 76,
                'yesRate' => 100,
                'min' => 10,
                'max' => 200000,
                'status' => 'ACTIVE',
            ],
            [
                'fancyId' => 'f_3',
                'runnerName' => "{$short1} 20 Over Total Runs",
                'category' => 'Line Markets',
                'noRun' => 168,
                'noRate' => 100,
                'yesRun' => 170,
                'yesRate' => 95,
                'min' => 10,
                'max' => 200000,
                'status' => 'ACTIVE',
            ],
            [
                'fancyId' => 'f_4',
                'runnerName' => "Fall Of Next Wicket {$short1}",
                'category' => 'Fancy',
                'noRun' => 45,
                'noRate' => 110,
                'yesRun' => 45,
                'yesRate' => 90,
                'min' => 10,
                'max' => 50000,
                'status' => 'ACTIVE',
            ],
            [
                'fancyId' => 'f_5',
                'runnerName' => "Next Ball Runs ({$short1})",
                'category' => 'Ball by Ball',
                'noRun' => 1,
                'noRate' => 110,
                'yesRun' => 2,
                'yesRate' => 90,
                'min' => 10,
                'max' => 25000,
                'status' => 'ACTIVE',
            ],
            [
                'fancyId' => 'f_6',
                'runnerName' => "{$short1} Total Match Boundaries",
                'category' => 'Meter Markets',
                'noRun' => 21,
                'noRate' => 100,
                'yesRun' => 22,
                'yesRate' => 100,
                'min' => 10,
                'max' => 50000,
                'status' => 'ACTIVE',
            ],
            [
                'fancyId' => 'f_7',
                'runnerName' => "Top Opening Batsman Runs ({$short1})",
                'category' => 'Khado Markets',
                'noRun' => 38,
                'noRate' => 110,
                'yesRun' => 39,
                'yesRate' => 90,
                'min' => 10,
                'max' => 50000,
                'status' => 'ACTIVE',
            ],
        ];
    }

    private function buildSportsbookMarkets(string $eventName, array $runners, string $sportId): array
    {
        $r1 = $runners[0]['runnerName'] ?? 'Team A';
        $r2 = $runners[1]['runnerName'] ?? 'Team B';
        return [
            [
                'marketId' => 'sb_toss',
                'marketName' => $sportId === '4' ? 'Who Will Win The Toss' : 'First Team To Score',
                'runners' => [
                    ['selectionId' => 'sb_1', 'runnerName' => $r1, 'odds' => 1.95],
                    ['selectionId' => 'sb_2', 'runnerName' => $r2, 'odds' => 1.95],
                ],
            ],
            [
                'marketId' => 'sb_high',
                'marketName' => $sportId === '4' ? 'Highest First 6 Overs Score' : 'Both Teams To Score',
                'runners' => [
                    ['selectionId' => 'sb_3', 'runnerName' => $sportId === '4' ? $r1 : 'Yes', 'odds' => 1.88],
                    ['selectionId' => 'sb_4', 'runnerName' => $sportId === '4' ? $r2 : 'No', 'odds' => 1.92],
                ],
            ],
        ];
    }

    private function buildLiveScore(string $eventName, array $runners, string $sportId, bool $inPlay): array
    {
        $r1 = $runners[0]['runnerName'] ?? 'Team 1';
        $r2 = $runners[1]['runnerName'] ?? 'Team 2';
        if ($sportId === '4') {
            return [
                'team1' => $r1,
                'team2' => $r2,
                'score1' => $inPlay ? '154/3 (16.4 ov)' : '0/0 (0.0 ov)',
                'score2' => $inPlay ? 'Yet to bat' : '0/0 (0.0 ov)',
                'crr' => $inPlay ? '9.24' : '0.00',
                'recentBalls' => ['1', '4', '0', '6', '1', '2'],
                'statusText' => $inPlay ? "{$r1} elected to bat first" : 'Match starts soon',
            ];
        }
        if ($sportId === '1') {
            return [
                'team1' => $r1,
                'team2' => $r2,
                'score1' => $inPlay ? '1' : '0',
                'score2' => $inPlay ? '1' : '0',
                'time' => $inPlay ? "64'" : "00'",
                'statusText' => $inPlay ? '2nd Half - Live' : 'Upcoming Kickoff',
            ];
        }
        return [
            'team1' => $r1,
            'team2' => $r2,
            'score1' => $inPlay ? '6-4, 3-2' : '0-0',
            'score2' => $inPlay ? '4-6, 2-3' : '0-0',
            'statusText' => $inPlay ? 'Set 2 - Live Serve' : 'Scheduled',
        ];
    }

    private function detectCasinoGameType(string $id, string $name, string $menuName): string
    {
        $u = strtoupper($name . ' ' . $id);
        if (str_contains($u, 'VIMAAN') || str_contains($u, '88.0022') || str_contains($u, 'AVIATOR')) return 'crash';
        if (str_contains($u, 'BALLOON') || str_contains($u, '88.0023')) return 'balloon';
        if (str_contains($u, 'HEADS') || str_contains($u, '88.0021')) return 'coinflip';
        if (str_contains($u, 'DREAM CATCHER') || str_contains($u, '88.0020') || str_contains($u, 'GAME_SHOW')) return 'wheel';
        if (str_contains($u, 'LUCKY 0 TO 9') || str_contains($u, '88.0019') || str_contains($u, 'LOTTERY')) return 'lucky09';
        if (str_contains($u, 'TEEN') || str_contains($u, 'MUFLIS')) return 'teenpatti';
        if (str_contains($u, 'LUCKY 7') || str_contains($u, 'LUCKY-7') || str_contains($u, '99.0030')) return 'lucky7';
        if (str_contains($u, 'DTL') || str_contains($u, '99.0041')) return 'dtl';
        if (str_contains($u, 'DRAGON') || str_contains($u, 'DRAGONTIGER')) return 'dragontiger';
        if (str_contains($u, 'BACCARAT')) return 'baccarat';
        if (str_contains($u, 'ANDAR')) return 'andarbahar';
        if (str_contains($u, '32 CARD')) return 'cards32';
        if (str_contains($u, 'POKER')) return 'poker';
        if (str_contains($u, 'AMAR AKBAR') || str_contains($u, '99.0005')) return 'aaa';
        if (str_contains($u, 'JUDGEMENT')) return 'judgement';
        if (str_contains($u, 'CASINO WAR') || str_contains($u, '99.0027')) return 'casinowar';
        if (str_contains($u, 'CARD RACE') || str_contains($u, '99.0046')) return 'cardrace';
        if (str_contains($u, 'ROULETTE')) return 'roulette';
        if (str_contains($u, 'BLACKJACK')) return 'blackjack';
        if (str_contains($u, 'SICBO')) return 'sicbo';
        if (str_contains($u, 'FANTAN')) return 'fantan';
        if ($menuName === 'Slot' || is_numeric($id)) return 'slot';
        return 'baccarat';
    }

    private function detectCategoryTab(string $menuName, string $company, string $group): string
    {
        $g = strtolower($group . ' ' . $company);
        if ($menuName === 'Slot' || str_contains($g, 'platin') || str_contains($g, 'kagaming') || str_contains($g, 'iconix')) {
            return 'PLATINGAMES';
        }
        if (str_contains($g, 'evolution')) return 'EVOLUTION';
        if (str_contains($g, 'ezugi')) return 'EZUGI';
        if (str_contains($g, 'tvbet')) return 'TVBET';
        if (str_contains($g, 'betgame')) return 'BETGAME';
        if ($menuName === 'Universe Original') return 'OUR VIRTUAL';
        return 'OUR CASINO';
    }

    private function buildCasinoGameConfig(string $gameType, string $eventName): array
    {
        return [
            'title' => $eventName,
            'gameType' => $gameType,
            'roundDuration' => 20,
        ];
    }
}
