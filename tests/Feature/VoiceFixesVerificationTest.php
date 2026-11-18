<?php

namespace Tests\Feature;

use App\Models\AccountStatement;
use App\Models\Bet;
use App\Models\CasinoGame;
use App\Models\ExchangeEvent;
use App\Models\User;
use App\Services\CasinoEngineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
