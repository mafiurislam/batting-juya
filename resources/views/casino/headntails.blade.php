@extends('layouts.app')

@section('content')
<style>
  /* Exact Winbook24 HEADS & TAILS (headNtails/88.0021) Styles */
  .hnt-wrapper {
    font-family: Tahoma, Helvetica, sans-serif;
    background: #000000;
    user-select: none;
    margin-bottom: 40px;
    position: relative;
  }

  /* Top Right Gear Icon */
  .hnt-player-info {
    position: relative;
    width: 100%;
    z-index: 30;
  }
  .hnt-right-list {
    position: absolute;
    right: 68px;
    top: 8px;
    z-index: 35;
  }
  .hnt-right-list ol {
    list-style: none;
    margin: 0;
    padding: 0;
  }
  .hnt-right-list li {
    cursor: pointer;
    color: #fd8f3b;
    font-size: 20px;
    transition: transform 0.2s ease;
  }
  .hnt-right-list li:hover {
    transform: rotate(45deg);
  }

  /* Circular Countdown Timer Overlay */
  .hnt-timer-overlay {
    position: absolute;
    right: 6px;
    top: 4px;
    z-index: 35;
    display: flex;
    flex-direction: column;
    align-items: center;
  }
  .hnt-timer-group {
    width: 50px;
    height: 50px;
    position: relative;
  }
  .hnt-timer-svg {
    width: 50px;
    height: 50px;
    transform: rotate(-90deg);
  }
  .hnt-timer-bg {
    fill: #111111;
    stroke: rgba(255, 255, 255, 0.12);
    stroke-width: 5;
  }
  .hnt-timer-prog {
    fill: none;
    stroke: #008000;
    stroke-width: 5;
    stroke-linecap: butt;
    stroke-dasharray: 138.2;
    stroke-dashoffset: 0;
    transition: stroke-dashoffset 0.95s linear, stroke 0.2s ease;
  }
  .hnt-timer-prog.red-alert {
    stroke: #ff0000;
  }
  .hnt-timer-face {
    position: absolute;
    inset: 5px;
    border-radius: 50%;
    background: #111111;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 20px;
    font-weight: 700;
  }
  .hnt-timer-face.red-alert {
    color: #ff0000;
    animation: hntBeat 0.5s infinite alternate;
  }
  @keyframes hntBeat {
    to { transform: scale(1.18); }
  }

  /* Black Stage & 3D Coin Flip */
  .hnt-casino-stream {
    position: relative;
    background: #000000;
    padding-bottom: 4px;
    overflow: hidden;
    perspective: 1200px;
  }
  .hnt-round-id {
    position: relative;
    top: 0;
    z-index: 20;
    font-weight: 700;
    font-size: 12px;
    color: #ffffff;
    padding: 4px 6px;
  }
  .hnt-coin {
    height: 350px;
    width: 350px;
    position: relative;
    margin: 18px auto 26px;
    -webkit-transform-style: preserve-3d;
    transform-style: preserve-3d;
  }
  .hnt-coin .heads,
  .hnt-coin .tails {
    position: absolute;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    -webkit-backface-visibility: hidden;
    backface-visibility: hidden;
  }
  .hnt-coin .tails {
    transform: rotateX(180deg);
  }
  .hnt-coin img {
    width: 350px;
    height: 350px;
    max-width: 100%;
    display: block;
    filter: drop-shadow(0 0 10px rgba(255, 200, 50, 0.18));
  }
  .hnt-coin.headAnimation {
    animation: spin-heads 2s forwards;
  }
  .hnt-coin.tailAnimation {
    animation: spin-tails 2s forwards;
  }
  @keyframes spin-heads {
    0% { transform: rotateX(0deg); }
    100% { transform: rotateX(2160deg); }
  }
  @keyframes spin-tails {
    0% { transform: rotateX(0deg); }
    100% { transform: rotateX(1980deg); }
  }

  /* Betting Buttons Zone (HEADS 1.90 & TAILS 1.90) */
  .hnt-lucky-footer {
    background: #000000;
    padding: 2px 0 0;
  }
  .hnt-bet-zone {
    width: 100%;
    display: flex;
    flex-direction: column;
  }
  .hnt-plsec {
    display: flex;
    width: 100%;
    padding: 0 6px;
    min-height: 18px;
  }
  .hnt-plsec .pl_heads,
  .hnt-plsec .pl_tails {
    width: 50%;
    text-align: center;
    font-size: 13px;
    font-weight: 700;
  }
  .hnt-plsec .plus-book {
    color: #32c64b;
  }
  .hnt-plsec .minus-book {
    color: #ff433f;
  }
  .hnt-down-set {
    display: flex;
    width: 100%;
    padding: 0 6px;
    height: 96px;
    margin: 4px 0 10px;
  }
  .hnt-box-player {
    position: relative;
    display: flex;
    flex: 1;
    height: 82px;
    margin-right: 6px;
    border: 1px solid #ffffff;
    border-radius: 2px;
    cursor: pointer;
    overflow: hidden;
    transition: transform 0.1s ease, box-shadow 0.15s ease;
  }
  .hnt-down-set .hnt-box-player:last-child {
    margin-right: 0;
  }
  .hnt-box-player:hover:not(.suspended) {
    box-shadow: 0 0 10px rgba(255, 255, 255, 0.55);
  }
  .hnt-box-player:active:not(.suspended) {
    transform: scale(0.985);
  }
  .hnt-box-player.winner-flash {
    box-shadow: 0 0 20px 5px #ffd700 !important;
    border-color: #ffd700 !important;
  }
  .hnt-box-player .betImage {
    width: 100%;
    height: 100%;
    display: block;
    object-fit: fill;
  }
  /* Suspended Lock Overlay */
  .hnt-box-player.suspended {
    pointer-events: none;
  }
  .hnt-box-player.suspended::after {
    position: absolute;
    inset: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: rgba(0, 0, 0, 0.6);
    color: #ffffff;
    font-family: "Font Awesome 6 Free", "Font Awesome 5 Free", FontAwesome;
    content: "\f023";
    font-weight: 900;
    font-size: 18px;
    z-index: 10;
  }

  /* Poker Chips Selector Row */
  .hnt-function-zone {
    width: 100%;
    height: 92px;
    line-height: 80px;
    text-align: center;
    background: #000000;
    padding: 4px 4px 0;
    position: relative;
    z-index: 15;
  }
  .hnt-chips {
    padding: 0;
    margin: 0;
    list-style: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    flex-wrap: wrap;
  }
  .hnt-chips li {
    width: 67px;
    height: 66px;
    position: relative;
    top: 0;
    list-style: none;
    cursor: pointer;
    display: inline-block;
    text-align: center;
    transition: all 0.15s linear;
  }
  .hnt-chips li:hover {
    top: -5px;
  }
  .hnt-chips li.now {
    top: -6px;
  }
  .hnt-chips li img {
    width: 67px;
    height: 66px;
    position: absolute;
    top: 0;
    left: 0;
    z-index: 20;
    filter: drop-shadow(0 0 0.25rem #fd8f3b);
  }
  .hnt-chips li p {
    width: 67px;
    height: 66px;
    text-align: center;
    line-height: 57px;
    font-size: 14px;
    font-weight: 700;
    color: #272727;
    position: absolute;
    top: 0;
    left: 0;
    z-index: 25;
    margin: 0;
    pointer-events: none;
  }
  .hnt-chips li .ring {
    position: absolute;
    width: 64px;
    height: 52px;
    border: 0 solid #fd8f3b;
    border-radius: 100px / 76px;
    top: 12px;
    left: calc(50% - 32px);
    font-size: 0;
    text-indent: -99999px;
    z-index: 30;
    pointer-events: none;
    transition: all 0.15s linear;
  }
  .hnt-chips li.now .ring {
    border: 4px solid #fd8f3b;
    box-shadow: 0 0 10px rgba(253, 143, 59, 0.85);
  }

  /* Recent Result Bar (Black with circular H & T badges) */
  .hnt-recent-res {
    background: #000000;
    color: #ffffff;
    padding: 8px 6px 14px;
    margin-top: 5px;
    width: 100%;
    display: flex;
    align-items: center;
    white-space: nowrap;
    overflow-x: auto;
  }
  .hnt-recent-res h5 {
    display: inline-block;
    font-size: 17px;
    font-weight: 700;
    margin: 0;
    color: #ffffff;
  }
  .hnt-recent-res ul {
    padding: 0;
    margin: 0;
    display: inline-flex;
    align-items: center;
  }
  .hnt-recent-res ul li {
    width: 40px;
    height: 40px;
    margin-left: 10px;
    padding: 4px;
    list-style: none;
    text-align: center;
    font-weight: 700;
    font-size: 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 25px;
    cursor: pointer;
  }
  .hnt-recent-res ul li.H {
    background: #1f1426;
    color: #cc85ff;
    border: 2px solid #533668;
  }
  .hnt-recent-res ul li.T {
    background: #0f1626;
    color: #6198ff;
    border: 2px solid #273c68;
  }
  .hnt-recent-res ul li a {
    color: inherit !important;
    text-decoration: none;
  }

  /* Toast Notification */
  .hnt-toast {
    position: fixed;
    top: 80px;
    right: 20px;
    z-index: 99999;
    min-width: 240px;
    padding: 12px 16px;
    border-radius: 4px;
    color: #fff;
    font-size: 13px;
    font-weight: 700;
    box-shadow: 0 4px 14px rgba(0,0,0,0.45);
    display: none;
  }
  .hnt-toast.success { background: #198754; }
  .hnt-toast.error { background: #dc3545; }
  .hnt-toast.info { background: #2e4b5e; }

  /* Quick Stakes & Result Modal */
  .hnt-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.65);
    z-index: 10000;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 16px;
  }
  .hnt-modal-overlay.active {
    display: flex;
  }
  .hnt-modal-card {
    background: #ffffff;
    border-radius: 5px;
    width: 100%;
    max-width: 480px;
    overflow: hidden;
    box-shadow: 0 8px 28px rgba(0,0,0,0.5);
  }
  .hnt-modal-header {
    background: linear-gradient(-180deg, #315195 0%, #14213D 100%);
    color: #ffffff;
    padding: 10px 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-weight: 700;
    font-size: 15px;
  }
  .hnt-modal-header button {
    background: transparent;
    border: 0;
    color: #ffffff;
    font-size: 20px;
    font-weight: 700;
    cursor: pointer;
    line-height: 1;
  }
  .hnt-modal-body {
    padding: 16px;
    color: #23282c;
  }
  .hnt-stakes-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
    margin-bottom: 14px;
  }
  .hnt-stakes-grid input {
    width: 100%;
    height: 34px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    text-align: center;
    font-weight: 700;
    font-size: 13px;
  }
  .hnt-save-btn {
    width: 100%;
    height: 36px;
    background: linear-gradient(-180deg, #315195 0%, #14213D 100%);
    color: #ffffff;
    border: 0;
    border-radius: 4px;
    font-weight: 700;
    cursor: pointer;
  }

  @media (max-width: 767px) {
    .hnt-coin, .hnt-coin img { width: 260px; height: 260px; }
    .hnt-down-set { height: 74px; }
    .hnt-box-player { height: 68px; }
    .hnt-chips li, .hnt-chips li img, .hnt-chips li p { width: 48px; height: 48px; line-height: 44px; font-size: 12px; }
    .hnt-chips li .ring { width: 46px; height: 38px; left: calc(50% - 23px); top: 8px; }
  }
</style>

<div class="hnt-wrapper">
  <!-- Top Gear Button -->
  <div id="playerInfo" class="hnt-player-info">
    <div class="hnt-right-list">
      <ol>
        <li onclick="openHntStakesModal()" title="Quick Stakes Settings">
          <i class="fa fa-cog fa-lg"></i>
        </li>
      </ol>
    </div>
  </div>

  <!-- Top-Right Circular Countdown Timer -->
  <div class="hnt-timer-overlay" id="hntTimerOverlay">
    <div class="hnt-timer-group">
      <svg class="hnt-timer-svg" viewBox="0 0 50 50">
        <circle class="hnt-timer-bg" cx="25" cy="25" r="22"></circle>
        <circle class="hnt-timer-prog" id="hntTimerCircle" cx="25" cy="25" r="22"></circle>
      </svg>
      <div class="hnt-timer-face" id="hntTimerText">16</div>
    </div>
  </div>

  <!-- Black Stage & 3D Gold Coin -->
  <div id="blackBackground" class="hnt-casino-stream">
    <div class="hnt-round-id" id="hntRoundId">RID: 8211011115231</div>

    <div id="coin" class="hnt-coin">
      <div class="heads">
        <img src="/assets/headntails/heads.png" alt="UNIVERSE HEADS COIN">
      </div>
      <div class="tails">
        <img src="/assets/headntails/tails.png" alt="UNIVERSE TAILS COIN">
      </div>
    </div>
  </div>

  <!-- Betting Buttons Zone: HEADS (1.90) & TAILS (1.90) -->
  <div class="hnt-lucky-footer">
    <section id="betZone" class="hnt-bet-zone">
      <div class="hnt-plsec">
        <span class="pl_heads" id="hntPl_HEADS"></span>
        <span class="pl_tails" id="hntPl_TAILS"></span>
      </div>
      <div name="betZoneBac" class="hnt-down-set">
        <div class="hnt-box-player 0 greenodd no_win" id="hntBox_HEADS" onclick="placeHeadNtailsBet('HEADS', 'HEADS', 1.90)">
          <img src="/assets/headntails/head-bet.jpg" class="betImage" alt="HEADS 1.90">
        </div>
        <div class="hnt-box-player 1 redodd no_win" id="hntBox_TAILS" onclick="placeHeadNtailsBet('TAILS', 'TAILS', 1.90)">
          <img src="/assets/headntails/tails-bet.jpg" class="betImage" alt="TAILS 1.90">
        </div>
      </div>
    </section>
  </div>

  <!-- Poker Chips Selector Row -->
  <section id="functionZone" class="hnt-function-zone virtual-chip mt-2">
    <ul id="chips" class="hnt-chips">
      <li class="chips_1 now" data-stake="100" onclick="selectHntChip(this, 100)">
        <img src="/assets/lucky0to9/chips1.svg" alt="100">
        <p>100</p>
        <span class="ring">ring</span>
      </li>
      <li class="chips_1" data-stake="150" onclick="selectHntChip(this, 150)">
        <img src="/assets/lucky0to9/chips1.svg" alt="150">
        <p>150</p>
        <span class="ring">ring</span>
      </li>
      <li class="chips_1" data-stake="200" onclick="selectHntChip(this, 200)">
        <img src="/assets/lucky0to9/chips1.svg" alt="200">
        <p>200</p>
        <span class="ring">ring</span>
      </li>
      <li class="chips_1" data-stake="250" onclick="selectHntChip(this, 250)">
        <img src="/assets/lucky0to9/chips1.svg" alt="250">
        <p>250</p>
        <span class="ring">ring</span>
      </li>
      <li class="chips_1" data-stake="300" onclick="selectHntChip(this, 300)">
        <img src="/assets/lucky0to9/chips1.svg" alt="300">
        <p>300</p>
        <span class="ring">ring</span>
      </li>
      <li class="chips_1" data-stake="500" onclick="selectHntChip(this, 500)">
        <img src="/assets/lucky0to9/chips1.svg" alt="500">
        <p>500</p>
        <span class="ring">ring</span>
      </li>
      <li class="chips_1" data-stake="700" onclick="selectHntChip(this, 700)">
        <img src="/assets/lucky0to9/chips1.svg" alt="700">
        <p>700</p>
        <span class="ring">ring</span>
      </li>
      <li class="chips_1" data-stake="1000" onclick="selectHntChip(this, 1000)">
        <img src="/assets/lucky0to9/chips1k.svg" alt="1k">
        <p>1k</p>
        <span class="ring">ring</span>
      </li>
    </ul>
  </section>

  <!-- Recent Result Bar -->
  <div class="hnt-recent-res teenpatti headntails">
    <h5>Recent Result</h5>
    <ul class="result" id="hntRecentResults">
      <li class="virtual H" onclick="openHntResultModal('8211011115156', 'H')"><a class="number">H</a></li>
      <li class="virtual T" onclick="openHntResultModal('8211011115121', 'T')"><a class="number">T</a></li>
      <li class="virtual H" onclick="openHntResultModal('8211011115046', 'H')"><a class="number">H</a></li>
      <li class="virtual H" onclick="openHntResultModal('8211011115011', 'H')"><a class="number">H</a></li>
      <li class="virtual T" onclick="openHntResultModal('8211011114936', 'T')"><a class="number">T</a></li>
      <li class="virtual H" onclick="openHntResultModal('8211011114901', 'H')"><a class="number">H</a></li>
      <li class="virtual H" onclick="openHntResultModal('8211011114826', 'H')"><a class="number">H</a></li>
      <li class="virtual T" onclick="openHntResultModal('8211011114751', 'T')"><a class="number">T</a></li>
      <li class="virtual H" onclick="openHntResultModal('8211011114716', 'H')"><a class="number">H</a></li>
      <li class="virtual T" onclick="openHntResultModal('8211011114641', 'T')"><a class="number">T</a></li>
    </ul>
  </div>
</div>

<!-- Quick Stakes Modal -->
<div class="hnt-modal-overlay" id="hntStakesModal">
  <div class="hnt-modal-card">
    <div class="hnt-modal-header">
      <span>Stake</span>
      <button type="button" onclick="closeHntStakesModal()">×</button>
    </div>
    <div class="hnt-modal-body">
      <div style="font-size:14px;font-weight:700;margin-bottom:8px;">Quick Stakes</div>
      <div class="hnt-stakes-grid" id="hntStakesInputs">
        <input type="number" value="100" min="10" max="999999">
        <input type="number" value="150" min="10" max="999999">
        <input type="number" value="200" min="10" max="999999">
        <input type="number" value="250" min="10" max="999999">
        <input type="number" value="300" min="10" max="999999">
        <input type="number" value="500" min="10" max="999999">
        <input type="number" value="700" min="10" max="999999">
        <input type="number" value="1000" min="10" max="999999">
      </div>
      <button type="button" class="hnt-save-btn" onclick="saveHntStakes()">Save</button>
    </div>
  </div>
</div>

<!-- Result Detail Modal -->
<div class="hnt-modal-overlay" id="hntResultModal">
  <div class="hnt-modal-card">
    <div class="hnt-modal-header">
      <span>HEADS &amp; TAILS Result</span>
      <button type="button" onclick="closeHntResultModal()">×</button>
    </div>
    <div class="hnt-modal-body">
      <div style="text-align:right;font-size:13px;font-weight:700;margin-bottom:12px;">
        Round Id: <span id="hntModalRoundId">8211011115156</span>
      </div>
      <div style="text-align:center;margin-bottom:14px;">
        <img id="hntModalCoinImg" src="/assets/headntails/heads.png" alt="Result Coin" style="width:120px;height:120px;">
      </div>
      <div style="border-top:1px solid #ccc;padding-top:10px;font-size:13px;">
        <div style="display:flex;justify-content:space-between;padding:4px 0;">
          <strong>WINNER</strong>
          <span id="hntModalWinnerText" style="font-weight:700;color:#198754;">HEADS</span>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="hnt-toast" id="hntToast"></div>

@push('scripts')
<script>
  const HNT_CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';
  const HNT_IS_AUTH = {{ auth()->check() ? 'true' : 'false' }};

  let hntSelectedStake = 100;
  let hntRoundCounter = 8211011115231;
  let hntTimeLeft = 16;
  let hntPhase = 'BETTING'; // 'BETTING' | 'FLIPPING'
  let hntBetInProcess = false;
  let hntStakesMap = { HEADS: 0, TAILS: 0 };

  const hntCoinAudio = new Audio('/assets/headntails/coinsound.mp3');
  const hntHeadWinAudio = new Audio('/assets/headntails/headwin.mp3');
  const hntTailWinAudio = new Audio('/assets/headntails/tailwin.mp3');

  function playHntSound(audioObj) {
    try {
      audioObj.currentTime = 0;
      audioObj.play().catch(() => {});
    } catch (e) {}
  }

  function showHntToast(msg, type = 'info') {
    const el = document.getElementById('hntToast');
    if (!el) return;
    el.textContent = msg;
    el.className = 'hnt-toast ' + type;
    el.style.display = 'block';
    clearTimeout(window._hntToastTimer);
    window._hntToastTimer = setTimeout(() => {
      el.style.display = 'none';
    }, 2800);
  }

  function selectHntChip(liEl, amount) {
    document.querySelectorAll('#chips li').forEach(el => el.classList.remove('now'));
    if (hntSelectedStake === amount) {
      hntSelectedStake = 0;
    } else {
      hntSelectedStake = amount;
      liEl.classList.add('now');
    }
  }

  function setHntSuspended(isSuspended) {
    const boxes = document.querySelectorAll('.hnt-box-player');
    const timerOverlay = document.getElementById('hntTimerOverlay');
    boxes.forEach(b => {
      b.classList.toggle('suspended', isSuspended);
      if (isSuspended) b.classList.remove('winner-flash');
    });
    if (timerOverlay) {
      timerOverlay.style.display = isSuspended ? 'none' : 'flex';
    }
  }

  function renderHntPlBook() {
    const hStake = hntStakesMap.HEADS || 0;
    const tStake = hntStakesMap.TAILS || 0;
    const plHeadsEl = document.getElementById('hntPl_HEADS');
    const plTailsEl = document.getElementById('hntPl_TAILS');

    if (hStake === 0 && tStake === 0) {
      if (plHeadsEl) plHeadsEl.innerHTML = '';
      if (plTailsEl) plTailsEl.innerHTML = '';
      return;
    }

    const netIfHeads = hStake * 0.90 - tStake;
    const netIfTails = tStake * 0.90 - hStake;

    if (plHeadsEl) {
      const cls = netIfHeads >= 0 ? 'plus-book' : 'minus-book';
      const sign = netIfHeads >= 0 ? '+' : '';
      plHeadsEl.innerHTML = `<span class="${cls}">${sign}${netIfHeads.toFixed(2)}</span>`;
    }
    if (plTailsEl) {
      const cls = netIfTails >= 0 ? 'plus-book' : 'minus-book';
      const sign = netIfTails >= 0 ? '+' : '';
      plTailsEl.innerHTML = `<span class="${cls}">${sign}${netIfTails.toFixed(2)}</span>`;
    }
  }

  async function placeHeadNtailsBet(selectionKey, selectionName, odds) {
    if (hntPhase !== 'BETTING' || hntBetInProcess) return;

    if (!hntSelectedStake || hntSelectedStake <= 0) {
      showHntToast('please select chips for Bet', 'error');
      return;
    }

    if (!HNT_IS_AUTH) {
      showHntToast('Please login (or click Login with Demo ID) to place bets.', 'error');
      return;
    }

    hntBetInProcess = true;
    try {
      const res = await fetch('/api/casino/teenpatti/place-bet', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': HNT_CSRF
        },
        body: JSON.stringify({
          event_id: '88.0021',
          round_id: String(hntRoundCounter),
          market_key: 'WINNER',
          market_name: 'WINNER',
          selection_key: selectionKey,
          selection_name: selectionName,
          odds: odds,
          stake: hntSelectedStake
        })
      });
      const data = await res.json();
      if (data.status) {
        hntStakesMap[selectionKey] = (hntStakesMap[selectionKey] || 0) + hntSelectedStake;
        renderHntPlBook();
        if (data.wallet) {
          updateHeaderWallet(data.wallet);
        }
        appendOpenBetRow(selectionName, odds, hntSelectedStake);
        showHntToast(`Bet Placed: ${selectionName} @ ${odds.toFixed(2)} (${hntSelectedStake} PTI)`, 'success');
      } else {
        showHntToast(data.message || 'Could not place bet', 'error');
      }
    } catch (e) {
      showHntToast('Network error placing bet', 'error');
    } finally {
      hntBetInProcess = false;
    }
  }

  function updateHeaderWallet(wallet) {
    const balEl = document.querySelector('.userTotalBalance, #userBalanceVal');
    const expEl = document.querySelector('.userTotalExposure, #userExposureVal');
    if (balEl && wallet.available !== undefined) balEl.textContent = Number(wallet.available).toFixed(2);
    if (expEl && wallet.exposure !== undefined) expEl.textContent = Number(wallet.exposure).toFixed(2);
  }

  function appendOpenBetRow(selectionName, odds, stake) {
    const container = document.getElementById('openBetsListContainer');
    if (!container) return;
    const row = document.createElement('div');
    row.style.cssText = 'background:#beddf4;border-bottom:1px solid #67afe5;padding:4px 6px;font-size:11px;display:flex;justify-content:space-between;align-items:center;';
    row.innerHTML = `<strong>HEADS & TAILS - ${selectionName}</strong><span>${odds.toFixed(2)} | ${stake} PTI</span>`;
    container.prepend(row);
  }

  async function triggerHntFlipAndSettle() {
    hntPhase = 'FLIPPING';
    setHntSuspended(true);

    let settleData = null;
    try {
      const res = await fetch('/api/casino/headntails/settle-round', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': HNT_CSRF
        },
        body: JSON.stringify({
          event_id: '88.0021',
          round_id: String(hntRoundCounter)
        })
      });
      settleData = await res.json();
    } catch (e) {
      const isHeads = Math.random() < 0.5;
      settleData = {
        status: true,
        round_id: String(hntRoundCounter),
        winner: isHeads ? 'HEADS' : 'TAILS',
        badge: isHeads ? 'H' : 'T',
        summary: `WINNER: ${isHeads ? 'HEADS' : 'TAILS'}`
      };
    }

    const winner = settleData?.winner === 'TAILS' ? 'TAILS' : 'HEADS';
    const badge = winner === 'TAILS' ? 'T' : 'H';

    const coinEl = document.getElementById('coin');
    if (coinEl) {
      coinEl.classList.remove('headAnimation', 'tailAnimation');
      void coinEl.offsetWidth; // Reflow to restart 3D flip animation
      playHntSound(hntCoinAudio);
      coinEl.classList.add(winner === 'HEADS' ? 'headAnimation' : 'tailAnimation');
    }

    setTimeout(() => {
      if (winner === 'HEADS') {
        playHntSound(hntHeadWinAudio);
      } else {
        playHntSound(hntTailWinAudio);
      }

      const winBox = document.getElementById('hntBox_' + winner);
      if (winBox) winBox.classList.add('winner-flash');

      // Prepend circular H or T badge to Recent Result bar
      const ul = document.getElementById('hntRecentResults');
      if (ul) {
        const li = document.createElement('li');
        li.className = 'virtual ' + badge;
        const rId = String(hntRoundCounter);
        li.onclick = () => openHntResultModal(rId, badge);
        li.innerHTML = `<a class="number">${badge}</a>`;
        ul.prepend(li);
        while (ul.children.length > 10) {
          ul.removeChild(ul.lastElementChild);
        }
      }

      if (settleData?.wallet) {
        updateHeaderWallet(settleData.wallet);
      }

      if (settleData?.settled_bets && settleData.settled_bets.length > 0) {
        const net = settleData.settled_bets.reduce((acc, b) => acc + Number(b.net_pl || 0), 0);
        if (net >= 0) {
          showHntToast(`Round #${hntRoundCounter}: ${settleData.summary} | Won +${net.toFixed(2)} PTI!`, 'success');
        } else {
          showHntToast(`Round #${hntRoundCounter}: ${settleData.summary} | Net ${net.toFixed(2)} PTI`, 'info');
        }
      }

      setTimeout(() => {
        startNewHntRound();
      }, 2500);
    }, 2000);
  }

  function startNewHntRound() {
    hntRoundCounter += 35;
    hntTimeLeft = 20;
    hntPhase = 'BETTING';
    hntStakesMap = { HEADS: 0, TAILS: 0 };
    document.getElementById('hntRoundId').textContent = 'RID: ' + hntRoundCounter;
    renderHntPlBook();
    setHntSuspended(false);
    updateHntTimerUI();
  }

  function updateHntTimerUI() {
    const txtEl = document.getElementById('hntTimerText');
    const cirEl = document.getElementById('hntTimerCircle');
    if (!txtEl || !cirEl) return;

    txtEl.textContent = hntTimeLeft;
    const circumference = 138.2;
    const offset = circumference - (hntTimeLeft / 20) * circumference;
    cirEl.style.strokeDashoffset = Math.max(0, offset);

    const isLow = hntTimeLeft < 6;
    txtEl.classList.toggle('red-alert', isLow);
    cirEl.classList.toggle('red-alert', isLow);
  }

  function openHntStakesModal() {
    document.getElementById('hntStakesModal')?.classList.add('active');
  }
  function closeHntStakesModal() {
    document.getElementById('hntStakesModal')?.classList.remove('active');
  }
  function saveHntStakes() {
    const inputs = document.querySelectorAll('#hntStakesInputs input');
    const chipItems = document.querySelectorAll('#chips li');
    inputs.forEach((inp, idx) => {
      const val = Math.max(10, parseInt(inp.value, 10) || 100);
      if (chipItems[idx]) {
        chipItems[idx].dataset.stake = val;
        chipItems[idx].setAttribute('onclick', `selectHntChip(this, ${val})`);
        const p = chipItems[idx].querySelector('p');
        const img = chipItems[idx].querySelector('img');
        if (p) p.textContent = val >= 1000 ? (val / 1000) + 'k' : val;
        if (img) img.src = val >= 1000 ? '/assets/lucky0to9/chips1k.svg' : '/assets/lucky0to9/chips1.svg';
      }
    });
    closeHntStakesModal();
    showHntToast('Quick Stakes updated!', 'success');
  }

  function openHntResultModal(roundId, badge) {
    document.getElementById('hntModalRoundId').textContent = roundId;
    const isTails = badge === 'T';
    const coinImg = document.getElementById('hntModalCoinImg');
    if (coinImg) {
      coinImg.src = isTails ? '/assets/headntails/tails.png' : '/assets/headntails/heads.png';
    }
    const winTxt = document.getElementById('hntModalWinnerText');
    if (winTxt) {
      winTxt.textContent = isTails ? 'TAILS' : 'HEADS';
      winTxt.style.color = isTails ? '#315195' : '#533668';
    }
    document.getElementById('hntResultModal')?.classList.add('active');
  }
  function closeHntResultModal() {
    document.getElementById('hntResultModal')?.classList.remove('active');
  }

  document.addEventListener('DOMContentLoaded', () => {
    updateHntTimerUI();

    setInterval(() => {
      if (hntPhase !== 'BETTING') return;
      hntTimeLeft--;
      if (hntTimeLeft <= 0) {
        hntTimeLeft = 0;
        updateHntTimerUI();
        triggerHntFlipAndSettle();
      } else {
        updateHntTimerUI();
      }
    }, 1000);
  });
</script>
@endpush
@endsection

