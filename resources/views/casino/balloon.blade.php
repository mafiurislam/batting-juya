@extends('layouts.app')
@section('title', 'winbook24 - BALLOON (88.0023)')

@php
    $hideSidebars = true;
@endphp

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet">
<style>
    body {
        background-color: #0b0b0b !important;
        overflow-x: hidden;
    }

    /* Full-width dark wrapper matching https://casino.winbook24.com/balloon/88.0023 */
    .wb-balloon-outer {
        background: #0b0b0b;
        min-height: calc(100vh - 76px);
        width: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 16px 8px;
        user-select: none;
        font-family: 'Roboto', 'Inter', sans-serif;
    }

    /* 950x520 Rounded Stage matching exact iframe dimensions */
    .wb-balloon-stage {
        position: relative;
        width: 950px;
        max-width: 100%;
        height: 520px;
        border-radius: 12px;
        overflow: hidden;
        background: linear-gradient(180deg, #63a7bd 0%, #9bc2c3 52%, #dcd6b4 100%);
        box-shadow: 0 12px 42px rgba(0, 0, 0, 0.85);
    }

    #balloonCanvas {
        display: block;
        width: 100%;
        height: 100%;
        border-radius: 12px;
    }

    /* Top Bar: Speaker + Multiplier History Pills + Dropdown Arrow */
    .bln-top-bar {
        position: absolute;
        top: 12px;
        left: 0;
        right: 0;
        z-index: 40;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 14px;
        pointer-events: none;
    }
    .bln-speaker-btn {
        pointer-events: auto;
        background: transparent;
        border: 0;
        padding: 2px;
        cursor: pointer;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        filter: drop-shadow(0 1px 2px rgba(0,0,0,0.45));
    }
    .bln-history-strip {
        pointer-events: auto;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        max-height: 25px;
        overflow: hidden;
        flex-wrap: nowrap;
    }
    .bln-mult-pill {
        height: 25px;
        min-width: 50px;
        border-radius: 5px;
        padding: 4px 8px 2px;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.4;
        text-align: center;
        cursor: pointer;
        transition: transform 0.12s;
        box-shadow: 0 1px 3px rgba(0,0,0,0.25);
    }
    .bln-mult-pill:hover {
        transform: scale(1.05);
    }
    .bln-mult-pill.blue {
        background-color: #05378f;
        color: #ffffff;
    }
    .bln-mult-pill.yellow {
        background-color: #ff8a00;
        color: #000000;
    }
    .bln-history-toggle {
        pointer-events: auto;
        width: 25px;
        height: 25px;
        border-radius: 50%;
        border: 0;
        background: linear-gradient(270deg, #f48808, #ffd098) !important;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 2px 5px rgba(0,0,0,0.3);
    }
    .bln-history-toggle img {
        width: 16px;
        transition: transform 0.25s ease;
    }
    .bln-history-toggle.open img {
        transform: rotate(180deg);
    }

    /* Expanded History Dropdown Panel */
    .bln-history-dropdown {
        position: absolute;
        top: 44px;
        left: 50%;
        transform: translateX(-50%);
        width: 560px;
        max-width: 92%;
        background: rgba(15, 23, 42, 0.94);
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 10px;
        padding: 10px 12px;
        z-index: 55;
        display: none;
        box-shadow: 0 10px 25px rgba(0,0,0,0.65);
    }
    .bln-history-dropdown.show {
        display: block;
    }

    /* Left Profile / Active Bets Toggle Pill */
    .bln-profile-pill {
        position: absolute;
        left: 4px;
        top: 94px;
        z-index: 35;
        background: #05276433 !important;
        border-radius: 0 20px 20px 0;
        height: 28px;
        padding-left: 12px;
        display: flex;
        align-items: center;
        gap: 12px;
        cursor: pointer;
    }
    .bln-profile-pill img.prof-icon {
        width: 16px;
    }
    .bln-profile-count {
        font-size: 13px;
        font-weight: 700;
        color: #052764;
        min-width: 10px;
    }
    .bln-profile-arrow {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        border: 0;
        background: linear-gradient(270deg, #052764, #5190ff) !important;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
    }
    .bln-profile-arrow img {
        width: 18px;
        transform: rotate(180deg);
        transition: transform 0.25s ease;
    }
    .bln-profile-pill.collapsed .bln-profile-arrow img {
        transform: rotate(0deg);
    }

    /* Left Live Players Bets List */
    .bln-players-list {
        position: absolute;
        left: 6px;
        top: 128px;
        z-index: 34;
        width: 215px;
        max-height: 225px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 5px;
        scrollbar-width: none;
    }
    .bln-players-list::-webkit-scrollbar {
        display: none;
    }
    .bln-player-row {
        background: rgba(15, 23, 42, 0.72);
        border: 1px solid rgba(255,255,255,0.18);
        border-radius: 14px;
        padding: 3px 9px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 11px;
        color: #fff;
        backdrop-filter: blur(3px);
    }
    .bln-player-row.cashed {
        background: #123405 !important;
        border-color: #427f00 !important;
    }
    .bln-player-mult {
        background: #ff8a00;
        color: #000;
        font-weight: 700;
        border-radius: 4px;
        padding: 0 4px;
        font-size: 10px;
    }

    /* Center Balloon Overlay (Multiplier + Waiting For Next Round + Countdown Timer) */
    .bln-center-overlay {
        position: absolute;
        left: 50%;
        top: 41%;
        transform: translate(-50%, -50%);
        z-index: 25;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        pointer-events: none;
        text-align: center;
        width: 280px;
    }
    .bln-waiting-text {
        font-size: 20px;
        font-weight: 700;
        color: #ffffff;
        text-shadow: 0 1px 4px rgba(0,0,0,0.45);
        margin-bottom: 8px;
        white-space: nowrap;
    }
    .bln-timer-circle {
        position: relative;
        width: 58px;
        height: 58px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .bln-timer-circle svg {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
    }
    .bln-timer-num {
        position: relative;
        z-index: 2;
        font-size: 26px;
        font-weight: 700;
        color: #ffffff;
    }
    .bln-live-multiplier {
        font-size: 44px;
        font-weight: 800;
        color: #ffffff;
        line-height: 1;
        text-shadow: 0 2px 8px rgba(0,0,0,0.35);
        letter-spacing: -0.5px;
    }
    .bln-live-multiplier.burst {
        color: #ffeb3b;
        transform: scale(1.12);
        transition: transform 0.2s ease;
    }

    /* Bottom Frosted Control Footer (.game-footer) */
    .bln-footer-wrapper {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 38;
        padding: 0 2px 2px;
    }
    .bln-game-footer {
        background-color: #b8b8b833;
        border-top-left-radius: 20px;
        border-top-right-radius: 20px;
        padding: 8px 12px 10px 14px;
        font-family: 'Inter', sans-serif;
    }

    /* Auto Switch Row */
    .bln-auto-row {
        display: flex;
        align-items: center;
        gap: 18px;
        margin-bottom: 6px;
        padding-left: 8px;
    }
    .bln-auto-group {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .bln-auto-label {
        font-size: 14px;
        font-weight: 600;
        color: #ffffff;
        line-height: 16.94px;
    }
    .bln-switch {
        position: relative;
        display: inline-flex;
        align-items: center;
        cursor: pointer;
        margin: 0;
    }
    .bln-switch input {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }
    .bln-switch-slider {
        width: 44px;
        height: 19px;
        background: rgba(27, 28, 29, 0.35);
        border: 1px solid rgba(255, 255, 255, 0.6);
        border-radius: 999px;
        position: relative;
        transition: all 0.2s ease;
    }
    .bln-switch-slider::after {
        content: '';
        position: absolute;
        top: 1px;
        left: 3px;
        width: 15px;
        height: 15px;
        background: #ffffff;
        border-radius: 50%;
        transition: transform 0.2s ease;
    }
    .bln-switch input:checked + .bln-switch-slider {
        background: #008826;
        border-color: #008826;
    }
    .bln-switch input:checked + .bln-switch-slider::after {
        transform: translateX(22px);
    }
    .bln-auto-mult-wrap {
        position: relative;
        width: 68px;
        height: 24px;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.15s ease;
    }
    .bln-auto-mult-wrap.visible {
        opacity: 1;
        pointer-events: auto;
    }
    .bln-auto-mult-input {
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.75);
        color: #fff;
        border: 1px solid rgba(255,255,255,0.35);
        border-radius: 10px;
        padding: 2px 18px 2px 8px;
        font-size: 12px;
        font-weight: 700;
        outline: none;
    }
    .bln-auto-mult-x {
        position: absolute;
        right: 6px;
        top: 50%;
        transform: translateY(-50%);
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        pointer-events: none;
    }

    /* Controls Grid: 8 Stake Pills + 4 Control Buttons + 2 HEAT Buttons */
    .bln-controls-grid {
        display: grid;
        grid-template-columns: 270px 130px 1fr;
        align-items: center;
        gap: 14px;
    }
    .bln-stakes-grid {
        display: grid;
        grid-template-columns: repeat(2, 120px);
        column-gap: 24px;
        row-gap: 5px;
    }
    .bln-bet-opt {
        height: 30px;
        width: 120px;
        border-radius: 100px;
        border: 2px solid #ffffff;
        background-color: #052764;
        color: #ffffff;
        font-size: 16px;
        font-weight: 600;
        line-height: 1;
        padding: 0;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.1s ease, border-color 0.15s ease;
    }
    .bln-bet-opt:hover {
        transform: translateY(-1px);
    }
    .bln-bet-opt.selectedBtn {
        border: 2px solid #ff1a1a !important;
        font-weight: 700 !important;
        box-shadow: 0 0 8px rgba(255, 26, 26, 0.55);
    }
    .bln-bet-opt input.bln-edit-stake-input {
        width: 90%;
        background: transparent;
        border: 0;
        color: #ffeb3b;
        font-size: 15px;
        font-weight: 700;
        text-align: center;
        outline: none;
    }

    /* Middle Column: Edits, Clear, Min, Max */
    .bln-mid-controls {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 5px;
    }
    .bln-control-btn {
        width: 116px;
        height: 24px;
        border-radius: 10px;
        border: 2px solid #ffffff;
        font-size: 14px;
        font-weight: 500;
        line-height: 16.94px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
    }
    .bln-edit-btn {
        background: linear-gradient(360deg, #cf7204, #f48808) !important;
        color: #eeeff4 !important;
    }
    .bln-clear-btn {
        background: linear-gradient(180deg, red, #a10000) !important;
        color: #eeeff4 !important;
    }
    .bln-minmax-btn {
        background: #210430 !important;
        color: #9e9e9e !important;
    }
    .bln-minmax-btn:hover {
        color: #ffffff !important;
    }

    /* Right Column: 2 Stacked HEAT Buttons */
    .bln-heat-col {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        justify-content: center;
        gap: 6px;
    }
    .bln-heat-btn {
        width: 200px;
        height: 52px;
        border-radius: 10px;
        border: 2px solid #000000 !important;
        box-shadow: 0 0 15px #000 inset;
        background: linear-gradient(360deg, #008826, #00df3f);
        display: flex;
        align-items: center;
        justify-content: space-around;
        padding: 0 10px;
        cursor: pointer;
        transition: transform 0.1s ease, filter 0.15s ease;
    }
    .bln-heat-btn:hover {
        filter: brightness(1.06);
    }
    .bln-heat-btn:active {
        transform: scale(0.98);
    }
    .bln-heat-btn.cancel-state {
        background: linear-gradient(180deg, #ff1e1e, #b30000) !important;
    }
    .bln-heat-btn.take-state {
        background: linear-gradient(360deg, #cf7000 40.5%, #ff8a00) !important;
    }
    .bln-heat-btn img {
        width: 44px;
        height: 47px;
        object-fit: contain;
    }
    .bln-heat-label-wrap {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .bln-heat-text {
        font-family: 'Inter', sans-serif;
        font-size: 24px;
        font-weight: 600;
        line-height: 26px;
        color: #ffffff;
        letter-spacing: 0.011em;
    }
    .bln-heat-sub {
        font-size: 11px;
        font-weight: 700;
        color: #eaffea;
        line-height: 1.1;
    }

    /* Win Cashout Banner Toast inside Stage */
    .bln-win-toast {
        position: absolute;
        top: 52px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 60;
        background: linear-gradient(90deg, #123405, #28a909, #123405);
        border: 2px solid #b2f2a3;
        color: #fff;
        padding: 6px 20px;
        border-radius: 30px;
        font-size: 14px;
        font-weight: 800;
        box-shadow: 0 6px 20px rgba(0,0,0,0.6);
        display: none;
    }

    @media (max-width: 980px) {
        .wb-balloon-stage {
            height: 500px;
        }
        .bln-controls-grid {
            grid-template-columns: 1fr 90px 150px;
            gap: 8px;
        }
        .bln-stakes-grid {
            grid-template-columns: repeat(2, 1fr);
            column-gap: 8px;
        }
        .bln-bet-opt {
            width: 100%;
            font-size: 14px;
            height: 26px;
        }
        .bln-control-btn {
            width: 84px;
            font-size: 12px;
        }
        .bln-heat-btn {
            width: 145px;
            height: 48px;
        }
        .bln-heat-text {
            font-size: 19px;
        }
    }
</style>
@endpush

@section('full_bleed')
<div class="wb-balloon-outer">
    <div class="wb-balloon-stage" id="balloonStage">

        <!-- Top Bar: Speaker, 10 Recent Multipliers, Dropdown Toggle -->
        <div class="bln-top-bar">
            <button type="button" class="bln-speaker-btn" id="blnMuteBtn" title="Toggle Sound">
                <!-- Muted icon by default matching screenshot -->
                <svg id="blnSpeakerIcon" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M11 5L6 9H2V15H6L11 19V5Z" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path id="blnMuteX1" d="M16 8L23 15" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path id="blnMuteX2" d="M23 8L16 15" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </button>

            <div class="bln-history-strip" id="blnHistoryStrip">
                <!-- Populated dynamically with exact orange (>=2x) and navy (<2x) pills -->
            </div>

            <button type="button" class="bln-history-toggle" id="blnHistoryToggleBtn" title="Round History">
                <img src="/assets/balloon/ArrowDown.webp" alt="open">
            </button>
        </div>

        <!-- Expanded Round History Panel -->
        <div class="bln-history-dropdown" id="blnHistoryDropdown">
            <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom border-secondary">
                <span class="text-white fw-bold" style="font-size:12px;">ROUND HISTORY (BALLOON 88.0023)</span>
                <span class="text-warning" style="font-size:11px;cursor:pointer;" id="blnCloseHistoryBtn">Close ✕</span>
            </div>
            <div class="d-flex flex-wrap gap-1" id="blnHistoryExpandedList"></div>
        </div>

        <!-- Win Cashout Celebration Toast -->
        <div class="bln-win-toast" id="blnWinToast"></div>

        <!-- Left Collapsible Player List Toggle -->
        <div class="bln-profile-pill" id="blnProfilePill" title="Toggle Live Bets">
            <img src="/assets/balloon/Profile.webp" alt="profile" class="prof-icon">
            <span class="bln-profile-count d-none" id="blnActivePlayersCount">10</span>
            <button type="button" class="bln-profile-arrow">
                <img src="/assets/balloon/Arrow2.webp" alt="arrow">
            </button>
        </div>

        <!-- Left Live Players Bets List (Hidden by default matching screenshot, opens on click) -->
        <div class="bln-players-list d-none" id="blnPlayersList"></div>

        <!-- Center Overlay inside Balloon: Multiplier OR Waiting For Next Round + Circular Countdown -->
        <div class="bln-center-overlay" id="blnCenterOverlay">
            <div id="blnWaitingWrap" style="display:none;">
                <div class="bln-waiting-text">Waiting For Next Round</div>
                <div class="bln-timer-circle mx-auto">
                    <svg viewBox="0 0 64 64">
                        <circle fill="#0006" cx="32" cy="32" r="29" stroke="#61a503" stroke-width="5.8" stroke-linecap="round"
                                id="blnTimerArc" transform="rotate(-90 32 32)" stroke-dasharray="182 182" stroke-dashoffset="0"></circle>
                    </svg>
                    <span class="bln-timer-num" id="blnTimerNum">5</span>
                </div>
            </div>
            <div id="blnMultiplierWrap">
                <div class="bln-live-multiplier" id="blnLiveMultiplier">9.46x</div>
            </div>
        </div>

        <!-- Interactive Sky, Mountains, Clouds, Flame & Hot Air Balloon Canvas -->
        <canvas id="balloonCanvas" width="950" height="520"></canvas>

        <!-- Bottom Frosted Control Footer -->
        <div class="bln-footer-wrapper">
            <div class="bln-game-footer">
                <!-- Top Auto Cashout / Auto Bet Row -->
                <div class="bln-auto-row">
                    <div class="bln-auto-group">
                        <span class="bln-auto-label">Auto</span>
                        <label class="bln-switch">
                            <input type="checkbox" id="blnAutoSwitch1">
                            <span class="bln-switch-slider"></span>
                        </label>
                        <div class="bln-auto-mult-wrap" id="blnAutoMultWrap1">
                            <input type="text" inputmode="decimal" class="bln-auto-mult-input" id="blnAutoMult1" value="2.00" autocomplete="off">
                            <span class="bln-auto-mult-x">x</span>
                        </div>
                    </div>

                    <div class="bln-auto-group">
                        <span class="bln-auto-label">Auto</span>
                        <label class="bln-switch">
                            <input type="checkbox" id="blnAutoSwitch2">
                            <span class="bln-switch-slider"></span>
                        </label>
                        <div class="bln-auto-mult-wrap" id="blnAutoMultWrap2">
                            <input type="text" inputmode="decimal" class="bln-auto-mult-input" id="blnAutoMult2" value="2.00" autocomplete="off">
                            <span class="bln-auto-mult-x">x</span>
                        </div>
                    </div>
                </div>

                <!-- Main Controls Grid: 8 Stake Pills | Edits/Clear/Min/Max | 2 HEAT Buttons -->
                <div class="bln-controls-grid">
                    <!-- 8 Quick Stake Pills (10, 50, 100, 500, 1000, 2500, 5000, 10000) -->
                    <div class="bln-stakes-grid" id="blnStakesGrid">
                        <button type="button" class="bln-bet-opt" data-idx="0" data-stake="10"><span>10</span></button>
                        <button type="button" class="bln-bet-opt" data-idx="1" data-stake="50"><span>50</span></button>
                        <button type="button" class="bln-bet-opt" data-idx="2" data-stake="100"><span>100</span></button>
                        <button type="button" class="bln-bet-opt" data-idx="3" data-stake="500"><span>500</span></button>
                        <button type="button" class="bln-bet-opt" data-idx="4" data-stake="1000"><span>1000</span></button>
                        <button type="button" class="bln-bet-opt" data-idx="5" data-stake="2500"><span>2500</span></button>
                        <button type="button" class="bln-bet-opt" data-idx="6" data-stake="5000"><span>5000</span></button>
                        <button type="button" class="bln-bet-opt" data-idx="7" data-stake="10000"><span>10000</span></button>
                    </div>

                    <!-- Edits, Clear, Min, Max -->
                    <div class="bln-mid-controls">
                        <button type="button" class="bln-control-btn bln-edit-btn" id="blnEditBtn">Edits</button>
                        <button type="button" class="bln-control-btn bln-clear-btn" id="blnClearBtn">Clear</button>
                        <button type="button" class="bln-control-btn bln-minmax-btn" id="blnMinBtn">Min</button>
                        <button type="button" class="bln-control-btn bln-minmax-btn" id="blnMaxBtn">Max</button>
                    </div>

                    <!-- 2 Green HEAT Buttons -->
                    <div class="bln-heat-col">
                        <button type="button" class="bln-heat-btn" id="blnHeatBtn1" data-slot="1">
                            <img src="/assets/balloon/heat-icon.webp" alt="heat">
                            <div class="bln-heat-label-wrap">
                                <span class="bln-heat-text" id="blnHeatText1">HEAT</span>
                                <span class="bln-heat-sub" id="blnHeatSub1"></span>
                            </div>
                        </button>

                        <button type="button" class="bln-heat-btn" id="blnHeatBtn2" data-slot="2">
                            <img src="/assets/balloon/heat-icon.webp" alt="heat">
                            <div class="bln-heat-label-wrap">
                                <span class="bln-heat-text" id="blnHeatText2">HEAT</span>
                                <span class="bln-heat-sub" id="blnHeatSub2"></span>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const canvas = document.getElementById('balloonCanvas');
    const ctx = canvas.getContext('2d');

    // Exact initial multiplier history matching the user's screenshot
    const historyMultipliers = [2.46, 8.06, 1.16, 1.86, 1.10, 1.05, 4.78, 2.12, 3.41, 1.08, 13.65, 1.62, 1.54, 1.65, 1.04];

    // Load original Balloon game sprites from /assets/balloon/
    const sprites = {};
    const spriteFiles = {
        balloon: '/assets/balloon/assets/airballoon-red.png',
        fire: '/assets/balloon/assets/fire@3x.png',
        clouds: '/assets/balloon/assets/clouds.png',
        loc1: '/assets/balloon/assets/locationLt.png',
        loc2: '/assets/balloon/assets/locationLt2.png',
        loc3: '/assets/balloon/assets/locationLt3.png',
        plane: '/assets/balloon/assets/plane@3x.png',
        ufo: '/assets/balloon/assets/ufo.png',
        moon: '/assets/balloon/assets/moon.png',
        stars: '/assets/balloon/assets/stars@2x.png'
    };
    Object.keys(spriteFiles).forEach(k => {
        const img = new Image();
        img.src = spriteFiles[k];
        sprites[k] = img;
    });

    // Sound effects (muted by default, enabled when user clicks speaker icon)
    let isMuted = true;
    const sounds = {
        bg: new Audio('/assets/balloon/assets/sound/balloon_bg_music.mp3'),
        fly: new Audio('/assets/balloon/assets/sound/balloon_fly.mp3'),
        heat: new Audio('/assets/balloon/assets/sound/balloon_heat.mp3'),
        win: new Audio('/assets/balloon/assets/sound/win.mp3'),
        blast: new Audio('/assets/balloon/assets/sound/balloon_blast.mp3')
    };
    sounds.bg.loop = true;
    sounds.bg.volume = 0.35;

    function playSound(name) {
        if (isMuted || !sounds[name]) return;
        try {
            sounds[name].currentTime = 0;
            sounds[name].play().catch(() => {});
        } catch (e) {}
    }

    document.getElementById('blnMuteBtn').addEventListener('click', function () {
        isMuted = !isMuted;
        const x1 = document.getElementById('blnMuteX1');
        const x2 = document.getElementById('blnMuteX2');
        if (isMuted) {
            x1.style.display = '';
            x2.style.display = '';
            sounds.bg.pause();
        } else {
            x1.style.display = 'none';
            x2.style.display = 'none';
            sounds.bg.play().catch(() => {});
        }
    });

    // Render top multiplier pills
    function renderHistory() {
        const strip = document.getElementById('blnHistoryStrip');
        const expanded = document.getElementById('blnHistoryExpandedList');
        const top10 = historyMultipliers.slice(0, 10);

        strip.innerHTML = top10.map(m => {
            const cls = m >= 2.0 ? 'yellow' : 'blue';
            return `<div class="bln-mult-pill ${cls}">${m.toFixed(2)}x</div>`;
        }).join('');

        expanded.innerHTML = historyMultipliers.map(m => {
            const cls = m >= 2.0 ? 'yellow' : 'blue';
            return `<div class="bln-mult-pill ${cls}">${m.toFixed(2)}x</div>`;
        }).join('');
    }
    renderHistory();

    // History dropdown toggle
    const histToggleBtn = document.getElementById('blnHistoryToggleBtn');
    const histDropdown = document.getElementById('blnHistoryDropdown');
    histToggleBtn.addEventListener('click', function () {
        histToggleBtn.classList.toggle('open');
        histDropdown.classList.toggle('show');
    });
    document.getElementById('blnCloseHistoryBtn').addEventListener('click', function () {
        histToggleBtn.classList.remove('open');
        histDropdown.classList.remove('show');
    });

    // Left live players list toggle
    const profilePill = document.getElementById('blnProfilePill');
    const playersListEl = document.getElementById('blnPlayersList');
    profilePill.addEventListener('click', function () {
        playersListEl.classList.toggle('d-none');
        profilePill.classList.toggle('collapsed');
    });

    // Simulated live players for social proof
    let livePlayers = [];
    function generateLivePlayers() {
        const masks = ['r*****9', 'a*****4', 'k*****2', 'm*****7', 's*****1', 'v*****8', 'p*****5', 'd*****3', 'j*****6', 'n*****0'];
        const stakes = [100, 250, 500, 1000, 2500, 50, 5000];
        livePlayers = masks.map(u => ({
            user: u,
            stake: stakes[Math.floor(Math.random() * stakes.length)],
            targetMult: +(1.25 + Math.random() * 4.5).toFixed(2),
            cashed: false
        }));
        document.getElementById('blnActivePlayersCount').textContent = livePlayers.length;
        renderLivePlayers();
    }

    function renderLivePlayers() {
        playersListEl.innerHTML = livePlayers.map(p => `
            <div class="bln-player-row ${p.cashed ? 'cashed' : ''}">
                <span class="fw-bold">${p.user}</span>
                <span>₹${p.stake}</span>
                ${p.cashed ? `<span class="bln-player-mult">${p.targetMult.toFixed(2)}x</span>` : `<span class="text-white-50">Flying</span>`}
            </div>
        `).join('');
    }
    generateLivePlayers();

    // Quick Stakes & Edit Mode
    let stakes = [10, 50, 100, 500, 1000, 2500, 5000, 10000];
    let selectedStake = 10;
    let isEditingStakes = false;

    const stakesGrid = document.getElementById('blnStakesGrid');
    const editBtn = document.getElementById('blnEditBtn');

    function bindStakeButtons() {
        stakesGrid.querySelectorAll('.bln-bet-opt').forEach(btn => {
            btn.addEventListener('click', function () {
                if (isEditingStakes) return;
                stakesGrid.querySelectorAll('.bln-bet-opt').forEach(b => b.classList.remove('selectedBtn'));
                this.classList.add('selectedBtn');
                selectedStake = parseFloat(this.dataset.stake) || 10;
                playSound('heat');
                updateHeatButtonLabels();
            });
        });
    }
    bindStakeButtons();

    editBtn.addEventListener('click', function () {
        if (!isEditingStakes) {
            isEditingStakes = true;
            editBtn.textContent = 'Save';
            stakesGrid.querySelectorAll('.bln-bet-opt').forEach((btn, idx) => {
                btn.innerHTML = `<input type="number" class="bln-edit-stake-input" value="${stakes[idx]}" min="10" max="100000">`;
            });
        } else {
            isEditingStakes = false;
            editBtn.textContent = 'Edits';
            stakesGrid.querySelectorAll('.bln-bet-opt').forEach((btn, idx) => {
                const inp = btn.querySelector('input');
                const val = Math.max(10, Math.min(100000, parseInt(inp?.value || stakes[idx], 10) || stakes[idx]));
                stakes[idx] = val;
                btn.dataset.stake = val;
                btn.innerHTML = `<span>${val}</span>`;
                if (btn.classList.contains('selectedBtn')) {
                    selectedStake = val;
                }
            });
            updateHeatButtonLabels();
        }
    });

    document.getElementById('blnClearBtn').addEventListener('click', function () {
        if (isEditingStakes) return;
        selectedStake = stakes[0];
        stakesGrid.querySelectorAll('.bln-bet-opt').forEach((b, i) => b.classList.toggle('selectedBtn', i === 0));
        updateHeatButtonLabels();
    });

    document.getElementById('blnMinBtn').addEventListener('click', function () {
        if (isEditingStakes) return;
        selectedStake = Math.min(...stakes);
        stakesGrid.querySelectorAll('.bln-bet-opt').forEach(b => {
            b.classList.toggle('selectedBtn', parseFloat(b.dataset.stake) === selectedStake);
        });
        updateHeatButtonLabels();
    });

    document.getElementById('blnMaxBtn').addEventListener('click', function () {
        if (isEditingStakes) return;
        selectedStake = Math.max(...stakes);
        stakesGrid.querySelectorAll('.bln-bet-opt').forEach(b => {
            b.classList.toggle('selectedBtn', parseFloat(b.dataset.stake) === selectedStake);
        });
        updateHeatButtonLabels();
    });

    // Auto Switches
    [1, 2].forEach(slotNum => {
        const sw = document.getElementById('blnAutoSwitch' + slotNum);
        const wrap = document.getElementById('blnAutoMultWrap' + slotNum);
        sw.addEventListener('change', function () {
            wrap.classList.toggle('visible', this.checked);
        });
    });

    // Two Bet Slots State (Slot 1 & Slot 2)
    const slots = {
        1: { state: 'IDLE', stake: 10, betId: null, cashedOut: false },
        2: { state: 'IDLE', stake: 10, betId: null, cashedOut: false }
    };

    // Game Phase State Machine
    // Start in IN_FLIGHT around 9.46x so initial view matches the user's screenshot immediately!
    let phase = 'IN_FLIGHT'; // 'WAITING', 'IN_FLIGHT', 'CRASHED'
    let currentMultiplier = 9.46;
    let crashPoint = 14.80;
    let waitingTimer = 5;
    let altitude = 420; // Mid-sky altitude matching screenshot
    let currentLocKey = 'loc1';
    let flameFrame = 0;
    let animTick = 0;
    let roundId = 'BLN' + Date.now();

    function updateWalletDOM(wallet) {
        if (!wallet) return;
        const balEl = document.getElementById('headerAvailableBalance');
        const expEl = document.getElementById('headerExposure');
        if (balEl && wallet.available !== undefined) balEl.textContent = parseFloat(wallet.available).toFixed(2);
        if (expEl && wallet.exposure !== undefined) expEl.textContent = parseFloat(wallet.exposure).toFixed(2);
    }

    function showWinToast(text) {
        const t = document.getElementById('blnWinToast');
        t.textContent = text;
        t.style.display = 'block';
        setTimeout(() => { t.style.display = 'none'; }, 3000);
    }

    function updateHeatButtonLabels() {
        [1, 2].forEach(s => {
            const btn = document.getElementById('blnHeatBtn' + s);
            const txt = document.getElementById('blnHeatText' + s);
            const sub = document.getElementById('blnHeatSub' + s);
            const slot = slots[s];

            btn.classList.remove('cancel-state', 'take-state');

            if (slot.state === 'IDLE') {
                txt.textContent = 'HEAT';
                sub.textContent = '';
            } else if (slot.state === 'QUEUED') {
                btn.classList.add('cancel-state');
                txt.textContent = 'CANCEL';
                sub.textContent = '₹' + slot.stake.toFixed(0) + ' Queued';
            } else if (slot.state === 'PLACED_WAITING') {
                btn.classList.add('cancel-state');
                txt.textContent = 'CANCEL';
                sub.textContent = '₹' + slot.stake.toFixed(0) + ' Placed';
            } else if (slot.state === 'ACTIVE') {
                btn.classList.add('take-state');
                txt.textContent = 'CASH OUT';
                sub.textContent = '₹' + (slot.stake * currentMultiplier).toFixed(2);
            }
        });
    }

    async function placeSlotBet(s) {
        const slot = slots[s];
        const stakeToUse = slot.state === 'QUEUED' ? slot.stake : selectedStake;
        const autoSw = document.getElementById('blnAutoSwitch' + s);
        const autoMult = parseFloat(document.getElementById('blnAutoMult' + s)?.value || '2.00') || 2.00;

        try {
            const res = await fetch('/api/balloon/place-bet', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    event_id: '88.0023',
                    event_name: 'BALLOON',
                    stake: stakeToUse,
                    slot: s,
                    round_id: roundId,
                    auto_cashout: autoSw?.checked ? autoMult : null
                })
            });
            const data = await res.json();
            if (!res.ok || !data.status) {
                if (res.status === 401 && data.redirect) {
                    window.location.href = data.redirect;
                    return;
                }
                showWinToast(data.message || 'Could not place bet');
                slot.state = 'IDLE';
                updateHeatButtonLabels();
                return;
            }
            slot.betId = data.bet.id;
            slot.stake = stakeToUse;
            slot.cashedOut = false;
            slot.state = (phase === 'IN_FLIGHT') ? 'ACTIVE' : 'PLACED_WAITING';
            playSound('heat');
            updateWalletDOM(data.wallet);
            updateHeatButtonLabels();
        } catch (err) {
            slot.state = 'IDLE';
            updateHeatButtonLabels();
        }
    }

    async function cancelSlotBet(s) {
        const slot = slots[s];
        if (slot.state === 'QUEUED' && !slot.betId) {
            slot.state = 'IDLE';
            updateHeatButtonLabels();
            return;
        }
        if (slot.betId) {
            try {
                const res = await fetch('/api/balloon/cancel-bet', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ bet_id: slot.betId })
                });
                const data = await res.json();
                if (data.wallet) updateWalletDOM(data.wallet);
            } catch (e) {}
        }
        slot.state = 'IDLE';
        slot.betId = null;
        updateHeatButtonLabels();
    }

    async function cashoutSlotBet(s) {
        const slot = slots[s];
        if (slot.state !== 'ACTIVE' || !slot.betId || slot.cashedOut) return;
        slot.cashedOut = true;
        const cashMult = currentMultiplier;

        try {
            const res = await fetch('/api/balloon/cashout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    bet_id: slot.betId,
                    multiplier: cashMult
                })
            });
            const data = await res.json();
            if (data.status) {
                playSound('win');
                const winAmt = (slot.stake * cashMult).toFixed(2);
                showWinToast(`WON ₹${winAmt} @ ${cashMult.toFixed(2)}x!`);
                updateWalletDOM(data.wallet);
            }
        } catch (e) {}

        slot.state = 'IDLE';
        slot.betId = null;
        updateHeatButtonLabels();
    }

    // Bind HEAT buttons
    [1, 2].forEach(s => {
        document.getElementById('blnHeatBtn' + s).addEventListener('click', function () {
            const slot = slots[s];
            if (slot.state === 'IDLE') {
                slot.stake = selectedStake;
                if (phase === 'WAITING') {
                    placeSlotBet(s);
                } else {
                    // Queue for next round
                    slot.state = 'QUEUED';
                    playSound('heat');
                    updateHeatButtonLabels();
                }
            } else if (slot.state === 'QUEUED' || slot.state === 'PLACED_WAITING') {
                cancelSlotBet(s);
            } else if (slot.state === 'ACTIVE' && phase === 'IN_FLIGHT') {
                cashoutSlotBet(s);
            }
        });
    });

    // Start Waiting Countdown
    function startWaitingPhase() {
        phase = 'WAITING';
        waitingTimer = 5;
        altitude = 0;
        currentMultiplier = 1.00;
        roundId = 'BLN' + Date.now();
        const locs = ['loc1', 'loc2', 'loc3'];
        currentLocKey = locs[Math.floor(Math.random() * locs.length)];

        document.getElementById('blnWaitingWrap').style.display = 'block';
        document.getElementById('blnMultiplierWrap').style.display = 'none';
        document.getElementById('blnTimerNum').textContent = waitingTimer;

        generateLivePlayers();

        // Place any queued bets or Auto-bets
        [1, 2].forEach(s => {
            const autoSw = document.getElementById('blnAutoSwitch' + s);
            if (slots[s].state === 'QUEUED' || (autoSw && autoSw.checked && slots[s].state === 'IDLE')) {
                slots[s].stake = selectedStake;
                placeSlotBet(s);
            }
        });

        const cdInterval = setInterval(() => {
            waitingTimer--;
            if (waitingTimer > 0) {
                document.getElementById('blnTimerNum').textContent = waitingTimer;
                const arc = document.getElementById('blnTimerArc');
                if (arc) {
                    const offset = ((5 - waitingTimer) / 5) * 182;
                    arc.setAttribute('stroke-dashoffset', offset.toFixed(1));
                }
            } else {
                clearInterval(cdInterval);
                startFlightPhase();
            }
        }, 1000);
    }

    function generateCrashPoint() {
        const r = Math.random();
        if (r < 0.38) return +(1.04 + Math.random() * 0.88).toFixed(2);
        if (r < 0.78) return +(2.01 + Math.random() * 3.80).toFixed(2);
        return +(6.00 + Math.random() * 14.00).toFixed(2);
    }

    function startFlightPhase() {
        phase = 'IN_FLIGHT';
        currentMultiplier = 1.00;
        crashPoint = generateCrashPoint();
        playSound('fly');

        document.getElementById('blnWaitingWrap').style.display = 'none';
        document.getElementById('blnMultiplierWrap').style.display = 'block';
        const multEl = document.getElementById('blnLiveMultiplier');
        multEl.classList.remove('burst');
        multEl.textContent = '1.00x';

        [1, 2].forEach(s => {
            if (slots[s].state === 'PLACED_WAITING') {
                slots[s].state = 'ACTIVE';
            }
        });
        updateHeatButtonLabels();
    }

    async function triggerBurst() {
        phase = 'CRASHED';
        playSound('blast');

        const multEl = document.getElementById('blnLiveMultiplier');
        multEl.classList.add('burst');
        multEl.textContent = currentMultiplier.toFixed(2) + 'x';

        // Prepend to recent multiplier bar
        historyMultipliers.unshift(currentMultiplier);
        if (historyMultipliers.length > 30) historyMultipliers.pop();
        renderHistory();

        // Settle any uncashed active bets as crashed
        const crashedBetIds = [];
        [1, 2].forEach(s => {
            if (slots[s].state === 'ACTIVE' && slots[s].betId) {
                crashedBetIds.push(slots[s].betId);
            }
            if (slots[s].state === 'ACTIVE') {
                slots[s].state = 'IDLE';
                slots[s].betId = null;
            }
        });
        updateHeatButtonLabels();

        if (crashedBetIds.length > 0) {
            try {
                const res = await fetch('/api/balloon/crash', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        bet_ids: crashedBetIds,
                        crash_multiplier: currentMultiplier
                    })
                });
                const data = await res.json();
                if (data.wallet) updateWalletDOM(data.wallet);
            } catch (e) {}
        }

        setTimeout(() => {
            startWaitingPhase();
        }, 2600);
    }

    // Main 60fps Canvas & Multiplier Loop
    function renderLoop() {
        animTick++;
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        // 1. Sky Gradient matching screenshot (#63a7bd -> #9bc2c3 -> #d7d4b4)
        const skyGrad = ctx.createLinearGradient(0, 0, 0, canvas.height);
        skyGrad.addColorStop(0, '#62a6bc');
        skyGrad.addColorStop(0.55, '#9ec3bf');
        skyGrad.addColorStop(1, '#dcd5b2');
        ctx.fillStyle = skyGrad;
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        // 2. Update flight multiplier & altitude
        if (phase === 'IN_FLIGHT') {
            const speed = 0.0045 + (currentMultiplier * 0.0018);
            currentMultiplier = +(currentMultiplier + speed).toFixed(2);
            altitude += 1.4;

            const multEl = document.getElementById('blnLiveMultiplier');
            if (multEl) multEl.textContent = currentMultiplier.toFixed(2) + 'x';

            // Check Auto-cashout for active slots
            [1, 2].forEach(s => {
                if (slots[s].state === 'ACTIVE') {
                    const autoSw = document.getElementById('blnAutoSwitch' + s);
                    const target = parseFloat(document.getElementById('blnAutoMult' + s)?.value || '0');
                    if (autoSw && autoSw.checked && target >= 1.01 && currentMultiplier >= target) {
                        cashoutSlotBet(s);
                    }
                }
            });
            updateHeatButtonLabels();

            // Update simulated players cashing out
            let changed = false;
            livePlayers.forEach(p => {
                if (!p.cashed && currentMultiplier >= p.targetMult) {
                    p.cashed = true;
                    changed = true;
                }
            });
            if (changed) renderLivePlayers();

            if (currentMultiplier >= crashPoint) {
                triggerBurst();
            }
        }

        // 3. Draw drifting peach clouds from /assets/balloon/assets/clouds.png
        if (sprites.clouds && sprites.clouds.complete && sprites.clouds.naturalWidth > 0) {
            const cloudY = ((altitude * 0.45) % 420);
            ctx.globalAlpha = 0.92;
            ctx.drawImage(sprites.clouds, 70, 95 + cloudY - 120, 810, 290);
            ctx.drawImage(sprites.clouds, 70, 95 + cloudY - 540, 810, 290);
            ctx.globalAlpha = 1.0;
        }

        // 4. Draw Mountains/Pyramids at low altitude (scrolls down out of view as altitude increases)
        const locImg = sprites[currentLocKey];
        if (locImg && locImg.complete && locImg.naturalWidth > 0 && altitude < 260) {
            const offsetY = altitude * 1.6;
            // Draw the bottom mountain portion of locationLt.png at the bottom of the stage
            const srcH = locImg.naturalHeight;
            const srcW = locImg.naturalWidth;
            ctx.save();
            ctx.drawImage(
                locImg,
                0, srcH * 0.46, srcW, srcH * 0.34,
                0, 220 + offsetY, canvas.width, 320
            );
            ctx.restore();
        }

        // 5. Draw Center Hot Air Balloon & Burner Flame
        const bobY = Math.sin(animTick * 0.05) * 4;
        const bx = canvas.width / 2;
        const by = 228 + bobY;

        if (sprites.balloon && sprites.balloon.complete && sprites.balloon.naturalWidth > 0) {
            const bw = 204;
            const bh = 282;

            // Draw animated flame between basket and envelope during IN_FLIGHT
            if (phase === 'IN_FLIGHT' && sprites.fire && sprites.fire.complete && sprites.fire.naturalWidth > 0) {
                const fw = sprites.fire.naturalWidth / 3;
                const fh = sprites.fire.naturalHeight;
                flameFrame = Math.floor(animTick / 5) % 3;
                ctx.drawImage(
                    sprites.fire,
                    flameFrame * fw, 0, fw, fh,
                    bx - 12, by + 76, 24, 32
                );
            }

            // Slight scale pulse on burst
            ctx.save();
            if (phase === 'CRASHED') {
                ctx.globalAlpha = 0.65;
                ctx.translate(bx, by);
                ctx.scale(0.92, 0.92);
                ctx.translate(-bx, -by);
            }
            ctx.drawImage(sprites.balloon, bx - bw / 2, by - bh / 2 + 12, bw, bh);
            ctx.restore();
        }

        requestAnimationFrame(renderLoop);
    }

    requestAnimationFrame(renderLoop);
});
</script>
@endpush
