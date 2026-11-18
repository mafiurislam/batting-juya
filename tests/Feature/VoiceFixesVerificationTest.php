<?php

namespace Tests\Feature;

use App\Models\AccountStatement;
use App\Models\BankingTransaction;
use App\Models\Bet;
use App\Models\CasinoGame;
use App\Models\ExchangeEvent;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\CasinoEngineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class VoiceFixesVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;
    public function test_fancy_bet_sanitizes_invalid_rate_nets_exposure_and_credits_wallet_on_win(): void
    {
        $user = User::create([
            'username' => 'fancy_tester_' . rand(1000, 9999),
            'name' => 'Fancy Tester',
            'phone' => '9876543211',
            'email' => 'fancy_tester_' . rand(1000, 9999) . '@example.com',
            'password' => bcrypt('password123'),
            'balance' => 1000.00,
            'exposure' => 0.00,
            'role' => 'user',
        ]);

        $event = ExchangeEvent::where('sport_id', '4')->firstOrFail();

        try {
            // 1. Place Fancy YES @ 44 with misformatted liquidity size (50000) -> should normalize rate to 100
            $res1 = $this->actingAs($user)->postJson('/api/exchange/place-bet', [
                'event_id' => $event->event_id,
                'event_name' => $event->event_name,
                'market_name' => '6 Overs IND Runs',
                'selection_id' => 'f_6ov',
                'selection_name' => '6 Overs IND Runs (YES @ 44)',
                'bet_type' => 'YES',
                'odds' => 50000,
                'stake' => 200,
                'category' => 'fancy',
                'run_line' => 44,
            ]);

            $res1->assertOk()->assertJsonPath('status', true);
            $this->assertEquals(100.0, (float) $res1->json('bet.odds'));
            $this->assertEquals(200.0, (float) $res1->json('bet.potential_profit'));
            $this->assertEquals(200.0, (float) $res1->json('wallet.exposure'));
            $this->assertEquals(800.0, (float) $res1->json('wallet.available'));

            $betId = $res1->json('bet.id');

            // 2. Settle Fancy bet as WON -> exposure becomes 0 and balance increases by +200 (1000 -> 1200)
            $resSettle = $this->actingAs($user)->postJson('/api/exchange/cashout-bet', [
                'bet_id' => $betId,
                'mode' => 'instant_settle',
            ]);

            $resSettle->assertOk()->assertJsonPath('status', true);
            $this->assertEquals(1200.0, (float) $resSettle->json('wallet.balance'));
            $this->assertEquals(0.0, (float) $resSettle->json('wallet.exposure'));
            $this->assertEquals(1200.0, (float) $resSettle->json('wallet.available'));

            // 3. Test Fancy hedging (YES @ 72 + NO @ 78 for 100 each -> exposure nets to 0, stake returned to wallet)
            $this->actingAs($user)->postJson('/api/exchange/place-bet', [
                'event_id' => $event->event_id,
                'event_name' => $event->event_name,
                'market_name' => '10 Overs IND Runs',
                'selection_id' => 'f_10ov',
                'selection_name' => '10 Overs IND Runs (YES @ 72)',
                'bet_type' => 'YES',
                'odds' => 100,
                'stake' => 100,
                'category' => 'fancy',
                'run_line' => 72,
            ])->assertOk();

            $hedgeRes = $this->actingAs($user)->postJson('/api/exchange/place-bet', [
                'event_id' => $event->event_id,
                'event_name' => $event->event_name,
                'market_name' => '10 Overs IND Runs',
                'selection_id' => 'f_10ov',
                'selection_name' => '10 Overs IND Runs (NO @ 78)',
                'bet_type' => 'NO',
                'odds' => 100,
                'stake' => 100,
                'category' => 'fancy',
                'run_line' => 78,
            ]);

            $hedgeRes->assertOk()->assertJsonPath('status', true);
            $this->assertEquals(0.0, (float) $hedgeRes->json('wallet.exposure'));
            $this->assertEquals(1200.0, (float) $hedgeRes->json('wallet.available'));
        } finally {
            Bet::where('user_id', $user->id)->delete();
            AccountStatement::where('user_id', $user->id)->delete();
            $user->delete();
        }
    }

    public function test_casino_table_bet_waits_for_timer_settlement_and_slot_payouts_are_balanced(): void
    {
        $user = User::create([
            'username' => 'casino_tester_' . rand(1000, 9999),
            'name' => 'Casino Tester',
            'phone' => '9876543212',
            'email' => 'casino_tester_' . rand(1000, 9999) . '@example.com',
            'password' => bcrypt('password123'),
            'balance' => 1000.00,
            'exposure' => 0.00,
            'role' => 'user',
        ]);

        $tableGame = CasinoGame::where('game_type', 'roulette')->first() ?? CasinoGame::firstOrFail();
        $slotGame = CasinoGame::where('game_type', 'slot')->first() ?? CasinoGame::firstOrFail();

        try {
            // 1. Placing a bet during timer locks stake in exposure (MATCHED), does NOT immediately settle!
            $placeRes = $this->actingAs($user)->postJson('/api/casino/teenpatti/place-bet', [
                'event_id' => $tableGame->event_id,
                'round_id' => 'R123456',
                'market_key' => 'ROULETTE',
                'market_name' => $tableGame->event_name,
                'selection_key' => 'RED',
                'selection_name' => 'RED',
                'side' => 'BACK',
                'odds' => 2.00,
                'stake' => 100,
            ]);

            $placeRes->assertOk()->assertJsonPath('status', true);
            $this->assertEquals('MATCHED', $placeRes->json('bet.status'));
            $this->assertEquals(100.0, (float) $placeRes->json('wallet.exposure'));
            $this->assertEquals(900.0, (float) $placeRes->json('wallet.available'));

            // 2. When timer reaches 0s, /api/casino/table/settle-round settles the bet and clears exposure
            $settleRes = $this->actingAs($user)->postJson('/api/casino/table/settle-round', [
                'event_id' => $tableGame->event_id,
                'round_id' => 'R123456',
            ]);

            $settleRes->assertOk()->assertJsonPath('status', true);
            $this->assertEquals(0.0, (float) $settleRes->json('wallet.exposure'));
            $settledStatus = $settleRes->json('settled_bets.0.status');
            $this->assertContains($settledStatus, ['WON', 'LOST']);

            $expectedBalance = $settledStatus === 'WON' ? 1100.0 : 900.0;
            $this->assertEquals($expectedBalance, (float) $settleRes->json('wallet.balance'));
            $this->assertEquals($expectedBalance, (float) $settleRes->json('wallet.available'));

            // 3. Verify Slot payouts are balanced (never 3700-3800 PTI on a 100 PTI spin)
            $engine = app(CasinoEngineService::class);
            $maxNetPl = -9999;
            $losses = 0;
            for ($i = 0; $i < 40; $i++) {
                $out = $engine->playRound($slotGame, 'SPIN', 100);
                $maxNetPl = max($maxNetPl, (float) $out['net_pl']);
                if (!$out['won']) {
                    $losses++;
                }
            }
            $this->assertLessThanOrEqual(1400.0, $maxNetPl, '100 PTI slot spin should stay within capped 15x return (<= 1400 net profit)');
            $this->assertGreaterThan(5, $losses, 'Slot spins should include realistic losing spins, not always winning');
        } finally {
            Bet::where('user_id', $user->id)->delete();
            AccountStatement::where('user_id', $user->id)->delete();
            $user->delete();
        }
    }

    public function test_banking_pages_show_deposit_and_withdrawal_history_tables(): void
    {
        $user = User::create([
            'username' => 'bank_hist_' . rand(1000, 9999),
            'name' => 'Bank History User',
            'phone' => '9876543213',
            'email' => 'bank_hist_' . rand(1000, 9999) . '@example.com',
            'password' => bcrypt('password123'),
            'balance' => 5000.00,
            'exposure' => 0.00,
            'role' => 'user',
        ]);

        BankingTransaction::create([
            'tx_ref' => 'DEP-TEST-001',
            'user_id' => $user->id,
            'type' => 'deposit',
            'method' => 'UPI',
            'amount' => 5000,
            'charges' => 0,
            'final_amount' => 5000,
            'utr_number' => '390063985807',
            'status' => 'APPROVED',
            'admin_note' => 'Verified UPI',
        ]);

        BankingTransaction::create([
            'tx_ref' => 'WDR-TEST-001',
            'user_id' => $user->id,
            'type' => 'withdraw',
            'method' => 'NORMAL',
            'amount' => 2200,
            'charges' => 0,
            'final_amount' => 2200,
            'status' => 'PENDING',
        ]);

        $this->actingAs($user)->get('/banking')
            ->assertOk()
            ->assertSee('390063985807')
            ->assertSee('Deposit History')
            ->assertSee('Withdrawal History')
            ->assertSee('2200');

        $this->actingAs($user)->get('/banking/withdraw')
            ->assertOk()
            ->assertSee('Withdrawal Type')
            ->assertSee('Request Time')
            ->assertSee('2200')
            ->assertSee('NORMAL');
    }

    public function test_vimaan_server_authoritative_crash_point_blocks_late_cashout_exploit(): void
    {
        $user = User::create([
            'username' => 'vimaan_edge_' . rand(1000, 9999),
            'name' => 'Vimaan Edge Tester',
            'phone' => '9876543214',
            'email' => 'vimaan_edge_' . rand(1000, 9999) . '@example.com',
            'password' => bcrypt('password123'),
            'balance' => 1000.00,
            'exposure' => 0.00,
            'role' => 'user',
        ]);

        $roundId = 'VM_AUTH_' . rand(10000, 99999);

        // 1. User places 100 PTI bet during WAIT phase
        $placeRes = $this->actingAs($user)->postJson('/api/vimaan/place-bet', [
            'stake' => 100,
            'slot' => 1,
            'round_id' => $roundId,
        ]);
        $placeRes->assertOk()->assertJsonPath('status', true);
        $betId = $placeRes->json('bet.id');

        // 2. Round starts -> server locks crash point in Cache
        $startRes = $this->actingAs($user)->postJson('/api/vimaan/round-start', [
            'round_id' => $roundId,
        ]);
        $startRes->assertOk()->assertJsonPath('status', true);
        $this->assertNotNull(Cache::get("vimaan_crash_{$roundId}"));

        // Force crash point to 1.20x in cache and attempt client exploit cashout at 3.50x
        Cache::put("vimaan_crash_{$roundId}", 1.20, 600);

        $exploitRes = $this->actingAs($user)->postJson('/api/vimaan/cashout', [
            'bet_id' => $betId,
            'multiplier' => 3.50,
        ]);

        $exploitRes->assertStatus(422)->assertJsonPath('status', false);
        $this->assertEquals(900.0, (float) $exploitRes->json('wallet.balance'));
        $this->assertEquals(0.0, (float) $exploitRes->json('wallet.exposure'));
        $this->assertEquals('LOST', Bet::find($betId)->status);
    }

    public function test_dragon_tiger_and_fancy_live_pulse_auto_settlement_credit_wallet(): void
    {
        $user = User::create([
            'username' => 'dt_fancy_' . rand(1000, 9999),
            'name' => 'DT Fancy Tester',
            'phone' => '9876543215',
            'email' => 'dt_fancy_' . rand(1000, 9999) . '@example.com',
            'password' => bcrypt('password123'),
            'balance' => 5000.00,
            'exposure' => 0.00,
            'role' => 'user',
        ]);

        // 1. Dragon Tiger settlement with house edge returns genuine cards matching declared winner
        SiteSetting::setValue('casino_house_edge_pct', '70', 'casino');
        $this->actingAs($user)->postJson('/api/casino/teenpatti/place-bet', [
            'event_id' => '99.0019',
            'round_id' => 'DT_EDGE_01',
            'market_key' => 'WINNER',
            'market_name' => 'WINNER',
            'selection_key' => 'DRAGON',
            'selection_name' => 'DRAGON',
            'odds' => 2.00,
            'stake' => 100,
        ])->assertOk();

        $dtRes = $this->actingAs($user)->postJson('/api/casino/dragontiger/settle-round', [
            'event_id' => '99.0019',
            'round_id' => 'DT_EDGE_01',
        ]);
        $dtRes->assertOk()->assertJsonPath('status', true);
        $this->assertContains($dtRes->json('winner'), ['DRAGON', 'TIGER', 'TIE']);
        $this->assertEquals(0.0, (float) $dtRes->json('wallet.exposure'));

        // 2. Fancy over-runs auto-settles via /api/live-pulse and credits Principal + Profit to Main PTI
        $event = ExchangeEvent::where('sport_id', '4')->where('event_id', '!=', '3544687543453')->firstOrFail();
        $sj = is_array($event->score_json) ? $event->score_json : [];
        $sj['universeScore'] = [
            'isScore' => true,
            'sportId' => '4',
            'score1' => '56/1',
            'overs1' => '6.0',
            'team1Flag' => true,
            'team2Flag' => false,
        ];
        $event->score_json = $sj;
        $event->save();

        $balBefore = (float) $user->fresh()->balance;

        $fancyRes = $this->actingAs($user)->postJson('/api/exchange/place-bet', [
            'event_id' => $event->event_id,
            'event_name' => $event->event_name,
            'market_name' => '6 Overs Runs',
            'selection_id' => 'f_6ov_auto',
            'selection_name' => '6 Overs Runs (YES @ 48)',
            'bet_type' => 'YES',
            'odds' => 100,
            'stake' => 1000,
            'category' => 'fancy',
            'run_line' => 48,
        ]);
        $fancyRes->assertOk();
        $fancyBetId = $fancyRes->json('bet.id');

        // Trigger live-pulse -> 56 >= 48 -> YES wins +1000 profit + 1000 exposure returned!
        $pulseRes = $this->actingAs($user)->getJson('/api/live-pulse');
        $pulseRes->assertOk()->assertJsonPath('status', true);

        $settledBet = Bet::find($fancyBetId);
        $this->assertEquals('WON', $settledBet->status);
        $this->assertEquals(round($balBefore + 1000.0, 2), (float) $pulseRes->json('wallet.balance'));
        $this->assertEquals(0.0, (float) $pulseRes->json('wallet.exposure'));
        $this->assertEquals(round($balBefore + 1000.0, 2), (float) $pulseRes->json('wallet.available'));
    }
}
