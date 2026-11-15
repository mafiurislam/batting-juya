@extends('layouts.app')
@section('title', $event->event_name . ' - winbook24')

@push('styles')
<style>
  .fm-banner {
    background-size: cover;
    background-repeat: no-repeat;
    background-position: center;
    color: #fff;
    height: 100px;
    padding: 6px 12px 12px 12px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    margin-bottom: 6px;
    position: relative;
    overflow: hidden;
    font-family: Arial, sans-serif;
  }
  .fm-banner.sport-4 { background-image: url("/assets/banners/cricket.jpg"); }
  .fm-banner.sport-1 { background-image: url("/assets/banners/soccer.jpg"); }
  .fm-banner.sport-2 { background-image: url("/assets/banners/tennis.jpg"); }
  .fm-banner.sport-7, .fm-banner.sport-4339 { background-image: url("/assets/banners/horseRacing.jpg"); }

  .fm-game-status {
    background: #000;
    color: #fff;
    font-weight: 800;
    font-size: 11px;
    padding: 2px 8px;
    border-radius: 3px;
    letter-spacing: 0.4px;
    z-index: 2;
  }
  .fm-game-time {
    font-size: 12px;
    font-weight: 700;
    color: #fff;
    z-index: 2;
  }
  .fm-bet-started {
    font-size: 13px;
    font-weight: 700;
    color: #7ed321;
    text-align: center;
    z-index: 2;
  }

  /* Official Powered By Universe Score Live Scoreboard (1:1 with winbook24.com) */
  .wb-universe-scorecard {
    min-height: 150px;
    background-color: #000000;
    color: #ffffff;
    font-family: Arial, sans-serif;
    display: flex;
    flex-direction: column;
    justify-content: space-around;
    margin-bottom: 6px;
    overflow: hidden;
    user-select: none;
  }
  .wb-sc-row {
    padding: 2px 10px;
    border-bottom: 1px solid #80808085;
    display: flex;
    align-items: center;
    font-size: 12px;
    font-weight: 700;
    min-height: 28px;
  }
  .wb-sc-row:last-child {
    border-bottom: none;
  }
  .wb-sc-head {
    font-size: 11px;
    font-weight: 700;
  }
  .wb-sc-brand {
    color: #00a826;
    font-weight: 700;
    margin-left: 3px;
  }
  .wb-sc-col-hdr {
    color: #f8e71c;
    font-weight: 700;
    text-align: center;
  }
  .wb-sc-score-green {
    color: #7ed321;
    font-weight: 700;
  }
  .wb-sc-ball {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    font-size: 10px;
    font-weight: 700;
    color: #fff;
    margin: 0 2px;
    background: #3a3a3a;
  }
  .wb-sc-ball.ball-4 { background: #27ae60; }
  .wb-sc-ball.ball-6 { background: #8e44ad; }
  .wb-sc-ball.ball-w { background: #dc3545; }
  .wb-sc-marquee {
    white-space: nowrap;
    overflow: hidden;
    width: 100%;
  }
  .wb-sc-marquee-inner {
    display: inline-block;
    animation: wbScMarquee 16s linear infinite;
  }
  @keyframes wbScMarquee {
    0% { transform: translateX(40%); }
    100% { transform: translateX(-100%); }
  }

  /* Filter Pills Bar */
  .fm-filter-bar {
    background: #e1e4e8;
    padding: 6px 8px;
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    gap: 6px;
    overflow-x: auto;
  }
  .fm-filter-pill {
    background: #ffffff;
    color: #1e1e1e;
    border: 1px solid #243a48;
    border-radius: 16px;
    padding: 3px 14px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    white-space: nowrap;
    text-decoration: none;
  }
  .fm-filter-pill.active,
  .fm-filter-pill:hover {
    background: linear-gradient(-180deg, #2E4B5E 0%, #243A48 82%);
    color: #ffffff;
  }

  /* Market Card Header */
  .fm-card {
    background: #fff;
    border: 1px solid #c8ced3;
    margin-bottom: 8px;
  }
  .fm-card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #c8ced3;
    background: #fff;
    padding-right: 10px;
  }
  .fm-head-tab {
    background: linear-gradient(-180deg, #2E4B5E 0%, #243A48 82%);
    color: #fff;
    font-weight: 700;
    font-size: 13px;
    padding: 5px 14px;
    border-radius: 0 15px 0 0;
    min-width: 145px;
    display: inline-flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
  }
  .fm-cashout-btn {
    background: #e9ecef;
    border: 1px solid #adb5bd;
    color: #212529;
    font-weight: 700;
    font-size: 11px;
    padding: 2px 9px;
    border-radius: 4px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    cursor: pointer;
  }
  .fm-cashout-dot {
    width: 9px;
    height: 9px;
    border-radius: 50%;
    background: #2bd13f;
    display: inline-block;
    box-shadow: 0 0 4px #2bd13f;
  }
  .fm-matched-txt {
    font-size: 12px;
    color: #23282c;
  }

  /* Odds Table Rows */
  .fm-subhead {
    display: flex;
    align-items: center;
    border-bottom: 1px solid #7e97a7;
    font-size: 12px;
    font-weight: 700;
    background: #fff;
  }
  .fm-subhead .wb-minmax-pill {
    white-space: nowrap;
    display: inline-block;
    background: #beddf4;
    color: #24577a;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 10.5px;
    font-weight: 700;
  }
  .fm-runner-row {
    display: flex;
    align-items: center;
    border-bottom: 1px solid #7e97a7;
    position: relative;
    min-height: 42px;
    background: #fff;
  }
  .fm-runner-name {
    flex: 1;
    padding: 4px 10px;
    font-weight: 700;
    font-size: 13px;
    color: #1e1e1e;
    display: flex;
    flex-direction: column;
    justify-content: center;
    line-height: 1.25;
  }
  .fm-runner-title {
    color: #1e1e1e;
    font-weight: 700;
    font-size: 13px;
  }
  .fm-runner-pl {
    font-size: 12px;
    font-weight: 700;
    line-height: 1.2;
    margin-top: 2px;
  }
  .fm-runner-pl.pl-pos {
    color: #1b8c00 !important;
  }
  .fm-runner-pl.pl-neg {
    color: #d0021b !important;
  }
  .fm-odds-6 {
    width: 348px;
    display: flex;
    gap: 1px;
    position: relative;
    flex-shrink: 0;
  }
  .fm-odd-cell {
    width: 57px;
    height: 41px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #1e1e1e;
    text-decoration: none;
    line-height: 1.15;
    user-select: none;
  }
  .fm-odd-cell strong {
    font-size: 12px;
    font-weight: 700;
  }
  .fm-odd-cell small {
    font-size: 10px;
    font-weight: 400;
    color: #222;
    margin-top: 1px;
  }
  .fm-odd-cell.selected-odd-back {
    background: #1a8ee9 !important;
    color: #ffffff !important;
    box-shadow: inset 0 1px 4px rgba(0, 0, 0, 0.35);
  }
  .fm-odd-cell.selected-odd-back strong,
  .fm-odd-cell.selected-odd-back small {
    color: #ffffff !important;
  }
  .fm-odd-cell.selected-odd-lay {
    background: #e05b78 !important;
    color: #ffffff !important;
    box-shadow: inset 0 1px 4px rgba(0, 0, 0, 0.35);
  }
  .fm-odd-cell.selected-odd-lay strong,
  .fm-odd-cell.selected-odd-lay small {
    color: #ffffff !important;
  }
  .fm-suspend-overlay {
    position: absolute;
    inset: 0;
    background: rgba(20, 26, 33, 0.78);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 4;
    pointer-events: all;
    cursor: not-allowed;
  }
  .fm-suspend-overlay span {
    color: #ff3333;
    font-weight: 700;
    font-size: 13px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
  }
  .fm-suspend-overlay span.fm-ballrun-badge {
    color: #ffc107 !important;
    font-style: italic !important;
    text-transform: none !important;
  }
  .wb-bookmaker-table .fm-runner-row,
  .wb-bookmaker-table .fm-runner-name {
    background: #f8f7ee !important;
  }

  /* Inline Bet Slip inside Match Odds / Bookmaker / Fancy (1:1 Winbook24 style) */
  .fm-inline-slip {
    background: #beddf4;
    border-bottom: 1px solid #7e97a7;
    padding: 8px 10px;
  }
  .fm-inline-slip.lay-mode {
    background: #f3dce2;
  }
  .fm-slip-btn-cancel {
    background: linear-gradient(180deg, #ffffff 0%, #e6e6e6 100%);
    border: 1px solid #8a99a8;
    border-radius: 5px;
    color: #1e1e1e;
    font-weight: 700;
    font-size: 13px;
    height: 36px;
    width: 100%;
    cursor: pointer;
    transition: filter 0.12s;
  }
  .fm-slip-btn-cancel:hover {
    filter: brightness(0.96);
  }
  .fm-slip-btn-place {
    background: linear-gradient(180deg, #2b4566 0%, #152438 100%);
    border: 1px solid #0d1724;
    border-radius: 5px;
    color: #ffffff;
    font-weight: 700;
    font-size: 14px;
    height: 36px;
    width: 100%;
    cursor: pointer;
    transition: filter 0.12s;
  }
  .fm-slip-btn-place:hover {
    filter: brightness(1.08);
  }
  .fm-slip-stepper {
    display: flex;
    align-items: stretch;
    height: 36px;
    border: 1px solid #8a99a8;
    border-radius: 5px;
    overflow: hidden;
    background: #ffffff;
  }
  .fm-step-btn {
    width: 40px;
    background: linear-gradient(180deg, #ffffff 0%, #e6e6e6 100%);
    border: none;
    color: #1f5f8b;
    font-size: 22px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    padding: 0;
    line-height: 1;
    user-select: none;
  }
  .fm-step-btn:hover {
    background: #dce4ec;
  }
  .fm-step-input {
    flex: 1;
    width: 100%;
    min-width: 0;
    border: none;
    border-left: 1px solid #b8c4ce;
    border-right: 1px solid #b8c4ce;
    text-align: center;
    font-weight: 700;
    font-size: 13px;
    color: #1e1e1e;
    background: #ffffff;
    outline: none;
  }
  .fm-stake-chip {
    background: #f5f7f8;
    border: 1px solid #243a48;
    border-radius: 4px;
    color: #000;
    font-weight: 600;
    font-size: 12px;
    height: 28px;
    width: 100%;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    text-decoration: none;
  }
  .fm-stake-chip:hover,
  .fm-stake-chip.active-stake {
    background: #dfeaf4;
    font-weight: 700;
  }

  /* To Win The Match Green Sportsbook Box */
  .fm-sb-green-body {
    background-image: linear-gradient(90deg, #82dda6cc, #82ddb059 49%, #82dda6cc) !important;
    padding: 8px 12px;
  }
  .fm-sb-pill {
    width: 100%;
    max-width: 310px;
    min-height: 56px;
    background: linear-gradient(to bottom, #c9bf34, #f7eb4c, #c9bf34) !important;
    border-radius: 10px;
    display: flex;
    align-items: center;
    cursor: pointer;
    color: #000 !important;
    text-decoration: none !important;
    box-shadow: 0 2px 5px rgba(0,0,0,0.14);
  }
  .fm-sb-pill-left {
    width: 40%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
  }
  .fm-sb-pill-right {
    width: 60%;
    min-height: 56px;
    border-left: 3px dashed #000;
    display: flex;
    align-items: center;
    padding-left: 12px;
    font-weight: 800;
    font-size: 13px;
  }

  .fm-subhead-right {
    width: 348px;
    display: flex;
    text-align: center;
    align-items: center;
  }
  .fm-mbl-minmax {
    display: none;
  }

  @media (max-width: 767px) {
    .fm-banner {
      height: 82px;
      padding: 6px 8px 8px 8px;
    }
    .wb-universe-scorecard {
      min-height: 132px;
    }
    .wb-sc-row {
      padding: 3px 6px;
      font-size: 11px;
      min-height: 25px;
    }
    .wb-sc-ball {
      width: 18px;
      height: 18px;
      font-size: 9.5px;
      margin: 0 1px;
    }
    .fm-card-head {
      padding-right: 6px;
      flex-wrap: wrap;
      gap: 4px;
    }
    .fm-head-tab {
      font-size: 12px;
      padding: 4px 10px;
      min-width: 120px;
    }
    .fm-matched-txt {
      font-size: 11px;
    }
    /* Match Odds & Bookmaker Subheader on Mobile: Min/Max on left, Back (3 cols) & Lay (3 cols) on right */
    .fm-subhead {
      padding: 3px 4px;
      justify-content: space-between;
    }
    .fm-mbl-minmax {
      display: inline-block;
      padding-left: 4px;
    }
    .fm-subhead-right {
      width: 100%;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 2px;
    }
    .fm-subhead-right .box-hide-mbl {
      display: none !important;
    }
    .fm-subhead-right .fm-subhead-back,
    .fm-subhead-right .fm-subhead-lay {
      width: 100% !important;
      border-radius: 4px !important;
      font-size: 11.5px;
      padding: 3px 0 !important;
    }

    /* Runner Row on Mobile: Full-width Team Name + P/L on top, all 6 Back/Lay odds in a 6-column grid below */
    .fm-runner-row {
      flex-wrap: wrap;
      padding-bottom: 3px;
    }
    .fm-runner-name {
      width: 100%;
      flex: 0 0 100%;
      flex-direction: row;
      justify-content: space-between;
      align-items: center;
      padding: 6px 8px 3px 8px;
      background: rgba(248, 250, 252, 0.7);
    }
    .fm-runner-title {
      font-size: 12.5px;
    }
    .fm-runner-pl {
      margin-top: 0;
      font-size: 12px;
    }
    .fm-odds-6 {
      width: 100%;
      display: grid;
      grid-template-columns: repeat(6, 1fr);
      gap: 2px;
      padding: 2px 4px;
    }
    .fm-odds-6 .box-hide-mbl {
      display: flex !important;
    }
    .fm-odd-cell {
      width: 100%;
      height: 39px;
      border-radius: 3px;
    }
    .fm-odd-cell strong {
      font-size: 11.5px;
    }
    .fm-odd-cell small {
      font-size: 9px;
    }

    /* To Win The Match Pills on Mobile */
    .fm-sb-green-body {
      padding: 6px;
    }
    .fm-sb-pill {
      min-height: 48px;
    }
    .fm-sb-pill-right {
      min-height: 48px;
      padding-left: 8px;
      font-size: 11.5px;
    }

    /* Fancy Bet & Sportsbook Tabs on Mobile */
    .wb-market-tabs-card .fancy_bet_tab-wrap {
      justify-content: flex-start;
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
      scrollbar-width: none;
      padding: 0 6px;
    }
    .wb-market-tabs-card .fancy_bet_tab-wrap::-webkit-scrollbar {
      display: none;
    }
    .wb-market-tabs-card .special_bets-tab {
      margin: 3px 0;
      flex-wrap: nowrap;
      justify-content: flex-start;
    }
    .wb-market-tabs-card .special_bets-tab a {
      white-space: nowrap;
      min-width: auto;
      padding: 0 8px;
      font-size: 11.5px;
    }
    .js-fancy-row {
      flex-wrap: nowrap;
      padding: 6px !important;
    }
    .js-fancy-row .fm-odd-cell {
      width: 52px;
      flex-shrink: 0;
    }
    .fm-fancy-minmax-col {
      width: 68px !important;
      font-size: 10px !important;
      line-height: 1.2;
    }
    /* Sportsbook Table on Mobile */
    .wb-market-tabs-card .sportbook-market-tbl col:nth-child(1) {
      width: 72% !important;
    }
    .wb-market-tabs-card .sportbook-market-tbl col:nth-child(2) {
      width: 28% !important;
    }
    .wb-market-tabs-card .sportbook-market-tbl col:nth-child(3),
    .wb-market-tabs-card .sportbook-market-tbl td.td-end-hide {
      display: none !important;
      width: 0 !important;
    }
  }

  /* ================= 1:1 WINBOOK24 APP-MARKET-TABS (FANCY BET & SPORTSBOOK) ================= */
  .wb-market-tabs-card {
    position: relative;
    display: flex;
    flex-direction: column;
    min-width: 0;
    word-wrap: break-word;
    border: none;
    border-radius: 0.25rem;
    margin-top: 2px;
    padding: 0;
    background: none;
    margin-bottom: 1.5rem;
    font-family: Tahoma, Helvetica, Arial, sans-serif;
  }
  .wb-market-tabs-card .p-rltv {
    position: relative;
  }
  .wb-market-tabs-card #fancyBetHeadNew.fancy-head {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    zoom: 1;
    z-index: 2;
  }
  .wb-market-tabs-card .fancy-head h4 {
    position: relative;
    height: 25px;
    line-height: 25px;
    font-size: 12px;
    color: #fff;
    z-index: 1;
    float: left;
    margin: 0;
    zoom: 1;
  }
  .wb-market-tabs-card .fancy-head h4 span {
    min-width: 72px;
    line-height: 30px;
    padding: 0 14px 0 8px;
    vertical-align: top;
    display: inline-block;
    background: linear-gradient(180deg, #0a92a5 15%, #076875 100%);
    color: #ffffff;
    font-weight: 700;
    cursor: pointer;
  }
  .wb-market-tabs-card .fancy-head .in-play-sportbook span {
    background: linear-gradient(180deg, #f26d1c 15%, #d14100 100%);
    color: #ffffff;
    cursor: pointer;
    border-top-left-radius: 10px;
    padding: 0 10px;
  }
  .wb-market-tabs-card .btn-fancybet_rules,
  .wb-market-tabs-card .btn-sportsbook_rules {
    vertical-align: top;
    margin-left: -4px;
    height: 30px;
    width: 41px;
    display: inline-block;
    background-repeat: no-repeat;
    background: linear-gradient(180deg, #1cb0b5 15%, #1cb0b5 100%);
    clip-path: polygon(100% 100%, 79% 22%, 76% 15%, 74% 12%, 72% 9%, 68% 6%, 59% 3%, 78% 2%, 0% 0%, 0% 100%);
    color: #ffffff !important;
    cursor: pointer;
  }
  .wb-market-tabs-card .btn-sportsbook_rules {
    background: linear-gradient(180deg, #f58b30 15%, #f58b30 100%);
  }
  .wb-market-tabs-card .btn-fancybet_rules svg,
  .wb-market-tabs-card .btn-sportsbook_rules svg {
    width: 15px;
    height: 15px;
    margin: 8px 14px;
    display: block;
  }
  .wb-market-tabs-card .fancy_bet_tab-wrap {
    display: flex;
    background: linear-gradient(180deg, #f26d1c 15%, #d14100 100%);
    justify-content: center;
    align-items: center;
    margin-top: 30px;
    height: 30px;
    width: 100%;
  }
  .wb-market-tabs-card .fancy_bet_tab-wrap.fancy_bet_back {
    background: linear-gradient(180deg, #0a92a5 15%, #076875 100%);
  }
  .wb-market-tabs-card .special_bets-tab {
    display: flex;
    width: auto;
    background: rgba(255, 255, 255, 0.5);
    border-radius: 5px;
    margin: 3px 0 3px 21px;
    justify-content: center;
    align-items: center;
    padding: 0;
    cursor: pointer;
    list-style: none;
  }
  .wb-market-tabs-card .special_bets-tab li {
    list-style: none;
    padding: 2px;
  }
  .wb-market-tabs-card .special_bets-tab a {
    min-width: 70px;
    height: 18px;
    line-height: 18px;
    color: #000000;
    font-weight: 700;
    font-size: 12px;
    border-radius: 4px;
    box-sizing: border-box;
    padding: 0 5px;
    display: block;
    text-align: center;
    text-decoration: none;
  }
  .wb-market-tabs-card .special_bets-tab li.select a {
    background: #ffffff !important;
    color: #000000 !important;
    text-decoration: none;
  }

  /* Sportsbook Market Card (.sportbookmarket) */
  .wb-market-tabs-card .fancy-card.sportbookmarket {
    border: 1px solid #c8ced3 !important;
    border-radius: 4px 4px 2px 2px;
    background: #ffffff;
    margin-top: 2px;
    overflow: hidden;
  }
  .wb-market-tabs-card .card-header.sportbook.inner-sportbook {
    background: linear-gradient(180deg, #f26d1c 15%, #d14100 100%);
    color: #ffffff;
    padding: 4px 8px !important;
    font-size: 12px;
    font-weight: 700;
    border: none;
    border-radius: 3px 3px 0 0;
    cursor: pointer;
    box-shadow: inset 0 1px rgba(0, 0, 0, 0.2);
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .wb-market-tabs-card .tooltip-betlimit {
    font-size: 14px;
    color: #ffffff;
  }
  .wb-sb-minmax-pop {
    position: absolute;
    right: 22px;
    top: -2px;
    background: #ffffff;
    color: #1e1e1e;
    padding: 2px 8px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 700;
    white-space: nowrap;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.35);
    z-index: 10;
  }
  .wb-market-tabs-card .sportbook-market-tbl {
    width: 100%;
    margin-bottom: 0;
    border-collapse: collapse;
    background: #ffffff !important;
    table-layout: fixed;
  }
  .wb-market-tabs-card .sportbook-market-tbl tr.odd-row {
    border-bottom: 1px solid #7e97a7 !important;
  }
  .wb-market-tabs-card .sportbook-market-tbl td {
    vertical-align: middle;
    border-top: none;
    padding: 3px 0 !important;
    font-size: 12px;
  }
  .wb-market-tabs-card .sportbook-market-tbl td.team-name.sportbook-name {
    width: 66%;
    text-align: left;
    padding-left: 6px !important;
  }
  .wb-market-tabs-card .sportbook-market-tbl td.team-name.sportbook-name .title {
    color: #23282C !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    display: block !important;
  }
  .wb-market-tabs-card .sportbook-market-tbl td.back-1.sportbook-minwidth {
    width: 11%;
    background: #72e3a0 !important;
    text-align: center;
    cursor: pointer;
    position: relative;
  }
  .wb-market-tabs-card .sportbook-market-tbl td.back-1.sportbook-minwidth:hover,
  .wb-market-tabs-card .sportbook-market-tbl td.back-1.sportbook-minwidth.active,
  .wb-market-tabs-card .sportbook-market-tbl td.back-1.sportbook-minwidth.select {
    background: #16a660 !important;
    color: #ffffff !important;
  }
  .wb-market-tabs-card .sportbook-market-tbl td.back-1.sportbook-minwidth a.back {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #1e1e1e;
    text-decoration: none !important;
    line-height: 1.25;
    padding: 2px 0;
  }
  .wb-market-tabs-card .sportbook-market-tbl td.back-1.sportbook-minwidth a.back span {
    display: block;
  }
  .wb-market-tabs-card .sportbook-market-tbl td.back-1.sportbook-minwidth:hover a.back,
  .wb-market-tabs-card .sportbook-market-tbl td.back-1.sportbook-minwidth.active a.back,
  .wb-market-tabs-card .sportbook-market-tbl td.back-1.sportbook-minwidth.select a.back {
    color: #ffffff !important;
  }
  .wb-market-tabs-card .sportbook-market-tbl td.td-end-hide {
    width: 23%;
    background: #ffffff;
  }
  .wb-market-tabs-card .suspend-sportbook-cell {
    position: absolute;
    inset: 0;
    background-color: rgba(36, 58, 72, 0.55);
    z-index: 5;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: not-allowed;
  }
  .wb-market-tabs-card .suspend-sportbook-cell span {
    font-size: 11px;
    font-weight: 700;
    color: #ff2a2a;
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.65);
  }
  .wb-market-tabs-card tr.betslipback td {
    background: #d3edd0 !important;
    padding: 0 !important;
  }
</style>
@endpush

@section('content')
@php
  $moList = $livePayload['matchOddsData'] ?? [];
  $bmList = $livePayload['bookmakersData'] ?? [];
  $sbList = $livePayload['sportsbookData'] ?? [];
  $fancyList = $livePayload['fancyData'] ?? [];
  $winSbList = array_values(array_filter($sbList, fn ($m) => strcasecmp(trim((string) ($m['marketName'] ?? '')), 'To Win The Match') === 0));
  $hasMainMarkets = !empty($moList) || !empty($bmList);
  $defaultTab = (empty($fancyList) && !empty($sbList)) ? 'SPORTSBOOK' : 'Fancy';

  $formatVolume = function ($v) {
    $n = (float) $v;
    if ($n <= 0) return '0';
    if ($n >= 1000000) return round($n / 1000000, 1) . 'M';
    if ($n >= 1000) return round($n / 1000, 1) . 'K';
    return (string) round($n, 2);
  };

  $bannerTimeStr = '';
  try {
    $bannerTimeStr = \Carbon\Carbon::parse($event->event_time)->setTimezone('Asia/Kolkata')->format('d-m-Y h:i:s A');
  } catch (\Throwable $e) {
    $bannerTimeStr = $event->event_time;
  }

  $sportIdStr = (string) ($event->sport_id ?: '4');
  $sc = $liveScore ?? null;
  $hasUniverseScore = is_array($sc) && (!isset($sc['isScore']) || $sc['isScore'] !== false);
@endphp

<!-- 1. Official Powered By Universe Score Live Scoreboard OR Official Sport Banner (1:1 with winbook24.com) -->
<div id="wbTopScoreboardOrBanner" data-event-name="{{ $event->event_name }}" class="{{ (!$hasMainMarkets && !$hasUniverseScore) ? 'd-none' : '' }}">
  @if($hasUniverseScore && $sportIdStr === '4')
    @php
      $isTest = !empty($sc['isTestMatch']);
      $t1Name = $sc['team1Name'] ?? (explode(' v ', $event->event_name)[0] ?? 'Team 1');
      $t2Name = $sc['team2Name'] ?? (explode(' v ', $event->event_name)[1] ?? 'Team 2');
      $t1Bat = !empty($sc['team1Flag']);
      $t2Bat = !empty($sc['team2Flag']);
      $batsmenParts = [];
      if (!empty($sc['batsman1Name'])) {
        $batsmenParts[] = trim($sc['batsman1Name']) . ' ' . ($sc['batsman1Runs'] ?? 0);
      }
      if (!empty($sc['batsman2Name'])) {
        $batsmenParts[] = trim($sc['batsman2Name']) . ' ' . ($sc['batsman2Runs'] ?? 0);
      }
      $batsmenStr = !empty($sc['batsmenText']) ? $sc['batsmenText'] : implode(', ', $batsmenParts);
      $bowlerStr = !empty($sc['bowlerText']) ? $sc['bowlerText'] : (!empty($sc['bowlerName']) ? (trim($sc['bowlerName']) . ' ' . ($sc['bowlerRuns'] ?? 0)) : '');
      $commStr = trim((string) ($sc['Commentary'] ?? $sc['commentary'] ?? ''));
      $ballsArr = is_array($sc['balls'] ?? null) ? $sc['balls'] : [];
      $cbSeries = trim((string) ($sc['seriesName'] ?? ''));
      $cbDesc = trim((string) ($sc['matchDesc'] ?? ''));
      $cbFormat = trim((string) ($sc['matchFormat'] ?? ''));
      $cbState = trim((string) ($sc['state'] ?? ''));
      $cbVenue = trim((string) ($sc['venue'] ?? ''));
      $cbLastWkt = trim((string) ($sc['lastWicket'] ?? ''));
      $cbPartnership = trim((string) ($sc['partnership'] ?? ''));
    @endphp
    <div id="wbUniverseScorecard" class="wb-universe-scorecard">
      @if($cbSeries !== '' || $cbDesc !== '')
        <div class="wb-sc-row" style="background:#0d1b24;font-size:11px;min-height:24px;justify-content:space-between;">
          <div class="text-truncate" style="max-width:72%;color:#a3d8ff;">
            <span style="background:#dc3545;color:#fff;padding:1px 5px;border-radius:3px;font-size:10px;font-weight:800;margin-right:5px;">{{ strtoupper($cbState ?: 'LIVE') }}</span>
            <strong class="text-white">{{ $cbSeries }}</strong>
            @if($cbDesc !== '') <span class="ms-1">• {{ $cbDesc }}</span> @endif
            @if($cbFormat !== '') <span class="badge bg-secondary ms-1" style="font-size:9px;">{{ $cbFormat }}</span> @endif
          </div>
          @if($cbVenue !== '')
            <div class="text-truncate d-none d-md-block" style="max-width:27%;color:#adb5bd;font-size:10.5px;">📍 {{ $cbVenue }}</div>
          @endif
        </div>
      @endif
      @if($isTest)
        <!-- Test Match Header -->
        <div class="wb-sc-row wb-sc-head">
          <div style="width:50%;">Powered By <span class="wb-sc-brand">Universe Score</span></div>
          <div style="width:50%;display:flex;" class="wb-sc-col-hdr">
            <div style="width:33.33%;">1st INNINGS</div>
            <div style="width:33.33%;">2nd INNINGS</div>
            <div style="width:33.33%;">RR</div>
          </div>
        </div>
        <!-- Team 1 -->
        <div class="wb-sc-row">
          <div style="width:50%;display:flex;justify-content:space-between;align-items:center;padding-right:10px;">
            <span class="js-sc-t1-name">{{ $t1Name }}</span>
            <span class="js-sc-t1-icon">
              @if($t1Bat)
                <img src="/assets/bat.svg" alt="bat" style="height:17px;">
              @else
                <img src="/assets/ball.svg" alt="ball" style="height:15px;">
              @endif
            </span>
          </div>
          <div style="width:50%;display:flex;text-align:center;align-items:center;">
            <div style="width:33.33%;" class="js-sc-t1-inn1">
              {{ ($sc['score1'] ?? '') !== '' ? $sc['score1'] : '-' }}
              @if(!empty($sc['overs1']) && $sc['overs1'] !== '-' && empty($sc['score3']))
                <span style="font-size:10px;font-weight:400;">({{ $sc['overs1'] }})</span>
              @endif
            </div>
            <div style="width:33.33%;" class="js-sc-t1-inn2">
              {{ !empty($sc['score3']) ? $sc['score3'] : '-' }}
              @if(!empty($sc['overs3']))
                <span style="font-size:10px;font-weight:400;">({{ $sc['overs3'] }})</span>
              @endif
            </div>
            <div style="width:33.33%;" class="js-sc-t1-rr">{{ ($t1Bat && !empty($sc['runRate'])) ? $sc['runRate'] : ($sc['rr1'] ?? '') }}</div>
          </div>
        </div>
        <!-- Team 2 -->
        <div class="wb-sc-row">
          <div style="width:50%;display:flex;justify-content:space-between;align-items:center;padding-right:10px;">
            <span class="js-sc-t2-name">{{ $t2Name }}</span>
            <span class="js-sc-t2-icon">
              @if($t2Bat)
                <img src="/assets/bat.svg" alt="bat" style="height:17px;">
              @else
                <img src="/assets/ball.svg" alt="ball" style="height:15px;">
              @endif
            </span>
          </div>
          <div style="width:50%;display:flex;text-align:center;align-items:center;">
            <div style="width:33.33%;" class="js-sc-t2-inn1">
              {{ ($sc['score2'] ?? '') !== '' ? $sc['score2'] : '-' }}
              @if(!empty($sc['overs2']) && $sc['overs2'] !== '-' && empty($sc['score4']))
                <span style="font-size:10px;font-weight:400;">({{ $sc['overs2'] }})</span>
              @endif
            </div>
            <div style="width:33.33%;" class="js-sc-t2-inn2">
              {{ !empty($sc['score4']) ? $sc['score4'] : '-' }}
              @if(!empty($sc['overs4']))
                <span style="font-size:10px;font-weight:400;">({{ $sc['overs4'] }})</span>
              @endif
            </div>
            <div style="width:33.33%;" class="js-sc-t2-rr">{{ ($t2Bat && !empty($sc['runRate'])) ? $sc['runRate'] : ($sc['rr2'] ?? '') }}</div>
          </div>
        </div>
      @else
        <!-- ODI / T20 Match Header -->
        <div class="wb-sc-row wb-sc-head">
          <div style="width:42%;">Powered By <span class="wb-sc-brand">Universe Score</span></div>
          <div style="width:58%;display:flex;" class="wb-sc-col-hdr">
            <div style="width:20%;">SCORE</div>
            <div style="width:16%;">OVS</div>
            <div style="width:16%;">RR</div>
            <div style="width:16%;">4S</div>
            <div style="width:16%;">6S</div>
            <div style="width:16%;">WIDES</div>
          </div>
        </div>
        <!-- Team 1 -->
        <div class="wb-sc-row">
          <div style="width:42%;display:flex;justify-content:space-between;align-items:center;padding-right:10px;">
            <span class="d-flex align-items-center gap-1">
              @if(!empty($sc['team1Icon']))
                <img src="{{ $sc['team1Icon'] }}" alt="" style="height:17px;" onerror="this.style.display='none'">
              @endif
              <span class="js-sc-t1-name">{{ $t1Name }}</span>
            </span>
            <span class="js-sc-t1-icon">
              @if($t1Bat)
                <img src="/assets/bat.svg" alt="bat" style="height:17px;">
              @else
                <img src="/assets/ball.svg" alt="ball" style="height:15px;">
              @endif
            </span>
          </div>
          <div style="width:58%;display:flex;text-align:center;align-items:center;">
            <div style="width:20%;" class="wb-sc-score-green js-sc-t1-score">{{ ($sc['score1'] ?? '') !== '' ? $sc['score1'] : '-' }}</div>
            <div style="width:16%;" class="js-sc-t1-ovs">{{ ($sc['overs1'] ?? '') !== '' ? $sc['overs1'] : '-' }}</div>
            <div style="width:16%;" class="js-sc-t1-rr">{{ !empty($sc['rr1']) && $sc['rr1'] !== '0.00' ? $sc['rr1'] : (($t1Bat && !empty($sc['runRate'])) ? $sc['runRate'] : '0.00') }}</div>
            <div style="width:16%;" class="js-sc-t1-4s">{{ $sc['totalFours1'] ?? 0 }}</div>
            <div style="width:16%;" class="js-sc-t1-6s">{{ $sc['totalSixes1'] ?? 0 }}</div>
            <div style="width:16%;" class="js-sc-t1-wides">{{ $sc['totalWides1'] ?? 0 }}</div>
          </div>
        </div>
        <!-- Team 2 -->
        <div class="wb-sc-row">
          <div style="width:42%;display:flex;justify-content:space-between;align-items:center;padding-right:10px;">
            <span class="d-flex align-items-center gap-1">
              @if(!empty($sc['team2Icon']))
                <img src="{{ $sc['team2Icon'] }}" alt="" style="height:17px;" onerror="this.style.display='none'">
              @endif
              <span class="js-sc-t2-name">{{ $t2Name }}</span>
            </span>
            <span class="js-sc-t2-icon">
              @if($t2Bat)
                <img src="/assets/bat.svg" alt="bat" style="height:17px;">
              @else
                <img src="/assets/ball.svg" alt="ball" style="height:15px;">
              @endif
            </span>
          </div>
          <div style="width:58%;display:flex;text-align:center;align-items:center;">
            <div style="width:20%;" class="wb-sc-score-green js-sc-t2-score">{{ ($sc['score2'] ?? '') !== '' ? $sc['score2'] : '-' }}</div>
            <div style="width:16%;" class="js-sc-t2-ovs">{{ ($sc['overs2'] ?? '') !== '' ? $sc['overs2'] : '-' }}</div>
            <div style="width:16%;" class="js-sc-t2-rr">{{ !empty($sc['rr2']) && $sc['rr2'] !== '0.00' ? $sc['rr2'] : (($t2Bat && !empty($sc['runRate'])) ? $sc['runRate'] : '0.00') }}</div>
            <div style="width:16%;" class="js-sc-t2-4s">{{ $sc['totalFours2'] ?? 0 }}</div>
            <div style="width:16%;" class="js-sc-t2-6s">{{ $sc['totalSixes2'] ?? 0 }}</div>
            <div style="width:16%;" class="js-sc-t2-wides">{{ $sc['totalWides2'] ?? 0 }}</div>
          </div>
        </div>
      @endif

      <!-- Batsmen & Bowler Row -->
      <div class="wb-sc-row" style="justify-content:space-between;gap:8px;">
        <div style="width:54%;" class="text-truncate js-sc-batsmen" title="{{ $batsmenStr }}">
          <span style="color:#f8e71c;">BAT:</span> {{ $batsmenStr ?: 'Waiting for batsmen...' }}
        </div>
        <div style="width:46%;text-align:right;" class="text-truncate js-sc-bowler" title="{{ $bowlerStr }}">
          <span style="color:#00d2ff;">BOWL:</span> {{ $bowlerStr ?: '-' }}
        </div>
      </div>

      <!-- Commentary, Partnership, Last Wicket & Last 6 Balls Marquee Row -->
      <div class="wb-sc-row">
        <div class="wb-sc-marquee">
          <div class="wb-sc-marquee-inner js-sc-marquee">
            <span class="me-3" style="color:#7ed321;">{{ $commStr }}</span>
            @if($cbPartnership !== '')
              <span class="me-3" style="color:#f8e71c;">Partnership: {{ $cbPartnership }}</span>
            @endif
            @if($cbLastWkt !== '')
              <span class="me-3" style="color:#ff8585;">Last Wkt: {{ $cbLastWkt }}</span>
            @endif
            @if(!empty($ballsArr))
              <span>Recent balls:
                @foreach($ballsArr as $bVal)
                  @php
                    $bClean = strtolower(trim((string) $bVal));
                    $bCls = $bClean === '4' ? 'ball-4' : ($bClean === '6' ? 'ball-6' : (str_starts_with($bClean, 'w') ? 'ball-w' : ''));
                    $bDisp = ($bClean === '0' || $bClean === '') ? '•' : $bVal;
                  @endphp
                  <span class="wb-sc-ball {{ $bCls }}">{{ $bDisp }}</span>
                @endforeach
              </span>
            @endif
          </div>
        </div>
      </div>
    </div>
  @elseif($hasUniverseScore && $sportIdStr === '1')
    @php
      $t1Name = $sc['team1Name'] ?? ($sc['team1'] ?? (explode(' v ', $event->event_name)[0] ?? 'Home'));
      $t2Name = $sc['team2Name'] ?? ($sc['team2'] ?? (explode(' v ', $event->event_name)[1] ?? 'Away'));
      $sTour = trim((string) ($sc['tournament'] ?? ($event->tournament_name ?? 'UEFA Champions League')));
      $sRound = trim((string) ($sc['round'] ?? ''));
      $sStage = trim((string) ($sc['stage'] ?? 'LIVE'));
      $sVenue = trim((string) ($sc['venue'] ?? ''));
      $c1 = trim((string) ($sc['team1Country'] ?? ''));
      $c2 = trim((string) ($sc['team2Country'] ?? ''));
      $gInfo1 = trim((string) ($sc['goalInfo1'] ?? ''));
      $gInfo2 = trim((string) ($sc['goalInfo2'] ?? ''));
    @endphp
    <div id="wbUniverseScorecard" class="wb-universe-scorecard">
      @if($sTour !== '')
        <div class="wb-sc-row" style="background:#0d1b24;font-size:11px;min-height:24px;justify-content:space-between;">
          <div class="text-truncate" style="max-width:72%;color:#a3d8ff;">
            <span style="background:#dc3545;color:#fff;padding:1px 5px;border-radius:3px;font-size:10px;font-weight:800;margin-right:5px;" class="js-sc-soc-stage">{{ strtoupper($sStage ?: 'LIVE') }}</span>
            <strong class="text-white">{{ $sTour }}</strong>
            @if($sRound !== '') <span class="ms-1">• {{ $sRound }}</span> @endif
          </div>
          @if($sVenue !== '')
            <div class="text-truncate d-none d-md-block" style="max-width:27%;color:#adb5bd;font-size:10.5px;">📍 {{ $sVenue }}</div>
          @endif
        </div>
      @endif
      <div class="wb-sc-row wb-sc-head">
        <div style="width:45%;">Powered By <span class="wb-sc-brand">Universe Score</span></div>
        <div style="width:55%;display:flex;text-align:center;align-items:center;">
          <div style="width:20%;"><img src="/assets/scoreCard/goal.svg" style="height:15px;" alt="Goal"></div>
          <div style="width:20%;"><img src="/assets/scoreCard/htscore.svg" style="height:15px;" alt="HT"></div>
          <div style="width:20%;"><img src="/assets/scoreCard/corner.svg" style="height:15px;" alt="Corner"></div>
          <div style="width:20%;"><img src="/assets/scoreCard/yellow-card.svg" style="height:15px;" alt="YC"></div>
          <div style="width:20%;"><img src="/assets/scoreCard/red-card.svg" style="height:15px;" alt="RC"></div>
        </div>
      </div>
      <div class="wb-sc-row">
        <div style="width:45%;display:flex;align-items:center;gap:6px;padding-right:6px;">
          @if(!empty($sc['team1Icon']))
            <img src="{{ $sc['team1Icon'] }}" alt="" style="height:18px;width:18px;object-fit:contain;" onerror="this.style.display='none'">
          @endif
          <div class="text-truncate">
            <span class="js-sc-t1-name">{{ $t1Name }}</span>
            @if($c1 !== '') <small style="color:#adb5bd;font-size:10px;">({{ $c1 }})</small> @endif
            @if($gInfo1 !== '') <div style="font-size:9.5px;color:#7ed321;font-weight:400;" class="text-truncate">⚽ {{ $gInfo1 }}</div> @endif
          </div>
        </div>
        <div style="width:55%;display:flex;text-align:center;align-items:center;">
          <div style="width:20%;" class="wb-sc-score-green js-sc-soc-g1">{{ $sc['score1'] ?? ($sc['goal1'] ?? 0) }}</div>
          <div style="width:20%;" class="js-sc-soc-ht1">{{ $sc['htScore1'] ?? 0 }}</div>
          <div style="width:20%;" class="js-sc-soc-cor1">{{ $sc['corner1'] ?? 0 }}</div>
          <div style="width:20%;" class="js-sc-soc-yc1">{{ $sc['yellowCard1'] ?? 0 }}</div>
          <div style="width:20%;" class="js-sc-soc-rc1">{{ $sc['redCard1'] ?? 0 }}</div>
        </div>
      </div>
      <div class="wb-sc-row">
        <div style="width:45%;display:flex;align-items:center;gap:6px;padding-right:6px;">
          @if(!empty($sc['team2Icon']))
            <img src="{{ $sc['team2Icon'] }}" alt="" style="height:18px;width:18px;object-fit:contain;" onerror="this.style.display='none'">
          @endif
          <div class="text-truncate">
            <span class="js-sc-t2-name">{{ $t2Name }}</span>
            @if($c2 !== '') <small style="color:#adb5bd;font-size:10px;">({{ $c2 }})</small> @endif
            @if($gInfo2 !== '') <div style="font-size:9.5px;color:#7ed321;font-weight:400;" class="text-truncate">⚽ {{ $gInfo2 }}</div> @endif
          </div>
        </div>
        <div style="width:55%;display:flex;text-align:center;align-items:center;">
          <div style="width:20%;" class="wb-sc-score-green js-sc-soc-g2">{{ $sc['score2'] ?? ($sc['goal2'] ?? 0) }}</div>
          <div style="width:20%;" class="js-sc-soc-ht2">{{ $sc['htScore2'] ?? 0 }}</div>
          <div style="width:20%;" class="js-sc-soc-cor2">{{ $sc['corner2'] ?? 0 }}</div>
          <div style="width:20%;" class="js-sc-soc-yc2">{{ $sc['yellowCard2'] ?? 0 }}</div>
          <div style="width:20%;" class="js-sc-soc-rc2">{{ $sc['redCard2'] ?? 0 }}</div>
        </div>
      </div>
      <div class="wb-sc-row">
        <div class="w-100 text-center text-warning js-sc-soc-status">{{ $sc['status'] ?? ($sc['matchTime'] ?? 'IN PLAY') }}</div>
      </div>
    </div>
  @elseif($hasUniverseScore && $sportIdStr === '2')
    @php
      $t1Name = $sc['team1Name'] ?? ($sc['player1'] ?? (explode(' v ', $event->event_name)[0] ?? 'Player 1'));
      $t2Name = $sc['team2Name'] ?? ($sc['player2'] ?? (explode(' v ', $event->event_name)[1] ?? 'Player 2'));
      $tTour = trim((string) ($sc['tournament'] ?? ($event->tournament_name ?? '')));
      $tStage = trim((string) ($sc['stage'] ?? 'LIVE'));
      $serving = (int) ($sc['serving'] ?? 0);
      $c1 = trim((string) ($sc['team1Country'] ?? ''));
      $c2 = trim((string) ($sc['team2Country'] ?? ''));
    @endphp
    <div id="wbUniverseScorecard" class="wb-universe-scorecard">
      @if($tTour !== '')
        <div class="wb-sc-row" style="background:#0d1b24;font-size:11px;min-height:24px;justify-content:space-between;">
          <div class="text-truncate" style="max-width:78%;color:#a3d8ff;">
            <span style="background:#dc3545;color:#fff;padding:1px 5px;border-radius:3px;font-size:10px;font-weight:800;margin-right:5px;">{{ strtoupper($tStage ?: 'LIVE') }}</span>
            <strong class="text-white">{{ $tTour }}</strong>
          </div>
          <div class="d-none d-md-block" style="color:#7ed321;font-size:10.5px;">🎾 Livescore.in Live</div>
        </div>
      @endif
      <div class="wb-sc-row wb-sc-head">
        <div style="width:45%;">Powered By <span class="wb-sc-brand">Universe Score</span></div>
        <div style="width:55%;display:flex;" class="wb-sc-col-hdr">
          <div style="width:20%;">SETS</div>
          <div style="width:20%;">1</div>
          <div style="width:20%;">2</div>
          <div style="width:20%;">3</div>
          <div style="width:20%;">PTS</div>
        </div>
      </div>
      <div class="wb-sc-row">
        <div style="width:45%;display:flex;justify-content:space-between;align-items:center;padding-right:10px;">
          <span>
            <span class="js-sc-t1-name">{{ $t1Name }}</span>
            @if($c1 !== '') <small style="color:#adb5bd;font-size:10px;">({{ $c1 }})</small> @endif
          </span>
          <span class="js-sc-t1-srv" style="color:#7ed321;font-size:11px;">{{ $serving === 1 ? '●' : '' }}</span>
        </div>
        <div style="width:55%;display:flex;text-align:center;">
          <div style="width:20%;" class="wb-sc-score-green js-sc-ten-sets1">{{ $sc['score1'] ?? ($sc['sets1'] ?? 0) }}</div>
          <div style="width:20%;" class="js-sc-ten-s1-1">{{ $sc['set1Score1'] ?? ($sc['s1_1'] ?? 0) }}</div>
          <div style="width:20%;" class="js-sc-ten-s2-1">{{ $sc['set2Score1'] ?? ($sc['s2_1'] ?? '-') }}</div>
          <div style="width:20%;" class="js-sc-ten-s3-1">{{ $sc['set3Score1'] ?? ($sc['s3_1'] ?? '-') }}</div>
          <div style="width:20%;color:#f8e71c;" class="js-sc-ten-pts1">{{ $sc['points1'] ?? '0' }}</div>
        </div>
      </div>
      <div class="wb-sc-row">
        <div style="width:45%;display:flex;justify-content:space-between;align-items:center;padding-right:10px;">
          <span>
            <span class="js-sc-t2-name">{{ $t2Name }}</span>
            @if($c2 !== '') <small style="color:#adb5bd;font-size:10px;">({{ $c2 }})</small> @endif
          </span>
          <span class="js-sc-t2-srv" style="color:#7ed321;font-size:11px;">{{ $serving === 2 ? '●' : '' }}</span>
        </div>
        <div style="width:55%;display:flex;text-align:center;">
          <div style="width:20%;" class="wb-sc-score-green js-sc-ten-sets2">{{ $sc['score2'] ?? ($sc['sets2'] ?? 0) }}</div>
          <div style="width:20%;" class="js-sc-ten-s1-2">{{ $sc['set1Score2'] ?? ($sc['s1_2'] ?? 0) }}</div>
          <div style="width:20%;" class="js-sc-ten-s2-2">{{ $sc['set2Score2'] ?? ($sc['s2_2'] ?? '-') }}</div>
          <div style="width:20%;" class="js-sc-ten-s3-2">{{ $sc['set3Score2'] ?? ($sc['s3_2'] ?? '-') }}</div>
          <div style="width:20%;color:#f8e71c;" class="js-sc-ten-pts2">{{ $sc['points2'] ?? '0' }}</div>
        </div>
      </div>
      <div class="wb-sc-row">
        <div class="w-100 text-center text-warning js-sc-ten-status">{{ $sc['status'] ?? 'IN PLAY' }}</div>
      </div>
    </div>
  @else
    <div class="fm-banner sport-{{ $sportIdStr }}">
      <div class="d-flex justify-content-between align-items-start w-100">
        <div class="fm-game-status">{{ $event->status ?: 'OPEN' }}</div>
        <div class="fm-game-time">
          <span class="me-1">Game time</span> {{ $bannerTimeStr }}
        </div>
      </div>
      <div class="d-flex flex-column align-items-center justify-content-center">
        <span class="fm-bet-started">Bet Started</span>
      </div>
    </div>
  @endif
</div>

<!-- 2. Market Filter Pills (All | Popular | Match Odds | Bookmaker | Fancy) -->
<div class="fm-filter-bar {{ !$hasMainMarkets ? 'd-none' : '' }}">
  <a href="javascript:void(0)" class="fm-filter-pill active" onclick="FullMkt.filterSection('all', this)">All</a>
  <a href="javascript:void(0)" class="fm-filter-pill" onclick="FullMkt.filterSection('all', this)">Popular</a>
  @if(!empty($moList))
    <a href="javascript:void(0)" class="fm-filter-pill" onclick="FullMkt.filterSection('match_odds', this)">{{ $moList[0]['marketName'] ?? 'Match Odds' }}</a>
  @endif
  @if(!empty($bmList))
    <a href="javascript:void(0)" class="fm-filter-pill" onclick="FullMkt.filterSection('bookmaker', this)">Bookmaker</a>
  @endif
  @if(!empty($fancyList))
    <a href="javascript:void(0)" class="fm-filter-pill" onclick="FullMkt.filterSection('fancy', this)">Fancy Bet</a>
  @endif
</div>

<!-- ================= 3. MATCH ODDS / TOURNAMENT WINNER SECTION ================= -->
<div id="fmSection_match_odds" class="js-fm-section">
  @foreach($moList as $moIdx => $mo)
    @php
      $moTitle = $mo['marketName'] ?? 'Match Odds';
      $moMin = $mo['min'] ?? 10;
      $moMax = $mo['max'] ?? 10000;
      $moMatched = (float) ($mo['oddsData']['totalMatched'] ?? $event->total_matched ?? 1200000);
      $moMatchedStr = $moMatched >= 1000000
          ? number_format($moMatched / 1000000, 1) . 'M'
          : ($moMatched >= 1000 ? number_format($moMatched / 1000, 1) . 'K' : number_format($moMatched, 0));
      $moRunners = $mo['oddsData']['runners'] ?? [];
      $moRunnersMap = $mo['runnersData'] ?? [];
      $moStatus = $mo['oddsData']['status'] ?? 'OPEN';
      $moKey = 'mo_' . $moIdx;
    @endphp
    <div class="fm-card js-market-pos-scope" data-market-key="{{ $moKey }}" data-market-name="{{ $moTitle }}" data-market-category="exchange">
      <div class="fm-card-head">
        <div class="fm-head-tab">
          <span>{{ $moTitle }}</span>
          <span style="cursor:pointer;" data-bs-toggle="modal" data-bs-target="#footerPolicyModal" onclick="showFooterPolicy('{{ addslashes($moTitle) }} Rules')">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 15 15">
              <path fill="currentColor" fill-rule="evenodd" d="M6.76 5.246V3.732h1.48v1.514H6.76zm.74 8.276a5.86 5.86 0 0 0 3.029-.83 5.839 5.839 0 0 0 2.163-2.163 5.86 5.86 0 0 0 .83-3.029 5.86 5.86 0 0 0-.83-3.029 5.839 5.839 0 0 0-2.163-2.163 5.86 5.86 0 0 0-3.029-.83 5.86 5.86 0 0 0-3.029.83A5.839 5.839 0 0 0 2.308 4.47a5.86 5.86 0 0 0-.83 3.029 5.86 5.86 0 0 0 .83 3.029 5.839 5.839 0 0 0 2.163 2.163 5.86 5.86 0 0 0 3.029.83zM7.5 0c1.37 0 2.638.343 3.804 1.028a7.108 7.108 0 0 1 2.668 2.668A7.376 7.376 0 0 1 15 7.5c0 1.37-.343 2.638-1.028 3.804a7.108 7.108 0 0 1-2.668 2.668A7.376 7.376 0 0 1 7.5 15a7.376 7.376 0 0 1-3.804-1.028 7.243 7.243 0 0 1-2.668-2.686A7.343 7.343 0 0 1 0 7.5c0-1.358.343-2.62 1.028-3.786a7.381 7.381 0 0 1 2.686-2.686A7.343 7.343 0 0 1 7.5 0zm-.74 11.268V6.761h1.48v4.507H6.76z"/>
            </svg>
          </span>
        </div>

        @if(count($moRunners) === 2 && stripos($moTitle, 'Tied') === false)
          <button type="button" class="fm-cashout-btn">
            <span class="fm-cashout-dot"></span> Cash Out
          </button>
        @endif

        <span class="fm-matched-txt">Matched <strong>€ {{ $moMatchedStr }}</strong></span>
      </div>

      <!-- Subheader: Back | Lay | Min/Max -->
      <div class="fm-subhead">
        <div class="flex-grow-1">
          <span class="wb-minmax-pill fm-mbl-minmax">Min/Max <strong class="text-dark">{{ $moMin }}-{{ $moMax }}</strong></span>
        </div>
        <div class="fm-subhead-right">
          <div class="box-hide-mbl" style="width:116px;"></div>
          <div class="fm-subhead-back" style="width:57px;background:var(--back-1);padding:4px 0;border-top-left-radius:6px;">Back</div>
          <div class="fm-subhead-lay" style="width:57px;background:var(--lay-1);padding:4px 0;border-top-right-radius:6px;">Lay</div>
          <div class="box-hide-mbl ps-1" style="width:118px;">
            <span class="wb-minmax-pill">Min/Max <strong class="text-dark">{{ $moMin }}-{{ $moMax }}</strong></span>
          </div>
        </div>
      </div>

      <!-- Runners -->
      @foreach($moRunners as $rIdx => $r)
        @php
          $selId = (string) ($r['selectionId'] ?? ($rIdx + 1));
          $rName = $moRunnersMap[$selId] ?? ($r['runnerName'] ?? ('Runner ' . ($rIdx + 1)));
          $backs = $r['price']['back'] ?? [];
          $lays = $r['price']['lay'] ?? [];
          $rSusp = ($moStatus === 'SUSPENDED' || ($r['status'] ?? '') === 'SUSPENDED');
          $slipRowId = 'moSlip_' . $moIdx . '_' . $selId;
        @endphp
        <div class="fm-runner-row" data-selection-id="{{ $selId }}" data-runner-name="{{ $rName }}">
          <div class="fm-runner-name">
            <span class="fm-runner-title">{{ $rName }}</span>
            <span class="fm-runner-pl js-runner-pl d-none"
                  data-market-key="{{ $moKey }}"
                  data-market-name="{{ $moTitle }}"
                  data-selection-id="{{ $selId }}"
                  data-runner-name="{{ $rName }}"></span>
          </div>
          <div class="fm-odds-6">
            <div class="fm-suspend-overlay {{ $rSusp ? '' : 'd-none' }}"><span>Suspended</span></div>
            <!-- Back 3, Back 2, Back 1 -->
            @for($bi = 2; $bi >= 0; $bi--)
              @php
                $bp = $backs[$bi]['price'] ?? 0;
                $bs = $backs[$bi]['size'] ?? 0;
                $bCls = $bi === 0 ? 'wb-odd-back' : ($bi === 1 ? 'wb-odd-back-2 box-hide-mbl' : 'wb-odd-back-3 box-hide-mbl');
                $bpLabel = $bp >= 1000 ? number_format((float)$bp) : ($bp > 0 ? $bp : '0');
              @endphp
              <a href="javascript:void(0)" class="fm-odd-cell {{ $bCls }} js-mo-cell" data-mo-type="back" data-mo-level="{{ $bi }}"
                 onclick="FullMkt.openInlineSlip('{{ $slipRowId }}', '{{ addslashes($moTitle) }}', '{{ $selId }}', '{{ addslashes($rName) }}', 'BACK', {{ $bp > 0 ? $bp : 1.90 }}, 'exchange', null, '{{ $moKey }}', this)">
                <strong>{{ $bpLabel }}</strong>
                <small>{{ $formatVolume($bs) }}</small>
              </a>
            @endfor

            <!-- Lay 1, Lay 2, Lay 3 -->
            @for($li = 0; $li <= 2; $li++)
              @php
                $lp = $lays[$li]['price'] ?? 0;
                $ls = $lays[$li]['size'] ?? 0;
                $lCls = $li === 0 ? 'wb-odd-lay' : ($li === 1 ? 'wb-odd-lay-2 box-hide-mbl' : 'wb-odd-lay-3 box-hide-mbl');
                $lpLabel = $lp >= 1000 ? number_format((float)$lp) : ($lp > 0 ? $lp : '0');
              @endphp
              <a href="javascript:void(0)" class="fm-odd-cell {{ $lCls }} js-mo-cell" data-mo-type="lay" data-mo-level="{{ $li }}"
                 onclick="FullMkt.openInlineSlip('{{ $slipRowId }}', '{{ addslashes($moTitle) }}', '{{ $selId }}', '{{ addslashes($rName) }}', 'LAY', {{ $lp > 0 ? $lp : 1.92 }}, 'exchange', null, '{{ $moKey }}', this)">
                <strong>{{ $lpLabel }}</strong>
                <small>{{ $formatVolume($ls) }}</small>
              </a>
            @endfor
          </div>
        </div>
        <div id="{{ $slipRowId }}" class="fm-inline-slip d-none"></div>
      @endforeach
    </div>
  @endforeach
</div>

<!-- ================= 4. BOOKMAKER / BOOKMAKER CUP BHAV SECTION ================= -->
@if(!empty($bmList))
<div id="fmSection_bookmaker" class="js-fm-section">
  @foreach($bmList as $bmIdx => $bm)
    @php
      $bmTitle = $bm['marketName'] ?? 'Bookmaker';
      $bmMin = $bm['min'] ?? 10;
      $bmMax = $bm['max'] ?? 500000;
      $bmMatched = (float) ($bm['oddsData']['totalMatched'] ?? 13800000);
      $bmMatchedStr = $bmMatched >= 1000000 ? number_format($bmMatched / 1000000, 1) . 'M' : number_format($bmMatched / 1000, 1) . 'K';
      $bmRunners = $bm['oddsData']['runners'] ?? [];
      $bmRunnersMap = $bm['runnersData'] ?? [];
      $bmStatus = strtoupper((string) ($bm['oddsData']['status'] ?? 'ONLINE'));
      $bmKey = 'bm_' . $bmIdx;
    @endphp
    <div class="fm-card wb-bookmaker-table js-market-pos-scope" data-market-key="{{ $bmKey }}" data-market-name="{{ $bmTitle }}" data-market-category="bookmaker">
      <div class="fm-card-head">
        <div class="fm-head-tab">
          <span>{{ $bmTitle }}</span>
          <span style="cursor:pointer;" onclick="showFooterPolicy('Bookmaker Rules')">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 15 15">
              <path fill="currentColor" fill-rule="evenodd" d="M6.76 5.246V3.732h1.48v1.514H6.76zm.74 8.276a5.86 5.86 0 0 0 3.029-.83 5.839 5.839 0 0 0 2.163-2.163 5.86 5.86 0 0 0 .83-3.029 5.86 5.86 0 0 0-.83-3.029 5.839 5.839 0 0 0-2.163-2.163 5.86 5.86 0 0 0-3.029-.83 5.86 5.86 0 0 0-3.029.83A5.839 5.839 0 0 0 2.308 4.47a5.86 5.86 0 0 0-.83 3.029 5.86 5.86 0 0 0 .83 3.029 5.839 5.839 0 0 0 2.163 2.163 5.86 5.86 0 0 0 3.029.83zM7.5 0c1.37 0 2.638.343 3.804 1.028a7.108 7.108 0 0 1 2.668 2.668A7.376 7.376 0 0 1 15 7.5c0 1.37-.343 2.638-1.028 3.804a7.108 7.108 0 0 1-2.668 2.668A7.376 7.376 0 0 1 7.5 15a7.376 7.376 0 0 1-3.804-1.028 7.243 7.243 0 0 1-2.668-2.686A7.343 7.343 0 0 1 0 7.5c0-1.358.343-2.62 1.028-3.786a7.381 7.381 0 0 1 2.686-2.686A7.343 7.343 0 0 1 7.5 0zm-.74 11.268V6.761h1.48v4.507H6.76z"/>
            </svg>
          </span>
        </div>

        @if(count($bmRunners) === 2)
          <button type="button" class="fm-cashout-btn">
            <span class="fm-cashout-dot"></span> Cash Out
          </button>
        @endif

        <span class="fm-matched-txt">Matched <strong>€ {{ $bmMatchedStr }}</strong></span>
      </div>

      <div class="fm-subhead" style="background:transparent;">
        <div class="flex-grow-1 d-flex align-items-center">
          <span class="wb-minmax-pill fm-mbl-minmax">Min/Max {{ $bmMin }}-{{ $bmMax }}</span>
          <span class="text-danger fw-bold ms-3 small box-hide-mbl" style="font-size:11px;">To Win The Match Bet Started In Sportsbook Market</span>
        </div>
        <div class="fm-subhead-right">
          <div class="box-hide-mbl" style="width:116px;"></div>
          <div class="fm-subhead-back" style="width:57px;padding:4px 0;">Back</div>
          <div class="fm-subhead-lay" style="width:57px;padding:4px 0;">Lay</div>
          <div class="box-hide-mbl ps-1" style="width:118px;">
            <span class="wb-minmax-pill">Min/Max {{ $bmMin }}-{{ $bmMax }}</span>
          </div>
        </div>
      </div>

      @foreach($bmRunners as $rIdx => $r)
        @php
          $selId = (string) ($r['selectionId'] ?? ($rIdx + 1));
          $rName = $bmRunnersMap[$selId] ?? ($r['runnerName'] ?? ('Runner ' . ($rIdx + 1)));
          $backs = $r['price']['back'] ?? [];
          $lays = $r['price']['lay'] ?? [];
          $rStatus = strtoupper((string) ($r['status'] ?? $bmStatus));
          $isSusp = in_array($rStatus, ['SUSPENDED', 'SUSPEND', 'BALL RUNNING', 'BALLRUN', 'CLOSED']);
          $suspLabel = str_contains($rStatus, 'BALL') ? 'Ballrun' : 'Suspended';
          $slipRowId = 'bmSlip_' . $bmIdx . '_' . $selId;
        @endphp
        <div class="fm-runner-row" data-selection-id="{{ $selId }}" data-runner-name="{{ $rName }}">
          <div class="fm-runner-name">
            <span class="fm-runner-title">{{ $rName }}</span>
            <span class="fm-runner-pl js-runner-pl d-none"
                  data-market-key="{{ $bmKey }}"
                  data-market-name="{{ $bmTitle }}"
                  data-selection-id="{{ $selId }}"
                  data-runner-name="{{ $rName }}"></span>
          </div>
          <div class="fm-odds-6">
            <div class="fm-suspend-overlay {{ $isSusp ? '' : 'd-none' }}"><span>{{ $suspLabel }}</span></div>
            @for($bi = 2; $bi >= 0; $bi--)
              @php
                $bp = $backs[$bi]['price'] ?? 0;
                $bs = $backs[$bi]['size'] ?? '5M';
                $bCls = $bi === 0 ? 'wb-odd-back' : ($bi === 1 ? 'wb-odd-back-2 box-hide-mbl' : 'wb-odd-back-3 box-hide-mbl');
              @endphp
              <a href="javascript:void(0)" class="fm-odd-cell {{ $bCls }} js-bm-cell" data-bm-type="back" data-bm-level="{{ $bi }}"
                 onclick="FullMkt.openInlineSlip('{{ $slipRowId }}', '{{ addslashes($bmTitle) }}', '{{ $selId }}', '{{ addslashes($rName) }}', 'BACK', {{ $bp > 0 ? $bp : 90 }}, 'bookmaker', null, '{{ $bmKey }}', this)">
                <strong>{{ $bp ?: '-' }}</strong>
                <small>{{ is_numeric($bs) ? $formatVolume($bs) : $bs }}</small>
              </a>
            @endfor

            @for($li = 0; $li <= 2; $li++)
              @php
                $lp = $lays[$li]['price'] ?? 0;
                $ls = $lays[$li]['size'] ?? '5M';
                $lCls = $li === 0 ? 'wb-odd-lay' : ($li === 1 ? 'wb-odd-lay-2 box-hide-mbl' : 'wb-odd-lay-3 box-hide-mbl');
              @endphp
              <a href="javascript:void(0)" class="fm-odd-cell {{ $lCls }} js-bm-cell" data-bm-type="lay" data-bm-level="{{ $li }}"
                 onclick="FullMkt.openInlineSlip('{{ $slipRowId }}', '{{ addslashes($bmTitle) }}', '{{ $selId }}', '{{ addslashes($rName) }}', 'LAY', {{ $lp > 0 ? $lp : 92 }}, 'bookmaker', null, '{{ $bmKey }}', this)">
                <strong>{{ $lp ?: '-' }}</strong>
                <small>{{ is_numeric($ls) ? $formatVolume($ls) : $ls }}</small>
              </a>
            @endfor
          </div>
        </div>
        <div id="{{ $slipRowId }}" class="fm-inline-slip d-none"></div>
      @endforeach
    </div>
  @endforeach
</div>
@endif

<!-- ================= 5. TO WIN THE MATCH (SPORTSBOOK GREEN BOX) ================= -->
@if(!empty($winSbList))
<div id="fmSection_sportsbook_box" class="js-fm-section">
  @foreach($winSbList as $sbIdx => $sb)
    @php
      $sbTitle = $sb['marketName'] ?? 'To Win The Match';
      $sbMin = $sb['min'] ?? 10;
      $sbMax = $sb['max'] ?? 250000;
      $sbRunners = $sb['oddsData']['runners'] ?? [];
      $sbRunnersMap = $sb['runnersData'] ?? [];
      $sbSlipId = 'sbBoxSlip_' . $sbIdx;
      $sbKey = 'sb_box_' . $sbIdx;
    @endphp
    <div class="fm-card js-market-pos-scope" data-market-key="{{ $sbKey }}" data-market-name="{{ $sbTitle }}" data-market-category="sportsbook">
      <div class="d-flex justify-content-between align-items-center px-3 py-1 text-white fw-bold" style="background:linear-gradient(-180deg, #2E4B5E 0%, #243A48 82%);font-size:13px;">
        <span>{{ $sbTitle }}</span>
        <span>Min/Max: {{ $sbMin }} - {{ $sbMax }}</span>
      </div>
      <div class="d-flex border-bottom text-center fw-bold py-1 bg-white">
        <div class="w-50 border-end">Back</div>
        <div class="w-50">Back</div>
      </div>
      <div class="fm-sb-green-body">
        <div class="row g-2">
          @foreach($sbRunners as $rIdx => $sbr)
            @php
              $selId = (string) ($sbr['selectionId'] ?? ($rIdx + 1));
              $rName = $sbRunnersMap[$selId] ?? ($sbr['runnerName'] ?? ('Team ' . ($rIdx + 1)));
              $oddsVal = $sbr['price']['back'][0]['price'] ?? ($sbr['odds'] ?? 1.90);
              $volVal = $sbr['price']['back'][0]['size'] ?? $sbMax;
            @endphp
            <div class="col-6 d-flex justify-content-center">
              <a href="javascript:void(0)" class="fm-sb-pill"
                 onclick="FullMkt.openInlineSlip('{{ $sbSlipId }}', '{{ addslashes($sbTitle) }}', '{{ $selId }}', '{{ addslashes($rName) }}', 'BACK', {{ $oddsVal }}, 'sportsbook', null, '{{ $sbKey }}', this)">
                <div class="fm-sb-pill-left">
                  <strong style="font-size:13px;">{{ $oddsVal }}</strong>
                  <small style="font-size:10px;">{{ $volVal }}</small>
                </div>
                <div class="fm-sb-pill-right flex-column">
                  <span>{{ $rName }}</span>
                  <span class="fm-runner-pl js-runner-pl d-none"
                        data-market-key="{{ $sbKey }}"
                        data-market-name="{{ $sbTitle }}"
                        data-selection-id="{{ $selId }}"
                        data-runner-name="{{ $rName }}"></span>
                </div>
              </a>
            </div>
          @endforeach
        </div>
      </div>
      <div id="{{ $sbSlipId }}" class="fm-inline-slip d-none"></div>
    </div>
  @endforeach
</div>
@endif

<!-- ================= 6. FANCY BET & SPORTSBOOK TABS (1:1 app-market-tabs) ================= -->
@if(!empty($fancyList) || !empty($sbList))
<div id="fmSection_fancy" class="js-fm-section {{ $hasMainMarkets ? 'mt-2' : '' }}">
  <div class="wb-market-tabs-card">
    <div class="p-rltv">
      <!-- Slanted Fancy Bet & Sportsbook Header Tabs -->
      <div id="fancyBetHeadNew" class="fancy-head">
        <h4 class="in-play" onclick="switchFancyOrSb('Fancy')">
          <span id="headerName">Fancy Bet</span>
          <a href="javascript:void(0)" class="btn-fancybet_rules" onclick="event.stopPropagation(); showFooterPolicy('Fancy Bet Rules')" title="Fancy Bet Rules">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 15 15">
              <path fill="currentColor" fill-rule="evenodd" d="M6.76 5.246V3.732h1.48v1.514H6.76zm.74 8.276a5.86 5.86 0 0 0 3.029-.83 5.839 5.839 0 0 0 2.163-2.163 5.86 5.86 0 0 0 .83-3.029 5.86 5.86 0 0 0-.83-3.029 5.839 5.839 0 0 0-2.163-2.163 5.86 5.86 0 0 0-3.029-.83 5.86 5.86 0 0 0-3.029.83A5.839 5.839 0 0 0 2.308 4.47a5.86 5.86 0 0 0-.83 3.029 5.86 5.86 0 0 0 .83 3.029 5.839 5.839 0 0 0 2.163 2.163 5.86 5.86 0 0 0 3.029.83zM7.5 0c1.37 0 2.638.343 3.804 1.028a7.108 7.108 0 0 1 2.668 2.668A7.376 7.376 0 0 1 15 7.5c0 1.37-.343 2.638-1.028 3.804a7.108 7.108 0 0 1-2.668 2.668A7.376 7.376 0 0 1 7.5 15a7.376 7.376 0 0 1-3.804-1.028 7.243 7.243 0 0 1-2.668-2.686A7.343 7.343 0 0 1 0 7.5c0-1.358.343-2.62 1.028-3.786a7.381 7.381 0 0 1 2.686-2.686A7.343 7.343 0 0 1 7.5 0zm-.74 11.268V6.761h1.48v4.507H6.76z"></path>
            </svg>
          </a>
        </h4>
        <h4 class="in-play-sportbook" onclick="switchFancyOrSb('SPORTSBOOK')">
          <span id="headerName">Sportsbook</span>
          <a href="javascript:void(0)" class="btn-sportsbook_rules" onclick="event.stopPropagation(); showFooterPolicy('Sportsbook Rules')" title="Sportsbook Rules">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 15 15">
              <path fill="currentColor" fill-rule="evenodd" d="M6.76 5.246V3.732h1.48v1.514H6.76zm.74 8.276a5.86 5.86 0 0 0 3.029-.83 5.839 5.839 0 0 0 2.163-2.163 5.86 5.86 0 0 0 .83-3.029 5.86 5.86 0 0 0-.83-3.029 5.839 5.839 0 0 0-2.163-2.163 5.86 5.86 0 0 0-3.029-.83 5.86 5.86 0 0 0-3.029.83A5.839 5.839 0 0 0 2.308 4.47a5.86 5.86 0 0 0-.83 3.029 5.86 5.86 0 0 0 .83 3.029 5.839 5.839 0 0 0 2.163 2.163 5.86 5.86 0 0 0 3.029.83zM7.5 0c1.37 0 2.638.343 3.804 1.028a7.108 7.108 0 0 1 2.668 2.668A7.376 7.376 0 0 1 15 7.5c0 1.37-.343 2.638-1.028 3.804a7.108 7.108 0 0 1-2.668 2.668A7.376 7.376 0 0 1 7.5 15a7.376 7.376 0 0 1-3.804-1.028 7.243 7.243 0 0 1-2.668-2.686A7.343 7.343 0 0 1 0 7.5c0-1.358.343-2.62 1.028-3.786a7.381 7.381 0 0 1 2.686-2.686A7.343 7.343 0 0 1 7.5 0zm-.74 11.268V6.761h1.48v4.507H6.76z"></path>
            </svg>
          </a>
        </h4>
      </div>

      <!-- Fancy Bet Category Bar -->
      <div id="subNav_Fancy" class="fancy_bet_tab-wrap fancy_bet_back {{ $defaultTab === 'Fancy' ? '' : 'd-none' }}">
        <ul class="special_bets-tab">
          @foreach(['ALL', 'Fancy', 'Line Markets', 'Ball by Ball', 'Meter Markets', 'Khado Markets'] as $fCat)
            <li class="js-fancy-cat-li {{ $fCat === 'ALL' ? 'select' : '' }}">
              <a href="javascript:void(0)" onclick="filterFancyCat('{{ $fCat }}', this)">{{ $fCat }}</a>
            </li>
          @endforeach
        </ul>
      </div>

      <!-- Sportsbook Category Bar (All | Match | Odds/Evens | Batsman | Bowler | Extra) -->
      <div id="subNav_SPORTSBOOK" class="fancy_bet_tab-wrap {{ $defaultTab === 'SPORTSBOOK' ? '' : 'd-none' }}">
        <ul class="special_bets-tab bookmakersTabs">
          @foreach([['label' => 'All', 'id' => 'ALL'], ['label' => 'Match', 'id' => 'MATCH'], ['label' => 'Odds/Evens', 'id' => 'ODDS/EVENS'], ['label' => 'Batsman', 'id' => 'BATSMAN'], ['label' => 'Bowler', 'id' => 'BOWLER'], ['label' => 'Extra', 'id' => 'EXTRA']] as $sbCat)
            <li class="js-sb-cat-li {{ $sbCat['id'] === 'ALL' ? 'select' : '' }}">
              <a href="javascript:void(0)" onclick="filterSportsbookCat('{{ $sbCat['id'] }}', this)">{{ $sbCat['label'] }}</a>
            </li>
          @endforeach
        </ul>
      </div>
    </div>

    <!-- Fancy Bet Container -->
    <div id="marketPane_fancy" class="bg-white border {{ $defaultTab === 'Fancy' ? '' : 'd-none' }}">
      <div class="d-flex align-items-center border-bottom py-1 px-2 small fw-bold">
        <div class="flex-grow-1"></div>
        <div style="width:56px;background:var(--lay-1);text-align:center;">No</div>
        <div style="width:56px;background:var(--back-1);text-align:center;">Yes</div>
        <div style="width:110px;text-align:center;color:#577c94;" class="fm-fancy-minmax-col">Min/Max</div>
      </div>

      @forelse($fancyList as $fIdx => $fItem)
        @php
          $fId = (string) ($fItem['exMarketId'] ?? $fItem['marketId'] ?? ('f_' . $fIdx));
          $fName = $fItem['marketName'] ?? ($fItem['runnerName'] ?? 'Session Runs');
          $fCat = $fItem['catName'] ?? ($fItem['category'] ?? 'Fancy');
          $fMin = $fItem['min'] ?? 10;
          $fMax = $fItem['max'] ?? 50000;
          $fRunner = $fItem['oddsData']['runners'][0] ?? [];
          $noRun = $fRunner['price']['lay'][0]['price'] ?? ($fItem['noRun'] ?? 0);
          $noRate = $fRunner['price']['lay'][0]['size'] ?? ($fItem['noRate'] ?? 0);
          $yesRun = $fRunner['price']['back'][0]['price'] ?? ($fItem['yesRun'] ?? 0);
          $yesRate = $fRunner['price']['back'][0]['size'] ?? ($fItem['yesRate'] ?? 0);
          $fStatus = strtoupper((string) ($fItem['oddsData']['status'] ?? $fRunner['status'] ?? 'ONLINE'));
          $isFancySusp = in_array($fStatus, ['SUSPENDED', 'SUSPEND', 'BALL RUNNING', 'CLOSED']) || ($noRun == 0 && $yesRun == 0);
          $fSlipId = 'fancySlip_' . $fIdx;
          $fKey = 'fancy_' . $fIdx;
        @endphp
        <div class="d-flex align-items-center border-bottom py-1 px-2 js-fancy-row js-market-pos-scope" data-fancy-id="{{ $fId }}" data-fcat="{{ $fCat }}" data-market-key="{{ $fKey }}" data-market-name="{{ $fName }}" data-market-category="fancy">
          <div class="flex-grow-1 d-flex justify-content-between align-items-center pe-2">
            <div class="d-flex flex-column">
              <span class="fw-bold text-dark">{{ $fName }}</span>
              <span class="fm-runner-pl js-runner-pl d-none"
                    data-market-key="{{ $fKey }}"
                    data-market-name="{{ $fName }}"
                    data-selection-id="{{ $fId }}"
                    data-runner-name="{{ $fName }}"></span>
            </div>
            <button type="button" class="btn btn-sm text-white py-0 px-2 fw-bold"
                    style="background:var(--exchange-bg);font-size:11px;"
                    onclick="showFancyBook('{{ addslashes($fName) }}', {{ (int)$noRun }}, {{ (int)$yesRun }})">
              Book
            </button>
          </div>

          <div class="d-flex position-relative">
            <div class="fm-suspend-overlay {{ $isFancySusp ? '' : 'd-none' }}"><span>Suspended</span></div>
            <!-- No (Pink) -->
            <a href="javascript:void(0)" class="fm-odd-cell wb-odd-lay js-fancy-no-cell"
               onclick="FullMkt.openInlineSlip('{{ $fSlipId }}', '{{ addslashes($fName) }}', '{{ $fId }}', '{{ addslashes($fName) }} (NO @ {{ $noRun }})', 'NO', {{ $noRate ?: 100 }}, 'fancy', {{ (float)$noRun }}, '{{ $fKey }}', this)">
              <strong>{{ $noRun }}</strong>
              <small>{{ $noRate }}</small>
            </a>
            <!-- Yes (Blue) -->
            <a href="javascript:void(0)" class="fm-odd-cell wb-odd-back ms-1 js-fancy-yes-cell"
               onclick="FullMkt.openInlineSlip('{{ $fSlipId }}', '{{ addslashes($fName) }}', '{{ $fId }}', '{{ addslashes($fName) }} (YES @ {{ $yesRun }})', 'YES', {{ $yesRate ?: 100 }}, 'fancy', {{ (float)$yesRun }}, '{{ $fKey }}', this)">
              <strong>{{ $yesRun }}</strong>
              <small>{{ $yesRate }}</small>
            </a>
          </div>

          <div style="width:110px;text-align:center;" class="fw-bold small text-dark fm-fancy-minmax-col">
            {{ $fMin }} - {{ $fMax }}
          </div>
        </div>
        <div id="{{ $fSlipId }}" class="fm-inline-slip d-none"></div>
      @empty
        <div class="p-3 text-center text-muted small fw-bold">No Fancy Markets Available</div>
      @endforelse
    </div>

    <!-- Sportsbook Tab Container (1:1 .fancy-card.sportbookmarket) -->
    <div id="marketPane_sportsbook" class="{{ $defaultTab === 'SPORTSBOOK' ? '' : 'd-none' }}">
      @forelse($sbList as $sbMktIdx => $sbMarket)
        @php
          $sbMktName = $sbMarket['marketName'] ?? 'Ball By Ball';
          $sbMktType = strtoupper((string) ($sbMarket['marketType'] ?? 'ALL'));
          $sbMin = $sbMarket['min'] ?? 100;
          $sbMax = $sbMarket['max'] ?? 100000;
          $sbMktStatus = strtoupper((string) ($sbMarket['oddsData']['status'] ?? 'ONLINE'));
          $sbRunners = $sbMarket['oddsData']['runners'] ?? ($sbMarket['runners'] ?? []);
          $sbMap = $sbMarket['runnersData'] ?? [];
          $sbTblKey = 'sb_tbl_' . $sbMktIdx;
        @endphp
        <div class="fancy-card sportbookmarket js-sb-market-card js-market-pos-scope" data-sb-mtype="{{ $sbMktType }}" data-market-key="{{ $sbTblKey }}" data-market-name="{{ $sbMktName }}" data-market-category="sportsbook">
          <div class="card-header sportbook inner-sportbook card-accrdn card-fancybet"
               onclick="FullMkt.toggleSbCard('sbBody_{{ $sbMktIdx }}')">
            <strong>{{ $sbMktName }}</strong>
            <span class="position-relative" onclick="event.stopPropagation(); FullMkt.toggleSbMinMax('sbMinMax_{{ $sbMktIdx }}')">
              <i class="fa fa-info-circle tooltip-betlimit" title="Min/Max: {{ $sbMin }} - {{ $sbMax }}"></i>
              <span id="sbMinMax_{{ $sbMktIdx }}" class="wb-sb-minmax-pop d-none">
                Min/Max: {{ $sbMin }} - {{ $sbMax }}
              </span>
            </span>
          </div>

          <div id="sbBody_{{ $sbMktIdx }}" class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover tbl-bets p-rltv sportbook-market-tbl position-relative">
                <colgroup>
                  <col style="width: 66%;">
                  <col style="width: 11%;">
                  <col style="width: 23%;">
                </colgroup>
                <tbody>
                  @foreach($sbRunners as $rIdx => $sbr)
                    @php
                      $selId = (string) ($sbr['selectionId'] ?? ($rIdx + 1));
                      $rName = $sbMap[$selId] ?? ($sbr['runnerName'] ?? ('Runner ' . ($rIdx + 1)));
                      $oddsVal = $sbr['price']['back'][0]['price'] ?? ($sbr['odds'] ?? 0);
                      $sizeVal = $sbr['price']['back'][0]['size'] ?? ($sbr['size'] ?? 100000);
                      $shortSize = is_numeric($sizeVal) ? $formatVolume($sizeVal) : $sizeVal;
                      $rStatus = strtoupper((string) ($sbr['status'] ?? $sbMktStatus));
                      $isSbSusp = in_array($rStatus, ['SUSPENDED', 'SUSPEND', 'BALLRUN', 'BALL RUNNING', 'CLOSED']) || in_array($sbMktStatus, ['SUSPENDED', 'SUSPEND', 'BALLRUN', 'CLOSED']);
                      $sbSuspTxt = str_contains($rStatus, 'BALL') || str_contains($sbMktStatus, 'BALL') ? 'BALLRUN' : 'Suspended';
                      $sbSlipRowId = 'sbTblSlip_' . $sbMktIdx . '_' . $selId;
                    @endphp
                    <tr class="odd-row js-sb-runner-row" data-selection-id="{{ $selId }}">
                      <td class="team-name sportbook-name">
                        <span class="title">{{ $rName }}</span>
                        <span class="fm-runner-pl js-runner-pl js-sb-pl-preview d-none"
                              data-market-key="{{ $sbTblKey }}"
                              data-market-name="{{ $sbMktName }}"
                              data-selection-id="{{ $selId }}"
                              data-runner-name="{{ $rName }}"></span>
                      </td>
                      <td class="back-1 count position-relative sportbook-minwidth"
                          onclick="FullMkt.openSbTableSlip('{{ $sbSlipRowId }}', '{{ addslashes($sbMktName) }}', '{{ $selId }}', '{{ addslashes($rName) }}', {{ $oddsVal > 0 ? $oddsVal : 2.0 }}, '{{ $sbTblKey }}')">
                        <div class="suspend-sportbook-cell js-sb-suspend {{ $isSbSusp ? '' : 'd-none' }}">
                          <span>{{ $sbSuspTxt }}</span>
                        </div>
                        <a href="javascript:void(0)" class="back">
                          <span class="position-relative js-sb-price" style="font-weight: 700; font-size: 14px;">{{ $oddsVal }}</span>
                          <span class="position-relative js-sb-size" style="font-size: 11px;">{{ $shortSize }}</span>
                        </a>
                      </td>
                      <td class="hidden-xs td-end-hide"></td>
                    </tr>
                    <tr id="{{ $sbSlipRowId }}_tr" class="betslipback d-none">
                      <td colspan="3">
                        <div id="{{ $sbSlipRowId }}" class="fm-inline-slip d-none" style="background:#d3edd0;border-bottom:1px solid #7e97a7;"></div>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      @empty
        <div class="p-3 bg-white border text-center text-muted small fw-bold">Sportsbook markets are currently closed.</div>
      @endforelse
    </div>
  </div>
</div>
@endif

<!-- Fancy Book Modal -->
<div class="modal fade" id="fancyBookModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header py-2 text-white" style="background:var(--header-bg);">
        <h6 class="modal-title fw-bold m-0" id="fancyBookTitle">Fancy Book</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-2">
        <table class="table table-bordered table-sm text-center small mb-0">
          <thead class="table-light">
            <tr><th>Runs</th><th>Projected P/L</th></tr>
          </thead>
          <tbody id="fancyBookBody"></tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
@php
  $initialMatchedBets = ($eventBets ?? collect())
      ->where('status', 'MATCHED')
      ->map(fn ($b) => [
          'id' => $b->id,
          'market_name' => (string) $b->market_name,
          'selection_id' => (string) $b->selection_id,
          'selection_name' => (string) $b->selection_name,
          'bet_type' => strtoupper((string) $b->bet_type),
          'odds' => (float) $b->odds,
          'stake' => (float) $b->stake,
          'category' => (string) ($b->category ?: 'exchange'),
          'run_line' => $b->run_line !== null ? (float) $b->run_line : null,
          'potential_profit' => (float) $b->potential_profit,
          'liability' => (float) $b->liability,
      ])
      ->values()
      ->toArray();
@endphp
<script src="https://www.gstatic.com/firebasejs/10.8.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.8.0/firebase-firestore-compat.js"></script>
<script>
  window.FullMkt = {
    sportId: '{{ $event->sport_id }}',
    eventId: '{{ $event->event_id }}',
    eventName: '{{ addslashes($event->event_name) }}',
    activeSlip: null,
    stake: 100,
    placedBets: @json($initialMatchedBets),

    init: function () {
      this.recalcAllPositions();
      this.initFirebaseRealtimeSync();
      setInterval(() => this.pollLiveMarket(), 2000);
    },

    initFirebaseRealtimeSync: function () {
      if (typeof firebase === 'undefined') return;
      const sId = String(this.sportId);
      const evId = String(this.eventId);

      const configs = {
        '4': {
          apiKey: "AIzaSyDaMs1YEOXQ7V7HmsfR5Jq-6XKl64m40lM",
          authDomain: "t20-cricket-add9b.firebaseapp.com",
          projectId: "t20-cricket-add9b",
          storageBucket: "t20-cricket-add9b.appspot.com",
          messagingSenderId: "449000859514",
          appId: "1:449000859514:web:e0b2cdb52895251bf9d8c3"
        },
        '1': {
          apiKey: "AIzaSyDYVXCzVHdLcBekTiLe7kayipIqzl1YYwE",
          authDomain: "soccer-57fb9.firebaseapp.com",
          projectId: "soccer-57fb9",
          storageBucket: "soccer-57fb9.appspot.com",
          messagingSenderId: "457682119930",
          appId: "1:457682119930:web:ae10649b51b3b4bc096d43"
        },
        '2': {
          apiKey: "AIzaSyBU7jAPhaqXDElWY0Dv0XT64EobhRP02OM",
          authDomain: "tennis-101ef.firebaseapp.com",
          projectId: "tennis-101ef",
          storageBucket: "tennis-101ef.appspot.com",
          messagingSenderId: "374641683570",
          appId: "1:374641683570:web:1f0449bf7af38f79bb26eb"
        },
        'default': {
          apiKey: "AIzaSyDSSs68xjpnOgXa5uIfvnXj2pDPtR98IX4",
          authDomain: "other-sports-1bba3.firebaseapp.com",
          projectId: "other-sports-1bba3",
          storageBucket: "other-sports-1bba3.appspot.com",
          messagingSenderId: "998572867339",
          appId: "1:998572867339:web:6b4659ceff82238d67ac0e"
        }
      };

      try {
        const cfg = configs[sId] || configs['default'];
        const appName = 'wb24_realtime_' + sId;
        let app;
        try {
          app = firebase.app(appName);
        } catch (e) {
          app = firebase.initializeApp(cfg, appName);
        }
        const db = firebase.firestore(app);

        // 1. Live Match Odds (Betfair)
        db.collection('Betfair').where('exEventId', '==', evId).onSnapshot((snap) => {
          const moList = [];
          snap.forEach(doc => {
            const d = doc.data();
            if (d) moList.push(d);
          });
          if (moList.length > 0) {
            this.updateMatchOddsDom(moList);
          }
        }, () => {});

        // 2. Live Bookmakers
        db.collection('Bookmakers').where('exEventId', '==', evId).onSnapshot((snap) => {
          const bmList = [];
          snap.forEach(doc => {
            const d = doc.data();
            if (d) bmList.push(d);
          });
          if (bmList.length > 0) {
            this.updateBookmakersDom(bmList);
          }
        }, () => {});

        // 3. Live Fancy Bet
        db.collection('Fancy').where('exEventId', '==', evId).where('isClosed', '==', 0).onSnapshot((snap) => {
          const fList = [];
          snap.forEach(doc => {
            const d = doc.data();
            if (d) fList.push(d);
          });
          if (fList.length > 0) {
            this.updateFancyDom(fList);
          }
        }, () => {});

        // 4. Live Sportsbook
        db.collection('Sportsbook').where('exEventId', '==', evId).where('isClosed', '==', 0).onSnapshot((snap) => {
          const sbList = [];
          snap.forEach(doc => {
            const d = doc.data();
            if (d) sbList.push(d);
          });
          if (sbList.length > 0) {
            this.updateSportsbookDom(sbList);
          }
        }, () => {});

        // 5. Live Universe Scorecard
        const scoreCfg = {
          apiKey: "AIzaSyBjh-y7heT8kshYPCpKu403q9UzaCKf26U",
          authDomain: "universe-score.firebaseapp.com",
          projectId: "universe-score",
          storageBucket: "universe-score.appspot.com",
          messagingSenderId: "228835528040",
          appId: "1:228835528040:web:43dca760c279a64b169add"
        };
        let scoreApp;
        try {
          scoreApp = firebase.app('wb24_score');
        } catch (e) {
          scoreApp = firebase.initializeApp(scoreCfg, 'wb24_score');
        }
        const scoreDb = firebase.firestore(scoreApp);
        const collName = sId === '2' ? 'tennisScore' : (sId === '1' ? 'soccerScore' : 'cricketScore');
        scoreDb.collection(collName).where('eventId', '==', evId).onSnapshot((snap) => {
          snap.forEach(doc => {
            const sc = doc.data();
            if (sc) this.updateFirebaseScorecard(sc);
          });
        }, () => {});
      } catch (err) {
        console.warn('Realtime streaming fallback to HTTP polling active.', err);
      }
    },

    updateFirebaseScorecard: function (sc) {
      if (!sc) return;
      const card = document.getElementById('wbUniverseScorecard');
      if (!card) return;

      const setTxt = (sel, val) => {
        const el = card.querySelector(sel);
        if (el && val !== undefined && val !== null) el.textContent = val;
      };

      if (sc.commentary) {
        setTxt('.js-sc-commentary-text', sc.commentary);
      }

      const scoreObj = sc.slider?.[0]?.score;
      if (!scoreObj) return;

      if (scoreObj.statusCommentry) {
        setTxt('.js-sc-status-commentary', scoreObj.statusCommentry);
      }

      const items = scoreObj.scoreItems || [];
      if (items[0]) {
        const t1Data = items[0].scoreData || [];
        const sItem = t1Data.find(d => d.displayText === 'SCORE' || d.displayText === '1st INNINGS');
        const oItem = t1Data.find(d => d.displayText === 'OVS' || (d.displayText && d.displayText.includes('Overs')));
        const rrItem = t1Data.find(d => d.displayText === 'RR');
        const fItem = t1Data.find(d => d.displayText === '4S');
        const sxItem = t1Data.find(d => d.displayText === '6S');
        const wItem = t1Data.find(d => d.displayText === 'WIDES');

        if (sItem?.value) setTxt('.js-sc-t1-score', sItem.value);
        if (oItem?.value) setTxt('.js-sc-t1-ovs', oItem.value);
        if (rrItem?.value) setTxt('.js-sc-t1-rr', rrItem.value);
        if (fItem?.value) setTxt('.js-sc-t1-4s', fItem.value);
        if (sxItem?.value) setTxt('.js-sc-t1-6s', sxItem.value);
        if (wItem?.value) setTxt('.js-sc-t1-wides', wItem.value);
      }

      if (items[1]) {
        const t2Data = items[1].scoreData || [];
        const sItem = t2Data.find(d => d.displayText === 'SCORE' || d.displayText === '1st INNINGS');
        const oItem = t2Data.find(d => d.displayText === 'OVS' || (d.displayText && d.displayText.includes('Overs')));
        const rrItem = t2Data.find(d => d.displayText === 'RR');
        const fItem = t2Data.find(d => d.displayText === '4S');
        const sxItem = t2Data.find(d => d.displayText === '6S');
        const wItem = t2Data.find(d => d.displayText === 'WIDES');

        if (sItem?.value) setTxt('.js-sc-t2-score', sItem.value);
        if (oItem?.value) setTxt('.js-sc-t2-ovs', oItem.value);
        if (rrItem?.value) setTxt('.js-sc-t2-rr', rrItem.value);
        if (fItem?.value) setTxt('.js-sc-t2-4s', fItem.value);
        if (sxItem?.value) setTxt('.js-sc-t2-6s', sxItem.value);
        if (wItem?.value) setTxt('.js-sc-t2-wides', wItem.value);
      }

      if (Array.isArray(scoreObj.batsman) && scoreObj.batsman.length > 0) {
        const bText = scoreObj.batsman.map(b => `${b.name || 'Batsman'} ${b.run || 0}${b.isFacing ? '*' : ''} (${b.ball || 0})`).join(', ');
        const bEl = card.querySelector('.js-sc-batsmen');
        if (bEl && bText) bEl.innerHTML = `<span style="color:#f8e71c;">BAT:</span> ${bText}`;
      }

      if (Array.isArray(scoreObj.bowler) && scoreObj.bowler.length > 0) {
        const b = scoreObj.bowler[0];
        const bowlText = `${b.name || 'Bowler'} ${b.wicket || 0}-${b.run || 0} (${b.over || 0})`;
        const bowlEl = card.querySelector('.js-sc-bowler');
        if (bowlEl && bowlText) bowlEl.innerHTML = `<span style="color:#00d2ff;">BOWL:</span> ${bowlText}`;
      }

      if (Array.isArray(scoreObj.balls) && scoreObj.balls.length > 0) {
        const ballsWrap = card.querySelector('.wb-sc-balls-list');
        if (ballsWrap) {
          ballsWrap.innerHTML = scoreObj.balls.map(ball => {
            const bStr = String(ball).trim();
            const bCls = bStr === '4' ? 'ball-four' : (bStr === '6' ? 'ball-six' : (bStr.includes('W') ? 'ball-wicket' : ''));
            return `<span class="wb-sc-ball ${bCls}">${bStr || '0'}</span>`;
          }).join('');
        }
      }
    },

    formatVol: function (val) {
      const n = parseFloat(val || 0);
      if (n >= 1000000) return +(n / 1000000).toFixed(2) + 'M';
      if (n >= 1000) return +(n / 1000).toFixed(1) + 'K';
      return n > 0 ? Math.round(n) : '0';
    },

    formatBookPl: function (val) {
      const abs = Math.abs(parseFloat(val) || 0);
      return '(' + abs.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ')';
    },

    computeSingleBetOutcome: function (bet, runnerSelId, runnerName) {
      const stake = parseFloat(bet.stake) || 0;
      const odds = parseFloat(bet.odds) || 0;
      if (stake <= 0 || odds <= 0) return 0;

      const betSelId = String(bet.selection_id ?? bet.selectionId ?? '').trim();
      const betSelName = String(bet.selection_name ?? bet.selectionName ?? '').trim().toLowerCase();
      const rSelId = String(runnerSelId || '').trim();
      const rName = String(runnerName || '').trim().toLowerCase();

      const isTargetRunner = (betSelId !== '' && rSelId !== '' && betSelId === rSelId) ||
                             (betSelName !== '' && rName !== '' && (betSelName === rName || betSelName.startsWith(rName + ' (')));

      const betType = String(bet.bet_type ?? bet.betType ?? 'BACK').toUpperCase();
      const cat = String(bet.category || 'exchange').toLowerCase();

      if (cat === 'bookmaker') {
        const rate = odds / 100;
        const winProfit = stake * rate;
        if (betType === 'BACK') {
          return isTargetRunner ? winProfit : -stake;
        } else {
          return isTargetRunner ? -winProfit : stake;
        }
      }

      if (cat === 'fancy' || betType === 'YES' || betType === 'NO') {
        if (!isTargetRunner) return 0;
        const rate = odds / 100;
        // In Fancy markets, show net exposure / liability on that session line
        return betType === 'YES' ? -stake : -(stake * rate);
      }

      // Standard Exchange (Match Odds) & Sportsbook decimal odds: stake * (odds - 1)
      const winProfit = stake * (odds - 1);
      if (betType === 'BACK') {
        return isTargetRunner ? winProfit : -stake;
      } else {
        return isTargetRunner ? -winProfit : stake;
      }
    },

    recalcAllPositions: function () {
      const scopes = document.querySelectorAll('.js-market-pos-scope');
      scopes.forEach((scope) => {
        const mktKey = scope.dataset.marketKey || '';
        const mktName = scope.dataset.marketName || '';
        const runnerEls = scope.querySelectorAll('.js-runner-pl');
        if (!runnerEls.length) return;

        // Gather matching placed bets for this market
        const mktPlacedBets = (this.placedBets || []).filter((b) => {
          const bMkt = String(b.market_name || '').trim().toLowerCase();
          const sMkt = String(mktName || '').trim().toLowerCase();
          return bMkt !== '' && bMkt === sMkt;
        });

        // Check if active inline bet slip belongs to this market
        const slipActiveHere = this.activeSlip &&
          ((this.activeSlip.marketKey && this.activeSlip.marketKey === mktKey) ||
           (!this.activeSlip.marketKey && String(this.activeSlip.marketName || '').toLowerCase() === String(mktName || '').toLowerCase()));

        const previewStake = slipActiveHere ? (parseFloat(this.stake) || 0) : 0;
        const previewOdds = slipActiveHere ? (parseFloat(this.activeSlip.odds) || 0) : 0;
        const hasPreview = slipActiveHere && previewStake > 0 && previewOdds > 0;
        const hasActivity = mktPlacedBets.length > 0 || hasPreview;

        runnerEls.forEach((el) => {
          if (!hasActivity) {
            el.classList.add('d-none');
            el.classList.remove('pl-pos', 'pl-neg');
            el.textContent = '';
            return;
          }

          const rSelId = el.dataset.selectionId || '';
          const rName = el.dataset.runnerName || '';
          let net = 0;

          mktPlacedBets.forEach((b) => {
            net += this.computeSingleBetOutcome(b, rSelId, rName);
          });

          if (hasPreview) {
            net += this.computeSingleBetOutcome({
              selection_id: this.activeSlip.selectionId,
              selection_name: this.activeSlip.selectionName,
              bet_type: this.activeSlip.betType,
              odds: previewOdds,
              stake: previewStake,
              category: this.activeSlip.category
            }, rSelId, rName);
          }

          el.classList.remove('d-none', 'pl-pos', 'pl-neg');
          el.classList.add(net >= 0 ? 'pl-pos' : 'pl-neg');
          el.textContent = this.formatBookPl(net);
        });
      });
    },

    onBetPlaced: function (bet) {
      if (!bet) return;
      this.placedBets.push({
        id: bet.id,
        market_name: String(bet.market_name || ''),
        selection_id: String(bet.selection_id || ''),
        selection_name: String(bet.selection_name || ''),
        bet_type: String(bet.bet_type || 'BACK').toUpperCase(),
        odds: parseFloat(bet.odds) || 0,
        stake: parseFloat(bet.stake) || 0,
        category: String(bet.category || 'exchange'),
        run_line: bet.run_line ?? null
      });
      this.recalcAllPositions();
    },

    onBetRemoved: function (betId) {
      this.placedBets = (this.placedBets || []).filter(b => String(b.id) !== String(betId));
      this.recalcAllPositions();
    },

    syncFromSidebarSlip: function (odds, stake) {
      if (!this.activeSlip) return;
      if (odds && !isNaN(odds)) {
        this.activeSlip.odds = parseFloat(odds);
        const oInp = document.getElementById('fmInlineOdds');
        if (oInp && document.activeElement !== oInp) oInp.value = this.activeSlip.odds;
      }
      if (stake !== undefined && !isNaN(stake)) {
        this.stake = parseFloat(stake) || 0;
        const sInp = document.getElementById('fmInlineStake');
        if (sInp && document.activeElement !== sInp) sInp.value = this.stake;
      }
      this.recalcAllPositions();
    },

    pollLiveMarket: async function () {
      try {
        const res = await fetch(`/api/exchange/market/live/${this.sportId}/${this.eventId}`);
        if (!res.ok) return;
        const json = await res.json();
        if (json.liveScore && typeof json.liveScore === 'object') {
          this.updateLiveScoreDom(json.liveScore);
        }
        const mData = json.data || {};
        if (Array.isArray(mData.matchOddsData) && mData.matchOddsData.length > 0) {
          this.updateMatchOddsDom(mData.matchOddsData);
        }
        if (Array.isArray(mData.bookmakersData) && mData.bookmakersData.length > 0) {
          this.updateBookmakersDom(mData.bookmakersData);
        }
        if (Array.isArray(mData.fancyData) && mData.fancyData.length > 0) {
          this.updateFancyDom(mData.fancyData);
        }
        const sbData = mData.sportsbookData || json.sportsbookData;
        if (Array.isArray(sbData) && sbData.length > 0) {
          this.updateSportsbookDom(sbData);
        }
      } catch (e) {}
    },

    updateMatchOddsDom: function (moList) {
      moList.forEach((mo, moIdx) => {
        const moTitle = mo.marketName || 'Match Odds';
        const moStatus = String(mo.oddsData?.status || 'OPEN').toUpperCase();
        const runners = mo.oddsData?.runners || [];
        const card = document.querySelector(`.js-market-pos-scope[data-market-key="mo_${moIdx}"]`) ||
                     document.querySelectorAll(`.js-market-pos-scope[data-market-category="exchange"]`)[moIdx] ||
                     document.querySelector(`.js-market-pos-scope[data-market-category="exchange"]`) ||
                     document.getElementById('fmSection_match_odds');
        if (!card) return;

        if (mo.oddsData?.totalMatched) {
          const mTxt = card.querySelector('.fm-matched-txt strong');
          if (mTxt) mTxt.textContent = '€ ' + this.formatVol(mo.oddsData.totalMatched);
        }

        const allRows = card.querySelectorAll('.fm-runner-row');

        runners.forEach((r, rIdx) => {
          const selId = String(r.selectionId ?? '');
          let row = card.querySelector(`.fm-runner-row[data-selection-id="${selId}"]`);
          if (!row && allRows[rIdx]) {
            row = allRows[rIdx];
          }
          if (!row) return;

          const rStatus = String(r.status || moStatus).toUpperCase();
          const isBallrun = rStatus.includes('BALL') || moStatus.includes('BALL');
          const isSusp = ['SUSPENDED', 'SUSPEND', 'BALLRUN', 'BALL RUNNING', 'CLOSED'].includes(rStatus) ||
                         ['SUSPENDED', 'SUSPEND', 'BALLRUN', 'CLOSED'].includes(moStatus);
          const suspEl = row.querySelector('.fm-suspend-overlay');
          if (suspEl) {
            suspEl.classList.toggle('d-none', !isSusp);
            const span = suspEl.querySelector('span');
            if (span) {
              span.textContent = isBallrun ? 'Ballrun' : 'Suspended';
              span.className = isBallrun ? 'fm-ballrun-badge' : 'fm-suspended-badge';
            }
          }

          const backs = r.price?.back || [];
          const lays = r.price?.lay || [];

          [2, 1, 0].forEach((bi) => {
            const cell = row.querySelector(`.js-mo-cell[data-mo-type="back"][data-mo-level="${bi}"]`);
            if (!cell) return;
            const bp = backs[bi]?.price ?? 0;
            const bs = backs[bi]?.size ?? 0;
            const pStr = (bp >= 1000) ? Number(bp).toLocaleString() : (bp > 0 ? String(bp) : '0');
            const sStr = this.formatVol(bs);

            const strEl = cell.querySelector('strong');
            const smEl = cell.querySelector('small');
            if (strEl && strEl.textContent.trim() !== pStr) {
              strEl.textContent = pStr;
              cell.classList.add('flash-change');
              setTimeout(() => cell.classList.remove('flash-change'), 600);
            }
            if (smEl) smEl.textContent = sStr;

            cell.setAttribute('onclick', `FullMkt.openInlineSlip('moSlip_${moIdx}_${selId}', '${moTitle.replace(/'/g, "\\'")}', '${selId}', '${(r.runnerName || row.getAttribute('data-runner-name') || '').replace(/'/g, "\\'")}', 'BACK', ${bp > 0 ? bp : 1.90}, 'exchange', null, 'mo_${moIdx}', this)`);
          });

          [0, 1, 2].forEach((li) => {
            const cell = row.querySelector(`.js-mo-cell[data-mo-type="lay"][data-mo-level="${li}"]`);
            if (!cell) return;
            const lp = lays[li]?.price ?? 0;
            const ls = lays[li]?.size ?? 0;
            const pStr = (lp >= 1000) ? Number(lp).toLocaleString() : (lp > 0 ? String(lp) : '0');
            const sStr = this.formatVol(ls);

            const strEl = cell.querySelector('strong');
            const smEl = cell.querySelector('small');
            if (strEl && strEl.textContent.trim() !== pStr) {
              strEl.textContent = pStr;
              cell.classList.add('flash-change');
              setTimeout(() => cell.classList.remove('flash-change'), 600);
            }
            if (smEl) smEl.textContent = sStr;

            cell.setAttribute('onclick', `FullMkt.openInlineSlip('moSlip_${moIdx}_${selId}', '${moTitle.replace(/'/g, "\\'")}', '${selId}', '${(r.runnerName || row.getAttribute('data-runner-name') || '').replace(/'/g, "\\'")}', 'LAY', ${lp > 0 ? lp : 1.92}, 'exchange', null, 'mo_${moIdx}', this)`);
          });
        });
      });
    },

    updateBookmakersDom: function (bmList) {
      bmList.forEach((bm, bmIdx) => {
        const bmTitle = bm.marketName || 'Bookmaker';
        const bmStatus = String(bm.oddsData?.status || 'ONLINE').toUpperCase();
        const runners = bm.oddsData?.runners || [];
        const card = document.querySelector(`.js-market-pos-scope[data-market-category="bookmaker"]`) ||
                     document.getElementById('fmSection_bookmaker');
        if (!card) return;

        const allRows = card.querySelectorAll('.fm-runner-row');

        runners.forEach((r, rIdx) => {
          const selId = String(r.selectionId ?? '');
          let row = card.querySelector(`.fm-runner-row[data-selection-id="${selId}"]`);
          if (!row && allRows[rIdx]) {
            row = allRows[rIdx];
          }
          if (!row) return;

          const rStatus = String(r.status || bmStatus).toUpperCase();
          const isSusp = ['SUSPENDED', 'SUSPEND', 'BALLRUN', 'BALL RUNNING', 'CLOSED'].includes(rStatus) ||
                         ['SUSPENDED', 'SUSPEND', 'BALLRUN', 'CLOSED'].includes(bmStatus);
          const isBallrun = rStatus.includes('BALL') || bmStatus.includes('BALL');
          const suspTxt = isBallrun ? 'Ballrun' : 'Suspended';
          const suspEl = row.querySelector('.fm-suspend-overlay');
          if (suspEl) {
            suspEl.classList.toggle('d-none', !isSusp);
            const span = suspEl.querySelector('span');
            if (span) {
              span.textContent = suspTxt;
              span.className = isBallrun ? 'fm-ballrun-badge' : 'fm-suspended-badge';
            }
          }

          const backs = r.price?.back || [];
          const lays = r.price?.lay || [];

          [2, 1, 0].forEach((bi) => {
            const cell = row.querySelector(`.js-bm-cell[data-bm-type="back"][data-bm-level="${bi}"]`);
            if (!cell) return;
            const rawP = backs[bi]?.price;
            const bp = (rawP !== null && rawP !== undefined && !isNaN(rawP) && rawP > 0) ? rawP : 0;
            const bs = backs[bi]?.size ?? '5M';
            const pStr = bp > 0 ? String(bp) : '-';
            const sStr = typeof bs === 'number' ? this.formatVol(bs) : String(bs || '-');

            const strEl = cell.querySelector('strong');
            const smEl = cell.querySelector('small');
            if (strEl && strEl.textContent.trim() !== pStr) {
              strEl.textContent = pStr;
              cell.classList.add('flash-change');
              setTimeout(() => cell.classList.remove('flash-change'), 600);
            }
            if (smEl) smEl.textContent = sStr;

            cell.setAttribute('onclick', `FullMkt.openInlineSlip('bmSlip_${bmIdx}_${selId}', '${bmTitle.replace(/'/g, "\\'")}', '${selId}', '${(r.runnerName || row.getAttribute('data-runner-name') || '').replace(/'/g, "\\'")}', 'BACK', ${bp > 0 ? bp : 90}, 'bookmaker', null, 'bm_${bmIdx}', this)`);
          });

          [0, 1, 2].forEach((li) => {
            const cell = row.querySelector(`.js-bm-cell[data-bm-type="lay"][data-bm-level="${li}"]`);
            if (!cell) return;
            const rawP = lays[li]?.price;
            const lp = (rawP !== null && rawP !== undefined && !isNaN(rawP) && rawP > 0) ? rawP : 0;
            const ls = lays[li]?.size ?? '5M';
            const pStr = lp > 0 ? String(lp) : '-';
            const sStr = typeof ls === 'number' ? this.formatVol(ls) : String(ls || '-');

            const strEl = cell.querySelector('strong');
            const smEl = cell.querySelector('small');
            if (strEl && strEl.textContent.trim() !== pStr) {
              strEl.textContent = pStr;
              cell.classList.add('flash-change');
              setTimeout(() => cell.classList.remove('flash-change'), 600);
            }
            if (smEl) smEl.textContent = sStr;

            cell.setAttribute('onclick', `FullMkt.openInlineSlip('bmSlip_${bmIdx}_${selId}', '${bmTitle.replace(/'/g, "\\'")}', '${selId}', '${(r.runnerName || row.getAttribute('data-runner-name') || '').replace(/'/g, "\\'")}', 'LAY', ${lp > 0 ? lp : 92}, 'bookmaker', null, 'bm_${bmIdx}', this)`);
          });
        });
      });
    },

    updateFancyDom: function (fancyList) {
      fancyList.forEach((fItem, fIdx) => {
        const fId = String(fItem.exMarketId || fItem.marketId || ('f_' + fIdx));
        const fName = String(fItem.marketName || fItem.runnerName || 'Session Runs').trim();
        const fRunner = fItem.oddsData?.runners?.[0] || {};
        const noRun = fRunner.price?.lay?.[0]?.price ?? fItem.noRun ?? 0;
        const noRate = fRunner.price?.lay?.[0]?.size ?? fItem.noRate ?? 0;
        const yesRun = fRunner.price?.back?.[0]?.price ?? fItem.yesRun ?? 0;
        const yesRate = fRunner.price?.back?.[0]?.size ?? fItem.yesRate ?? 0;
        const fStatus = String(fItem.oddsData?.status || fRunner.status || 'ONLINE').toUpperCase();
        const isSusp = ['SUSPENDED', 'SUSPEND', 'BALLRUN', 'BALL RUNNING', 'CLOSED'].includes(fStatus) || (noRun == 0 && yesRun == 0);

        let row = document.querySelector(`.js-fancy-row[data-fancy-id="${fId}"]`) ||
                  document.querySelector(`.js-fancy-row[data-market-name="${fName}"]`);
        if (!row) {
          const pane = document.getElementById('marketPane_fancy');
          if (pane) {
            const div = document.createElement('div');
            div.className = 'd-flex align-items-center border-bottom py-1 px-2 js-fancy-row js-market-pos-scope';
            div.setAttribute('data-fancy-id', fId);
            div.setAttribute('data-fcat', fItem.catName || 'Fancy');
            div.setAttribute('data-market-key', 'fancy_' + fId);
            div.setAttribute('data-market-name', fName);
            div.setAttribute('data-market-category', 'fancy');
            div.innerHTML = `
              <div class="flex-grow-1 d-flex justify-content-between align-items-center pe-2">
                <div class="d-flex flex-column">
                  <span class="fw-bold text-dark">${fName}</span>
                  <span class="fm-runner-pl js-runner-pl d-none" data-market-key="fancy_${fId}" data-market-name="${fName}" data-selection-id="${fId}" data-runner-name="${fName}"></span>
                </div>
                <button type="button" class="btn btn-sm text-white py-0 px-2 fw-bold" style="background:var(--exchange-bg);font-size:11px;" onclick="showFancyBook('${fName.replace(/'/g, "\\'")}', ${parseInt(noRun)}, ${parseInt(yesRun)})">Book</button>
              </div>
              <div class="d-flex position-relative">
                <div class="fm-suspend-overlay ${isSusp ? '' : 'd-none'}"><span>Suspended</span></div>
                <a href="javascript:void(0)" class="fm-odd-cell wb-odd-lay js-fancy-no-cell" onclick="FullMkt.openInlineSlip('fancySlip_${fIdx}', '${fName.replace(/'/g, "\\'")}', '${fId}', '${fName.replace(/'/g, "\\'")} (NO @ ${noRun})', 'NO', ${noRate || 100}, 'fancy', ${parseFloat(noRun)}, 'fancy_${fId}', this)">
                  <strong>${noRun || '0'}</strong>
                  <small>${noRate || '0'}</small>
                </a>
                <a href="javascript:void(0)" class="fm-odd-cell wb-odd-back ms-1 js-fancy-yes-cell" onclick="FullMkt.openInlineSlip('fancySlip_${fIdx}', '${fName.replace(/'/g, "\\'")}', '${fId}', '${fName.replace(/'/g, "\\'")} (YES @ ${yesRun})', 'YES', ${yesRate || 100}, 'fancy', ${parseFloat(yesRun)}, 'fancy_${fId}', this)">
                  <strong>${yesRun || '0'}</strong>
                  <small>${yesRate || '0'}</small>
                </a>
              </div>
              <div style="width:110px;text-align:center;" class="fw-bold small text-dark fm-fancy-minmax-col">10 - 50000</div>
            `;
            pane.appendChild(div);
            row = div;
          }
        }
        if (!row) return;

        const isBallrun = fStatus.includes('BALL');
        const suspTxt = isBallrun ? 'Ballrun' : 'Suspended';
        const suspEl = row.querySelector('.fm-suspend-overlay');
        if (suspEl) {
          suspEl.classList.toggle('d-none', !isSusp);
          const span = suspEl.querySelector('span');
          if (span) {
            span.textContent = suspTxt;
            span.className = isBallrun ? 'fm-ballrun-badge' : 'fm-suspended-badge';
          }
        }

        const noCell = row.querySelector('.js-fancy-no-cell');
        if (noCell) {
          const strEl = noCell.querySelector('strong');
          const smEl = noCell.querySelector('small');
          const noStr = String(noRun || '0');
          const rateStr = String(noRate || '0');
          if (strEl && strEl.textContent.trim() !== noStr) {
            strEl.textContent = noStr;
            noCell.classList.add('flash-change');
            setTimeout(() => noCell.classList.remove('flash-change'), 600);
          }
          if (smEl) smEl.textContent = rateStr;
          noCell.setAttribute('onclick', `FullMkt.openInlineSlip('fancySlip_${fIdx}', '${fName.replace(/'/g, "\\'")}', '${fId}', '${fName.replace(/'/g, "\\'")} (NO @ ${noRun})', 'NO', ${noRate || 100}, 'fancy', ${parseFloat(noRun)}, 'fancy_${fIdx}', this)`);
        }

        const yesCell = row.querySelector('.js-fancy-yes-cell');
        if (yesCell) {
          const strEl = yesCell.querySelector('strong');
          const smEl = yesCell.querySelector('small');
          const yesStr = String(yesRun || '0');
          const rateStr = String(yesRate || '0');
          if (strEl && strEl.textContent.trim() !== yesStr) {
            strEl.textContent = yesStr;
            yesCell.classList.add('flash-change');
            setTimeout(() => yesCell.classList.remove('flash-change'), 600);
          }
          if (smEl) smEl.textContent = rateStr;
          yesCell.setAttribute('onclick', `FullMkt.openInlineSlip('fancySlip_${fIdx}', '${fName.replace(/'/g, "\\'")}', '${fId}', '${fName.replace(/'/g, "\\'")} (YES @ ${yesRun})', 'YES', ${yesRate || 100}, 'fancy', ${parseFloat(yesRun)}, 'fancy_${fIdx}', this)`);
        }
      });
    },

    updateSportsbookDom: function (sbData) {
      sbData.forEach((mkt) => {
        const mktStatus = String(mkt?.oddsData?.status || 'ONLINE').toUpperCase();
        const runners = mkt?.oddsData?.runners || mkt?.runners || [];
        runners.forEach((r) => {
          const selId = String(r.selectionId || '');
          const row = document.querySelector(`.js-sb-runner-row[data-selection-id="${selId}"]`);
          if (!row) return;
          const priceVal = r?.price?.back?.[0]?.price ?? r?.odds ?? 0;
          const sizeVal = r?.price?.back?.[0]?.size ?? r?.size ?? 100000;
          const rStatus = String(r?.status || mktStatus).toUpperCase();
          const isSusp = ['SUSPENDED', 'SUSPEND', 'BALLRUN', 'BALL RUNNING', 'CLOSED'].includes(rStatus) ||
                         ['SUSPENDED', 'SUSPEND', 'BALLRUN', 'CLOSED'].includes(mktStatus);
          const suspTxt = (rStatus.includes('BALL') || mktStatus.includes('BALL')) ? 'BALLRUN' : 'Suspended';

          const priceEl = row.querySelector('.js-sb-price');
          const sizeEl = row.querySelector('.js-sb-size');
          const suspEl = row.querySelector('.js-sb-suspend');

          if (priceEl && priceVal !== undefined) priceEl.textContent = priceVal;
          if (sizeEl && sizeVal !== undefined) sizeEl.textContent = typeof sizeVal === 'number' ? this.formatVol(sizeVal) : sizeVal;
          if (suspEl) {
            suspEl.classList.toggle('d-none', !isSusp);
            const span = suspEl.querySelector('span');
            if (span) span.textContent = suspTxt;
          }
        });
      });
    },

    updateLiveScoreDom: function (sc) {
      const card = document.getElementById('wbUniverseScorecard');
      if (!card || !sc) return;

      const setTxt = (sel, val) => {
        const el = card.querySelector(sel);
        if (el && val !== undefined && val !== null) el.textContent = val;
      };

      if (String(sc.sportId) === '1') {
        setTxt('.js-sc-soc-g1', sc.score1 ?? '0');
        setTxt('.js-sc-soc-ht1', sc.htScore1 ?? '0');
        setTxt('.js-sc-soc-cor1', sc.corner1 ?? '0');
        setTxt('.js-sc-soc-yc1', sc.yellowCard1 ?? '0');
        setTxt('.js-sc-soc-rc1', sc.redCard1 ?? '0');
        setTxt('.js-sc-soc-g2', sc.score2 ?? '0');
        setTxt('.js-sc-soc-ht2', sc.htScore2 ?? '0');
        setTxt('.js-sc-soc-cor2', sc.corner2 ?? '0');
        setTxt('.js-sc-soc-yc2', sc.yellowCard2 ?? '0');
        setTxt('.js-sc-soc-rc2', sc.redCard2 ?? '0');
        setTxt('.js-sc-soc-status', sc.status ?? 'IN PLAY');
        if (sc.stage) setTxt('.js-sc-soc-stage', String(sc.stage).toUpperCase());
        return;
      }

      if (String(sc.sportId) === '2') {
        setTxt('.js-sc-ten-sets1', sc.score1 ?? '0');
        setTxt('.js-sc-ten-s1-1', sc.set1Score1 ?? '-');
        setTxt('.js-sc-ten-s2-1', sc.set2Score1 ?? '-');
        setTxt('.js-sc-ten-s3-1', sc.set3Score1 ?? '-');
        setTxt('.js-sc-ten-pts1', sc.points1 ?? '0');
        setTxt('.js-sc-ten-sets2', sc.score2 ?? '0');
        setTxt('.js-sc-ten-s1-2', sc.set1Score2 ?? '-');
        setTxt('.js-sc-ten-s2-2', sc.set2Score2 ?? '-');
        setTxt('.js-sc-ten-s3-2', sc.set3Score2 ?? '-');
        setTxt('.js-sc-ten-pts2', sc.points2 ?? '0');
        setTxt('.js-sc-ten-status', sc.status ?? 'IN PLAY');
        setTxt('.js-sc-t1-srv', sc.serving === 1 ? '●' : '');
        setTxt('.js-sc-t2-srv', sc.serving === 2 ? '●' : '');
        return;
      }

      if (sc.isTestMatch) {
        const inn1_1 = (sc.score1 || '-') + (sc.overs1 && !sc.score3 ? ` (${sc.overs1})` : '');
        const inn1_2 = (sc.score3 || '-') + (sc.overs3 ? ` (${sc.overs3})` : '');
        const inn2_1 = (sc.score2 || '-') + (sc.overs2 && !sc.score4 ? ` (${sc.overs2})` : '');
        const inn2_2 = (sc.score4 || '-') + (sc.overs4 ? ` (${sc.overs4})` : '');
        setTxt('.js-sc-t1-inn1', inn1_1);
        setTxt('.js-sc-t1-inn2', inn1_2);
        setTxt('.js-sc-t2-inn1', inn2_1);
        setTxt('.js-sc-t2-inn2', inn2_2);
      } else {
        setTxt('.js-sc-t1-score', sc.score1 || '-');
        setTxt('.js-sc-t1-ovs', sc.overs1 || '-');
        setTxt('.js-sc-t1-4s', sc.totalFours1 ?? 0);
        setTxt('.js-sc-t1-6s', sc.totalSixes1 ?? 0);
        setTxt('.js-sc-t1-wides', sc.totalWides1 ?? 0);
        setTxt('.js-sc-t2-score', sc.score2 || '-');
        setTxt('.js-sc-t2-ovs', sc.overs2 || '-');
        setTxt('.js-sc-t2-4s', sc.totalFours2 ?? 0);
        setTxt('.js-sc-t2-6s', sc.totalSixes2 ?? 0);
        setTxt('.js-sc-t2-wides', sc.totalWides2 ?? 0);
      }

      setTxt('.js-sc-t1-rr', sc.team1Flag && sc.runRate ? sc.runRate : (sc.isTestMatch ? '' : '0.00'));
      setTxt('.js-sc-t2-rr', sc.team2Flag && sc.runRate ? sc.runRate : (sc.isTestMatch ? '' : '0.00'));

      const bParts = [];
      if (sc.batsman1Name) bParts.push(`${sc.batsman1Name} ${sc.batsman1Runs ?? 0}`);
      if (sc.batsman2Name) bParts.push(`${sc.batsman2Name} ${sc.batsman2Runs ?? 0}`);
      const batText = sc.batsmenText || bParts.join(', ');
      const bowlText = sc.bowlerText || (sc.bowlerName ? `${sc.bowlerName} ${sc.bowlerRuns ?? 0}` : '');
      const batEl = card.querySelector('.js-sc-batsmen');
      if (batEl && batText) batEl.innerHTML = `<span style="color:#f8e71c;">BAT:</span> ${batText}`;
      const bowlEl = card.querySelector('.js-sc-bowler');
      if (bowlEl && bowlText) bowlEl.innerHTML = `<span style="color:#00d2ff;">BOWL:</span> ${bowlText}`;
    },

    filterSection: function (sec, pill) {
      document.querySelectorAll('.fm-filter-pill').forEach(p => p.classList.remove('active'));
      if (pill) pill.classList.add('active');
      document.querySelectorAll('.js-fm-section').forEach(el => {
        if (sec === 'all') {
          el.classList.remove('d-none');
        } else {
          el.classList.toggle('d-none', el.id !== 'fmSection_' + sec);
        }
      });
    },

    toggleSbCard: function (bodyId) {
      const el = document.getElementById(bodyId);
      if (el) el.classList.toggle('d-none');
    },

    toggleSbMinMax: function (popId) {
      const pop = document.getElementById(popId);
      if (pop) pop.classList.toggle('d-none');
    },

    openSbTableSlip: function (containerId, marketName, selectionId, selectionName, odds, marketKey) {
      document.querySelectorAll('.sportbook-market-tbl td.back-1.select').forEach(td => td.classList.remove('select'));
      document.querySelectorAll('tr.betslipback').forEach(tr => tr.classList.add('d-none'));

      const row = document.querySelector(`.js-sb-runner-row[data-selection-id="${selectionId}"]`);
      if (row) {
        const backTd = row.querySelector('td.back-1');
        if (backTd) backTd.classList.add('select');
      }

      const trSlip = document.getElementById(containerId + '_tr');
      if (trSlip) trSlip.classList.remove('d-none');

      this.openInlineSlip(containerId, marketName, selectionId, selectionName, 'BACK', odds, 'sportsbook', null, marketKey);
      const box = document.getElementById(containerId);
      if (box) {
        box.style.background = '#d3edd0';
      }
    },

    openInlineSlip: function (containerId, marketName, selectionId, selectionName, betType, odds, category, runLine, marketKey, triggerEl) {
      document.querySelectorAll('.fm-inline-slip').forEach(el => {
        el.classList.add('d-none');
        el.innerHTML = '';
      });
      document.querySelectorAll('.fm-odd-cell.selected-odd-back, .fm-odd-cell.selected-odd-lay').forEach(el => {
        el.classList.remove('selected-odd-back', 'selected-odd-lay');
      });

      const isLay = (betType === 'LAY' || betType === 'NO');
      if (triggerEl && triggerEl.classList && triggerEl.classList.contains('fm-odd-cell')) {
        triggerEl.classList.add(isLay ? 'selected-odd-lay' : 'selected-odd-back');
      }

      const box = document.getElementById(containerId);
      this.activeSlip = {
        containerId,
        marketKey: marketKey || null,
        marketName,
        selectionId: String(selectionId),
        selectionName,
        betType,
        odds: parseFloat(odds) || 1.90,
        category: category || 'exchange',
        runLine: runLine ?? null
      };

      // Also sync with Right Sidebar Bet Slip
      if (window.WB24 && typeof WB24.openBetSlip === 'function') {
        WB24.openBetSlip({
          eventId: this.eventId,
          eventName: this.eventName,
          marketName,
          selectionId: String(selectionId),
          selectionName,
          betType,
          odds: parseFloat(odds) || 1.90,
          category: category || 'exchange',
          runLine,
          skipMobileModal: true
        });
        if (window.WB24.currentSlip) {
          window.WB24.currentSlip.stake = this.stake;
          const sInp = document.getElementById('slipStakeInput');
          if (sInp) sInp.value = this.stake;
          WB24.updateSlipCalculations();
        }
      }

      if (box) {
        box.className = 'fm-inline-slip ' + (isLay ? 'lay-mode' : '');
        const stakes = [100, 200, 500, 5000, 10000, 25000, 50000, 100000];
        const oddStep = (category === 'bookmaker' || category === 'fancy' || this.activeSlip.odds >= 10) ? 1 : 0.01;
        box.innerHTML = `
          <div class="row g-2 align-items-center mb-2">
            <div class="col-3 d-none d-md-block">
              <button type="button" class="fm-slip-btn-cancel" onclick="FullMkt.closeInlineSlip()">Cancel</button>
            </div>
            <div class="col-6 col-md-3">
              <div class="fm-slip-stepper">
                <button class="fm-step-btn" type="button" onclick="FullMkt.stepOdds(-${oddStep})">&minus;</button>
                <input type="number" step="any" id="fmInlineOdds" class="fm-step-input" value="${this.activeSlip.odds}" oninput="FullMkt.onOddsInput(this.value)">
                <button class="fm-step-btn" type="button" onclick="FullMkt.stepOdds(${oddStep})">+</button>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="fm-slip-stepper">
                <button class="fm-step-btn" type="button" onclick="FullMkt.stepStake(-100)">&minus;</button>
                <input type="number" step="any" id="fmInlineStake" class="fm-step-input" value="${this.stake}" oninput="FullMkt.onStakeInput(this.value)">
                <button class="fm-step-btn" type="button" onclick="FullMkt.stepStake(100)">+</button>
              </div>
            </div>
            <div class="col-3 d-none d-md-block">
              <button type="button" class="fm-slip-btn-place" onclick="FullMkt.submitInlineBet()">Place Bet</button>
            </div>
          </div>
          <div class="row g-1">
            ${stakes.map(s => `<div class="col-3 col-md"><a href="javascript:void(0)" class="fm-stake-chip ${Number(this.stake) === s ? 'active-stake' : ''}" data-stake-val="${s}" onclick="FullMkt.setStake(${s})">${s}</a></div>`).join('')}
          </div>
          <div class="row g-2 mt-1 d-md-none">
            <div class="col-6"><button type="button" class="fm-slip-btn-cancel" onclick="FullMkt.closeInlineSlip()">Cancel</button></div>
            <div class="col-6"><button type="button" class="fm-slip-btn-place" onclick="FullMkt.submitInlineBet()">Place Bet</button></div>
          </div>
        `;
      }

      this.recalcAllPositions();
    },

    closeInlineSlip: function () {
      this.activeSlip = null;
      document.querySelectorAll('.fm-inline-slip').forEach(el => {
        el.classList.add('d-none');
        el.innerHTML = '';
      });
      document.querySelectorAll('.fm-odd-cell.selected-odd-back, .fm-odd-cell.selected-odd-lay').forEach(el => {
        el.classList.remove('selected-odd-back', 'selected-odd-lay');
      });
      document.querySelectorAll('tr.betslipback').forEach(tr => tr.classList.add('d-none'));
      document.querySelectorAll('.sportbook-market-tbl td.back-1.select').forEach(td => td.classList.remove('select'));
      if (window.WB24 && typeof WB24.closeBetSlip === 'function') {
        WB24.closeBetSlip();
      }
      this.recalcAllPositions();
    },

    onStakeInput: function (val) {
      this.stake = Math.max(0, parseFloat(val) || 0);
      document.querySelectorAll('.fm-stake-chip').forEach(ch => {
        ch.classList.toggle('active-stake', Number(ch.dataset.stakeVal) === Number(this.stake));
      });
      if (window.WB24 && typeof WB24.setStake === 'function') {
        WB24.setStake(this.stake);
      }
      this.recalcAllPositions();
    },

    onOddsInput: function (val) {
      if (!this.activeSlip) return;
      const parsed = parseFloat(val);
      if (!isNaN(parsed) && parsed > 0) {
        this.activeSlip.odds = parsed;
        const rOdds = document.getElementById('slipOddsInput');
        if (rOdds) {
          rOdds.value = parsed;
          if (window.WB24 && typeof WB24.updateSlipCalculations === 'function') {
            WB24.updateSlipCalculations();
          }
        }
      }
      this.recalcAllPositions();
    },

    setStake: function (v) {
      this.stake = parseFloat(v) || 100;
      const inp = document.getElementById('fmInlineStake');
      if (inp) inp.value = this.stake;
      document.querySelectorAll('.fm-stake-chip').forEach(ch => {
        ch.classList.toggle('active-stake', Number(ch.dataset.stakeVal) === Number(this.stake));
      });
      if (window.WB24 && typeof WB24.setStake === 'function') WB24.setStake(this.stake);
      this.recalcAllPositions();
    },

    stepStake: function (d) {
      this.setStake(Math.max(10, (parseFloat(this.stake) || 100) + d));
    },

    stepOdds: function (d) {
      const inp = document.getElementById('fmInlineOdds');
      if (!inp || !this.activeSlip) return;
      const nextVal = Math.max(1.01, Math.round((this.activeSlip.odds + d) * 100) / 100);
      this.activeSlip.odds = nextVal;
      inp.value = nextVal;
      const rOdds = document.getElementById('slipOddsInput');
      if (rOdds) {
        rOdds.value = nextVal;
        if (window.WB24 && typeof WB24.updateSlipCalculations === 'function') {
          WB24.updateSlipCalculations();
        }
      }
      this.recalcAllPositions();
    },

    submitInlineBet: async function () {
      if (window.WB24 && typeof WB24.submitBet === 'function') {
        const sInp = document.getElementById('fmInlineStake');
        const oInp = document.getElementById('fmInlineOdds');
        const rOdds = document.getElementById('slipOddsInput');
        const rStake = document.getElementById('slipStakeInput');
        if (oInp && rOdds) rOdds.value = oInp.value;
        if (sInp && rStake) rStake.value = sInp.value;
        await WB24.submitBet(false);
        this.closeInlineSlip();
      }
    }
  };

  function switchFancyOrSb(type) {
    const isFancy = (type === 'Fancy' || type === 'fancy');
    document.getElementById('subNav_Fancy')?.classList.toggle('d-none', !isFancy);
    document.getElementById('subNav_SPORTSBOOK')?.classList.toggle('d-none', isFancy);
    document.getElementById('marketPane_fancy')?.classList.toggle('d-none', !isFancy);
    document.getElementById('marketPane_sportsbook')?.classList.toggle('d-none', isFancy);
  }

  function filterFancyCat(cat, anchor) {
    document.querySelectorAll('.js-fancy-cat-li').forEach(li => li.classList.remove('select'));
    if (anchor && anchor.parentElement) anchor.parentElement.classList.add('select');
    document.querySelectorAll('.js-fancy-row').forEach(r => {
      if (cat === 'ALL' || r.dataset.fcat === cat) {
        r.classList.remove('d-none');
      } else {
        r.classList.add('d-none');
      }
    });
  }

  function filterSportsbookCat(cat, anchor) {
    document.querySelectorAll('.js-sb-cat-li').forEach(li => li.classList.remove('select'));
    if (anchor && anchor.parentElement) anchor.parentElement.classList.add('select');
    document.querySelectorAll('.js-sb-market-card').forEach(c => {
      const mtype = (c.dataset.sbMtype || 'ALL').toUpperCase();
      if (cat === 'ALL' || mtype === cat.toUpperCase() || mtype === 'ALL') {
        c.classList.remove('d-none');
      } else {
        c.classList.add('d-none');
      }
    });
  }

  function showFancyBook(title, noRun, yesRun) {
    document.getElementById('fancyBookTitle').textContent = title;
    const base = Math.max(1, noRun || yesRun || 45);
    let html = '';
    for (let r = Math.max(0, base - 3); r <= base + 3; r++) {
      const pl = r >= (yesRun || base) ? '+100.00' : '-100.00';
      const cls = r >= (yesRun || base) ? 'text-success fw-bold' : 'text-danger fw-bold';
      html += `<tr><td>${r}</td><td class="${cls}">${pl}</td></tr>`;
    }
    document.getElementById('fancyBookBody').innerHTML = html;
    new bootstrap.Modal(document.getElementById('fancyBookModal')).show();
  }

  document.addEventListener('DOMContentLoaded', () => FullMkt.init());
</script>
@endpush
