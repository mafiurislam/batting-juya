@extends('layouts.app')

@section('title', 'Ball By Ball | WINBOOK24')

@php
  $game = $bbbGame ?? [];
  $exMarketId = $game['exMarketId'] ?? $game['marketId'] ?? '366411202611127133185';
  $minBet = $game['min'] ?? 100;
  $maxBet = $game['max'] ?? 100000;
  $newsText = $game['news'] ?? 'Results are based on stream only. Score board may be different or updated later';
  $oddsData = $game['oddsData'] ?? [];
  $marketStatus = $oddsData['status'] ?? 'ONLINE';
  $secondsLeft = (int) ($oddsData['seconds'] ?? 20);
  $videoData = $oddsData['video'] ?? [];
  $runnersData = $game['runnersData'] ?? [
    '304350101' => '0 RUNS',
    '304350102' => '1 RUNS',
    '304350103' => '2 RUNS',
    '304350104' => '3 RUNS',
    '304350105' => '4 RUNS',
    '304350106' => '6 RUNS',
    '304350107' => 'WICKET',
    '304350108' => 'EXTRA RUNS',
  ];
  $rawRunners = $oddsData['runners'] ?? [];
  $runnerByIdx = [];
  foreach ($rawRunners as $idx => $r) {
      $selId = (string) ($r['selectionId'] ?? ('30435010' . ($idx + 1)));
      $runnerByIdx[$idx] = [
          'selectionId' => $selId,
          'name' => $runnersData[$selId] ?? '0 RUNS',
          'status' => $r['status'] ?? $marketStatus,
          'price' => $r['price']['back'][0]['price'] ?? 2.0,
          'size' => $r['price']['back'][0]['size'] ?? 100000,
      ];
  }

  $defaultRunners = [
      0 => ['selectionId' => '304350101', 'name' => '0 RUNS', 'status' => 'ONLINE', 'price' => 2.18, 'size' => 100000],
      1 => ['selectionId' => '304350102', 'name' => '1 RUNS', 'status' => 'ONLINE', 'price' => 2.62, 'size' => 100000],
      2 => ['selectionId' => '304350103', 'name' => '2 RUNS', 'status' => 'ONLINE', 'price' => 7.92, 'size' => 100000],
      3 => ['selectionId' => '304350104', 'name' => '3 RUNS', 'status' => 'ONLINE', 'price' => 11,   'size' => 100000],
      4 => ['selectionId' => '304350105', 'name' => '4 RUNS', 'status' => 'ONLINE', 'price' => 6.52, 'size' => 100000],
      5 => ['selectionId' => '304350106', 'name' => '6 RUNS', 'status' => 'ONLINE', 'price' => 12.2, 'size' => 100000],
      6 => ['selectionId' => '304350108', 'name' => 'WICKET', 'status' => 'ONLINE', 'price' => 6.87, 'size' => 100000],
      7 => ['selectionId' => '304350109', 'name' => 'EXTRA RUNS', 'status' => 'ONLINE', 'price' => 8.67, 'size' => 100000],
  ];
  for ($i = 0; $i < 8; $i++) {
      if (!isset($runnerByIdx[$i])) {
          $runnerByIdx[$i] = $defaultRunners[$i];
      }
  }

  $resultsList = $bbbResults ?? [];
  $stakeButtons = auth()->check() ? auth()->user()->getStakeButtons() : [
      ['label' => '100', 'value' => 100], ['label' => '500', 'value' => 500],
      ['label' => '1000', 'value' => 1000], ['label' => '5000', 'value' => 5000],
      ['label' => '10000', 'value' => 10000], ['label' => '25000', 'value' => 25000],
      ['label' => '50000', 'value' => 50000], ['label' => '100000', 'value' => 100000],
  ];
@endphp

@push('styles')
<style>
  :root {
    --timerValue: 20s;
    --fancy-news-bg: #d4eef5;
    --fancy-news-text: #095970;
    --market-tabs-cmp-betslipback-background: #bed5d8;
    --matchodds-pl-plus: #168940;
    --market-details-cmp-minus-book-color: #d0021b;
  }

  /* ================= 1:1 WINBOOK24 BALL BY BALL STYLES (chunk_403) ================= */
  .bbb-page-wrap {
    font-family: Tahoma, Helvetica, sans-serif;
    user-select: none;
  }

  /* Top Highlight Header (BALL BY BALL | Round ID) */
  .bbb-page-wrap .game-highlight-wrap {
    background: linear-gradient(180deg, #2e4b5e 0%, #243a48 100%);
    color: #fff;
    font-size: 12px;
    line-height: 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 6px;
  }
  .bbb-page-wrap .cric-txt {
    font-weight: 900;
    position: relative;
    left: 2px;
    letter-spacing: 0.2px;
  }
  .bbb-page-wrap .roundId {
    font-size: 11px;
    font-weight: 600;
    margin-right: 2px;
  }

  /* Video / Poster Stage (app-video-ballby-ball) */
  .bbb-page-wrap .videoPlayer {
    position: relative;
    display: flex;
    width: 100%;
    background: #000;
    overflow: hidden;
  }
  .bbb-page-wrap .ballbyballPoster {
    width: 100%;
    height: auto;
    display: block;
  }
  .bbb-page-wrap #videoBallByball {
    width: 100%;
    height: auto;
    aspect-ratio: 16 / 9;
    background-color: #000;
    background-size: contain;
    object-fit: fill;
    display: block;
  }

  /* Zooming Result Ball Overlay (.ballbyballResult) */
  .bbb-page-wrap .ballbyballResult {
    position: absolute;
    top: 50%;
    left: 50%;
    margin-left: -60px;
    margin-top: -60px;
    z-index: 10;
    animation: bbbZoomInOut 1s ease;
    animation-iteration-count: 3;
    pointer-events: none;
  }
  .bbb-page-wrap .ballbyballResult img {
    width: 120px;
    transform: rotate(341deg);
    display: block;
  }
  .bbb-page-wrap .ballbyballResult span {
    position: absolute;
    top: 50%;
    left: 46%;
    transform: translate(-50%, -50%);
    color: #fff;
    font-weight: 700;
    text-transform: uppercase;
    font-size: 20px;
    line-height: 19px;
    text-align: center;
    white-space: nowrap;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.85);
  }
  @media screen and (max-width: 766px) {
    .bbb-page-wrap .ballbyballResult {
      margin-left: -40px;
      margin-top: -40px;
    }
    .bbb-page-wrap .ballbyballResult img {
      width: 80px;
    }
    .bbb-page-wrap .ballbyballResult span {
      font-size: 16px;
      line-height: 20px;
      top: 47%;
      left: 42%;
    }
  }
  @keyframes bbbZoomInOut {
    0% { transform: scale(1); }
    50% { transform: scale(1.5); }
    100% { transform: scale(1); }
  }

  /* Circular 2-Hand Countdown Timer (.timer-overlay, .timer-group) */
  .bbb-page-wrap .timer-overlay {
    position: absolute;
    right: 4px;
    top: 0;
    height: auto;
    display: flex;
    flex-direction: column;
    align-items: center;
    z-index: 8;
  }
  .bbb-page-wrap .timer-group {
    height: 50px;
    margin: 5px auto 0;
    position: relative;
    width: 50px;
  }
  .bbb-page-wrap .timer {
    border-radius: 50%;
    height: 50px;
    overflow: hidden;
    position: absolute;
    width: 50px;
  }
  .bbb-page-wrap .timer:after {
    background: #111;
    border-radius: 50%;
    content: "";
    display: block;
    height: 40px;
    left: 5px;
    position: absolute;
    width: 40px;
    top: 5px;
  }
  .bbb-page-wrap .timer .hand {
    float: left;
    height: 100%;
    overflow: hidden;
    position: relative;
    width: 50%;
  }
  .bbb-page-wrap .timer .hand span {
    border: 25px solid rgba(0, 255, 255, 0.4);
    border-bottom-color: transparent;
    border-left-color: transparent;
    border-top-color: green;
    border-right-color: green;
    border-radius: 50%;
    display: block;
    height: 0;
    position: absolute;
    right: 0;
    top: 0;
    transform: rotate(225deg);
    width: 0;
    animation-duration: var(--timerValue);
    animation-iteration-count: infinite;
    animation-timing-function: linear;
  }
  .bbb-page-wrap .timer .hand:first-child {
    transform: rotate(180deg);
  }
  .bbb-page-wrap .timer .hand:first-child span {
    animation-name: bbbSpin1;
  }
  .bbb-page-wrap .timer .hand:last-child span {
    animation-name: bbbSpin2;
  }
  .bbb-page-wrap .timer.minute {
    background: rgba(0, 0, 0, 0.2);
    height: 50px;
    right: 0;
    width: 50px;
    bottom: 0;
  }
  .bbb-page-wrap .spanColorRed {
    border-top-color: red !important;
    border-right-color: red !important;
  }
  .bbb-page-wrap .face {
    border-radius: 50%;
    height: 40px;
    left: 0;
    position: absolute;
    width: 40px;
    text-align: center;
    top: 0;
    z-index: 3;
  }
  .bbb-page-wrap .face p {
    font-size: 20px;
    font-weight: 700;
    position: absolute;
    top: 8px;
    text-align: center;
    right: 2px;
    color: #fff;
    margin: 0;
    line-height: 24px;
  }
  .bbb-page-wrap .singleDigit {
    top: 9px !important;
    right: 8px !important;
  }
  .bbb-page-wrap .heart {
    color: #e00 !important;
    animation: bbbBeat 0.5s infinite alternate;
    transform-origin: center;
  }
  @keyframes bbbBeat {
    to { transform: scale(1.25); }
  }
  @keyframes bbbSpin1 {
    0% { transform: rotate(225deg); }
    50% { transform: rotate(225deg); }
    100% { transform: rotate(405deg); }
  }
  @keyframes bbbSpin2 {
    0% { transform: rotate(225deg); }
    50% { transform: rotate(405deg); }
    100% { transform: rotate(405deg); }
  }

  /* Market Title Bar (Runs | Rules Icon | Min/Max: 100 - 100000) */
  .bbb-page-wrap .market-title {
    background: linear-gradient(180deg, #2e4b5e 0%, #243a48 100%);
    color: #fff;
    padding: 5px 10px;
    font-size: 15px;
    font-weight: 700;
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
  }
  .bbb-page-wrap .market-title-label {
    align-items: center;
    display: flex;
    gap: 6px;
  }
  .bbb-page-wrap .btn-ballbyball-rules {
    align-items: center;
    color: inherit;
    cursor: pointer;
    display: inline-flex;
    height: 18px;
    justify-content: center;
    text-decoration: none;
    width: 18px;
  }
  .bbb-page-wrap .btn-ballbyball-rules:hover {
    color: #fff;
  }
  .bbb-page-wrap .minMaxDesktop {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    width: 100%;
    font-size: 14px;
    font-weight: 700;
  }

  /* Market Header (Back | Back) */
  .bbb-page-wrap .market-header {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    padding: 0;
    border-bottom: 0.01em solid #c7c8ca;
    background: #fff;
  }
  .bbb-page-wrap .market-row {
    display: flex;
    flex-wrap: wrap;
    border-bottom: 0.01em solid #c7c8ca;
  }
  .bbb-page-wrap .market-header .market-row {
    border-bottom: 0;
  }
  .bbb-page-wrap .market-odd-box {
    width: 165px;
    padding: 2px 0;
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    color: #000;
    text-decoration: none;
    min-height: 44px;
  }
  .bbb-page-wrap .market-header .market-odd-box {
    min-height: 28px;
    cursor: default;
  }
  .bbb-page-wrap .market-header .market-odd-box b {
    font-size: 15px;
    font-weight: 700;
    color: #000;
  }

  /* Market Body (Mint Green Background + 8 Dual-Pill Runners) */
  .bbb-page-wrap .market-body {
    background-image: linear-gradient(90deg, #82dda6cc, #82ddb059 49%, #82dda6cc) !important;
  }
  .bbb-page-wrap .paddingZero {
    padding-left: 0 !important;
    padding-right: 0 !important;
  }
  .bbb-page-wrap .fancy-market {
    padding-bottom: 0.625rem;
  }
  .bbb-page-wrap .fancy-market .market-row {
    border-bottom: 0;
  }
  .bbb-page-wrap .blb-box {
    position: relative;
  }
  .bbb-page-wrap .pl-market {
    border-style: none none none dashed;
    border-width: 3px;
    border-color: #000;
    width: 60%;
    min-height: 65px;
    max-height: 65px;
    display: flex;
    justify-content: center;
    flex-direction: column;
  }
  .bbb-page-wrap .back-1 {
    background: linear-gradient(to bottom, #c9bf34, #f7eb4c, #c9bf34) !important;
    border-radius: 10px;
    min-height: 65px !important;
  }
  .bbb-page-wrap .back-4 {
    background: linear-gradient(to bottom, #2dcd68, #98ffbe, #168940) !important;
    border-radius: 10px;
    min-height: 65px !important;
  }
  .bbb-page-wrap .back-6 {
    background: linear-gradient(to bottom, #8b97ff, #a8bdff, #7173fc) !important;
    border-radius: 10px;
    min-height: 65px !important;
  }
  .bbb-page-wrap .back-wicket {
    background: linear-gradient(to bottom, #e7645a, #f68f87, #ff5043) !important;
    border-radius: 10px;
    min-height: 65px !important;
  }
  .bbb-page-wrap .selected_Cell {
    background: #16a660 !important;
    color: #fff !important;
    text-shadow: 2px 2px 2px rgba(0, 0, 0, 0.5) !important;
  }
  .bbb-page-wrap .market-odd-box .market-odd {
    font-size: 12px;
    font-weight: 700;
    margin-bottom: 3px;
    line-height: 1;
    color: #000 !important;
    text-shadow: none !important;
  }
  .bbb-page-wrap .market-volume {
    font-size: 12px !important;
    color: #000 !important;
    text-shadow: none !important;
    line-height: 1;
  }
  .bbb-page-wrap .market-nation-name {
    font-size: 15px;
    color: #000;
  }
  .bbb-page-wrap .pointer {
    cursor: pointer;
  }
  .bbb-page-wrap .plus-book {
    color: var(--matchodds-pl-plus) !important;
    align-items: center;
    font-size: 11px;
    font-weight: 700;
  }
  .bbb-page-wrap .minus-book {
    color: var(--market-details-cmp-minus-book-color);
    align-items: center;
    font-size: 11px;
    font-weight: 700;
  }

  /* Suspended Lock Overlay on Runner Pill */
  .bbb-page-wrap .fancy-market .suspended:after {
    cursor: not-allowed;
    position: absolute;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100%;
    right: 0;
    background: rgba(0, 0, 0, 0.6);
    color: #fff;
    text-transform: uppercase;
    font-family: "Font Awesome 5 Free", "Font Awesome 6 Free";
    content: "\f023";
    font-weight: 900;
    font-size: 18px;
    top: 0;
    width: 100%;
    border-radius: 5px;
  }

  @media only screen and (max-width: 767px) {
    .bbb-page-wrap .fancy-market {
      width: 80%;
    }
    .bbb-page-wrap .border-r-transparent,
    .bbb-page-wrap .border-r-grey {
      border-right: 2px solid #c7c8ca;
    }
    .bbb-page-wrap .paddingZero {
      padding: 0 !important;
    }
    .bbb-page-wrap .market-title {
      font-size: 13px;
      padding: 3px 6px;
    }
    .bbb-page-wrap .blb-box {
      width: 100%;
      position: relative;
      border-radius: 5px;
      min-height: 65px;
    }
    .bbb-page-wrap .blb-box .market-odd-box {
      width: 100%;
    }
  }

  /* Inline Bet Slip inside .market-body (chunk_403 #betslip) */
  .bbb-page-wrap .sportsbookBg {
    background: var(--market-tabs-cmp-betslipback-background) !important;
  }
  .bbb-page-wrap .blue-bet-slip-back {
    border-bottom: 1px solid #7e97a7;
    text-align: center;
    font-size: 12px;
    padding: 6px 8px;
    font-weight: 700;
    border-top: none;
  }

  /* Marquee News Bar (.progress-bar) */
  .bbb-page-wrap .progress-bar {
    width: 100%;
    height: 25px;
    padding: 0;
    margin: 0;
    background: var(--fancy-news-bg);
    border-radius: 0;
    box-shadow: inset 0 2px #0000001a;
    transition: width 0.4s ease;
    display: block;
  }
  .bbb-page-wrap .progress-bar-txt {
    width: 100%;
    font-weight: 700;
    color: var(--fancy-news-text);
    line-height: 25px;
    font-size: 12px;
    padding: 0;
    display: block;
  }

  /* Recent Result Bar (app-results .recent-res.teenpatti) */
  .bbb-page-wrap .recent-res {
    background: #000;
    padding: 5px;
    color: #fff;
    margin-top: 50px;
    width: 100%;
    display: inline-block;
    white-space: nowrap;
    overflow-x: auto;
    margin-bottom: 20px;
  }
  @media only screen and (max-width: 992px) {
    .bbb-page-wrap .recent-res {
      margin-top: 10px;
    }
  }
  .bbb-page-wrap .recent-res h5 {
    display: inline-block;
    font-size: 1.07375rem;
    margin: 0;
    font-weight: 700;
    color: #fff;
    vertical-align: middle;
  }
  .bbb-page-wrap .recent-res ul.result {
    padding: 0;
    display: inline-block;
    margin: 0;
    vertical-align: middle;
  }
  .bbb-page-wrap .recent-res ul.result li.sportsbookTile {
    width: 35px;
    min-height: 28px !important;
    height: 28px;
    color: #333;
    margin-left: 10px;
    padding: 4px 5px;
    list-style: none;
    text-align: center;
    font-weight: 700;
    display: inline-block;
    border-radius: 15px !important;
    cursor: pointer;
    line-height: 20px;
  }
  .bbb-page-wrap .recent-res ul.result li.sportsbookTile a.number {
    text-decoration: none;
    color: #333 !important;
    font-size: 13px;
    font-weight: 700;
    display: block;
  }

  /* Rules Modal & Result Modal (chunk_403) */
  .ball-market-rules {
    background-image: linear-gradient(90deg, #82dda6cc, #82ddb059 49%, #82dda6cc);
    color: #182832;
    font-family: Tahoma, Helvetica, sans-serif;
    padding: 10px;
  }
  .ball-market-header {
    background: linear-gradient(180deg, #2e4b5e 0%, #243a48 100%);
    color: #fff;
    margin: 0 0 10px;
    padding: 10px 12px;
  }
  .ball-market-header h2 {
    color: inherit;
    font-size: 18px;
    font-weight: 800;
    line-height: 22px;
    margin: 0 0 4px;
    text-align: left;
  }
  .ball-market-header p {
    font-size: 12px;
    font-weight: 600;
    line-height: 17px;
    margin: 0;
  }
  .ball-market-rules .section-title {
    background: #ffffffb8;
    border: 1px solid rgba(36, 58, 72, 0.18);
    color: #243a48;
    font-size: 14px;
    font-weight: 800;
    line-height: 18px;
    margin: 0 0 8px;
    padding: 8px 10px;
  }
  .ball-market-rules .rules-grid {
    display: grid;
    gap: 8px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
  @media only screen and (max-width: 767px) {
    .ball-market-rules .rules-grid {
      grid-template-columns: 1fr;
    }
  }
  .ball-market-rules .rule-card {
    background: #ffffffdb;
    border: 1px solid rgba(36, 58, 72, 0.22);
    border-radius: 6px;
    min-height: 98px;
    padding: 10px;
  }
  .ball-market-rules .rule-card strong {
    align-items: center;
    color: #111;
    display: flex;
    font-size: 13px;
    font-weight: 800;
    gap: 8px;
    margin-bottom: 6px;
  }
  .ball-market-rules .rule-card p {
    color: #243a48;
    font-size: 12px;
    font-weight: 600;
    line-height: 17px;
    margin: 0;
  }
  .ball-market-rules .rule-badge {
    align-items: center;
    background: linear-gradient(to bottom, #c9bf34, #f7eb4c, #c9bf34);
    border: 1px solid rgba(0, 0, 0, 0.25);
    border-radius: 5px;
    color: #000;
    display: inline-flex;
    font-size: 12px;
    font-weight: 900;
    height: 28px;
    justify-content: center;
    min-width: 32px;
    padding: 0 8px;
  }
  .ball-market-rules .rule-card:nth-child(5) .rule-badge {
    background: linear-gradient(to bottom, #2dcd68, #98ffbe, #168940);
  }
  .ball-market-rules .rule-card:nth-child(6) .rule-badge {
    background: linear-gradient(to bottom, #8b97ff, #a8bdff, #7173fc);
  }
  .ball-market-rules .highlight-wicket .rule-badge {
    background: linear-gradient(to bottom, #e7645a, #f68f87, #ff5043);
  }
  .ball-market-rules .important-box {
    background: #ffffffe0;
    border: 1px solid rgba(36, 58, 72, 0.24);
    border-left: 4px solid #243a48;
    border-radius: 6px;
    margin-top: 10px;
    padding: 10px;
  }
  .ball-market-rules .important-box h3 {
    color: #243a48;
    font-size: 14px;
    font-weight: 800;
    line-height: 18px;
    margin: 0 0 6px;
  }
  .ball-market-rules .important-box p {
    color: #243a48;
    font-size: 12px;
    font-weight: 600;
    line-height: 17px;
    margin: 0 0 6px;
  }
  .rules-wrap .btn-wrap {
    border-top: 1px solid #ccc;
    padding: 6px 0;
    margin: 0;
    background: #fff;
  }
  .rules-wrap .btnok {
    width: 150px;
    margin: 0 auto;
    display: block;
    background: #eee;
    border: 1px solid #bbb;
    border-radius: 4px;
    color: #1e1e1e;
    font-weight: 700;
    line-height: 25px;
    font-size: 12px;
    text-align: center;
    text-decoration: none;
    cursor: pointer;
  }
</style>
@endpush

@section('content')
<div class="bbb-page-wrap" id="appBallByBallRoot">
  <!-- 1. Top Highlight Bar + Video/Poster Stage (mobileScoreCardAndStream) -->
  <div class="position-relative mobileScoreCardAndStream">
    <div class="game-highlight-wrap">
      <span class="cric-txt">BALL BY BALL</span>
      <span class="roundId" id="bbbRoundIdText">{{ $exMarketId }}</span>
    </div>

    <div id="tabs" class="accordion-body">
      <div class="videoPlayer">
        <!-- Live Ball Video Stream (shown when SUSPENDED & !showBanner) -->
        <video
          id="videoBallByball"
          width="100%"
          poster="/assets/ballbyball.gif"
          autoplay
          playsinline
          muted
          preload="auto"
          class="isDesktop d-none"
          style="background-color: black; background-size: contain;"
        ></video>

        <!-- Uncropped Animated Poster (shown when ONLINE or after video finishes) -->
        <img
          id="bbbPosterImg"
          src="/assets/ballbyball.gif"
          alt="Ball by Ball"
          class="isDesktop ballbyballPoster"
        >

        <!-- Center Zooming Result Ball Overlay (.ballbyballResult) -->
        <div id="bbbResultOverlay" class="ballbyballResult d-none">
          <img src="/assets/emptyBall.png" alt="Result Ball">
          <span id="bbbResultOverlayText">0 RUNS</span>
        </div>

        <!-- Top-Right 2-Hand Circular Countdown Timer (.timer-overlay) -->
        <div class="timer-overlay">
          <div id="bbbTimerGroup" class="timer-group {{ $marketStatus === 'ONLINE' && $secondsLeft > 0 ? '' : 'd-none' }}">
            <div class="timer minute">
              <div class="hand"><span id="bbbTimerHand1" class=""></span></div>
              <div class="hand"><span id="bbbTimerHand2" class=""></span></div>
            </div>
            <div class="face">
              <span id="bbbTimerFaceSpan">
                <p id="lazy" class="{{ $secondsLeft < 10 ? 'singleDigit' : '' }}">{{ $secondsLeft }}</p>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- 2. Market Section (#marketScroll -> .game-market.market-6) -->
  <div id="marketScroll" class="overflow-hidden">
    <div class="game-market market-6">
      <!-- Runs Title Bar -->
      <div class="market-title">
        <div class="market-title-label">
          <span>Runs</span>
          <a data-bs-toggle="modal" data-bs-target="#ballByBallRulesModal" aria-label="Rules" class="btn-ballbyball-rules">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 15 15">
              <path fill="currentColor" fill-rule="evenodd" d="M6.76 5.246V3.732h1.48v1.514H6.76zm.74 8.276a5.86 5.86 0 0 0 3.029-.83 5.839 5.839 0 0 0 2.163-2.163 5.86 5.86 0 0 0 .83-3.029 5.86 5.86 0 0 0-.83-3.029 5.839 5.839 0 0 0-2.163-2.163 5.86 5.86 0 0 0-3.029-.83 5.86 5.86 0 0 0-3.029.83A5.839 5.839 0 0 0 2.308 4.47a5.86 5.86 0 0 0-.83 3.029 5.86 5.86 0 0 0 .83 3.029 5.839 5.839 0 0 0 2.163 2.163 5.86 5.86 0 0 0 3.029.83zM7.5 0c1.37 0 2.638.343 3.804 1.028a7.108 7.108 0 0 1 2.668 2.668A7.376 7.376 0 0 1 15 7.5c0 1.37-.343 2.638-1.028 3.804a7.108 7.108 0 0 1-2.668 2.668A7.376 7.376 0 0 1 7.5 15a7.376 7.376 0 0 1-3.804-1.028 7.243 7.243 0 0 1-2.668-2.686A7.343 7.343 0 0 1 0 7.5c0-1.358.343-2.62 1.028-3.786a7.381 7.381 0 0 1 2.686-2.686A7.343 7.343 0 0 1 7.5 0zm-.74 11.268V6.761h1.48v4.507H6.76z"></path>
            </svg>
          </a>
        </div>
        <div class="position-relative">
          <div class="minMaxDesktop">
            <span>Min/Max: <span id="bbbMinMaxText">{{ $minBet }} - {{ $maxBet }}</span> </span>
          </div>
        </div>
      </div>

      <!-- Back | Back Subheader -->
      <div class="market-header row ms-0 me-0">
        <div class="col-6 border-r-transparent paddingZero">
          <div class="market-row justify-content-center">
            <div class="market-odd-box back-0"><b>Back</b></div>
          </div>
        </div>
        <div class="col-6 paddingZero">
          <div class="market-row justify-content-center">
            <div class="market-odd-box back-0"><b>Back</b></div>
          </div>
        </div>
      </div>

      <!-- Mint Green Market Body with 4 Rows x 2 Dual-Pill Runners -->
      @php
        $pillPairs = [
          [
            ['idx' => 0, 'cls' => 'back-1', 'nameStyle' => 'font-weight: 800; line-height: 22px; padding-left: 15px;', 'nameCls' => 'market-nation-name pointer'],
            ['idx' => 1, 'cls' => 'back-1', 'nameStyle' => 'font-weight: 800; line-height: 22px; padding-left: 15px;', 'nameCls' => 'market-nation-name pointer'],
          ],
          [
            ['idx' => 2, 'cls' => 'back-1', 'nameStyle' => 'font-weight: 800; line-height: 22px; padding-left: 15px;', 'nameCls' => 'market-nation-name pointer'],
            ['idx' => 3, 'cls' => 'back-1', 'nameStyle' => 'font-weight: 800; line-height: 22px; padding-left: 15px;', 'nameCls' => 'market-nation-name pointer'],
          ],
          [
            ['idx' => 4, 'cls' => 'back-4', 'nameStyle' => 'font-weight: 800; line-height: 22px; padding-left: 15px;', 'nameCls' => 'market-nation-name pointer'],
            ['idx' => 5, 'cls' => 'back-6', 'nameStyle' => 'font-weight: 800; line-height: 22px; padding-left: 15px;', 'nameCls' => 'market-nation-name pointer'],
          ],
          [
            ['idx' => 6, 'cls' => 'back-wicket', 'nameStyle' => 'font-weight: 800; line-height: 42px; padding-left: 15px;', 'nameCls' => 'market-nation-name pointer'],
            ['idx' => 7, 'cls' => 'back-1', 'nameStyle' => 'font-weight: 800; line-height: 24px; padding-left: 9px; font-size: 12px !important;', 'nameCls' => 'market-nation-name pointer text-center d-flex justify-content-center align-items-center'],
          ],
        ];
      @endphp

      <div class="market-body">
        @foreach($pillPairs as $rowIdx => $pair)
          <div class="row ms-0 me-0" @if($rowIdx === 0) style="padding-top: 8px;" @endif>
            @foreach($pair as $colIdx => $cfg)
              @php
                $r = $runnerByIdx[$cfg['idx']];
                $isSusp = ($marketStatus === 'SUSPENDED' || ($r['status'] ?? '') === 'SUSPENDED' || ($r['status'] ?? '') === 'SUSPEND');
              @endphp
              <div class="col-6 d-flex justify-content-center {{ $colIdx === 0 ? 'border-r-grey' : '' }} paddingZero">
                <div data-title="OPEN" class="fancy-market">
                  <div class="market-row">
                    <div class="blb-box">
                      <a
                        href="javascript:void(0)"
                        id="bbbRunnerBox_{{ $r['selectionId'] }}"
                        data-selection-id="{{ $r['selectionId'] }}"
                        data-runner-name="{{ $r['name'] }}"
                        data-row-index="{{ $rowIdx }}"
                        onclick="BBBGame.onClickRunner('{{ $r['selectionId'] }}', '{{ $r['name'] }}', {{ $rowIdx }})"
                        class="market-odd-box {{ $cfg['cls'] }} js-bbb-runner {{ $isSusp ? 'suspended' : '' }}"
                      >
                        <div class="d-flex flex-column align-items-center" style="width: 40%;">
                          <span class="market-odd" id="bbbOdd_{{ $r['selectionId'] }}">{{ $r['price'] }}</span>
                          <span class="market-volume" id="bbbVol_{{ $r['selectionId'] }}">{{ $r['size'] }}</span>
                        </div>
                        <div class="position-relative pl-market">
                          <span class="{{ $cfg['nameCls'] }}" style="{{ $cfg['nameStyle'] }}">{{ $r['name'] }}</span>
                          <span
                            id="bbbPlWrap_{{ $r['selectionId'] }}"
                            class="d-flex align-items-center justify-content-center position-absolute"
                            style="bottom: 4px; left: 50%; transform: translateX(-50%);"
                          ></span>
                        </div>
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            @endforeach
          </div>

          <!-- Inline Bet Slip Row (opens directly below the clicked runner row, 1:1 with chunk_403) -->
          <div id="bbbInlineSlipRow_{{ $rowIdx }}" class="paddingZero d-none" style="padding-bottom: 0.625rem !important;"></div>
        @endforeach
      </div>

      <!-- Hidden Inline Bet Slip Template -->
      <div id="bbbInlineSlipWidget" class="sportsbookBg blue-bet-slip-back custom-td d-none">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2 px-1">
          <div class="text-start">
            <span class="badge bg-primary me-1">BACK</span>
            <strong id="bbbSlipRunnerName" class="text-dark">0 RUNS</strong>
            <span class="text-muted ms-1" style="font-size:11px;">(Runs)</span>
          </div>
          <div class="d-flex align-items-center gap-2">
            <div>
              <label class="me-1 text-dark">Odds</label>
              <input type="number" step="0.01" id="bbbSlipOdds" class="form-control form-control-sm d-inline-block text-center fw-bold" style="width:80px;height:26px;font-size:12px;" readonly>
            </div>
            <div>
              <label class="me-1 text-dark">Stake</label>
              <input type="number" step="10" id="bbbSlipStake" class="form-control form-control-sm d-inline-block text-center fw-bold" style="width:95px;height:26px;font-size:12px;" placeholder="Stake" oninput="BBBGame.updateSlipCalc()">
            </div>
          </div>
        </div>
        <div class="d-flex flex-wrap justify-content-center gap-1 mb-2">
          @foreach($stakeButtons as $sb)
            <button type="button" class="btn btn-sm btn-dark fw-bold py-0 px-2" style="font-size:11px;height:24px;" onclick="BBBGame.setStake({{ $sb['value'] }})">+{{ $sb['label'] }}</button>
          @endforeach
        </div>
        <div class="d-flex justify-content-between align-items-center px-1">
          <div class="small">
            Profit: <strong class="text-success" id="bbbSlipProfit">0.00</strong> |
            Exposure: <strong class="text-danger" id="bbbSlipLiability">0.00</strong>
          </div>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-danger fw-bold py-0 px-3" style="font-size:12px;height:26px;" onclick="BBBGame.closeBetSlip()">Cancel</button>
            <button type="button" id="bbbSlipSubmitBtn" class="btn btn-sm btn-success fw-bold py-0 px-3" style="font-size:12px;height:26px;" onclick="BBBGame.submitBet()">Place Bet</button>
          </div>
        </div>
      </div>

      <!-- Marquee Disclaimer Bar (.progress-bar) -->
      <div class="text-right progress-bar">
        <marquee direction="left" scrollamount="3" behavior="scroll" class="pull-right progress-bar-txt" id="bbbNewsMarquee">
          {{ $newsText }}
        </marquee>
      </div>

      <!-- Recent Result Bar (app-results .recent-res.teenpatti) -->
      <div class="recent-res teenpatti">
        <h5>Recent Result</h5>
        <ul class="result" id="bbbRecentResultList">
          @foreach($resultsList as $idx => $resItem)
            @php
              $resStr = strtoupper(trim($resItem['result'] ?? '0 RUNS'));
              $shortChar = substr($resStr, 0, 1);
              $tileCls = 'back-1';
              if ($resStr === '4 RUNS') $tileCls = 'back-4';
              elseif ($resStr === '6 RUNS') $tileCls = 'back-6';
              elseif ($resStr === 'WICKET') $tileCls = 'back-wicket';
            @endphp
            <li class="casino-a sportsbookTile {{ $tileCls }}" onclick="BBBGame.openResultModal({{ $idx }})">
              <a href="javascript:void(0)" class="number text-dark">{{ $shortChar }}</a>
            </li>
          @endforeach
        </ul>
      </div>
    </div>
  </div>

  <!-- 3. Recent Result Video Replay Modal (#ballbyballResult, 1:1 with chunk_403) -->
  <div id="ballbyballResult" tabindex="-1" aria-labelledby="ballbyballResult" aria-hidden="true" class="modal fade">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bet-slip-head d-flex justify-content-between align-items-center" style="background: linear-gradient(180deg, #2e4b5e 0%, #243a48 100%); color: #fff; padding: 10px;">
          <h5 id="exampleModalLabel" class="modal-title m-0" style="font-size: 14px;">
            <b style="text-transform: capitalize;" id="bbbModalEventName">Ball By Ball</b>
          </h5>
          <button type="button" data-bs-dismiss="modal" aria-label="Close" class="close btn text-white p-0 m-0" style="font-size: 22px; font-weight: 700; line-height: 1;" onclick="BBBGame.stopModalVideo()">×</button>
        </div>
        <div class="modal-body p-0" style="background: #fff;">
          <div style="margin-bottom: 20px;">
            <div class="modal-title p-2">
              <h6 class="text-end round-id m-0" style="font-size: 13px; font-weight: 700; color: #243a48;">
                <b>Round Id:</b> <span id="bbbModalRoundId">{{ $exMarketId }}</span>
              </h6>
            </div>
            <div class="col-12 paati_boxs" style="text-align: center; padding: 5px 0;">
              <div class="resultVideo" style="background: #000;">
                <video id="videoballbyballResult" width="100%" autoplay playsinline controls loop muted preload="auto" class="w-100"></video>
              </div>
            </div>
            <div class="clearfix" style="border-top: 1px solid #ccc;"></div>
            <div class="my-3 w-100 d-flex justify-content-center">
              <span class="bg-success pt-2 pb-2 ps-3 pe-3 text-white" style="border-radius: 3px;">
                <b id="bbbModalWinnerText">0 RUNS</b>
                <i class="fa fa-trophy winner-icon text-white ms-2"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- 4. Ball by Ball Market Rules Modal (#ballByBallRulesModal, 1:1 with chunk_403) -->
  <div id="ballByBallRulesModal" tabindex="-1" role="dialog" aria-labelledby="ballByBallRulesModalLabel" aria-hidden="true" class="modal fade">
    <div role="document" class="modal-dialog modal-md">
      <div class="modal-content rules-wrap">
        <div class="rules-content" style="max-height: 80vh; overflow-y: auto;">
          <div class="ball-market-rules">
            <div class="ball-market-header">
              <h2>Ball by Ball Market Rules</h2>
              <p>These rules apply to each individual delivery and define how every available option will be settled.</p>
            </div>
            <h3 class="section-title">Available Options Rules</h3>
            <div class="rules-grid">
              <div class="rule-card">
                <strong><span class="rule-badge">0</span> 0 Runs</strong>
                <p>Wins if exactly 0 runs are scored on the ball. No extras and no wicket should occur.</p>
              </div>
              <div class="rule-card">
                <strong><span class="rule-badge">1</span> 1 Run</strong>
                <p>Wins if exactly 1 run is scored off the bat.</p>
              </div>
              <div class="rule-card">
                <strong><span class="rule-badge">2</span> 2 Runs</strong>
                <p>Wins if exactly 2 runs are scored off the bat.</p>
              </div>
              <div class="rule-card">
                <strong><span class="rule-badge">3</span> 3 Runs</strong>
                <p>Wins if exactly 3 runs are scored off the bat.</p>
              </div>
              <div class="rule-card">
                <strong><span class="rule-badge">4</span> 4 Runs</strong>
                <p>Wins only if a boundary four is scored.</p>
              </div>
              <div class="rule-card">
                <strong><span class="rule-badge">6</span> 6 Runs</strong>
                <p>Wins only if a six is scored.</p>
              </div>
              <div class="rule-card highlight-wicket">
                <strong><span class="rule-badge">W</span> Wicket</strong>
                <p>Wins if any wicket falls on the ball. Wicket overrides all other outcomes.</p>
              </div>
              <div class="rule-card highlight-extra">
                <strong><span class="rule-badge">EX</span> Extra Runs</strong>
                <p>Wins if the delivery results in any extras, including Wide, No Ball, Bye, or Leg Bye. This also applies when runs are scored along with extras, such as No Ball + 4, Wide + runs, or Bye + runs.</p>
              </div>
            </div>
            <div class="important-box">
              <h3>Video &amp; Scoreboard Disclaimer</h3>
              <p>Videos may be sourced from different broadcasters, which can result in delays or discrepancies in the scoreboard.</p>
              <p>In such cases, all bets will be settled strictly as per our platform rules and based on the video displayed on our system.</p>
              <p>Any delay or mismatch in external scoreboards will not affect the final result.</p>
            </div>
          </div>
        </div>
        <ul class="btn-wrap break">
          <li style="list-style: none;">
            <a data-bs-dismiss="modal" class="btnok">OK</a>
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  window.BBBGame = {
    eventId: '3544687543453',
    sportId: '4',
    currentMarketId: @json($exMarketId),
    marketStatus: @json($marketStatus),
    casinoFlag: 1,
    TIME_LIMIT: 0,
    timeLeft: 0,
    timerInterval: null,
    showBanner: false,
    result: false,
    resultDeclared: null,
    lastPlayedRoundId: '',
    lastSettledRoundId: '',
    videoTimer1: null,
    videoTimer2: null,
    selectedSelectionId: '',
    selectedRunnerName: '',
    selectedOdds: 0,
    selectedRowIdx: null,
    resultsArray: @json($resultsList),
    runnerBets: {}, // selectionId => [{stake, odds}]
    blobCache: {},
    preloadingUrls: {},
    playedUrls: [],

    videoPool: [
      { url: '/assets/ballbyball/ballbyball-1739792690909.mp4', duration: 11.5, runs: '4 RUNS' },
      { url: '/assets/ballbyball/ballbyball-1771769169151.mp4', duration: 11.5, runs: '1 RUNS' },
      { url: '/assets/ballbyball/ballbyball-1741938448485.mp4', duration: 11.0, runs: '0 RUNS' },
      { url: '/assets/ballbyball/ballbyball-1771380411499.mp4', duration: 10.6, runs: 'EXTRA RUNS' },
      { url: '/assets/ballbyball/ballbyball-1741880221245.mp4', duration: 12.0, runs: '4 RUNS' },
      { url: '/assets/ballbyball/ballbyball-1771041600943.mp4', duration: 11.1, runs: '0 RUNS' },
      { url: '/assets/ballbyball/ballbyball-1741878934721.mp4', duration: 11.5, runs: '1 RUNS' },
      { url: '/assets/ballbyball/ballbyball-1739716365799.mp4', duration: 8.2,  runs: '4 RUNS' },
      { url: '/assets/ballbyball/ballbyball-1771773140134.mp4', duration: 11.5, runs: '1 RUNS' },
      { url: '/assets/ballbyball/ballbyball-1739473116892.mp4', duration: 11.5, runs: '0 RUNS' },
      { url: '/assets/ballbyball/ballbyball-1771334071733.mp4', duration: 11.1, runs: '1 RUNS' },
      { url: '/assets/ballbyball/ballbyball-1771253542905.mp4', duration: 11.5, runs: '0 RUNS' },
      { url: '/assets/ballbyball/ballbyball-1741000016001.mp4', duration: 11.5, runs: '1 RUNS' },
      { url: '/assets/ballbyball/ballbyball-1770881394633.mp4', duration: 10.3, runs: '0 RUNS' },
      { url: '/assets/ballbyball/ballbyball-1740809277094.mp4', duration: 12.0, runs: '1 RUNS' },
      { url: '/assets/ballbyball/ballbyball-1740430795502.mp4', duration: 11.5, runs: '0 RUNS' },
      { url: '/assets/ballbyball/ballbyball-1740044897403.mp4', duration: 11.5, runs: '1 RUNS' },
      { url: '/assets/ballbyball/ballbyball-1739949510757.mp4', duration: 11.5, runs: '0 RUNS' },
      { url: '/assets/ballbyball/ballbyball-1739950941157.mp4', duration: 11.0, runs: '1 RUNS' },
      { url: '/assets/ballbyball/ballbyball-1741682789820.mp4', duration: 11.5, runs: '1 RUNS' },
      { url: '/assets/ballbyball/ballbyball-1771252859796.mp4', duration: 11.5, runs: '1 RUNS' },
      { url: '/assets/ballbyball/ballbyball-1740809424332.mp4', duration: 12.0, runs: '1 RUNS' },
      { url: '/assets/ballbyball/ballbyball-1740558318747.mp4', duration: 11.5, runs: '1 RUNS' },
      { url: '/assets/ballbyball/ballbyball-1740906991238.mp4', duration: 10.4, runs: '1 RUNS' }
    ],

    isPolling: false,
    isPlayingStream: false,

    init() {
      try {
        const saved = JSON.parse(sessionStorage.getItem('wb24_bbb_played_urls') || '[]');
        if (Array.isArray(saved)) this.playedUrls = saved;
      } catch (e) {}

      const initialGame = @json($game);
      this.applyGameData(initialGame, true);
      // Poll live state every 1.2s with non-overlapping guard
      setInterval(() => this.pollState(), 1200);
    },

    preloadVideo(url) {
      if (!url || this.blobCache[url] || this.preloadingUrls[url]) return;
      this.preloadingUrls[url] = true;
      fetch(url)
        .then(r => (r.ok ? r.blob() : null))
        .then(blob => {
          if (blob && blob.size > 10000) {
            this.blobCache[url] = URL.createObjectURL(blob);
          }
        })
        .catch(() => {})
        .finally(() => {
          delete this.preloadingUrls[url];
        });
    },

    pickNonRepeatingVideo(preferredUrl, preferredDur, preferredRuns) {
      if (preferredUrl && !this.playedUrls.includes(preferredUrl)) {
        this.recordPlayedUrl(preferredUrl);
        return {
          url: preferredUrl,
          duration: parseFloat(preferredDur) || 11.5,
          runs: preferredRuns || '0 RUNS'
        };
      }

      let unplayed = this.videoPool.filter(v => !this.playedUrls.includes(v.url));
      if (unplayed.length === 0) {
        const lastUrl = this.playedUrls[this.playedUrls.length - 1] || '';
        this.playedUrls = lastUrl ? [lastUrl] : [];
        unplayed = this.videoPool.filter(v => v.url !== lastUrl);
      }

      const pick = unplayed[Math.floor(Math.random() * unplayed.length)] || this.videoPool[0];
      this.recordPlayedUrl(pick.url);
      return {
        url: pick.url,
        duration: pick.duration || 11.5,
        runs: pick.runs || '0 RUNS'
      };
    },

    recordPlayedUrl(url) {
      if (!url) return;
      this.playedUrls.push(url);
      if (this.playedUrls.length >= this.videoPool.length) {
        this.playedUrls = this.playedUrls.slice(-15);
      }
      try {
        sessionStorage.setItem('wb24_bbb_played_urls', JSON.stringify(this.playedUrls));
      } catch (e) {}
    },

    async pollState() {
      if (this.isPolling) return;
      this.isPolling = true;
      try {
        const res = await fetch(`/api/exchange/ballbyball/state?eventId=${this.eventId}&sportId=${this.sportId}`, {
          headers: { 'Accept': 'application/json' }
        });
        if (!res.ok) return;
        const json = await res.json();
        if (json && json.data) {
          this.applyGameData(json.data, false);
        }
      } catch (e) {
        // Silent retry on next tick
      } finally {
        this.isPolling = false;
      }
    },

    async pollResults() {
      try {
        const res = await fetch(`/api/exchange/ballbyball/results?eventId=${this.eventId}`, {
          headers: { 'Accept': 'application/json' }
        });
        if (!res.ok) return;
        const json = await res.json();
        if (json && Array.isArray(json.data)) {
          this.resultsArray = json.data;
          this.renderResultsList();
        }
      } catch (e) {}
    },

    nextVideoHint: null,

    applyGameData(game, isFirstLoad) {
      if (!game || !game.oddsData) return;

      if (game.nextVideoUrl) {
        this.nextVideoHint = {
          url: game.nextVideoUrl,
          duration: parseFloat(game.nextVideoDuration) || 11.5,
          runs: game.nextVideoRuns || '0 RUNS'
        };
        this.preloadVideo(game.nextVideoUrl);
      }

      const newMarketId = String(game.exMarketId || game.marketId || '');
      if (newMarketId && newMarketId !== this.currentMarketId) {
        this.currentMarketId = newMarketId;
        this.casinoFlag = 1;
        this.showBanner = false;
        this.result = false;
        this.isPlayingStream = false;
        this.runnerBets = {};
        this.renderPlPreviews();
        const rEl = document.getElementById('bbbRoundIdText');
        if (rEl) rEl.textContent = newMarketId;
        this.pollResults();
      }

      const odds = game.oddsData;
      this.marketStatus = odds.status || 'ONLINE';

      // Update runner odds, volumes & suspended locks
      const runners = odds.runners || [];
      runners.forEach(r => {
        const selId = String(r.selectionId);
        const price = r.price?.back?.[0]?.price ?? 0;
        const size = r.price?.back?.[0]?.size ?? 0;
        const oddEl = document.getElementById(`bbbOdd_${selId}`);
        const volEl = document.getElementById(`bbbVol_${selId}`);
        const boxEl = document.getElementById(`bbbRunnerBox_${selId}`);
        if (oddEl && price) oddEl.textContent = price;
        if (volEl) volEl.textContent = size;
        if (boxEl) {
          const isSusp = (this.marketStatus === 'SUSPENDED' || this.isPlayingStream || r.status === 'SUSPENDED' || r.status === 'SUSPEND');
          boxEl.classList.toggle('suspended', isSusp);
        }
      });

      // Exact gameManager() logic from chunk_403.1fb5a77138a09c4f.js
      if (this.marketStatus === 'SUSPENDED') {
        this.TIME_LIMIT = 0;
        this.timeLeft = 0;
        this.casinoFlag = 1;
        if (this.timerInterval) {
          clearInterval(this.timerInterval);
          this.timerInterval = null;
        }
        const timerGroup = document.getElementById('bbbTimerGroup');
        if (timerGroup) timerGroup.classList.add('d-none');

        if (this.selectedSelectionId) {
          this.closeBetSlip();
        }

        const videoObj = odds.video || {};
        let videoUrl = videoObj.url || '';
        if (videoUrl) {
          videoUrl = videoUrl.replace('{$domain}', 'https://winbook24.com');
        }

        if (this.lastPlayedRoundId !== this.currentMarketId) {
          this.lastPlayedRoundId = this.currentMarketId;
          this.showBanner = false;
          const chosen = this.pickNonRepeatingVideo(videoUrl, videoObj.duration, videoObj.runs);
          this.playBallStream(chosen.url, chosen.duration, chosen.runs, this.currentMarketId);
        } else if (!videoUrl && this.showBanner) {
          this.showPoster();
        }
      } else if (this.marketStatus === 'ONLINE') {
        if (!this.isPlayingStream && this.lastPlayedRoundId !== this.currentMarketId) {
          this.showBanner = false;
          this.clearVideoTimers();
          this.hideResultOverlay();
          this.showPoster();

          if (this.casinoFlag === 1) {
            this.result = false;
            this.casinoFlag = 2;
            this.TIME_LIMIT = parseInt(odds.seconds, 10) || 20;
            this.timeLeft = this.TIME_LIMIT;
            document.documentElement.style.setProperty('--timerValue', '20s');
            this.startCountdownTimer();
          }
        }
      }
    },

    triggerStreamOnTimerEnd() {
      if (this.lastPlayedRoundId === this.currentMarketId) {
        this.pollState();
        return;
      }
      this.marketStatus = 'SUSPENDED';
      this.casinoFlag = 1;
      const timerGroup = document.getElementById('bbbTimerGroup');
      if (timerGroup) timerGroup.classList.add('d-none');
      document.querySelectorAll('.js-bbb-runner').forEach(el => el.classList.add('suspended'));
      if (this.selectedSelectionId) {
        this.closeBetSlip();
      }

      this.lastPlayedRoundId = this.currentMarketId;
      this.showBanner = false;
      const hint = this.nextVideoHint || {};
      const chosen = this.pickNonRepeatingVideo(hint.url || '', hint.duration || 11.5, hint.runs || '0 RUNS');
      this.playBallStream(chosen.url, chosen.duration, chosen.runs, this.currentMarketId);
      this.pollState();
    },

    startCountdownTimer() {
      if (this.timerInterval) {
        clearInterval(this.timerInterval);
      }
      const timerGroup = document.getElementById('bbbTimerGroup');
      if (timerGroup) {
        // Restart CSS keyframe animation on timer hands synced to elapsed time out of 20s
        const h1 = document.getElementById('bbbTimerHand1');
        const h2 = document.getElementById('bbbTimerHand2');
        const elapsedSec = Math.max(0, 20 - (this.timeLeft || 20));
        if (h1 && h2) {
          h1.style.animation = 'none';
          h2.style.animation = 'none';
          void h1.offsetWidth;
          h1.style.animation = '';
          h2.style.animation = '';
          h1.style.animationDelay = `-${elapsedSec}s`;
          h2.style.animationDelay = `-${elapsedSec}s`;
        }
        timerGroup.classList.remove('d-none');
      }
      this.renderTimerFace();

      this.timerInterval = setInterval(() => {
        this.timeLeft = this.timeLeft - 1;
        if (this.timeLeft <= 0) {
          this.timeLeft = 0;
          clearInterval(this.timerInterval);
          this.timerInterval = null;
          this.renderTimerFace();
          this.triggerStreamOnTimerEnd();
          return;
        }
        this.renderTimerFace();
      }, 1000);
    },

    renderTimerFace() {
      const pEl = document.getElementById('lazy');
      const faceSpan = document.getElementById('bbbTimerFaceSpan');
      const h1 = document.getElementById('bbbTimerHand1');
      const h2 = document.getElementById('bbbTimerHand2');
      const val = Math.max(0, this.timeLeft);
      if (pEl) {
        pEl.textContent = val;
        pEl.classList.toggle('singleDigit', val < 10);
        pEl.classList.toggle('heart', val < 6);
      }
      if (faceSpan) {
        faceSpan.classList.toggle('heart', val < 6);
      }
      if (h1) h1.classList.toggle('spanColorRed', val < 6);
      if (h2) h2.classList.toggle('spanColorRed', val < 6);
    },

    playBallStream(url, durationSec, runsText, roundId) {
      this.clearVideoTimers();
      this.hideResultOverlay();
      const videoEl = document.getElementById('videoBallByball');
      const posterEl = document.getElementById('bbbPosterImg');
      if (!videoEl) return;

      this.isPlayingStream = true;
      const playSrc = this.blobCache[url] || url;

      if (posterEl) posterEl.classList.add('d-none');
      videoEl.classList.remove('d-none');
      videoEl.muted = true;
      videoEl.src = playSrc;
      videoEl.load();

      const playPromise = videoEl.play();
      if (playPromise && typeof playPromise.catch === 'function') {
        playPromise.catch(() => {
          videoEl.muted = true;
          videoEl.play().catch(() => {});
        });
      }

      const revealDelayMs = Math.max(2000, (durationSec - 2.2) * 1000);
      this.videoTimer1 = setTimeout(() => {
        this.showResultOverlay(runsText);
        this.settleRoundBets(runsText, roundId);
        this.pollResults();
        this.videoTimer2 = setTimeout(() => {
          this.isPlayingStream = false;
          this.hideResultOverlay();
          this.showBanner = true;
          this.showPoster();
          this.pollState();
        }, 2000);
      }, revealDelayMs);
    },

    showPoster() {
      const videoEl = document.getElementById('videoBallByball');
      const posterEl = document.getElementById('bbbPosterImg');
      if (videoEl) {
        videoEl.pause();
        videoEl.classList.add('d-none');
      }
      if (posterEl) {
        posterEl.classList.remove('d-none');
      }
    },

    showResultOverlay(runsText) {
      this.result = true;
      this.resultDeclared = runsText || '0 RUNS';
      const ov = document.getElementById('bbbResultOverlay');
      const txt = document.getElementById('bbbResultOverlayText');
      if (txt) txt.textContent = this.resultDeclared;
      if (ov) ov.classList.remove('d-none');
    },

    hideResultOverlay() {
      this.resultDeclared = null;
      const ov = document.getElementById('bbbResultOverlay');
      if (ov) ov.classList.add('d-none');
    },

    clearVideoTimers() {
      if (this.videoTimer1) {
        clearTimeout(this.videoTimer1);
        this.videoTimer1 = null;
      }
      if (this.videoTimer2) {
        clearTimeout(this.videoTimer2);
        this.videoTimer2 = null;
      }
    },

    onClickRunner(selectionId, runnerName, rowIdx) {
      const boxEl = document.getElementById(`bbbRunnerBox_${selectionId}`);
      if (this.marketStatus === 'SUSPENDED' || (boxEl && boxEl.classList.contains('suspended'))) {
        return;
      }

      const oddEl = document.getElementById(`bbbOdd_${selectionId}`);
      const oddsVal = parseFloat(oddEl ? oddEl.textContent : '2.0') || 2.0;

      this.selectedSelectionId = selectionId;
      this.selectedRunnerName = runnerName;
      this.selectedOdds = oddsVal;
      this.selectedRowIdx = rowIdx;

      document.querySelectorAll('.js-bbb-runner').forEach(el => el.classList.remove('selected_Cell'));
      if (boxEl) boxEl.classList.add('selected_Cell');

      // Show inline betslip under the clicked row (1:1 with chunk_403)
      for (let i = 0; i < 4; i++) {
        const rContainer = document.getElementById(`bbbInlineSlipRow_${i}`);
        if (rContainer) rContainer.classList.add('d-none');
      }
      const targetRow = document.getElementById(`bbbInlineSlipRow_${rowIdx}`);
      const widget = document.getElementById('bbbInlineSlipWidget');
      if (targetRow && widget) {
        targetRow.appendChild(widget);
        widget.classList.remove('d-none');
        targetRow.classList.remove('d-none');
      }

      const nameEl = document.getElementById('bbbSlipRunnerName');
      const oddsInput = document.getElementById('bbbSlipOdds');
      const stakeInput = document.getElementById('bbbSlipStake');
      if (nameEl) nameEl.textContent = runnerName;
      if (oddsInput) oddsInput.value = oddsVal.toFixed(2);
      if (stakeInput && !stakeInput.value) stakeInput.value = 100;
      this.updateSlipCalc();
    },

    setStake(val) {
      const stakeInput = document.getElementById('bbbSlipStake');
      if (stakeInput) {
        stakeInput.value = val;
        this.updateSlipCalc();
      }
    },

    updateSlipCalc() {
      const stake = parseFloat(document.getElementById('bbbSlipStake')?.value || '0') || 0;
      const odds = this.selectedOdds || 0;
      const profit = stake > 0 && odds > 1 ? stake * (odds - 1) : 0;
      const pEl = document.getElementById('bbbSlipProfit');
      const lEl = document.getElementById('bbbSlipLiability');
      if (pEl) pEl.textContent = profit.toFixed(2);
      if (lEl) lEl.textContent = stake.toFixed(2);
      this.renderPlPreviews(stake, this.selectedSelectionId, odds);
    },

    closeBetSlip() {
      this.selectedSelectionId = '';
      this.selectedRunnerName = '';
      this.selectedRowIdx = null;
      document.querySelectorAll('.js-bbb-runner').forEach(el => el.classList.remove('selected_Cell'));
      for (let i = 0; i < 4; i++) {
        const rContainer = document.getElementById(`bbbInlineSlipRow_${i}`);
        if (rContainer) rContainer.classList.add('d-none');
      }
      const widget = document.getElementById('bbbInlineSlipWidget');
      if (widget) widget.classList.add('d-none');
      this.renderPlPreviews(0, '', 0);
    },

    renderPlPreviews(previewStake = 0, previewSelId = '', previewOdds = 0) {
      const allSelIds = ['304350101','304350102','304350103','304350104','304350105','304350106','304350107','304350108','304350109'];
      const hasPlacedBets = Object.keys(this.runnerBets).length > 0;
      if (!hasPlacedBets && (!previewStake || !previewSelId)) {
        allSelIds.forEach(id => {
          const wrap = document.getElementById(`bbbPlWrap_${id}`);
          if (wrap) wrap.innerHTML = '';
        });
        return;
      }

      allSelIds.forEach(selId => {
        let net = 0;
        Object.entries(this.runnerBets).forEach(([bSelId, list]) => {
          list.forEach(b => {
            if (bSelId === selId) net += b.stake * (b.odds - 1);
            else net -= b.stake;
          });
        });
        if (previewStake > 0 && previewSelId) {
          if (previewSelId === selId) net += previewStake * (previewOdds - 1);
          else net -= previewStake;
        }
        const wrap = document.getElementById(`bbbPlWrap_${selId}`);
        if (!wrap) return;
        if (Math.abs(net) < 0.01) {
          wrap.innerHTML = '';
        } else {
          const fmt = '(' + Math.abs(net).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ')';
          wrap.innerHTML = net >= 0
            ? `<span class="plus-book">${fmt}</span>`
            : `<span class="minus-book">${fmt}</span>`;
        }
      });
    },

    async submitBet() {
      if (this.marketStatus === 'SUSPENDED' || !this.selectedSelectionId) {
        if (window.WB24 && WB24.showToast) WB24.showToast('Market is suspended!', 'error');
        return;
      }
      const stake = parseFloat(document.getElementById('bbbSlipStake')?.value || '0') || 0;
      if (stake < 100) {
        if (window.WB24 && WB24.showToast) WB24.showToast('Minimum stake is 100 PTI', 'error');
        return;
      }

      const btn = document.getElementById('bbbSlipSubmitBtn');
      if (btn) btn.disabled = true;

      try {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const res = await fetch('/api/exchange/place-bet', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf
          },
          body: JSON.stringify({
            event_id: this.eventId,
            event_name: 'Ball By Ball',
            market_name: 'Runs',
            selection_name: this.selectedRunnerName,
            bet_type: 'BACK',
            odds: this.selectedOdds,
            stake: stake,
            category: 'sports'
          })
        });
        const data = await res.json();
        if (data.status) {
          if (!this.runnerBets[this.selectedSelectionId]) {
            this.runnerBets[this.selectedSelectionId] = [];
          }
          this.runnerBets[this.selectedSelectionId].push({ stake: stake, odds: this.selectedOdds });
          if (data.wallet && window.WB24 && WB24.syncHeaderWallet) {
            WB24.syncHeaderWallet(data.wallet);
          }
          if (window.WB24 && WB24.showToast) {
            WB24.showToast(data.message || 'Bet placed on ' + this.selectedRunnerName, 'success');
          }
          this.closeBetSlip();
        } else {
          if (window.WB24 && WB24.showToast) {
            WB24.showToast(data.message || 'Could not place bet', 'error');
          }
        }
      } catch (e) {
        if (window.WB24 && WB24.showToast) WB24.showToast('Please login to place bets', 'error');
      } finally {
        if (btn) btn.disabled = false;
      }
    },

    async settleRoundBets(runsText, roundId) {
      if (this.lastSettledRoundId === roundId) return;
      this.lastSettledRoundId = roundId;
      try {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const res = await fetch('/api/exchange/ballbyball/settle', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf
          },
          body: JSON.stringify({
            winner_name: runsText,
            market_id: roundId
          })
        });
        const data = await res.json();
        if (data && data.status && data.wallet && window.WB24 && WB24.syncHeaderWallet) {
          WB24.syncHeaderWallet(data.wallet);
        }
      } catch (e) {}
    },

    renderResultsList() {
      const ul = document.getElementById('bbbRecentResultList');
      if (!ul || !Array.isArray(this.resultsArray)) return;
      ul.innerHTML = this.resultsArray.map((item, idx) => {
        const rStr = String(item.result || '0 RUNS').trim().toUpperCase();
        const ch = rStr.slice(0, 1) || '0';
        let cls = 'back-1';
        if (rStr === '4 RUNS') cls = 'back-4';
        else if (rStr === '6 RUNS') cls = 'back-6';
        else if (rStr === 'WICKET') cls = 'back-wicket';
        return `<li class="casino-a sportsbookTile ${cls}" onclick="BBBGame.openResultModal(${idx})">
          <a href="javascript:void(0)" class="number text-dark">${ch}</a>
        </li>`;
      }).join('');
    },

    openResultModal(idx) {
      const item = this.resultsArray[idx];
      if (!item) return;
      const mEvent = document.getElementById('bbbModalEventName');
      const mRound = document.getElementById('bbbModalRoundId');
      const mWin = document.getElementById('bbbModalWinnerText');
      const vEl = document.getElementById('videoballbyballResult');

      if (mEvent) mEvent.textContent = item.eventName || 'Ball By Ball';
      if (mRound) mRound.textContent = item.marketId || '';
      if (mWin) mWin.textContent = item.result || '0 RUNS';

      let streamUrl = item.streamUrl || '';
      if (streamUrl) {
        streamUrl = streamUrl.replace('{$domain}', 'https://winbook24.com');
      }
      if (vEl && streamUrl) {
        vEl.src = this.blobCache[streamUrl] || streamUrl;
        vEl.muted = true;
        vEl.load();
        vEl.play().catch(() => {});
      }

      const modalEl = document.getElementById('ballbyballResult');
      if (modalEl && window.bootstrap) {
        const m = bootstrap.Modal.getOrCreateInstance(modalEl);
        m.show();
      }
    },

    stopModalVideo() {
      const vEl = document.getElementById('videoballbyballResult');
      if (vEl) vEl.pause();
    }
  };

  document.addEventListener('DOMContentLoaded', () => {
    BBBGame.init();
  });
</script>
@endpush
