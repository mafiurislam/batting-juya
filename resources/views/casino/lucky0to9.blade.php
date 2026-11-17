@extends('layouts.app')

@section('content')
<style>
  /* Exact Winbook24 LUCKY 0 TO 9 (lucky0To9/88.0019) Styles */
  .l09-wrapper {
    font-family: Tahoma, Helvetica, sans-serif;
    background: #000000;
    user-select: none;
    margin-bottom: 40px;
    position: relative;
  }

  /* Top Right Gear Icon */
  .l09-player-info {
    position: relative;
    width: 100%;
    z-index: 30;
  }
  .l09-right-list {
    position: absolute;
    right: 68px;
    top: 8px;
    z-index: 35;
  }
  .l09-right-list ol {
    list-style: none;
    margin: 0;
    padding: 0;
  }
  .l09-right-list li {
    cursor: pointer;
    color: #fd8f3b;
    font-size: 20px;
    transition: transform 0.2s ease;
  }
  .l09-right-list li:hover {
    transform: rotate(45deg);
  }

  /* Circular Countdown Timer Overlay */
  .l09-timer-overlay {
    position: absolute;
    right: 6px;
    top: 4px;
    z-index: 35;
    display: flex;
    flex-direction: column;
    align-items: center;
  }
  .l09-timer-group {
    width: 50px;
    height: 50px;
    position: relative;
  }
  .l09-timer-svg {
    width: 50px;
    height: 50px;
    transform: rotate(-90deg);
  }
  .l09-timer-bg {
    fill: #111111;
    stroke: rgba(255, 255, 255, 0.12);
    stroke-width: 5;
  }
  .l09-timer-prog {
    fill: none;
    stroke: #008000;
    stroke-width: 5;
    stroke-linecap: butt;
    stroke-dasharray: 138.2;
    stroke-dashoffset: 0;
    transition: stroke-dashoffset 0.95s linear, stroke 0.2s ease;
  }
  .l09-timer-prog.red-alert {
    stroke: #ff0000;
  }
  .l09-timer-face {
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
  .l09-timer-face.red-alert {
    color: #ff0000;
    animation: l09Beat 0.5s infinite alternate;
  }
  @keyframes l09Beat {
    to { transform: scale(1.18); }
  }

  /* Cyberpunk Rainy City Stage & Wheel */
  .l09-casino-stream {
    position: relative;
    background: url('/assets/lucky0to9/wheelmainbg.gif') center center / cover no-repeat;
    min-height: 410px;
    padding-bottom: 6px;
    overflow: hidden;
  }
  .l09-round-id {
    position: relative;
    top: 0;
    z-index: 20;
    font-weight: 700;
    font-size: 12px;
    color: #ffffff;
    padding: 4px 6px;
  }
  .l09-wheel-wrap {
    display: flex;
    justify-content: center;
    align-items: center;
    position: relative;
    margin-top: -12px;
  }
  #luckyWheelCanvas {
    background: url('/assets/lucky0to9/wheelbg.png') center center no-repeat;
    width: 400px;
    height: 400px;
    max-width: 100%;
    display: block;
  }
  .l09-final-result-div {
    position: absolute;
    left: 0;
    right: 0;
    top: 40px;
    z-index: 25;
    display: none;
    justify-content: center;
    pointer-events: none;
  }
  .l09-final-result-div.active {
    display: flex;
  }
  .l09-final-result-div img {
    width: 120px;
    height: 120px;
  }

  /* Betting Grid (0-9, EVEN, RED, BLACK, ODD) */
  .l09-lucky-footer {
    background: #000000;
    padding: 4px 0 0;
  }
  .l09-bet-zone {
    width: 100%;
    display: flex;
    flex-direction: column;
  }
  .l09-down-set {
    display: flex;
    width: 100%;
    padding: 0 6px;
    height: 86px;
    margin: 6px 0;
  }
  .l09-box-player {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    flex: 1;
    height: 82px;
    margin-right: 6px;
    border: 2px solid #ff433f;
    border-radius: 4px;
    cursor: pointer;
    transition: transform 0.1s ease, box-shadow 0.15s ease;
    overflow: hidden;
  }
  .l09-down-set .l09-box-player:last-child {
    margin-right: 0;
  }
  .l09-box-player:hover:not(.suspended) {
    box-shadow: 0 0 10px rgba(255, 67, 63, 0.75);
  }
  .l09-box-player:active:not(.suspended) {
    transform: scale(0.97);
  }
  .l09-box-player.winner-flash {
    box-shadow: 0 0 20px 6px #ffd700 !important;
    border-color: #ffd700 !important;
  }
  .l09-box-player.greenodd {
    background: linear-gradient(-180deg, #105d24 1%, #196631 99%);
  }
  .l09-box-player.redodd {
    background: linear-gradient(-180deg, #e1282a 1%, #e1272c 99%);
  }
  .l09-box-player.blackodd {
    background: linear-gradient(-180deg, #211e1e 1%, #000000 99%);
  }
  .l09-box-player h2 {
    color: #ffffff !important;
    font-size: 18px;
    font-weight: 700;
    margin: 0;
    width: 100%;
    text-align: center;
    z-index: 2;
  }
  /* Suspended Lock Overlay */
  .l09-box-player.suspended {
    pointer-events: none;
  }
  .l09-box-player.suspended::after {
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
  /* P&L Pill inside Betting Box */
  .l09-pl-lucky {
    position: absolute;
    bottom: 4px;
    border-radius: 4px;
    padding: 1px 6px;
    background: #cccccc;
    font-size: 11px;
    font-weight: 700;
    z-index: 5;
    display: none;
  }
  .l09-pl-lucky.active {
    display: inline-block;
  }
  .l09-pl-lucky .plus-book {
    color: #156226;
  }
  .l09-pl-lucky .minus-book {
    color: #d0021b;
  }

  /* Poker Chips Row */
  .l09-function-zone {
    width: 100%;
    height: 92px;
    line-height: 80px;
    text-align: center;
    background: #000000;
    padding: 8px 4px 0;
    position: relative;
    z-index: 15;
  }
  .l09-chips {
    padding: 0;
    margin: 0;
    list-style: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    flex-wrap: wrap;
  }
  .l09-chips li {
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
  .l09-chips li:hover {
    top: -5px;
  }
  .l09-chips li.now {
    top: -6px;
  }
  .l09-chips li img {
    width: 67px;
    height: 66px;
    position: absolute;
    top: 0;
    left: 0;
    z-index: 20;
    filter: drop-shadow(0 0 0.25rem #fd8f3b);
  }
  .l09-chips li p {
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
  .l09-chips li .ring {
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
  .l09-chips li.now .ring {
    border: 4px solid #fd8f3b;
    box-shadow: 0 0 10px rgba(253, 143, 59, 0.85);
  }

  /* Recent Result Bar */
  .l09-recent-res {
    background: #b3b0b0;
    color: #000000;
    padding: 5px 8px;
    margin-top: 5px;
    width: 100%;
    display: flex;
    align-items: center;
    white-space: nowrap;
    overflow-x: auto;
  }
  .l09-recent-res h5 {
    display: inline-block;
    font-size: 17px;
    font-weight: 700;
    margin: 0;
    color: #000000;
  }
  .l09-recent-res ul {
    padding: 0;
    margin: 0;
    display: inline-flex;
    align-items: center;
  }
  .l09-recent-res ul li {
    width: 35px;
    height: 31px;
    margin-left: 10px;
    padding: 4px;
    list-style: none;
    text-align: center;
    font-weight: 700;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 15px;
    cursor: pointer;
    color: #ffffff;
  }
  .l09-recent-res ul li.greenNumber {
    background: linear-gradient(-180deg, #105d24 1%, #196631 99%);
  }
  .l09-recent-res ul li.redNumber {
    background: linear-gradient(-180deg, #e1282a 1%, #e1272c 99%);
  }
  .l09-recent-res ul li.blackNumber {
    background: linear-gradient(-180deg, #211e1e 1%, #000000 99%);
  }
  .l09-recent-res ul li a {
    color: #ffffff !important;
    text-decoration: none;
  }

  /* Toast Notification */
  .l09-toast {
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
  .l09-toast.success { background: #198754; }
  .l09-toast.error { background: #dc3545; }
  .l09-toast.info { background: #2e4b5e; }

  /* Quick Stakes & Result Modal */
  .l09-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.65);
    z-index: 10000;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 16px;
  }
  .l09-modal-overlay.active {
    display: flex;
  }
  .l09-modal-card {
    background: #ffffff;
    border-radius: 5px;
    width: 100%;
    max-width: 480px;
    overflow: hidden;
    box-shadow: 0 8px 28px rgba(0,0,0,0.5);
  }
  .l09-modal-header {
    background: linear-gradient(-180deg, #315195 0%, #14213D 100%);
    color: #ffffff;
    padding: 10px 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-weight: 700;
    font-size: 15px;
  }
  .l09-modal-header button {
    background: transparent;
    border: 0;
    color: #ffffff;
    font-size: 20px;
    font-weight: 700;
    cursor: pointer;
    line-height: 1;
  }
  .l09-modal-body {
    padding: 16px;
    color: #23282c;
  }
  .l09-stakes-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
    margin-bottom: 14px;
  }
  .l09-stakes-grid input {
    width: 100%;
    height: 34px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    text-align: center;
    font-weight: 700;
    font-size: 13px;
  }
  .l09-save-btn {
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
    .l09-casino-stream { min-height: 320px; }
    #luckyWheelCanvas { width: 310px; height: 310px; }
    .l09-down-set { height: 64px; }
    .l09-box-player { height: 60px; }
    .l09-chips li, .l09-chips li img, .l09-chips li p { width: 48px; height: 48px; line-height: 44px; font-size: 12px; }
    .l09-chips li .ring { width: 46px; height: 38px; left: calc(50% - 23px); top: 8px; }
  }
</style>

<div class="l09-wrapper">
  <!-- Top Gear Button -->
  <div id="playerInfo" class="l09-player-info">
    <div class="l09-right-list">
      <ol>
        <li onclick="openL09StakesModal()" title="Quick Stakes Settings">
          <i class="fa fa-cog fa-lg"></i>
        </li>
      </ol>
    </div>
  </div>

  <!-- Top-Right Circular Countdown Timer -->
  <div class="l09-timer-overlay" id="l09TimerOverlay">
    <div class="l09-timer-group">
      <svg class="l09-timer-svg" viewBox="0 0 50 50">
        <circle class="l09-timer-bg" cx="25" cy="25" r="22"></circle>
        <circle class="l09-timer-prog" id="l09TimerCircle" cx="25" cy="25" r="22"></circle>
      </svg>
      <div class="l09-timer-face" id="l09TimerText">20</div>
    </div>
  </div>

  <!-- Cyberpunk Rainy Neon City Stage & 0-9 Wheel -->
  <div class="l09-casino-stream">
    <div class="l09-round-id" id="l09RoundId">RID: 8191011114908</div>

    <div class="l09-final-result-div" id="l09FinalResultGif">
      <img src="/assets/lucky0to9/luckyresult.gif" alt="Winner">
    </div>

    <div class="l09-wheel-wrap">
      <canvas id="luckyWheelCanvas" width="400" height="400"></canvas>
    </div>
  </div>

  <!-- Betting Grid (0-9 + EVEN/RED/BLACK/ODD) -->
  <div class="l09-lucky-footer">
    <section id="betZone" class="l09-bet-zone">
      <!-- Row 1: 0, 1, 2, 3, 4 -->
      <div name="betZoneBac" class="l09-down-set">
        <div class="l09-box-player 0 greenodd no_win" id="l09Box_0" onclick="placeLucky0To9Bet('0', '0', 'WINNER', 9.5)">
          <h2>0</h2>
          <span class="l09-pl-lucky" id="l09Pl_0"><span class="plus-book">0.00</span></span>
        </div>
        <div class="l09-box-player 1 redodd no_win" id="l09Box_1" onclick="placeLucky0To9Bet('1', '1', 'WINNER', 9.5)">
          <h2>1</h2>
          <span class="l09-pl-lucky" id="l09Pl_1"><span class="plus-book">0.00</span></span>
        </div>
        <div class="l09-box-player 2 blackodd no_win" id="l09Box_2" onclick="placeLucky0To9Bet('2', '2', 'WINNER', 9.5)">
          <h2>2</h2>
          <span class="l09-pl-lucky" id="l09Pl_2"><span class="plus-book">0.00</span></span>
        </div>
        <div class="l09-box-player 3 redodd no_win" id="l09Box_3" onclick="placeLucky0To9Bet('3', '3', 'WINNER', 9.5)">
          <h2>3</h2>
          <span class="l09-pl-lucky" id="l09Pl_3"><span class="plus-book">0.00</span></span>
        </div>
        <div class="l09-box-player 4 blackodd no_win" id="l09Box_4" onclick="placeLucky0To9Bet('4', '4', 'WINNER', 9.5)">
          <h2>4</h2>
          <span class="l09-pl-lucky" id="l09Pl_4"><span class="plus-book">0.00</span></span>
        </div>
      </div>

      <!-- Row 2: 5, 6, 7, 8, 9 -->
      <div name="betZoneBac" class="l09-down-set">
        <div class="l09-box-player 5 redodd no_win" id="l09Box_5" onclick="placeLucky0To9Bet('5', '5', 'WINNER', 9.5)">
          <h2>5</h2>
          <span class="l09-pl-lucky" id="l09Pl_5"><span class="plus-book">0.00</span></span>
        </div>
        <div class="l09-box-player 6 blackodd no_win" id="l09Box_6" onclick="placeLucky0To9Bet('6', '6', 'WINNER', 9.5)">
          <h2>6</h2>
          <span class="l09-pl-lucky" id="l09Pl_6"><span class="plus-book">0.00</span></span>
        </div>
        <div class="l09-box-player 7 redodd no_win" id="l09Box_7" onclick="placeLucky0To9Bet('7', '7', 'WINNER', 9.5)">
          <h2>7</h2>
          <span class="l09-pl-lucky" id="l09Pl_7"><span class="plus-book">0.00</span></span>
        </div>
        <div class="l09-box-player 8 blackodd no_win" id="l09Box_8" onclick="placeLucky0To9Bet('8', '8', 'WINNER', 9.5)">
          <h2>8</h2>
          <span class="l09-pl-lucky" id="l09Pl_8"><span class="plus-book">0.00</span></span>
        </div>
        <div class="l09-box-player 9 redodd no_win" id="l09Box_9" onclick="placeLucky0To9Bet('9', '9', 'WINNER', 9.5)">
          <h2>9</h2>
          <span class="l09-pl-lucky" id="l09Pl_9"><span class="plus-book">0.00</span></span>
        </div>
      </div>

      <!-- Row 3: EVEN, RED, BLACK, ODD -->
      <div name="betZoneBac" class="l09-down-set">
        <div class="l09-box-player EVEN blackodd no_win" id="l09Box_EVEN" onclick="placeLucky0To9Bet('EVEN', 'EVEN', 'ODD/EVEN', 1.98)">
          <h2>EVEN</h2>
          <span class="l09-pl-lucky" id="l09Pl_EVEN"><span class="plus-book">0.00</span></span>
        </div>
        <div class="l09-box-player RED redodd no_win" id="l09Box_RED" onclick="placeLucky0To9Bet('RED', 'RED', 'COLOR', 1.98)">
          <h2>RED</h2>
          <span class="l09-pl-lucky" id="l09Pl_RED"><span class="plus-book">0.00</span></span>
        </div>
        <div class="l09-box-player BLACK blackodd no_win" id="l09Box_BLACK" onclick="placeLucky0To9Bet('BLACK', 'BLACK', 'COLOR', 2.40)">
          <h2>BLACK</h2>
          <span class="l09-pl-lucky" id="l09Pl_BLACK"><span class="plus-book">0.00</span></span>
        </div>
        <div class="l09-box-player ODD redodd no_win" id="l09Box_ODD" onclick="placeLucky0To9Bet('ODD', 'ODD', 'ODD/EVEN', 1.98)">
          <h2>ODD</h2>
          <span class="l09-pl-lucky" id="l09Pl_ODD"><span class="plus-book">0.00</span></span>
        </div>
      </div>
    </section>
  </div>

  <!-- Poker Chips Selector Row -->
  <section id="functionZone" class="l09-function-zone virtual-chip mt-2">
    <ul id="chips" class="l09-chips">
      <li class="chips_1 now" data-stake="100" onclick="selectL09Chip(this, 100)">
        <img src="/assets/lucky0to9/chips1.svg" alt="100">
        <p>100</p>
        <span class="ring">ring</span>
      </li>
      <li class="chips_1" data-stake="150" onclick="selectL09Chip(this, 150)">
        <img src="/assets/lucky0to9/chips1.svg" alt="150">
        <p>150</p>
        <span class="ring">ring</span>
      </li>
      <li class="chips_1" data-stake="200" onclick="selectL09Chip(this, 200)">
        <img src="/assets/lucky0to9/chips1.svg" alt="200">
        <p>200</p>
        <span class="ring">ring</span>
      </li>
      <li class="chips_1" data-stake="250" onclick="selectL09Chip(this, 250)">
        <img src="/assets/lucky0to9/chips1.svg" alt="250">
        <p>250</p>
        <span class="ring">ring</span>
      </li>
      <li class="chips_1" data-stake="300" onclick="selectL09Chip(this, 300)">
        <img src="/assets/lucky0to9/chips1.svg" alt="300">
        <p>300</p>
        <span class="ring">ring</span>
      </li>
      <li class="chips_1" data-stake="500" onclick="selectL09Chip(this, 500)">
        <img src="/assets/lucky0to9/chips1.svg" alt="500">
        <p>500</p>
        <span class="ring">ring</span>
      </li>
      <li class="chips_1" data-stake="700" onclick="selectL09Chip(this, 700)">
        <img src="/assets/lucky0to9/chips1.svg" alt="700">
        <p>700</p>
        <span class="ring">ring</span>
      </li>
      <li class="chips_1" data-stake="1000" onclick="selectL09Chip(this, 1000)">
        <img src="/assets/lucky0to9/chips1k.svg" alt="1k">
        <p>1k</p>
        <span class="ring">ring</span>
      </li>
    </ul>
  </section>

  <!-- Recent Result Bar -->
  <div class="l09-recent-res teenpatti lucky0to9">
    <h5>Recent Result</h5>
    <ul class="result" id="l09RecentResults">
      <li class="virtual blackNumber" onclick="openL09ResultModal('8191011114833', 8)"><a class="number">8</a></li>
      <li class="virtual greenNumber" onclick="openL09ResultModal('8191011114758', 0)"><a class="number">0</a></li>
      <li class="virtual redNumber" onclick="openL09ResultModal('8191011114723', 5)"><a class="number">5</a></li>
      <li class="virtual blackNumber" onclick="openL09ResultModal('8191011114648', 2)"><a class="number">2</a></li>
      <li class="virtual blackNumber" onclick="openL09ResultModal('8191011114613', 6)"><a class="number">6</a></li>
      <li class="virtual blackNumber" onclick="openL09ResultModal('8191011114538', 2)"><a class="number">2</a></li>
      <li class="virtual greenNumber" onclick="openL09ResultModal('8191011114503', 0)"><a class="number">0</a></li>
      <li class="virtual redNumber" onclick="openL09ResultModal('8191011114428', 9)"><a class="number">9</a></li>
      <li class="virtual blackNumber" onclick="openL09ResultModal('8191011114353', 2)"><a class="number">2</a></li>
      <li class="virtual blackNumber" onclick="openL09ResultModal('8191011114318', 8)"><a class="number">8</a></li>
    </ul>
  </div>
</div>

<!-- Quick Stakes Modal -->
<div class="l09-modal-overlay" id="l09StakesModal">
  <div class="l09-modal-card">
    <div class="l09-modal-header">
      <span>Stake</span>
      <button type="button" onclick="closeL09StakesModal()">×</button>
    </div>
    <div class="l09-modal-body">
      <div style="font-size:14px;font-weight:700;margin-bottom:8px;">Quick Stakes</div>
      <div class="l09-stakes-grid" id="l09StakesInputs">
        <input type="number" value="100" min="10" max="999999">
        <input type="number" value="150" min="10" max="999999">
        <input type="number" value="200" min="10" max="999999">
        <input type="number" value="250" min="10" max="999999">
        <input type="number" value="300" min="10" max="999999">
        <input type="number" value="500" min="10" max="999999">
        <input type="number" value="700" min="10" max="999999">
        <input type="number" value="1000" min="10" max="999999">
      </div>
      <button type="button" class="l09-save-btn" onclick="saveL09Stakes()">Save</button>
    </div>
  </div>
</div>

<!-- Result Detail Modal -->
<div class="l09-modal-overlay" id="l09ResultModal">
  <div class="l09-modal-card">
    <div class="l09-modal-header">
      <span>LUCKY 0 TO 9 Result</span>
      <button type="button" onclick="closeL09ResultModal()">×</button>
    </div>
    <div class="l09-modal-body">
      <div style="text-align:right;font-size:13px;font-weight:700;margin-bottom:12px;">
        Round Id: <span id="l09ModalRoundId">8191011114833</span>
      </div>
      <div style="text-align:center;margin-bottom:14px;">
        <div id="l09ModalWinnerBadge" style="display:inline-flex;width:64px;height:64px;border-radius:50%;align-items:center;justify-content:center;font-size:28px;font-weight:900;color:#fff;background:#000;border:3px solid #fd8f3b;">8</div>
      </div>
      <div style="border-top:1px solid #ccc;padding-top:10px;font-size:13px;">
        <div style="display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px solid #eee;">
          <strong>WINNER</strong>
          <span id="l09ModalWinnerNum" style="font-weight:700;color:#198754;">8</span>
        </div>
        <div style="display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px solid #eee;">
          <strong>ODD / EVEN</strong>
          <span id="l09ModalOddEven" style="font-weight:700;">EVEN</span>
        </div>
        <div style="display:flex;justify-content:space-between;padding:4px 0;">
          <strong>COLOR</strong>
          <span id="l09ModalColor" style="font-weight:700;">BLACK</span>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="l09-toast" id="l09Toast"></div>

@push('scripts')
<script>
  const L09_CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';
  const L09_IS_AUTH = {{ auth()->check() ? 'true' : 'false' }};

  // Exact 10 segments from Winbook24 Lucky 0 to 9 seed
  const L09_SEGMENTS = [
    { fillStyle: '#105d24', value: 0, text: '0' },
    { fillStyle: '#FF0000', value: 1, text: '1' },
    { fillStyle: '#000000', value: 2, text: '2' },
    { fillStyle: '#FF0000', value: 3, text: '3' },
    { fillStyle: '#000000', value: 4, text: '4' },
    { fillStyle: '#FF0000', value: 5, text: '5' },
    { fillStyle: '#000000', value: 6, text: '6' },
    { fillStyle: '#FF0000', value: 7, text: '7' },
    { fillStyle: '#000000', value: 8, text: '8' },
    { fillStyle: '#FF0000', value: 9, text: '9' }
  ];

  let l09WheelRotation = 0; // degrees (0 = segment 0 at top pointer)
  let l09SelectedStake = 100;
  let l09RoundCounter = 8191011114908;
  let l09TimeLeft = 20;
  let l09Phase = 'BETTING'; // 'BETTING' | 'SPINNING'
  let l09BetInProcess = false;
  let l09StakesMap = {};

  function drawLucky0To9Wheel(rotationDeg = 0) {
    const canvas = document.getElementById('luckyWheelCanvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const width = canvas.width;
    const height = canvas.height;
    const centerX = width / 2;       // 200
    const centerY = height / 2 + 20; // 220 (matches Winwheel centerY: height/2 + 20 so hub aligns with wheelbg.png)
    const innerRadius = 48;
    const outerRadius = height / 2 - 20; // 180
    const numSegments = L09_SEGMENTS.length;
    const arcRad = (2 * Math.PI) / numSegments; // 36 deg per slice

    ctx.clearRect(0, 0, width, height);

    const rotRad = (rotationDeg * Math.PI) / 180;

    for (let i = 0; i < numSegments; i++) {
      const seg = L09_SEGMENTS[i];
      // Segment 0 starts at -90 deg (top)
      const startAngle = -Math.PI / 2 + i * arcRad + rotRad;
      const endAngle = startAngle + arcRad;

      ctx.save();
      ctx.beginPath();
      ctx.arc(centerX, centerY, outerRadius, startAngle, endAngle, false);
      ctx.arc(centerX, centerY, innerRadius, endAngle, startAngle, true);
      ctx.closePath();
      ctx.fillStyle = seg.fillStyle;
      ctx.fill();
      ctx.lineWidth = 1.5;
      ctx.strokeStyle = '#000000';
      ctx.stroke();

      // Draw outer-aligned vertical text (matches Winwheel textOrientation: VERTICAL, textAlignment: OUTER, textFontSize: 50)
      const midAngle = startAngle + arcRad / 2;
      ctx.translate(centerX, centerY);
      ctx.rotate(midAngle + Math.PI / 2);
      ctx.fillStyle = '#ffffff';
      ctx.font = 'bold 44px Arial, sans-serif';
      ctx.textAlign = 'center';
      ctx.textBaseline = 'middle';
      ctx.fillText(seg.text, 0, -(outerRadius - 36));
      ctx.restore();
    }

    // Draw Gold Top Pointer Triangle (matches drawPointer() in universe-wheel)
    ctx.save();
    ctx.lineWidth = 2;
    ctx.strokeStyle = 'black';
    ctx.fillStyle = 'gold';
    ctx.beginPath();
    ctx.moveTo(width / 2 - 20, 2);
    ctx.lineTo(width / 2 + 20, 2);
    ctx.lineTo(width / 2, 42);
    ctx.lineTo(width / 2 - 20, 2);
    ctx.stroke();
    ctx.fill();
    ctx.restore();
  }

  function spinWheelToNumber(winnerNum, durationMs = 4500, onDone) {
    const segAngle = 360 / L09_SEGMENTS.length; // 36 deg
    // To put center of segment `winnerNum` at top (-90 deg), target rotation modulo 360 is:
    // -(winnerNum * 36 + 18) mod 360
    const targetMod = (360 - ((winnerNum * segAngle + segAngle / 2) % 360)) % 360;
    const currentMod = ((l09WheelRotation % 360) + 360) % 360;
    let delta = targetMod - currentMod;
    if (delta < 0) delta += 360;

    const totalRotation = 360 * 8 + delta; // 8 full spins + exact landing angle
    const startRotation = l09WheelRotation;
    const startTime = performance.now();

    function animateFrame(now) {
      const elapsed = now - startTime;
      const t = Math.min(1, elapsed / durationMs);
      // Cubic ease-out
      const easeOut = 1 - Math.pow(1 - t, 3);
      l09WheelRotation = startRotation + totalRotation * easeOut;
      drawLucky0To9Wheel(l09WheelRotation);

      if (t < 1) {
        requestAnimationFrame(animateFrame);
      } else {
        if (typeof onDone === 'function') onDone();
      }
    }

    requestAnimationFrame(animateFrame);
  }

  function showL09Toast(msg, type = 'info') {
    const el = document.getElementById('l09Toast');
    if (!el) return;
    el.textContent = msg;
    el.className = 'l09-toast ' + type;
    el.style.display = 'block';
    clearTimeout(window._l09ToastTimer);
    window._l09ToastTimer = setTimeout(() => {
      el.style.display = 'none';
    }, 2800);
  }

  function selectL09Chip(liEl, amount) {
    document.querySelectorAll('#chips li').forEach(el => el.classList.remove('now'));
    if (l09SelectedStake === amount) {
      l09SelectedStake = 0;
    } else {
      l09SelectedStake = amount;
      liEl.classList.add('now');
    }
  }

  function setL09Suspended(isSuspended) {
    const boxes = document.querySelectorAll('.l09-box-player');
    const timerOverlay = document.getElementById('l09TimerOverlay');
    boxes.forEach(b => {
      b.classList.toggle('suspended', isSuspended);
      if (isSuspended) b.classList.remove('winner-flash');
    });
    if (timerOverlay) {
      timerOverlay.style.display = isSuspended ? 'none' : 'flex';
    }
  }

  async function placeLucky0To9Bet(selectionKey, selectionName, marketName, odds) {
    if (l09Phase !== 'BETTING' || l09BetInProcess) return;

    if (!l09SelectedStake || l09SelectedStake <= 0) {
      showL09Toast('please select chips for Bet', 'error');
      return;
    }

    if (!L09_IS_AUTH) {
      showL09Toast('Please login (or click Login with Demo ID) to place bets.', 'error');
      return;
    }

    l09BetInProcess = true;
    try {
      const res = await fetch('/api/casino/teenpatti/place-bet', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': L09_CSRF
        },
        body: JSON.stringify({
          event_id: '88.0019',
          round_id: String(l09RoundCounter),
          market_key: marketName,
          market_name: marketName,
          selection_key: selectionKey,
          selection_name: selectionName,
          odds: odds,
          stake: l09SelectedStake
        })
      });
      const data = await res.json();
      if (data.status) {
        l09StakesMap[selectionKey] = (l09StakesMap[selectionKey] || 0) + l09SelectedStake;
        const plEl = document.getElementById('l09Pl_' + selectionKey);
        if (plEl) {
          const totalProfit = (l09StakesMap[selectionKey] * (odds - 1)).toFixed(2);
          plEl.innerHTML = `<span class="plus-book">+${totalProfit}</span>`;
          plEl.classList.add('active');
        }
        if (data.wallet) {
          updateHeaderWallet(data.wallet);
        }
        appendOpenBetRow(selectionName, odds, l09SelectedStake);
        showL09Toast(`Bet Placed: ${selectionName} @ ${odds} (${l09SelectedStake} PTI)`, 'success');
      } else {
        showL09Toast(data.message || 'Could not place bet', 'error');
      }
    } catch (e) {
      showL09Toast('Network error placing bet', 'error');
    } finally {
      l09BetInProcess = false;
    }
  }

  function updateHeaderWallet(wallet) {
    if (!wallet) return;
    if (window.WB24 && typeof WB24.updateWalletDisplay === 'function') {
      WB24.updateWalletDisplay(wallet);
    }
    document.querySelectorAll('.js-user-balance, .userTotalBalance, #userBalanceVal').forEach(el => {
      if (wallet.available !== undefined) el.textContent = Number(wallet.available).toFixed(2);
    });
    document.querySelectorAll('.js-user-exposure, .userTotalExposure, #userExposureVal').forEach(el => {
      if (wallet.exposure !== undefined) el.textContent = Number(wallet.exposure).toFixed(2);
    });
  }

  function appendOpenBetRow(selectionName, odds, stake) {
    const container = document.getElementById('openBetsListContainer');
    if (!container) return;
    const row = document.createElement('div');
    row.style.cssText = 'background:#beddf4;border-bottom:1px solid #67afe5;padding:4px 6px;font-size:11px;display:flex;justify-content:space-between;align-items:center;';
    row.innerHTML = `<strong>LUCKY 0 TO 9 - ${selectionName}</strong><span>${odds} | ${stake} PTI</span>`;
    container.prepend(row);
  }

  async function triggerL09SpinAndSettle() {
    l09Phase = 'SPINNING';
    setL09Suspended(true);
    document.getElementById('l09FinalResultGif')?.classList.remove('active');

    let settleData = null;
    try {
      const res = await fetch('/api/casino/lucky0to9/settle-round', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': L09_CSRF
        },
        body: JSON.stringify({
          event_id: '88.0019',
          round_id: String(l09RoundCounter)
        })
      });
      settleData = await res.json();
    } catch (e) {
      const fallbackNum = Math.floor(Math.random() * 10);
      settleData = {
        status: true,
        round_id: String(l09RoundCounter),
        winner_num: fallbackNum,
        winner: String(fallbackNum),
        color_class: fallbackNum === 0 ? 'greenNumber' : (fallbackNum % 2 === 1 ? 'redNumber' : 'blackNumber'),
        summary: `WINNER: ${fallbackNum}`
      };
    }

    const winnerNum = settleData?.winner_num ?? 0;

    spinWheelToNumber(winnerNum, 4500, () => {
      // Show celebration GIF overlay
      document.getElementById('l09FinalResultGif')?.classList.add('active');

      // Highlight winning box
      const winBox = document.getElementById('l09Box_' + winnerNum);
      if (winBox) winBox.classList.add('winner-flash');

      // Prepend badge to Recent Result bar
      const ul = document.getElementById('l09RecentResults');
      if (ul) {
        const li = document.createElement('li');
        li.className = 'virtual ' + (settleData.color_class || 'greenNumber');
        const rId = String(l09RoundCounter);
        li.onclick = () => openL09ResultModal(rId, winnerNum);
        li.innerHTML = `<a class="number">${winnerNum}</a>`;
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
          showL09Toast(`Round #${l09RoundCounter}: ${settleData.summary} | Won +${net.toFixed(2)} PTI!`, 'success');
        } else {
          showL09Toast(`Round #${l09RoundCounter}: ${settleData.summary} | Net ${net.toFixed(2)} PTI`, 'info');
        }
      }

      setTimeout(() => {
        startNewL09Round();
      }, 2500);
    });
  }

  function startNewL09Round() {
    l09RoundCounter += 35;
    l09TimeLeft = 20;
    l09Phase = 'BETTING';
    l09StakesMap = {};
    document.getElementById('l09RoundId').textContent = 'RID: ' + l09RoundCounter;
    document.getElementById('l09FinalResultGif')?.classList.remove('active');
    document.querySelectorAll('.l09-pl-lucky').forEach(el => {
      el.classList.remove('active');
      el.innerHTML = '<span class="plus-book">0.00</span>';
    });
    setL09Suspended(false);
    updateL09TimerUI();
  }

  function updateL09TimerUI() {
    const txtEl = document.getElementById('l09TimerText');
    const cirEl = document.getElementById('l09TimerCircle');
    if (!txtEl || !cirEl) return;

    txtEl.textContent = l09TimeLeft;
    const circumference = 138.2;
    const offset = circumference - (l09TimeLeft / 20) * circumference;
    cirEl.style.strokeDashoffset = Math.max(0, offset);

    const isLow = l09TimeLeft < 6;
    txtEl.classList.toggle('red-alert', isLow);
    cirEl.classList.toggle('red-alert', isLow);
  }

  function openL09StakesModal() {
    document.getElementById('l09StakesModal')?.classList.add('active');
  }
  function closeL09StakesModal() {
    document.getElementById('l09StakesModal')?.classList.remove('active');
  }
  function saveL09Stakes() {
    const inputs = document.querySelectorAll('#l09StakesInputs input');
    const chipItems = document.querySelectorAll('#chips li');
    inputs.forEach((inp, idx) => {
      const val = Math.max(10, parseInt(inp.value, 10) || 100);
      if (chipItems[idx]) {
        chipItems[idx].dataset.stake = val;
        chipItems[idx].setAttribute('onclick', `selectL09Chip(this, ${val})`);
        const p = chipItems[idx].querySelector('p');
        const img = chipItems[idx].querySelector('img');
        if (p) p.textContent = val >= 1000 ? (val / 1000) + 'k' : val;
        if (img) img.src = val >= 1000 ? '/assets/lucky0to9/chips1k.svg' : '/assets/lucky0to9/chips1.svg';
      }
    });
    closeL09StakesModal();
    showL09Toast('Quick Stakes updated!', 'success');
  }

  function openL09ResultModal(roundId, num) {
    document.getElementById('l09ModalRoundId').textContent = roundId;
    const badge = document.getElementById('l09ModalWinnerBadge');
    if (badge) {
      badge.textContent = num;
      badge.style.background = num === 0 ? '#105d24' : (num % 2 === 1 ? '#e1282a' : '#000000');
    }
    document.getElementById('l09ModalWinnerNum').textContent = num;
    document.getElementById('l09ModalOddEven').textContent = (num % 2 === 0) ? 'EVEN' : 'ODD';
    document.getElementById('l09ModalColor').textContent = num === 0 ? 'GREEN (0)' : (num % 2 === 1 ? 'RED' : 'BLACK');
    document.getElementById('l09ResultModal')?.classList.add('active');
  }
  function closeL09ResultModal() {
    document.getElementById('l09ResultModal')?.classList.remove('active');
  }

  document.addEventListener('DOMContentLoaded', () => {
    drawLucky0To9Wheel(0);
    updateL09TimerUI();

    setInterval(() => {
      if (l09Phase !== 'BETTING') return;
      l09TimeLeft--;
      if (l09TimeLeft <= 0) {
        l09TimeLeft = 0;
        updateL09TimerUI();
        triggerL09SpinAndSettle();
      } else {
        updateL09TimerUI();
      }
    }, 1000);
  });
</script>
@endpush
@endsection
