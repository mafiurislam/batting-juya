@extends('layouts.app')
@section('title', ($game->event_name ?? '20-20 DRAGON TIGER') . ' - CRICEXPERTS')

@php
  $stakeButtons = auth()->check() ? auth()->user()->getStakeButtons() : [
    ['label' => '100', 'value' => 100], ['label' => '500', 'value' => 500],
    ['label' => '1000', 'value' => 1000], ['label' => '5000', 'value' => 5000],
    ['label' => '10000', 'value' => 10000], ['label' => '25000', 'value' => 25000],
    ['label' => '50000', 'value' => 50000], ['label' => '100000', 'value' => 100000],
  ];
@endphp

@push('styles')
<style>
  /* Exact 1:1 Styles from https://casino.winbook24.com/casino3Patti/99.0019 */
  body {
    background-color: #f4f4f4;
    font-family: Tahoma, Helvetica, Arial, sans-serif;
  }

  .dt-page-wrap {
    padding: 2px 7px 20px 7px;
  }

  .dt-row {
    display: flex;
    flex-wrap: wrap;
    margin: 0 -4px;
  }

  .dt-col-left {
    width: 16.66666667%;
    padding: 0 4px;
  }

  .dt-col-center {
    width: 57.33333333%;
    padding: 0 4px;
  }

  .dt-col-right {
    width: 26%;
    padding: 0 4px;
  }

  @media (max-width: 991.98px) {
    .dt-col-left {
      display: none;
    }
    .dt-col-center,
    .dt-col-right {
      width: 100%;
      margin-bottom: 10px;
    }
  }

  /* Left Sports Sidebar */
  .dt-sport-box {
    background: #fff;
    border-bottom: 1px solid #d5d5d5;
  }

  .dt-sport-head {
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

  .dt-sport-list {
    list-style: none;
    margin: 0;
    padding: 0;
  }

  .dt-sport-list li a {
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

  .dt-sport-list li a:hover {
    background: #eef6fb;
    color: #14213d;
  }

  .dt-sport-arrow {
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
  .dt-video-player {
    position: relative;
    background: #000000;
    width: 100%;
    text-align: center;
    overflow: hidden;
    user-select: none;
  }

  .dt-stage-gif {
    width: 75%;
    aspect-ratio: 16 / 9;
    height: auto;
    display: inline-block;
    vertical-align: middle;
  }

  /* Top-Left RID + DRAGON & TIGER Cards Overlay (Exact match to screenshot) */
  .dt-video-overlay {
    position: absolute;
    top: 0;
    left: 5px;
    text-align: left;
    width: 200px;
    background: rgba(0, 0, 0, 0.25);
    padding: 2px 4px;
    z-index: 5;
  }

  .dt-rid-line {
    font-size: 10px;
    color: #ffffff;
    font-weight: 700;
    margin: 0 0 2px 0;
    line-height: 1.25;
  }

  .dt-card-group {
    margin-bottom: 3px;
  }

  .dt-card-head {
    color: #ffffff;
    font-size: 12px;
    font-weight: 700;
    margin: 1px 0 2px 0;
    line-height: 1.15;
    text-transform: uppercase;
  }

  .dt-card-img {
    width: 23px;
    height: auto;
    border-radius: 2px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.7);
    transition: transform 0.16s ease;
  }

  .dt-card-img.flipping {
    transform: rotateY(90deg) scale(1.06);
  }

  /* Top-Right Orange Gear Icon & Circular Countdown Timer (19) */
  .dt-topright-bar {
    position: absolute;
    top: 5px;
    right: 6px;
    display: flex;
    align-items: center;
    gap: 10px;
    z-index: 6;
  }

  .dt-cog-btn {
    cursor: pointer;
    color: #fd8f3b;
    font-size: 20px;
    line-height: 1;
    background: transparent;
    border: none;
    padding: 2px;
  }

  .dt-cog-btn:hover {
    color: #ffa95e;
  }

  .dt-timer-ring {
    width: 46px;
    height: 46px;
    border-radius: 50%;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    background: conic-gradient(#16a34a var(--dt-prog, 85%), rgba(255, 255, 255, 0.1) 0);
    transition: opacity 0.2s ease;
  }

  .dt-timer-ring.danger {
    background: conic-gradient(#dc2626 var(--dt-prog, 25%), rgba(255, 255, 255, 0.1) 0);
  }

  .dt-timer-ring.hidden-timer {
    opacity: 0;
    pointer-events: none;
  }

  .dt-timer-face {
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
  .dt-winner-toast {
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

  /* WINNER Market Section Below Stage (Exact match to media_1794305244441_a35bf8f1.jpg) */
  .dt-market-block {
    margin-top: 4px;
  }

  .dt-market-header {
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

  .dt-market-body {
    position: relative;
    background: linear-gradient(90deg, rgb(153, 199, 241) 0%, rgba(138, 189, 216, 0.5) 49%, rgb(146, 198, 246) 100%);
    padding: 8px 10px 14px 10px;
    border-bottom: 1px solid #b8d4ea;
  }

  .dt-odd-row {
    display: flex;
    align-items: center;
    justify-content: space-around;
    width: 100%;
    padding-top: 2px;
    padding-bottom: 10px;
  }

  .dt-odd-row:last-of-type {
    padding-bottom: 2px;
  }

  .dt-box-player {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
  }

  .dt-runner-title {
    font-size: 12px;
    font-weight: 700;
    color: #23282c;
    margin-bottom: 4px;
    text-transform: uppercase;
  }

  .dt-odds-box {
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
    min-height: 40px;
    transition: transform 0.1s ease, filter 0.1s ease;
  }

  .dt-odds-box:hover {
    filter: brightness(1.05);
    transform: translateY(-1px);
  }

  .dt-odds-box.winner-pulse {
    box-shadow: 0 0 0 2px #16a34a, 0 2px 10px rgba(22, 163, 74, 0.65);
    background: #86efac;
  }

  .dt-odds-val {
    font-size: 15px;
    font-weight: 700;
    color: #1e1e1e;
    line-height: 1.2;
  }

  .dt-odds-vol {
    font-size: 11px;
    font-weight: 400;
    color: #1e1e1e;
    line-height: 1.1;
    margin-top: 1px;
  }

  .dt-book-pl {
    font-size: 10px;
    font-weight: 700;
    margin-top: 2px;
    min-height: 12px;
  }

  /* Red SUSPENDED Overlay */
  .dt-suspended-overlay {
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

  .dt-suspended-overlay.active {
    display: flex;
  }

  .dt-suspended-overlay span {
    font-size: 25px;
    font-weight: 700;
    color: #ca1010;
    letter-spacing: 0.5px;
    text-transform: uppercase;
  }

  /* Bottom Recent Result Bar (Exact match to media_1794305244441_a35bf8f1.jpg & dt_990019_dom.html) */
  .dt-recent-bar {
    background: #000000;
    color: #ffffff;
    padding: 6px 8px;
    margin-top: 10px;
    width: 100%;
    display: flex;
    align-items: center;
    white-space: nowrap;
    overflow-x: auto;
  }

  .dt-recent-title {
    display: inline-block;
    font-size: 16.5px;
    font-weight: 700;
    margin: 0;
    color: #ffffff;
  }

  .dt-recent-list {
    padding: 0;
    margin: 0;
    display: inline-flex;
    align-items: center;
    list-style: none;
  }

  .dt-recent-list li {
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

  .dt-recent-list li.casino-a {
    background: #72bbef; /* Dragon (D) - Blue Circle */
    color: #000000;
  }

  .dt-recent-list li.casino-b {
    background: #f9a9ba; /* Tiger (T) - Pink Circle */
    color: #000000;
  }

  .dt-recent-list li.casino-c {
    background: #ffff33; /* Tie - Yellow Circle */
    color: #000000;
  }

  /* Right Sidebar: Open Bets & Bet Slip */
  .dt-openbets-box {
    background: #ffffff;
    border: 1px solid #d5d5d5;
  }

  .dt-openbets-head {
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

  .dt-slip-box {
    background: #d4e8f8;
    border: 1px solid #72bbef;
    padding: 8px;
    margin: 6px;
    border-radius: 4px;
    display: none;
  }

  .dt-slip-box.active {
    display: block;
  }

  .dt-stake-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 4px;
    margin-top: 6px;
  }

  .dt-stake-chip {
    background: #2E4B5E;
    color: #ffffff;
    border: 1px solid #1b2d38;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 700;
    padding: 4px 2px;
    cursor: pointer;
  }

  .dt-stake-chip:hover {
    background: #3b6078;
  }
</style>
@endpush

@section('full_bleed')
<div class="dt-page-wrap">
  <div class="dt-row">

    <!-- ================= LEFT COLUMN: SPORTS SIDEBAR ================= -->
    <div class="dt-col-left">
      <div class="dt-sport-box">
        <div class="dt-sport-head">
          <span><i class="fas fa-ellipsis-v"></i></span>
          <span>Sports</span>
        </div>
        <ul class="dt-sport-list">
          <li>
            <a href="{{ route('sport.highlight', '4') }}">
              <span>Cricket</span>
              <span class="dt-sport-arrow"><i class="fas fa-chevron-right"></i></span>
            </a>
          </li>
          <li>
            <a href="{{ route('sport.highlight', '2') }}">
              <span>Tennis</span>
              <span class="dt-sport-arrow"><i class="fas fa-chevron-right"></i></span>
            </a>
          </li>
          <li>
            <a href="{{ route('sport.highlight', '1') }}">
              <span>Soccer</span>
              <span class="dt-sport-arrow"><i class="fas fa-chevron-right"></i></span>
            </a>
          </li>
          <li>
            <a href="{{ route('horse.racing') }}">
              <span>Horse Racing</span>
              <span class="dt-sport-arrow"><i class="fas fa-chevron-right"></i></span>
            </a>
          </li>
          <li>
            <a href="{{ route('horse.racing', ['type' => 'greyhound']) }}">
              <span>Greyhound Racing</span>
              <span class="dt-sport-arrow"><i class="fas fa-chevron-right"></i></span>
            </a>
          </li>
          <li>
            <a href="{{ route('sport.highlight', '7522') }}">
              <span>Basketball</span>
              <span class="dt-sport-arrow"><i class="fas fa-chevron-right"></i></span>
            </a>
          </li>
          <li>
            <a href="{{ route('sport.highlight', '66104') }}">
              <span>Lottery</span>
              <span class="dt-sport-arrow"><i class="fas fa-chevron-right"></i></span>
            </a>
          </li>
        </ul>
      </div>
    </div>

    <!-- ================= CENTER COLUMN: 20-20 DRAGON TIGER (99.0019) STAGE & WINNER MARKET ================= -->
    <div class="dt-col-center">
      <div class="center-Main">

        <!-- Black 16:9 Universe Casino Stage -->
        <div class="dt-video-player">
          <img src="/assets/casino/loader.gif" class="dt-stage-gif" alt="Universe Casino">

          <!-- Top-Left RID + DRAGON & TIGER Card Overlay (Matches media_1794305244441_a35bf8f1.jpg) -->
          <div class="dt-video-overlay">
            <div class="dt-rid-line">RID: <span id="dtRoundIdText">9191011100500</span></div>
            <div class="dt-card-group">
              <h3 class="dt-card-head">DRAGON</h3>
              <div>
                <img id="dtDragonCard" src="/assets/cards/0.png" class="dt-card-img" alt="Dragon Card">
              </div>
            </div>
            <div class="dt-card-group">
              <h3 class="dt-card-head">TIGER</h3>
              <div>
                <img id="dtTigerCard" src="/assets/cards/0.png" class="dt-card-img" alt="Tiger Card">
              </div>
            </div>
          </div>

          <!-- Top-Right Orange Gear Icon + Circular Countdown Timer (19) -->
          <div class="dt-topright-bar">
            <button type="button" class="dt-cog-btn" data-bs-toggle="modal" data-bs-target="#dtStakeModal" title="Edit Quick Stakes">
              <i class="fas fa-cog"></i>
            </button>
            <div id="dtTimerRing" class="dt-timer-ring" style="--dt-prog: 95%;">
              <div class="dt-timer-face" id="dtTimerText">19</div>
            </div>
          </div>

          <!-- Round Winner Toast -->
          <div id="dtWinnerBanner" class="dt-winner-toast">
            <i class="fas fa-trophy me-1"></i> <span id="dtWinnerBannerText">WINNER: DRAGON</span>
          </div>
        </div>

        <!-- WINNER Market Section (#marketScroll) -->
        <div id="marketScroll">
          <div class="dt-market-block" data-market-key="WINNER">
            <div class="dt-market-header" data-bs-toggle="collapse" data-bs-target="#dtMkt_WINNER" aria-expanded="true">
              <span>WINNER</span>
              <span>Min/Max: 100 - 100000</span>
            </div>
            <div id="dtMkt_WINNER" class="collapse show">
              <div class="dt-market-body">
                <!-- Row 1: DRAGON (2) & TIGER (2) -->
                <div class="dt-odd-row">
                  <div class="dt-box-player">
                    <div class="dt-runner-title">DRAGON</div>
                    <button type="button" id="dtOddsBtn_DRAGON" class="dt-odds-box"
                            onclick="DRAGONTIGER.openSlip('WINNER', 'WINNER', 'DRAGON', 'DRAGON', 2, 100, 100000)">
                      <span class="dt-odds-val">2</span>
                      <span class="dt-odds-vol">500000</span>
                    </button>
                    <div class="dt-book-pl" id="dtBook_DRAGON"></div>
                  </div>

                  <div class="dt-box-player">
                    <div class="dt-runner-title">TIGER</div>
                    <button type="button" id="dtOddsBtn_TIGER" class="dt-odds-box"
                            onclick="DRAGONTIGER.openSlip('WINNER', 'WINNER', 'TIGER', 'TIGER', 2, 100, 100000)">
                      <span class="dt-odds-val">2</span>
                      <span class="dt-odds-vol">500000</span>
                    </button>
                    <div class="dt-book-pl" id="dtBook_TIGER"></div>
                  </div>
                </div>

                <!-- Row 2: TIE (11) centered -->
                <div class="dt-odd-row">
                  <div class="dt-box-player">
                    <div class="dt-runner-title">TIE</div>
                    <button type="button" id="dtOddsBtn_TIE" class="dt-odds-box"
                            onclick="DRAGONTIGER.openSlip('WINNER', 'WINNER', 'TIE', 'TIE', 11, 100, 100000)">
                      <span class="dt-odds-val">11</span>
                      <span class="dt-odds-vol">500000</span>
                    </button>
                    <div class="dt-book-pl" id="dtBook_TIE"></div>
                  </div>
                </div>

                <div class="dt-suspended-overlay js-dt-suspended">
                  <span>SUSPENDED</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Bottom Recent Result Bar (Exact match to media_1794305244441_a35bf8f1.jpg: T T D D D D D T T D) -->
        <div class="dt-recent-bar recent-res teenpatti">
          <h5 class="dt-recent-title">Recent Result</h5>
          <ul class="dt-recent-list result" id="dtRecentBalls"></ul>
        </div>

      </div>
    </div>

    <!-- ================= RIGHT COLUMN: OPEN BETS & INTERACTIVE BET SLIP ================= -->
    <div class="dt-col-right">
      <div class="dt-openbets-box">
        <div class="dt-openbets-head">
          <span>Open Bets</span>
          <span class="badge bg-light text-dark" style="font-size:10px;" id="dtOpenBetsCount">{{ isset($openBets) ? $openBets->count() : 0 }} Active</span>
        </div>

        <div class="p-2 border-bottom">
          <select id="dtMarketFilter" class="form-select form-select-sm" style="font-size:12px;border-color:#c8ced3;" onchange="DRAGONTIGER.filterOpenBets(this.value)">
            <option value="all">Select Market</option>
            <option value="WINNER">WINNER</option>
          </select>
        </div>

        <!-- Interactive 20-20 Dragon Tiger Bet Slip -->
        <div id="dtBetSlip" class="dt-slip-box">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <div>
              <span class="badge bg-primary" style="font-size:10px;">BACK</span>
              <strong id="dtSlipSelectionName" class="ms-1" style="font-size:12px;">DRAGON</strong>
            </div>
            <button type="button" class="btn-close btn-sm" onclick="DRAGONTIGER.closeSlip()"></button>
          </div>
          <div id="dtSlipMarketTitle" class="text-muted mb-2" style="font-size:10.5px;">20-20 DRAGON TIGER • WINNER</div>

          <div class="row g-2 align-items-center mb-2">
            <div class="col-6">
              <label class="small fw-bold d-block mb-1" style="font-size:11px;">Odds</label>
              <input type="text" id="dtSlipOdds" class="form-control form-control-sm text-center fw-bold bg-white" readonly value="2">
            </div>
            <div class="col-6">
              <label class="small fw-bold d-block mb-1" style="font-size:11px;">Stake (PTI)</label>
              <input type="number" id="dtSlipStake" class="form-control form-control-sm text-center fw-bold" value="100" min="10" step="10" oninput="DRAGONTIGER.updateSlipCalc()">
            </div>
          </div>

          <div class="dt-stake-grid" id="dtQuickStakeGrid">
            @foreach($stakeButtons as $sb)
              <button type="button" class="dt-stake-chip" onclick="DRAGONTIGER.setStake({{ $sb['value'] }})">+{{ $sb['label'] }}</button>
            @endforeach
          </div>

          <div class="d-flex justify-content-between small mt-2 pt-1 border-top border-secondary-subtle" style="font-size:11px;">
            <span>Profit: <strong class="text-success" id="dtSlipProfit">+100.00</strong></span>
            <span>Liability: <strong class="text-danger" id="dtSlipLiability">-100.00</strong></span>
          </div>

          <div id="dtSlipAlert" class="alert py-1 px-2 mt-2 mb-0 small fw-bold d-none" style="font-size:11px;"></div>

          <div class="d-flex justify-content-between gap-1 mt-2">
            <button type="button" class="btn btn-sm btn-secondary fw-bold flex-fill" style="font-size:11px;" onclick="DRAGONTIGER.closeSlip()">Cancel</button>
            <button type="button" id="dtBtnPlaceBet" class="btn btn-sm btn-success fw-bold flex-fill" style="font-size:11px;" onclick="DRAGONTIGER.submitBet(false)">Place Bet</button>
          </div>
        </div>

        <!-- Active & Settled Round Bets List -->
        <div id="dtOpenBetsContainer" class="p-2" style="max-height:420px;overflow-y:auto;">
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
            <div id="dtNoOpenBetsMsg" class="text-center text-muted py-3 small" style="font-size:11.5px;">
              Click on DRAGON, TIGER, or TIE odds box to place a bet.
            </div>
          @endif
        </div>
      </div>
    </div>

  </div>
</div>

<!-- ================= QUICK STAKE SETTINGS MODAL (Gear Icon) ================= -->
<div class="modal fade" id="dtStakeModal" tabindex="-1" aria-hidden="true">
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
              <input type="number" class="form-control form-control-sm fw-bold text-center js-dt-stake-input" value="{{ $sb['value'] }}">
            </div>
          @endforeach
        </div>
        <button type="button" class="btn btn-success w-100 fw-bold mt-3 btn-sm" onclick="DRAGONTIGER.saveQuickStakes()" data-bs-dismiss="modal">
          Save
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ================= ROUND RESULT DETAIL MODAL (#dragonTiger2020 - Clicking T / D in Recent Result) ================= -->
<div class="modal fade" id="dragonTiger2020" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header py-2" style="background:linear-gradient(-180deg, #2E4B5E 0%, #243A48 82%);color:#fff;">
        <h6 class="modal-title fw-bold mb-0" style="font-size:13.5px;">20-20 DRAGON TIGER RESULT</h6>
        <button type="button" class="btn-close btn-close-white btn-sm" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center p-3">
        <h6 class="fw-bold mb-3" style="font-size:13px;">Round Id: <span id="dtModalRoundId">9191011100500</span></h6>
        <div class="row g-2 justify-content-center my-2">
          <div class="col-6 paati_boxs">
            <h6 class="fw-bold small mb-2">Dragon</h6>
            <div class="card-Img-box">
              <img id="dtModalDragonCard" src="/assets/cards/SK_.png" style="width:52px;border-radius:4px;box-shadow:0 2px 8px rgba(0,0,0,0.4);" alt="Dragon Card">
            </div>
          </div>
          <div class="col-6 paati_boxs">
            <h6 class="fw-bold small mb-2">Tiger</h6>
            <div class="card-Img-box">
              <img id="dtModalTigerCard" src="/assets/cards/H9_.png" style="width:52px;border-radius:4px;box-shadow:0 2px 8px rgba(0,0,0,0.4);" alt="Tiger Card">
            </div>
          </div>
        </div>
        <div class="fw-bold text-success mt-3" style="font-size:13px;" id="dtModalWinnerText">WINNER: DRAGON</div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  const DRAGONTIGER = (() => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const eventId = @json($game->event_id ?? '99.0019');

    // Starts in active betting state matching media_1794305244441_a35bf8f1.jpg (RID: 9191011100500, DRAGON & TIGER face-down 0.png, timer 19)
    let roundId = '9191011100500';
    let countdown = 19;
    const maxCountdown = 20;
    let phase = 'BETTING'; // 'BETTING' or 'DEALING'
    let currentSlip = null;
    let openRoundBets = [];
    let settlingInProgress = false;

    // Exact 10 badges from media_1794305244441_a35bf8f1.jpg: T, T, D, D, D, D, D, T, T, D
    const recentHistory = [
      { roundId: '9191011100499', badge: 'T', dragon: 'C7_',  tiger: 'HK_',  winner: 'TIGER (Dragon: 7♣ vs Tiger: K♥)' },
      { roundId: '9191011100498', badge: 'T', dragon: 'D5_',  tiger: 'SQ_',  winner: 'TIGER (Dragon: 5♦ vs Tiger: Q♠)' },
      { roundId: '9191011100497', badge: 'D', dragon: 'SK_',  tiger: 'H9_',  winner: 'DRAGON (Dragon: K♠ vs Tiger: 9♥)' },
      { roundId: '9191011100496', badge: 'D', dragon: 'HQ_',  tiger: 'C6_',  winner: 'DRAGON (Dragon: Q♥ vs Tiger: 6♣)' },
      { roundId: '9191011100495', badge: 'D', dragon: 'DJ_',  tiger: 'S4_',  winner: 'DRAGON (Dragon: J♦ vs Tiger: 4♠)' },
      { roundId: '9191011100494', badge: 'D', dragon: 'S10_', tiger: 'H3_',  winner: 'DRAGON (Dragon: 10♠ vs Tiger: 3♥)' },
      { roundId: '9191011100493', badge: 'D', dragon: 'CK_',  tiger: 'D8_',  winner: 'DRAGON (Dragon: K♣ vs Tiger: 8♦)' },
      { roundId: '9191011100492', badge: 'T', dragon: 'H2_',  tiger: 'SJ_',  winner: 'TIGER (Dragon: 2♥ vs Tiger: J♠)' },
      { roundId: '9191011100491', badge: 'T', dragon: 'DA_',  tiger: 'C10_', winner: 'TIGER (Dragon: A♦ vs Tiger: 10♣)' },
      { roundId: '9191011100490', badge: 'D', dragon: 'DK_',  tiger: 'S7_',  winner: 'DRAGON (Dragon: K♦ vs Tiger: 7♠)' },
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
      document.querySelectorAll('.js-dt-suspended').forEach(el => {
        el.classList.toggle('active', isSuspended);
      });
      const timerRing = document.getElementById('dtTimerRing');
      if (timerRing) {
        timerRing.classList.toggle('hidden-timer', isSuspended);
      }
    }

    function updateTimerUI() {
      const textEl = document.getElementById('dtTimerText');
      const ringEl = document.getElementById('dtTimerRing');
      if (textEl) textEl.textContent = String(countdown);
      if (ringEl) {
        const pct = Math.max(5, Math.round((countdown / maxCountdown) * 100));
        ringEl.style.setProperty('--dt-prog', pct + '%');
        ringEl.classList.toggle('danger', countdown <= 5);
      }
    }

    function renderRecentBalls() {
      const container = document.getElementById('dtRecentBalls');
      if (!container) return;
      container.innerHTML = recentHistory.slice(0, 10).map((item, idx) => {
        const cls = item.badge === 'D' ? 'casino-a' : (item.badge === 'T' ? 'casino-b' : 'casino-c');
        return `
          <li class="${cls}"
              onclick="DRAGONTIGER.showResultModal(${idx})"
              title="Round ${item.roundId}: ${item.winner}">
            <a class="number text-dark" style="text-decoration:none;">${item.badge}</a>
          </li>
        `;
      }).join('');
    }

    function showResultModal(idx) {
      const item = recentHistory[idx];
      if (!item) return;
      document.getElementById('dtModalRoundId').textContent = item.roundId;
      document.getElementById('dtModalDragonCard').src = `/assets/cards/${item.dragon}.png`;
      document.getElementById('dtModalTigerCard').src = `/assets/cards/${item.tiger}.png`;
      document.getElementById('dtModalWinnerText').textContent = `WINNER: ${item.winner}`;
      const modal = new bootstrap.Modal(document.getElementById('dragonTiger2020'));
      modal.show();
    }

    function startNewBettingRound() {
      roundId = '91910' + Math.floor(11100501 + Math.random() * 80000);
      const ridEl = document.getElementById('dtRoundIdText');
      if (ridEl) ridEl.textContent = roundId;

      setCard('dtDragonCard', '0');
      setCard('dtTigerCard', '0');
      document.querySelectorAll('.dt-odds-box').forEach(b => b.classList.remove('winner-pulse'));
      const banner = document.getElementById('dtWinnerBanner');
      if (banner) banner.style.display = 'none';

      phase = 'BETTING';
      countdown = 19;
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
          setCard('dtDragonCard', data.card_dragon || 'SK_');
          setTimeout(() => {
            setCard('dtTigerCard', data.card_tiger || 'H9_');
          }, 120);

          setTimeout(() => {
            (data.winning_keys || []).forEach(k => {
              document.getElementById('dtOddsBtn_' + k)?.classList.add('winner-pulse');
            });

            const banner = document.getElementById('dtWinnerBanner');
            const bannerText = document.getElementById('dtWinnerBannerText');
            if (banner && bannerText) {
              bannerText.textContent = `WINNER: ${data.summary}`;
              banner.style.display = 'block';
            }

            if (data.wallet) {
              updateHeaderWallet(data.wallet);
            }

            openRoundBets = [];
            document.querySelectorAll('.dt-book-pl').forEach(el => { el.innerHTML = ''; });
            renderOpenBetsList(data.settled_bets || []);

            recentHistory.unshift({
              roundId: data.round_id || roundId,
              badge: data.badge || 'D',
              dragon: data.card_dragon || 'SK_',
              tiger: data.card_tiger || 'H9_',
              winner: data.summary || data.winner || 'DRAGON'
            });
            renderRecentBalls();
          }, 300);
        }
      } catch (e) {
        console.error('Dragon Tiger settle error:', e);
      } finally {
        setTimeout(() => {
          settlingInProgress = false;
          startNewBettingRound();
        }, 3800);
      }
    }

    function openSlip(marketKey, marketTitle, selectionKey, selectionLabel, odds, minBet, maxBet) {
      if (phase !== 'BETTING' || countdown <= 0) {
        if (window.WB24) WB24.toast('Market is suspended! Wait for next round timer.', 'danger');
        return;
      }
      currentSlip = { marketKey, marketTitle, selectionKey, selectionLabel, odds, minBet, maxBet };
      document.getElementById('dtSlipSelectionName').textContent = selectionLabel;
      document.getElementById('dtSlipMarketTitle').textContent = `20-20 DRAGON TIGER • ${marketTitle}`;
      document.getElementById('dtSlipOdds').value = odds;
      const stakeInput = document.getElementById('dtSlipStake');
      if (!stakeInput.value || Number(stakeInput.value) < minBet) {
        stakeInput.value = minBet;
      }
      document.getElementById('dtSlipAlert').classList.add('d-none');
      const slipEl = document.getElementById('dtBetSlip');
      slipEl.classList.add('active');
      updateSlipCalc();
      if (window.innerWidth < 992) {
        slipEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    }

    function closeSlip() {
      document.getElementById('dtBetSlip').classList.remove('active');
      currentSlip = null;
    }

    function setStake(val) {
      document.getElementById('dtSlipStake').value = val;
      updateSlipCalc();
    }

    function updateSlipCalc() {
      if (!currentSlip) return;
      const stake = Math.max(0, parseFloat(document.getElementById('dtSlipStake').value) || 0);
      const profit = stake * (currentSlip.odds - 1);
      document.getElementById('dtSlipProfit').textContent = '+' + profit.toFixed(2);
      document.getElementById('dtSlipLiability').textContent = '-' + stake.toFixed(2);
    }

    function showSlipMessage(msg, isError = false) {
      const alertEl = document.getElementById('dtSlipAlert');
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
      const stake = parseFloat(document.getElementById('dtSlipStake').value) || 0;
      if (stake < 10) {
        showSlipMessage('Minimum stake is 10 PTI.', true);
        return;
      }

      const btnPlace = document.getElementById('dtBtnPlaceBet');
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
      document.querySelectorAll('.dt-book-pl').forEach(el => { el.innerHTML = ''; });
      const totalsBySelection = {};
      openRoundBets.forEach(b => {
        if (!totalsBySelection[b.selection_id]) {
          totalsBySelection[b.selection_id] = { profit: 0, stake: 0 };
        }
        totalsBySelection[b.selection_id].profit += Number(b.potential_profit || 0);
        totalsBySelection[b.selection_id].stake += Number(b.stake || 0);
      });

      Object.keys(totalsBySelection).forEach(selKey => {
        const bookEl = document.getElementById('dtBook_' + selKey);
        if (bookEl) {
          const info = totalsBySelection[selKey];
          bookEl.innerHTML = `<span class="text-success">+${info.profit.toFixed(0)}</span> / <span class="text-danger">-${info.stake.toFixed(0)}</span>`;
        }
      });
    }

    function renderOpenBetsList(settledBets = []) {
      const container = document.getElementById('dtOpenBetsContainer');
      const badge = document.getElementById('dtOpenBetsCount');
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
        html = `<div id="dtNoOpenBetsMsg" class="text-center text-muted py-3 small" style="font-size:11.5px;">Click on DRAGON, TIGER, or TIE odds box to place a bet.</div>`;
      }
      container.innerHTML = html;
    }

    function filterOpenBets(mktKey) {
      document.querySelectorAll('#dtOpenBetsContainer [data-mkt]').forEach(el => {
        el.style.display = (mktKey === 'all' || el.dataset.mkt.includes(mktKey)) ? '' : 'none';
      });
    }

    function saveQuickStakes() {
      const inputs = Array.from(document.querySelectorAll('.js-dt-stake-input'));
      const values = inputs.map(i => Math.max(10, parseInt(i.value, 10) || 100));
      const grid = document.getElementById('dtQuickStakeGrid');
      if (grid) {
        grid.innerHTML = values.map(v =>
          `<button type="button" class="dt-stake-chip" onclick="DRAGONTIGER.setStake(${v})">+${v}</button>`
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

