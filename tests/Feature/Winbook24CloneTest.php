<?php

namespace Tests\Feature;

use App\Models\CasinoGame;
use App\Models\ExchangeEvent;
use App\Models\OtpVerification;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\SportmonksService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Winbook24CloneTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_home_and_public_pages_render_cleanly(): void
    {
        $this->get('/home')->assertStatus(200)->assertSee('Highlights');
        $this->get('/inplay')->assertStatus(200)->assertSee('In-Play');
        $this->get('/livecasino')->assertStatus(200)->assertSee('Universe Live');
        $this->get('/newCasino')->assertStatus(200)->assertSee('OUR CASINO');
        $this->get('/login')->assertStatus(200)->assertSee('Login with Demo ID');
        $this->get('/signup')->assertStatus(200)->assertSee('Generate OTP');
        $this->get('/forgetPassword')->assertStatus(200)->assertSee('Reset Password');
    }

    public function test_msg91_otp_signup_and_verification_flow(): void
    {
        $phone = '9811' . random_int(100000, 999999);
        $username = 'testwb' . random_int(1000, 9999);

        $otpRes = $this->postJson('/auth/signup/send-otp', [
            'username' => $username,
            'phone' => $phone,
            'country_code' => '+91',
        ]);

        $otpRes->assertStatus(200)->assertJson(['status' => true]);
        $otpRecord = OtpVerification::where('phone', $phone)->latest()->first();
        $this->assertNotNull($otpRecord);

        $verifyRes = $this->postJson('/auth/signup/verify', [
            'username' => $username,
            'phone' => $phone,
            'country_code' => '+91',
            'otp' => $otpRecord->otp_code,
            'password' => 'Test@1234',
            'promo_code' => 'WB24ADMIN',
        ]);

        $verifyRes->assertStatus(200)->assertJson(['status' => true]);
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['username' => $username, 'phone' => $phone]);
    }

    public function test_exchange_bet_placement_and_cashout(): void
    {
        $user = User::where('username', 'player1')->firstOrFail();
        $event = ExchangeEvent::firstOrFail();

        $res = $this->actingAs($user)->postJson('/api/exchange/place-bet', [
            'event_id' => $event->event_id,
            'event_name' => $event->event_name,
            'market_name' => 'Match Odds',
            'selection_name' => 'Team A',
            'bet_type' => 'BACK',
            'odds' => 2.00,
            'stake' => 100,
            'category' => 'exchange',
        ]);

        $res->assertStatus(200)->assertJson(['status' => true]);
        $betId = $res->json('bet.id');

        $cashout = $this->actingAs($user)->postJson('/api/exchange/cashout-bet', [
            'bet_id' => $betId,
            'mode' => 'instant_settle',
        ]);

        $cashout->assertStatus(200)->assertJson(['status' => true]);
    }

    public function test_casino_games_play_and_settle_in_sqlite(): void
    {
        $user = User::where('username', 'demo')->firstOrFail();
        $user->update(['balance' => 10000.00, 'exposure' => 0.00]);

        foreach (['88.0022', '99.0010', '99.0030', '99.0025', 'wc-roulette', '4'] as $eventId) {
            $game = CasinoGame::where('event_id', $eventId)->first();
            if (!$game) continue;

            $this->get('/casino/play/' . $eventId)->assertStatus(200);

            $playRes = $this->actingAs($user)->postJson('/api/casino/play-round', [
                'event_id' => $eventId,
                'selection_key' => 'PLAYER_A',
                'stake' => 50,
                'side' => 'BACK',
            ]);

            $playRes->assertStatus(200)->assertJson(['status' => true]);
        }
    }

    public function test_banking_deposit_with_laravel_storage_receipt(): void
    {
        Storage::fake('public');
        $user = User::where('username', 'player1')->firstOrFail();

        $file = UploadedFile::fake()->create('utr_receipt.jpg', 120, 'image/jpeg');

        $res = $this->actingAs($user)->post('/banking/deposit', [
            'amount' => 1500,
            'method' => 'UPI',
            'utr_number' => '412398765432',
            'receipt' => $file,
            'instant_credit' => 1,
        ]);

        $res->assertRedirect();
        $this->assertDatabaseHas('banking_transactions', [
            'user_id' => $user->id,
            'utr_number' => '412398765432',
            'status' => 'APPROVED',
        ]);
    }

    public function test_vimaan_dedicated_game_and_real_time_cashout_flow(): void
    {
        $user = User::where('username', 'player1')->firstOrFail();
        $initialBalance = (float) $user->balance;

        // 1. Open dedicated VIMAAN page
        $pageRes = $this->actingAs($user)->get('/vimaan');
        $pageRes->assertStatus(200);
        $pageRes->assertSee('vimaanCanvas');
        $pageRes->assertSee('Provably Fair');
        $pageRes->assertSee('Powered by');

        // 2. Place a VIMAAN bet on Slot 1
        $placeRes = $this->actingAs($user)->postJson('/api/vimaan/place-bet', [
            'stake' => 250,
            'slot' => 1,
        ]);
        $placeRes->assertStatus(200)->assertJson(['status' => true]);
        $betId = $placeRes->json('bet.id');

        $user->refresh();
        $this->assertEquals($initialBalance - 250, (float) $user->available_balance);

        // 3. Cash out at 2.75x (payout 687.50, net profit +437.50)
        $cashoutRes = $this->actingAs($user)->postJson('/api/vimaan/cashout', [
            'bet_id' => $betId,
            'multiplier' => 2.75,
        ]);
        $cashoutRes->assertStatus(200)->assertJsonPath('cashout.multiplier', 2.75);
        $cashoutRes->assertJsonPath('cashout.payout', 687.5);

        $user->refresh();
        $this->assertEquals($initialBalance + 437.5, (float) $user->balance);

        // 4. Place another VIMAAN bet and test crash
        $placeRes2 = $this->actingAs($user)->postJson('/api/vimaan/place-bet', [
            'stake' => 100,
            'slot' => 2,
        ]);
        $placeRes2->assertStatus(200)->assertJson(['status' => true]);
        $betId2 = $placeRes2->json('bet.id');

        $crashRes = $this->actingAs($user)->postJson('/api/vimaan/crash', [
            'bet_ids' => [$betId2],
            'crash_multiplier' => 1.49,
        ]);
        $crashRes->assertStatus(200)->assertJson(['status' => true]);
    }

    public function test_teenpatti_2020_dedicated_game_and_settlement_flow(): void
    {
        $user = User::where('username', 'player1')->firstOrFail();
        $initialBalance = (float) $user->balance;

        // 1. Open dedicated 20-20 TEENPATTI page (/casino3Patti/99.0010)
        $pageRes = $this->actingAs($user)->get('/casino3Patti/99.0010');
        $pageRes->assertStatus(200);
        $pageRes->assertSee('PAIR ( DUBBLE ) 1:4');
        $pageRes->assertSee('LOVE MARRIAGE ( Q &amp; K WITH SAME SUIT ) 1:25', false);
        $pageRes->assertSee('/assets/casino/loader.gif');

        // 2. Place a bet on WINNER -> PLAYER A (1.98)
        $placeRes = $this->actingAs($user)->postJson('/api/casino/teenpatti/place-bet', [
            'event_id' => '99.0010',
            'round_id' => '9101011079999',
            'market_key' => 'WINNER',
            'market_name' => 'WINNER',
            'selection_key' => 'PLAYER_A',
            'selection_name' => 'PLAYER A',
            'odds' => 1.98,
            'stake' => 100,
        ]);
        $placeRes->assertStatus(200)->assertJson(['status' => true]);

        $user->refresh();
        $this->assertEquals($initialBalance - 100, (float) $user->available_balance);

        // 3. Settle the round and verify 6 cards are dealt and exposure is released
        $settleRes = $this->actingAs($user)->postJson('/api/casino/teenpatti/settle-round', [
            'event_id' => '99.0010',
            'round_id' => '9101011079999',
        ]);
        $settleRes->assertStatus(200)->assertJson(['status' => true]);
        $this->assertCount(3, $settleRes->json('cards_a'));
        $this->assertCount(3, $settleRes->json('cards_b'));
        $this->assertNotEmpty($settleRes->json('settled_bets'));
    }

    public function test_lucky7_dedicated_game_and_settlement_flow(): void
    {
        $user = User::where('username', 'player1')->firstOrFail();
        $initialBalance = (float) $user->balance;

        // 1. Open dedicated LUCKY 7 page (/lucky7/99.0030)
        $pageRes = $this->actingAs($user)->get('/lucky7/99.0030');
        $pageRes->assertStatus(200);
        $pageRes->assertSee('LOW CARD ( A to 6 )');
        $pageRes->assertSee('HIGH CARD ( 8 to K )');
        $pageRes->assertSee('LUCKY ODD/EVEN');
        $pageRes->assertSee('LUCKY COLOR');
        $pageRes->assertSee('LUCKY CARD SUIT');
        $pageRes->assertSee('/assets/t10cards/A.jpeg');

        // 2. Place a bet on WINNER -> LOW CARD ( A to 6 ) (2)
        $placeRes = $this->actingAs($user)->postJson('/api/casino/teenpatti/place-bet', [
            'event_id' => '99.0030',
            'round_id' => '9301011081931',
            'market_key' => 'WINNER',
            'market_name' => 'WINNER',
            'selection_key' => 'LOW_CARD',
            'selection_name' => 'LOW CARD ( A to 6 )',
            'odds' => 2.00,
            'stake' => 100,
        ]);
        $placeRes->assertStatus(200)->assertJson(['status' => true]);

        $user->refresh();
        $this->assertEquals($initialBalance - 100, (float) $user->available_balance);

        // 3. Settle the Lucky 7 round and verify card_code and settlement
        $settleRes = $this->actingAs($user)->postJson('/api/casino/lucky7/settle-round', [
            'event_id' => '99.0030',
            'round_id' => '9301011081931',
        ]);
        $settleRes->assertStatus(200)->assertJson(['status' => true]);
        $this->assertNotEmpty($settleRes->json('card_code'));
        $this->assertNotEmpty($settleRes->json('settled_bets'));
    }

    public function test_dragontiger_2020_dedicated_game_and_settlement_flow(): void
    {
        $user = User::where('username', 'player1')->firstOrFail();
        $initialBalance = (float) $user->balance;

        // 1. Open dedicated 20-20 DRAGON TIGER page (/casino3Patti/99.0019)
        $pageRes = $this->actingAs($user)->get('/casino3Patti/99.0019');
        $pageRes->assertStatus(200);
        $pageRes->assertSee('9191011100500');
        $pageRes->assertSee('DRAGON');
        $pageRes->assertSee('TIGER');
        $pageRes->assertSee('TIE');
        $pageRes->assertSee('Min/Max: 100 - 100000');
        $pageRes->assertSee('Recent Result');

        // 2. Place a bet on WINNER -> DRAGON (2)
        $placeRes = $this->actingAs($user)->postJson('/api/casino/teenpatti/place-bet', [
            'event_id' => '99.0019',
            'round_id' => '9191011100500',
            'market_key' => 'WINNER',
            'market_name' => 'WINNER',
            'selection_key' => 'DRAGON',
            'selection_name' => 'DRAGON',
            'odds' => 2.00,
            'stake' => 100,
        ]);
        $placeRes->assertStatus(200)->assertJson(['status' => true]);

        $user->refresh();
        $this->assertEquals($initialBalance - 100, (float) $user->available_balance);

        // 3. Settle the 20-20 Dragon Tiger round and verify card_dragon, card_tiger, and settlement
        $settleRes = $this->actingAs($user)->postJson('/api/casino/dragontiger/settle-round', [
            'event_id' => '99.0019',
            'round_id' => '9191011100500',
        ]);
        $settleRes->assertStatus(200)->assertJson(['status' => true]);
        $this->assertNotEmpty($settleRes->json('card_dragon'));
        $this->assertNotEmpty($settleRes->json('card_tiger'));
        $this->assertNotEmpty($settleRes->json('settled_bets'));
    }

    public function test_dragontiger_990018_dedicated_game_and_settlement_flow(): void
    {
        $user = User::where('username', 'player1')->firstOrFail();
        $initialBalance = (float) $user->balance;

        // 1. Open dedicated DRAGON TIGER page (/casino/99.0018)
        $pageRes = $this->actingAs($user)->get('/casino/99.0018');
        $pageRes->assertStatus(200);
        $pageRes->assertSee('9181011104950');
        $pageRes->assertSee('WINNER');
        $pageRes->assertSee('DRAGON ODD/EVEN');
        $pageRes->assertSee('TIGER ODD/EVEN');
        $pageRes->assertSee('DRAGON CARD COLOR');
        $pageRes->assertSee('TIGER CARD COLOR');
        $pageRes->assertSee('DRAGON CARD');
        $pageRes->assertSee('TIGER CARD');
        $pageRes->assertSee('Min/Max: 100 - 20000');
        $pageRes->assertSee('Recent Result');

        // 2. Place a bet on DRAGON ODD/EVEN -> DRAGON ODD (1.81)
        $placeRes = $this->actingAs($user)->postJson('/api/casino/teenpatti/place-bet', [
            'event_id' => '99.0018',
            'round_id' => '9181011104950',
            'market_key' => 'DRAGON_ODD_EVEN',
            'market_name' => 'DRAGON ODD/EVEN',
            'selection_key' => 'DRAGON_ODD',
            'selection_name' => 'DRAGON ODD',
            'odds' => 1.81,
            'stake' => 100,
        ]);
        $placeRes->assertStatus(200)->assertJson(['status' => true]);

        $user->refresh();
        $this->assertEquals($initialBalance - 100, (float) $user->available_balance);

        // 3. Settle the Dragon Tiger 99.0018 round and verify card_dragon, card_tiger, and settlement
        $settleRes = $this->actingAs($user)->postJson('/api/casino/dragontiger/settle-round', [
            'event_id' => '99.0018',
            'round_id' => '9181011104950',
        ]);
        $settleRes->assertStatus(200)->assertJson(['status' => true]);
        $this->assertNotEmpty($settleRes->json('card_dragon'));
        $this->assertNotEmpty($settleRes->json('card_tiger'));
        $this->assertNotEmpty($settleRes->json('settled_bets'));
    }

    public function test_balloon_880023_dedicated_game_and_cashout_flow(): void
    {
        $user = User::where('username', 'player1')->firstOrFail();
        $initialBalance = (float) $user->balance;

        // 1. Open dedicated BALLOON page (/balloon/88.0023)
        $pageRes = $this->actingAs($user)->get('/balloon/88.0023');
        $pageRes->assertStatus(200);
        $pageRes->assertSee('balloonCanvas');
        $pageRes->assertSee('/assets/balloon/heat-icon.webp');
        $pageRes->assertSee('Waiting For Next Round');
        $pageRes->assertSee('Edits');
        $pageRes->assertSee('Clear');
        $pageRes->assertSee('HEAT');

        // 2. Place a BALLOON bet on Slot 1 (stake 100)
        $placeRes = $this->actingAs($user)->postJson('/api/balloon/place-bet', [
            'event_id' => '88.0023',
            'event_name' => 'BALLOON',
            'stake' => 100,
            'slot' => 1,
            'round_id' => 'BLN999888',
        ]);
        $placeRes->assertStatus(200)->assertJson(['status' => true]);
        $betId = $placeRes->json('bet.id');

        $user->refresh();
        $this->assertEquals($initialBalance - 100, (float) $user->available_balance);

        // 3. Cashout at 3.50x (payout 350, net profit +250)
        $cashoutRes = $this->actingAs($user)->postJson('/api/balloon/cashout', [
            'bet_id' => $betId,
            'multiplier' => 3.50,
        ]);
        $cashoutRes->assertStatus(200)->assertJson(['status' => true]);

        $user->refresh();
        $this->assertEquals($initialBalance + 250, (float) $user->balance);
    }

    public function test_lucky0to9_880019_dedicated_game_and_settlement_flow(): void
    {
        $user = User::where('username', 'player1')->firstOrFail();
        $initialBalance = (float) $user->balance;

        // 1. Open dedicated LUCKY 0 TO 9 page (/lucky0To9/88.0019)
        $pageRes = $this->actingAs($user)->get('/lucky0To9/88.0019');
        $pageRes->assertStatus(200);
        $pageRes->assertSee('8191011114908');
        $pageRes->assertSee('luckyWheelCanvas');
        $pageRes->assertSee('/assets/lucky0to9/wheelmainbg.gif');
        $pageRes->assertSee('/assets/lucky0to9/wheelbg.png');
        $pageRes->assertSee('/assets/lucky0to9/chips1.svg');
        $pageRes->assertSee('/assets/lucky0to9/chips1k.svg');
        $pageRes->assertSee('EVEN');
        $pageRes->assertSee('RED');
        $pageRes->assertSee('BLACK');
        $pageRes->assertSee('ODD');
        $pageRes->assertSee('Recent Result');

        // 2. Place a bet on WINNER -> 5 (odds 9.5, stake 100)
        $placeRes = $this->actingAs($user)->postJson('/api/casino/teenpatti/place-bet', [
            'event_id' => '88.0019',
            'round_id' => '8191011114908',
            'market_key' => 'WINNER',
            'market_name' => 'WINNER',
            'selection_key' => '5',
            'selection_name' => '5',
            'odds' => 9.50,
            'stake' => 100,
        ]);
        $placeRes->assertStatus(200)->assertJson(['status' => true]);

        $user->refresh();
        $this->assertEquals($initialBalance - 100, (float) $user->available_balance);

        // 3. Settle the Lucky 0 to 9 round with winner_number = 5 (net profit +850)
        $settleRes = $this->actingAs($user)->postJson('/api/casino/lucky0to9/settle-round', [
            'event_id' => '88.0019',
            'round_id' => '8191011114908',
            'winner_number' => 5,
        ]);
        $settleRes->assertStatus(200)->assertJson([
            'status' => true,
            'winner' => '5',
            'winner_num' => 5,
            'color_class' => 'redNumber',
        ]);
        $this->assertNotEmpty($settleRes->json('settled_bets'));

        $user->refresh();
        $this->assertEquals($initialBalance + 850, (float) $user->balance);
    }

    public function test_my_account_all_ten_options_and_subpages_match_winbook24(): void
    {
        $user = User::where('username', 'player1')->firstOrFail();

        $pages = [
            '/myprofile' => 'Account Details',
            '/2fa' => 'Re-generate 2-Factor Authentication',
            '/refer-and-earn' => 'Copy to Share',
            '/rolling-commission' => 'Get Commission',
            '/banking' => 'Minimum deposit Amount is 100.',
            '/banking/withdraw' => 'Withdraw 24*7',
            '/banking/withdrawdetails' => 'Withdrawal Details',
            '/banking/reward' => 'Rolling Reward',
            '/account-statement' => 'Get Statement',
            '/bet-history' => 'Get History',
            '/profit-loss' => 'Get P&amp;L',
            '/password-history' => 'Password Change History',
            '/activity-log' => 'Login Successful',
        ];

        foreach ($pages as $url => $expectedText) {
            $res = $this->actingAs($user)->get($url);
            $res->assertStatus(200);
            $res->assertSee($expectedText, false);
        }
    }

    public function test_login_portal_and_header_login_and_settings_flow(): void
    {
        // 1. Guest home page has header login inputs & 18+ age confirmation modal
        $homeRes = $this->get('/home');
        $homeRes->assertStatus(200);
        $homeRes->assertSee('wbHeaderLoginForm');
        $homeRes->assertSee('ageconfirmationModal');
        $homeRes->assertSee('Non-Gambling Territories.');

        // 2. Full-screen /login portal matches winbook24.com/login 1:1
        $loginPage = $this->get('/login');
        $loginPage->assertStatus(200);
        $loginPage->assertSee('main-Box-login');
        $loginPage->assertSee('centered-container');
        $loginPage->assertSee('Login with Demo ID');
        $loginPage->assertSee('Forgot Password?');

        // 3. AJAX Header Login with valid credentials
        $ajaxLogin = $this->postJson('/auth/login', [
            'username' => 'player1',
            'password' => 'Player@123',
        ]);
        $ajaxLogin->assertStatus(200)->assertJson([
            'status' => true,
        ]);
        $this->assertAuthenticated();

        // 4. Settings page (Theme + Quick Stakes)
        $settingsPage = $this->get('/settings');
        $settingsPage->assertStatus(200);
        $settingsPage->assertSee('Theme');
        $settingsPage->assertSee('Quick Stakes');
    }

    public function test_headntails_880021_dedicated_game_and_settlement_flow(): void
    {
        $user = User::where('username', 'player1')->firstOrFail();
        $initialBalance = (float) $user->balance;

        // 1. Verify /headNtails/88.0021 renders the 1:1 HEADS & TAILS game view
        $pageRes = $this->actingAs($user)->get('/headNtails/88.0021');
        $pageRes->assertStatus(200);
        $pageRes->assertSee('RID: 8211011115231');
        $pageRes->assertSee('/assets/headntails/heads.png');
        $pageRes->assertSee('/assets/headntails/tails.png');
        $pageRes->assertSee('/assets/headntails/head-bet.jpg');
        $pageRes->assertSee('/assets/headntails/tails-bet.jpg');
        $pageRes->assertSee('Recent Result');

        // 2. Place a 100 PTI bet on HEADS (1.90)
        $betRes = $this->actingAs($user)->postJson('/api/casino/teenpatti/place-bet', [
            'event_id' => '88.0021',
            'round_id' => '8211011115231',
            'market_key' => 'WINNER',
            'market_name' => 'WINNER',
            'selection_key' => 'HEADS',
            'selection_name' => 'HEADS',
            'odds' => 1.90,
            'stake' => 100,
        ]);
        $betRes->assertStatus(200)->assertJson(['status' => true]);

        // 3. Settle the round forcing HEADS as winner
        $settleRes = $this->actingAs($user)->postJson('/api/casino/headntails/settle-round', [
            'event_id' => '88.0021',
            'round_id' => '8211011115231',
            'forced_winner' => 'HEADS',
        ]);
        $settleRes->assertStatus(200)->assertJson([
            'status' => true,
            'winner' => 'HEADS',
            'badge' => 'H',
        ]);

        $user->refresh();
        $this->assertEquals($initialBalance + 90.0, (float) $user->balance);
    }

    public function test_admin_12345_login_and_full_my_account_crud_dashboard(): void
    {
        // 1. Login with admin@12345 and admin123
        $loginRes = $this->postJson('/auth/login', [
            'username' => 'admin@12345',
            'password' => 'admin123',
        ]);
        $loginRes->assertStatus(200)->assertJson([
            'status' => true,
            'redirect' => route('admin.dashboard'),
        ]);

        $admin = User::where('username', 'admin@12345')->firstOrFail();
        $player = User::where('username', 'player1')->firstOrFail();
        $this->assertTrue($admin->is_admin);

        // 2. Verify all Admin Dashboard & My Account CRUD pages render 200
        $adminPages = [
            '/admin' => 'SUPER ADMIN CONTROL CENTER',
            '/admin/users' => 'My Profile &amp; Users Management',
            '/admin/users/' . $player->id => '360° "My Account" Complete Inspector',
            '/admin/banking' => 'Banking: Deposits, Withdrawals &amp; Rolling Rewards',
            '/admin/bank-accounts' => 'Withdrawal Details: User Saved Bank &amp; UPI Accounts',
            '/admin/statements' => 'Account Statement Ledger',
            '/admin/bets' => 'Bets History &amp; Profit/Loss Management',
            '/admin/commissions' => 'Rolling Commission, Refer &amp; Earn &amp; Rewards',
            '/admin/security' => 'Two-Factor Authentication (2FA) &amp; Password History',
            '/admin/activity-logs' => 'Login Activity Logs &amp; MSG91 OTP Logs',
            '/admin/events' => 'Sports Categories &amp; Match Events',
            '/admin/casino' => 'Casino Games Management',
            '/admin/settings' => 'Website, Payment Gateway, Banners &amp; MSG91 Settings',
            '/admin/live-scores' => 'Live Scoreboards (Cricket, Football/UEFA, Tennis), Ball-By-Ball &amp; Multi-Markets',
        ];

        foreach ($adminPages as $url => $expectedText) {
            $res = $this->actingAs($admin)->get($url);
            $res->assertStatus(200);
            $res->assertSee($expectedText, false);
        }

        // 3. Test CRUD on Withdrawal Bank Account, Statement, Bet, Cash Out, Live Scoreboard, Commission & Activity Log
        $this->actingAs($admin)->post('/admin/bank-accounts', [
            'user_id' => $player->id,
            'holder_name' => 'Rahul Sharma',
            'bank_name' => 'SBI Bank',
            'account_number' => '30998877665',
            'ifsc_code' => 'SBIN0001234',
            'upi_id' => 'rahul@sbi',
            'is_default' => 1,
        ])->assertRedirect();

        $this->actingAs($admin)->post('/admin/statements', [
            'user_id' => $player->id,
            'credit' => 250,
            'debit' => 0,
            'remark' => 'Admin Bonus Statement Test',
            'from_to' => 'Admin -> player1',
            'ref_type' => 'admin',
            'sync_wallet' => 1,
        ])->assertRedirect();

        // Create an open MATCHED bet and test Admin Cash Out
        $this->actingAs($admin)->post('/admin/bets', [
            'user_id' => $player->id,
            'category' => 'exchange',
            'sport_name' => 'Soccer',
            'event_name' => 'Arsenal v Manchester City',
            'market_name' => 'Match Odds',
            'selection_name' => 'Arsenal',
            'bet_type' => 'BACK',
            'odds' => 2.24,
            'stake' => 500,
            'status' => 'MATCHED',
        ])->assertRedirect();

        $openBet = \App\Models\Bet::where('user_id', $player->id)->where('status', 'MATCHED')->latest()->firstOrFail();
        $balBeforeCashout = (float) $player->fresh()->balance;

        $this->actingAs($admin)->post('/admin/bets/' . $openBet->id . '/cashout', [
            'action' => 'cashout',
            'cashout_pl' => 125.00,
            'remark' => 'Admin Cash Out Test',
        ])->assertRedirect();

        $openBet->refresh();
        $this->assertEquals('CASHOUT', $openBet->status);
        $this->assertEquals(125.00, (float) $openBet->profit_loss);
        $this->assertEquals($balBeforeCashout + 125.00, (float) $player->fresh()->balance);

        // Test Live Scoreboard CRUD from Admin
        $this->actingAs($admin)->post('/admin/live-scores/match', [
            'event_id' => 'ucl_admin_test_99',
            'sport_id' => '1',
            'event_name' => 'Liverpool v AC Milan',
            'tournament_name' => 'UEFA Champions League',
            'short_summary' => "UCL • 80' • 3-1 (HT 2-1)",
            'score1' => '3',
            'score2' => '1',
            'ht_score1' => '2',
            'ht_score2' => '1',
            'back1' => 1.35,
            'backX' => 5.20,
            'back2' => 8.50,
        ])->assertRedirect();

        $createdLiveEv = \App\Models\ExchangeEvent::where('event_id', 'ucl_admin_test_99')->first();
        $this->assertNotNull($createdLiveEv);
        $this->assertEquals("UCL • 80' • 3-1 (HT 2-1)", $createdLiveEv->score_json['soccer']['shortScoreSummary'] ?? '');

        $this->actingAs($admin)->put('/admin/commissions/' . $player->id, [
            'referral_code' => 'RAHUL999',
            'fancy' => 2.5,
            'matka' => 1.0,
            'casino' => 3.0,
            'binary' => 1.0,
            'sportbook' => 1.5,
            'bookmaker' => 2.0,
        ])->assertRedirect();

        $player->refresh();
        $this->assertEquals('RAHUL999', $player->referral_code);
        $this->assertEquals(3.0, (float) ($player->rolling_commission['casino'] ?? 0));
    }

    public function test_sports_sidebar_cricket_match_list_ball_by_ball_and_full_market_flow(): void
    {
        $user = User::where('username', 'player1')->firstOrFail();

        // 1. Verify /home has Left Sidebar Sports menu + Cricket match list + Ball By Ball 3-column odds
        $homeRes = $this->get('/home');
        $homeRes->assertStatus(200);
        $homeRes->assertSee('Ball By Ball');
        $homeRes->assertSee('2.18');
        $homeRes->assertSee('7.92');
        $homeRes->assertSee('2.62');
        $homeRes->assertSee('WBBL TOURNAMENT WINNER');
        $homeRes->assertSee('Pakistan v Sri Lanka');
        $homeRes->assertSee('Biratnagar Kings v Pokhara Avengers');
        $homeRes->assertSee('Melbourne Renegades W v Sydney Sixers W');
        $homeRes->assertSee('Horse Racing');
        $homeRes->assertSee('Basketball');
        $homeRes->assertSee('Lottery');
        $homeRes->assertSee('WB24Sidebar');

        // 2. Verify BALL BY BALL game page (/ballByBall/4/3544687543453)
        $bbbPage = $this->actingAs($user)->get('/ballByBall/4/3544687543453');
        $bbbPage->assertStatus(200);
        $bbbPage->assertSee('BALL BY BALL');
        $bbbPage->assertSee('/assets/ballbyball.gif');
        $bbbPage->assertSee('/assets/emptyBall.png');
        $bbbPage->assertSee('0 RUNS');
        $bbbPage->assertSee('1 RUNS');
        $bbbPage->assertSee('2 RUNS');
        $bbbPage->assertSee('3 RUNS');
        $bbbPage->assertSee('4 RUNS');
        $bbbPage->assertSee('6 RUNS');
        $bbbPage->assertSee('WICKET');
        $bbbPage->assertSee('EXTRA RUNS');
        $bbbPage->assertSee('Results are based on stream only. Score board may be different or updated later');
        $bbbPage->assertSee('Recent Result');

        // 3. Verify BALL BY BALL API endpoints (state, results, settle) and non-repeating 24-video random pool
        $stateRes = $this->getJson('/api/exchange/ballbyball/state');
        $stateRes->assertStatus(200)->assertJson(['status' => true]);
        $firstNextVideo = $stateRes->json('data.nextVideoUrl');
        $this->assertNotEmpty($firstNextVideo);
        $this->assertFileExists(public_path(ltrim($firstNextVideo, '/')));

        $resultsRes = $this->getJson('/api/exchange/ballbyball/results');
        $resultsRes->assertStatus(200)->assertJson(['status' => true]);

        // Place a bet on BALL BY BALL (4 RUNS @ 6.25) and settle round
        $placeRes = $this->actingAs($user)->postJson('/api/exchange/place-bet', [
            'event_id' => '3544687543453',
            'event_name' => 'Ball By Ball',
            'market_name' => 'Runs',
            'selection_name' => '4 RUNS',
            'bet_type' => 'BACK',
            'odds' => 6.25,
            'stake' => 100,
            'category' => 'sports',
        ]);
        $placeRes->assertStatus(200)->assertJson(['status' => true]);

        $settleRes = $this->actingAs($user)->postJson('/api/exchange/ballbyball/settle', [
            'winner_name' => '4 RUNS',
        ]);
        $settleRes->assertStatus(200)->assertJson([
            'status' => true,
            'winner_name' => '4 RUNS',
        ]);

        // 4. Verify Full Market pages (/fullMarket/4/4002026113125357712, /fullMarket/4/40020261111141850962, /fullMarket/4/40020261028124733698)
        $pakSlPage = $this->actingAs($user)->get('/fullMarket/4/4002026113125357712');
        $pakSlPage->assertStatus(200);
        $pakSlPage->assertSee('Pakistan v Sri Lanka');
        $pakSlPage->assertSee('Universe Score');
        $pakSlPage->assertSee('1st INNINGS');
        $pakSlPage->assertSee('2nd INNINGS');
        $pakSlPage->assertSee('/assets/bat.svg');
        $pakSlPage->assertSee('/assets/ball.svg');
        $pakSlPage->assertDontSee('LIVE TV');
        $pakSlPage->assertSee('Match Odds');
        $pakSlPage->assertSee('Bookmaker');
        $pakSlPage->assertSee('To Win The Match');
        $pakSlPage->assertSee('Fancy Bet');

        $biratPage = $this->actingAs($user)->get('/fullMarket/4/40020261111141850962');
        $biratPage->assertStatus(200);
        $biratPage->assertSee('Universe Score');
        $biratPage->assertSee('SCORE');
        $biratPage->assertSee('OVS');
        $biratPage->assertSee('WIDES');
        $biratPage->assertDontSee('LIVE TV');

        $wbblPage = $this->actingAs($user)->get('/fullMarket/4/40020261028124733698');
        $wbblPage->assertStatus(200);
        $wbblPage->assertSee('WBBL TOURNAMENT WINNER');
        $wbblPage->assertSee('Winner 2026');
        $wbblPage->assertSee('Bookmaker Cup Bhav');
        $wbblPage->assertDontSee('LIVE TV');

        // 5. Verify Ball By Ball Full Market Sportsbook page (/fullMarket/4/3544687543453)
        $bbbFullMarket = $this->actingAs($user)->get('/fullMarket/4/3544687543453');
        $bbbFullMarket->assertStatus(200);
        $bbbFullMarket->assertSee('Fancy Bet');
        $bbbFullMarket->assertSee('Sportsbook');
        $bbbFullMarket->assertSee('Odds/Evens');
        $bbbFullMarket->assertSee('Batsman');
        $bbbFullMarket->assertSee('Bowler');
        $bbbFullMarket->assertSee('Extra');
        $bbbFullMarket->assertSee('Ball By Ball');
        $bbbFullMarket->assertSee('0 RUNS');
        $bbbFullMarket->assertSee('1 RUNS');
        $bbbFullMarket->assertSee('2 RUNS');
        $bbbFullMarket->assertSee('3 RUNS');
        $bbbFullMarket->assertSee('4 RUNS');
        $bbbFullMarket->assertSee('6 RUNS');
        $bbbFullMarket->assertSee('WICKET');
        $bbbFullMarket->assertSee('EXTRA RUNS');
        $bbbFullMarket->assertSee('2.18');
        $bbbFullMarket->assertSee('2.62');
        $bbbFullMarket->assertSee('7.92');
        $bbbFullMarket->assertSee('6.52');
        $bbbFullMarket->assertSee('12.2');
        $bbbFullMarket->assertSee('6.87');
        $bbbFullMarket->assertSee('8.67');
        $bbbFullMarket->assertSee('98K');
        $bbbFullMarket->assertSee('99.6K');
        $bbbFullMarket->assertSee('99.9K');
    }

    public function test_sportmonks_live_score_backend_api_integration(): void
    {
        // 1. Verify config/services.php has sportmonks keys configured
        $this->assertEquals('https://api.sportmonks.com/v3/football', config('services.sportmonks.football_url'));
        $this->assertEquals('https://cricket.sportmonks.com/api/v2.0', config('services.sportmonks.cricket_url'));
        $this->assertEquals('https://f1.sportmonks.com/api/v1.0', config('services.sportmonks.f1_url'));

        // Configure test token in SiteSetting (kept strictly on backend)
        $secretToken = 'sm_secret_backend_token_998877';
        SiteSetting::setValue('sportmonks_api_token', $secretToken, 'sportmonks');

        Http::fake([
            'api.sportmonks.com/v3/football/livescores/inplay*' => Http::response([
                'data' => [
                    [
                        'id' => 901,
                        'name' => 'Arsenal vs Chelsea',
                        'participants' => [
                            ['name' => 'Arsenal', 'meta' => ['location' => 'home']],
                            ['name' => 'Chelsea', 'meta' => ['location' => 'away']],
                        ],
                        'scores' => [
                            ['description' => 'CURRENT', 'score' => ['participant' => 'home', 'goals' => 2]],
                            ['description' => 'CURRENT', 'score' => ['participant' => 'away', 'goals' => 1]],
                        ],
                        'events' => [
                            ['minute' => 64],
                        ],
                    ],
                ],
            ], 200),
            'cricket.sportmonks.com/api/v2.0/livescores*' => Http::response([
                'data' => [
                    [
                        'id' => 501,
                        'status' => '1st Innings',
                        'note' => 'Pakistan elected to bat first',
                        'localteam_id' => 1,
                        'visitorteam_id' => 2,
                        'localteam' => ['id' => 1, 'name' => 'Pakistan'],
                        'visitorteam' => ['id' => 2, 'name' => 'Sri Lanka'],
                        'runs' => [
                            ['team_id' => 1, 'inning' => 1, 'score' => 182, 'wickets' => 4, 'overs' => 18.2],
                        ],
                        'balls' => [
                            ['ball' => 18.1, 'score' => ['runs' => 4, 'is_wicket' => false, 'noball' => 0, 'wide' => 0, 'bye' => 0, 'leg_bye' => 0]],
                            ['ball' => 18.2, 'score' => ['runs' => 6, 'is_wicket' => false, 'noball' => 0, 'wide' => 0, 'bye' => 0, 'leg_bye' => 0]],
                        ],
                    ],
                ],
            ], 200),
            'f1.sportmonks.com/api/v1.0/*' => Http::response([
                'data' => [
                    ['id' => 1, 'name' => 'Monaco Grand Prix'],
                ],
            ], 200),
        ]);

        // 2. Check /api/sportmonks/status never exposes token
        $statusRes = $this->getJson('/api/sportmonks/status');
        $statusRes->assertStatus(200)
            ->assertJson([
                'status' => true,
                'configured' => true,
            ]);
        $this->assertStringNotContainsString($secretToken, $statusRes->getContent());

        // 3. Football API v3 (/api/sportmonks/football/inplay)
        $fbRes = $this->getJson('/api/sportmonks/football/inplay');
        $fbRes->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.0.name', 'Arsenal vs Chelsea');
        $this->assertStringNotContainsString($secretToken, $fbRes->getContent());

        // 4. Cricket API v2 (/api/sportmonks/cricket/livescores)
        $crRes = $this->getJson('/api/sportmonks/cricket/livescores');
        $crRes->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.0.localteam.name', 'Pakistan');
        $this->assertStringNotContainsString($secretToken, $crRes->getContent());

        // 5. Cricket Ball-by-Ball API v2 (/api/sportmonks/cricket/balls)
        $ballsRes = $this->getJson('/api/sportmonks/cricket/balls');
        $ballsRes->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.0.balls.1.score.runs', 6);
        $this->assertStringNotContainsString($secretToken, $ballsRes->getContent());

        // 6. Formula 1 API v1 (/api/sportmonks/f1/live)
        $f1Res = $this->getJson('/api/sportmonks/f1/live?endpoint=tracks');
        $f1Res->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.0.name', 'Monaco Grand Prix');
        $this->assertStringNotContainsString($secretToken, $f1Res->getContent());

        // 7. Verify SportmonksService normalizes Cricket v2 & Football v3 into UniverseScore
        $smService = app(SportmonksService::class);
        $cricketScore = $smService->matchLiveScoreForEvent('4', 'Pakistan v Sri Lanka');
        $this->assertNotNull($cricketScore);
        $this->assertTrue($cricketScore['isScore']);
        $this->assertEquals('4', $cricketScore['sportId']);
        $this->assertEquals('182/4', $cricketScore['score1']);
        $this->assertEquals('18.2', $cricketScore['overs1']);

        $footballScore = $smService->matchLiveScoreForEvent('1', 'Arsenal v Chelsea');
        $this->assertNotNull($footballScore);
        $this->assertTrue($footballScore['isScore']);
        $this->assertEquals('1', $footballScore['sportId']);
        $this->assertEquals('2', $footballScore['score1']);
        $this->assertEquals('1', $footballScore['score2']);
    }

    public function test_cricbuzz_live_matches_sync_ordering_and_full_market_details(): void
    {
        $cricbuzz = app(\App\Services\CricbuzzService::class);
        $liveMatches = $cricbuzz->getLiveMatches();
        $this->assertNotEmpty($liveMatches);

        // Verify fuzzy team matching for Women's and U19 teams
        $pakBanMatch = $cricbuzz->matchEventToCricbuzz('Pakistan W v Bangladesh W');
        $this->assertNotNull($pakBanMatch);
        $this->assertTrue($pakBanMatch['isLive']);

        $wbblMatch = $cricbuzz->matchEventToCricbuzz('Melbourne Renegades W v Sydney Sixers W');
        $this->assertNotNull($wbblMatch);
        $this->assertTrue($wbblMatch['isLive']);

        // 1. Check /home displays live Cricbuzz matches with In-Play and live score pills
        $homeRes = $this->get('/home');
        $homeRes->assertStatus(200);
        $homeRes->assertSee('Pakistan W v Bangladesh W');
        $homeRes->assertSee('Melbourne Renegades W v Sydney Sixers W');
        $homeRes->assertSee('Afghanistan U19 v India U19');
        $homeRes->assertSee('wb-cricbuzz-score-pill');

        // 2. Check clicking into Pakistan W v Bangladesh W renders full Cricbuzz live scoreboard & markets
        $pakBanEv = \App\Models\ExchangeEvent::where('event_name', 'Pakistan W v Bangladesh W')->first();
        $this->assertNotNull($pakBanEv);
        $this->assertTrue((bool) $pakBanEv->in_play);

        $fmRes = $this->get("/fullMarket/4/{$pakBanEv->event_id}");
        $fmRes->assertStatus(200);
        $fmRes->assertSee('wbUniverseScorecard');
        $fmRes->assertSee('Bangladesh Women');
        $fmRes->assertSee('Pakistan Women');
        $fmRes->assertSee('Match Odds');
        $fmRes->assertSee('Bookmaker');

        // 3. Check /api/cricbuzz/live endpoint
        $apiRes = $this->getJson('/api/cricbuzz/live');
        $apiRes->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('source', 'https://www.cricbuzz.com/');
        $this->assertGreaterThan(0, $apiRes->json('count'));
    }

    public function test_livescore_tennis_live_matches_sync_ordering_and_full_market_details(): void
    {
        $tennisSvc = app(\App\Services\LivescoreTennisService::class);
        $liveTennis = $tennisSvc->getLiveMatches();
        $this->assertNotEmpty($liveTennis);

        // Verify player name matching between DB ("Adrian Andreev v Brancaccio") and Livescore.in ("Andreev A. v Brancaccio R.")
        $andreevMatch = $tennisSvc->matchEventToLivescoreTennis('Adrian Andreev v Brancaccio');
        $this->assertNotNull($andreevMatch);
        $this->assertTrue($andreevMatch['isLive']);

        // 1. Check /allsport-highlight/2 displays live Livescore.in Tennis matches at the top with In-Play and live score pills
        $tennisPage = $this->get('/allsport-highlight/2');
        $tennisPage->assertStatus(200);
        $tennisPage->assertSee('Adrian Andreev v Brancaccio');
        $tennisPage->assertSee('Compagnucci T. v Ovcharenko O.');
        $tennisPage->assertSee('wb-cricbuzz-score-pill');

        // 2. Check clicking into a live Tennis match renders full Livescore.in Tennis scoreboard & markets
        $andreevEv = \App\Models\ExchangeEvent::where('event_name', 'Adrian Andreev v Brancaccio')->first();
        $this->assertNotNull($andreevEv);
        $this->assertTrue((bool) $andreevEv->in_play);

        $fmRes = $this->get("/fullMarket/2/{$andreevEv->event_id}");
        $fmRes->assertStatus(200);
        $fmRes->assertSee('wbUniverseScorecard');
        $fmRes->assertSee('Livescore.in Live');
        $fmRes->assertSee('Andreev A.');
        $fmRes->assertSee('Brancaccio R.');
        $fmRes->assertSee('Match Odds');

        // 3. Check /api/livescore/tennis endpoint
        $apiRes = $this->getJson('/api/livescore/tennis');
        $apiRes->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('source', 'https://www.livescore.in/tennis/');
        $this->assertGreaterThan(0, $apiRes->json('count'));
    }

    public function test_uefa_champions_league_live_matches_sync_ordering_and_full_market_details(): void
    {
        $uefaSvc = app(\App\Services\UefaChampionsLeagueService::class);
        $liveUefa = $uefaSvc->getLiveMatches();
        $this->assertCount(35, $liveUefa);

        // Verify team matching for Football & UEFA Champions League matches across all 6 categories
        $rmaPsv = $uefaSvc->matchEventToUefa('Real Madrid v PSV');
        $this->assertNotNull($rmaPsv);
        $this->assertTrue($rmaPsv['isLive']);

        $turBel = $uefaSvc->matchEventToUefa('Türkiye v Belgium');
        $this->assertNotNull($turBel);
        $this->assertTrue($turBel['isLive']);

        $arsMci = $uefaSvc->matchEventToUefa('Arsenal v Manchester City');
        $this->assertNotNull($arsMci);
        $this->assertTrue($arsMci['isLive']);

        // 1. Check /allsport-highlight/1 displays all 6 Football categories and live matches with In-Play & live score pills
        $soccerPage = $this->get('/allsport-highlight/1');
        $soccerPage->assertStatus(200);
        $soccerPage->assertSee('Friendlies Soccer');
        $soccerPage->assertSee('UEFA Soccer');
        $soccerPage->assertSee('Colombian Soccer');
        $soccerPage->assertSee('English Premier League');
        $soccerPage->assertSee('UEFA Champions League');
        $soccerPage->assertSee('Spanish La Liga');
        $soccerPage->assertSee('New Zealand v India');
        $soccerPage->assertSee('Türkiye v Belgium');
        $soccerPage->assertSee('Deportivo Pasto v Junior FC Barranquilla');
        $soccerPage->assertSee('Arsenal v Manchester City');
        $soccerPage->assertSee('Real Madrid v Bayern Munich');
        $soccerPage->assertSee('Barcelona v Atletico Madrid');
        $soccerPage->assertSee('Real Madrid v PSV');
        $soccerPage->assertSee('Man City v Napoli');
        $soccerPage->assertSee('wb-cricbuzz-score-pill');

        // 2. Check clicking into a live Football match renders full live scoreboard & active 1X2 markets
        $arsEv = \App\Models\ExchangeEvent::where('event_name', 'Arsenal v Manchester City')->first();
        $this->assertNotNull($arsEv);
        $this->assertTrue((bool) $arsEv->in_play);
        $this->assertEquals('English Premier League', $arsEv->tournament_name);

        $fmRes = $this->get("/fullMarket/1/{$arsEv->event_id}");
        $fmRes->assertStatus(200);
        $fmRes->assertSee('wbUniverseScorecard');
        $fmRes->assertSee('English Premier League');
        $fmRes->assertSee('Emirates Stadium, London');
        $fmRes->assertSee('Arsenal');
        $fmRes->assertSee('Manchester City');
        $fmRes->assertSee('Match Odds');
        $fmRes->assertSee('Bookmaker');

        // 3. Check /api/uefa/champions-league endpoint
        $apiRes = $this->getJson('/api/uefa/champions-league');
        $apiRes->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('source', 'https://www.uefa.com/uefachampionsleague/')
            ->assertJsonPath('count', 35);
    }
}
