<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'winbook24')</title>
  <link rel="icon" type="image/x-icon" href="/favicon.ico">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
  <link href="/css/winbook24.css" rel="stylesheet">
  @stack('styles')
</head>
<body>
@php
  $activeWhatsapp = $supportWhatsapp ?? \App\Models\SiteSetting::getValue('support_whatsapp', '+919831391470');
@endphp

  <!-- ================= TOP HEADER ================= -->
  <header class="wb-top-header">
    <div class="d-flex align-items-center gap-2" style="height:62px;max-height:62px;overflow:hidden;">
      @auth
        <a href="{{ route('bet.history') }}" class="d-lg-none btn btn-sm text-white border border-dark px-2 py-1" style="background:var(--header-bg);font-size:11px;font-weight:700;">
          <i class="fas fa-coins me-1"></i> Bets
        </a>
      @endauth
      <a href="{{ route('home') }}" class="wb-logo">
        <img src="/assets/brand/logo.png?v=2" alt="cricexperts.com">
      </a>
      @if(View::hasSection('full_bleed'))
        <div class="d-none d-lg-flex align-items-end gap-1 ms-3" style="height:62px;padding-bottom:6px;">
          <a href="{{ route('livecasino') }}" class="px-3 py-1 fw-bolder text-dark text-decoration-none" style="background:var(--sportsbar-bg);border-radius:4px 4px 0 0;font-size:13px;line-height:1.2;">LOBBY</a>
          <a href="{{ route('home') }}" class="px-3 py-1 fw-bolder text-white text-decoration-none" style="background:var(--exchange-bg);border-radius:20px;font-size:13px;line-height:1.2;">EXCHANGE</a>
          <a href="{{ route('livecasino') }}" class="px-3 py-1 fw-bolder text-white text-decoration-none" style="background:var(--exchange-bg);border-radius:20px;font-size:13px;line-height:1.2;">LIVE CASINO</a>
          <a href="{{ route('tips.previews') }}" class="px-3 py-1 fw-bolder text-white text-decoration-none" style="background:var(--exchange-bg);border-radius:20px;font-size:13px;line-height:1.2;">TIPS &amp; PREVIEWS</a>
        </div>
      @endif
    </div>

    <div class="wb-header-right login-signup-uname-pass" id="wbHeaderLoginForm">
      <!-- Live Event & Casino Search (1:1 with winbook24.com app-desktop-header) -->
      <ul class="desktopviewButton m-0 p-0 d-flex align-items-center" style="list-style:none;position:relative;">
        <li style="position:relative;">
          <div id="wbSearchBox" class="searchIconDiv me-2 ng-input slide-in">
            <i aria-hidden="true" class="fa fa-search searchIcon"></i>
            <input type="text" placeholder="" aria-label="Search" id="myInput" class="form-control searchbar" autocomplete="off" data-lpignore="true" readonly onfocus="this.removeAttribute('readonly')" onmousedown="this.removeAttribute('readonly')">
            <span aria-hidden="true" id="myClose" class="searchIconCross" onclick="WB24.closeSearch()">×</span>
            <div id="wbSearchDropdown" class="searchBox">
              <div class="scrollSearchBox">
                <ul id="wbSearchGameUl" class="search-game-ul" style="margin-bottom:0px;"></ul>
              </div>
            </div>
          </div>
        </li>
        <li class="search float-left me-2">
          <a onclick="WB24.toggleSearch()" style="cursor:pointer;" title="Search Events">
            <i id="searchiconDesktopID" class="fas fa-search-plus wb-search-trigger"></i>
          </a>
        </li>

        @guest
        <li class="float-left account d-none d-lg-block">
          <!-- Hidden decoy inputs to block browser password manager autofill -->
          <input type="text" name="prevent_autofill_user" tabindex="-1" autocomplete="username" style="position:absolute;opacity:0;height:0;width:0;pointer-events:none;">
          <input type="password" name="prevent_autofill_pass" tabindex="-1" autocomplete="current-password" style="position:absolute;opacity:0;height:0;width:0;pointer-events:none;">
          <div class="d-flex align-items-center">
            <div class="form-group mb-0">
              <input type="text" id="wbHeaderUsername" name="usernameForLogin" value="" class="form-control uname wb-header-input me-2" placeholder="Username" maxlength="30" autocomplete="off" autocapitalize="none" spellcheck="false" data-lpignore="true" data-form-type="other" readonly onfocus="this.removeAttribute('readonly')" onmousedown="this.removeAttribute('readonly')">
            </div>
            <div class="form-group mb-0 margin-setting">
              <input type="password" id="wbHeaderPassword" name="passForLogin" value="" class="form-control pass wb-header-input me-2" placeholder="Password" autocomplete="new-password" data-lpignore="true" data-form-type="other" readonly onfocus="this.removeAttribute('readonly')" onmousedown="this.removeAttribute('readonly')">
            </div>
          </div>
          <span id="wbHeaderLoginError" class="wb-header-err d-none"></span>
        </li>
        @endguest
      </ul>

      @guest
        <!-- Desktop Login + Signup Buttons (1:1 with winbook24.com) -->
        <div class="login-signup d-none d-lg-flex align-items-center">
          <button type="button" class="btn login wb-btn-login-head me-1" onclick="WB24.handleHeaderLoginClick()">
            <b style="width:33px;height:18px;">Login</b> <i class="fa fa-sign-in-alt ms-1"></i>
          </button>
          <button type="button" class="btn login wb-btn-signup-head" onclick="WB24.handleHeaderSignupClick()">
            <b style="width:33px;height:18px;">Signup</b>
          </button>
        </div>

        <!-- Mobile Login / Sign up Buttons -->
        <div class="d-flex d-lg-none align-items-center gap-1">
          <a href="{{ route('login') }}" class="wb-btn-login-head px-2" onclick="return WB24.handleMobileAuthClick(event, '{{ route('login') }}')">
            <b>Login</b> <i class="fas fa-sign-in-alt ms-1"></i>
          </a>
          <a href="{{ route('signup') }}" class="wb-btn-signup-head px-2" onclick="return WB24.handleMobileAuthClick(event, '{{ route('signup') }}')">
            <b>Sign up</b>
          </a>
        </div>
      @else
        <!-- Logged In Balance, Exposure, Refresh & My Account Dropdown (Screenshots 15, 16) -->
        <div class="wb-user-balance-box">
          <div class="pti-line">
            Main <strong>PTI <span class="js-user-balance">{{ number_format(auth()->user()->available_balance, 2) }}</span></strong>
          </div>
          <div class="exp-line">
            Exposure
            <span class="exp-val" data-bs-toggle="modal" data-bs-target="#exposureModal">
              (<span class="js-user-exposure">{{ number_format(auth()->user()->exposure, 2) }}</span>)
            </span>
          </div>
        </div>

        <button type="button" class="wb-refresh-btn" onclick="window.location.reload()" title="Refresh Balance">
          <i class="fas fa-undo-alt"></i>
        </button>

        <button type="button" class="btn btn-sm wb-header-deposit-btn me-1" data-bs-toggle="modal" data-bs-target="#DepositModal" title="Deposit Money">
          <i class="fas fa-wallet me-1"></i> Deposit
        </button>

        <a href="{{ route('banking') }}" class="wb-refresh-btn d-lg-none" title="Banking">
          <i class="fas fa-university"></i>
        </a>

        @if(auth()->user()->is_admin)
          <a href="{{ route('admin.dashboard') }}" class="btn btn-sm fw-bold me-1 d-none d-md-inline-flex align-items-center gap-1" style="background:linear-gradient(135deg,#f59e0b,#ea580c);color:#fff;font-size:11px;padding:4px 10px;border-radius:4px;box-shadow:0 2px 6px rgba(0,0,0,0.3);">
            <i class="fas fa-crown"></i> Admin Panel
          </a>
        @endif

        <div class="dropdown wb-myaccount-dropdown">
          <button class="wb-myaccount-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-user"></i>
            <span>My Account</span>
            <i class="fas fa-sort-down" style="position:relative;top:-2px;"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-end wb-myaccount-menu">
            <div class="acct-head">
              <strong>{{ auth()->user()->username }}</strong>
            </div>
            @if(auth()->user()->is_admin)
              <a class="dropdown-item fw-bold text-danger" href="{{ route('admin.dashboard') }}">
                <i class="fas fa-shield-alt me-1"></i> Admin Panel
              </a>
            @endif
            <a class="dropdown-item {{ request()->routeIs('myprofile') ? 'active-item' : '' }}" href="{{ route('myprofile') }}">My Profile</a>
            <a class="dropdown-item {{ request()->routeIs('two.factor') ? 'active-item' : '' }}" href="{{ route('two.factor') }}">Two Factor Authentication</a>
            <a class="dropdown-item {{ request()->routeIs('refer.earn') ? 'active-item' : '' }}" href="{{ route('refer.earn') }}">Refer &amp; Earn</a>
            <a class="dropdown-item {{ request()->routeIs('rolling.commission') ? 'active-item' : '' }}" href="{{ route('rolling.commission') }}">Rolling Commission</a>
            <a class="dropdown-item fw-bold text-success" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#DepositModal">
              <i class="fas fa-plus-circle me-1"></i> Deposit
            </a>
            <a class="dropdown-item {{ request()->routeIs('banking*') ? 'active-item' : '' }}" href="{{ route('banking') }}">Banking</a>
            <a class="dropdown-item {{ request()->routeIs('account.statement') ? 'active-item' : '' }}" href="{{ route('account.statement') }}">Account Statement</a>
            <a class="dropdown-item {{ request()->routeIs('bet.history') ? 'active-item' : '' }}" href="{{ route('bet.history') }}">Bets History</a>
            <a class="dropdown-item {{ request()->routeIs('profit.loss') ? 'active-item' : '' }}" href="{{ route('profit.loss') }}">Profit &amp; Loss</a>
            <a class="dropdown-item {{ request()->routeIs('password.history') ? 'active-item' : '' }}" href="{{ route('password.history') }}">Password History</a>
            <a class="dropdown-item {{ request()->routeIs('activity.log') ? 'active-item' : '' }}" href="{{ route('activity.log') }}">Activity Log</a>
            <div class="drp-logout">
              <form action="{{ route('logout') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="wb-logout-btn">
                  LOGOUT <i class="fas fa-sign-out-alt ms-1"></i>
                </button>
              </form>
            </div>
          </div>
        </div>
      @endguest
    </div>
  </header>

  <!-- ================= DESKTOP GREEN NAVIGATION BAR ================= -->
  <nav class="wb-green-navbar">
    <ul class="wb-nav-list">
      <li><a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Home</a></li>
      <li><a href="{{ route('inplay') }}" class="{{ request()->routeIs('inplay') ? 'active' : '' }}">In-Play</a></li>
      <li><a href="{{ route('multimarket') }}" class="{{ request()->routeIs('multimarket') ? 'active' : '' }}">Multi Markets</a></li>
      <li><a href="{{ route('sport.highlight', '4') }}" class="{{ request()->is('allsport-highlight/4') ? 'active' : '' }}">Cricket</a></li>
      <li class="vimaan-item">
        <a href="{{ route('vimaan') }}">
          <img src="/assets/casinoslpot/vimaan.png" alt="Vimaan"> Vimaan
        </a>
      </li>
      <li><a href="{{ route('sport.highlight', '2') }}" class="{{ request()->is('allsport-highlight/2') ? 'active' : '' }}">Tennis</a></li>
      <li><a href="{{ route('sport.highlight', '1') }}" class="{{ request()->is('allsport-highlight/1') ? 'active' : '' }}">Soccer</a></li>
      <li><a href="{{ route('horse.racing') }}" class="{{ request()->routeIs('horse.racing') && request('type') !== 'greyhound' ? 'active' : '' }}">Horse Racing</a></li>
      <li><a href="{{ route('horse.racing', ['type' => 'greyhound']) }}" class="{{ request()->routeIs('horse.racing') && request('type') === 'greyhound' ? 'active' : '' }}">Greyhound Racing</a></li>
      <li><a href="{{ route('sport.highlight', '7522') }}" class="{{ request()->is('allsport-highlight/7522') ? 'active' : '' }}">Basketball</a></li>
      <li><a href="{{ route('sport.highlight', '66104') }}" class="{{ request()->is('allsport-highlight/66104') ? 'active' : '' }}">Lottery</a></li>
      @unless(View::hasSection('full_bleed'))
        <li><a href="{{ route('livecasino') }}" class="{{ request()->routeIs('livecasino') ? 'active' : '' }}">Live Casino</a></li>
        <li><a href="{{ route('newcasino') }}" class="{{ request()->routeIs('newcasino') ? 'active' : '' }}">Our Casino</a></li>
        <li><a href="{{ route('tips.previews') }}" class="{{ request()->routeIs('tips.previews') ? 'active' : '' }}">Tips &amp; Previews</a></li>
      @endunless
    </ul>
    <a href="{{ route('settings') }}" class="wb-nav-setting">
      Setting <i class="fas fa-cog ms-1"></i>
    </a>
  </nav>

  @php
    $stakeButtons = auth()->check() ? auth()->user()->getStakeButtons() : [
      ['label' => '100', 'value' => 100], ['label' => '500', 'value' => 500],
      ['label' => '1000', 'value' => 1000], ['label' => '5000', 'value' => 5000],
      ['label' => '10000', 'value' => 10000], ['label' => '25000', 'value' => 25000],
      ['label' => '50000', 'value' => 50000], ['label' => '100000', 'value' => 100000],
    ];
  @endphp

  @hasSection('full_bleed')
    @yield('full_bleed')
  @else
  <!-- ================= MOBILE SPORTS BAR (Screenshot 02) ================= -->
  <div class="wb-mobile-sports-bar py-1 px-2 gap-1">
    <button type="button" class="btn btn-sm text-white fw-bold d-inline-flex align-items-center gap-1 px-2 py-1" style="background:var(--exchange-bg);border-radius:6px;white-space:nowrap;font-size:11.5px;" onclick="WB24Sidebar.toggleMobileSidebar()">
      <i class="fas fa-bars"></i> All Leagues
    </button>
    <a href="{{ route('inplay') }}" class="btn btn-sm fw-bold px-2 py-1 {{ request()->routeIs('inplay') ? 'text-white' : 'text-dark' }}" style="{{ request()->routeIs('inplay') ? 'background:var(--header-bg);' : '' }}border-radius:6px;white-space:nowrap;font-size:11.5px;">
      <i class="fas fa-stopwatch me-1"></i> In-Play
    </a>
    <a href="{{ route('vimaan') }}" class="btn btn-sm bg-black text-warning fw-bold d-inline-flex align-items-center gap-1 px-2 py-1" style="border-radius:6px;white-space:nowrap;font-size:11.5px;">
      <img src="/assets/casinoslpot/vimaan.png" style="height:15px;" alt="Vimaan"> Vimaan
    </a>
    <a href="{{ route('sport.highlight', '4') }}" class="btn btn-sm fw-bold px-2 py-1 {{ request()->is('allsport-highlight/4') ? 'text-white' : 'text-dark' }}" style="{{ request()->is('allsport-highlight/4') ? 'background:var(--header-bg);' : '' }}border-radius:6px;white-space:nowrap;font-size:11.5px;">
      <i class="fas fa-baseball-ball me-1"></i> Cricket
    </a>
    <a href="{{ route('sport.highlight', '1') }}" class="btn btn-sm fw-bold px-2 py-1 {{ request()->is('allsport-highlight/1') ? 'text-white' : 'text-dark' }}" style="{{ request()->is('allsport-highlight/1') ? 'background:var(--header-bg);' : '' }}border-radius:6px;white-space:nowrap;font-size:11.5px;">
      <i class="fas fa-futbol me-1"></i> Soccer
    </a>
    <a href="{{ route('sport.highlight', '2') }}" class="btn btn-sm fw-bold px-2 py-1 {{ request()->is('allsport-highlight/2') ? 'text-white' : 'text-dark' }}" style="{{ request()->is('allsport-highlight/2') ? 'background:var(--header-bg);' : '' }}border-radius:6px;white-space:nowrap;font-size:11.5px;">
      <i class="fas fa-table-tennis me-1"></i> Tennis
    </a>
    <a href="{{ route('horse.racing') }}" class="btn btn-sm text-dark fw-bold px-2 py-1" style="white-space:nowrap;font-size:11.5px;">
      <i class="fas fa-horse-head me-1"></i> Racing
    </a>
    <a href="{{ route('livecasino') }}" class="btn btn-sm text-dark fw-bold px-2 py-1" style="white-space:nowrap;font-size:11.5px;">
      <i class="fas fa-dice me-1"></i> Casino
    </a>
    <a href="{{ route('multimarket') }}" class="btn btn-sm text-dark fw-bold px-2 py-1" style="white-space:nowrap;font-size:11.5px;">
      <i class="fas fa-thumbtack me-1"></i> Multi Markets
    </a>
    <a href="{{ route('settings') }}" class="btn btn-sm text-dark fw-bold px-2 py-1" style="white-space:nowrap;font-size:11.5px;">
      <i class="fas fa-cog me-1"></i> Setting
    </a>
  </div>
  <div id="wbSidebarBackdrop" class="wb-sidebar-backdrop" onclick="WB24Sidebar.closeMobileSidebar()"></div>

  <!-- Flash Messages -->
  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show m-2 py-2 small fw-bold" role="alert">
      <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
      <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
    </div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show m-2 py-2 small fw-bold" role="alert">
      <i class="fas fa-exclamation-triangle me-1"></i> {{ $errors->first() }}
      <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
    </div>
  @endif

  <!-- ================= 3-COLUMN EXCHANGE BODY ================= -->
  <div class="wb-app-body">
    <!-- Left Sports Sidebar (1:1 with winbook24.com app-side-bar) -->
    @php
      $currentSidebarSport = $activeSportId ?? null;
    @endphp
    <aside id="wbLeftSidebar" class="wb-left-sidebar">
      <div class="wb-sidebar-head" onclick="WB24Sidebar.showMainSports()" style="cursor:pointer;" title="Back to All Sports">
        <a href="javascript:void(0)" class="path-back text-white text-decoration-none" onclick="event.stopPropagation(); WB24Sidebar.showMainSports();">
          <i class="fa fa-ellipsis-v" style="font-size:14px;"></i>
        </a>
        <a href="javascript:void(0)" class="text-white text-decoration-none" onclick="event.stopPropagation(); WB24Sidebar.showMainSports();">
          Sports
        </a>
        <a href="javascript:void(0)" class="d-lg-none text-white text-decoration-none ms-2" onclick="event.stopPropagation(); WB24Sidebar.closeMobileSidebar();" title="Close Menu">
          <i class="fas fa-times" style="font-size:14px;"></i>
        </a>
      </div>

      <!-- Level 1: Main 7 Sports (Cricket, Casino, Tennis, Soccer, Horse Racing, Basketball, Lottery) -->
      <ul id="wbSidebarMainSports" class="wb-sidebar-menu {{ $currentSidebarSport && isset($sidebarTree[$currentSidebarSport]) ? 'd-none' : '' }}">
        <li>
          <a href="{{ route('sport.highlight', '4') }}" onclick="return WB24Sidebar.selectSport(event, '4', '{{ route('sport.highlight', '4') }}')">
            <span>Cricket</span>
            <span class="wb-sidebar-arrow" onclick="event.preventDefault(); event.stopPropagation(); WB24Sidebar.openSportTree('4');" title="Expand Cricket Tournaments">
              <i class="fas fa-chevron-right"></i>
            </span>
          </a>
        </li>
        <li>
          <a href="{{ route('livecasino') }}">
            <span>Casino</span>
            <span class="wb-sidebar-arrow"><i class="fas fa-chevron-right"></i></span>
          </a>
        </li>
        <li>
          <a href="{{ route('sport.highlight', '2') }}" onclick="return WB24Sidebar.selectSport(event, '2', '{{ route('sport.highlight', '2') }}')">
            <span>Tennis</span>
            <span class="wb-sidebar-arrow" onclick="event.preventDefault(); event.stopPropagation(); WB24Sidebar.openSportTree('2');" title="Expand Tennis Tournaments">
              <i class="fas fa-chevron-right"></i>
            </span>
          </a>
        </li>
        <li>
          <a href="{{ route('sport.highlight', '1') }}" onclick="return WB24Sidebar.selectSport(event, '1', '{{ route('sport.highlight', '1') }}')">
            <span>Soccer</span>
            <span class="wb-sidebar-arrow" onclick="event.preventDefault(); event.stopPropagation(); WB24Sidebar.openSportTree('1');" title="Expand Soccer Tournaments">
              <i class="fas fa-chevron-right"></i>
            </span>
          </a>
        </li>
        <li>
          <a href="{{ route('horse.racing') }}">
            <span>Horse Racing</span>
            <span class="wb-sidebar-arrow"><i class="fas fa-chevron-right"></i></span>
          </a>
        </li>
        <li>
          <a href="{{ route('sport.highlight', '7522') }}" onclick="return WB24Sidebar.selectSport(event, '7522', '{{ route('sport.highlight', '7522') }}')">
            <span>Basketball</span>
            <span class="wb-sidebar-arrow" onclick="event.preventDefault(); event.stopPropagation(); WB24Sidebar.openSportTree('7522');" title="Expand Basketball Tournaments">
              <i class="fas fa-chevron-right"></i>
            </span>
          </a>
        </li>
        <li>
          <a href="{{ route('sport.highlight', '66104') }}" onclick="return WB24Sidebar.selectSport(event, '66104', '{{ route('sport.highlight', '66104') }}')">
            <span>Lottery</span>
            <span class="wb-sidebar-arrow" onclick="event.preventDefault(); event.stopPropagation(); WB24Sidebar.openSportTree('66104');" title="Expand Lottery">
              <i class="fas fa-chevron-right"></i>
            </span>
          </a>
        </li>
      </ul>

      <!-- Level 2 & 3: Sport -> Tournament Accordion -> Match Links -->
      @foreach(($sidebarTree ?? []) as $sId => $tournaments)
        <ul id="wbSidebarSportTree_{{ $sId }}" class="wb-sidebar-menu js-sidebar-sport-tree {{ (string)$currentSidebarSport === (string)$sId ? '' : 'd-none' }}">
          @foreach($tournaments as $tName => $tEvents)
            @php
              $tSlug = 't_' . $sId . '_' . md5($tName);
              $isCurrentEventInTournament = isset($event) && collect($tEvents)->contains('event_id', $event->event_id);
              $autoOpenUcl = ((string)$sId === '1' && !isset($event));
              $isOpenTournament = $isCurrentEventInTournament || $autoOpenUcl;
            @endphp
            <li class="wb-sidebar-dropdown {{ $isOpenTournament ? 'open' : '' }}">
              <a href="javascript:void(0)" class="wb-tournament-toggle align-items-start" onclick="WB24Sidebar.toggleTournament('{{ $tSlug }}', this)">
                <span class="pe-2" style="line-height:1.35;">{{ trim($tName) }}</span>
                <span class="wb-sidebar-arrow flex-shrink-0">
                  <i class="fas fa-chevron-right js-t-icon"></i>
                </span>
              </a>
              <ul id="{{ $tSlug }}" class="wb-sidebar-subitems {{ $isOpenTournament ? '' : 'd-none' }}">
                @foreach($tEvents as $tEv)
                  <li>
                    <a href="{{ $tEv['url'] }}" class="wb-sidebar-match-link {{ isset($event) && $event->event_id === $tEv['event_id'] ? 'active' : '' }}">
                      <i class="fa fa-arrow-right" style="font-size:11px;color:#223869;"></i>
                      <span>{{ $tEv['event_name'] }}</span>
                    </a>
                  </li>
                @endforeach
              </ul>
            </li>
          @endforeach
        </ul>
      @endforeach
    </aside>

    <!-- Center Main Content -->
    <main class="wb-center-content">
      @yield('content')

      <!-- Exchange Footer (Screenshot 08) -->
      <footer class="wb-footer">
        <div class="mb-2 fw-bold">
          Support &nbsp;
          <a href="https://wa.me/{{ preg_replace('/\D/', '', $activeWhatsapp) }}" target="_blank" class="text-dark fw-normal">
            <i class="fab fa-whatsapp" style="color:#28D146;font-size:16px;"></i> WhatsApp {{ $activeWhatsapp }}
          </a>
        </div>
        <div class="wb-footer-links">
          <a onclick="showFooterPolicy('Privacy Policy')">Privacy Policy</a> |
          <a onclick="showFooterPolicy('KYC Policy')">KYC</a> |
          <a onclick="showFooterPolicy('Terms and Conditions')">Terms and Conditions</a> |
          <a onclick="showFooterPolicy('Rules and Regulations')">Rules and Regulations</a> |
          <a onclick="showFooterPolicy('Responsible Gambling')">Responsible Gambling</a>
        </div>
      </footer>
    </main>

    <!-- Right Open Bets & Bet Slip Sidebar -->
    <aside class="wb-right-sidebar">
      <div class="wb-right-title">
        <span>Open Bets</span>
        @if(!request()->is('ballByBall*'))
          @auth
            <span class="badge bg-light text-dark" style="font-size:10px;">{{ isset($openBets) ? $openBets->count() : 0 }} Active</span>
          @endauth
        @endif
      </div>

      @if(!request()->is('ballByBall*'))
      <div class="p-2 border-bottom">
        <select class="form-select form-select-sm" style="font-size:12px;border-color:#c8ced3;">
          <option value="all">Select Market</option>
          <option value="match_odds">Match Odds</option>
          <option value="bookmaker">Bookmaker</option>
          <option value="fancy">Fancy Bet</option>
          <option value="sportsbook">Sportsbook</option>
        </select>
      </div>
      @endif

      <!-- Interactive Bet Slip Panel -->
      <div id="wbBetSlipPanel" class="wb-betslip-panel">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <div>
            <span id="slipBetTypeBadge" class="badge bg-primary">BACK</span>
            <strong id="slipSelectionName" class="ms-1">Runner</strong>
          </div>
          <button type="button" class="btn-close btn-sm" onclick="WB24.closeBetSlip()"></button>
        </div>
        <div id="slipMarketName" class="text-muted mb-2" style="font-size:10px;">Match • Match Odds</div>

        <div class="row g-2 align-items-center mb-2">
          <div class="col-6">
            <label class="small fw-bold d-block mb-1">Odds</label>
            <div class="input-group input-group-sm">
              <button class="btn btn-outline-secondary" type="button" onclick="WB24.stepOdds(-0.01)">-</button>
              <input type="number" step="0.01" id="slipOddsInput" class="form-control text-center fw-bold" oninput="WB24.updateSlipCalculations()">
              <button class="btn btn-outline-secondary" type="button" onclick="WB24.stepOdds(0.01)">+</button>
            </div>
          </div>
          <div class="col-6">
            <label class="small fw-bold d-block mb-1">Stake (PTI)</label>
            <input type="number" step="10" id="slipStakeInput" class="form-control form-control-sm text-center fw-bold" oninput="WB24.updateSlipCalculations()">
          </div>
        </div>

        @php
          $stakeButtons = auth()->check() ? auth()->user()->getStakeButtons() : [
            ['label' => '100', 'value' => 100], ['label' => '500', 'value' => 500],
            ['label' => '1000', 'value' => 1000], ['label' => '5000', 'value' => 5000],
            ['label' => '10000', 'value' => 10000], ['label' => '25000', 'value' => 25000],
            ['label' => '50000', 'value' => 50000], ['label' => '100000', 'value' => 100000],
          ];
        @endphp
        <div class="wb-stake-grid">
          @foreach($stakeButtons as $sb)
            <button type="button" class="wb-stake-btn" onclick="WB24.setStake({{ $sb['value'] }})">+{{ $sb['label'] }}</button>
          @endforeach
        </div>

        <div class="d-flex justify-content-between small mt-2 pt-1 border-top border-secondary-subtle">
          <span>Profit: <strong class="text-success" id="slipProfitVal">0.00</strong></span>
          <span>Liability: <strong class="text-danger" id="slipLiabilityVal">0.00</strong></span>
        </div>

        <div class="d-flex justify-content-between mt-2">
          <button type="button" class="wb-btn-cancelbet" onclick="WB24.closeBetSlip()">Cancel</button>
          <button type="button" id="btnPlaceBetSubmit" class="wb-btn-placebet" onclick="WB24.submitBet(false)">Place Bet</button>
        </div>
      </div>

      <!-- Open Bets List -->
      <div id="wbOpenBetsList" class="p-1">
        @if(isset($openBets) && $openBets->count() > 0)
          @foreach($openBets as $ob)
            <div id="openBetRow_{{ $ob->id }}" class="p-2 mb-1 border-bottom small {{ $ob->bet_type === 'LAY' ? 'bg-danger-subtle' : 'bg-info-subtle' }}">
              <div class="d-flex justify-content-between fw-bold">
                <span>{{ $ob->selection_name }} <span class="badge bg-dark">{{ $ob->bet_type }}</span></span>
                <span>@ {{ number_format($ob->odds, 2) }}</span>
              </div>
              <div class="text-muted" style="font-size:10px;">{{ $ob->event_name }} ({{ $ob->market_name }})</div>
              <div class="d-flex justify-content-between align-items-center mt-1">
                <span>Stake: <b>{{ number_format($ob->stake, 2) }}</b> | P/L: <b class="text-success">+{{ number_format($ob->potential_profit, 2) }}</b></span>
                <div class="btn-group btn-group-sm">
                  <button type="button" class="btn btn-warning btn-sm py-0 px-1" style="font-size:10px;" onclick="WB24.cashoutBet({{ $ob->id }}, 'cashout')">Cashout</button>
                  <button type="button" class="btn btn-success btn-sm py-0 px-1" style="font-size:10px;" onclick="WB24.cashoutBet({{ $ob->id }}, 'instant_settle')">Settle</button>
                </div>
              </div>
            </div>
          @endforeach
        @else
          @if(!request()->is('ballByBall*'))
          <div class="js-no-open-bets text-center text-muted py-3 small">
            Click on any Back (blue) or Lay (pink) odds box to place a bet.
          </div>
          @endif
        @endif
      </div>
    </aside>
  </div>
  @endif

  <!-- ================= FLOATING CONTACT US WIDGET ================= -->
  @unless(View::hasSection('full_bleed'))
  <a href="https://wa.me/{{ preg_replace('/\D/', '', $activeWhatsapp) }}" target="_blank" class="wb-contact-float">
    <span class="contact-label">Contact Us...</span>
    <span class="contact-circle"><i class="fas fa-comment-alt"></i></span>
  </a>
  @endunless

  <!-- ================= MOBILE BOTTOM NAVIGATION (Screenshot 02) ================= -->
  <nav class="wb-mobile-bottom-nav">
    <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">
      <i class="fas fa-home fs-5 mb-1"></i>
      <span>Home</span>
    </a>
    <a href="{{ route('inplay') }}" class="{{ request()->routeIs('inplay') ? 'active' : '' }}">
      <i class="fas fa-stopwatch fs-5 mb-1"></i>
      <span>In-Play</span>
    </a>
    <a href="{{ route('sports.mobile') }}" class="{{ request()->routeIs('sports.mobile') ? 'active' : '' }}">
      <i class="fas fa-trophy fs-5 mb-1"></i>
      <span>Sports</span>
    </a>
    <a href="{{ route('livecasino') }}" class="{{ request()->routeIs('livecasino') ? 'active' : '' }}">
      <i class="fas fa-dice fs-5 mb-1"></i>
      <span>Casino</span>
    </a>
    <a href="{{ auth()->check() ? route('myprofile') : route('login') }}" class="{{ request()->routeIs('myprofile') ? 'active' : '' }}">
      <i class="fas fa-user-circle fs-5 mb-1"></i>
      <span>Account</span>
    </a>
  </nav>

  <!-- ================= 18+ NON-GAMBLING TERRITORIES MODAL (Guest Only) ================= -->
  @guest
  <div class="modal fade wb-age-modal" id="ageconfirmationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <span class="wb-age-close" data-bs-dismiss="modal" aria-label="Close"><i class="fas fa-times"></i></span>
        <div class="text1 text-confirm-bold">Non-Gambling Territories.</div>
        <hr class="confirm-line">
        <div class="text2 mb-3">
          Connecting to our site from non gambling countries, it will be User's responsibility to ensure that their use of the service is lawful.
        </div>
        <div class="text1 text-confirm-bold">Underage gambling is prohibited.</div>
        <hr class="confirm-line">
        <div class="text2 mb-3">
          Please confirm if you are <span>18 years old and above</span> as of today.
        </div>
        <div class="d-flex justify-content-center gap-3 mt-2">
          <button type="button" class="wb-age-btn-confirm" onclick="WB24.confirmAge()">Confirm</button>
          <button type="button" class="wb-age-btn-exit" data-bs-dismiss="modal">Exit</button>
        </div>
      </div>
    </div>
  </div>
  @endguest

  <!-- ================= EXPOSURE DETAIL MODAL ================= -->
  <div class="modal fade" id="exposureModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header py-2 text-white" style="background:var(--header-bg);">
          <h6 class="modal-title fw-bold m-0">Exposure Detail</h6>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-2">
          <table class="table table-bordered table-sm mb-0 small text-center">
            <thead class="table-light">
              <tr>
                <th>Sport Name</th>
                <th>Event Name</th>
                <th>Market Name</th>
                <th>Liability</th>
              </tr>
            </thead>
            <tbody>
              @if(isset($openBets) && $openBets->count() > 0)
                @foreach($openBets as $eb)
                  <tr>
                    <td>{{ $eb->sport_name }}</td>
                    <td><a href="{{ route('full.market', ['sportId' => $eb->sport_id ?? '4', 'eventId' => $eb->event_id]) }}">{{ $eb->event_name }}</a></td>
                    <td>{{ $eb->market_name }} ({{ $eb->selection_name }})</td>
                    <td class="text-danger fw-bold">-{{ number_format($eb->liability, 2) }}</td>
                  </tr>
                @endforeach
              @else
                <tr>
                  <td colspan="4" class="text-center text-muted py-2">No real-time exposure records found</td>
                </tr>
              @endif
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- ================= MOBILE BETSLIP MODAL ================= -->
  <div class="modal fade" id="mobileBetSlipModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header py-2 text-white" style="background:var(--header-bg);">
          <h6 class="modal-title fw-bold m-0">Place Bet - <span id="mblSlipSelection">Selection</span></h6>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-2 mb-2">
            <div class="col-6">
              <label class="small fw-bold">Odds</label>
              <input type="number" step="0.01" id="mblSlipOdds" class="form-control form-control-sm fw-bold text-center">
            </div>
            <div class="col-6">
              <label class="small fw-bold">Stake (PTI)</label>
              <input type="number" step="10" id="mblSlipStake" class="form-control form-control-sm fw-bold text-center">
            </div>
          </div>
          <div class="wb-stake-grid mb-3">
            @foreach($stakeButtons as $sb)
              <button type="button" class="wb-stake-btn" onclick="WB24.setStake({{ $sb['value'] }})">+{{ $sb['label'] }}</button>
            @endforeach
          </div>
          <button type="button" class="wb-btn-placebet w-100 py-2" data-bs-dismiss="modal" onclick="WB24.submitBet(true)">
            Confirm &amp; Place Bet
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- ================= FOOTER POLICY MODAL ================= -->
  <div class="modal fade" id="footerPolicyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header py-2 text-white" style="background:var(--header-bg);">
          <h6 class="modal-title fw-bold m-0" id="footerPolicyTitle">Rules and Regulations</h6>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body small">
          <p class="fw-bold mb-2">winbook24.com Official Exchange Policy</p>
          <p class="mb-2">1. All users must be 18 years of age or older. Underage gambling is strictly prohibited.</p>
          <p class="mb-2">2. All sports exchange markets (Match Odds, Bookmaker, Fancy Bet, Sportsbook) are settled according to official governing federation results.</p>
          <p class="mb-0">3. Minimum Deposit is 500 PTI and Minimum Withdrawal is 1000 PTI with 24x7 customer support.</p>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="/js/winbook24.js"></script>
  <script>
    function showFooterPolicy(title) {
      document.getElementById('footerPolicyTitle').textContent = title;
      new bootstrap.Modal(document.getElementById('footerPolicyModal')).show();
    }

    window.WB24Sidebar = {
      toggleMobileSidebar: function () {
        var sb = document.getElementById('wbLeftSidebar');
        var bd = document.getElementById('wbSidebarBackdrop');
        if (!sb) return;
        var isOpen = sb.classList.contains('mobile-open');
        sb.classList.toggle('mobile-open', !isOpen);
        if (bd) bd.classList.toggle('show', !isOpen);
      },
      closeMobileSidebar: function () {
        document.getElementById('wbLeftSidebar')?.classList.remove('mobile-open');
        document.getElementById('wbSidebarBackdrop')?.classList.remove('show');
      },
      showMainSports: function () {
        document.querySelectorAll('.js-sidebar-sport-tree').forEach(function (el) {
          el.classList.add('d-none');
        });
        var main = document.getElementById('wbSidebarMainSports');
        if (main) main.classList.remove('d-none');
      },
      openSportTree: function (sportId) {
        var tree = document.getElementById('wbSidebarSportTree_' + sportId);
        if (!tree) return false;
        var main = document.getElementById('wbSidebarMainSports');
        if (main) main.classList.add('d-none');
        document.querySelectorAll('.js-sidebar-sport-tree').forEach(function (el) {
          el.classList.add('d-none');
        });
        tree.classList.remove('d-none');
        return true;
      },
      selectSport: function (e, sportId, url) {
        this.openSportTree(sportId);
        return true;
      },
      toggleTournament: function (slug, anchor) {
        var ul = document.getElementById(slug);
        if (!ul) return;
        var isHidden = ul.classList.contains('d-none');
        ul.classList.toggle('d-none', !isHidden);
        if (anchor) {
          var li = anchor.closest('.wb-sidebar-dropdown');
          if (li) li.classList.toggle('open', isHidden);
        }
      }
    };
  </script>
  @auth
    @include('partials.deposit-modal')
  @endauth
  @stack('scripts')
</body>
</html>

