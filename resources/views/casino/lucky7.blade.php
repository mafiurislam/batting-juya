@extends('layouts.app')
@section('title', ($game->event_name ?? 'LUCKY 7') . ' - CRICEXPERTS')

@php
  $stakeButtons = auth()->check() ? auth()->user()->getStakeButtons() : [
    ['label' => '100', 'value' => 100], ['label' => '500', 'value' => 500],
    ['label' => '1000', 'value' => 1000], ['label' => '5000', 'value' => 5000],
    ['label' => '10000', 'value' => 10000], ['label' => '25000', 'value' => 25000],
    ['label' => '50000', 'value' => 50000], ['label' => '100000', 'value' => 100000],
  ];

  $luckyCardRanks = [
    ['code' => 'A',  'vol' => 49900],
    ['code' => '2',  'vol' => 49400],
    ['code' => '3',  'vol' => 49400],
    ['code' => '4',  'vol' => 48600],
    ['code' => '5',  'vol' => 47800],
    ['code' => '6',  'vol' => 50000],
    ['code' => '7',  'vol' => 48048],
    ['code' => '8',  'vol' => 48300],
    ['code' => '9',  'vol' => 49375],
    ['code' => '10', 'vol' => 48000],
    ['code' => 'J',  'vol' => 48700],
    ['code' => 'Q',  'vol' => 48372],
    ['code' => 'K',  'vol' => 48550],
  ];
@endphp

@push('styles')
<style>
  /* Exact 1:1 Styles from https://casino.winbook24.com/lucky7/99.0030 */
  body {
    background-color: #f4f4f4;
    font-family: Tahoma, Helvetica, Arial, sans-serif;
  }

  .l7-page-wrap {
    padding: 2px 7px 20px 7px;
  }

  .l7-row {
    display: flex;
    flex-wrap: wrap;
    margin: 0 -4px;
  }

  .l7-col-left {
    width: 16.66666667%;
    padding: 0 4px;
  }

  .l7-col-center {
    width: 57.33333333%;
    padding: 0 4px;
  }

  .l7-col-right {
    width: 26%;
    padding: 0 4px;
  }

  @media (max-width: 991.98px) {
    .l7-col-left {
      display: none;
    }
    .l7-col-center,
    .l7-col-right {
      width: 100%;
      margin-bottom: 10px;
    }
  }

  /* Left Sports Sidebar */
  .l7-sport-box {
    background: #fff;
    border-bottom: 1px solid #d5d5d5;
  }

  .l7-sport-head {
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

  .l7-sport-list {
    list-style: none;
    margin: 0;
    padding: 0;
  }

  .l7-sport-list li a {
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

  .l7-sport-list li a:hover {
    background: #eef6fb;
    color: #14213d;
  }

  .l7-sport-arrow {
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

  /* Top Black 16:9 Universe Casino Stage */
  .l7-video-player {
    position: relative;
    background: #000000;
    width: 100%;
    text-align: center;
    overflow: hidden;
    user-select: none;
  }

  .l7-stage-gif {
    width: 75%;
    aspect-ratio: 16 / 9;
    height: auto;
    display: inline-block;
    vertical-align: middle;
  }

  /* Top-Left RID & Single CARD Overlay */
  .l7-video-overlay {
    position: absolute;
    top: 0;
    left: 5px;
    text-align: left;
    width: 200px;
    background: rgba(0, 0, 0, 0.25);
    padding: 2px 4px;
    z-index: 5;
  }

  .l7-rid-line {
    font-size: 10px;
    color: #ffffff;
    font-weight: 700;
    margin: 0 0 1px 0;
    line-height: 1.25;
  }

  .l7-card-head {
    color: #ffffff;
    font-size: 12.5px;
    font-weight: 700;
    margin: 1px 0 2px 0;
    line-height: 1.15;
    text-transform: uppercase;
  }

  .l7-card-img {
    width: 23px;
    height: auto;
    border-radius: 2px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.7);
    transition: transform 0.16s ease;
  }

  .l7-card-img.flipping {
    transform: rotateY(90deg) scale(1.06);
  }

  /* Top-Right Orange Gear Icon & Circular Countdown Timer (Exact screenshot match) */
  .l7-topright-bar {
    position: absolute;
    top: 5px;
    right: 6px;
    display: flex;
    align-items: center;
    gap: 10px;
    z-index: 6;
  }

  .l7-cog-btn {
    cursor: pointer;
    color: #fd8f3b;
    font-size: 20px;
    line-height: 1;
    background: transparent;
    border: none;
    padding: 2px;
  }

  .l7-cog-btn:hover {
    color: #ffa95e;
  }

  .l7-timer-ring {
    width: 46px;
    height: 46px;
    border-radius: 50%;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    background: conic-gradient(#16a34a var(--l7-prog, 75%), rgba(255, 255, 255, 0.1) 0);
    transition: opacity 0.2s ease;
  }

  .l7-timer-ring.danger {
    background: conic-gradient(#dc2626 var(--l7-prog, 25%), rgba(255, 255, 255, 0.1) 0);
  }

  .l7-timer-ring.hidden-timer {
    opacity: 0;
    pointer-events: none;
  }

  .l7-timer-face {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #111111;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 17px;
    font-weight: 700;
  }

  /* Winner Overlay Banner inside stage */
  .l7-winner-toast {
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
  .l7-market-block {
    margin-top: 4px;
  }

  .l7-market-header {
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

  .l7-market-body {
    position: relative;
    background: linear-gradient(90deg, rgb(153, 199, 241) 0%, rgba(138, 189, 216, 0.5) 49%, rgb(146, 198, 246) 100%);
    padding: 6px 10px 10px 10px;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-around;
    border-bottom: 1px solid #b8d4ea;
  }

  .l7-runner-col {
    width: 48%;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    margin-bottom: 6px;
  }

  .l7-runner-title {
    font-size: 11.5px;
    font-weight: 700;
    color: #23282c;
    margin-bottom: 4px;
    text-transform: uppercase;
  }

  .l7-odds-box {
    position: relative;
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
    min-height: 38px;
    transition: transform 0.1s ease, filter 0.1s ease;
  }

  .l7-odds-box:hover {
    filter: brightness(1.05);
    transform: translateY(-1px);
  }

  .l7-odds-box.winner-pulse {
    box-shadow: 0 0 0 2px #16a34a, 0 2px 10px rgba(22, 163, 74, 0.65);
    background: #86efac;
  }

  .l7-left-icon {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    display: flex;
    flex-direction: column;
    align-items: center;
    line-height: 1;
    font-size: 12px;
    font-weight: 700;
  }

  .l7-suit-red {
    color: #e60000;
  }

  .l7-suit-black {
    color: #000000;
  }

  .l7-odds-val {
    font-size: 14px;
    font-weight: 700;
    color: #1e1e1e;
    line-height: 1.2;
  }

  .l7-odds-vol {
    font-size: 9.5px;
    color: #2f353a;
    line-height: 1.1;
    margin-top: 1px;
  }

  .l7-book-pl {
    font-size: 10px;
    font-weight: 700;
    margin-top: 2px;
    min-height: 12px;
  }

  /* Red SUSPENDED Overlay */
  .l7-suspended-overlay {
    position: absolute;
    inset: 0;
    background-color: rgba(255, 255, 255, 0.5);
    border: 2px solid #ca1010;
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 4;
    pointer-events: none;
  }

  .l7-suspended-overlay.active {
    display: flex;
  }

  .l7-suspended-overlay span {
    font-size: 25px;
    font-weight: 700;
    color: #ca1010;
    letter-spacing: 0.5px;
    text-transform: uppercase;
  }

  /* Bottom Recent Result Bar */
  .l7-recent-bar {
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

  .l7-recent-title {
    font-size: 12.5px;
    font-weight: 700;
  }

  .l7-recent-balls {
    display: flex;
    align-items: center;
    gap: 4px;
    flex-wrap: wrap;
  }

  .l7-res-ball {
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

  .l7-res-ball.ball-h {
    background: #72bbef; /* Blue H (High Card) */
  }

  .l7-res-ball.ball-l {
    background: #f9a9ba; /* Pink L (Low Card) */
  }

  .l7-res-ball.ball-t {
    background: #fde047; /* Gold T (Lucky 7) */
  }

  /* Right Sidebar: Open Bets & Bet Slip */
  .l7-openbets-box {
    background: #ffffff;
    border: 1px solid #d5d5d5;
  }

  .l7-openbets-head {
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

  .l7-slip-box {
    background: #d4e8f8;
    border: 1px solid #72bbef;
    padding: 8px;
    margin: 6px;
    border-radius: 4px;
    display: none;
  }

  .l7-slip-box.active {
    display: block;
  }

  .l7-stake-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 4px;
    margin-top: 6px;
  }

  .l7-stake-chip {
    background: #2E4B5E;
    color: #ffffff;
    border: 1px solid #1b2d38;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 700;
    padding: 4px 2px;
    cursor: pointer;
  }

  .l7-stake-chip:hover {
    background: #3b6078;
  }
</style>
@endpush

@section('full_bleed')
<div class="l7-page-wrap">
  <div class="l7-row">

    <!-- ================= LEFT COLUMN: SPORTS SIDEBAR ================= -->
    <div class="l7-col-left">
      <div class="l7-sport-box">
        <div class="l7-sport-head">
          <span><i class="fas fa-ellipsis-v"></i></span>
          <span>Sports</span>
        </div>
        <ul class="l7-sport-list">
          <li>
            <a href="{{ route('sport.highlight', '4') }}">
              <span>Cricket</span>
              <span class="l7-sport-arrow"><i class="fas fa-chevron-right"></i></span>
            </a>
          </li>
          <li>
            <a href="{{ route('sport.highlight', '2') }}">
              <span>Tennis</span>
              <span class="l7-sport-arrow"><i class="fas fa-chevron-right"></i></span>
            </a>
          </li>
          <li>
            <a href="{{ route('sport.highlight', '1') }}">
              <span>Soccer</span>
              <span class="l7-sport-arrow"><i class="fas fa-chevron-right"></i></span>
            </a>
          </li>
          <li>
            <a href="{{ route('horse.racing') }}">
              <span>Horse Racing</span>
              <span class="l7-sport-arrow"><i class="fas fa-chevron-right"></i></span>
            </a>
          </li>
          <li>
            <a href="{{ route('horse.racing', ['type' => 'greyhound']) }}">
              <span>Greyhound Racing</span>
              <span class="l7-sport-arrow"><i class="fas fa-chevron-right"></i></span>
            </a>
          </li>
          <li>
            <a href="{{ route('sport.highlight', '7522') }}">
              <span>Basketball</span>
              <span class="l7-sport-arrow"><i class="fas fa-chevron-right"></i></span>
            </a>
          </li>
          <li>
            <a href="{{ route('sport.highlight', '66104') }}">
              <span>Lottery</span>
              <span class="l7-sport-arrow"><i class="fas fa-chevron-right"></i></span>
            </a>
          </li>
        </ul>
      </div>
    </div>

    <!-- ================= CENTER COLUMN: LUCKY 7 (99.0030) STAGE & 5 MARKETS ================= -->
    <div class="l7-col-center">
      <div class="center-Main">

        <!-- Black 16:9 Universe Casino Stage -->
        <div class="l7-video-player">
          <img src="/assets/casino/loader.gif" class="l7-stage-gif" alt="Universe Casino">

          <!-- Top-Left RID + Single CARD Overlay -->
          <div class="l7-video-overlay">
            <p class="l7-rid-line">RID: <span id="l7RoundIdText">9301011081931</span></p>
            <div>
              <h3 class="l7-card-head">CARD</h3>
              <div>
                <img id="l7MainCard" src="/assets/cards/0.png" class="l7-card-img" alt="Card">
              </div>
            </div>
          </div>

          <!-- Top-Right Orange Gear Icon + Circular Countdown Timer (12) -->
          <div class="l7-topright-bar">
            <button type="button" class="l7-cog-btn" data-bs-toggle="modal" data-bs-target="#l7StakeModal" title="Edit Quick Stakes">
              <i class="fas fa-cog"></i>
            </button>
            <div id="l7TimerRing" class="l7-timer-ring" style="--l7-prog: 75%;">
              <div class="l7-timer-face" id="l7TimerText">12</div>
            </div>
          </div>

          <!-- Round Winner Toast -->
          <div id="l7WinnerBanner" class="l7-winner-toast">
            <i class="fas fa-trophy me-1"></i> <span id="l7WinnerBannerText">RESULT: HIGH CARD</span>
          </div>
        </div>

        <!-- 5 Betting Markets (#marketScroll) -->
        <div id="marketScroll">

          <!-- 1. WINNER (LOW CARD A to 6 vs HIGH CARD 8 to K) -->
          <div class="l7-market-block" data-market-key="WINNER">
            <div class="l7-market-header" data-bs-toggle="collapse" data-bs-target="#l7Mkt_WINNER" aria-expanded="true">
              <span>WINNER</span>
              <span>Min/Max: 100 - 100000</span>
            </div>
            <div id="l7Mkt_WINNER" class="collapse show">
              <div class="l7-market-body">
                <div class="l7-runner-col">
                  <div class="l7-runner-title">LOW CARD ( A to 6 )</div>
                  <button type="button" id="l7OddsBtn_LOW_CARD" class="l7-odds-box"
                          onclick="LUCKY7.openSlip('WINNER', 'WINNER', 'LOW_CARD', 'LOW CARD ( A to 6 )', 2, 100, 100000)">
                    <span class="l7-odds-val">2</span>
                    <span class="l7-odds-vol">989450</span>
                  </button>
                  <div class="l7-book-pl" id="l7Book_LOW_CARD"></div>
                </div>

                <div class="l7-runner-col">
                  <div class="l7-runner-title">HIGH CARD ( 8 to K )</div>
                  <button type="button" id="l7OddsBtn_HIGH_CARD" class="l7-odds-box"
                          onclick="LUCKY7.openSlip('WINNER', 'WINNER', 'HIGH_CARD', 'HIGH CARD ( 8 to K )', 2, 100, 100000)">
                    <span class="l7-odds-val">2</span>
                    <span class="l7-odds-vol">997345</span>
                  </button>
                  <div class="l7-book-pl" id="l7Book_HIGH_CARD"></div>
                </div>

                <div class="l7-suspended-overlay js-l7-suspended">
                  <span>SUSPENDED</span>
                </div>
              </div>
            </div>
          </div>

          <!-- 2. LUCKY ODD/EVEN -->
          <div class="l7-market-block" data-market-key="ODD_EVEN">
            <div class="l7-market-header" data-bs-toggle="collapse" data-bs-target="#l7Mkt_ODDEVEN" aria-expanded="true">
              <span>LUCKY ODD/EVEN</span>
              <span>Min/Max: 100 - 100000</span>
            </div>
            <div id="l7Mkt_ODDEVEN" class="collapse show">
              <div class="l7-market-body">
                <div class="l7-runner-col">
                  <div class="l7-runner-title">ODD</div>
                  <button type="button" id="l7OddsBtn_ODD" class="l7-odds-box"
                          onclick="LUCKY7.openSlip('ODD_EVEN', 'LUCKY ODD/EVEN', 'ODD', 'ODD', 1.81, 100, 100000)">
                    <span class="l7-odds-val">1.81</span>
                    <span class="l7-odds-vol">300000</span>
                  </button>
                  <div class="l7-book-pl" id="l7Book_ODD"></div>
                </div>

                <div class="l7-runner-col">
                  <div class="l7-runner-title">EVEN</div>
                  <button type="button" id="l7OddsBtn_EVEN" class="l7-odds-box"
                          onclick="LUCKY7.openSlip('ODD_EVEN', 'LUCKY ODD/EVEN', 'EVEN', 'EVEN', 2.14, 100, 100000)">
                    <span class="l7-odds-val">2.14</span>
                    <span class="l7-odds-vol">299700</span>
                  </button>
                  <div class="l7-book-pl" id="l7Book_EVEN"></div>
                </div>

                <div class="l7-suspended-overlay js-l7-suspended">
                  <span>SUSPENDED</span>
                </div>
              </div>
            </div>
          </div>

          <!-- 3. LUCKY COLOR (RED vs BLACK) -->
          <div class="l7-market-block" data-market-key="COLOR">
            <div class="l7-market-header" data-bs-toggle="collapse" data-bs-target="#l7Mkt_COLOR" aria-expanded="true">
              <span>LUCKY COLOR</span>
              <span>Min/Max: 100 - 100000</span>
            </div>
            <div id="l7Mkt_COLOR" class="collapse show">
              <div class="l7-market-body">
                <div class="l7-runner-col">
                  <div class="l7-runner-title">RED</div>
                  <button type="button" id="l7OddsBtn_RED" class="l7-odds-box"
                          onclick="LUCKY7.openSlip('COLOR', 'LUCKY COLOR', 'RED', 'RED', 1.95, 100, 100000)">
                    <span class="l7-left-icon">
                      <span class="l7-suit-red">♥</span>
                      <span class="l7-suit-red">♦</span>
                    </span>
                    <span class="l7-odds-val">1.95</span>
                    <span class="l7-odds-vol">297360</span>
                  </button>
                  <div class="l7-book-pl" id="l7Book_RED"></div>
                </div>

                <div class="l7-runner-col">
                  <div class="l7-runner-title">BLACK</div>
                  <button type="button" id="l7OddsBtn_BLACK" class="l7-odds-box"
                          onclick="LUCKY7.openSlip('COLOR', 'LUCKY COLOR', 'BLACK', 'BLACK', 1.95, 100, 100000)">
                    <span class="l7-left-icon">
                      <span class="l7-suit-black">♠</span>
                      <span class="l7-suit-black">♣</span>
                    </span>
                    <span class="l7-odds-val">1.95</span>
                    <span class="l7-odds-vol">299000</span>
                  </button>
                  <div class="l7-book-pl" id="l7Book_BLACK"></div>
                </div>

                <div class="l7-suspended-overlay js-l7-suspended">
                  <span>SUSPENDED</span>
                </div>
              </div>
            </div>
          </div>

          <!-- 4. LUCKY CARD SUIT (SPADES, HEARTS, DIAMONDS, CLUBS) -->
          <div class="l7-market-block" data-market-key="SUIT">
            <div class="l7-market-header" data-bs-toggle="collapse" data-bs-target="#l7Mkt_SUIT" aria-expanded="true">
              <span>LUCKY CARD SUIT</span>
              <span>Min/Max: 100 - 100000</span>
            </div>
            <div id="l7Mkt_SUIT" class="collapse show">
              <div class="l7-market-body">
                <div class="l7-runner-col">
                  <div class="l7-runner-title">SPADES</div>
                  <button type="button" id="l7OddsBtn_SUIT_S" class="l7-odds-box"
                          onclick="LUCKY7.openSlip('SUIT', 'LUCKY CARD SUIT', 'SUIT_S', 'SPADES', 3.75, 100, 100000)">
                    <span class="l7-left-icon"><span class="l7-suit-black" style="font-size:14px;">♠</span></span>
                    <span class="l7-odds-val">3.75</span>
                    <span class="l7-odds-vol">299900</span>
                  </button>
                  <div class="l7-book-pl" id="l7Book_SUIT_S"></div>
                </div>

                <div class="l7-runner-col">
                  <div class="l7-runner-title">HEARTS</div>
                  <button type="button" id="l7OddsBtn_SUIT_H" class="l7-odds-box"
                          onclick="LUCKY7.openSlip('SUIT', 'LUCKY CARD SUIT', 'SUIT_H', 'HEARTS', 3.75, 100, 100000)">
                    <span class="l7-left-icon"><span class="l7-suit-red" style="font-size:14px;">♥</span></span>
                    <span class="l7-odds-val">3.75</span>
                    <span class="l7-odds-vol">299400</span>
                  </button>
                  <div class="l7-book-pl" id="l7Book_SUIT_H"></div>
                </div>

                <div class="l7-runner-col">
                  <div class="l7-runner-title">DIAMONDS</div>
                  <button type="button" id="l7OddsBtn_SUIT_D" class="l7-odds-box"
                          onclick="LUCKY7.openSlip('SUIT', 'LUCKY CARD SUIT', 'SUIT_D', 'DIAMONDS', 3.75, 100, 100000)">
                    <span class="l7-left-icon"><span class="l7-suit-red" style="font-size:14px;">♦</span></span>
                    <span class="l7-odds-val">3.75</span>
                    <span class="l7-odds-vol">299405</span>
                  </button>
                  <div class="l7-book-pl" id="l7Book_SUIT_D"></div>
                </div>

                <div class="l7-runner-col">
                  <div class="l7-runner-title">CLUBS</div>
                  <button type="button" id="l7OddsBtn_SUIT_C" class="l7-odds-box"
                          onclick="LUCKY7.openSlip('SUIT', 'LUCKY CARD SUIT', 'SUIT_C', 'CLUBS', 3.75, 100, 100000)">
                    <span class="l7-left-icon"><span class="l7-suit-black" style="font-size:14px;">♣</span></span>
                    <span class="l7-odds-val">3.75</span>
                    <span class="l7-odds-vol">300000</span>
                  </button>
                  <div class="l7-book-pl" id="l7Book_SUIT_C"></div>
                </div>

                <div class="l7-suspended-overlay js-l7-suspended">
                  <span>SUSPENDED</span>
                </div>
              </div>
            </div>
          </div>

          <!-- 5. LUCKY CARD (A, 2, 3, 4, 5, 6, 7, 8, 9, 10, J, Q, K @ 12) -->
          <div class="l7-market-block" data-market-key="CARD">
            <div class="l7-market-header" data-bs-toggle="collapse" data-bs-target="#l7Mkt_CARD" aria-expanded="true">
              <span>LUCKY CARD</span>
              <span>Min/Max: 100 - 20000</span>
            </div>
            <div id="l7Mkt_CARD" class="collapse show">
              <div class="l7-market-body">
                @foreach($luckyCardRanks as $cr)
                  @php $selKey = 'CARD_' . $cr['code']; @endphp
                  <div class="l7-runner-col">
                    <div class="l7-runner-title">{{ $cr['code'] }}</div>
                    <button type="button" id="l7OddsBtn_{{ $selKey }}" class="l7-odds-box"
                            onclick="LUCKY7.openSlip('CARD', 'LUCKY CARD', '{{ $selKey }}', 'CARD {{ $cr['code'] }}', 12, 100, 20000)">
                      <span class="l7-left-icon">
                        <img src="/assets/t10cards/{{ $cr['code'] }}.jpeg" style="width:20px;" alt="{{ $cr['code'] }}">
                      </span>
                      <span class="l7-odds-val">12</span>
                      <span class="l7-odds-vol">{{ $cr['vol'] }}</span>
                    </button>
                    <div class="l7-book-pl" id="l7Book_{{ $selKey }}"></div>
                  </div>
                @endforeach
                <div class="l7-runner-col"></div>

                <div class="l7-suspended-overlay js-l7-suspended">
                  <span>SUSPENDED</span>
                </div>
              </div>
            </div>
          </div>

        </div>

        <!-- Bottom Recent Result Bar (L / H / T badges) -->
        <div class="l7-recent-bar">
          <div class="l7-recent-title">Recent Result</div>
          <div class="l7-recent-balls" id="l7RecentBalls"></div>
        </div>

      </div>
    </div>

    <!-- ================= RIGHT COLUMN: OPEN BETS & INTERACTIVE BET SLIP ================= -->
    <div class="l7-col-right">
      <div class="l7-openbets-box">
        <div class="l7-openbets-head">
          <span>Open Bets</span>
          <span class="badge bg-light text-dark" style="font-size:10px;" id="l7OpenBetsCount">{{ isset($openBets) ? $openBets->count() : 0 }} Active</span>
        </div>

        <div class="p-2 border-bottom">
          <select id="l7MarketFilter" class="form-select form-select-sm" style="font-size:12px;border-color:#c8ced3;" onchange="LUCKY7.filterOpenBets(this.value)">
            <option value="all">Select Market</option>
            <option value="WINNER">WINNER</option>
            <option value="LUCKY ODD/EVEN">LUCKY ODD/EVEN</option>
            <option value="LUCKY COLOR">LUCKY COLOR</option>
            <option value="LUCKY CARD SUIT">LUCKY CARD SUIT</option>
            <option value="LUCKY CARD">LUCKY CARD</option>
          </select>
        </div>

        <!-- Interactive Lucky 7 Bet Slip -->
        <div id="l7BetSlip" class="l7-slip-box">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <div>
              <span class="badge bg-primary" style="font-size:10px;">BACK</span>
              <strong id="l7SlipSelectionName" class="ms-1" style="font-size:12px;">LOW CARD ( A to 6 )</strong>
            </div>
            <button type="button" class="btn-close btn-sm" onclick="LUCKY7.closeSlip()"></button>
          </div>
          <div id="l7SlipMarketTitle" class="text-muted mb-2" style="font-size:10.5px;">LUCKY 7 • WINNER</div>

          <div class="row g-2 align-items-center mb-2">
            <div class="col-6">
              <label class="small fw-bold d-block mb-1" style="font-size:11px;">Odds</label>
              <input type="text" id="l7SlipOdds" class="form-control form-control-sm text-center fw-bold bg-white" readonly value="2">
            </div>
            <div class="col-6">
              <label class="small fw-bold d-block mb-1" style="font-size:11px;">Stake (PTI)</label>
              <input type="number" id="l7SlipStake" class="form-control form-control-sm text-center fw-bold" value="100" min="10" step="10" oninput="LUCKY7.updateSlipCalc()">
            </div>
          </div>

          <div class="l7-stake-grid" id="l7QuickStakeGrid">
            @foreach($stakeButtons as $sb)
              <button type="button" class="l7-stake-chip" onclick="LUCKY7.setStake({{ $sb['value'] }})">+{{ $sb['label'] }}</button>
            @endforeach
          </div>

          <div class="d-flex justify-content-between small mt-2 pt-1 border-top border-secondary-subtle" style="font-size:11px;">
            <span>Profit: <strong class="text-success" id="l7SlipProfit">+100.00</strong></span>
            <span>Liability: <strong class="text-danger" id="l7SlipLiability">-100.00</strong></span>
          </div>

          <div id="l7SlipAlert" class="alert py-1 px-2 mt-2 mb-0 small fw-bold d-none" style="font-size:11px;"></div>

          <div class="d-flex justify-content-between gap-1 mt-2">
            <button type="button" class="btn btn-sm btn-secondary fw-bold flex-fill" style="font-size:11px;" onclick="LUCKY7.closeSlip()">Cancel</button>
            <button type="button" id="l7BtnPlaceBet" class="btn btn-sm btn-primary fw-bold flex-fill" style="font-size:11px;background:#243A48;border-color:#14213D;" onclick="LUCKY7.submitBet(false)">Place Bet</button>
            <button type="button" id="l7BtnDealNow" class="btn btn-sm btn-success fw-bold flex-fill" style="font-size:11px;" onclick="LUCKY7.submitBet(true)" title="Place Bet & Reveal Card Immediately">Bet &amp; Deal</button>
          </div>
        </div>

        <!-- Active & Settled Round Bets List -->
        <div id="l7OpenBetsContainer" class="p-2" style="max-height:420px;overflow-y:auto;">
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
            <div id="l7NoOpenBetsMsg" class="text-center text-muted py-3 small" style="font-size:11.5px;">
              Click on any Back odds box to place a Lucky 7 bet.
            </div>
          @endif
        </div>
      </div>
    </div>

  </div>
</div>

<!-- ================= QUICK STAKE SETTINGS MODAL (Gear Icon) ================= -->
<div class="modal fade" id="l7StakeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header py-2" style="background:#2f4553;color:#fff;">
        <h6 class="modal-title fw-bold mb-0" style="font-size:14px;">Stake</h6>
        <button type="button" class="btn-close btn-close-white btn-sm" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-3">
        <div class="row g-2">
          @foreach($stakeButtons as $sb)
            <div class="col-6">
              <input type="number" class="form-control form-control-sm fw-bold text-center js-l7-stake-input" value="{{ $sb['value'] }}">
            </div>
          @endforeach
        </div>
        <button type="button" class="btn btn-success w-100 fw-bold mt-3 btn-sm" onclick="LUCKY7.saveQuickStakes()" data-bs-dismiss="modal">
          Save
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ================= ROUND RESULT DETAIL MODAL (Clicking L / H / T in Recent Result) ================= -->
<div class="modal fade" id="l7ResultModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header py-2" style="background:linear-gradient(-180deg, #2E4B5E 0%, #243A48 82%);color:#fff;">
        <h6 class="modal-title fw-bold mb-0" style="font-size:13.5px;">LUCKY 7 RESULT</h6>
        <button type="button" class="btn-close btn-close-white btn-sm" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center p-3">
        <h6 class="fw-bold mb-2" style="font-size:13px;">Round Id: <span id="l7ModalRoundId">9301011081931</span></h6>
        <div class="my-3">
          <img id="l7ModalCardImg" src="/assets/cards/SK_.png" style="width:56px;border-radius:4px;box-shadow:0 2px 8px rgba(0,0,0,0.4);" alt="Result Card">
        </div>
        <div class="fw-bold text-success mb-1" style="font-size:13px;" id="l7ModalOutcomeText">HIGH CARD ( 8 to K )</div>
        <div class="small text-muted fw-bold" id="l7ModalSubText">ODD • BLACK • SPADES • CARD K</div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  const LUCKY7 = (() => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const eventId = @json($game->event_id ?? '99.0030');

    // Starts in active betting state matching media_1794298908878_2f88d24b.jpg (RID: 9301011081931, CARD face-down 0.png, timer 12)
    let roundId = '9301011081931';
    let countdown = 12;
    const maxCountdown = 16;
    let phase = 'BETTING'; // 'BETTING' or 'DEALING'
    let currentSlip = null;
    let openRoundBets = [];
    let settlingInProgress = false;

    const recentHistory = [
      { roundId: '9301011081930', badge: 'L', card: 'H4_', outcome: 'LOW CARD ( A to 6 )', sub: 'EVEN • RED • HEARTS • CARD 4' },
      { roundId: '9301011081929', badge: 'L', card: 'S3_', outcome: 'LOW CARD ( A to 6 )', sub: 'ODD • BLACK • SPADES • CARD 3' },
      { roundId: '9301011081928', badge: 'H', card: 'DK_', outcome: 'HIGH CARD ( 8 to K )', sub: 'ODD • RED • DIAMONDS • CARD K' },
      { roundId: '9301011081927', badge: 'H', card: 'C10_', outcome: 'HIGH CARD ( 8 to K )', sub: 'EVEN • BLACK • CLUBS • CARD 10' },
      { roundId: '9301011081926', badge: 'L', card: 'DA_', outcome: 'LOW CARD ( A to 6 )', sub: 'ODD • RED • DIAMONDS • CARD A' },
      { roundId: '9301011081925', badge: 'L', card: 'C6_', outcome: 'LOW CARD ( A to 6 )', sub: 'EVEN • BLACK • CLUBS • CARD 6' },
      { roundId: '9301011081924', badge: 'H', card: 'HQ_', outcome: 'HIGH CARD ( 8 to K )', sub: 'EVEN • RED • HEARTS • CARD Q' },
      { roundId: '9301011081923', badge: 'H', card: 'SJ_', outcome: 'HIGH CARD ( 8 to K )', sub: 'ODD • BLACK • SPADES • CARD J' },
      { roundId: '9301011081922', badge: 'H', card: 'D9_', outcome: 'HIGH CARD ( 8 to K )', sub: 'ODD • RED • DIAMONDS • CARD 9' },
      { roundId: '9301011081921', badge: 'L', card: 'S2_', outcome: 'LOW CARD ( A to 6 )', sub: 'EVEN • BLACK • SPADES • CARD 2' },
    ];

    function setCard(cardCode) {
      const el = document.getElementById('l7MainCard');
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
      document.querySelectorAll('.js-l7-suspended').forEach(el => {
        el.classList.toggle('active', isSuspended);
      });
      const timerRing = document.getElementById('l7TimerRing');
      if (timerRing) {
        timerRing.classList.toggle('hidden-timer', isSuspended);
      }
    }

    function updateTimerUI() {
      const textEl = document.getElementById('l7TimerText');
      const ringEl = document.getElementById('l7TimerRing');
      if (textEl) textEl.textContent = String(countdown);
      if (ringEl) {
        const pct = Math.max(5, Math.round((countdown / maxCountdown) * 100));
        ringEl.style.setProperty('--l7-prog', pct + '%');
        ringEl.classList.toggle('danger', countdown <= 5);
      }
    }

    function renderRecentBalls() {
      const container = document.getElementById('l7RecentBalls');
      if (!container) return;
      container.innerHTML = recentHistory.slice(0, 10).map((item, idx) => {
        const cls = item.badge === 'H' ? 'ball-h' : (item.badge === 'L' ? 'ball-l' : 'ball-t');
        return `
          <span class="l7-res-ball ${cls}"
                onclick="LUCKY7.showResultModal(${idx})"
                title="Round ${item.roundId}: ${item.outcome}">
            ${item.badge}
          </span>
        `;
      }).join('');
    }

    function showResultModal(idx) {
      const item = recentHistory[idx];
      if (!item) return;
      document.getElementById('l7ModalRoundId').textContent = item.roundId;
      document.getElementById('l7ModalCardImg').src = `/assets/cards/${item.card}.png`;
      document.getElementById('l7ModalOutcomeText').textContent = item.outcome;
      document.getElementById('l7ModalSubText').textContent = item.sub;
      const modal = new bootstrap.Modal(document.getElementById('l7ResultModal'));
      modal.show();
    }

    function startNewBettingRound() {
      roundId = '93010' + Math.floor(11081932 + Math.random() * 80000);
      const ridEl = document.getElementById('l7RoundIdText');
      if (ridEl) ridEl.textContent = roundId;

      setCard('0');
      document.querySelectorAll('.l7-odds-box').forEach(b => b.classList.remove('winner-pulse'));
      const banner = document.getElementById('l7WinnerBanner');
      if (banner) banner.style.display = 'none';

      phase = 'BETTING';
      countdown = maxCountdown;
      setSuspended(false);
      updateTimerUI();
    }

    async function triggerDealAndSettle() {
      if (settlingInProgress) return;
      settlingInProgress = true;
      phase = 'DEALING';
      countdown = 0;
      setSuspended(true);

      try {
        const res = await fetch('/api/casino/lucky7/settle-round', {
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
          setCard(data.card_code || 'SK_');

          setTimeout(() => {
            (data.winning_keys || []).forEach(k => {
              document.getElementById('l7OddsBtn_' + k)?.classList.add('winner-pulse');
            });

            const banner = document.getElementById('l7WinnerBanner');
            const bannerText = document.getElementById('l7WinnerBannerText');
            if (banner && bannerText) {
              bannerText.textContent = `${data.outcome_label} (${data.sub_summary})`;
              banner.style.display = 'block';
            }

            if (data.wallet) {
              updateHeaderWallet(data.wallet);
            }

            openRoundBets = [];
            document.querySelectorAll('.l7-book-pl').forEach(el => { el.innerHTML = ''; });
            renderOpenBetsList(data.settled_bets || []);

            recentHistory.unshift({
              roundId: data.round_id || roundId,
              badge: data.badge || 'H',
              card: data.card_code || 'SK_',
              outcome: data.outcome_label || 'HIGH CARD ( 8 to K )',
              sub: data.sub_summary || ''
            });
            renderRecentBalls();
          }, 250);
        }
      } catch (e) {
        console.error('Lucky 7 settle error:', e);
      } finally {
        setTimeout(() => {
          settlingInProgress = false;
          startNewBettingRound();
        }, 3800);
      }
    }

    function openSlip(marketKey, marketTitle, selectionKey, selectionLabel, odds, minBet, maxBet) {
      currentSlip = { marketKey, marketTitle, selectionKey, selectionLabel, odds, minBet, maxBet };
      document.getElementById('l7SlipSelectionName').textContent = selectionLabel;
      document.getElementById('l7SlipMarketTitle').textContent = `LUCKY 7 • ${marketTitle}`;
      document.getElementById('l7SlipOdds').value = odds;
      const stakeInput = document.getElementById('l7SlipStake');
      if (!stakeInput.value || Number(stakeInput.value) < minBet) {
        stakeInput.value = minBet;
      }
      document.getElementById('l7SlipAlert').classList.add('d-none');
      document.getElementById('l7BetSlip').classList.add('active');
      updateSlipCalc();
    }

    function closeSlip() {
      document.getElementById('l7BetSlip').classList.remove('active');
      currentSlip = null;
    }

    function setStake(val) {
      document.getElementById('l7SlipStake').value = val;
      updateSlipCalc();
    }

    function updateSlipCalc() {
      if (!currentSlip) return;
      const stake = Math.max(0, parseFloat(document.getElementById('l7SlipStake').value) || 0);
      const profit = stake * (currentSlip.odds - 1);
      document.getElementById('l7SlipProfit').textContent = '+' + profit.toFixed(2);
      document.getElementById('l7SlipLiability').textContent = '-' + stake.toFixed(2);
    }

    function showSlipMessage(msg, isError = false) {
      const alertEl = document.getElementById('l7SlipAlert');
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

    async function submitBet(dealImmediately = false) {
      if (!currentSlip) return;
      const stake = parseFloat(document.getElementById('l7SlipStake').value) || 0;
      if (stake < 10) {
        showSlipMessage('Minimum stake is 10 PTI.', true);
        return;
      }

      const btnPlace = document.getElementById('l7BtnPlaceBet');
      const btnDeal = document.getElementById('l7BtnDealNow');
      btnPlace.disabled = true;
      btnDeal.disabled = true;

      try {
        // Uses the same casino place-bet endpoint!
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
        showSlipMessage(`Bet Placed: ${currentSlip.selectionLabel} @ ${currentSlip.odds} (Stake: ${stake.toFixed(2)})`, false);

        if (dealImmediately) {
          await triggerDealAndSettle();
        }
      } catch (err) {
        showSlipMessage('Network error placing bet.', true);
      } finally {
        btnPlace.disabled = false;
        btnDeal.disabled = false;
      }
    }

    function updateBookPreview() {
      document.querySelectorAll('.l7-book-pl').forEach(el => { el.innerHTML = ''; });
      const totalsBySelection = {};
      openRoundBets.forEach(b => {
        if (!totalsBySelection[b.selection_id]) {
          totalsBySelection[b.selection_id] = { profit: 0, stake: 0 };
        }
        totalsBySelection[b.selection_id].profit += Number(b.potential_profit || 0);
        totalsBySelection[b.selection_id].stake += Number(b.stake || 0);
      });

      Object.keys(totalsBySelection).forEach(selKey => {
        const bookEl = document.getElementById('l7Book_' + selKey);
        if (bookEl) {
          const info = totalsBySelection[selKey];
          bookEl.innerHTML = `<span class="text-success">+${info.profit.toFixed(0)}</span> / <span class="text-danger">-${info.stake.toFixed(0)}</span>`;
        }
      });
    }

    function renderOpenBetsList(settledBets = []) {
      const container = document.getElementById('l7OpenBetsContainer');
      const badge = document.getElementById('l7OpenBetsCount');
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
        html = `<div id="l7NoOpenBetsMsg" class="text-center text-muted py-3 small" style="font-size:11.5px;">Click on any Back odds box to place a Lucky 7 bet.</div>`;
      }
      container.innerHTML = html;
    }

    function filterOpenBets(mktKey) {
      document.querySelectorAll('#l7OpenBetsContainer [data-mkt]').forEach(el => {
        el.style.display = (mktKey === 'all' || el.dataset.mkt.includes(mktKey)) ? '' : 'none';
      });
    }

    function saveQuickStakes() {
      const inputs = Array.from(document.querySelectorAll('.js-l7-stake-input'));
      const values = inputs.map(i => Math.max(10, parseInt(i.value, 10) || 100));
      const grid = document.getElementById('l7QuickStakeGrid');
      if (grid) {
        grid.innerHTML = values.map(v =>
          `<button type="button" class="l7-stake-chip" onclick="LUCKY7.setStake(${v})">+${v}</button>`
        ).join('');
      }
    }

    document.addEventListener('DOMContentLoaded', () => {
      renderRecentBalls();
      updateTimerUI();

      setInterval(() => {
        if (phase === 'BETTING') {
          if (countdown > 1) {
            countdown--;
            updateTimerUI();
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
