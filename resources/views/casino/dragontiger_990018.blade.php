@extends('layouts.app')
@section('title', ($game->event_name ?? 'DRAGON TIGER') . ' - CRICEXPERTS')

@php
  $stakeButtons = auth()->check() ? auth()->user()->getStakeButtons() : [
    ['label' => '100', 'value' => 100], ['label' => '500', 'value' => 500],
    ['label' => '1000', 'value' => 1000], ['label' => '5000', 'value' => 5000],
    ['label' => '10000', 'value' => 10000], ['label' => '25000', 'value' => 25000],
    ['label' => '50000', 'value' => 50000], ['label' => '100000', 'value' => 100000],
  ];

  $cardRanks = ['A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K'];
@endphp

@push('styles')
<style>
  /* Exact 1:1 Styles from https://casino.winbook24.com/casino/99.0018 & media_1794307738399_53170207.jpg */
  body {
    background-color: #f4f4f4;
    font-family: Tahoma, Helvetica, Arial, sans-serif;
  }

  .dt18-page-wrap {
    padding: 2px 7px 20px 7px;
  }

  .dt18-row {
    display: flex;
    flex-wrap: wrap;
    margin: 0 -4px;
  }

  .dt18-col-left {
    width: 16.66666667%;
    padding: 0 4px;
  }

  .dt18-col-center {
    width: 57.33333333%;
    padding: 0 4px;
  }

  .dt18-col-right {
    width: 26%;
    padding: 0 4px;
  }

  @media (max-width: 991.98px) {
    .dt18-col-left {
      display: none;
    }
    .dt18-col-center,
    .dt18-col-right {
      width: 100%;
      margin-bottom: 10px;
    }
  }

  /* Left Sports Sidebar */
  .dt18-sport-box {
    background: #fff;
    border-bottom: 1px solid #d5d5d5;
  }

  .dt18-sport-head {
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

  .dt18-sport-list {
    list-style: none;
    margin: 0;
    padding: 0;
  }

  .dt18-sport-list li a {
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

  .dt18-sport-list li a:hover {
    background: #eef6fb;
    color: #14213d;
  }

  .dt18-sport-arrow {
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
  .dt18-video-player {
    position: relative;
    background: #000000;
    width: 100%;
    text-align: center;
    overflow: hidden;
    user-select: none;
  }

  .dt18-stage-gif {
    width: 75%;
    aspect-ratio: 16 / 9;
    height: auto;
    display: inline-block;
    vertical-align: middle;
  }

  /* Top-Left RID + DRAGON & TIGER Card Overlay */
  .dt18-video-overlay {
    position: absolute;
    top: 0;
    left: 5px;
    text-align: left;
    width: 200px;
    background: rgba(0, 0, 0, 0.25);
    padding: 2px 4px;
    z-index: 5;
  }

  .dt18-rid-line {
    font-size: 10px;
    color: #ffffff;
    font-weight: 700;
    margin: 0 0 2px 0;
    line-height: 1.25;
  }

  .dt18-card-group {
    margin-bottom: 3px;
  }

  .dt18-card-head {
    color: #ffffff;
    font-size: 12px;
    font-weight: 700;
    margin: 1px 0 2px 0;
    line-height: 1.15;
    text-transform: uppercase;
  }

  .dt18-card-img {
    width: 23px;
    height: auto;
    border-radius: 2px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.7);
    transition: transform 0.16s ease;
  }

  .dt18-card-img.flipping {
    transform: rotateY(90deg) scale(1.06);
  }

  /* Top-Right Orange Gear Icon & Circular Countdown Timer */
  .dt18-topright-bar {
    position: absolute;
    top: 5px;
    right: 6px;
    display: flex;
    align-items: center;
    gap: 10px;
    z-index: 6;
  }

  .dt18-cog-btn {
    cursor: pointer;
    color: #fd8f3b;
    font-size: 20px;
    line-height: 1;
    background: transparent;
    border: none;
    padding: 2px;
  }

  .dt18-cog-btn:hover {
    color: #ffa95e;
  }

  .dt18-timer-ring {
    width: 46px;
    height: 46px;
    border-radius: 50%;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    background: conic-gradient(#16a34a var(--dt18-prog, 85%), rgba(255, 255, 255, 0.1) 0);
    transition: opacity 0.2s ease;
  }

  .dt18-timer-ring.danger {
    background: conic-gradient(#dc2626 var(--dt18-prog, 25%), rgba(255, 255, 255, 0.1) 0);
  }

  .dt18-timer-ring.hidden-timer {
    opacity: 0;
    pointer-events: none;
  }

  .dt18-timer-face {
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
  .dt18-winner-toast {
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

  /* Horizontal Table-Row Market Cards (Exact match to media_1794307738399_53170207.jpg & dt_990018_dom.html) */
  .dt18-market-card {
    margin-top: 3px;
    background: #ffffff;
    border: none;
  }

  .dt18-market-header {
    background: linear-gradient(-180deg, #2E4B5E 0%, #243A48 82%);
    color: #ffffff;
    padding: 5px 8px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    line-height: 1.3;
  }

  .dt18-tbl-wrap {
    position: relative;
    background: #ffffff;
  }

  .dt18-tbl-bets {
    width: 100%;
    margin: 0;
    padding: 0;
    border-collapse: collapse;
    background: #ffffff;
  }

  .dt18-tbl-bets tr.odd-row {
    border-bottom: 1px solid #d9d9d9;
  }

  .dt18-tbl-bets td.team-name {
    width: 70%;
    padding: 6px 8px;
    vertical-align: middle;
    background: #ffffff;
  }

  .dt18-tbl-bets td.team-name .title {
    font-size: 11.5px;
    font-weight: 700;
    color: #23282c;
    display: inline-flex;
    align-items: center;
    gap: 3px;
  }

  .dt18-tbl-bets td.team-name .title .red {
    color: #e60000;
    font-size: 13px;
    margin-left: 2px;
  }

  .dt18-tbl-bets td.back-1 {
    width: 15%;
    min-width: 110px;
    background-color: #72bbef !important;
    text-align: center;
    vertical-align: middle;
    padding: 3px 4px;
    cursor: pointer;
    border-left: 1px solid #e0e0e0;
    border-right: 1px solid #e0e0e0;
    transition: filter 0.1s ease;
  }

  .dt18-tbl-bets td.back-1:hover {
    filter: brightness(1.06);
  }

  .dt18-tbl-bets td.back-1.winner-pulse {
    background-color: #86efac !important;
    box-shadow: inset 0 0 0 2px #16a34a;
  }

  .dt18-tbl-bets td.back-1 .dt18-odds-val {
    display: block;
    font-size: 12.5px;
    font-weight: 700;
    color: #1e1e1e;
    line-height: 1.15;
  }

  .dt18-tbl-bets td.back-1 .dt18-odds-vol {
    display: block;
    font-size: 10px;
    font-weight: 400;
    color: #333333;
    line-height: 1.1;
    margin-top: 1px;
  }

  .dt18-tbl-bets td.right-spacer {
    width: 15%;
    background: #ffffff;
  }

  .dt18-book-pl {
    font-size: 10px;
    font-weight: 700;
    margin-left: 6px;
  }

  /* Red SUSPENDED Overlay (Exact match to media_1794307738399_53170207.jpg) */
  .dt18-suspended-overlay {
    position: absolute;
    inset: 0;
    background-color: rgba(255, 255, 255, 0.52);
    border: 1.5px solid #ca1010;
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 4;
    pointer-events: none;
  }

  .dt18-suspended-overlay.active {
    display: flex;
  }

  .dt18-suspended-overlay span {
    font-size: 21px;
    font-weight: 700;
    color: rgba(202, 16, 16, 0.72);
    letter-spacing: 0.5px;
    text-transform: uppercase;
  }

  /* Bottom Recent Result Bar */
  .dt18-recent-bar {
    background: #000000;
    color: #ffffff;
    padding: 6px 8px;
    margin-top: 8px;
    width: 100%;
    display: flex;
    align-items: center;
    white-space: nowrap;
    overflow-x: auto;
  }

  .dt18-recent-title {
    display: inline-block;
    font-size: 16px;
    font-weight: 700;
    margin: 0;
    color: #ffffff;
  }

  .dt18-recent-list {
    padding: 0;
    margin: 0;
    display: inline-flex;
    align-items: center;
    list-style: none;
  }

  .dt18-recent-list li {
    width: 32px;
    height: 32px;
    margin-left: 10px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 700;
    color: #000000;
    cursor: pointer;
    user-select: none;
  }

  .dt18-recent-list li.casino-a {
    background: #72bbef; /* Dragon (D) */
    color: #000000;
  }

  .dt18-recent-list li.casino-b {
    background: #f9a9ba; /* Tiger (T) */
    color: #000000;
  }

  .dt18-recent-list li.casino-c {
    background: #ffff33; /* Tie */
    color: #000000;
  }

  /* Right Sidebar: Open Bets & Bet Slip */
  .dt18-openbets-box {
    background: #ffffff;
    border: 1px solid #d5d5d5;
  }

  .dt18-openbets-head {
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

  .dt18-slip-box {
    background: #d4e8f8;
    border: 1px solid #72bbef;
    padding: 8px;
    margin: 6px;
    border-radius: 4px;
    display: none;
  }

  .dt18-slip-box.active {
    display: block;
  }

  .dt18-stake-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 4px;
    margin-top: 6px;
  }

  .dt18-stake-chip {
    background: #2E4B5E;
    color: #ffffff;
    border: 1px solid #1b2d38;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 700;
    padding: 4px 2px;
    cursor: pointer;
  }

  .dt18-stake-chip:hover {
    background: #3b6078;
  }
</style>
@endpush

@section('full_bleed')
<div class="dt18-page-wrap">
  <div class="dt18-row">

    <!-- ================= LEFT COLUMN: SPORTS SIDEBAR ================= -->
    <div class="dt18-col-left">
      <div class="dt18-sport-box">
        <div class="dt18-sport-head">
          <span><i class="fas fa-ellipsis-v"></i></span>
          <span>Sports</span>
        </div>
        <ul class="dt18-sport-list">
          <li>
            <a href="{{ route('sport.highlight', '4') }}">
              <span>Cricket</span>
              <span class="dt18-sport-arrow"><i class="fas fa-chevron-right"></i></span>
            </a>
          </li>
          <li>
            <a href="{{ route('sport.highlight', '2') }}">
              <span>Tennis</span>
              <span class="dt18-sport-arrow"><i class="fas fa-chevron-right"></i></span>
            </a>
          </li>
          <li>
            <a href="{{ route('sport.highlight', '1') }}">
              <span>Soccer</span>
              <span class="dt18-sport-arrow"><i class="fas fa-chevron-right"></i></span>
            </a>
          </li>
          <li>
            <a href="{{ route('horse.racing') }}">
              <span>Horse Racing</span>
              <span class="dt18-sport-arrow"><i class="fas fa-chevron-right"></i></span>
            </a>
          </li>
          <li>
            <a href="{{ route('horse.racing', ['type' => 'greyhound']) }}">
              <span>Greyhound Racing</span>
              <span class="dt18-sport-arrow"><i class="fas fa-chevron-right"></i></span>
            </a>
          </li>
          <li>
            <a href="{{ route('sport.highlight', '7522') }}">
              <span>Basketball</span>
              <span class="dt18-sport-arrow"><i class="fas fa-chevron-right"></i></span>
            </a>
          </li>
          <li>
            <a href="{{ route('sport.highlight', '66104') }}">
              <span>Lottery</span>
              <span class="dt18-sport-arrow"><i class="fas fa-chevron-right"></i></span>
            </a>
          </li>
        </ul>
      </div>
    </div>

    <!-- ================= CENTER COLUMN: DRAGON TIGER (99.0018) STAGE & 7 HORIZONTAL MARKETS ================= -->
    <div class="dt18-col-center">
      <div class="center-Main">

        <!-- Black 16:9 Universe Casino Stage -->
        <div class="dt18-video-player">
          <img src="/assets/casino/loader.gif" class="dt18-stage-gif" alt="Universe Casino">

          <!-- Top-Left RID + DRAGON & TIGER Card Overlay -->
          <div class="dt18-video-overlay">
            <div class="dt18-rid-line">RID: <span id="dt18RoundIdText">9181011104950</span></div>
            <div class="dt18-card-group">
              <h3 class="dt18-card-head">DRAGON</h3>
              <div>
                <img id="dt18DragonCard" src="/assets/cards/S9_.png" class="dt18-card-img" alt="Dragon Card">
              </div>
            </div>
            <div class="dt18-card-group">
              <h3 class="dt18-card-head">TIGER</h3>
              <div>
                <img id="dt18TigerCard" src="/assets/cards/0.png" class="dt18-card-img" alt="Tiger Card">
              </div>
            </div>
          </div>

          <!-- Top-Right Orange Gear Icon + Circular Countdown Timer -->
          <div class="dt18-topright-bar">
            <button type="button" class="dt18-cog-btn" data-bs-toggle="modal" data-bs-target="#dt18StakeModal" title="Edit Quick Stakes">
              <i class="fas fa-cog"></i>
            </button>
            <div id="dt18TimerRing" class="dt18-timer-ring" style="--dt18-prog: 85%;">
              <div class="dt18-timer-face" id="dt18TimerText">18</div>
            </div>
          </div>

          <!-- Round Winner Toast -->
          <div id="dt18WinnerBanner" class="dt18-winner-toast">
            <i class="fas fa-trophy me-1"></i> <span id="dt18WinnerBannerText">WINNER: DRAGON</span>
          </div>
        </div>

        <!-- 7 Horizontal Table-Row Markets (#marketScroll) -->
        <div id="marketScroll">

          <!-- 1. WINNER (DRAGON @ 2, TIE @ 11, TIGER @ 2) -->
          <div class="dt18-market-card" data-market-key="WINNER">
            <div class="dt18-market-header" data-bs-toggle="collapse" data-bs-target="#dt18Mkt_WINNER" aria-expanded="true">
              <span>WINNER</span>
              <span>Min/Max: 100 - 100000</span>
            </div>
            <div id="dt18Mkt_WINNER" class="collapse show">
              <div class="dt18-tbl-wrap">
                <table class="dt18-tbl-bets">
                  <tbody>
                    <tr class="odd-row">
                      <td class="team-name">
                        <span class="title">DRAGON</span>
                        <span class="dt18-book-pl" id="dt18Book_DRAGON"></span>
                      </td>
                      <td class="back-1" id="dt18OddsBtn_DRAGON"
                          onclick="DT990018.openSlip('WINNER', 'WINNER', 'DRAGON', 'DRAGON', 2, 100, 100000)">
                        <span class="dt18-odds-val">2</span>
                        <span class="dt18-odds-vol">500000</span>
                      </td>
                      <td class="right-spacer"></td>
                    </tr>
                    <tr class="odd-row">
                      <td class="team-name">
                        <span class="title">TIE</span>
                        <span class="dt18-book-pl" id="dt18Book_TIE"></span>
                      </td>
                      <td class="back-1" id="dt18OddsBtn_TIE"
                          onclick="DT990018.openSlip('WINNER', 'WINNER', 'TIE', 'TIE', 11, 100, 100000)">
                        <span class="dt18-odds-val">11</span>
                        <span class="dt18-odds-vol">500000</span>
                      </td>
                      <td class="right-spacer"></td>
                    </tr>
                    <tr class="odd-row">
                      <td class="team-name">
                        <span class="title">TIGER</span>
                        <span class="dt18-book-pl" id="dt18Book_TIGER"></span>
                      </td>
                      <td class="back-1" id="dt18OddsBtn_TIGER"
                          onclick="DT990018.openSlip('WINNER', 'WINNER', 'TIGER', 'TIGER', 2, 100, 100000)">
                        <span class="dt18-odds-val">2</span>
                        <span class="dt18-odds-vol">500000</span>
                      </td>
                      <td class="right-spacer"></td>
                    </tr>
                  </tbody>
                </table>
                <div class="dt18-suspended-overlay js-dt18-suspended">
                  <span>SUSPENDED</span>
                </div>
              </div>
            </div>
          </div>

          <!-- 2. DRAGON ODD/EVEN (DRAGON ODD @ 1.81, DRAGON EVEN @ 2.14) -->
          <div class="dt18-market-card" data-market-key="DRAGON_ODD_EVEN">
            <div class="dt18-market-header" data-bs-toggle="collapse" data-bs-target="#dt18Mkt_DRAGON_OE" aria-expanded="true">
              <span>DRAGON ODD/EVEN</span>
              <span>Min/Max: 100 - 100000</span>
            </div>
            <div id="dt18Mkt_DRAGON_OE" class="collapse show">
              <div class="dt18-tbl-wrap">
                <table class="dt18-tbl-bets">
                  <tbody>
                    <tr class="odd-row">
                      <td class="team-name">
                        <span class="title">DRAGON ODD</span>
                        <span class="dt18-book-pl" id="dt18Book_DRAGON_ODD"></span>
                      </td>
                      <td class="back-1" id="dt18OddsBtn_DRAGON_ODD"
                          onclick="DT990018.openSlip('DRAGON_ODD_EVEN', 'DRAGON ODD/EVEN', 'DRAGON_ODD', 'DRAGON ODD', 1.81, 100, 100000)">
                        <span class="dt18-odds-val">1.81</span>
                        <span class="dt18-odds-vol">500000</span>
                      </td>
                      <td class="right-spacer"></td>
                    </tr>
                    <tr class="odd-row">
                      <td class="team-name">
                        <span class="title">DRAGON EVEN</span>
                        <span class="dt18-book-pl" id="dt18Book_DRAGON_EVEN"></span>
                      </td>
                      <td class="back-1" id="dt18OddsBtn_DRAGON_EVEN"
                          onclick="DT990018.openSlip('DRAGON_ODD_EVEN', 'DRAGON ODD/EVEN', 'DRAGON_EVEN', 'DRAGON EVEN', 2.14, 100, 100000)">
                        <span class="dt18-odds-val">2.14</span>
                        <span class="dt18-odds-vol">500000</span>
                      </td>
                      <td class="right-spacer"></td>
                    </tr>
                  </tbody>
                </table>
                <div class="dt18-suspended-overlay js-dt18-suspended">
                  <span>SUSPENDED</span>
                </div>
              </div>
            </div>
          </div>

          <!-- 3. TIGER ODD/EVEN (TIGER ODD @ 1.81, TIGER EVEN @ 2.14) -->
          <div class="dt18-market-card" data-market-key="TIGER_ODD_EVEN">
            <div class="dt18-market-header" data-bs-toggle="collapse" data-bs-target="#dt18Mkt_TIGER_OE" aria-expanded="true">
              <span>TIGER ODD/EVEN</span>
              <span>Min/Max: 100 - 100000</span>
            </div>
            <div id="dt18Mkt_TIGER_OE" class="collapse show">
              <div class="dt18-tbl-wrap">
                <table class="dt18-tbl-bets">
                  <tbody>
                    <tr class="odd-row">
                      <td class="team-name">
                        <span class="title">TIGER ODD</span>
                        <span class="dt18-book-pl" id="dt18Book_TIGER_ODD"></span>
                      </td>
                      <td class="back-1" id="dt18OddsBtn_TIGER_ODD"
                          onclick="DT990018.openSlip('TIGER_ODD_EVEN', 'TIGER ODD/EVEN', 'TIGER_ODD', 'TIGER ODD', 1.81, 100, 100000)">
                        <span class="dt18-odds-val">1.81</span>
                        <span class="dt18-odds-vol">500000</span>
                      </td>
                      <td class="right-spacer"></td>
                    </tr>
                    <tr class="odd-row">
                      <td class="team-name">
                        <span class="title">TIGER EVEN</span>
                        <span class="dt18-book-pl" id="dt18Book_TIGER_EVEN"></span>
                      </td>
                      <td class="back-1" id="dt18OddsBtn_TIGER_EVEN"
                          onclick="DT990018.openSlip('TIGER_ODD_EVEN', 'TIGER ODD/EVEN', 'TIGER_EVEN', 'TIGER EVEN', 2.14, 100, 100000)">
                        <span class="dt18-odds-val">2.14</span>
                        <span class="dt18-odds-vol">500000</span>
                      </td>
                      <td class="right-spacer"></td>
                    </tr>
                  </tbody>
                </table>
                <div class="dt18-suspended-overlay js-dt18-suspended">
                  <span>SUSPENDED</span>
                </div>
              </div>
            </div>
          </div>

          <!-- 4. DRAGON CARD COLOR (RED ♥ ♦ @ 1.97, BLACK ♠ ♣ @ 1.97) -->
          <div class="dt18-market-card" data-market-key="DRAGON_COLOR">
            <div class="dt18-market-header" data-bs-toggle="collapse" data-bs-target="#dt18Mkt_DRAGON_COLOR" aria-expanded="true">
              <span>DRAGON CARD COLOR</span>
              <span>Min/Max: 100 - 100000</span>
            </div>
            <div id="dt18Mkt_DRAGON_COLOR" class="collapse show">
              <div class="dt18-tbl-wrap">
                <table class="dt18-tbl-bets">
                  <tbody>
                    <tr class="odd-row">
                      <td class="team-name">
                        <span class="title">RED <span class="red">♥ ♦</span></span>
                        <span class="dt18-book-pl" id="dt18Book_DRAGON_RED"></span>
                      </td>
                      <td class="back-1" id="dt18OddsBtn_DRAGON_RED"
                          onclick="DT990018.openSlip('DRAGON_COLOR', 'DRAGON CARD COLOR', 'DRAGON_RED', 'DRAGON RED', 1.97, 100, 100000)">
                        <span class="dt18-odds-val">1.97</span>
                        <span class="dt18-odds-vol">500000</span>
                      </td>
                      <td class="right-spacer"></td>
                    </tr>
                    <tr class="odd-row">
                      <td class="team-name">
                        <span class="title">BLACK ♠ ♣</span>
                        <span class="dt18-book-pl" id="dt18Book_DRAGON_BLACK"></span>
                      </td>
                      <td class="back-1" id="dt18OddsBtn_DRAGON_BLACK"
                          onclick="DT990018.openSlip('DRAGON_COLOR', 'DRAGON CARD COLOR', 'DRAGON_BLACK', 'DRAGON BLACK', 1.97, 100, 100000)">
                        <span class="dt18-odds-val">1.97</span>
                        <span class="dt18-odds-vol">500000</span>
                      </td>
                      <td class="right-spacer"></td>
                    </tr>
                  </tbody>
                </table>
                <div class="dt18-suspended-overlay js-dt18-suspended">
                  <span>SUSPENDED</span>
                </div>
              </div>
            </div>
          </div>

          <!-- 5. TIGER CARD COLOR (RED ♥ ♦ @ 1.97, BLACK ♠ ♣ @ 1.97) -->
          <div class="dt18-market-card" data-market-key="TIGER_COLOR">
            <div class="dt18-market-header" data-bs-toggle="collapse" data-bs-target="#dt18Mkt_TIGER_COLOR" aria-expanded="true">
              <span>TIGER CARD COLOR</span>
              <span>Min/Max: 100 - 100000</span>
            </div>
            <div id="dt18Mkt_TIGER_COLOR" class="collapse show">
              <div class="dt18-tbl-wrap">
                <table class="dt18-tbl-bets">
                  <tbody>
                    <tr class="odd-row">
                      <td class="team-name">
                        <span class="title">RED <span class="red">♥ ♦</span></span>
                        <span class="dt18-book-pl" id="dt18Book_TIGER_RED"></span>
                      </td>
                      <td class="back-1" id="dt18OddsBtn_TIGER_RED"
                          onclick="DT990018.openSlip('TIGER_COLOR', 'TIGER CARD COLOR', 'TIGER_RED', 'TIGER RED', 1.97, 100, 100000)">
                        <span class="dt18-odds-val">1.97</span>
                        <span class="dt18-odds-vol">500000</span>
                      </td>
                      <td class="right-spacer"></td>
                    </tr>
                    <tr class="odd-row">
                      <td class="team-name">
                        <span class="title">BLACK ♠ ♣</span>
                        <span class="dt18-book-pl" id="dt18Book_TIGER_BLACK"></span>
                      </td>
                      <td class="back-1" id="dt18OddsBtn_TIGER_BLACK"
                          onclick="DT990018.openSlip('TIGER_COLOR', 'TIGER CARD COLOR', 'TIGER_BLACK', 'TIGER BLACK', 1.97, 100, 100000)">
                        <span class="dt18-odds-val">1.97</span>
                        <span class="dt18-odds-vol">500000</span>
                      </td>
                      <td class="right-spacer"></td>
                    </tr>
                  </tbody>
                </table>
                <div class="dt18-suspended-overlay js-dt18-suspended">
                  <span>SUSPENDED</span>
                </div>
              </div>
            </div>
          </div>

          <!-- 6. DRAGON CARD (A..K @ 12, Vol 50000) -->
          <div class="dt18-market-card" data-market-key="DRAGON_CARD">
            <div class="dt18-market-header" data-bs-toggle="collapse" data-bs-target="#dt18Mkt_DRAGON_CARD" aria-expanded="true">
              <span>DRAGON CARD</span>
              <span>Min/Max: 100 - 20000</span>
            </div>
            <div id="dt18Mkt_DRAGON_CARD" class="collapse show">
              <div class="dt18-tbl-wrap">
                <table class="dt18-tbl-bets">
                  <tbody>
                    @foreach($cardRanks as $rk)
                      @php $selKey = 'DRAGON_CARD_' . $rk; @endphp
                      <tr class="odd-row">
                        <td class="team-name" style="padding:3px 8px;">
                          <span class="title">
                            <img src="/assets/t10cards/{{ $rk }}.jpeg" style="width:28px;" alt="Dragon Card {{ $rk }}">
                          </span>
                          <span class="dt18-book-pl" id="dt18Book_{{ $selKey }}"></span>
                        </td>
                        <td class="back-1" id="dt18OddsBtn_{{ $selKey }}"
                            onclick="DT990018.openSlip('DRAGON_CARD', 'DRAGON CARD', '{{ $selKey }}', 'DRAGON CARD {{ $rk }}', 12, 100, 20000)">
                          <span class="dt18-odds-val">12</span>
                          <span class="dt18-odds-vol">50000</span>
                        </td>
                        <td class="right-spacer"></td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
                <div class="dt18-suspended-overlay js-dt18-suspended">
                  <span>SUSPENDED</span>
                </div>
              </div>
            </div>
          </div>

          <!-- 7. TIGER CARD (A..K @ 12, Vol 50000) -->
          <div class="dt18-market-card" data-market-key="TIGER_CARD">
            <div class="dt18-market-header" data-bs-toggle="collapse" data-bs-target="#dt18Mkt_TIGER_CARD" aria-expanded="true">
              <span>TIGER CARD</span>
              <span>Min/Max: 100 - 20000</span>
            </div>
            <div id="dt18Mkt_TIGER_CARD" class="collapse show">
              <div class="dt18-tbl-wrap">
                <table class="dt18-tbl-bets">
                  <tbody>
                    @foreach($cardRanks as $rk)
                      @php $selKey = 'TIGER_CARD_' . $rk; @endphp
                      <tr class="odd-row">
                        <td class="team-name" style="padding:3px 8px;">
                          <span class="title">
                            <img src="/assets/t10cards/{{ $rk }}.jpeg" style="width:28px;" alt="Tiger Card {{ $rk }}">
                          </span>
                          <span class="dt18-book-pl" id="dt18Book_{{ $selKey }}"></span>
                        </td>
                        <td class="back-1" id="dt18OddsBtn_{{ $selKey }}"
                            onclick="DT990018.openSlip('TIGER_CARD', 'TIGER CARD', '{{ $selKey }}', 'TIGER CARD {{ $rk }}', 12, 100, 20000)">
                          <span class="dt18-odds-val">12</span>
                          <span class="dt18-odds-vol">50000</span>
                        </td>
                        <td class="right-spacer"></td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
                <div class="dt18-suspended-overlay js-dt18-suspended">
                  <span>SUSPENDED</span>
                </div>
              </div>
            </div>
          </div>

        </div>

        <!-- Bottom Recent Result Bar -->
        <div class="dt18-recent-bar recent-res teenpatti">
          <h5 class="dt18-recent-title">Recent Result</h5>
          <ul class="dt18-recent-list result" id="dt18RecentBalls"></ul>
        </div>

      </div>
    </div>

    <!-- ================= RIGHT COLUMN: OPEN BETS & INTERACTIVE BET SLIP ================= -->
    <div class="dt18-col-right">
      <div class="dt18-openbets-box">
        <div class="dt18-openbets-head">
          <span>Open Bets</span>
          <span class="badge bg-light text-dark" style="font-size:10px;" id="dt18OpenBetsCount">{{ isset($openBets) ? $openBets->count() : 0 }} Active</span>
        </div>

        <div class="p-2 border-bottom">
          <select id="dt18MarketFilter" class="form-select form-select-sm" style="font-size:12px;border-color:#c8ced3;" onchange="DT990018.filterOpenBets(this.value)">
            <option value="all">Select Market</option>
            <option value="WINNER">WINNER</option>
            <option value="DRAGON ODD/EVEN">DRAGON ODD/EVEN</option>
            <option value="TIGER ODD/EVEN">TIGER ODD/EVEN</option>
            <option value="DRAGON CARD COLOR">DRAGON CARD COLOR</option>
            <option value="TIGER CARD COLOR">TIGER CARD COLOR</option>
            <option value="DRAGON CARD">DRAGON CARD</option>
            <option value="TIGER CARD">TIGER CARD</option>
          </select>
        </div>

        <!-- Interactive Dragon Tiger (99.0018) Bet Slip -->
        <div id="dt18BetSlip" class="dt18-slip-box">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <div>
              <span class="badge bg-primary" style="font-size:10px;">BACK</span>
              <strong id="dt18SlipSelectionName" class="ms-1" style="font-size:12px;">DRAGON</strong>
            </div>
            <button type="button" class="btn-close btn-sm" onclick="DT990018.closeSlip()"></button>
          </div>
          <div id="dt18SlipMarketTitle" class="text-muted mb-2" style="font-size:10.5px;">DRAGON TIGER • WINNER</div>

          <div class="row g-2 align-items-center mb-2">
            <div class="col-6">
              <label class="small fw-bold d-block mb-1" style="font-size:11px;">Odds</label>
              <input type="text" id="dt18SlipOdds" class="form-control form-control-sm text-center fw-bold bg-white" readonly value="2">
            </div>
            <div class="col-6">
              <label class="small fw-bold d-block mb-1" style="font-size:11px;">Stake (PTI)</label>
              <input type="number" id="dt18SlipStake" class="form-control form-control-sm text-center fw-bold" value="100" min="10" step="10" oninput="DT990018.updateSlipCalc()">
            </div>
          </div>

          <div class="dt18-stake-grid" id="dt18QuickStakeGrid">
            @foreach($stakeButtons as $sb)
              <button type="button" class="dt18-stake-chip" onclick="DT990018.setStake({{ $sb['value'] }})">+{{ $sb['label'] }}</button>
            @endforeach
          </div>

          <div class="d-flex justify-content-between small mt-2 pt-1 border-top border-secondary-subtle" style="font-size:11px;">
            <span>Profit: <strong class="text-success" id="dt18SlipProfit">+100.00</strong></span>
            <span>Liability: <strong class="text-danger" id="dt18SlipLiability">-100.00</strong></span>
          </div>

          <div id="dt18SlipAlert" class="alert py-1 px-2 mt-2 mb-0 small fw-bold d-none" style="font-size:11px;"></div>

          <div class="d-flex justify-content-between gap-1 mt-2">
            <button type="button" class="btn btn-sm btn-secondary fw-bold flex-fill" style="font-size:11px;" onclick="DT990018.closeSlip()">Cancel</button>
            <button type="button" id="dt18BtnPlaceBet" class="btn btn-sm btn-primary fw-bold flex-fill" style="font-size:11px;background:#243A48;border-color:#14213D;" onclick="DT990018.submitBet(false)">Place Bet</button>
            <button type="button" id="dt18BtnDealNow" class="btn btn-sm btn-success fw-bold flex-fill" style="font-size:11px;" onclick="DT990018.submitBet(true)" title="Place Bet & Reveal Cards Immediately">Bet &amp; Deal</button>
          </div>
        </div>

        <!-- Active & Settled Round Bets List -->
        <div id="dt18OpenBetsContainer" class="p-2" style="max-height:420px;overflow-y:auto;">
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
            <div id="dt18NoOpenBetsMsg" class="text-center text-muted py-3 small" style="font-size:11.5px;">
              Click on any Back odds cell to place a Dragon Tiger bet.
            </div>
          @endif
        </div>
      </div>
    </div>

  </div>
</div>

<!-- ================= QUICK STAKE SETTINGS MODAL (Gear Icon) ================= -->
<div class="modal fade" id="dt18StakeModal" tabindex="-1" aria-hidden="true">
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
              <input type="number" class="form-control form-control-sm fw-bold text-center js-dt18-stake-input" value="{{ $sb['value'] }}">
            </div>
          @endforeach
        </div>
        <button type="button" class="btn btn-success w-100 fw-bold mt-3 btn-sm" onclick="DT990018.saveQuickStakes()" data-bs-dismiss="modal">
          Save
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ================= ROUND RESULT DETAIL MODAL (#dragonTigerResultModal) ================= -->
<div class="modal fade" id="dragonTigerResultModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header py-2" style="background:linear-gradient(-180deg, #2E4B5E 0%, #243A48 82%);color:#fff;">
        <h6 class="modal-title fw-bold mb-0" style="font-size:13.5px;">DRAGON TIGER RESULT</h6>
        <button type="button" class="btn-close btn-close-white btn-sm" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center p-3">
        <h6 class="fw-bold mb-3" style="font-size:13px;">Round Id: <span id="dt18ModalRoundId">9181011104950</span></h6>
        <div class="row g-2 justify-content-center my-2">
          <div class="col-6">
            <h6 class="fw-bold small mb-2">Dragon</h6>
            <div>
              <img id="dt18ModalDragonCard" src="/assets/cards/S9_.png" style="width:52px;border-radius:4px;box-shadow:0 2px 8px rgba(0,0,0,0.4);" alt="Dragon Card">
            </div>
          </div>
          <div class="col-6">
            <h6 class="fw-bold small mb-2">Tiger</h6>
            <div>
              <img id="dt18ModalTigerCard" src="/assets/cards/H6_.png" style="width:52px;border-radius:4px;box-shadow:0 2px 8px rgba(0,0,0,0.4);" alt="Tiger Card">
            </div>
          </div>
        </div>
        <div class="fw-bold text-success mt-3" style="font-size:13px;" id="dt18ModalWinnerText">WINNER: DRAGON</div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  const DT990018 = (() => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const eventId = @json($game->event_id ?? '99.0018');

    let roundId = '9181011104950';
    let countdown = 18;
    const maxCountdown = 20;
    let phase = 'BETTING'; // 'BETTING' or 'DEALING'
    let currentSlip = null;
    let openRoundBets = [];
    let settlingInProgress = false;

    const recentHistory = [
      { roundId: '9181011104949', badge: 'T', dragon: 'C7_',  tiger: 'HK_',  winner: 'TIGER (Dragon: 7♣ vs Tiger: K♥)' },
      { roundId: '9181011104948', badge: 'T', dragon: 'D5_',  tiger: 'SQ_',  winner: 'TIGER (Dragon: 5♦ vs Tiger: Q♠)' },
      { roundId: '9181011104947', badge: 'D', dragon: 'S9_',  tiger: 'H6_',  winner: 'DRAGON (Dragon: 9♠ vs Tiger: 6♥)' },
      { roundId: '9181011104946', badge: 'T', dragon: 'H4_',  tiger: 'CJ_',  winner: 'TIGER (Dragon: 4♥ vs Tiger: J♣)' },
      { roundId: '9181011104945', badge: 'D', dragon: 'DJ_',  tiger: 'S4_',  winner: 'DRAGON (Dragon: J♦ vs Tiger: 4♠)' },
      { roundId: '9181011104944', badge: 'D', dragon: 'S10_', tiger: 'H3_',  winner: 'DRAGON (Dragon: 10♠ vs Tiger: 3♥)' },
      { roundId: '9181011104943', badge: 'T', dragon: 'C3_',  tiger: 'D8_',  winner: 'TIGER (Dragon: 3♣ vs Tiger: 8♦)' },
      { roundId: '9181011104942', badge: 'T', dragon: 'H2_',  tiger: 'SJ_',  winner: 'TIGER (Dragon: 2♥ vs Tiger: J♠)' },
      { roundId: '9181011104941', badge: 'D', dragon: 'DK_',  tiger: 'C10_', winner: 'DRAGON (Dragon: K♦ vs Tiger: 10♣)' },
      { roundId: '9181011104940', badge: 'D', dragon: 'SK_',  tiger: 'S7_',  winner: 'DRAGON (Dragon: K♠ vs Tiger: 7♠)' },
    ];

    function setCard(imgId, cardCode) {
      const el = document.getElementById(imgId);
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
      document.querySelectorAll('.js-dt18-suspended').forEach(el => {
        el.classList.toggle('active', isSuspended);
      });
      const timerRing = document.getElementById('dt18TimerRing');
      if (timerRing) {
        timerRing.classList.toggle('hidden-timer', isSuspended);
      }
    }

    function updateTimerUI() {
      const textEl = document.getElementById('dt18TimerText');
      const ringEl = document.getElementById('dt18TimerRing');
      if (textEl) textEl.textContent = String(countdown);
      if (ringEl) {
        const pct = Math.max(5, Math.round((countdown / maxCountdown) * 100));
        ringEl.style.setProperty('--dt18-prog', pct + '%');
        ringEl.classList.toggle('danger', countdown <= 5);
      }
    }

    function renderRecentBalls() {
      const container = document.getElementById('dt18RecentBalls');
      if (!container) return;
      container.innerHTML = recentHistory.slice(0, 10).map((item, idx) => {
        const cls = item.badge === 'D' ? 'casino-a' : (item.badge === 'T' ? 'casino-b' : 'casino-c');
        return `
          <li class="${cls}"
              onclick="DT990018.showResultModal(${idx})"
              title="Round ${item.roundId}: ${item.winner}">
            <a class="number text-dark" style="text-decoration:none;">${item.badge}</a>
          </li>
        `;
      }).join('');
    }

    function showResultModal(idx) {
      const item = recentHistory[idx];
      if (!item) return;
      document.getElementById('dt18ModalRoundId').textContent = item.roundId;
      document.getElementById('dt18ModalDragonCard').src = `/assets/cards/${item.dragon}.png`;
      document.getElementById('dt18ModalTigerCard').src = `/assets/cards/${item.tiger}.png`;
      document.getElementById('dt18ModalWinnerText').textContent = `WINNER: ${item.winner}`;
      const modal = new bootstrap.Modal(document.getElementById('dragonTigerResultModal'));
      modal.show();
    }

    function startNewBettingRound() {
      roundId = '91810' + Math.floor(11104951 + Math.random() * 80000);
      const ridEl = document.getElementById('dt18RoundIdText');
      if (ridEl) ridEl.textContent = roundId;

      setCard('dt18DragonCard', '0');
      setCard('dt18TigerCard', '0');
      document.querySelectorAll('.dt18-tbl-bets td.back-1').forEach(b => b.classList.remove('winner-pulse'));
      const banner = document.getElementById('dt18WinnerBanner');
      if (banner) banner.style.display = 'none';

      phase = 'BETTING';
      countdown = 18;
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
        const res = await fetch('/api/casino/dragontiger/settle-round', {
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
          setCard('dt18DragonCard', data.card_dragon || 'S9_');
          setTimeout(() => {
            setCard('dt18TigerCard', data.card_tiger || 'H6_');
          }, 120);

          setTimeout(() => {
            (data.winning_keys || []).forEach(k => {
              document.getElementById('dt18OddsBtn_' + k)?.classList.add('winner-pulse');
            });

            const banner = document.getElementById('dt18WinnerBanner');
            const bannerText = document.getElementById('dt18WinnerBannerText');
            if (banner && bannerText) {
              bannerText.textContent = `WINNER: ${data.summary}`;
              banner.style.display = 'block';
            }

            if (data.wallet) {
              updateHeaderWallet(data.wallet);
            }

            openRoundBets = [];
            document.querySelectorAll('.dt18-book-pl').forEach(el => { el.innerHTML = ''; });
            renderOpenBetsList(data.settled_bets || []);

            recentHistory.unshift({
              roundId: data.round_id || roundId,
              badge: data.badge || 'D',
              dragon: data.card_dragon || 'S9_',
              tiger: data.card_tiger || 'H6_',
              winner: data.summary || data.winner || 'DRAGON'
            });
            renderRecentBalls();
          }, 300);
        }
      } catch (e) {
        console.error('Dragon Tiger 99.0018 settle error:', e);
      } finally {
        setTimeout(() => {
          settlingInProgress = false;
          startNewBettingRound();
        }, 3800);
      }
    }

    function openSlip(marketKey, marketTitle, selectionKey, selectionLabel, odds, minBet, maxBet) {
      currentSlip = { marketKey, marketTitle, selectionKey, selectionLabel, odds, minBet, maxBet };
      document.getElementById('dt18SlipSelectionName').textContent = selectionLabel;
      document.getElementById('dt18SlipMarketTitle').textContent = `DRAGON TIGER • ${marketTitle}`;
      document.getElementById('dt18SlipOdds').value = odds;
      const stakeInput = document.getElementById('dt18SlipStake');
      if (!stakeInput.value || Number(stakeInput.value) < minBet) {
        stakeInput.value = minBet;
      }
      document.getElementById('dt18SlipAlert').classList.add('d-none');
      document.getElementById('dt18BetSlip').classList.add('active');
      updateSlipCalc();
    }

    function closeSlip() {
      document.getElementById('dt18BetSlip').classList.remove('active');
      currentSlip = null;
    }

    function setStake(val) {
      document.getElementById('dt18SlipStake').value = val;
      updateSlipCalc();
    }

    function updateSlipCalc() {
      if (!currentSlip) return;
      const stake = Math.max(0, parseFloat(document.getElementById('dt18SlipStake').value) || 0);
      const profit = stake * (currentSlip.odds - 1);
      document.getElementById('dt18SlipProfit').textContent = '+' + profit.toFixed(2);
      document.getElementById('dt18SlipLiability').textContent = '-' + stake.toFixed(2);
    }

    function showSlipMessage(msg, isError = false) {
      const alertEl = document.getElementById('dt18SlipAlert');
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
      const stake = parseFloat(document.getElementById('dt18SlipStake').value) || 0;
      if (stake < 10) {
        showSlipMessage('Minimum stake is 10 PTI.', true);
        return;
      }

      const btnPlace = document.getElementById('dt18BtnPlaceBet');
      const btnDeal = document.getElementById('dt18BtnDealNow');
      btnPlace.disabled = true;
      btnDeal.disabled = true;

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
      document.querySelectorAll('.dt18-book-pl').forEach(el => { el.innerHTML = ''; });
      const totalsBySelection = {};
      openRoundBets.forEach(b => {
        if (!totalsBySelection[b.selection_id]) {
          totalsBySelection[b.selection_id] = { profit: 0, stake: 0 };
        }
        totalsBySelection[b.selection_id].profit += Number(b.potential_profit || 0);
        totalsBySelection[b.selection_id].stake += Number(b.stake || 0);
      });

      Object.keys(totalsBySelection).forEach(selKey => {
        const bookEl = document.getElementById('dt18Book_' + selKey);
        if (bookEl) {
          const info = totalsBySelection[selKey];
          bookEl.innerHTML = `<span class="text-success">+${info.profit.toFixed(0)}</span> / <span class="text-danger">-${info.stake.toFixed(0)}</span>`;
        }
      });
    }

    function renderOpenBetsList(settledBets = []) {
      const container = document.getElementById('dt18OpenBetsContainer');
      const badge = document.getElementById('dt18OpenBetsCount');
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
        html = `<div id="dt18NoOpenBetsMsg" class="text-center text-muted py-3 small" style="font-size:11.5px;">Click on any Back odds cell to place a Dragon Tiger bet.</div>`;
      }
      container.innerHTML = html;
    }

    function filterOpenBets(mktKey) {
      document.querySelectorAll('#dt18OpenBetsContainer [data-mkt]').forEach(el => {
        el.style.display = (mktKey === 'all' || el.dataset.mkt.includes(mktKey)) ? '' : 'none';
      });
    }

    function saveQuickStakes() {
      const inputs = Array.from(document.querySelectorAll('.js-dt18-stake-input'));
      const values = inputs.map(i => Math.max(10, parseInt(i.value, 10) || 100));
      const grid = document.getElementById('dt18QuickStakeGrid');
      if (grid) {
        grid.innerHTML = values.map(v =>
          `<button type="button" class="dt18-stake-chip" onclick="DT990018.setStake(${v})">+${v}</button>`
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

