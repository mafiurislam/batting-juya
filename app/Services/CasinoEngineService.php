<?php

namespace App\Services;

use App\Models\CasinoGame;

class CasinoEngineService
{
    private array $suits = ['♠', '♥', '♣', '♦'];
    private array $ranks = [
        1 => 'A', 2 => '2', 3 => '3', 4 => '4', 5 => '5', 6 => '6', 7 => '7',
        8 => '8', 9 => '9', 10 => '10', 11 => 'J', 12 => 'Q', 13 => 'K',
    ];

    /**
     * Return betting markets & UI schema for any of the 217+ casino games.
     */
    public function getTableDefinition(CasinoGame $game): array
    {
        $type = $game->game_type;
        $name = strtoupper($game->event_name);

        return match ($type) {
            'crash' => [
                'layout' => 'crash',
                'subtitle' => 'Universe Original Crash Multiplier',
                'markets' => [
                    ['key' => 'AUTO_1_5', 'label' => 'Auto Cashout 1.50x', 'odds' => 1.50],
                    ['key' => 'AUTO_2_0', 'label' => 'Auto Cashout 2.00x', 'odds' => 2.00],
                    ['key' => 'AUTO_3_0', 'label' => 'Auto Cashout 3.00x', 'odds' => 3.00],
                    ['key' => 'AUTO_5_0', 'label' => 'Auto Cashout 5.00x', 'odds' => 5.00],
                ],
            ],
            'balloon' => [
                'layout' => 'balloon',
                'subtitle' => 'Pump & Cashout Before Burst',
                'markets' => [
                    ['key' => 'PUMP_1_5', 'label' => 'Safe Pump (1.50x)', 'odds' => 1.50],
                    ['key' => 'PUMP_2_0', 'label' => 'Medium Pump (2.00x)', 'odds' => 2.00],
                    ['key' => 'PUMP_3_5', 'label' => 'High Pump (3.50x)', 'odds' => 3.50],
                    ['key' => 'PUMP_7_0', 'label' => 'Mega Pump (7.00x)', 'odds' => 7.00],
                ],
            ],
            'coinflip' => [
                'layout' => 'coinflip',
                'subtitle' => 'Universe Original Heads & Tails',
                'markets' => [
                    ['key' => 'HEADS', 'label' => 'HEADS (Gold Crown)', 'odds' => 1.98, 'lay' => 2.02],
                    ['key' => 'TAILS', 'label' => 'TAILS (Lucky Clover)', 'odds' => 1.98, 'lay' => 2.02],
                ],
            ],
            'wheel' => [
                'layout' => 'wheel',
                'subtitle' => 'Live 54-Segment Money Wheel',
                'markets' => [
                    ['key' => 'SEG_1', 'label' => 'Number 1 (2x)', 'odds' => 2.00],
                    ['key' => 'SEG_2', 'label' => 'Number 2 (3x)', 'odds' => 3.00],
                    ['key' => 'SEG_5', 'label' => 'Number 5 (6x)', 'odds' => 6.00],
                    ['key' => 'SEG_10', 'label' => 'Number 10 (11x)', 'odds' => 11.00],
                    ['key' => 'SEG_20', 'label' => 'Number 20 (21x)', 'odds' => 21.00],
                    ['key' => 'SEG_40', 'label' => 'Number 40 (41x)', 'odds' => 41.00],
                ],
            ],
            'lucky09', 'worli' => [
                'layout' => 'lucky09',
                'subtitle' => $type === 'worli' ? '3-Card Instant Worli Matka Ank' : 'Spinning 0 to 9 Lucky Wheel',
                'markets' => array_merge(
                    [
                        ['key' => 'LOW', 'label' => 'LOW (0 - 4)', 'odds' => 1.95],
                        ['key' => 'HIGH', 'label' => 'HIGH (5 - 9)', 'odds' => 1.95],
                        ['key' => 'EVEN', 'label' => 'EVEN', 'odds' => 1.95],
                        ['key' => 'ODD', 'label' => 'ODD', 'odds' => 1.95],
                    ],
                    array_map(fn ($n) => ['key' => "NUM_{$n}", 'label' => "Digit {$n}", 'odds' => 9.50], range(0, 9))
                ),
            ],
            'teenpatti' => [
                'layout' => 'cards_ab',
                'sideA' => 'PLAYER A',
                'sideB' => 'PLAYER B',
                'subtitle' => str_contains($name, 'MUFLIS') ? 'Lowest 3-Card Hand Wins' : 'Classic 3-Card Indian Teen Patti',
                'markets' => [
                    ['key' => 'PLAYER_A', 'label' => 'Player A', 'odds' => 1.98, 'lay' => 2.02],
                    ['key' => 'PLAYER_B', 'label' => 'Player B', 'odds' => 1.98, 'lay' => 2.02],
                    ['key' => 'PAIR_PLUS_A', 'label' => 'Pair Plus A', 'odds' => 3.25],
                    ['key' => 'PAIR_PLUS_B', 'label' => 'Pair Plus B', 'odds' => 3.25],
                    ['key' => 'ANY_TRAIL', 'label' => 'Any Trail / Trio', 'odds' => 24.00],
                ],
            ],
            'lucky7' => [
                'layout' => 'single_card',
                'sideA' => 'LUCKY 7 CARD',
                'subtitle' => 'Single Card Low (A-6) / 7 / High (8-K)',
                'markets' => array_merge([
                    ['key' => 'LOW_CARD', 'label' => 'LOW Card (A - 6)', 'odds' => 2.00, 'lay' => 2.05],
                    ['key' => 'HIGH_CARD', 'label' => 'HIGH Card (8 - K)', 'odds' => 2.00, 'lay' => 2.05],
                    ['key' => 'SEVEN', 'label' => 'LUCKY 7', 'odds' => 12.00],
                    ['key' => 'EVEN', 'label' => 'Even', 'odds' => 2.10],
                    ['key' => 'ODD', 'label' => 'Odd', 'odds' => 1.80],
                    ['key' => 'RED', 'label' => 'Red (♥ ♦)', 'odds' => 1.95],
                    ['key' => 'BLACK', 'label' => 'Black (♠ ♣)', 'odds' => 1.95],
                ], array_map(fn ($r, $lbl) => ['key' => "CARD_{$r}", 'label' => "Card {$lbl}", 'odds' => 12.00], array_keys($this->ranks), $this->ranks)),
            ],
            'dragontiger' => [
                'layout' => 'cards_ab',
                'sideA' => 'DRAGON',
                'sideB' => 'TIGER',
                'subtitle' => '1-Card High Rank Showdown',
                'markets' => [
                    ['key' => 'DRAGON', 'label' => 'Dragon', 'odds' => 2.00, 'lay' => 2.04],
                    ['key' => 'TIGER', 'label' => 'Tiger', 'odds' => 2.00, 'lay' => 2.04],
                    ['key' => 'TIE', 'label' => 'Tie', 'odds' => 50.00],
                    ['key' => 'PAIR', 'label' => 'Same Rank Pair', 'odds' => 11.00],
                    ['key' => 'DRAGON_EVEN', 'label' => 'Dragon Even', 'odds' => 2.10],
                    ['key' => 'DRAGON_ODD', 'label' => 'Dragon Odd', 'odds' => 1.80],
                    ['key' => 'DRAGON_RED', 'label' => 'Dragon Red (♥♦)', 'odds' => 1.95],
                    ['key' => 'DRAGON_BLACK', 'label' => 'Dragon Black (♠♣)', 'odds' => 1.95],
                    ['key' => 'TIGER_RED', 'label' => 'Tiger Red (♥♦)', 'odds' => 1.95],
                    ['key' => 'TIGER_BLACK', 'label' => 'Tiger Black (♠♣)', 'odds' => 1.95],
                ],
            ],
            'dtl' => [
                'layout' => 'cards_3',
                'sides' => ['DRAGON', 'TIGER', 'LION'],
                'subtitle' => '3-Hand Dragon Tiger Lion Showdown',
                'markets' => [
                    ['key' => 'DRAGON', 'label' => 'Winner Dragon', 'odds' => 2.90, 'lay' => 3.05],
                    ['key' => 'TIGER', 'label' => 'Winner Tiger', 'odds' => 2.90, 'lay' => 3.05],
                    ['key' => 'LION', 'label' => 'Winner Lion', 'odds' => 2.90, 'lay' => 3.05],
                    ['key' => 'D_RED', 'label' => 'Dragon Red', 'odds' => 1.95],
                    ['key' => 'T_RED', 'label' => 'Tiger Red', 'odds' => 1.95],
                    ['key' => 'L_RED', 'label' => 'Lion Red', 'odds' => 1.95],
                ],
            ],
            'baccarat' => [
                'layout' => 'cards_ab',
                'sideA' => 'PLAYER',
                'sideB' => 'BANKER',
                'subtitle' => 'Official 8-Deck Live Baccarat Rules',
                'markets' => [
                    ['key' => 'PLAYER', 'label' => 'Player', 'odds' => 2.00, 'lay' => 2.04],
                    ['key' => 'BANKER', 'label' => 'Banker', 'odds' => 1.95, 'lay' => 2.00],
                    ['key' => 'TIE', 'label' => 'Tie', 'odds' => 9.00],
                    ['key' => 'PLAYER_PAIR', 'label' => 'Player Pair', 'odds' => 11.00],
                    ['key' => 'BANKER_PAIR', 'label' => 'Banker Pair', 'odds' => 11.00],
                    ['key' => 'BIG', 'label' => 'Big (5-6 Cards)', 'odds' => 1.54],
                    ['key' => 'SMALL', 'label' => 'Small (4 Cards)', 'odds' => 2.50],
                ],
            ],
            'andarbahar' => [
                'layout' => 'andarbahar',
                'subtitle' => 'Joker Match Dealing to Andar & Bahar',
                'markets' => [
                    ['key' => 'ANDAR', 'label' => 'Andar', 'odds' => 1.95, 'lay' => 2.00],
                    ['key' => 'BAHAR', 'label' => 'Bahar', 'odds' => 2.00, 'lay' => 2.05],
                    ['key' => 'JOKER_ODD', 'label' => 'Joker Odd', 'odds' => 1.85],
                    ['key' => 'JOKER_EVEN', 'label' => 'Joker Even', 'odds' => 2.05],
                    ['key' => 'JOKER_RED', 'label' => 'Joker Red (♥♦)', 'odds' => 1.95],
                    ['key' => 'JOKER_BLACK', 'label' => 'Joker Black (♠♣)', 'odds' => 1.95],
                ],
            ],
            'cards32' => [
                'layout' => 'cards_4',
                'sides' => ['PLAYER 8 (+8)', 'PLAYER 9 (+9)', 'PLAYER 10 (+10)', 'PLAYER 11 (+11)'],
                'subtitle' => '32-Card Deck (6 to K) Base + Card Value',
                'markets' => [
                    ['key' => 'P8', 'label' => 'Player 8', 'odds' => 4.25, 'lay' => 4.50],
                    ['key' => 'P9', 'label' => 'Player 9', 'odds' => 3.45, 'lay' => 3.65],
                    ['key' => 'P10', 'label' => 'Player 10', 'odds' => 2.75, 'lay' => 2.90],
                    ['key' => 'P11', 'label' => 'Player 11', 'odds' => 2.15, 'lay' => 2.26],
                ],
            ],
            'poker' => [
                'layout' => 'poker',
                'sideA' => 'PLAYER A',
                'sideB' => 'PLAYER B',
                'subtitle' => 'Texas Hold\'em Heads-Up + 5 Community Cards',
                'markets' => [
                    ['key' => 'PLAYER_A', 'label' => 'Player A Wins', 'odds' => 1.98, 'lay' => 2.02],
                    ['key' => 'PLAYER_B', 'label' => 'Player B Wins', 'odds' => 1.98, 'lay' => 2.02],
                    ['key' => 'TIE', 'label' => 'Split Pot Tie', 'odds' => 18.00],
                    ['key' => 'TWO_PAIR_PLUS', 'label' => 'Winning Hand Two Pair+', 'odds' => 2.20],
                    ['key' => 'FLUSH_PLUS', 'label' => 'Winning Hand Flush+', 'odds' => 5.50],
                ],
            ],
            'aaa' => [
                'layout' => 'single_card',
                'sideA' => 'AMAR AKBAR ANTHONY',
                'subtitle' => 'Amar (A-6) | Akbar (7-10) | Anthony (J-K)',
                'markets' => [
                    ['key' => 'AMAR', 'label' => 'Amar (A - 6)', 'odds' => 2.12, 'lay' => 2.20],
                    ['key' => 'AKBAR', 'label' => 'Akbar (7 - 10)', 'odds' => 3.15, 'lay' => 3.30],
                    ['key' => 'ANTHONY', 'label' => 'Anthony (J - K)', 'odds' => 4.15, 'lay' => 4.35],
                    ['key' => 'EVEN', 'label' => 'Even', 'odds' => 2.10],
                    ['key' => 'ODD', 'label' => 'Odd', 'odds' => 1.82],
                    ['key' => 'RED', 'label' => 'Red (♥ ♦)', 'odds' => 1.95],
                    ['key' => 'BLACK', 'label' => 'Black (♠ ♣)', 'odds' => 1.95],
                    ['key' => 'UNDER_7', 'label' => 'Under 7', 'odds' => 2.00],
                    ['key' => 'OVER_7', 'label' => 'Over 7', 'odds' => 2.00],
                ],
            ],
            'bollywood' => [
                'layout' => 'single_card',
                'sideA' => 'BOLLYWOOD CARD',
                'subtitle' => 'J, Q, K, A Movie Table (16-Card Deck)',
                'markets' => [
                    ['key' => 'DON', 'label' => 'A. Don (♠A / ♣A)', 'odds' => 7.50],
                    ['key' => 'AAA', 'label' => 'B. Amar Akbar Anthony (♥A / ♦A)', 'odds' => 7.50],
                    ['key' => 'SAHIB', 'label' => 'C. Sahib Bibi Aur Ghulam (King)', 'odds' => 3.75],
                    ['key' => 'DHARAM', 'label' => 'D. Dharam Veer (Queen)', 'odds' => 3.75],
                    ['key' => 'KKPK', 'label' => 'E. Kis Kisko Pyaar Karoon (Jack)', 'odds' => 3.75],
                    ['key' => 'ODD', 'label' => 'Odd (J / K)', 'odds' => 1.95],
                    ['key' => 'EVEN', 'label' => 'Even (Q / A)', 'odds' => 1.95],
                    ['key' => 'RED', 'label' => 'Red (♥ ♦)', 'odds' => 1.95],
                    ['key' => 'BLACK', 'label' => 'Black (♠ ♣)', 'odds' => 1.95],
                ],
            ],
            'judgement' => [
                'layout' => 'cards_3',
                'sides' => ['CARD 1', 'CARD 2', 'CARD 3'],
                'subtitle' => '3 Card Judgement - Predict Rank Group Hit',
                'markets' => [
                    ['key' => 'YES_LOW', 'label' => 'YES: Any A, 2, 3, 4 Dealt', 'odds' => 1.55],
                    ['key' => 'NO_LOW', 'label' => 'NO: None of A, 2, 3, 4 Dealt', 'odds' => 2.45],
                    ['key' => 'YES_FACE', 'label' => 'YES: Any J, Q, K Dealt', 'odds' => 1.75],
                    ['key' => 'NO_FACE', 'label' => 'NO: No J, Q, K Dealt', 'odds' => 2.10],
                    ['key' => 'RED_MAJ', 'label' => '2+ Red Cards', 'odds' => 1.95],
                    ['key' => 'BLACK_MAJ', 'label' => '2+ Black Cards', 'odds' => 1.95],
                ],
            ],
            'casinowar' => [
                'layout' => 'casinowar',
                'subtitle' => '6 Player Seats vs Dealer High Card War',
                'markets' => [
                    ['key' => 'SEAT_1', 'label' => 'Player 1 Beats Dealer', 'odds' => 1.98],
                    ['key' => 'SEAT_2', 'label' => 'Player 2 Beats Dealer', 'odds' => 1.98],
                    ['key' => 'SEAT_3', 'label' => 'Player 3 Beats Dealer', 'odds' => 1.98],
                    ['key' => 'SEAT_4', 'label' => 'Player 4 Beats Dealer', 'odds' => 1.98],
                    ['key' => 'SEAT_5', 'label' => 'Player 5 Beats Dealer', 'odds' => 1.98],
                    ['key' => 'SEAT_6', 'label' => 'Player 6 Beats Dealer', 'odds' => 1.98],
                ],
            ],
            'cardrace' => [
                'layout' => 'cardrace',
                'subtitle' => '4-Suit Card Race - First Suit to 5 Cards Wins!',
                'markets' => [
                    ['key' => 'SPADES', 'label' => '♠ Spades Winner', 'odds' => 3.75, 'lay' => 4.00],
                    ['key' => 'HEARTS', 'label' => '♥ Hearts Winner', 'odds' => 3.75, 'lay' => 4.00],
                    ['key' => 'CLUBS', 'label' => '♣ Clubs Winner', 'odds' => 3.75, 'lay' => 4.00],
                    ['key' => 'DIAMONDS', 'label' => '♦ Diamonds Winner', 'odds' => 3.75, 'lay' => 4.00],
                ],
            ],
            'meter' => [
                'layout' => 'cards_ab',
                'sideA' => 'LOW ZONE (A - 8)',
                'sideB' => 'HIGH ZONE (9 - K)',
                'subtitle' => 'Casino Meter Run Accumulation Battle',
                'markets' => [
                    ['key' => 'LOW_ZONE', 'label' => 'Low Zone Wins', 'odds' => 1.98, 'lay' => 2.02],
                    ['key' => 'HIGH_ZONE', 'label' => 'High Zone Wins', 'odds' => 1.98, 'lay' => 2.02],
                ],
            ],
            'kbc' => [
                'layout' => 'single_card',
                'sideA' => 'KBC QUIZ CARD',
                'subtitle' => 'Kaun Banega Crorepati Card Quiz',
                'markets' => [
                    ['key' => 'Q1_RED', 'label' => 'Q1: Red Card (♥ ♦)', 'odds' => 1.95],
                    ['key' => 'Q1_BLACK', 'label' => 'Q1: Black Card (♠ ♣)', 'odds' => 1.95],
                    ['key' => 'Q2_ODD', 'label' => 'Q2: Odd Rank', 'odds' => 1.85],
                    ['key' => 'Q2_EVEN', 'label' => 'Q2: Even Rank', 'odds' => 2.05],
                    ['key' => 'Q3_UP7', 'label' => 'Q3: 7 & Up (7 - K)', 'odds' => 1.85],
                    ['key' => 'Q3_DOWN7', 'label' => 'Q3: Below 7 (A - 6)', 'odds' => 2.05],
                    ['key' => 'Q4_SPADE', 'label' => 'Q4: Suit ♠ Spades', 'odds' => 3.80],
                    ['key' => 'Q4_HEART', 'label' => 'Q4: Suit ♥ Hearts', 'odds' => 3.80],
                ],
            ],
            'roulette' => [
                'layout' => 'roulette',
                'subtitle' => 'European Single-Zero Live Roulette (0 - 36)',
                'markets' => array_merge(
                    [
                        ['key' => 'RED', 'label' => 'RED', 'odds' => 2.00],
                        ['key' => 'BLACK', 'label' => 'BLACK', 'odds' => 2.00],
                        ['key' => 'EVEN', 'label' => 'EVEN', 'odds' => 2.00],
                        ['key' => 'ODD', 'label' => 'ODD', 'odds' => 2.00],
                        ['key' => 'LOW_18', 'label' => '1 to 18', 'odds' => 2.00],
                        ['key' => 'HIGH_18', 'label' => '19 to 36', 'odds' => 2.00],
                        ['key' => 'DOZ_1', 'label' => '1st 12 (1-12)', 'odds' => 3.00],
                        ['key' => 'DOZ_2', 'label' => '2nd 12 (13-24)', 'odds' => 3.00],
                        ['key' => 'DOZ_3', 'label' => '3rd 12 (25-36)', 'odds' => 3.00],
                    ],
                    array_map(fn ($n) => ['key' => "NUM_{$n}", 'label' => "Straight #{$n}", 'odds' => 36.00], range(0, 36))
                ),
            ],
            'blackjack' => [
                'layout' => 'cards_ab',
                'sideA' => 'PLAYER HAND (21)',
                'sideB' => 'DEALER HAND (17+)',
                'subtitle' => 'Live Casino 21 Blackjack Showdown',
                'markets' => [
                    ['key' => 'PLAYER_WIN', 'label' => 'Player Beats Dealer', 'odds' => 2.00],
                    ['key' => 'DEALER_WIN', 'label' => 'Dealer Wins', 'odds' => 1.95],
                    ['key' => 'PLAYER_BJ', 'label' => 'Natural 20/21 Hand', 'odds' => 4.20],
                    ['key' => 'DEALER_BUST', 'label' => 'Dealer Bust (>21)', 'odds' => 3.40],
                ],
            ],
            'sicbo' => [
                'layout' => 'sicbo',
                'subtitle' => '3-Dice Live Sic Bo Shaker',
                'markets' => [
                    ['key' => 'SMALL', 'label' => 'SMALL (4 - 10)', 'odds' => 2.00],
                    ['key' => 'BIG', 'label' => 'BIG (11 - 17)', 'odds' => 2.00],
                    ['key' => 'ODD', 'label' => 'ODD Sum', 'odds' => 2.00],
                    ['key' => 'EVEN', 'label' => 'EVEN Sum', 'odds' => 2.00],
                    ['key' => 'ANY_TRIPLE', 'label' => 'Any Triple (111-666)', 'odds' => 30.00],
                    ['key' => 'DOUBLE_ANY', 'label' => 'At Least One Pair', 'odds' => 2.20],
                ],
            ],
            'fantan' => [
                'layout' => 'fantan',
                'subtitle' => 'Traditional Bead Cup Remainder (Mod 4)',
                'markets' => [
                    ['key' => 'FAN_1', 'label' => '1 Fan', 'odds' => 3.85],
                    ['key' => 'FAN_2', 'label' => '2 Fan', 'odds' => 3.85],
                    ['key' => 'FAN_3', 'label' => '3 Fan', 'odds' => 3.85],
                    ['key' => 'FAN_4', 'label' => '4 Fan', 'odds' => 3.85],
                    ['key' => 'ODD', 'label' => 'Odd (1 or 3)', 'odds' => 1.95],
                    ['key' => 'EVEN', 'label' => 'Even (2 or 4)', 'odds' => 1.95],
                ],
            ],
            'slot' => [
                'layout' => 'slot',
                'subtitle' => '5-Reel × 3-Row Video Slot • 20 Paylines • Wilds & Free Spins',
                'symbols' => $this->getSlotSymbols($game->event_name),
                'markets' => [
                    ['key' => 'SPIN', 'label' => '20-Line Video Slot Spin', 'odds' => 1.00],
                ],
            ],
            default => [
                'layout' => 'cards_ab',
                'sideA' => 'PLAYER A',
                'sideB' => 'PLAYER B',
                'subtitle' => 'Live Studio Table',
                'markets' => [
                    ['key' => 'PLAYER_A', 'label' => 'Player A', 'odds' => 1.98, 'lay' => 2.02],
                    ['key' => 'PLAYER_B', 'label' => 'Player B', 'odds' => 1.98, 'lay' => 2.02],
                ],
            ],
        };
    }

    /**
     * Play a round of ANY casino game and compute exact outcome, visual payload & payout!
     */
    public function playRound(CasinoGame $game, string $selectionKey, float $stake, string $side = 'BACK', ?float $targetMultiplier = null): array
    {
        $type = $game->game_type;
        $def = $this->getTableDefinition($game);

        // Find market odds
        $market = collect($def['markets'])->firstWhere('key', $selectionKey) ?? $def['markets'][0];
        $odds = $side === 'LAY' ? (float) ($market['lay'] ?? ($market['odds'] + 0.04)) : (float) ($market['odds'] ?? 2.00);

        $roundId = 'R' . now()->format('ymdHis') . random_int(100, 999);

        return match ($type) {
            'crash' => $this->playCrash($roundId, $stake, $targetMultiplier ?: ($market['odds'] ?? 2.00)),
            'balloon' => $this->playBalloon($roundId, $stake, $targetMultiplier ?: ($market['odds'] ?? 2.00)),
            'coinflip' => $this->playCoinFlip($roundId, $selectionKey, $stake, $odds, $side),
            'wheel' => $this->playWheel($roundId, $selectionKey, $stake, $odds),
            'lucky09', 'worli' => $this->playLucky09($roundId, $selectionKey, $stake, $odds, $type === 'worli'),
            'teenpatti' => $this->playTeenPatti($roundId, $game->event_name, $selectionKey, $stake, $odds, $side),
            'lucky7' => $this->playLucky7($roundId, $selectionKey, $stake, $odds, $side),
            'dragontiger' => $this->playDragonTiger($roundId, $selectionKey, $stake, $odds, $side),
            'dtl' => $this->playDtl($roundId, $selectionKey, $stake, $odds, $side),
            'baccarat' => $this->playBaccarat($roundId, $selectionKey, $stake, $odds, $side),
            'andarbahar' => $this->playAndarBahar($roundId, $selectionKey, $stake, $odds, $side),
            'cards32' => $this->playCards32($roundId, $selectionKey, $stake, $odds, $side),
            'poker' => $this->playPoker($roundId, $selectionKey, $stake, $odds, $side),
            'aaa' => $this->playAaa($roundId, $selectionKey, $stake, $odds, $side),
            'bollywood' => $this->playBollywood($roundId, $selectionKey, $stake, $odds),
            'judgement' => $this->playJudgement($roundId, $selectionKey, $stake, $odds),
            'casinowar' => $this->playCasinoWar($roundId, $selectionKey, $stake, $odds),
            'cardrace' => $this->playCardRace($roundId, $selectionKey, $stake, $odds, $side),
            'meter' => $this->playMeter($roundId, $selectionKey, $stake, $odds, $side),
            'kbc' => $this->playKbc($roundId, $selectionKey, $stake, $odds),
            'roulette' => $this->playRoulette($roundId, $selectionKey, $stake, $odds),
            'blackjack' => $this->playBlackjack($roundId, $selectionKey, $stake, $odds),
            'sicbo' => $this->playSicBo($roundId, $selectionKey, $stake, $odds),
            'fantan' => $this->playFanTan($roundId, $selectionKey, $stake, $odds),
            'slot' => $this->playSlot($roundId, $game->event_name, $stake),
            default => $this->playTeenPatti($roundId, $game->event_name, $selectionKey, $stake, $odds, $side),
        };
    }

    private function freshDeck(): array
    {
        $deck = [];
        foreach ($this->suits as $suit) {
            foreach ($this->ranks as $val => $lbl) {
                $deck[] = [
                    'rank' => $val,
                    'label' => $lbl,
                    'suit' => $suit,
                    'color' => in_array($suit, ['♥', '♦']) ? 'RED' : 'BLACK',
                    'code' => $lbl . $suit,
                ];
            }
        }
        shuffle($deck);
        return $deck;
    }

    private function settleBackLay(bool $selectionHappened, float $stake, float $odds, string $side): array
    {
        if ($side === 'LAY') {
            $liability = round($stake * ($odds - 1), 2);
            if (!$selectionHappened) {
                return ['won' => true, 'net_pl' => round($stake, 2), 'payout' => round($liability + $stake, 2), 'liability' => $liability];
            }
            return ['won' => false, 'net_pl' => -$liability, 'payout' => 0.0, 'liability' => $liability];
        }
        if ($selectionHappened) {
            $profit = round($stake * ($odds - 1), 2);
            return ['won' => true, 'net_pl' => $profit, 'payout' => round($stake + $profit, 2), 'liability' => $stake];
        }
        return ['won' => false, 'net_pl' => -$stake, 'payout' => 0.0, 'liability' => $stake];
    }

    private function playCrash(string $roundId, float $stake, float $targetCashout): array
    {
        $target = max(1.05, min(500.0, round($targetCashout, 2)));
        $r = random_int(1, 10000) / 10000;
        // 3% instant 1.00x house edge, heavy tail multiplier
        $crashAt = $r < 0.03 ? 1.00 : round(max(1.01, min(250.0, 0.97 / (1 - $r))), 2);
        $won = $crashAt >= $target;
        $netPl = $won ? round($stake * ($target - 1), 2) : -$stake;

        return [
            'round_id' => $roundId,
            'won' => $won,
            'odds' => $target,
            'stake' => $stake,
            'liability' => $stake,
            'net_pl' => $netPl,
            'summary' => $won ? "Flew to {$crashAt}x (Cashed out @ {$target}x)" : "Flew away @ {$crashAt}x",
            'badge' => $crashAt . 'x',
            'visual' => [
                'type' => 'crash',
                'crash_at' => $crashAt,
                'cashout_at' => $target,
            ],
        ];
    }

    private function playBalloon(string $roundId, float $stake, float $targetCashout): array
    {
        $target = max(1.10, min(100.0, round($targetCashout, 2)));
        $r = random_int(1, 10000) / 10000;
        $burstAt = $r < 0.04 ? 1.02 : round(max(1.05, min(100.0, 0.96 / (1 - $r))), 2);
        $won = $burstAt >= $target;
        $netPl = $won ? round($stake * ($target - 1), 2) : -$stake;

        return [
            'round_id' => $roundId,
            'won' => $won,
            'odds' => $target,
            'stake' => $stake,
            'liability' => $stake,
            'net_pl' => $netPl,
            'summary' => $won ? "Balloon pumped to {$target}x (Popped @ {$burstAt}x)" : "Balloon popped @ {$burstAt}x!",
            'badge' => $burstAt . 'x',
            'visual' => [
                'type' => 'balloon',
                'burst_at' => $burstAt,
                'cashout_at' => $target,
            ],
        ];
    }

    private function playCoinFlip(string $roundId, string $sel, float $stake, float $odds, string $side): array
    {
        $outcome = random_int(0, 1) === 0 ? 'HEADS' : 'TAILS';
        $hit = ($sel === $outcome);
        $s = $this->settleBackLay($hit, $stake, $odds, $side);

        return [
            'round_id' => $roundId,
            'won' => $s['won'],
            'odds' => $odds,
            'stake' => $stake,
            'liability' => $s['liability'],
            'net_pl' => $s['net_pl'],
            'summary' => "Coin landed on {$outcome}",
            'badge' => $outcome[0],
            'visual' => ['type' => 'coinflip', 'outcome' => $outcome],
        ];
    }

    private function playWheel(string $roundId, string $sel, float $stake, float $odds): array
    {
        $segments = array_merge(
            array_fill(0, 23, 1),
            array_fill(0, 15, 2),
            array_fill(0, 7, 5),
            array_fill(0, 4, 10),
            array_fill(0, 2, 20),
            [40]
        );
        $landed = $segments[array_rand($segments)];
        $hit = ($sel === "SEG_{$landed}");
        $actualOdds = $landed + 1;
        $netPl = $hit ? round($stake * ($actualOdds - 1), 2) : -$stake;

        return [
            'round_id' => $roundId,
            'won' => $hit,
            'odds' => $odds,
            'stake' => $stake,
            'liability' => $stake,
            'net_pl' => $netPl,
            'summary' => "Dream Catcher stopped on {$landed}x",
            'badge' => $landed . 'x',
            'visual' => ['type' => 'wheel', 'segment' => $landed],
        ];
    }

    private function playLucky09(string $roundId, string $sel, float $stake, float $odds, bool $isWorli = false): array
    {
        $deck = $this->freshDeck();
        $cards = [$deck[0], $deck[1], $deck[2]];
        $digit = $isWorli
            ? (($cards[0]['rank'] + $cards[1]['rank'] + $cards[2]['rank']) % 10)
            : random_int(0, 9);

        $hit = match ($sel) {
            'LOW' => $digit <= 4,
            'HIGH' => $digit >= 5,
            'EVEN' => $digit % 2 === 0,
            'ODD' => $digit % 2 === 1,
            default => $sel === "NUM_{$digit}",
        };
        $netPl = $hit ? round($stake * ($odds - 1), 2) : -$stake;

        return [
            'round_id' => $roundId,
            'won' => $hit,
            'odds' => $odds,
            'stake' => $stake,
            'liability' => $stake,
            'net_pl' => $netPl,
            'summary' => ($isWorli ? "Worli Ank: {$digit}" : "Lucky Number: {$digit}"),
            'badge' => (string) $digit,
            'visual' => ['type' => 'lucky09', 'digit' => $digit, 'cards' => $isWorli ? $cards : []],
        ];
    }

    private function evalTeenPatti(array $hand): array
    {
        $ranks = array_map(fn ($c) => $c['rank'] === 1 ? 14 : $c['rank'], $hand);
        rsort($ranks);
        $suits = array_column($hand, 'suit');
        $flush = count(array_unique($suits)) === 1;
        $trail = ($ranks[0] === $ranks[1] && $ranks[1] === $ranks[2]);
        $seq = ($ranks[0] - $ranks[1] === 1 && $ranks[1] - $ranks[2] === 1) || ($ranks === [14, 3, 2]);
        if ($ranks === [14, 3, 2]) {
            $ranks = [14, 3, 2];
        }
        $pair = !$trail && ($ranks[0] === $ranks[1] || $ranks[1] === $ranks[2]);

        if ($trail) return ['tier' => 6, 'name' => 'Trail (Trio)', 'score' => 600000 + $ranks[0] * 100];
        if ($flush && $seq) return ['tier' => 5, 'name' => 'Pure Sequence', 'score' => 500000 + $ranks[0] * 100];
        if ($seq) return ['tier' => 4, 'name' => 'Sequence', 'score' => 400000 + $ranks[0] * 100];
        if ($flush) return ['tier' => 3, 'name' => 'Color (Flush)', 'score' => 300000 + $ranks[0] * 400 + $ranks[1] * 20 + $ranks[2]];
        if ($pair) {
            $pairRank = $ranks[0] === $ranks[1] ? $ranks[0] : $ranks[1];
            $kicker = $ranks[0] === $ranks[1] ? $ranks[2] : $ranks[0];
            return ['tier' => 2, 'name' => 'Pair', 'score' => 200000 + $pairRank * 100 + $kicker];
        }
        return ['tier' => 1, 'name' => 'High Card', 'score' => 100000 + $ranks[0] * 400 + $ranks[1] * 20 + $ranks[2]];
    }

    private function playTeenPatti(string $roundId, string $gameName, string $sel, float $stake, float $odds, string $side): array
    {
        $deck = $this->freshDeck();
        $handA = [$deck[0], $deck[2], $deck[4]];
        $handB = [$deck[1], $deck[3], $deck[5]];
        $evalA = $this->evalTeenPatti($handA);
        $evalB = $this->evalTeenPatti($handB);
        $isMuflis = str_contains(strtoupper($gameName), 'MUFLIS');

        $winner = $isMuflis
            ? ($evalA['score'] <= $evalB['score'] ? 'PLAYER_A' : 'PLAYER_B')
            : ($evalA['score'] >= $evalB['score'] ? 'PLAYER_A' : 'PLAYER_B');

        $hit = match ($sel) {
            'PLAYER_A' => $winner === 'PLAYER_A',
            'PLAYER_B' => $winner === 'PLAYER_B',
            'PAIR_PLUS_A' => $evalA['tier'] >= 2,
            'PAIR_PLUS_B' => $evalB['tier'] >= 2,
            'ANY_TRAIL' => $evalA['tier'] === 6 || $evalB['tier'] === 6,
            default => $winner === 'PLAYER_A',
        };

        $s = $this->settleBackLay($hit, $stake, $odds, $side);
        $winLabel = $winner === 'PLAYER_A' ? 'Player A' : 'Player B';

        return [
            'round_id' => $roundId,
            'won' => $s['won'],
            'odds' => $odds,
            'stake' => $stake,
            'liability' => $s['liability'],
            'net_pl' => $s['net_pl'],
            'summary' => "{$winLabel} Won ({$evalA['name']} vs {$evalB['name']})",
            'badge' => $winner === 'PLAYER_A' ? 'A' : 'B',
            'visual' => [
                'type' => 'cards_ab',
                'handA' => $handA,
                'handB' => $handB,
                'labelA' => "Player A ({$evalA['name']})",
                'labelB' => "Player B ({$evalB['name']})",
                'winner' => $winner,
            ],
        ];
    }

    private function playLucky7(string $roundId, string $sel, float $stake, float $odds, string $side): array
    {
        $deck = $this->freshDeck();
        $card = $deck[0];
        $r = $card['rank'];
        $outcome = $r < 7 ? 'LOW_CARD' : ($r > 7 ? 'HIGH_CARD' : 'SEVEN');

        $hit = match ($sel) {
            'LOW_CARD' => $r < 7,
            'HIGH_CARD' => $r > 7,
            'SEVEN' => $r === 7,
            'EVEN' => $r % 2 === 0,
            'ODD' => $r % 2 === 1,
            'RED' => $card['color'] === 'RED',
            'BLACK' => $card['color'] === 'BLACK',
            default => $sel === "CARD_{$r}",
        };
        $s = $this->settleBackLay($hit, $stake, $odds, $side);

        return [
            'round_id' => $roundId,
            'won' => $s['won'],
            'odds' => $odds,
            'stake' => $stake,
            'liability' => $s['liability'],
            'net_pl' => $s['net_pl'],
            'summary' => "Card {$card['code']} (" . ($r < 7 ? 'Low' : ($r > 7 ? 'High' : 'Lucky 7')) . ")",
            'badge' => $r < 7 ? 'L' : ($r > 7 ? 'H' : '7'),
            'visual' => ['type' => 'single_card', 'card' => $card, 'outcome' => $outcome],
        ];
    }

    private function playDragonTiger(string $roundId, string $sel, float $stake, float $odds, string $side): array
    {
        $deck = $this->freshDeck();
        $d = $deck[0];
        $t = $deck[1];
        $winner = $d['rank'] > $t['rank'] ? 'DRAGON' : ($t['rank'] > $d['rank'] ? 'TIGER' : 'TIE');

        $hit = match ($sel) {
            'DRAGON' => $winner === 'DRAGON',
            'TIGER' => $winner === 'TIGER',
            'TIE' => $winner === 'TIE',
            'PAIR' => $d['rank'] === $t['rank'],
            'DRAGON_EVEN' => $d['rank'] % 2 === 0,
            'DRAGON_ODD' => $d['rank'] % 2 === 1,
            'DRAGON_RED' => $d['color'] === 'RED',
            'DRAGON_BLACK' => $d['color'] === 'BLACK',
            'TIGER_RED' => $t['color'] === 'RED',
            'TIGER_BLACK' => $t['color'] === 'BLACK',
            default => $winner === 'DRAGON',
        };
        $s = $this->settleBackLay($hit, $stake, $odds, $side);

        return [
            'round_id' => $roundId,
            'won' => $s['won'],
            'odds' => $odds,
            'stake' => $stake,
            'liability' => $s['liability'],
            'net_pl' => $s['net_pl'],
            'summary' => "Dragon {$d['code']} vs Tiger {$t['code']} ({$winner})",
            'badge' => $winner[0],
            'visual' => ['type' => 'cards_ab', 'handA' => [$d], 'handB' => [$t], 'labelA' => 'Dragon', 'labelB' => 'Tiger', 'winner' => $winner],
        ];
    }

    private function playDtl(string $roundId, string $sel, float $stake, float $odds, string $side): array
    {
        $deck = $this->freshDeck();
        $d = $deck[0];
        $t = $deck[1];
        $l = $deck[2];
        $max = max($d['rank'], $t['rank'], $l['rank']);
        $winner = $d['rank'] === $max ? 'DRAGON' : ($t['rank'] === $max ? 'TIGER' : 'LION');

        $hit = match ($sel) {
            'DRAGON' => $winner === 'DRAGON',
            'TIGER' => $winner === 'TIGER',
            'LION' => $winner === 'LION',
            'D_RED' => $d['color'] === 'RED',
            'T_RED' => $t['color'] === 'RED',
            'L_RED' => $l['color'] === 'RED',
            default => $winner === 'DRAGON',
        };
        $s = $this->settleBackLay($hit, $stake, $odds, $side);

        return [
            'round_id' => $roundId,
            'won' => $s['won'],
            'odds' => $odds,
            'stake' => $stake,
            'liability' => $s['liability'],
            'net_pl' => $s['net_pl'],
            'summary' => "D:{$d['code']} | T:{$t['code']} | L:{$l['code']} -> {$winner}",
            'badge' => $winner[0],
            'visual' => ['type' => 'cards_3', 'cards' => [$d, $t, $l], 'labels' => ['Dragon', 'Tiger', 'Lion'], 'winner' => $winner],
        ];
    }

    private function baccVal(array $c): int
    {
        return $c['rank'] >= 10 ? 0 : $c['rank'];
    }

    private function playBaccarat(string $roundId, string $sel, float $stake, float $odds, string $side): array
    {
        $deck = $this->freshDeck();
        $p = [$deck[0], $deck[2]];
        $b = [$deck[1], $deck[3]];
        $pTot = ($this->baccVal($p[0]) + $this->baccVal($p[1])) % 10;
        $bTot = ($this->baccVal($b[0]) + $this->baccVal($b[1])) % 10;

        if ($pTot < 8 && $bTot < 8) {
            if ($pTot <= 5) {
                $p[] = $deck[4];
                $pTot = ($pTot + $this->baccVal($deck[4])) % 10;
            }
            if ($bTot <= 5) {
                $b[] = $deck[5];
                $bTot = ($bTot + $this->baccVal($deck[5])) % 10;
            }
        }
        $winner = $pTot > $bTot ? 'PLAYER' : ($bTot > $pTot ? 'BANKER' : 'TIE');
        $totalCards = count($p) + count($b);

        $hit = match ($sel) {
            'PLAYER' => $winner === 'PLAYER',
            'BANKER' => $winner === 'BANKER',
            'TIE' => $winner === 'TIE',
            'PLAYER_PAIR' => $p[0]['rank'] === $p[1]['rank'],
            'BANKER_PAIR' => $b[0]['rank'] === $b[1]['rank'],
            'BIG' => $totalCards >= 5,
            'SMALL' => $totalCards === 4,
            default => $winner === 'PLAYER',
        };
        $s = $this->settleBackLay($hit, $stake, $odds, $side);

        return [
            'round_id' => $roundId,
            'won' => $s['won'],
            'odds' => $odds,
            'stake' => $stake,
            'liability' => $s['liability'],
            'net_pl' => $s['net_pl'],
            'summary' => "Player {$pTot} vs Banker {$bTot} ({$winner})",
            'badge' => $winner[0],
            'visual' => ['type' => 'cards_ab', 'handA' => $p, 'handB' => $b, 'labelA' => "Player ({$pTot})", 'labelB' => "Banker ({$bTot})", 'winner' => $winner],
        ];
    }

    private function playAndarBahar(string $roundId, string $sel, float $stake, float $odds, string $side): array
    {
        $deck = $this->freshDeck();
        $joker = array_shift($deck);
        $andar = [];
        $bahar = [];
        $winner = 'ANDAR';
        $turn = 'ANDAR';

        foreach ($deck as $card) {
            if ($turn === 'ANDAR') {
                $andar[] = $card;
                if ($card['rank'] === $joker['rank']) {
                    $winner = 'ANDAR';
                    break;
                }
                $turn = 'BAHAR';
            } else {
                $bahar[] = $card;
                if ($card['rank'] === $joker['rank']) {
                    $winner = 'BAHAR';
                    break;
                }
                $turn = 'ANDAR';
            }
        }

        $hit = match ($sel) {
            'ANDAR' => $winner === 'ANDAR',
            'BAHAR' => $winner === 'BAHAR',
            'JOKER_ODD' => $joker['rank'] % 2 === 1,
            'JOKER_EVEN' => $joker['rank'] % 2 === 0,
            'JOKER_RED' => $joker['color'] === 'RED',
            'JOKER_BLACK' => $joker['color'] === 'BLACK',
            default => $winner === 'ANDAR',
        };
        $s = $this->settleBackLay($hit, $stake, $odds, $side);

        return [
            'round_id' => $roundId,
            'won' => $s['won'],
            'odds' => $odds,
            'stake' => $stake,
            'liability' => $s['liability'],
            'net_pl' => $s['net_pl'],
            'summary' => "Joker {$joker['code']} matched on {$winner} (" . (count($andar) + count($bahar)) . " cards)",
            'badge' => $winner[0],
            'visual' => ['type' => 'andarbahar', 'joker' => $joker, 'andar' => array_slice($andar, -5), 'bahar' => array_slice($bahar, -5), 'winner' => $winner],
        ];
    }

    private function playCards32(string $roundId, string $sel, float $stake, float $odds, string $side): array
    {
        $deck = array_values(array_filter($this->freshDeck(), fn ($c) => $c['rank'] >= 6));
        $c8 = $deck[0];
        $c9 = $deck[1];
        $c10 = $deck[2];
        $c11 = $deck[3];
        $totals = [
            'P8' => 8 + $c8['rank'],
            'P9' => 9 + $c9['rank'],
            'P10' => 10 + $c10['rank'],
            'P11' => 11 + $c11['rank'],
        ];
        arsort($totals);
        $winner = array_key_first($totals);
        $hit = ($sel === $winner);
        $s = $this->settleBackLay($hit, $stake, $odds, $side);

        return [
            'round_id' => $roundId,
            'won' => $s['won'],
            'odds' => $odds,
            'stake' => $stake,
            'liability' => $s['liability'],
            'net_pl' => $s['net_pl'],
            'summary' => "Winner {$winner} (Total {$totals[$winner]})",
            'badge' => str_replace('P', '', $winner),
            'visual' => ['type' => 'cards_4', 'cards' => [$c8, $c9, $c10, $c11], 'totals' => $totals, 'winner' => $winner],
        ];
    }

    private function playPoker(string $roundId, string $sel, float $stake, float $odds, string $side): array
    {
        $deck = $this->freshDeck();
        $holeA = [$deck[0], $deck[2]];
        $holeB = [$deck[1], $deck[3]];
        $board = [$deck[4], $deck[5], $deck[6], $deck[7], $deck[8]];

        $scoreA = $this->quickHoldemScore(array_merge($holeA, $board));
        $scoreB = $this->quickHoldemScore(array_merge($holeB, $board));
        $winner = $scoreA['score'] > $scoreB['score'] ? 'PLAYER_A' : ($scoreB['score'] > $scoreA['score'] ? 'PLAYER_B' : 'TIE');
        $bestTier = max($scoreA['tier'], $scoreB['tier']);

        $hit = match ($sel) {
            'PLAYER_A' => $winner === 'PLAYER_A',
            'PLAYER_B' => $winner === 'PLAYER_B',
            'TIE' => $winner === 'TIE',
            'TWO_PAIR_PLUS' => $bestTier >= 3,
            'FLUSH_PLUS' => $bestTier >= 6,
            default => $winner === 'PLAYER_A',
        };
        $s = $this->settleBackLay($hit, $stake, $odds, $side);

        return [
            'round_id' => $roundId,
            'won' => $s['won'],
            'odds' => $odds,
            'stake' => $stake,
            'liability' => $s['liability'],
            'net_pl' => $s['net_pl'],
            'summary' => "{$winner} ({$scoreA['name']} vs {$scoreB['name']})",
            'badge' => $winner === 'PLAYER_A' ? 'A' : ($winner === 'PLAYER_B' ? 'B' : 'T'),
            'visual' => ['type' => 'poker', 'holeA' => $holeA, 'holeB' => $holeB, 'board' => $board, 'winner' => $winner, 'handAName' => $scoreA['name'], 'handBName' => $scoreB['name']],
        ];
    }

    private function quickHoldemScore(array $sevenCards): array
    {
        $ranks = array_map(fn ($c) => $c['rank'] === 1 ? 14 : $c['rank'], $sevenCards);
        rsort($ranks);
        $counts = array_count_values($ranks);
        arsort($counts);
        $suitCounts = array_count_values(array_column($sevenCards, 'suit'));
        $isFlush = max($suitCounts) >= 5;
        $vals = array_values($counts);
        $keys = array_keys($counts);

        if ($vals[0] === 4) return ['tier' => 8, 'name' => 'Four of a Kind', 'score' => 800000 + $keys[0] * 100];
        if ($vals[0] === 3 && ($vals[1] ?? 0) >= 2) return ['tier' => 7, 'name' => 'Full House', 'score' => 700000 + $keys[0] * 100 + $keys[1]];
        if ($isFlush) return ['tier' => 6, 'name' => 'Flush', 'score' => 600000 + $ranks[0] * 100 + $ranks[1]];
        if ($vals[0] === 3) return ['tier' => 4, 'name' => 'Three of a Kind', 'score' => 400000 + $keys[0] * 100 + $keys[1]];
        if ($vals[0] === 2 && ($vals[1] ?? 0) === 2) return ['tier' => 3, 'name' => 'Two Pair', 'score' => 300000 + max($keys[0], $keys[1]) * 100 + min($keys[0], $keys[1])];
        if ($vals[0] === 2) return ['tier' => 2, 'name' => 'One Pair', 'score' => 200000 + $keys[0] * 100 + $keys[1]];
        return ['tier' => 1, 'name' => 'High Card', 'score' => 100000 + $ranks[0] * 100 + $ranks[1]];
    }

    private function playAaa(string $roundId, string $sel, float $stake, float $odds, string $side): array
    {
        $card = $this->freshDeck()[0];
        $r = $card['rank'];
        $group = $r <= 6 ? 'AMAR' : ($r <= 10 ? 'AKBAR' : 'ANTHONY');

        $hit = match ($sel) {
            'AMAR' => $r <= 6,
            'AKBAR' => $r >= 7 && $r <= 10,
            'ANTHONY' => $r >= 11,
            'EVEN' => $r % 2 === 0,
            'ODD' => $r % 2 === 1,
            'RED' => $card['color'] === 'RED',
            'BLACK' => $card['color'] === 'BLACK',
            'UNDER_7' => $r < 7,
            'OVER_7' => $r > 7,
            default => $group === 'AMAR',
        };
        $s = $this->settleBackLay($hit, $stake, $odds, $side);

        return [
            'round_id' => $roundId,
            'won' => $s['won'],
            'odds' => $odds,
            'stake' => $stake,
            'liability' => $s['liability'],
            'net_pl' => $s['net_pl'],
            'summary' => "Card {$card['code']} -> {$group}",
            'badge' => $group[0],
            'visual' => ['type' => 'single_card', 'card' => $card, 'outcome' => $group],
        ];
    }

    private function playBollywood(string $roundId, string $sel, float $stake, float $odds): array
    {
        $deck = array_values(array_filter($this->freshDeck(), fn ($c) => $c['rank'] === 1 || $c['rank'] >= 11));
        $card = $deck[0];
        $r = $card['rank'];
        $movie = $r === 1 ? ($card['color'] === 'BLACK' ? 'DON' : 'AAA') : ($r === 13 ? 'SAHIB' : ($r === 12 ? 'DHARAM' : 'KKPK'));

        $hit = match ($sel) {
            'DON', 'AAA', 'SAHIB', 'DHARAM', 'KKPK' => $sel === $movie,
            'ODD' => in_array($r, [11, 13]),
            'EVEN' => in_array($r, [12, 1]),
            'RED' => $card['color'] === 'RED',
            'BLACK' => $card['color'] === 'BLACK',
            default => $sel === $movie,
        };
        $netPl = $hit ? round($stake * ($odds - 1), 2) : -$stake;

        return [
            'round_id' => $roundId,
            'won' => $hit,
            'odds' => $odds,
            'stake' => $stake,
            'liability' => $stake,
            'net_pl' => $netPl,
            'summary' => "Dealt {$card['code']} ({$movie})",
            'badge' => $movie[0],
            'visual' => ['type' => 'single_card', 'card' => $card, 'outcome' => $movie],
        ];
    }

    private function playJudgement(string $roundId, string $sel, float $stake, float $odds): array
    {
        $deck = $this->freshDeck();
        $cards = [$deck[0], $deck[1], $deck[2]];
        $ranks = array_column($cards, 'rank');
        $reds = count(array_filter($cards, fn ($c) => $c['color'] === 'RED'));
        $hasLow = count(array_intersect($ranks, [1, 2, 3, 4])) > 0;
        $hasFace = count(array_intersect($ranks, [11, 12, 13])) > 0;

        $hit = match ($sel) {
            'YES_LOW' => $hasLow,
            'NO_LOW' => !$hasLow,
            'YES_FACE' => $hasFace,
            'NO_FACE' => !$hasFace,
            'RED_MAJ' => $reds >= 2,
            'BLACK_MAJ' => $reds <= 1,
            default => $hasLow,
        };
        $netPl = $hit ? round($stake * ($odds - 1), 2) : -$stake;

        return [
            'round_id' => $roundId,
            'won' => $hit,
            'odds' => $odds,
            'stake' => $stake,
            'liability' => $stake,
            'net_pl' => $netPl,
            'summary' => "3 Cards: {$cards[0]['code']} {$cards[1]['code']} {$cards[2]['code']}",
            'badge' => $hit ? 'W' : 'L',
            'visual' => ['type' => 'cards_3', 'cards' => $cards, 'labels' => ['Card 1', 'Card 2', 'Card 3'], 'winner' => $sel],
        ];
    }

    private function playCasinoWar(string $roundId, string $sel, float $stake, float $odds): array
    {
        $deck = $this->freshDeck();
        $seats = array_slice($deck, 0, 6);
        $dealer = $deck[6];
        $idx = max(0, min(5, ((int) str_replace('SEAT_', '', $sel)) - 1));
        $picked = $seats[$idx];
        $hit = ($picked['rank'] === 1 ? 14 : $picked['rank']) > ($dealer['rank'] === 1 ? 14 : $dealer['rank']);
        $netPl = $hit ? round($stake * ($odds - 1), 2) : -$stake;

        return [
            'round_id' => $roundId,
            'won' => $hit,
            'odds' => $odds,
            'stake' => $stake,
            'liability' => $stake,
            'net_pl' => $netPl,
            'summary' => "Seat " . ($idx + 1) . " ({$picked['code']}) vs Dealer ({$dealer['code']})",
            'badge' => $hit ? 'P' : 'D',
            'visual' => ['type' => 'casinowar', 'seats' => $seats, 'dealer' => $dealer, 'pickedSeat' => $idx + 1],
        ];
    }

    private function playCardRace(string $roundId, string $sel, float $stake, float $odds, string $side): array
    {
        $deck = $this->freshDeck();
        $pos = ['SPADES' => 0, 'HEARTS' => 0, 'CLUBS' => 0, 'DIAMONDS' => 0];
        $map = ['♠' => 'SPADES', '♥' => 'HEARTS', '♣' => 'CLUBS', '♦' => 'DIAMONDS'];
        $drawn = [];
        $winner = 'SPADES';

        foreach ($deck as $c) {
            $k = $map[$c['suit']];
            $pos[$k]++;
            $drawn[] = $c;
            if ($pos[$k] >= 5) {
                $winner = $k;
                break;
            }
        }
        $s = $this->settleBackLay($sel === $winner, $stake, $odds, $side);

        return [
            'round_id' => $roundId,
            'won' => $s['won'],
            'odds' => $odds,
            'stake' => $stake,
            'liability' => $s['liability'],
            'net_pl' => $s['net_pl'],
            'summary' => "{$winner} won the 5-step Card Race!",
            'badge' => $winner[0],
            'visual' => ['type' => 'cardrace', 'positions' => $pos, 'drawn' => array_slice($drawn, -8), 'winner' => $winner],
        ];
    }

    private function playMeter(string $roundId, string $sel, float $stake, float $odds, string $side): array
    {
        $deck = array_slice($this->freshDeck(), 0, 8);
        $lowPts = 0;
        $highPts = 0;
        $lowCards = [];
        $highCards = [];
        foreach ($deck as $c) {
            if ($c['rank'] <= 8) {
                $lowPts += $c['rank'];
                $lowCards[] = $c;
            } else {
                $highPts += $c['rank'];
                $highCards[] = $c;
            }
        }
        $winner = $lowPts >= $highPts ? 'LOW_ZONE' : 'HIGH_ZONE';
        $s = $this->settleBackLay($sel === $winner, $stake, $odds, $side);

        return [
            'round_id' => $roundId,
            'won' => $s['won'],
            'odds' => $odds,
            'stake' => $stake,
            'liability' => $s['liability'],
            'net_pl' => $s['net_pl'],
            'summary' => "Low {$lowPts} pts vs High {$highPts} pts",
            'badge' => $winner === 'LOW_ZONE' ? 'L' : 'H',
            'visual' => ['type' => 'cards_ab', 'handA' => $lowCards, 'handB' => $highCards, 'labelA' => "Low ({$lowPts})", 'labelB' => "High ({$highPts})", 'winner' => $winner],
        ];
    }

    private function playKbc(string $roundId, string $sel, float $stake, float $odds): array
    {
        $card = $this->freshDeck()[0];
        $r = $card['rank'];
        $hit = match ($sel) {
            'Q1_RED' => $card['color'] === 'RED',
            'Q1_BLACK' => $card['color'] === 'BLACK',
            'Q2_ODD' => $r % 2 === 1,
            'Q2_EVEN' => $r % 2 === 0,
            'Q3_UP7' => $r >= 7,
            'Q3_DOWN7' => $r < 7,
            'Q4_SPADE' => $card['suit'] === '♠',
            'Q4_HEART' => $card['suit'] === '♥',
            default => $card['color'] === 'RED',
        };
        $netPl = $hit ? round($stake * ($odds - 1), 2) : -$stake;

        return [
            'round_id' => $roundId,
            'won' => $hit,
            'odds' => $odds,
            'stake' => $stake,
            'liability' => $stake,
            'net_pl' => $netPl,
            'summary' => "KBC Card: {$card['code']}",
            'badge' => $card['label'],
            'visual' => ['type' => 'single_card', 'card' => $card, 'outcome' => $card['code']],
        ];
    }

    private function playRoulette(string $roundId, string $sel, float $stake, float $odds): array
    {
        $redNumbers = [1, 3, 5, 7, 9, 12, 14, 16, 18, 19, 21, 23, 25, 27, 30, 32, 34, 36];
        $num = random_int(0, 36);
        $color = $num === 0 ? 'GREEN' : (in_array($num, $redNumbers) ? 'RED' : 'BLACK');

        $hit = match ($sel) {
            'RED' => $color === 'RED',
            'BLACK' => $color === 'BLACK',
            'EVEN' => $num > 0 && $num % 2 === 0,
            'ODD' => $num > 0 && $num % 2 === 1,
            'LOW_18' => $num >= 1 && $num <= 18,
            'HIGH_18' => $num >= 19 && $num <= 36,
            'DOZ_1' => $num >= 1 && $num <= 12,
            'DOZ_2' => $num >= 13 && $num <= 24,
            'DOZ_3' => $num >= 25 && $num <= 36,
            default => $sel === "NUM_{$num}",
        };
        $netPl = $hit ? round($stake * ($odds - 1), 2) : -$stake;

        return [
            'round_id' => $roundId,
            'won' => $hit,
            'odds' => $odds,
            'stake' => $stake,
            'liability' => $stake,
            'net_pl' => $netPl,
            'summary' => "Ball landed on {$num} ({$color})",
            'badge' => (string) $num,
            'visual' => ['type' => 'roulette', 'number' => $num, 'color' => $color],
        ];
    }

    private function bjSum(array $cards): int
    {
        $sum = 0;
        $aces = 0;
        foreach ($cards as $c) {
            if ($c['rank'] === 1) {
                $aces++;
                $sum += 11;
            } else {
                $sum += min(10, $c['rank']);
            }
        }
        while ($sum > 21 && $aces > 0) {
            $sum -= 10;
            $aces--;
        }
        return $sum;
    }

    private function playBlackjack(string $roundId, string $sel, float $stake, float $odds): array
    {
        $deck = $this->freshDeck();
        $p = [$deck[0], $deck[2]];
        $d = [$deck[1], $deck[3]];
        $idx = 4;
        while ($this->bjSum($p) < 17) {
            $p[] = $deck[$idx++];
        }
        if ($this->bjSum($p) <= 21) {
            while ($this->bjSum($d) < 17) {
                $d[] = $deck[$idx++];
            }
        }
        $pSum = $this->bjSum($p);
        $dSum = $this->bjSum($d);
        $pWin = ($pSum <= 21 && ($dSum > 21 || $pSum > $dSum));

        $hit = match ($sel) {
            'PLAYER_WIN' => $pWin,
            'DEALER_WIN' => !$pWin && $pSum !== $dSum,
            'PLAYER_BJ' => count($p) === 2 && $pSum >= 20,
            'DEALER_BUST' => $dSum > 21,
            default => $pWin,
        };
        $netPl = $hit ? round($stake * ($odds - 1), 2) : -$stake;

        return [
            'round_id' => $roundId,
            'won' => $hit,
            'odds' => $odds,
            'stake' => $stake,
            'liability' => $stake,
            'net_pl' => $netPl,
            'summary' => "Player {$pSum} vs Dealer {$dSum}",
            'badge' => $pWin ? 'P' : 'D',
            'visual' => ['type' => 'cards_ab', 'handA' => $p, 'handB' => $d, 'labelA' => "Player ({$pSum})", 'labelB' => "Dealer ({$dSum})", 'winner' => $pWin ? 'PLAYER_WIN' : 'DEALER_WIN'],
        ];
    }

    private function playSicBo(string $roundId, string $sel, float $stake, float $odds): array
    {
        $d1 = random_int(1, 6);
        $d2 = random_int(1, 6);
        $d3 = random_int(1, 6);
        $sum = $d1 + $d2 + $d3;
        $triple = ($d1 === $d2 && $d2 === $d3);
        $pair = ($d1 === $d2 || $d2 === $d3 || $d1 === $d3);

        $hit = match ($sel) {
            'SMALL' => !$triple && $sum >= 4 && $sum <= 10,
            'BIG' => !$triple && $sum >= 11 && $sum <= 17,
            'ODD' => !$triple && $sum % 2 === 1,
            'EVEN' => !$triple && $sum % 2 === 0,
            'ANY_TRIPLE' => $triple,
            'DOUBLE_ANY' => $pair,
            default => !$triple && $sum >= 11,
        };
        $netPl = $hit ? round($stake * ($odds - 1), 2) : -$stake;

        return [
            'round_id' => $roundId,
            'won' => $hit,
            'odds' => $odds,
            'stake' => $stake,
            'liability' => $stake,
            'net_pl' => $netPl,
            'summary' => "Dice [{$d1}, {$d2}, {$d3}] = {$sum}",
            'badge' => (string) $sum,
            'visual' => ['type' => 'sicbo', 'dice' => [$d1, $d2, $d3], 'sum' => $sum],
        ];
    }

    private function playFanTan(string $roundId, string $sel, float $stake, float $odds): array
    {
        $beads = random_int(28, 60);
        $fan = ($beads % 4) ?: 4;
        $hit = match ($sel) {
            'FAN_1' => $fan === 1,
            'FAN_2' => $fan === 2,
            'FAN_3' => $fan === 3,
            'FAN_4' => $fan === 4,
            'ODD' => in_array($fan, [1, 3]),
            'EVEN' => in_array($fan, [2, 4]),
            default => $fan === 1,
        };
        $netPl = $hit ? round($stake * ($odds - 1), 2) : -$stake;

        return [
            'round_id' => $roundId,
            'won' => $hit,
            'odds' => $odds,
            'stake' => $stake,
            'liability' => $stake,
            'net_pl' => $netPl,
            'summary' => "{$beads} Beads -> {$fan} Fan",
            'badge' => "{$fan}F",
            'visual' => ['type' => 'fantan', 'beads' => $beads, 'fan' => $fan],
        ];
    }

    private function getSlotSymbols(string $name): array
    {
        $u = strtolower($name);
        if (str_contains($u, 'pharaoh') || str_contains($u, 'amon') || str_contains($u, 'sands') || str_contains($u, 'arabian')) {
            return ['👑', '🪲', '🏺', '👁️', '💎', '🅰️', '🅺', '🌟'];
        }
        if (str_contains($u, 'fruit') || str_contains($u, 'sweet') || str_contains($u, 'tropic') || str_contains($u, 'cocktail')) {
            return ['7️⃣', '🍉', '🍇', '🍊', '🍋', '🍒', '🔔', '⭐'];
        }
        if (str_contains($u, 'gem') || str_contains($u, 'stone') || str_contains($u, 'diamond') || str_contains($u, 'jewel')) {
            return ['💎', '🔮', '💠', '🔷', '🔶', '👑', '✨', '🌟'];
        }
        if (str_contains($u, 'pirate') || str_contains($u, 'sea') || str_contains($u, 'viking') || str_contains($u, 'warrior') || str_contains($u, 'gladiator')) {
            return ['🏴‍☠️', '⚔️', '🛡️', '💰', '🧭', '👑', '🔥', '⭐'];
        }
        return ['7️⃣', '💎', '👑', '🔔', '🍀', '🍒', '🔥', '⭐'];
    }

    private function playSlot(string $roundId, string $gameName, float $stake): array
    {
        $syms = $this->getSlotSymbols($gameName);
        // Realistic slot symbol weights (~95% RTP, ~34% hit frequency, balanced payouts)
        // Indices: 0=Top Jackpot, 1=High, 2=Mid-High, 3=Mid, 4=Low-Mid, 5=Low, 6=Wild (rare), 7=Scatter (rare)
        $weights = [3, 5, 7, 10, 14, 17, 2, 2];
        $pool = [];
        foreach ($syms as $i => $sym) {
            for ($w = 0; $w < ($weights[$i] ?? 10); $w++) {
                $pool[] = $i;
            }
        }

        // 5 columns x 3 rows
        $grid = [];
        for ($r = 0; $r < 3; $r++) {
            $row = [];
            for ($c = 0; $c < 5; $c++) {
                $idx = $pool[random_int(0, count($pool) - 1)];
                $row[] = ['idx' => $idx, 'sym' => $syms[$idx]];
            }
            $grid[] = $row;
        }

        $paylines = [
            [1, 1, 1, 1, 1], // middle
            [0, 0, 0, 0, 0], // top
            [2, 2, 2, 2, 2], // bottom
            [0, 1, 2, 1, 0], // V
            [2, 1, 0, 1, 2], // inverted V
            [0, 0, 1, 2, 2],
            [2, 2, 1, 0, 0],
            [1, 0, 0, 0, 1],
            [1, 2, 2, 2, 1],
            [0, 1, 1, 1, 0],
        ];
        // Realistic per-line multipliers scaled to total spin stake (stake split across 10 paylines)
        $payMultipliers = [
            0 => [3 => 1.50, 4 => 4.00, 5 => 12.00],
            1 => [3 => 1.00, 4 => 2.80, 5 => 8.00],
            2 => [3 => 0.75, 4 => 2.00, 5 => 5.50],
            3 => [3 => 0.50, 4 => 1.40, 5 => 4.00],
            4 => [3 => 0.35, 4 => 1.00, 5 => 2.80],
            5 => [3 => 0.25, 4 => 0.75, 5 => 2.00],
            6 => [3 => 1.25, 4 => 3.50, 5 => 10.00], // Wild
        ];

        $totalMultiplier = 0.0;
        $winningLines = [];

        foreach ($paylines as $lineNo => $coords) {
            $lineIdxs = [];
            for ($c = 0; $c < 5; $c++) {
                $lineIdxs[] = $grid[$coords[$c]][$c]['idx'];
            }
            $base = $lineIdxs[0];
            if ($base === 7) continue;
            if ($base === 6) {
                foreach ($lineIdxs as $li) {
                    if ($li !== 6 && $li !== 7) {
                        $base = $li;
                        break;
                    }
                }
            }
            $matchCount = 0;
            foreach ($lineIdxs as $li) {
                if ($li === $base || $li === 6) {
                    $matchCount++;
                } else {
                    break;
                }
            }
            if ($matchCount >= 3 && isset($payMultipliers[$base][$matchCount])) {
                $mult = $payMultipliers[$base][$matchCount];
                $totalMultiplier += $mult;
                $winningLines[] = [
                    'line' => $lineNo + 1,
                    'coords' => $coords,
                    'count' => $matchCount,
                    'symbol' => $syms[$base],
                    'multiplier' => $mult,
                ];
            }
        }

        // Check Scatter count anywhere
        $scatters = 0;
        foreach ($grid as $row) {
            foreach ($row as $cell) {
                if ($cell['idx'] === 7) $scatters++;
            }
        }
        if ($scatters >= 3) {
            $scatterBonus = $scatters === 3 ? 1.50 : ($scatters === 4 ? 4.00 : 10.00);
            $totalMultiplier += $scatterBonus;
        }

        // Cap maximum single-spin return to 15x stake so payouts stay realistic and controlled
        $totalMultiplier = round(min(15.0, $totalMultiplier), 2);
        $totalReturn = round($stake * $totalMultiplier, 2);
        $netPl = round($totalReturn - $stake, 2);
        $won = $netPl > 0;

        return [
            'round_id' => $roundId,
            'won' => $won,
            'odds' => max(0, $totalMultiplier),
            'stake' => $stake,
            'liability' => $stake,
            'net_pl' => $netPl,
            'summary' => $totalReturn > 0
                ? ($won ? "Won {$totalMultiplier}x (" . count($winningLines) . " lines)" : "Returned {$totalMultiplier}x (Net " . number_format($netPl, 2) . ")")
                : "Spin Complete - No Win",
            'badge' => $totalReturn > 0 ? "{$totalMultiplier}x" : '0x',
            'visual' => [
                'type' => 'slot',
                'grid' => $grid,
                'winningLines' => $winningLines,
                'scatters' => $scatters,
                'totalMultiplier' => $totalMultiplier,
                'totalReturn' => $totalReturn,
            ],
        ];
    }

    /**
     * Evaluate whether a specific selection key hit against a single dealt round's visual outcome,
     * so multiple bets placed during the same timer window settle consistently against the same dealt cards/spin.
     */
    public function didSelectionHitOutcome(array $outcome, string $selectionKey): bool
    {
        $vis = $outcome['visual'] ?? [];
        $vType = (string) ($vis['type'] ?? '');
        $sel = strtoupper(trim($selectionKey));

        if ($vType === 'roulette') {
            $num = (int) ($vis['number'] ?? 0);
            $color = strtoupper((string) ($vis['color'] ?? 'GREEN'));
            return match ($sel) {
                'RED' => $color === 'RED',
                'BLACK' => $color === 'BLACK',
                'EVEN' => $num > 0 && $num % 2 === 0,
                'ODD' => $num > 0 && $num % 2 === 1,
                'LOW_18' => $num >= 1 && $num <= 18,
                'HIGH_18' => $num >= 19 && $num <= 36,
                'DOZ_1' => $num >= 1 && $num <= 12,
                'DOZ_2' => $num >= 13 && $num <= 24,
                'DOZ_3' => $num >= 25 && $num <= 36,
                default => $sel === "NUM_{$num}",
            };
        }

        if ($vType === 'sicbo') {
            $dice = $vis['dice'] ?? [1, 2, 3];
            $sum = (int) ($vis['sum'] ?? array_sum($dice));
            $triple = count($dice) === 3 && $dice[0] === $dice[1] && $dice[1] === $dice[2];
            $pair = count($dice) === 3 && ($dice[0] === $dice[1] || $dice[1] === $dice[2] || $dice[0] === $dice[2]);
            return match ($sel) {
                'SMALL' => !$triple && $sum >= 4 && $sum <= 10,
                'BIG' => !$triple && $sum >= 11 && $sum <= 17,
                'ODD' => !$triple && $sum % 2 === 1,
                'EVEN' => !$triple && $sum % 2 === 0,
                'ANY_TRIPLE' => $triple,
                'DOUBLE_ANY' => $pair,
                default => false,
            };
        }

        if ($vType === 'fantan') {
            $fan = (int) ($vis['fan'] ?? 1);
            return match ($sel) {
                'FAN_1' => $fan === 1,
                'FAN_2' => $fan === 2,
                'FAN_3' => $fan === 3,
                'FAN_4' => $fan === 4,
                'ODD' => in_array($fan, [1, 3], true),
                'EVEN' => in_array($fan, [2, 4], true),
                default => false,
            };
        }

        if ($vType === 'single_card' && !empty($vis['card'])) {
            $card = $vis['card'];
            $r = (int) ($card['rank'] ?? 1);
            $color = strtoupper((string) ($card['color'] ?? 'BLACK'));
            $suit = (string) ($card['suit'] ?? '♠');
            $out = strtoupper((string) ($vis['outcome'] ?? ''));
            return match ($sel) {
                'AMAR' => $r <= 6,
                'AKBAR' => $r >= 7 && $r <= 10,
                'ANTHONY' => $r >= 11,
                'LOW_CARD', 'UNDER_7', 'Q3_DOWN7' => $r < 7,
                'HIGH_CARD', 'OVER_7' => $r > 7,
                'Q3_UP7' => $r >= 7,
                'SEVEN' => $r === 7,
                'EVEN', 'Q2_EVEN' => $r % 2 === 0,
                'ODD', 'Q2_ODD' => $r % 2 === 1,
                'RED', 'Q1_RED' => $color === 'RED',
                'BLACK', 'Q1_BLACK' => $color === 'BLACK',
                'Q4_SPADE' => $suit === '♠',
                'Q4_HEART' => $suit === '♥',
                default => ($sel === $out || $sel === "CARD_{$r}"),
            };
        }

        if ($vType === 'lucky09' && isset($vis['digit'])) {
            $d = (int) $vis['digit'];
            return match ($sel) {
                'LOW' => $d <= 4,
                'HIGH' => $d >= 5,
                'EVEN' => $d % 2 === 0,
                'ODD' => $d % 2 === 1,
                default => $sel === "NUM_{$d}",
            };
        }

        if ($vType === 'wheel' && isset($vis['landed'])) {
            return $sel === 'SEG_' . (int) $vis['landed'];
        }

        if ($vType === 'andarbahar' && isset($vis['winner'])) {
            $joker = $vis['joker'] ?? [];
            $r = (int) ($joker['rank'] ?? 1);
            $color = strtoupper((string) ($joker['color'] ?? 'RED'));
            return match ($sel) {
                'ANDAR', 'BAHAR' => $sel === strtoupper((string) $vis['winner']),
                'JOKER_ODD' => $r % 2 === 1,
                'JOKER_EVEN' => $r % 2 === 0,
                'JOKER_RED' => $color === 'RED',
                'JOKER_BLACK' => $color === 'BLACK',
                default => false,
            };
        }

        // Generic winner check (cards_ab, cards_3, cards_4, poker, coinflip, cardrace, etc.)
        $winner = strtoupper((string) ($vis['winner'] ?? $vis['outcome'] ?? ''));
        if ($winner !== '') {
            return $sel === $winner;
        }

        return (bool) ($outcome['won'] ?? false);
    }
}

