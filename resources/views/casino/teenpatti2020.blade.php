@extends('layouts.app')
@section('title', ($game->event_name ?? '20-20 TEENPATTI') . ' - CRICEXPERTS')

@php
  $stakeButtons = auth()->check() ? auth()->user()->getStakeButtons() : [
    ['label' => '100', 'value' => 100], ['label' => '500', 'value' => 500],
    ['label' => '1000', 'value' => 1000], ['label' => '5000', 'value' => 5000],
    ['label' => '10000', 'value' => 10000], ['label' => '25000', 'value' => 25000],
    ['label' => '50000', 'value' => 50000], ['label' => '100000', 'value' => 100000],
  ];

  $teenPattiMarkets = [
    [
      'key' => 'WINNER',
      'title' => 'WINNER',
      'min_max' => '100 - 500000',
      'min' => 100,
      'max' => 500000,
      'a_key' => 'PLAYER_A',
      'a_label' => 'PLAYER A',
      'a_odds' => 1.98,
      'a_vol' => 2330301,
      'b_key' => 'PLAYER_B',
      'b_label' => 'PLAYER B',
      'b_odds' => 1.98,
      'b_vol' => 2067139,
    ],
    [
      'key' => 'PAIR',
      'title' => 'PAIR ( DUBBLE ) 1:4',
      'min_max' => '100 - 100000',
      'min' => 100,
      'max' => 100000,
      'a_key' => 'PLAYER_A_PAIR',
      'a_label' => 'PLAYER A ( PAIR )',
      'a_odds' => 4,
      'a_vol' => 499800,
      'b_key' => 'PLAYER_B_PAIR',
      'b_label' => 'PLAYER B ( PAIR )',
      'b_odds' => 4,
      'b_vol' => 499800,
    ],
    [
      'key' => 'FLUSH',
      'title' => 'FLUSH ( COLOR ) 1:8',
      'min_max' => '100 - 100000',
      'min' => 100,
      'max' => 100000,
      'a_key' => 'PLAYER_A_FLUSH',
      'a_label' => 'PLAYER A ( FLUSH )',
      'a_odds' => 8,
      'a_vol' => 500000,
      'b_key' => 'PLAYER_B_FLUSH',
      'b_label' => 'PLAYER B ( FLUSH )',
      'b_odds' => 8,
      'b_vol' => 500000,
    ],
    [
      'key' => 'STRAIGHT',
      'title' => 'STRAIGHT ( ROWN ) 1:14',
      'min_max' => '100 - 100000',
      'min' => 100,
      'max' => 100000,
      'a_key' => 'PLAYER_A_STRAIGHT',
      'a_label' => 'PLAYER A ( STRAIGHT )',
      'a_odds' => 14,
      'a_vol' => 500000,
      'b_key' => 'PLAYER_B_STRAIGHT',
      'b_label' => 'PLAYER B ( STRAIGHT )',
      'b_odds' => 14,
      'b_vol' => 500000,
    ],
    [
      'key' => 'STRAIGHT_FLUSH',
      'title' => 'STRAIGHT FLUSH ( PAKKI ROWN ) 1:40',
      'min_max' => '100 - 100000',
      'min' => 100,
      'max' => 100000,
      'a_key' => 'PLAYER_A_SF',
      'a_label' => 'PLAYER A ( STRAIGHT FLUSH )',
      'a_odds' => 40,
      'a_vol' => 500000,
      'b_key' => 'PLAYER_B_SF',
      'b_label' => 'PLAYER B ( STRAIGHT FLUSH )',
      'b_odds' => 40,
      'b_vol' => 500000,
    ],
    [
      'key' => 'TRIO',
      'title' => 'TRIO ( TEEN ) 1:75',
      'min_max' => '100 - 100000',
      'min' => 100,
      'max' => 100000,
      'a_key' => 'PLAYER_A_TRIO',
      'a_label' => 'PLAYER A ( TRIO )',
      'a_odds' => 75,
      'a_vol' => 500000,
      'b_key' => 'PLAYER_B_TRIO',
      'b_label' => 'PLAYER B ( TRIO )',
      'b_odds' => 75,
      'b_vol' => 500000,
    ],
    [
      'key' => 'PUTLA_1',
      'title' => 'PUTLA ( 1 PICTURE IN GAME ) 1:0.70',
      'min_max' => '100 - 100000',
      'min' => 100,
      'max' => 100000,
      'a_key' => 'PLAYER_A_PUTLA1',
      'a_label' => 'PLAYER A ( PUTLA 1 )',
      'a_odds' => 1.7,
      'a_vol' => 999500,
      'b_key' => 'PLAYER_B_PUTLA1',
      'b_label' => 'PLAYER B ( PUTLA 1 )',
      'b_odds' => 1.7,
      'b_vol' => 1000000,
    ],
    [
      'key' => 'PUTLA_2',
      'title' => 'PUTLA ( 2 PICTURE IN GAME ) 1:4',
      'min_max' => '100 - 100000',
      'min' => 100,
      'max' => 100000,
      'a_key' => 'PLAYER_A_PUTLA2',
      'a_label' => 'PLAYER A ( PUTLA 2 )',
      'a_odds' => 4,
      'a_vol' => 1000000,
      'b_key' => 'PLAYER_B_PUTLA2',
      'b_label' => 'PLAYER B ( PUTLA 2 )',
      'b_odds' => 4,
      'b_vol' => 1000000,
    ],
    [
      'key' => 'PUTLA_3',
      'title' => 'PUTLA ( 3 PICTURE IN GAME ) 1:25',
      'min_max' => '100 - 100000',
      'min' => 100,
      'max' => 100000,
      'a_key' => 'PLAYER_A_PUTLA3',
      'a_label' => 'PLAYER A ( PUTLA 3 )',
      'a_odds' => 25,
      'a_vol' => 500000,
      'b_key' => 'PLAYER_B_PUTLA3',
      'b_label' => 'PLAYER B ( PUTLA 3 )',
      'b_odds' => 25,
      'b_vol' => 500000,
    ],
    [
      'key' => 'LOVE_MARRIAGE',
      'title' => 'LOVE MARRIAGE ( Q & K WITH SAME SUIT ) 1:25',
      'min_max' => '100 - 100000',
      'min' => 100,
      'max' => 100000,
      'a_key' => 'PLAYER_A_QK',
      'a_label' => 'PLAYER A ( QUEEN & KING )',
      'a_odds' => 25,
      'a_vol' => 500000,
      'b_key' => 'PLAYER_B_QK',
      'b_label' => 'PLAYER B ( QUEEN & KING )',
      'b_odds' => 25,
      'b_vol' => 499900,
    ],
    [
      'key' => 'BF_LOVE_GF',
      'title' => 'BF LOVE GF ( J & Q WITH SAME SUIT ) 1:25',
      'min_max' => '100 - 100000',
      'min' => 100,
      'max' => 100000,
      'a_key' => 'PLAYER_A_JQ',
      'a_label' => 'PLAYER A ( JACK & QUEEN )',
      'a_odds' => 25,
      'a_vol' => 500000,
      'b_key' => 'PLAYER_B_JQ',
      'b_label' => 'PLAYER B ( JACK & QUEEN )',
      'b_odds' => 25,
      'b_vol' => 500000,
    ],
  ];
@endphp

@push('styles')
<style>
  /* Exact 1:1 Styles from https://casino.winbook24.com/casino3Patti/99.0010 */
  body {
    background-color: #f4f4f4;
    font-family: Tahoma, Helvetica, Arial, sans-serif;
  }

  .tp-page-wrap {
    padding: 2px 7px 20px 7px;
  }

  .tp-row {
    display: flex;
    flex-wrap: wrap;
    margin: 0 -4px;
  }

  .tp-col-left {
    width: 16.66666667%;
    padding: 0 4px;
  }

  .tp-col-center {
    width: 57.33333333%;
    padding: 0 4px;
  }

  .tp-col-right {
    width: 26%;
    padding: 0 4px;
  }

  @media (max-width: 991.98px) {
    .tp-col-left {
      display: none;
    }
    .tp-col-center,
    .tp-col-right {
      width: 100%;
      margin-bottom: 10px;
    }
  }

  /* Left Sports Sidebar (Exact casino.winbook24.com look) */
  .tp-sport-box {
    background: #fff;
    border-bottom: 1px solid #d5d5d5;
  }

  .tp-sport-head {
    background: linear-gradient(180deg, #315195 0%, #14213D 100%);
    color: #fff;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 8px;
    font-size: 13px;
    font-weight: 700;
  }

  .tp-sport-list {
    list-style: none;
    margin: 0;
    padding: 0;
  }

  .tp-sport-list li a {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 5px 8px;
    font-size: 12px;
    color: #253b49;
    text-decoration: none;
    border-bottom: 1px solid #e2e2e2;
    background: #fff;
  }

  .tp-sport-list li a:hover {
    background: #eef6fb;
    color: #14213d;
  }

  .tp-sport-arrow {
    width: 16px;
    height: 16px;
    border: 1px solid #62971b;
    border-radius: 3px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #62971b;
    font-size: 9px;
  }

  /* Video / Universe Casino Stage */
  .tp-video-player {
    position: relative;
    background: #000000;
    width: 100%;
    text-align: center;
    overflow: hidden;
    user-select: none;
  }

  .tp-stage-gif {
    width: 75%;
    aspect-ratio: 16 / 9;
    height: auto;
    display: inline-block;
    vertical-align: middle;
  }

  /* Top-Left RID & Player A / Player B Cards Overlay */
  .tp-video-overlay {
    position: absolute;
    top: 0;
    left: 5px;
    text-align: left;
    width: 220px;
    background: rgba(0, 0, 0, 0.25);
    padding: 2px 4px;
    z-index: 5;
  }

  .tp-rid-line {
    font-size: 10px;
    color: #ffffff;
    font-weight: 700;
    margin: 0 0 1px 0;
    line-height: 1.25;
  }

  .tp-player-head {
    color: #ffffff;
    font-size: 12.5px;
    font-weight: 700;
    margin: 1px 0 2px 0;
    line-height: 1.15;
    text-transform: uppercase;
  }

  .tp-cards-row {
    display: flex;
    align-items: center;
    gap: 2px;
    margin-bottom: 2px;
  }

  .tp-card-img {
    width: 23px;
    height: auto;
    border-radius: 2px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.7);
    transition: transform 0.22s ease;
  }

  .tp-card-img.flipping {
    transform: rotateY(90deg) scale(1.06);
  }

  /* Top-Right Orange Cog & Countdown Timer */
  .tp-cog-btn {
    position: absolute;
    top: 8px;
    right: 8px;
    z-index: 6;
    cursor: pointer;
    color: #fd8f3b;
    font-size: 20px;
    line-height: 1;
    background: transparent;
    border: none;
    padding: 2px;
  }

  .tp-cog-btn:hover {
    color: #ffa95e;
  }

  .tp-timer-badge {
    position: absolute;
    bottom: 12px;
    right: 12px;
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: rgba(0, 0, 0, 0.78);
    border: 3px solid #72e3a0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 19px;
    font-weight: 800;
    z-index: 6;
    box-shadow: 0 0 12px rgba(114, 227, 160, 0.45);
    transition: opacity 0.2s ease, border-color 0.2s ease;
  }

  .tp-timer-badge.danger {
    border-color: #ff3b5c;
    color: #ffcad4;
    box-shadow: 0 0 14px rgba(255, 59, 92, 0.65);
  }

  .tp-timer-badge.hidden-timer {
    opacity: 0;
    pointer-events: none;
  }

  /* Winner Overlay Banner inside stage */
  .tp-winner-toast {
    position: absolute;
    bottom: 12px;
    left: 12px;
    background: linear-gradient(90deg, rgba(20, 33, 61, 0.92), rgba(46, 75, 94, 0.92));
    border: 1px solid #ffd54f;
    color: #ffd54f;
    font-size: 11px;
    font-weight: 800;
    padding: 4px 10px;
    border-radius: 4px;
    z-index: 6;
    display: none;
  }

  /* Market Sections Below Stage */
  .tp-market-block {
    margin-top: 4px;
  }

  .tp-market-header {
    background: linear-gradient(-180deg, #2E4B5E 0%, #243A48 82%);
    color: #ffffff;
    padding: 4px 8px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 11.5px;
    font-weight: 700;
    cursor: pointer;
    line-height: 1.3;
  }

  .tp-market-body {
    position: relative;
    background: linear-gradient(90deg, rgb(153, 199, 241) 0%, rgba(138, 189, 216, 0.5) 49%, rgb(146, 198, 246) 100%);
    padding: 6px 10px 10px 10px;
    display: flex;
    align-items: center;
    justify-content: space-around;
    border-bottom: 1px solid #b8d4ea;
  }

  .tp-runner-col {
    width: 48%;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
  }

  .tp-runner-title {
    font-size: 11.5px;
    font-weight: 700;
    color: #23282c;
    margin-bottom: 4px;
    text-transform: uppercase;
  }

  .tp-odds-box {
    width: 150px;
    max-width: 100%;
    background: #72bbef;
    border: none;
    border-radius: 5px;
    padding: 4px 6px;
    box-shadow: 0 2px 7px 1px rgba(103, 130, 139, 0.9);
    cursor: pointer;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    transition: transform 0.1s ease, filter 0.1s ease;
  }

  .tp-odds-box:hover {
    filter: brightness(1.05);
    transform: translateY(-1px);
  }

  .tp-odds-box.winner-pulse {
    box-shadow: 0 0 0 2px #16a34a, 0 2px 10px rgba(22, 163, 74, 0.65);
    background: #86efac;
  }

  .tp-odds-val {
    font-size: 14px;
    font-weight: 700;
    color: #1e1e1e;
    line-height: 1.2;
  }

  .tp-odds-vol {
    font-size: 9.5px;
    color: #2f353a;
    line-height: 1.1;
    margin-top: 1px;
  }

  .tp-book-pl {
    font-size: 10px;
    font-weight: 700;
    margin-top: 3px;
    min-height: 13px;
  }

  /* Exact Red SUSPENDED Overlay from media_1794296847391_a140136d.jpg */
  .tp-suspended-overlay {
    position: absolute;
    inset: 0;
    background-color: rgba(255, 255, 255, 0.5);
    border: 2px solid #ca1010;
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 4;
    pointer-events: none; /* Allows clicking odds box underneath if user wants to queue a bet! */
  }

  .tp-suspended-overlay.active {
    display: flex;
  }

  .tp-suspended-overlay span {
    font-size: 25px;
    font-weight: 700;
    color: #ca1010;
    letter-spacing: 0.5px;
    text-transform: uppercase;
  }

  /* Bottom Recent Result Bar */
  .tp-recent-bar {
    background: #000000;
    color: #ffffff;
    padding: 5px 8px;
    margin-top: 4px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 6px;
  }

  .tp-recent-title {
    font-size: 12.5px;
    font-weight: 700;
  }

  .tp-recent-balls {
    display: flex;
    align-items: center;
    gap: 4px;
    flex-wrap: wrap;
  }

  .tp-res-ball {
    width: 32px;
    height: 24px;
    border-radius: 15px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
    color: #000;
    cursor: pointer;
    user-select: none;
  }

  .tp-res-ball.ball-a {
    background: #72bbef;
  }

  .tp-res-ball.ball-b {
    background: #f9a9ba;
  }

  /* Right Sidebar: Open Bets & Bet Slip */
  .tp-openbets-box {
    background: #ffffff;
    border: 1px solid #d5d5d5;
  }

  .tp-openbets-head {
    background: linear-gradient(180deg, #315195 0%, #14213D 100%);
    color: #ffffff;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 8px;
    font-size: 12.5px;
    font-weight: 700;
  }

  .tp-slip-box {
    background: #d4e8f8;
    border: 1px solid #72bbef;
    padding: 8px;
    margin: 6px;
    border-radius: 4px;
    display: none;
  }

  .tp-slip-box.active {
    display: block;
  }

  .tp-stake-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 4px;
    margin-top: 6px;
  }

  .tp-stake-chip {
    background: #2E4B5E;
    color: #ffffff;
    border: 1px solid #1b2d38;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 700;
    padding: 4px 2px;
    cursor: pointer;
  }

  .tp-stake-chip:hover {
    background: #3b6078;
  }
</style>
@endpush

@section('full_bleed')
<div class="tp-page-wrap">
  <div class="tp-row">

    <!-- ================= LEFT COLUMN: SPORTS SIDEBAR (Exact 7 items from screenshot) ================= -->
    <div class="tp-col-left">
      <div class="tp-sport-box">
        <div class="tp-sport-head">
          <span><i class="fas fa-ellipsis-v"></i></span>
          <span>Sports</span>
        </div>
        <ul class="tp-sport-list">
          <li>
            <a href="{{ route('sport.highlight', '4') }}">
              <span>Cricket</span>
              <span class="tp-sport-arrow"><i class="fas fa-chevron-right"></i></span>
            </a>
          </li>
          <li>
            <a href="{{ route('sport.highlight', '2') }}">
              <span>Tennis</span>
              <span class="tp-sport-arrow"><i class="fas fa-chevron-right"></i></span>
            </a>
          </li>
          <li>
            <a href="{{ route('sport.highlight', '1') }}">
              <span>Soccer</span>
              <span class="tp-sport-arrow"><i class="fas fa-chevron-right"></i></span>
            </a>
          </li>
          <li>
            <a href="{{ route('horse.racing') }}">
              <span>Horse Racing</span>
              <span class="tp-sport-arrow"><i class="fas fa-chevron-right"></i></span>
            </a>
          </li>
          <li>
            <a href="{{ route('horse.racing', ['type' => 'greyhound']) }}">
              <span>Greyhound Racing</span>
              <span class="tp-sport-arrow"><i class="fas fa-chevron-right"></i></span>
            </a>
          </li>
          <li>
            <a href="{{ route('sport.highlight', '7522') }}">
              <span>Basketball</span>
              <span class="tp-sport-arrow"><i class="fas fa-chevron-right"></i></span>
            </a>
          </li>
          <li>
            <a href="{{ route('sport.highlight', '66104') }}">
              <span>Lottery</span>
              <span class="tp-sport-arrow"><i class="fas fa-chevron-right"></i></span>
            </a>
          </li>
        </ul>
      </div>
    </div>

    <!-- ================= CENTER COLUMN: 20-20 TEENPATTI STAGE & 11 MARKETS ================= -->
    <div class="tp-col-center">
      <div class="center-Main">

        <!-- Black 16:9 Universe Casino Stage -->
        <div class="tp-video-player">
          <img src="/assets/casino/loader.gif" class="tp-stage-gif" alt="Universe Casino">

          <!-- Top-Left RID + PLAYER A & PLAYER B 3-Card Overlay -->
          <div class="tp-video-overlay">
            <p class="tp-rid-line">RID: <span id="tpRoundIdText">9101011075147</span></p>
            <div>
              <h3 class="tp-player-head">PLAYER A</h3>
              <div class="tp-cards-row">
                <img id="tpCardA1" src="/assets/cards/S8_.png" class="tp-card-img" alt="A1">
                <img id="tpCardA2" src="/assets/cards/D6_.png" class="tp-card-img" alt="A2">
                <img id="tpCardA3" src="/assets/cards/0.png" class="tp-card-img" alt="A3">
              </div>
            </div>
            <div>
              <h3 class="tp-player-head">PLAYER B</h3>
              <div class="tp-cards-row">
                <img id="tpCardB1" src="/assets/cards/D9_.png" class="tp-card-img" alt="B1">
                <img id="tpCardB2" src="/assets/cards/H2_.png" class="tp-card-img" alt="B2">
                <img id="tpCardB3" src="/assets/cards/0.png" class="tp-card-img" alt="B3">
              </div>
            </div>
          </div>

          <!-- Top-Right Orange Gear Icon (Opens Quick Stake Settings Modal) -->
          <button type="button" class="tp-cog-btn" data-bs-toggle="modal" data-bs-target="#tpStakeModal" title="Edit Quick Stakes">
            <i class="fas fa-cog"></i>
          </button>

          <!-- Circular Countdown Timer -->
          <div id="tpTimerCircle" class="tp-timer-badge hidden-timer">20</div>

          <!-- Round Winner Toast -->
          <div id="tpWinnerBanner" class="tp-winner-toast">
            <i class="fas fa-trophy me-1"></i> <span id="tpWinnerBannerText">WINNER: PLAYER A</span>
          </div>
        </div>

        <!-- 11 Betting Markets (#marketScroll) -->
        <div id="marketScroll">
          @foreach($teenPattiMarkets as $idx => $m)
            <div class="tp-market-block" data-market-key="{{ $m['key'] }}">
              <div class="tp-market-header" data-bs-toggle="collapse" data-bs-target="#tpMktBody_{{ $idx }}" aria-expanded="true">
                <span>{{ $m['title'] }}</span>
                <span>Min/Max: {{ $m['min_max'] }}</span>
              </div>
              <div id="tpMktBody_{{ $idx }}" class="collapse show">
                <div class="tp-market-body">
                  <!-- Player A Runner -->
                  <div class="tp-runner-col">
                    <div class="tp-runner-title">{{ $m['a_label'] }}</div>
                    <button type="button"
                            id="tpOddsBtn_{{ $m['a_key'] }}"
                            class="tp-odds-box"
                            onclick="TP2020.openSlip('{{ $m['key'] }}', '{{ $m['title'] }}', '{{ $m['a_key'] }}', '{{ $m['a_label'] }}', {{ $m['a_odds'] }}, {{ $m['min'] }}, {{ $m['max'] }})">
                      <span class="tp-odds-val">{{ $m['a_odds'] }}</span>
                      <span class="tp-odds-vol" id="tpVol_{{ $m['a_key'] }}">{{ $m['a_vol'] }}</span>
                    </button>
                    <div class="tp-book-pl" id="tpBook_{{ $m['a_key'] }}"></div>
                  </div>

                  <!-- Player B Runner -->
                  <div class="tp-runner-col">
                    <div class="tp-runner-title">{{ $m['b_label'] }}</div>
                    <button type="button"
                            id="tpOddsBtn_{{ $m['b_key'] }}"
                            class="tp-odds-box"
                            onclick="TP2020.openSlip('{{ $m['key'] }}', '{{ $m['title'] }}', '{{ $m['b_key'] }}', '{{ $m['b_label'] }}', {{ $m['b_odds'] }}, {{ $m['min'] }}, {{ $m['max'] }})">
                      <span class="tp-odds-val">{{ $m['b_odds'] }}</span>
                      <span class="tp-odds-vol" id="tpVol_{{ $m['b_key'] }}">{{ $m['b_vol'] }}</span>
                    </button>
                    <div class="tp-book-pl" id="tpBook_{{ $m['b_key'] }}"></div>
                  </div>

                  <!-- Red SUSPENDED Overlay (Active during card reveal / dealing phase) -->
                  <div class="tp-suspended-overlay active js-tp-suspended">
                    <span>SUSPENDED</span>
                  </div>
                </div>
              </div>
            </div>
          @endforeach
        </div>

        <!-- Bottom Recent Result Bar -->
        <div class="tp-recent-bar">
          <div class="tp-recent-title">Recent Result</div>
          <div class="tp-recent-balls" id="tpRecentBalls">
            <!-- Populated dynamically with clickable A / B badges -->
          </div>
        </div>

      </div>
    </div>

    <!-- ================= RIGHT COLUMN: OPEN BETS & INTERACTIVE BET SLIP ================= -->
    <div class="tp-col-right">
      <div class="tp-openbets-box">
        <div class="tp-openbets-head">
          <span>Open Bets</span>
          <span class="badge bg-light text-dark" style="font-size:10px;" id="tpOpenBetsCount">{{ isset($openBets) ? $openBets->count() : 0 }} Active</span>
        </div>

        <div class="p-2 border-bottom">
          <select id="tpMarketFilter" class="form-select form-select-sm" style="font-size:12px;border-color:#c8ced3;" onchange="TP2020.filterOpenBets(this.value)">
            <option value="all">Select Market</option>
            @foreach($teenPattiMarkets as $m)
              <option value="{{ $m['key'] }}">{{ $m['title'] }}</option>
            @endforeach
          </select>
        </div>

        <!-- Interactive 3-Patti Bet Slip -->
        <div id="tpBetSlip" class="tp-slip-box">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <div>
              <span class="badge bg-primary" style="font-size:10px;">BACK</span>
              <strong id="tpSlipSelectionName" class="ms-1" style="font-size:12px;">PLAYER A</strong>
            </div>
            <button type="button" class="btn-close btn-sm" onclick="TP2020.closeSlip()"></button>
          </div>
          <div id="tpSlipMarketTitle" class="text-muted mb-2" style="font-size:10.5px;">20-20 TEENPATTI • WINNER</div>

          <div class="row g-2 align-items-center mb-2">
            <div class="col-6">
              <label class="small fw-bold d-block mb-1" style="font-size:11px;">Odds</label>
              <input type="text" id="tpSlipOdds" class="form-control form-control-sm text-center fw-bold bg-white" readonly value="1.98">
            </div>
            <div class="col-6">
              <label class="small fw-bold d-block mb-1" style="font-size:11px;">Stake (PTI)</label>
              <input type="number" id="tpSlipStake" class="form-control form-control-sm text-center fw-bold" value="100" min="10" step="10" oninput="TP2020.updateSlipCalc()">
            </div>
          </div>

          <div class="tp-stake-grid" id="tpQuickStakeGrid">
            @foreach($stakeButtons as $sb)
              <button type="button" class="tp-stake-chip" onclick="TP2020.setStake({{ $sb['value'] }})">+{{ $sb['label'] }}</button>
            @endforeach
          </div>

          <div class="d-flex justify-content-between small mt-2 pt-1 border-top border-secondary-subtle" style="font-size:11px;">
            <span>Profit: <strong class="text-success" id="tpSlipProfit">+98.00</strong></span>
            <span>Liability: <strong class="text-danger" id="tpSlipLiability">-100.00</strong></span>
          </div>

          <div id="tpSlipAlert" class="alert py-1 px-2 mt-2 mb-0 small fw-bold d-none" style="font-size:11px;"></div>

          <div class="d-flex justify-content-between gap-1 mt-2">
            <button type="button" class="btn btn-sm btn-secondary fw-bold flex-fill" style="font-size:11px;" onclick="TP2020.closeSlip()">Cancel</button>
            <button type="button" id="tpBtnPlaceBet" class="btn btn-sm btn-success fw-bold flex-fill" style="font-size:11px;" onclick="TP2020.submitBet(false)">Place Bet</button>
          </div>
        </div>

        <!-- Active & Recent Round Bets List -->
        <div id="tpOpenBetsContainer" class="p-2" style="max-height:420px;overflow-y:auto;">
          @if(isset($openBets) && $openBets->count() > 0)
            @foreach($openBets as $ob)
              <div class="p-2 mb-1 border-bottom small bg-info-subtle rounded" data-mkt="{{ $ob->market_name }}">
                <div class="d-flex justify-content-between fw-bold" style="font-size:11.5px;">
                  <span>{{ $ob->selection_name }} <span class="badge bg-dark">BACK</span></span>
                  <span>@ {{ number_format($ob->odds, 2) }}</span>
                </div>
                <div class="text-muted" style="font-size:10px;">{{ $ob->event_name }} • {{ $ob->market_name }}</div>
                <div class="d-flex justify-content-between mt-1" style="font-size:11px;">
                  <span>Stake: <b>{{ number_format($ob->stake, 2) }}</b></span>
                  <span class="text-success fw-bold">To Win: +{{ number_format($ob->potential_profit, 2) }}</span>
                </div>
              </div>
            @endforeach
          @else
            <div id="tpNoOpenBetsMsg" class="text-center text-muted py-3 small" style="font-size:11.5px;">
              Click on any Back odds box to place a 20-20 Teenpatti bet.
            </div>
          @endif
        </div>
      </div>
    </div>

  </div>
</div>

<!-- ================= QUICK STAKE SETTINGS MODAL (Gear Icon) ================= -->
<div class="modal fade" id="tpStakeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header py-2" style="background:#2f4553;color:#fff;">
        <h6 class="modal-title fw-bold mb-0" style="font-size:14px;">Stake</h6>
        <button type="button" class="btn-close btn-close-white btn-sm" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-3">
        <div class="row g-2" id="tpStakeModalInputs">
          @foreach($stakeButtons as $i => $sb)
            <div class="col-6">
              <input type="number" class="form-control form-control-sm fw-bold text-center js-tp-stake-input" value="{{ $sb['value'] }}">
            </div>
          @endforeach
        </div>
        <button type="button" class="btn btn-success w-100 fw-bold mt-3 btn-sm" onclick="TP2020.saveQuickStakes()" data-bs-dismiss="modal">
          Save
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ================= ROUND RESULT DETAIL MODAL (Clicking A / B in Recent Result) ================= -->
<div class="modal fade" id="tpResultModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header py-2" style="background:linear-gradient(-180deg, #2E4B5E 0%, #243A48 82%);color:#fff;">
        <h6 class="modal-title fw-bold mb-0" style="font-size:13.5px;">20-20 TEENPATTI RESULT</h6>
        <button type="button" class="btn-close btn-close-white btn-sm" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center p-3">
        <h6 class="fw-bold mb-3" style="font-size:13px;">Round Id: <span id="tpModalRoundId">9101011075147</span></h6>
        <div class="row align-items-center">
          <div class="col-6 border-end">
            <h6 class="fw-bold mb-2" style="font-size:13px;">
              Player A <span id="tpModalWinBadgeA" class="badge bg-success ms-1 d-none"><i class="fas fa-trophy"></i> WIN</span>
            </h6>
            <div class="d-flex justify-content-center gap-2 my-2" id="tpModalCardsA"></div>
            <div class="small text-muted fw-bold" id="tpModalDescA">High Card</div>
          </div>
          <div class="col-6">
            <h6 class="fw-bold mb-2" style="font-size:13px;">
              Player B <span id="tpModalWinBadgeB" class="badge bg-success ms-1 d-none"><i class="fas fa-trophy"></i> WIN</span>
            </h6>
            <div class="d-flex justify-content-center gap-2 my-2" id="tpModalCardsB"></div>
            <div class="small text-muted fw-bold" id="tpModalDescB">Pair</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  const TP2020 = (() => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const eventId = @json($game->event_id ?? '99.0010');

    // Initial state matches the exact user screenshot (media_1794296847391_a140136d.jpg):
    // Player A: 8♠ (S8_), 6♦ (D6_), Face-down (0)
    // Player B: 9♦ (D9_), 2♥ (H2_), Face-down (0)
    // Markets: SUSPENDED during initial 2.5s reveal, then opens 16s betting countdown!
    let roundId = '91010' + Math.floor(11075000 + Math.random() * 9000);
    let countdown = 0;
    let phase = 'DEALING'; // 'DEALING' (shows SUSPENDED) or 'BETTING' (active countdown)
    let currentSlip = null;
    let openRoundBets = [];
    let settlingInProgress = false;

    const recentHistory = [
      { roundId: '9101011075146', winner: 'B', cardsA: ['S8_', 'D6_', 'C3_'], cardsB: ['D9_', 'H2_', 'H9_'], descA: 'High Card (8)', descB: 'Pair (9s)' },
      { roundId: '9101011075145', winner: 'B', cardsA: ['C4_', 'D7_', 'HK_'], cardsB: ['S10_', 'SJ_', 'SQ_'], descA: 'High Card (K)', descB: 'Straight Flush' },
      { roundId: '9101011075144', winner: 'B', cardsA: ['H3_', 'S5_', 'D10_'], cardsB: ['CA_', 'DA_', 'C6_'], descA: 'High Card (10)', descB: 'Pair (Aces)' },
      { roundId: '9101011075143', winner: 'B', cardsA: ['D2_', 'C8_', 'HJ_'], cardsB: ['HK_', 'SK_', 'D4_'], descA: 'High Card (J)', descB: 'Pair (Kings)' },
      { roundId: '9101011075142', winner: 'A', cardsA: ['SA_', 'HA_', 'DA_'], cardsB: ['CQ_', 'DQ_', 'S9_'], descA: 'Trio (Aces)', descB: 'Pair (Queens)' },
      { roundId: '9101011075141', winner: 'B', cardsA: ['S4_', 'D5_', 'C9_'], cardsB: ['H7_', 'H8_', 'H9_'], descA: 'High Card (9)', descB: 'Straight Flush' },
      { roundId: '9101011075140', winner: 'B', cardsA: ['C2_', 'D6_', 'S10_'], cardsB: ['DJ_', 'HJ_', 'CA_'], descA: 'High Card (10)', descB: 'Pair (Jacks)' },
      { roundId: '9101011075139', winner: 'B', cardsA: ['H4_', 'S7_', 'CK_'], cardsB: ['C5_', 'D6_', 'S7_'], descA: 'High Card (K)', descB: 'Straight (5-6-7)' },
      { roundId: '9101011075138', winner: 'B', cardsA: ['D3_', 'H6_', 'SQ_'], cardsB: ['S2_', 'S8_', 'SA_'], descA: 'High Card (Q)', descB: 'Flush (Spades)' },
      { roundId: '9101011075137', winner: 'A', cardsA: ['HQ_', 'HK_', 'HA_'], cardsB: ['C10_', 'DK_', 'SA_'], descA: 'Straight Flush + Love Marriage', descB: 'High Card (A)' },
    ];

    function setCard(elId, cardCode) {
      const el = document.getElementById(elId);
      if (!el) return;
      const targetSrc = '/assets/cards/' + cardCode + '.png';
      if (el.getAttribute('src') === targetSrc) return;
      el.classList.add('flipping');
      setTimeout(() => {
        el.setAttribute('src', targetSrc);
        el.classList.remove('flipping');
      }, 60);
    }

    function setSuspended(isSuspended) {
      document.querySelectorAll('.js-tp-suspended').forEach(el => {
        el.classList.toggle('active', isSuspended);
      });
      const timerEl = document.getElementById('tpTimerCircle');
      if (timerEl) {
        timerEl.classList.toggle('hidden-timer', isSuspended);
      }
    }

    function renderRecentBalls() {
      const container = document.getElementById('tpRecentBalls');
      if (!container) return;
      container.innerHTML = recentHistory.slice(0, 10).map((item, idx) => `
        <span class="tp-res-ball ${item.winner === 'A' ? 'ball-a' : 'ball-b'}"
              onclick="TP2020.showResultModal(${idx})"
              title="Round ${item.roundId}: Winner Player ${item.winner}">
          ${item.winner}
        </span>
      `).join('');
    }

    function showResultModal(idx) {
      const item = recentHistory[idx];
      if (!item) return;
      document.getElementById('tpModalRoundId').textContent = item.roundId;
      document.getElementById('tpModalWinBadgeA').classList.toggle('d-none', item.winner !== 'A');
      document.getElementById('tpModalWinBadgeB').classList.toggle('d-none', item.winner !== 'B');
      document.getElementById('tpModalDescA').textContent = item.descA || '';
      document.getElementById('tpModalDescB').textContent = item.descB || '';

      document.getElementById('tpModalCardsA').innerHTML = item.cardsA.map(c =>
        `<img src="/assets/cards/${c}.png" style="width:42px;border-radius:4px;box-shadow:0 2px 5px rgba(0,0,0,0.4);">`
      ).join('');
      document.getElementById('tpModalCardsB').innerHTML = item.cardsB.map(c =>
        `<img src="/assets/cards/${c}.png" style="width:42px;border-radius:4px;box-shadow:0 2px 5px rgba(0,0,0,0.4);">`
      ).join('');

      const modal = new bootstrap.Modal(document.getElementById('tpResultModal'));
      modal.show();
    }

    function startNewBettingRound() {
      roundId = '91010' + Math.floor(11075100 + Math.random() * 89000);
      const ridEl = document.getElementById('tpRoundIdText');
      if (ridEl) ridEl.textContent = roundId;

      // Reset cards to face-down (/assets/cards/0.png) while betting is open
      setCard('tpCardA1', '0');
      setCard('tpCardA2', '0');
      setCard('tpCardA3', '0');
      setCard('tpCardB1', '0');
      setCard('tpCardB2', '0');
      setCard('tpCardB3', '0');

      document.querySelectorAll('.tp-odds-box').forEach(b => b.classList.remove('winner-pulse'));
      const banner = document.getElementById('tpWinnerBanner');
      if (banner) banner.style.display = 'none';

      phase = 'BETTING';
      countdown = 16;
      setSuspended(false);
      updateTimerDisplay();
    }

    function updateTimerDisplay() {
      const timerEl = document.getElementById('tpTimerCircle');
      if (!timerEl) return;
      timerEl.textContent = String(countdown);
      timerEl.classList.toggle('danger', countdown <= 5);
    }

    async function triggerDealAndSettle() {
      if (settlingInProgress) return;
      settlingInProgress = true;
      phase = 'DEALING';
      countdown = 0;
      setSuspended(true);

      try {
        const res = await fetch('/api/casino/teenpatti/settle-round', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
          },
          body: JSON.stringify({
            event_id: eventId,
            round_id: roundId
          })
        });
        const data = await res.json();
        if (data && data.status) {
          const cA = data.cards_a || ['S8_', 'D6_', 'CA_'];
          const cB = data.cards_b || ['D9_', 'H2_', 'SK_'];

          // Progressive card reveal matching live 20-20 Teenpatti
          setCard('tpCardA1', cA[0]);
          setCard('tpCardB1', cB[0]);
          setTimeout(() => {
            setCard('tpCardA2', cA[1]);
            setCard('tpCardB2', cB[1]);
          }, 250);
          setTimeout(() => {
            setCard('tpCardA3', cA[2]);
            setCard('tpCardB3', cB[2]);

            // Highlight winning markets
            (data.winning_keys || []).forEach(k => {
              document.getElementById('tpOddsBtn_' + k)?.classList.add('winner-pulse');
            });

            // Show winner banner
            const banner = document.getElementById('tpWinnerBanner');
            const bannerText = document.getElementById('tpWinnerBannerText');
            if (banner && bannerText) {
              bannerText.textContent = `WINNER: ${data.winner_label} (${data.winner === 'A' ? data.hand_a_desc : data.hand_b_desc})`;
              banner.style.display = 'block';
            }

            // Update wallet in top header if user had bets
            if (data.wallet) {
              updateHeaderWallet(data.wallet);
            }

            // Clear book previews for settled round
            openRoundBets = [];
            document.querySelectorAll('.tp-book-pl').forEach(el => { el.innerHTML = ''; });
            renderOpenBetsList(data.settled_bets || []);

            // Add to Recent Result bar
            recentHistory.unshift({
              roundId: data.round_id || roundId,
              winner: data.winner || 'A',
              cardsA: cA,
              cardsB: cB,
              descA: data.hand_a_desc || '',
              descB: data.hand_b_desc || ''
            });
            renderRecentBalls();
          }, 500);
        }
      } catch (e) {
        console.error('Teenpatti settle error:', e);
      } finally {
        setTimeout(() => {
          settlingInProgress = false;
          startNewBettingRound();
        }, 4200);
      }
    }

    function openSlip(marketKey, marketTitle, selectionKey, selectionLabel, odds, minBet, maxBet) {
      if (phase !== 'BETTING' || countdown <= 0) {
        if (window.WB24) WB24.toast('Market is suspended! Wait for next round timer.', 'danger');
        return;
      }
      currentSlip = { marketKey, marketTitle, selectionKey, selectionLabel, odds, minBet, maxBet };
      document.getElementById('tpSlipSelectionName').textContent = selectionLabel;
      document.getElementById('tpSlipMarketTitle').textContent = `20-20 TEENPATTI • ${marketTitle}`;
      document.getElementById('tpSlipOdds').value = odds;
      const stakeInput = document.getElementById('tpSlipStake');
      if (!stakeInput.value || Number(stakeInput.value) < minBet) {
        stakeInput.value = minBet;
      }
      document.getElementById('tpSlipAlert').classList.add('d-none');
      const slipEl = document.getElementById('tpBetSlip');
      slipEl.classList.add('active');
      updateSlipCalc();
      if (window.innerWidth < 992) {
        slipEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    }

    function closeSlip() {
      document.getElementById('tpBetSlip').classList.remove('active');
      currentSlip = null;
    }

    function setStake(val) {
      const stakeInput = document.getElementById('tpSlipStake');
      stakeInput.value = val;
      updateSlipCalc();
    }

    function updateSlipCalc() {
      if (!currentSlip) return;
      const stake = Math.max(0, parseFloat(document.getElementById('tpSlipStake').value) || 0);
      const profit = stake * (currentSlip.odds - 1);
      document.getElementById('tpSlipProfit').textContent = '+' + profit.toFixed(2);
      document.getElementById('tpSlipLiability').textContent = '-' + stake.toFixed(2);
    }

    function showSlipMessage(msg, isError = false) {
      const alertEl = document.getElementById('tpSlipAlert');
      if (!alertEl) return;
      alertEl.textContent = msg;
      alertEl.className = `alert py-1 px-2 mt-2 mb-0 small fw-bold ${isError ? 'alert-danger' : 'alert-success'}`;
    }

    function updateHeaderWallet(wallet) {
      if (!wallet) return;
      document.querySelectorAll('.js-user-balance').forEach(el => {
        el.textContent = Number(wallet.available ?? wallet.balance).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      });
      document.querySelectorAll('.js-user-exposure').forEach(el => {
        el.textContent = Number(wallet.exposure || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      });
    }

    async function submitBet() {
      if (!currentSlip) return;
      if (phase !== 'BETTING' || countdown <= 0) {
        showSlipMessage('Betting closed! Wait for next round timer.', true);
        return;
      }
      const stake = parseFloat(document.getElementById('tpSlipStake').value) || 0;
      if (stake < 10) {
        showSlipMessage('Minimum stake is 10 PTI.', true);
        return;
      }

      const btnPlace = document.getElementById('tpBtnPlaceBet');
      if (btnPlace) btnPlace.disabled = true;

      try {
        const res = await fetch('/api/casino/teenpatti/place-bet', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
          },
          body: JSON.stringify({
            event_id: eventId,
            round_id: roundId,
            market_key: currentSlip.marketKey,
            market_name: currentSlip.marketTitle,
            selection_key: currentSlip.selectionKey,
            selection_name: currentSlip.selectionLabel,
            odds: currentSlip.odds,
            stake: stake
          })
        });

        const data = await res.json();
        if (res.status === 401 && data.redirect) {
          window.location.href = data.redirect;
          return;
        }
        if (!data.status) {
          showSlipMessage(data.message || 'Could not place bet.', true);
          return;
        }

        if (data.wallet) {
          updateHeaderWallet(data.wallet);
        }

        openRoundBets.unshift(data.bet);
        updateBookPreview();
        renderOpenBetsList();
        showSlipMessage(`Bet Placed: ${currentSlip.selectionLabel} @ ${currentSlip.odds} (${stake.toFixed(2)} PTI) — Winner in ${countdown}s`, false);
      } catch (err) {
        showSlipMessage('Network error placing bet.', true);
      } finally {
        if (btnPlace) btnPlace.disabled = false;
      }
    }

    function updateBookPreview() {
      document.querySelectorAll('.tp-book-pl').forEach(el => { el.innerHTML = ''; });
      const totalsBySelection = {};
      openRoundBets.forEach(b => {
        if (!totalsBySelection[b.selection_id]) {
          totalsBySelection[b.selection_id] = { profit: 0, stake: 0 };
        }
        totalsBySelection[b.selection_id].profit += Number(b.potential_profit || 0);
        totalsBySelection[b.selection_id].stake += Number(b.stake || 0);
      });

      Object.keys(totalsBySelection).forEach(selKey => {
        const bookEl = document.getElementById('tpBook_' + selKey);
        if (bookEl) {
          const info = totalsBySelection[selKey];
          bookEl.innerHTML = `<span class="text-success">+${info.profit.toFixed(0)}</span> / <span class="text-danger">-${info.stake.toFixed(0)}</span>`;
        }
      });
    }

    function renderOpenBetsList(settledBets = []) {
      const container = document.getElementById('tpOpenBetsContainer');
      const badge = document.getElementById('tpOpenBetsCount');
      if (badge) badge.textContent = `${openRoundBets.length} Active`;
      if (!container) return;

      let html = '';
      if (openRoundBets.length > 0) {
        html += openRoundBets.map(b => `
          <div class="p-2 mb-1 border-bottom small bg-info-subtle rounded" data-mkt="${b.market_name}">
            <div class="d-flex justify-content-between fw-bold" style="font-size:11.5px;">
              <span>${b.selection_name} <span class="badge bg-primary">BACK</span></span>
              <span>@ ${Number(b.odds).toFixed(2)}</span>
            </div>
            <div class="text-muted" style="font-size:10px;">Round #${b.market_id} • ${b.market_name}</div>
            <div class="d-flex justify-content-between mt-1" style="font-size:11px;">
              <span>Stake: <b>${Number(b.stake).toFixed(2)}</b></span>
              <span class="text-success fw-bold">To Win: +${Number(b.potential_profit).toFixed(2)}</span>
            </div>
          </div>
        `).join('');
      }

      if (settledBets.length > 0) {
        html += settledBets.map(sb => `
          <div class="p-2 mb-1 border-bottom small ${sb.status === 'WON' ? 'bg-success-subtle' : 'bg-danger-subtle'} rounded">
            <div class="d-flex justify-content-between fw-bold" style="font-size:11.5px;">
              <span>${sb.selection_name}</span>
              <span class="badge ${sb.status === 'WON' ? 'bg-success' : 'bg-danger'}">${sb.status} (${sb.net_pl >= 0 ? '+' : ''}${Number(sb.net_pl).toFixed(2)})</span>
            </div>
            <div class="text-muted" style="font-size:10px;">${sb.result_summary || ''}</div>
          </div>
        `).join('');
      }

      if (!html) {
        html = `<div id="tpNoOpenBetsMsg" class="text-center text-muted py-3 small" style="font-size:11.5px;">Click on any Back odds box to place a 20-20 Teenpatti bet.</div>`;
      }
      container.innerHTML = html;
    }

    function filterOpenBets(mktKey) {
      document.querySelectorAll('#tpOpenBetsContainer [data-mkt]').forEach(el => {
        el.style.display = (mktKey === 'all' || el.dataset.mkt.includes(mktKey)) ? '' : 'none';
      });
    }

    function saveQuickStakes() {
      const inputs = Array.from(document.querySelectorAll('.js-tp-stake-input'));
      const values = inputs.map(i => Math.max(10, parseInt(i.value, 10) || 100));
      const grid = document.getElementById('tpQuickStakeGrid');
      if (grid) {
        grid.innerHTML = values.map(v =>
          `<button type="button" class="tp-stake-chip" onclick="TP2020.setStake(${v})">+${v}</button>`
        ).join('');
      }
    }

    // Boot lifecycle:
    // Start in the exact visual state of media_1794296847391_a140136d.jpg (S8_, D6_, 0 / D9_, H2_, 0 + SUSPENDED),
    // then transition smoothly into live 20-20 Teenpatti cycles!
    document.addEventListener('DOMContentLoaded', () => {
      renderRecentBalls();

      // After 3 seconds, complete the initial hand reveal and open the 16-second betting cycle
      setTimeout(() => {
        setCard('tpCardA3', 'CA_');
        setCard('tpCardB3', 'SK_');
        setTimeout(() => {
          startNewBettingRound();
        }, 1200);
      }, 2800);

      // 1-second round heartbeat
      setInterval(() => {
        if (phase === 'BETTING') {
          if (countdown > 1) {
            countdown--;
            updateTimerDisplay();
          } else {
            triggerDealAndSettle();
          }
        }
      }, 1000);
    });

    return {
      openSlip,
      closeSlip,
      setStake,
      updateSlipCalc,
      submitBet,
      showResultModal,
      filterOpenBets,
      saveQuickStakes,
      triggerDealAndSettle
    };
  })();
</script>
@endpush
