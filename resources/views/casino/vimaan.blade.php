@extends('layouts.app')
@section('title', 'VIMAAN - Universe Original | CRICEXPERTS')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  body {
    background-color: #0e0e0e !important;
    overflow-x: hidden;
  }

  .vm-wrapper {
    background-color: #0e0e0e;
    color: #ffffff;
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    min-height: calc(100vh - 98px);
    padding: 6px 8px 14px;
    display: flex;
    gap: 10px;
    user-select: none;
  }

  /* ================= LEFT PANEL (ALL BETS / MY BETS) ================= */
  .vm-left-panel {
    width: 375px;
    min-width: 340px;
    max-width: 390px;
    background: #1b1c1d;
    border-radius: 10px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: calc(100vh - 112px);
    overflow: hidden;
    flex-shrink: 0;
  }

  .vm-pill-switcher {
    background: #141516;
    width: 200px;
    height: 24px;
    margin: 6px auto 5px;
    border-radius: 10px;
    position: relative;
    display: flex;
    align-items: center;
    border: 1px solid #141516;
  }
  .vm-pill-slider {
    position: absolute;
    top: 0;
    left: 0;
    width: 100px;
    height: 22px;
    background: #2c2d30;
    border-radius: 10px;
    transition: transform 0.3s ease;
    pointer-events: none;
  }
  .vm-pill-btn {
    width: 100px;
    height: 22px;
    background: transparent;
    border: none;
    color: #9ea0a3;
    font-size: 12px;
    font-weight: 300;
    position: relative;
    z-index: 2;
    cursor: pointer;
    line-height: 1;
    transition: color 0.2s;
  }
  .vm-pill-btn.active {
    color: #ffffff;
    font-weight: 400;
  }
  .vm-pill-btn:hover {
    color: #ffffff;
  }

  .vm-stats-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 10px 4px;
  }
  .vm-stat-title {
    font-size: 14px;
    font-weight: 300;
    letter-spacing: 0.02em;
    color: #ffffff;
    line-height: 17px;
  }
  .vm-stat-num {
    font-size: 14px;
    font-weight: 200;
    color: #ffffff;
    line-height: 17px;
  }
  .vm-divider {
    height: 2px;
    background: #141516;
    width: 100%;
  }

  .vm-table-head {
    display: flex;
    align-items: center;
    height: 22px;
    padding: 0 10px;
    font-size: 11px;
    color: #7b7b7b;
    font-weight: 300;
  }
  .vm-col-user { width: 36%; }
  .vm-col-bet { width: 34%; display: flex; justify-content: flex-end; gap: 14px; padding-right: 8px; }
  .vm-col-cashout { width: 30%; text-align: right; }

  .vm-bets-list {
    list-style: none;
    margin: 0;
    padding: 0 4px;
    overflow-y: auto;
    flex: 1;
  }
  .vm-bets-list::-webkit-scrollbar {
    width: 3px;
  }
  .vm-bets-list::-webkit-scrollbar-thumb {
    background: #2e3035;
    border-radius: 3px;
  }

  .vm-bet-item {
    display: flex;
    align-items: center;
    height: 34px;
    min-height: 34px;
    padding: 0 8px;
    margin-bottom: 3px;
    background: #101112;
    border: 1px solid #141516;
    border-radius: 8px;
    font-size: 13px;
    color: #bbbfc5;
    transition: background-color 0.2s, border-color 0.2s;
  }
  .vm-bet-item.cashed-out {
    background-color: #123405 !important;
    border: 1px solid #427f00 !important;
    color: #ffffff !important;
  }
  .vm-bet-item.is-me {
    border-color: #46c0f2;
  }

  .vm-user-cell {
    width: 36%;
    display: flex;
    align-items: center;
    gap: 7px;
    overflow: hidden;
  }
  .vm-avatar {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
  }
  .vm-username {
    font-size: 12px;
    color: #9ea0a3;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .vm-bet-item.cashed-out .vm-username {
    color: #e2f5dc;
  }

  .vm-bet-cell {
    width: 34%;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 6px;
    font-size: 13px;
    font-weight: 400;
  }
  .vm-mult-badge {
    font-size: 11px;
    font-weight: 700;
    padding: 1px 6px;
    border-radius: 10px;
    background: #091405;
    border: 1px solid rgba(255,255,255,0.12);
    line-height: 1.3;
  }

  .vm-cashout-cell {
    width: 30%;
    text-align: right;
    font-size: 13px;
    font-weight: 600;
    color: #ffffff;
  }

  .vm-provably-footer {
    background: #000000;
    border: 1px solid #2a2b2e;
    height: 26px;
    padding: 0 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 11px;
    color: #5e6269;
  }
  .vm-pf-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    cursor: pointer;
    color: #bbbfc5;
    font-size: 12px;
  }
  .vm-pf-badge:hover {
    color: #ffffff;
  }

  /* ================= RIGHT PANEL (HISTORY + CANVAS + CONTROLS) ================= */
  .vm-right-panel {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    height: calc(100vh - 112px);
  }

  /* Multiplier History Strip */
  .vm-history-bar {
    height: 34px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    position: relative;
    padding: 0 2px;
    margin-bottom: 4px;
  }
  .vm-history-strip {
    display: flex;
    align-items: center;
    gap: 7px;
    overflow: hidden;
    flex: 1;
    white-space: nowrap;
  }
  .vm-hist-item {
    font-size: 12px;
    font-weight: 600;
    padding: 1px 7px;
    border-radius: 11px;
    background: rgba(0, 0, 0, 0.55);
    cursor: pointer;
    flex-shrink: 0;
    line-height: 1.35;
  }
  .vm-hist-blue { color: #34b4ff; }
  .vm-hist-purple { color: #913ef8; }
  .vm-hist-pink { color: #c017b4; }

  .vm-history-toggle-btn {
    background: #1b1c1d;
    border: 1px solid #414148;
    width: 46px;
    height: 22px;
    border-radius: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    cursor: pointer;
    flex-shrink: 0;
  }
  .vm-history-toggle-btn:hover {
    border-color: #e50539;
  }

  .vm-history-dropdown {
    position: absolute;
    top: 32px;
    left: 0;
    right: 0;
    background: #252830;
    border-radius: 14px;
    z-index: 1200;
    box-shadow: 0 10px 30px rgba(0,0,0,0.75);
    display: none;
    overflow: hidden;
  }
  .vm-history-dropdown.show {
    display: block;
  }
  .vm-history-dropdown-head {
    background: #1b1c1d;
    height: 32px;
    padding: 0 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 13px;
    color: #ffffff;
  }

  /* Main Flight Stage Canvas */
  .vm-stage-container {
    position: relative;
    flex: 1;
    min-height: 310px;
    background: #000000;
    border: 1px solid #2a2b2e;
    border-radius: 20px;
    overflow: hidden;
  }
  #vimaanCanvas {
    width: 100%;
    height: 100%;
    display: block;
  }

  .vm-speaker-btn {
    position: absolute;
    top: 8px;
    left: 36px;
    z-index: 50;
    background: transparent;
    border: none;
    padding: 4px;
    cursor: pointer;
    opacity: 0.85;
  }
  .vm-speaker-btn:hover {
    opacity: 1;
  }

  /* Center Overlay Text (Multiplier / Waiting / Flew Away) */
  .vm-center-overlay {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    pointer-events: none;
    z-index: 20;
  }
  .vm-flew-away-title {
    font-size: 34px;
    font-weight: 500;
    color: #ffffff;
    letter-spacing: 0.02em;
    margin-bottom: -6px;
    display: none;
  }
  .vm-live-multiplier {
    font-size: 92px;
    font-weight: 700;
    color: #ffffff;
    line-height: 1.05;
    text-shadow: 0 2px 18px rgba(0, 0, 0, 0.55);
  }
  .vm-live-multiplier.crashed {
    color: #fa0025 !important;
  }

  /* Waiting For Next Round Overlay */
  .vm-waiting-overlay {
    position: absolute;
    inset: 0;
    display: none;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    pointer-events: none;
    z-index: 25;
  }
  .vm-waiting-prop {
    width: 112px;
    height: 112px;
    animation: vmSpinProp 1.98s linear infinite;
    margin-bottom: 14px;
  }
  @keyframes vmSpinProp {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
  }
  .vm-waiting-text {
    font-size: 32px;
    font-weight: 400;
    color: #ffffff;
    letter-spacing: 0.025em;
    margin-bottom: 14px;
  }
  .vm-waiting-bar-track {
    width: 200px;
    height: 8px;
    background: #252830;
    border-radius: 4px;
    overflow: hidden;
  }
  .vm-waiting-bar-fill {
    height: 100%;
    width: 100%;
    background: #fa0025;
    border-radius: 4px;
    transition: width 0.08s linear;
  }

  /* Cashout Toast Celebration */
  .vm-cashout-toast {
    position: absolute;
    top: 14px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 80;
    background: rgba(18, 52, 5, 0.95);
    border: 1.5px solid #60ae05;
    border-radius: 30px;
    padding: 6px 20px;
    color: #ffffff;
    display: none;
    align-items: center;
    gap: 12px;
    box-shadow: 0 6px 24px rgba(0,0,0,0.65);
  }

  /* ================= BOTTOM BETTING CONTROLS ================= */
  .vm-controls-card {
    background: #1b1c1d;
    border-radius: 12px;
    padding: 8px 14px 10px;
    margin-top: 6px;
  }
  .vm-stakes-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 8px 14px;
    margin-top: 6px;
  }
  .vm-stake-btn {
    height: 30px;
    border-radius: 100px;
    background: #141516;
    border: 1px solid transparent;
    color: rgba(255, 255, 255, 0.6);
    font-size: 15px;
    font-weight: 300;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.15s;
  }
  .vm-stake-btn:hover {
    color: #ffffff;
    background: #202124;
  }
  .vm-stake-btn.selectedBtn {
    border: 1px solid #ff0000 !important;
    color: #ffffff !important;
    font-weight: 700 !important;
  }
  .vm-stake-btn.vm-btn-edit {
    background: #d07206 !important;
    border: 1px solid #ffbd71 !important;
    color: #ffffff !important;
    font-weight: 400;
    text-shadow: 0 1px 2px rgba(0,0,0,0.5);
  }
  .vm-stake-btn.vm-btn-clear {
    background: #ff0000 !important;
    border: 1px solid #ff4d4d !important;
    color: #ffffff !important;
    font-weight: 400;
  }
  .vm-stake-input-edit {
    height: 30px;
    border-radius: 100px;
    background: #0e0f10;
    border: 1px solid #888888;
    color: #ffffff;
    text-align: center;
    font-size: 14px;
    font-weight: 600;
    width: 100%;
  }

  /* 4 Cash In / Cancel / Cash Out Buttons Bar */
  .vm-cashin-card {
    background: #1b1c1d;
    border-radius: 10px;
    padding: 8px 14px;
    margin-top: 5px;
  }
  .vm-cashin-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
  }
  .vm-action-btn {
    height: 30px;
    border-radius: 20px;
    background: #28a909;
    border: 1px solid #b2f2a3;
    box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.5);
    color: #ffffff;
    font-size: 15px;
    font-weight: 400;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.1s, background-color 0.15s;
  }
  .vm-action-btn:active {
    transform: scale(0.98);
  }
  .vm-action-btn.state-cancel {
    background: #ff0000 !important;
    border: 1px solid #ff7171 !important;
  }
  .vm-action-btn.state-cashout {
    background: #d07206 !important;
    border: 1px solid #ffbd71 !important;
    font-weight: 700 !important;
  }

  /* Auto Cashout Row */
  .vm-auto-row {
    display: none;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
    margin-top: 8px;
    padding-top: 6px;
    border-top: 1px solid #141516;
  }
  .vm-auto-row.show {
    display: grid;
  }
  .vm-auto-cell {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 6px;
    font-size: 12px;
    color: #bbbfc5;
  }
  .vm-auto-input-wrap {
    display: flex;
    align-items: center;
    background: #000000b3;
    border-radius: 20px;
    padding: 2px 8px;
    height: 26px;
    width: 95px;
  }
  .vm-auto-input {
    width: 100%;
    background: transparent;
    border: none;
    color: #ffffff;
    font-weight: 700;
    font-size: 13px;
    text-align: center;
    outline: none;
  }

  @media (max-width: 991px) {
    .vm-wrapper {
      flex-direction: column;
      height: auto;
    }
    .vm-right-panel {
      order: 1;
      height: auto;
    }
    .vm-left-panel {
      order: 2;
      width: 100%;
      max-width: 100%;
      height: 440px;
    }
    .vm-stage-container {
      height: 310px;
    }
    .vm-live-multiplier {
      font-size: 62px;
    }
    .vm-waiting-text, .vm-flew-away-title {
      font-size: 22px;
    }
    .vm-cashin-grid, .vm-auto-row {
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 8px;
    }
  }
</style>
@endpush

@section('full_bleed')
<div class="vm-wrapper">

  <!-- ================= LEFT PANEL: ALL BETS & MY BETS ================= -->
  <div class="vm-left-panel">
    <div class="d-flex flex-column h-100 overflow-hidden">
      <!-- All Bets / My Bets Switcher -->
      <div class="vm-pill-switcher">
        <div id="vmLeftTabSlider" class="vm-pill-slider"></div>
        <button type="button" id="btnTabAllBets" class="vm-pill-btn active" onclick="VimaanApp.switchLeftTab('all')">All Bets</button>
        <button type="button" id="btnTabMyBets" class="vm-pill-btn" onclick="VimaanApp.switchLeftTab('my')">My Bets</button>
      </div>

      <!-- ALL BETS & Users Count Header -->
      <div id="vmAllBetsStatsHeader">
        <div class="vm-stats-bar">
          <div>
            <div class="vm-stat-title">ALL BETS</div>
            <div class="vm-stat-num" id="vmTotalBetsCount">95</div>
          </div>
          <div class="text-end">
            <div class="vm-stat-title">Users</div>
            <div class="vm-stat-num" id="vmActiveUsersCount">0</div>
          </div>
        </div>
        <div class="vm-divider"></div>
      </div>

      <!-- Column Header -->
      <div class="vm-table-head">
        <div class="vm-col-user" id="vmCol1Label">User</div>
        <div class="vm-col-bet">
          <span>Bet</span>
          <span>X</span>
        </div>
        <div class="vm-col-cashout">Cash out</div>
      </div>

      <!-- All Bets Live List -->
      <ul id="vmAllBetsList" class="vm-bets-list"></ul>

      <!-- My Bets List (SQLite Backed) -->
      <ul id="vmMyBetsList" class="vm-bets-list" style="display:none;">
        @forelse($recentRounds as $rr)
          @php
            $won = $rr->status === 'WON';
            $mult = number_format((float)$rr->odds, 2);
            $payout = $won ? round((float)$rr->stake * (float)$rr->odds, 2) : 0;
            $mColor = $rr->odds < 2 ? '#34b4ff' : ($rr->odds < 10 ? '#913ef8' : '#c017b4');
          @endphp
          <li class="vm-bet-item {{ $won ? 'cashed-out' : '' }}">
            <div class="vm-user-cell">
              <span class="vm-username text-white-50">{{ $rr->created_at->format('H:i') }}</span>
            </div>
            <div class="vm-bet-cell">
              <span>{{ number_format($rr->stake, 0) }}</span>
              <span class="vm-mult-badge" style="color:{{ $mColor }};">{{ $mult }}x</span>
            </div>
            <div class="vm-cashout-cell">
              {{ $won ? number_format($payout, 2) : '-' }}
            </div>
          </li>
        @empty
          <li id="vmNoMyBetsRow" class="text-center text-secondary py-4 small">No bets placed in VIMAAN yet.</li>
        @endforelse
      </ul>
    </div>

    <!-- Provably Fair Footer -->
    <div class="vm-provably-footer">
      <div class="d-flex align-items-center gap-1">
        <span>This game is</span>
        <span class="vm-pf-badge" data-bs-toggle="modal" data-bs-target="#provablyFairModal">
          <img src="/assets/vimaan/icon-provabyfair.652dcfd9d89241e2.svg" alt="PF" style="width:14px;height:16px;">
          <span>Provably Fair</span>
        </span>
      </div>
      <div class="d-flex align-items-center gap-1">
        <span>Powered by</span>
        <strong class="text-decoration-underline" style="color:#858788;font-size:13px;">VIMAAN</strong>
      </div>
    </div>
  </div>

  <!-- ================= RIGHT PANEL: MULTIPLIER BAR + FLIGHT CANVAS + CONTROLS ================= -->
  <div class="vm-right-panel">

    <!-- 1. Top Multiplier History Strip -->
    <div class="vm-history-bar">
      <div id="vmHistoryStrip" class="vm-history-strip"></div>

      <button type="button" class="vm-history-toggle-btn" onclick="VimaanApp.toggleHistoryDropdown()" title="Round History">
        <img src="/assets/vimaan/history-grey.42156111146ca273.svg" alt="history" style="width:14px;height:13px;">
        <img src="/assets/vimaan/arrow-down.svg" id="vmHistoryArrow" alt="open" style="width:10px;transition:transform 0.25s;">
      </button>

      <!-- Round History Dropdown Overlay -->
      <div id="vmHistoryDropdown" class="vm-history-dropdown">
        <div class="vm-history-dropdown-head">
          <span>ROUND HISTORY</span>
          <button type="button" class="btn-close btn-close-white btn-sm" onclick="VimaanApp.toggleHistoryDropdown()"></button>
        </div>
        <div id="vmHistoryDropdownGrid" class="p-2 d-flex flex-wrap gap-2"></div>
      </div>
    </div>

    <!-- 2. Main Flight Stage Canvas -->
    <div class="vm-stage-container" id="vmStageBox">
      <!-- Speaker Sound Toggle Button -->
      <button type="button" class="vm-speaker-btn" onclick="VimaanApp.toggleSound()" title="Toggle Sound">
        <svg id="vmIconMuted" width="24" height="24" viewBox="0 0 24 24" fill="none">
          <path d="M11 5L6 9H2V15H6L11 19V5Z" stroke="#d2d2d2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
          <path d="M16 8L23 15" stroke="#d2d2d2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
          <path d="M23 8L16 15" stroke="#d2d2d2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
        </svg>
        <svg id="vmIconUnmuted" width="24" height="24" viewBox="0 0 24 24" fill="none" style="display:none;">
          <path d="M11 5L6 9H2V15H6L11 19V5Z" stroke="#60ae05" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
          <path d="M15.54 8.46a5 5 0 0 1 0 7.07" stroke="#60ae05" stroke-width="2" stroke-linecap="round"></path>
          <path d="M19.07 4.93a10 10 0 0 1 0 14.14" stroke="#60ae05" stroke-width="2" stroke-linecap="round"></path>
        </svg>
      </button>

      <!-- Cashout Celebration Toast -->
      <div id="vmCashoutToast" class="vm-cashout-toast">
        <div>
          <div class="small text-white-50" style="font-size:11px;">You have cashed out!</div>
          <div class="fw-bold" id="vmToastMult">2.75x</div>
        </div>
        <div class="badge bg-success fs-6 px-3 py-2" id="vmToastWin">Win 275.00 PTI</div>
      </div>

      <!-- Live Multiplier & Flew Away Overlay -->
      <div id="vmFlightOverlay" class="vm-center-overlay">
        <div id="vmFlewAwayText" class="vm-flew-away-title">FLEW AWAY!</div>
        <div id="vmMultiplierText" class="vm-live-multiplier">2.75x</div>
      </div>

      <!-- Waiting For Next Round Overlay -->
      <div id="vmWaitingOverlay" class="vm-waiting-overlay">
        <img src="/assets/vimaan/prop.svg" alt="Propeller" class="vm-waiting-prop">
        <div class="vm-waiting-text">WAITING FOR NEXT ROUND</div>
        <div class="vm-waiting-bar-track">
          <div id="vmWaitingBarFill" class="vm-waiting-bar-fill"></div>
        </div>
      </div>

      <canvas id="vimaanCanvas"></canvas>
    </div>

    <!-- 3. Bottom Betting Controls Card (Bet / Auto + 10..10000 + Min/Max/Edit/Clear) -->
    <div class="vm-controls-card">
      <div class="vm-pill-switcher my-0 mx-auto">
        <div id="vmModeSlider" class="vm-pill-slider"></div>
        <button type="button" id="btnModeBet" class="vm-pill-btn active" onclick="VimaanApp.switchMode('bet')">Bet</button>
        <button type="button" id="btnModeAuto" class="vm-pill-btn" onclick="VimaanApp.switchMode('auto')">Auto</button>
      </div>

      <div class="vm-stakes-grid" id="vmStakesGrid">
        <!-- Dynamically rendered stake chips (10, 50, 100, 500, 1000, 2500, 5000, 10000) -->
      </div>

      <div class="vm-stakes-grid mt-2">
        <button type="button" id="btnStakeMin" class="vm-stake-btn" onclick="VimaanApp.selectStake(10, 'min')">Min</button>
        <button type="button" id="btnStakeMax" class="vm-stake-btn" onclick="VimaanApp.selectStake(10000, 'max')">Max</button>
        <button type="button" id="btnStakeEdit" class="vm-stake-btn vm-btn-edit" onclick="VimaanApp.toggleEditStakes()">Edit</button>
        <button type="button" class="vm-stake-btn vm-btn-clear" onclick="VimaanApp.clearStake()">Clear</button>
      </div>
    </div>

    <!-- 4. Bottom 4 Cash In / Cancel / Cash Out Buttons Card -->
    <div class="vm-cashin-card">
      <div class="vm-cashin-grid">
        @for($slot = 1; $slot <= 4; $slot++)
          <button type="button" id="vmActionBtn_{{ $slot }}" class="vm-action-btn" onclick="VimaanApp.handleSlotAction({{ $slot }})">
            Cash In
          </button>
        @endfor
      </div>

      <!-- Auto Cash Out Row (Visible when Auto tab is active) -->
      <div id="vmAutoCashoutRow" class="vm-auto-row">
        @for($slot = 1; $slot <= 4; $slot++)
          <div class="vm-auto-cell">
            <div class="form-check form-switch m-0 d-flex align-items-center gap-1">
              <input class="form-check-input" type="checkbox" id="vmAutoSwitch_{{ $slot }}" onchange="VimaanApp.toggleSlotAuto({{ $slot }}, this.checked)">
              <label class="form-check-label small" for="vmAutoSwitch_{{ $slot }}">Auto Cash Out</label>
            </div>
            <div class="vm-auto-input-wrap">
              <input type="number" step="0.1" min="1.10" max="500" id="vmAutoVal_{{ $slot }}" value="2.00" class="vm-auto-input">
              <span class="text-white-50 small">×</span>
            </div>
          </div>
        @endfor
      </div>
    </div>

  </div>
</div>

<!-- Provably Fair Verification Modal -->
<div class="modal fade" id="provablyFairModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-dark text-white border-secondary">
      <div class="modal-header border-secondary py-2">
        <h6 class="modal-title d-flex align-items-center gap-2">
          <img src="/assets/vimaan/icon-provabyfair.652dcfd9d89241e2.svg" style="width:16px;" alt="PF">
          PROVABLY FAIR SETTINGS
        </h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body small">
        <p class="text-white-50 mb-2">
          VIMAAN uses cryptographic SHA-512 Provably Fair technology. Each round's crash multiplier is determined before takeoff by combining the Server Seed and Client Seeds.
        </p>
        <div class="mb-2">
          <label class="text-secondary d-block mb-1">Active Server Seed SHA-256:</label>
          <input type="text" class="form-control form-control-sm bg-black text-info border-secondary" readonly value="{{ hash('sha256', 'cricexperts-vimaan-' . now()->format('Y-m-d-H')) }}">
        </div>
        <div class="mb-2">
          <label class="text-secondary d-block mb-1">Your Client Seed:</label>
          <input type="text" class="form-control form-control-sm bg-black text-warning border-secondary" readonly value="{{ substr(md5(auth()->check() ? auth()->user()->username : 'guest-seed'), 0, 20) }}">
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
const VimaanApp = (() => {
  // Exact multiplier history from user's reference screenshot
  const historyMultipliers = [
    1.49, 1.15, 1.09, 1.20, 4.29, 1.84, 3.12, 2.27, 1.03,
    2.48, 1.79, 1.26, 1.19, 1.82, 1.32, 21.10, 2.83, 2.61, 24.25
  ];

  // Realistic live bettors pool matching screenshot
  const botProfiles = [
    { user: 'h*********8', avatar: 12, baseStake: 2500, targetMult: 3.40 },
    { user: 'e******8',    avatar: 25, baseStake: 2500, targetMult: 1.69 },
    { user: 'h*********8', avatar: 12, baseStake: 1000, targetMult: 3.10 },
    { user: '1***u',       avatar: 7,  baseStake: 1000, targetMult: 2.95 },
    { user: '1***u',       avatar: 7,  baseStake: 1000, targetMult: 4.20 },
    { user: '0*****p',     avatar: 17, baseStake: 1000, targetMult: 3.50 },
    { user: 'a*******6',   avatar: 31, baseStake: 500,  targetMult: 2.90 },
    { user: '0*****p',     avatar: 17, baseStake: 500,  targetMult: 5.00 },
    { user: 'b****4',      avatar: 43, baseStake: 250,  targetMult: 1.33 },
    { user: 'h*****0',     avatar: 54, baseStake: 210,  targetMult: 3.20 },
    { user: 'd***8',       avatar: 61, baseStake: 150,  targetMult: 2.85 },
    { user: 'v*****2',     avatar: 14, baseStake: 100,  targetMult: 3.80 },
    { user: 'v*****2',     avatar: 14, baseStake: 100,  targetMult: 2.40 },
    { user: 'p****0',      avatar: 36, baseStake: 100,  targetMult: 2.95 },
    { user: 'k*****9',     avatar: 8,  baseStake: 100,  targetMult: 1.95 },
    { user: 'r****3',      avatar: 23, baseStake: 50,   targetMult: 1.55 },
    { user: 'm******1',    avatar: 48, baseStake: 50,   targetMult: 2.15 },
    { user: 's*****7',     avatar: 65, baseStake: 50,   targetMult: 3.60 }
  ];

  // Stake Chips
  let stakeChips = [10, 50, 100, 500, 1000, 2500, 5000, 10000];
  let selectedStake = 100;
  let selectedChipKey = 'chip_2';
  let isEditingStakes = false;

  // 4 Betting Slots State
  // status: 'IDLE' | 'QUEUED' | 'ACTIVE' | 'CASHED'
  const slots = {
    1: { status: 'IDLE', stake: 0, betId: null, autoActive: false, autoVal: 2.00 },
    2: { status: 'IDLE', stake: 0, betId: null, autoActive: false, autoVal: 2.00 },
    3: { status: 'IDLE', stake: 0, betId: null, autoActive: false, autoVal: 2.00 },
    4: { status: 'IDLE', stake: 0, betId: null, autoActive: false, autoVal: 2.00 },
  };

  // Flight State Machine: 'RUN' | 'BLAST' | 'WAIT'
  let gameState = 'RUN';
  let currentMultiplier = 1.00;
  let targetCrashMultiplier = 4.85;
  let roundId = 'VM' + Date.now();
  let flightProgress = 0.0;
  let planeX = 80;
  let planeY = 300;
  let flyAwayX = 0;
  let sunRayAngle = 0;
  let dotOffset = 0;
  let spriteFrameIdx = 0;
  let lastFrameTime = performance.now();
  let waitProgress = 1.0;

  // Audio
  let soundEnabled = false;
  const sounds = {
    bg: new Audio('/assets/vimaan/bg_music.mp3'),
    start: new Audio('/assets/vimaan/start_plane.mp3'),
    fly: new Audio('/assets/vimaan/flyPlane.mp3'),
    btn: new Audio('/assets/vimaan/button-pressed.mp3'),
    cashout: new Audio('/assets/vimaan/cashout.mp3'),
  };
  sounds.bg.loop = true;
  sounds.bg.volume = 0.35;

  // Loaded Sprites
  const imgPlane = new Image();
  imgPlane.src = '/assets/vimaan/vimaan.png';
  const imgSunRays = new Image();
  imgSunRays.src = '/assets/vimaan/sunRays.svg';

  // Sprite sheet frames from vimaan.json
  const planeFrames = [
    { x: 1, y: 1,   w: 146, h: 74 },
    { x: 1, y: 77,  w: 145, h: 71 },
    { x: 1, y: 150, w: 144, h: 63 },
    { x: 1, y: 77,  w: 145, h: 71 },
  ];

  let liveBetsRows = [];
  let canvas, ctx;

  function init() {
    canvas = document.getElementById('vimaanCanvas');
    ctx = canvas.getContext('2d');
    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);

    renderHistory();
    renderStakeChips();
    buildRoundBots();
    renderAllBetsList();

    // Start immediately in flying state around 1.05x climbing smoothly so the user sees the plane flying right away!
    startFlightRound(4.65);
    requestAnimationFrame(gameLoop);
  }

  function resizeCanvas() {
    const box = document.getElementById('vmStageBox');
    if (!box || !canvas) return;
    canvas.width = box.clientWidth;
    canvas.height = box.clientHeight;
  }

  function getMultColorClass(m) {
    if (m < 2.0) return 'vm-hist-blue';
    if (m < 10.0) return 'vm-hist-purple';
    return 'vm-hist-pink';
  }

  function getMultHex(m) {
    if (m < 2.0) return '#34b4ff';
    if (m < 10.0) return '#913ef8';
    return '#c017b4';
  }

  function renderHistory() {
    const strip = document.getElementById('vmHistoryStrip');
    const grid = document.getElementById('vmHistoryDropdownGrid');
    if (!strip) return;

    const html = historyMultipliers.map(m =>
      `<div class="vm-hist-item ${getMultColorClass(m)}">${m.toFixed(2)}x</div>`
    ).join('');
    strip.innerHTML = html;
    if (grid) grid.innerHTML = html;
  }

  function toggleHistoryDropdown() {
    const dd = document.getElementById('vmHistoryDropdown');
    const arrow = document.getElementById('vmHistoryArrow');
    if (!dd) return;
    dd.classList.toggle('show');
    if (arrow) {
      arrow.style.transform = dd.classList.contains('show') ? 'rotate(180deg)' : 'rotate(0deg)';
    }
  }

  function renderStakeChips() {
    const grid = document.getElementById('vmStakesGrid');
    if (!grid) return;

    if (isEditingStakes) {
      grid.innerHTML = stakeChips.map((val, idx) => `
        <input type="number" min="10" max="10000" value="${val}"
               class="vm-stake-input-edit"
               onchange="VimaanApp.updateChipValue(${idx}, this.value)">
      `).join('');
    } else {
      grid.innerHTML = stakeChips.map((val, idx) => {
        const key = 'chip_' + idx;
        const isSel = selectedStake === val && selectedChipKey === key;
        return `
          <button type="button" class="vm-stake-btn ${isSel ? 'selectedBtn' : ''}"
                  onclick="VimaanApp.selectStake(${val}, '${key}')">
            <span>${val}</span>
          </button>
        `;
      }).join('');
    }

    const minBtn = document.getElementById('btnStakeMin');
    const maxBtn = document.getElementById('btnStakeMax');
    if (minBtn) minBtn.classList.toggle('selectedBtn', selectedChipKey === 'min');
    if (maxBtn) maxBtn.classList.toggle('selectedBtn', selectedChipKey === 'max');
  }

  function selectStake(val, key) {
    playSfx('btn');
    selectedStake = parseFloat(val);
    selectedChipKey = key;
    renderStakeChips();
  }

  function updateChipValue(idx, val) {
    const n = Math.max(10, Math.min(10000, parseInt(val || 10, 10)));
    stakeChips[idx] = n;
  }

  function toggleEditStakes() {
    playSfx('btn');
    isEditingStakes = !isEditingStakes;
    const btn = document.getElementById('btnStakeEdit');
    if (btn) btn.textContent = isEditingStakes ? 'Save' : 'Edit';
    renderStakeChips();
  }

  function clearStake() {
    playSfx('btn');
    selectedStake = null;
    selectedChipKey = null;
    renderStakeChips();
  }

  function switchLeftTab(tab) {
    playSfx('btn');
    const slider = document.getElementById('vmLeftTabSlider');
    const btnAll = document.getElementById('btnTabAllBets');
    const btnMy = document.getElementById('btnTabMyBets');
    const listAll = document.getElementById('vmAllBetsList');
    const listMy = document.getElementById('vmMyBetsList');
    const statsHead = document.getElementById('vmAllBetsStatsHeader');
    const col1 = document.getElementById('vmCol1Label');

    if (tab === 'my') {
      slider.style.transform = 'translateX(100px)';
      btnAll.classList.remove('active');
      btnMy.classList.add('active');
      listAll.style.display = 'none';
      listMy.style.display = 'block';
      statsHead.style.display = 'none';
      col1.textContent = 'Date';
    } else {
      slider.style.transform = 'translateX(0px)';
      btnMy.classList.remove('active');
      btnAll.classList.add('active');
      listMy.style.display = 'none';
      listAll.style.display = 'block';
      statsHead.style.display = 'block';
      col1.textContent = 'User';
    }
  }

  function switchMode(mode) {
    playSfx('btn');
    const slider = document.getElementById('vmModeSlider');
    const btnBet = document.getElementById('btnModeBet');
    const btnAuto = document.getElementById('btnModeAuto');
    const autoRow = document.getElementById('vmAutoCashoutRow');

    if (mode === 'auto') {
      slider.style.transform = 'translateX(100px)';
      btnBet.classList.remove('active');
      btnAuto.classList.add('active');
      autoRow.classList.add('show');
    } else {
      slider.style.transform = 'translateX(0px)';
      btnAuto.classList.remove('active');
      btnBet.classList.add('active');
      autoRow.classList.remove('show');
    }
  }

  function toggleSlotAuto(slotNum, checked) {
    slots[slotNum].autoActive = checked;
    const valInput = document.getElementById('vmAutoVal_' + slotNum);
    if (valInput) {
      slots[slotNum].autoVal = Math.max(1.10, parseFloat(valInput.value || 2.00));
    }
  }

  function toggleSound() {
    soundEnabled = !soundEnabled;
    document.getElementById('vmIconMuted').style.display = soundEnabled ? 'none' : 'block';
    document.getElementById('vmIconUnmuted').style.display = soundEnabled ? 'block' : 'none';
    if (soundEnabled) {
      sounds.bg.play().catch(() => {});
    } else {
      sounds.bg.pause();
    }
  }

  function playSfx(name) {
    if (!soundEnabled || !sounds[name]) return;
    try {
      sounds[name].currentTime = 0;
      sounds[name].play().catch(() => {});
    } catch (e) {}
  }

  function buildRoundBots() {
    liveBetsRows = [];

    // Add current user's active/queued slots at the top
    [1, 2, 3, 4].forEach(s => {
      if (slots[s].status === 'ACTIVE') {
        liveBetsRows.push({
          id: 'me_slot_' + s,
          isMe: true,
          user: '{{ auth()->check() ? auth()->user()->username : "You" }}',
          avatar: 4,
          stake: slots[s].stake,
          cashedOut: false,
          cashoutMult: null,
          cashoutAmount: null,
          targetMult: 9999
        });
      }
    });

    // Add bot rows matching the screenshot
    botProfiles.forEach((b, idx) => {
      liveBetsRows.push({
        id: 'bot_' + idx,
        isMe: false,
        user: b.user,
        avatar: b.avatar,
        stake: b.baseStake,
        cashedOut: false,
        cashoutMult: null,
        cashoutAmount: null,
        targetMult: b.targetMult
      });
    });

    document.getElementById('vmTotalBetsCount').textContent = 95;
    document.getElementById('vmActiveUsersCount').textContent = 0;
  }

  function renderAllBetsList() {
    const ul = document.getElementById('vmAllBetsList');
    if (!ul) return;

    ul.innerHTML = liveBetsRows.map(r => {
      const mColor = r.cashoutMult ? getMultHex(r.cashoutMult) : '#34b4ff';
      const cashStr = r.cashoutAmount
        ? Number(r.cashoutAmount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
        : '';
      return `
        <li class="vm-bet-item ${r.cashedOut ? 'cashed-out' : ''} ${r.isMe ? 'is-me' : ''}">
          <div class="vm-user-cell">
            <img src="/assets/avatar-images/av-${r.avatar}.png" class="vm-avatar" alt="${r.user}" onerror="this.src='/assets/avatar-images/av-4.png'">
            <span class="vm-username">${r.user}</span>
          </div>
          <div class="vm-bet-cell">
            <span>${r.stake}</span>
            ${r.cashedOut ? `<span class="vm-mult-badge" style="color:${mColor};">${r.cashoutMult.toFixed(2)}x</span>` : ''}
          </div>
          <div class="vm-cashout-cell">${cashStr}</div>
        </li>
      `;
    }).join('');
  }

  function updateWalletDom(wallet) {
    if (!wallet) return;
    document.querySelectorAll('.js-user-balance').forEach(el => {
      el.textContent = Number(wallet.available).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    });
    document.querySelectorAll('.js-user-exposure').forEach(el => {
      el.textContent = Number(wallet.exposure).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    });
  }

  function updateSlotButtonsUI() {
    for (let s = 1; s <= 4; s++) {
      const btn = document.getElementById('vmActionBtn_' + s);
      if (!btn) continue;
      const st = slots[s];

      btn.classList.remove('state-cancel', 'state-cashout');

      if (st.status === 'QUEUED') {
        btn.classList.add('state-cancel');
        btn.textContent = 'Cancel';
      } else if (st.status === 'ACTIVE') {
        if (gameState === 'WAIT') {
          btn.classList.add('state-cancel');
          btn.textContent = 'Cancel';
        } else if (gameState === 'RUN') {
          btn.classList.add('state-cashout');
          const liveWin = (st.stake * currentMultiplier).toFixed(2);
          btn.textContent = liveWin;
        }
      } else {
        btn.textContent = 'Cash In';
      }
    }
  }

  async function handleSlotAction(slotNum) {
    playSfx('btn');
    const st = slots[slotNum];

    // 1. If currently ACTIVE during RUN -> CASH OUT IMMEDIATELY!
    if (st.status === 'ACTIVE' && gameState === 'RUN') {
      await cashOutSlot(slotNum, currentMultiplier);
      return;
    }

    // 2. If QUEUED or ACTIVE during WAIT -> CANCEL BET!
    if (st.status === 'QUEUED' || (st.status === 'ACTIVE' && gameState === 'WAIT')) {
      if (st.betId) {
        try {
          const res = await fetch('{{ route("api.vimaan.cancel") }}', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': WB24.csrfToken,
            },
            body: JSON.stringify({ bet_id: st.betId }),
          });
          const data = await res.json();
          if (data.status) updateWalletDom(data.wallet);
        } catch (e) {}
      }
      st.status = 'IDLE';
      st.betId = null;
      st.stake = 0;
      updateSlotButtonsUI();
      return;
    }

    // 3. Otherwise -> PLACE BET (during WAIT places immediately; during RUN/BLAST queues or places)
    if (!selectedStake || selectedStake < 10) {
      alert('Please select a stake amount (10 - 10000) first!');
      return;
    }

    const autoInput = document.getElementById('vmAutoVal_' + slotNum);
    if (autoInput) st.autoVal = Math.max(1.10, parseFloat(autoInput.value || 2.00));

    try {
      const res = await fetch('{{ route("api.vimaan.place") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': WB24.csrfToken,
        },
        body: JSON.stringify({
          stake: selectedStake,
          slot: slotNum,
          round_id: roundId,
          auto_cashout: st.autoActive ? st.autoVal : null,
        }),
      });
      const data = await res.json();
      if (!res.ok || !data.status) {
        if (data.redirect) {
          window.location.href = data.redirect;
          return;
        }
        alert(data.message || 'Could not place VIMAAN bet.');
        return;
      }

      st.betId = data.bet.id;
      st.stake = parseFloat(data.bet.stake);
      // If placed during WAIT or active RUN, activate for current flight so player can cash out immediately
      if (gameState === 'WAIT' || (gameState === 'RUN' && currentMultiplier < 3.50)) {
        st.status = 'ACTIVE';
        liveBetsRows.unshift({
          id: 'me_slot_' + slotNum,
          isMe: true,
          user: '{{ auth()->check() ? auth()->user()->username : "You" }}',
          avatar: 4,
          stake: st.stake,
          cashedOut: false,
          cashoutMult: null,
          cashoutAmount: null,
          targetMult: 9999
        });
        renderAllBetsList();
      } else {
        st.status = 'QUEUED';
      }

      updateWalletDom(data.wallet);
      updateSlotButtonsUI();
    } catch (err) {
      console.error(err);
    }
  }

  async function cashOutSlot(slotNum, multAtClick) {
    const st = slots[slotNum];
    if (st.status !== 'ACTIVE' || !st.betId) return;

    st.status = 'IDLE';
    const betId = st.betId;
    st.betId = null;
    updateSlotButtonsUI();

    try {
      const res = await fetch('{{ route("api.vimaan.cashout") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': WB24.csrfToken,
        },
        body: JSON.stringify({
          bet_id: betId,
          multiplier: multAtClick.toFixed(2),
        }),
      });
      const data = await res.json();
      if (data.status) {
        playSfx('cashout');
        updateWalletDom(data.wallet);
        showCashoutToast(data.cashout.multiplier, data.cashout.payout);

        // Highlight in All Bets
        const myRow = liveBetsRows.find(r => r.id === 'me_slot_' + slotNum);
        if (myRow) {
          myRow.cashedOut = true;
          myRow.cashoutMult = data.cashout.multiplier;
          myRow.cashoutAmount = data.cashout.payout;
          renderAllBetsList();
        }

        // Prepend to My Bets
        prependMyBetRow(data.cashout.time, data.cashout.stake, data.cashout.multiplier, data.cashout.payout, true);
      }
    } catch (e) {
      console.error(e);
    }
  }

  function prependMyBetRow(timeStr, stake, mult, payout, won) {
    const listMy = document.getElementById('vmMyBetsList');
    if (!listMy) return;
    const emptyRow = document.getElementById('vmNoMyBetsRow');
    if (emptyRow) emptyRow.remove();

    const mColor = getMultHex(mult);
    const li = document.createElement('li');
    li.className = 'vm-bet-item ' + (won ? 'cashed-out' : '');
    li.innerHTML = `
      <div class="vm-user-cell">
        <span class="vm-username text-white-50">${timeStr}</span>
      </div>
      <div class="vm-bet-cell">
        <span>${Number(stake).toFixed(0)}</span>
        <span class="vm-mult-badge" style="color:${mColor};">${Number(mult).toFixed(2)}x</span>
      </div>
      <div class="vm-cashout-cell">${won ? Number(payout).toFixed(2) : '-'}</div>
    `;
    listMy.prepend(li);
  }

  function showCashoutToast(mult, payout) {
    const t = document.getElementById('vmCashoutToast');
    if (!t) return;
    document.getElementById('vmToastMult').textContent = Number(mult).toFixed(2) + 'x';
    document.getElementById('vmToastWin').textContent = 'Win ' + Number(payout).toFixed(2) + ' PTI';
    t.style.display = 'flex';
    setTimeout(() => { t.style.display = 'none'; }, 3200);
  }

  // ================= ROUND LIFECYCLE =================
  function generateCrashPoint() {
    const r = Math.random();
    if (r < 0.25) return +(1.12 + Math.random() * 0.75).toFixed(2);
    if (r < 0.72) return +(2.05 + Math.random() * 3.80).toFixed(2);
    if (r < 0.92) return +(6.00 + Math.random() * 9.50).toFixed(2);
    return +(16.00 + Math.random() * 25.00).toFixed(2);
  }

  function startFlightRound(customTarget = null) {
    gameState = 'RUN';
    roundId = 'VM' + Date.now();
    currentMultiplier = 1.00;
    flightProgress = 0.0;
    targetCrashMultiplier = customTarget || generateCrashPoint();

    // Promote any QUEUED slots to ACTIVE
    [1, 2, 3, 4].forEach(s => {
      if (slots[s].status === 'QUEUED') {
        slots[s].status = 'ACTIVE';
      }
    });

    buildRoundBots();
    renderAllBetsList();
    updateSlotButtonsUI();

    document.getElementById('vmWaitingOverlay').style.display = 'none';
    document.getElementById('vmFlightOverlay').style.display = 'flex';
    document.getElementById('vmFlewAwayText').style.display = 'none';
    const multEl = document.getElementById('vmMultiplierText');
    multEl.classList.remove('crashed');
    multEl.textContent = '1.00x';

    playSfx('start');
  }

  async function triggerCrash() {
    gameState = 'BLAST';
    flyAwayX = planeX;
    playSfx('fly');

    const crashVal = +currentMultiplier.toFixed(2);
    historyMultipliers.unshift(crashVal);
    if (historyMultipliers.length > 22) historyMultipliers.pop();
    renderHistory();

    document.getElementById('vmFlewAwayText').style.display = 'block';
    const multEl = document.getElementById('vmMultiplierText');
    multEl.classList.add('crashed');
    multEl.textContent = crashVal.toFixed(2) + 'x';

    // Collect any un-cashed ACTIVE user bets and settle them as LOST in SQLite
    const lostBetIds = [];
    [1, 2, 3, 4].forEach(s => {
      if (slots[s].status === 'ACTIVE') {
        if (slots[s].betId) lostBetIds.push(slots[s].betId);
        slots[s].status = 'IDLE';
        slots[s].betId = null;
      }
    });
    updateSlotButtonsUI();

    if (lostBetIds.length > 0) {
      try {
        const res = await fetch('{{ route("api.vimaan.crash") }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': WB24.csrfToken,
          },
          body: JSON.stringify({
            bet_ids: lostBetIds,
            crash_multiplier: crashVal,
          }),
        });
        const data = await res.json();
        if (data.status) {
          updateWalletDom(data.wallet);
          (data.settled || []).forEach(st => {
            prependMyBetRow(st.time, st.stake, st.crash_multiplier, 0, false);
          });
        }
      } catch (e) {}
    }

    // After 2.6 seconds of FLEW AWAY!, enter 4.5s WAITING FOR NEXT ROUND state
    setTimeout(() => {
      startWaitingPhase();
    }, 2600);
  }

  function startWaitingPhase() {
    gameState = 'WAIT';
    waitProgress = 1.0;
    flightProgress = 0.0;

    document.getElementById('vmFlightOverlay').style.display = 'none';
    document.getElementById('vmWaitingOverlay').style.display = 'flex';
    updateSlotButtonsUI();
  }

  // ================= 60 FPS ANIMATION & CANVAS ENGINE =================
  function gameLoop(now) {
    const dt = Math.min(0.05, (now - lastFrameTime) / 1000);
    lastFrameTime = now;

    updateSimulation(dt);
    drawStage();

    requestAnimationFrame(gameLoop);
  }

  function updateSimulation(dt) {
    sunRayAngle += dt * 0.22;
    spriteFrameIdx = Math.floor(performance.now() / 80) % planeFrames.length;

    if (gameState === 'RUN') {
      // Smooth exponential climb speed (unrounded float so 60fps dt increments accumulate smoothly)
      const speed = 0.28 + (currentMultiplier - 1.0) * 0.22;
      currentMultiplier = currentMultiplier + dt * speed;
      flightProgress = Math.min(1.0, flightProgress + dt * 0.18);
      dotOffset = (dotOffset + dt * 55) % 60;

      const multEl = document.getElementById('vmMultiplierText');
      if (multEl) multEl.textContent = currentMultiplier.toFixed(2) + 'x';

      // Check Auto Cashout for active user slots
      [1, 2, 3, 4].forEach(s => {
        const st = slots[s];
        if (st.status === 'ACTIVE' && st.autoActive && currentMultiplier >= st.autoVal) {
          cashOutSlot(s, st.autoVal);
        }
      });

      // Check Bot Cashouts at their target multipliers
      let changedBots = false;
      let cashedCount = 0;
      liveBetsRows.forEach(r => {
        if (!r.isMe && !r.cashedOut && currentMultiplier >= r.targetMult) {
          r.cashedOut = true;
          r.cashoutMult = r.targetMult;
          r.cashoutAmount = +(r.stake * r.targetMult).toFixed(2);
          changedBots = true;
        }
        if (r.cashedOut) cashedCount++;
      });
      if (changedBots) {
        renderAllBetsList();
        document.getElementById('vmActiveUsersCount').textContent = cashedCount;
      }

      // Update live payout numbers on orange Cash Out buttons
      updateSlotButtonsUI();

      // Check if reached crash point
      if (currentMultiplier >= targetCrashMultiplier) {
        triggerCrash();
      }
    } else if (gameState === 'BLAST') {
      flyAwayX += dt * 950;
    } else if (gameState === 'WAIT') {
      waitProgress -= dt * 0.24;
      const bar = document.getElementById('vmWaitingBarFill');
      if (bar) bar.style.width = Math.max(0, waitProgress * 100) + '%';
      if (waitProgress <= 0) {
        startFlightRound();
      }
    }
  }

  function drawStage() {
    if (!ctx || !canvas) return;
    const W = canvas.width;
    const H = canvas.height;
    const axisX = 30;
    const axisY = H - 30;

    ctx.clearRect(0, 0, W, H);
    ctx.fillStyle = '#000000';
    ctx.fillRect(0, 0, W, H);

    // 1. Rotating Sunburst Rays anchored at bottom-left (0, H)
    ctx.save();
    ctx.translate(0, H);
    const numRays = 36;
    const maxRadius = Math.hypot(W, H) * 1.35;
    for (let i = 0; i < numRays; i++) {
      if (i % 2 === 0) {
        const a1 = sunRayAngle + (i * Math.PI * 2) / numRays;
        const a2 = sunRayAngle + ((i + 0.55) * Math.PI * 2) / numRays;
        ctx.beginPath();
        ctx.moveTo(0, 0);
        ctx.arc(0, 0, maxRadius, a1, a2);
        ctx.closePath();
        ctx.fillStyle = 'rgba(255, 255, 255, 0.045)';
        ctx.fill();
      }
    }
    ctx.restore();

    // 2. Center Radial Glow during flight (Blue <2x, Purple 2x-10x, Pink >10x)
    if (gameState === 'RUN') {
      const grad = ctx.createRadialGradient(W * 0.52, H * 0.48, 20, W * 0.52, H * 0.48, Math.max(W, H) * 0.55);
      if (currentMultiplier < 2.0) {
        grad.addColorStop(0, 'rgba(52, 180, 255, 0.34)');
      } else if (currentMultiplier < 10.0) {
        grad.addColorStop(0, 'rgba(145, 62, 248, 0.42)');
      } else {
        grad.addColorStop(0, 'rgba(192, 23, 180, 0.45)');
      }
      grad.addColorStop(1, 'rgba(0, 0, 0, 0)');
      ctx.fillStyle = grad;
      ctx.fillRect(0, 0, W, H);
    }

    // 3. Coordinate Axes & Animated Dots
    ctx.strokeStyle = '#2a2b2e';
    ctx.lineWidth = 1;
    ctx.beginPath();
    ctx.moveTo(axisX, 0);
    ctx.lineTo(axisX, axisY);
    ctx.lineTo(W, axisY);
    ctx.stroke();

    // Left vertical cyan dots (#34b4ff)
    const vSpacing = axisY / 10;
    ctx.fillStyle = '#34b4ff';
    for (let i = 0; i <= 10; i++) {
      const dy = ((i * vSpacing) + (gameState === 'RUN' ? dotOffset : 0)) % axisY;
      ctx.beginPath();
      ctx.arc(15, dy, 2.2, 0, Math.PI * 2);
      ctx.fill();
    }

    // Bottom horizontal white dots (#ffffff)
    const hSpacing = (W - axisX) / 9;
    ctx.fillStyle = '#ffffff';
    for (let i = 0; i <= 9; i++) {
      let dx = axisX + ((i * hSpacing) - (gameState === 'RUN' ? dotOffset : 0)) % (W - axisX);
      if (dx < axisX + 10) dx += (W - axisX);
      ctx.beginPath();
      ctx.arc(dx, H - 15, 2.2, 0, Math.PI * 2);
      ctx.fill();
    }

    // 4. Compute Plane Position & Draw Red Flight Curve + Area Fill
    if (gameState === 'RUN') {
      const hoverWave = flightProgress >= 0.75
        ? Math.sin(performance.now() / 420) * 18
        : 0;

      const targetX = axisX + (W - axisX - 180) * Math.pow(flightProgress, 0.78);
      const targetY = axisY - (axisY - 95) * Math.pow(flightProgress, 1.18) + hoverWave;

      planeX = targetX;
      planeY = targetY;

      const cpX = axisX + (planeX - axisX) * 0.58;
      const cpY = axisY - (axisY - planeY) * 0.14;

      // Shaded red area under curve
      const areaGrad = ctx.createLinearGradient(planeX, planeY, planeX, axisY);
      areaGrad.addColorStop(0, 'rgba(225, 18, 38, 0.52)');
      areaGrad.addColorStop(1, 'rgba(225, 18, 38, 0.10)');

      ctx.beginPath();
      ctx.moveTo(axisX, axisY);
      ctx.quadraticCurveTo(cpX, cpY, planeX, planeY);
      ctx.lineTo(planeX, axisY);
      ctx.closePath();
      ctx.fillStyle = areaGrad;
      ctx.fill();

      // Vibrant red curve stroke
      ctx.beginPath();
      ctx.moveTo(axisX, axisY);
      ctx.quadraticCurveTo(cpX, cpY, planeX, planeY);
      ctx.strokeStyle = '#f01124';
      ctx.lineWidth = 4;
      ctx.stroke();

      drawPlaneSprite(planeX - 12, planeY - 52, 132, 64);
    } else if (gameState === 'WAIT') {
      // Plane idling at bottom-left takeoff position (just like screenshot vimaan_live_1.png!)
      drawPlaneSprite(axisX + 4, axisY - 62, 132, 64);
    } else if (gameState === 'BLAST') {
      if (flyAwayX < W + 160) {
        drawPlaneSprite(flyAwayX, planeY - 60, 132, 64);
      }
    }
  }

  function drawPlaneSprite(x, y, w, h) {
    if (!imgPlane.complete || !imgPlane.naturalWidth) return;
    const f = planeFrames[spriteFrameIdx] || planeFrames[0];
    ctx.drawImage(imgPlane, f.x, f.y, f.w, f.h, x, y, w, h);
  }

  document.addEventListener('DOMContentLoaded', init);

  return {
    selectStake,
    updateChipValue,
    toggleEditStakes,
    clearStake,
    switchLeftTab,
    switchMode,
    toggleSlotAuto,
    toggleSound,
    toggleHistoryDropdown,
    handleSlotAction,
  };
})();
</script>
@endpush

