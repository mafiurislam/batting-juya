<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Master Admin Dashboard - CRICEXPERTS.COM')</title>
  <meta name="theme-color" content="#081225">
  <link rel="icon" type="image/x-icon" href="/favicon.ico">
  <link rel="icon" type="image/png" sizes="192x192" href="/assets/brand/app-icon-192.png">
  <link rel="apple-touch-icon" sizes="180x180" href="/assets/brand/apple-touch-icon.png">
  <link rel="manifest" href="/manifest.json">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
  <style>
    :root {
      --adm-bg: #f1f5f9;
      --adm-sidebar-bg: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
      --adm-topbar-bg: linear-gradient(90deg, #0f172a 0%, #1e3a8a 50%, #172554 100%);
    }
    body {
      background: var(--adm-bg);
      font-family: 'Segoe UI', Tahoma, Helvetica, Arial, sans-serif;
      font-size: 13px;
      color: #1e293b;
      margin: 0;
      overflow-x: hidden;
    }

    /* Top Header Bar */
    .adm-topbar {
      background: var(--adm-topbar-bg);
      color: #ffffff;
      height: 64px;
      padding: 0 22px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: sticky;
      top: 0;
      z-index: 1040;
      box-shadow: 0 4px 16px rgba(15, 23, 42, 0.25);
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    /* Left Sidebar Navigation */
    .adm-sidebar {
      width: 260px;
      min-height: calc(100vh - 64px);
      background: var(--adm-sidebar-bg);
      color: #e2e8f0;
      flex-shrink: 0;
      padding-bottom: 30px;
      box-shadow: 4px 0 18px rgba(15, 23, 42, 0.12);
    }
    .adm-nav-section {
      font-size: 10.5px;
      font-weight: 800;
      letter-spacing: 0.9px;
      text-transform: uppercase;
      color: #94a3b8;
      padding: 16px 18px 6px;
    }
    .adm-sidebar a.adm-nav-link {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 10px 16px;
      margin: 2px 10px;
      border-radius: 8px;
      color: #cbd5e1;
      text-decoration: none;
      font-weight: 600;
      font-size: 13px;
      transition: all 0.18s ease;
    }
    .adm-sidebar a.adm-nav-link .nav-left {
      display: flex;
      align-items: center;
      gap: 11px;
    }
    .adm-sidebar a.adm-nav-link .nav-icon {
      width: 28px;
      height: 28px;
      border-radius: 7px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 13px;
      background: rgba(255, 255, 255, 0.08);
      color: #f8fafc;
      transition: all 0.18s ease;
    }
    .adm-sidebar a.adm-nav-link:hover {
      background: rgba(255, 255, 255, 0.09);
      color: #ffffff;
      transform: translateX(3px);
    }
    .adm-sidebar a.adm-nav-link.active {
      background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
      color: #ffffff;
      box-shadow: 0 4px 12px rgba(37, 99, 235, 0.38);
    }
    .adm-sidebar a.adm-nav-link.active .nav-icon {
      background: rgba(255, 255, 255, 0.22);
      color: #ffffff;
    }

    /* Colorful Gradient KPI Cards */
    .adm-stat-card {
      border-radius: 12px;
      padding: 16px 18px;
      color: #ffffff;
      position: relative;
      overflow: hidden;
      box-shadow: 0 6px 18px rgba(15, 23, 42, 0.12);
      transition: transform 0.18s ease, box-shadow 0.18s ease;
      height: 100%;
    }
    .adm-stat-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 24px rgba(15, 23, 42, 0.18);
    }
    .adm-stat-card .stat-icon {
      position: absolute;
      right: 14px;
      top: 14px;
      width: 44px;
      height: 44px;
      border-radius: 10px;
      background: rgba(255, 255, 255, 0.2);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
    }
    .adm-stat-card .stat-label {
      font-size: 11.5px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.4px;
      opacity: 0.9;
    }
    .adm-stat-card .stat-value {
      font-size: 22px;
      font-weight: 800;
      margin: 4px 0 2px;
    }
    .adm-stat-card .stat-sub {
      font-size: 11.5px;
      opacity: 0.92;
      font-weight: 600;
    }

    .adm-grad-blue   { background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); }
    .adm-grad-green  { background: linear-gradient(135deg, #10b981 0%, #047857 100%); }
    .adm-grad-purple { background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); }
    .adm-grad-orange { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
    .adm-grad-red    { background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%); }
    .adm-grad-teal   { background: linear-gradient(135deg, #06b6d4 0%, #0e7490 100%); }
    .adm-grad-pink   { background: linear-gradient(135deg, #ec4899 0%, #be185d 100%); }
    .adm-grad-indigo { background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%); }

    /* Elevated Content Cards */
    .adm-card {
      background: #ffffff;
      border-radius: 12px;
      border: 1px solid #e2e8f0;
      box-shadow: 0 2px 10px rgba(15, 23, 42, 0.05);
      overflow: hidden;
    }
    .adm-card-header {
      padding: 13px 18px;
      background: #f8fafc;
      border-bottom: 1px solid #e2e8f0;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 8px;
    }
    .adm-card-body {
      padding: 16px 18px;
    }

    .adm-kpi-card {
      background: #fff;
      border-radius: 10px;
      border-left: 4px solid #2563eb;
      padding: 14px 16px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }

    .table thead th {
      background: #f1f5f9;
      color: #334155;
      font-weight: 700;
      font-size: 11.5px;
      text-transform: uppercase;
      letter-spacing: 0.3px;
      border-bottom-width: 1px;
    }

    .adm-backdrop {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(15, 23, 42, 0.6);
      z-index: 1045;
    }
    .adm-backdrop.show {
      display: block;
    }

    @media (max-width: 991px) {
      .adm-topbar {
        height: 56px;
        padding: 0 10px;
      }
      .adm-sidebar {
        position: fixed;
        top: 56px;
        left: -275px;
        width: 265px;
        height: calc(100vh - 56px);
        overflow-y: auto;
        z-index: 1050;
        transition: left 0.25s ease;
      }
      .adm-sidebar.mobile-open {
        left: 0;
      }
      .adm-card-body {
        padding: 12px;
      }
    }
  </style>
  @stack('styles')
</head>
<body>
  <!-- ================= TOP ADMIN HEADER ================= -->
  <header class="adm-topbar">
    <div class="d-flex align-items-center gap-2 gap-md-3">
      <button type="button" class="btn btn-sm btn-outline-light d-lg-none" onclick="toggleAdminMobileMenu()">
        <i class="fas fa-bars"></i>
      </button>
      <a href="{{ route('admin.dashboard') }}" class="d-flex align-items-center text-decoration-none">
        <img src="/assets/brand/logo.png?v=2" alt="cricexperts.com" style="height:38px;">
      </a>
      <span class="badge rounded-pill px-2 px-md-3 py-1 py-md-2 fw-bold d-none d-sm-inline-flex align-items-center" style="background:linear-gradient(135deg,#f59e0b,#ea580c);color:#fff;font-size:11px;">
        <i class="fas fa-crown me-1"></i> SUPER ADMIN CONTROL CENTER
      </span>
    </div>

    <div class="d-flex align-items-center gap-2">
      <a href="{{ route('admin.live.scores') }}" class="btn btn-sm btn-danger fw-bold d-none d-md-inline-flex align-items-center gap-1">
        <i class="fas fa-satellite-dish"></i> Live Scores &amp; BallByBall
      </a>
      <a href="{{ route('admin.users') }}" class="btn btn-sm btn-outline-light fw-bold d-none d-md-inline-flex align-items-center gap-1">
        <i class="fas fa-user-cog"></i> My Account Hub
      </a>
      <a href="{{ route('admin.banking') }}" class="btn btn-sm btn-warning text-dark fw-bold d-none d-md-inline-flex align-items-center gap-1">
        <i class="fas fa-university"></i> Banking
      </a>
      <a href="{{ route('home') }}" class="btn btn-sm btn-success fw-bold d-inline-flex align-items-center gap-1">
        <i class="fas fa-external-link-alt"></i> Live Website
      </a>
      <div class="px-3 py-1 rounded-pill d-none d-sm-flex align-items-center gap-2" style="background:rgba(255,255,255,0.12);">
        <i class="fas fa-user-shield text-warning"></i>
        <span class="fw-bold small">{{ auth()->user()->username }}</span>
      </div>
      <form action="{{ route('logout') }}" method="POST" class="m-0">
        @csrf
        <button type="submit" class="btn btn-sm btn-danger fw-bold">
          <i class="fas fa-sign-out-alt me-1"></i> Logout
        </button>
      </form>
    </div>
  </header>

  <div id="admMobileBackdrop" class="adm-backdrop" onclick="toggleAdminMobileMenu()"></div>
  <div class="d-flex">
    <!-- ================= LEFT SIDEBAR MENU ================= -->
    <aside id="admSidebar" class="adm-sidebar">
      <div class="adm-nav-section">Main Control</div>
      <a href="{{ route('admin.dashboard') }}" class="adm-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <span class="nav-left">
          <span class="nav-icon" style="color:#60a5fa;"><i class="fas fa-tachometer-alt"></i></span>
          <span>Dashboard Overview</span>
        </span>
        <span class="badge bg-primary rounded-pill">Live</span>
      </a>

      <div class="adm-nav-section">My Account Systems (A-to-Z)</div>
      <a href="{{ route('admin.users') }}" class="adm-nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
        <span class="nav-left">
          <span class="nav-icon" style="color:#34d399;"><i class="fas fa-user-circle"></i></span>
          <span>My Profile &amp; Users</span>
        </span>
        <span class="badge bg-success rounded-pill">CRUD</span>
      </a>

      <a href="{{ route('admin.banking') }}" class="adm-nav-link {{ request()->routeIs('admin.banking*') ? 'active' : '' }}">
        <span class="nav-left">
          <span class="nav-icon" style="color:#fbbf24;"><i class="fas fa-wallet"></i></span>
          <span>Deposits &amp; Withdrawals</span>
        </span>
      </a>

      <a href="{{ route('admin.bank.accounts') }}" class="adm-nav-link {{ request()->routeIs('admin.bank.accounts*') ? 'active' : '' }}">
        <span class="nav-left">
          <span class="nav-icon" style="color:#38bdf8;"><i class="fas fa-university"></i></span>
          <span>Withdraw Details (Banks)</span>
        </span>
      </a>

      <a href="{{ route('admin.statements') }}" class="adm-nav-link {{ request()->routeIs('admin.statements*') ? 'active' : '' }}">
        <span class="nav-left">
          <span class="nav-icon" style="color:#a78bfa;"><i class="fas fa-file-invoice-dollar"></i></span>
          <span>Account Statements</span>
        </span>
      </a>

      <a href="{{ route('admin.bets') }}" class="adm-nav-link {{ request()->routeIs('admin.bets*') ? 'active' : '' }}">
        <span class="nav-left">
          <span class="nav-icon" style="color:#f472b6;"><i class="fas fa-receipt"></i></span>
          <span>Bets History &amp; P/L</span>
        </span>
      </a>

      <a href="{{ route('admin.commissions') }}" class="adm-nav-link {{ request()->routeIs('admin.commissions*') ? 'active' : '' }}">
        <span class="nav-left">
          <span class="nav-icon" style="color:#fb923c;"><i class="fas fa-percentage"></i></span>
          <span>Commission &amp; Referrals</span>
        </span>
      </a>

      <a href="{{ route('admin.security') }}" class="adm-nav-link {{ request()->routeIs('admin.security*') ? 'active' : '' }}">
        <span class="nav-left">
          <span class="nav-icon" style="color:#4ade80;"><i class="fas fa-shield-alt"></i></span>
          <span>2FA &amp; Password History</span>
        </span>
      </a>

      <a href="{{ route('admin.activity.logs') }}" class="adm-nav-link {{ request()->routeIs('admin.activity.logs*') ? 'active' : '' }}">
        <span class="nav-left">
          <span class="nav-icon" style="color:#f87171;"><i class="fas fa-history"></i></span>
          <span>Activity &amp; OTP Logs</span>
        </span>
      </a>

      <div class="adm-nav-section">Exchange &amp; Website Control</div>
      <a href="{{ route('admin.live.scores') }}" class="adm-nav-link {{ request()->routeIs('admin.live.scores*') ? 'active' : '' }}">
        <span class="nav-left">
          <span class="nav-icon" style="color:#f43f5e;"><i class="fas fa-satellite-dish"></i></span>
          <span>Live Scores &amp; BallByBall</span>
        </span>
        <span class="badge bg-danger rounded-pill">LIVE</span>
      </a>

      <a href="{{ route('admin.events') }}" class="adm-nav-link {{ request()->routeIs('admin.events*') ? 'active' : '' }}">
        <span class="nav-left">
          <span class="nav-icon" style="color:#facc15;"><i class="fas fa-baseball-ball"></i></span>
          <span>Sports &amp; Match Odds</span>
        </span>
      </a>

      <a href="{{ route('admin.casino') }}" class="adm-nav-link {{ request()->routeIs('admin.casino*') ? 'active' : '' }}">
        <span class="nav-left">
          <span class="nav-icon" style="color:#c084fc;"><i class="fas fa-dice"></i></span>
          <span>Casino Games (217+)</span>
        </span>
      </a>

      <a href="{{ route('admin.settings') }}" class="adm-nav-link {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
        <span class="nav-left">
          <span class="nav-icon" style="color:#2dd4bf;"><i class="fas fa-sliders-h"></i></span>
          <span>Website &amp; Payment Setup</span>
        </span>
      </a>
    </aside>

    <!-- ================= MAIN ADMIN WORKSPACE ================= -->
    <main class="flex-grow-1 p-3 p-lg-4" style="min-width:0;">
      @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 px-3 fw-bold shadow-sm border-0 mb-3" style="background:#d1fae5;color:#065f46;">
          <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
          <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
        </div>
      @endif
      @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show py-2 px-3 fw-bold shadow-sm border-0 mb-3" style="background:#fee2e2;color:#991b1b;">
          <i class="fas fa-exclamation-triangle me-1"></i> {{ $errors->first() }}
          <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
        </div>
      @endif

      @yield('content')
    </main>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function toggleAdminMobileMenu() {
      document.getElementById('admSidebar')?.classList.toggle('mobile-open');
      document.getElementById('admMobileBackdrop')?.classList.toggle('show');
    }
  </script>
  @stack('scripts')
</body>
</html>
