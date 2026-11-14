@extends('layouts.admin')
@section('title', 'Master Admin Dashboard - CRICEXPERTS.COM')

@section('content')
  <!-- Welcome Banner -->
  <div class="p-3 p-md-4 mb-4 rounded-3 text-white d-flex flex-wrap justify-content-between align-items-center gap-3 shadow-sm"
       style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 55%, #3b0764 100%);">
    <div>
      <span class="badge bg-warning text-dark fw-bold mb-1">A-TO-Z MASTER CONTROL &amp; MY ACCOUNT SUITE</span>
      <h4 class="fw-bolder m-0">Welcome back, {{ auth()->user()->name ?? auth()->user()->username }}!</h4>
      <div class="small text-light opacity-75 mt-1">
        Full Create, Read, Update, Edit &amp; Delete (CRUD) control over Users, My Account Modules, Banking, Casino Games, Sports Odds &amp; Website Settings.
      </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
      <a href="{{ route('admin.live.scores') }}" class="btn btn-sm btn-danger fw-bold text-white px-3 py-2">
        <i class="fas fa-satellite-dish me-1"></i> Live Scores &amp; Ball-By-Ball
      </a>
      <a href="{{ route('admin.users') }}" class="btn btn-sm btn-warning fw-bold text-dark px-3 py-2">
        <i class="fas fa-user-plus me-1"></i> Manage Users &amp; Profiles
      </a>
      <a href="{{ route('admin.banking') }}" class="btn btn-sm btn-info fw-bold text-dark px-3 py-2">
        <i class="fas fa-wallet me-1"></i> Deposits &amp; Withdrawals
      </a>
      <a href="{{ route('admin.bets') }}" class="btn btn-sm btn-success fw-bold text-white px-3 py-2">
        <i class="fas fa-hand-holding-usd me-1"></i> Bets &amp; Cash Out
      </a>
      <a href="{{ route('admin.settings') }}" class="btn btn-sm btn-light fw-bold text-dark px-3 py-2">
        <i class="fas fa-sliders-h me-1"></i> Customize Website
      </a>
    </div>
  </div>

  <!-- Quick Payment & Support Settings Card (WhatsApp Number & Deposit QR Code Management) -->
  <div class="card border-0 shadow-sm mb-4" style="border-radius:10px;background:#ffffff;overflow:hidden;">
    <div class="card-header py-2 px-3 d-flex justify-content-between align-items-center" style="background:linear-gradient(135deg, #1e293b 0%, #334155 100%);color:#fff;">
      <h6 class="m-0 fw-bold d-flex align-items-center gap-2" style="font-size:14px;">
        <i class="fab fa-whatsapp text-success" style="font-size:16px;"></i> WhatsApp Support &amp; Deposit QR Code Management
      </h6>
      <a href="{{ route('admin.settings') }}" class="btn btn-sm btn-outline-light py-0 px-2 fw-bold" style="font-size:11px;">
        <i class="fas fa-sliders-h me-1"></i> All Settings
      </a>
    </div>
    <div class="card-body p-3">
      <div class="row g-3 align-items-start">
        
        <!-- 1. WhatsApp Number & UPI ID Update Form -->
        <div class="col-lg-7">
          <form action="{{ route('admin.dashboard.quick-settings') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row g-2 mb-3">
              <div class="col-md-6">
                <label class="small fw-bold text-dark d-flex align-items-center gap-1 mb-1">
                  <i class="fab fa-whatsapp text-success" style="font-size:15px;"></i> WhatsApp Support Number <span class="text-danger">*</span>
                </label>
                <input type="text" 
                       name="support_whatsapp" 
                       value="{{ $supportWhatsapp }}" 
                       class="form-control form-control-sm fw-bold" 
                       placeholder="+919831391470" 
                       required>
                <small class="text-muted" style="font-size:11px;">Appears on header, footer, and floating 24x7 WhatsApp button.</small>
              </div>

              <div class="col-md-6">
                <label class="small fw-bold text-dark d-flex align-items-center gap-1 mb-1">
                  <i class="fas fa-university text-primary"></i> Deposit UPI ID
                </label>
                <input type="text" 
                       name="deposit_upi_id" 
                       value="{{ $depositUpi }}" 
                       class="form-control form-control-sm fw-bold" 
                       placeholder="winbook24.pay@axis">
                <small class="text-muted" style="font-size:11px;">UPI ID shown to players under the QR Code.</small>
              </div>
            </div>

            <div class="mb-3 p-2 border rounded bg-light">
              <label class="small fw-bold text-dark d-flex align-items-center gap-1 mb-1">
                <i class="fas fa-upload text-info"></i> Upload / Exchange New QR Code Image
              </label>
              <input type="file" 
                     name="deposit_qr_image" 
                     class="form-control form-control-sm" 
                     accept="image/*">
              <small class="text-muted" style="font-size:11px;">Upload your own custom Google Pay, PhonePe, Paytm or Bank QR code (PNG, JPG, WEBP).</small>
            </div>

            <button type="submit" class="btn btn-sm btn-primary fw-bold px-3 py-1">
              <i class="fas fa-save me-1"></i> Update WhatsApp &amp; QR Code
            </button>
          </form>
        </div>

        <!-- 2. Active QR Code Preview & Remove / Delete Box -->
        <div class="col-lg-5">
          <div class="border rounded p-3 text-center bg-light h-100 d-flex flex-column justify-content-between align-items-center">
            <div>
              <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                <span class="small fw-bold text-dark">Active Deposit QR Code:</span>
                @if(!empty($depositQrImage))
                  <span class="badge bg-success">Custom Uploaded QR</span>
                @else
                  <span class="badge bg-primary">Dynamic UPI QR</span>
                @endif
              </div>

              <div class="p-2 bg-white border rounded d-inline-block shadow-sm mb-2">
                @if(!empty($depositQrImage))
                  <img src="{{ $depositQrImage }}" alt="Custom Deposit QR" style="width:130px;height:130px;object-fit:contain;display:block;">
                @else
                  <img src="https://api.qrserver.com/v1/create-qr-code/?size=130x130&margin=4&data={{ urlencode('upi://pay?pa=' . $depositUpi . '&pn=WINBOOK24&cu=INR') }}" alt="Default UPI QR" style="width:130px;height:130px;object-fit:contain;display:block;">
                @endif
              </div>
              
              <div class="small text-muted" style="font-size:11px;">
                @if(!empty($depositQrImage))
                  Players scan this custom QR code when depositing.
                @else
                  Automatically generated from UPI ID <code>{{ $depositUpi }}</code>.
                @endif
              </div>
            </div>

            @if(!empty($depositQrImage))
              <form action="{{ route('admin.settings.qr.delete') }}" method="POST" class="mt-2 m-0" onsubmit="return confirm('Remove custom QR code and revert to default dynamic UPI QR?')">
                @csrf
                <button type="submit" class="btn btn-sm btn-danger py-1 px-3 fw-bold" style="font-size:11.5px;">
                  <i class="fas fa-trash-alt me-1"></i> Delete Custom QR Code
                </button>
              </form>
            @endif
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- 8 Colorful Gradient KPI Cards -->
  <div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
      <div class="adm-stat-card adm-grad-blue">
        <div class="stat-icon"><i class="fas fa-users"></i></div>
        <div class="stat-label">Registered Users</div>
        <div class="stat-value">{{ number_format($stats['total_users']) }}</div>
        <div class="stat-sub">
          <i class="fas fa-coins me-1"></i> Wallet Sum: {{ number_format($stats['total_balance'], 2) }} PTI
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-xl-3">
      <div class="adm-stat-card adm-grad-green">
        <div class="stat-icon"><i class="fas fa-university"></i></div>
        <div class="stat-label">Approved Deposits</div>
        <div class="stat-value">{{ number_format($stats['total_deposits'], 2) }} PTI</div>
        <div class="stat-sub">
          Withdrawals Paid: {{ number_format($stats['total_withdrawals'], 2) }} PTI
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-xl-3">
      <div class="adm-stat-card adm-grad-purple">
        <div class="stat-icon"><i class="fas fa-receipt"></i></div>
        <div class="stat-label">Total Bets &amp; Exposure</div>
        <div class="stat-value">{{ number_format($stats['total_bets']) }} Bets</div>
        <div class="stat-sub">
          Open Exposure: {{ number_format($stats['total_exposure'], 2) }} PTI ({{ $stats['open_bets'] }} Open)
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-xl-3">
      <div class="adm-stat-card adm-grad-orange">
        <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
        <div class="stat-label">House Net P&amp;L</div>
        <div class="stat-value">{{ $stats['house_pl'] >= 0 ? '+' : '' }}{{ number_format($stats['house_pl'], 2) }} PTI</div>
        <div class="stat-sub">
          Pending Banking: {{ $stats['pending_deposits'] + $stats['pending_withdrawals'] }} Requests
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-xl-3">
      <div class="adm-stat-card adm-grad-teal">
        <div class="stat-icon"><i class="fas fa-baseball-ball"></i></div>
        <div class="stat-label">Sports &amp; Live Odds</div>
        <div class="stat-value">{{ $stats['total_events'] }} Matches</div>
        <div class="stat-sub">
          {{ $stats['inplay_events'] }} In-Play Live Markets Open
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-xl-3">
      <div class="adm-stat-card adm-grad-pink">
        <div class="stat-icon"><i class="fas fa-dice"></i></div>
        <div class="stat-label">Live &amp; Virtual Casino</div>
        <div class="stat-value">{{ $stats['casino_games'] }} Games</div>
        <div class="stat-sub">
          Teenpatti, Lucky 7, Dragon Tiger, Vimaan, Balloon, Spin
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-xl-3">
      <div class="adm-stat-card adm-grad-indigo">
        <div class="stat-icon"><i class="fas fa-id-card"></i></div>
        <div class="stat-label">Saved Withdraw Banks</div>
        <div class="stat-value">{{ $stats['saved_bank_accounts'] }} Accounts</div>
        <div class="stat-sub">
          {{ $stats['total_statements'] }} Account Statement Entries
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-xl-3">
      <div class="adm-stat-card adm-grad-red">
        <div class="stat-icon"><i class="fas fa-shield-alt"></i></div>
        <div class="stat-label">Security &amp; Activity Logs</div>
        <div class="stat-value">{{ $stats['two_factor_users'] }} 2FA Active</div>
        <div class="stat-sub">
          {{ $stats['total_activity_logs'] }} Recorded Login Activity Logs
        </div>
      </div>
    </div>
  </div>

  <!-- In-Built "My Account" Complete Management Hub (All 10 Frontend My Account Systems) -->
  <div class="adm-card mb-4">
    <div class="adm-card-header">
      <div>
        <h6 class="fw-bolder m-0 text-primary">
          <i class="fas fa-th-large me-1"></i> In-Built "My Account" Systems Control Center (A-to-Z CRUD)
        </h6>
        <small class="text-muted">Every single option inside the user's "My Account" menu is directly manageable here with Create, Edit, Update &amp; Delete.</small>
      </div>
      <a href="{{ route('myprofile') }}" target="_blank" class="btn btn-sm btn-outline-primary fw-bold">
        <i class="fas fa-external-link-alt me-1"></i> View Frontend My Account
      </a>
    </div>
    <div class="adm-card-body">
      <div class="row g-2">
        <div class="col-6 col-md-4 col-xl-2">
          <a href="{{ route('admin.users') }}" class="d-block p-3 rounded-3 text-decoration-none border h-100" style="background:#eff6ff;border-color:#bfdbfe !important;">
            <div class="fw-bold text-primary mb-1"><i class="fas fa-user-edit me-1"></i> 1. My Profile</div>
            <div class="small text-secondary">Account details, phone, password &amp; user CRUD</div>
          </a>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
          <a href="{{ route('admin.security') }}" class="d-block p-3 rounded-3 text-decoration-none border h-100" style="background:#f0fdf4;border-color:#bbf7d0 !important;">
            <div class="fw-bold text-success mb-1"><i class="fas fa-lock me-1"></i> 2. Two Factor (2FA)</div>
            <div class="small text-secondary">Enable/Disable 2FA &amp; secret key regeneration</div>
          </a>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
          <a href="{{ route('admin.commissions') }}" class="d-block p-3 rounded-3 text-decoration-none border h-100" style="background:#fffbeb;border-color:#fde68a !important;">
            <div class="fw-bold text-warning mb-1" style="color:#b45309 !important;"><i class="fas fa-gift me-1"></i> 3. Refer &amp; Earn</div>
            <div class="small text-secondary">Referral codes, referred users &amp; bonus payouts</div>
          </a>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
          <a href="{{ route('admin.commissions') }}" class="d-block p-3 rounded-3 text-decoration-none border h-100" style="background:#faf5ff;border-color:#e9d5ff !important;">
            <div class="fw-bold mb-1" style="color:#7e22ce;"><i class="fas fa-percent me-1"></i> 4. Rolling Comm.</div>
            <div class="small text-secondary">Fancy, Matka, Casino, Sportbook, Bookmaker %</div>
          </a>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
          <a href="{{ route('admin.banking') }}" class="d-block p-3 rounded-3 text-decoration-none border h-100" style="background:#ecfeff;border-color:#a5f3fc !important;">
            <div class="fw-bold mb-1" style="color:#0e7490;"><i class="fas fa-money-check-alt me-1"></i> 5. Banking &amp; Reward</div>
            <div class="small text-secondary">Deposits, Withdrawals, UTR receipts &amp; Rewards</div>
          </a>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
          <a href="{{ route('admin.bank.accounts') }}" class="d-block p-3 rounded-3 text-decoration-none border h-100" style="background:#fdf2f8;border-color:#fbcfe8 !important;">
            <div class="fw-bold mb-1" style="color:#be185d;"><i class="fas fa-university me-1"></i> 6. Withdraw Details</div>
            <div class="small text-secondary">User saved Bank Accounts, IFSC &amp; UPI IDs</div>
          </a>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
          <a href="{{ route('admin.statements') }}" class="d-block p-3 rounded-3 text-decoration-none border h-100" style="background:#f8fafc;border-color:#cbd5e1 !important;">
            <div class="fw-bold text-dark mb-1"><i class="fas fa-file-invoice-dollar text-primary me-1"></i> 7. Account Statement</div>
            <div class="small text-secondary">Create, edit &amp; delete Credit/Debit/Balance ledger entries</div>
          </a>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
          <a href="{{ route('admin.bets') }}" class="d-block p-3 rounded-3 text-decoration-none border h-100" style="background:#f0fdf4;border-color:#86efac !important;">
            <div class="fw-bold text-dark mb-1"><i class="fas fa-hand-holding-usd text-success me-1"></i> 8. Bets &amp; Cash Out</div>
            <div class="small text-secondary">Create, edit, Cash Out, Win/Lose/Void bets + P&amp;L report</div>
          </a>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
          <a href="{{ route('admin.security') }}" class="d-block p-3 rounded-3 text-decoration-none border h-100" style="background:#f8fafc;border-color:#cbd5e1 !important;">
            <div class="fw-bold text-dark mb-1"><i class="fas fa-key text-warning me-1"></i> 9. Password History</div>
            <div class="small text-secondary">Track, add, edit &amp; delete user password change history</div>
          </a>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
          <a href="{{ route('admin.activity.logs') }}" class="d-block p-3 rounded-3 text-decoration-none border h-100" style="background:#f8fafc;border-color:#cbd5e1 !important;">
            <div class="fw-bold text-dark mb-1"><i class="fas fa-globe text-danger me-1"></i> 10. Activity &amp; OTPs</div>
            <div class="small text-secondary">Login status, IP address, ISP, City/State/Country &amp; OTP logs</div>
          </a>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
          <a href="{{ route('admin.live.scores') }}" class="d-block p-3 rounded-3 text-decoration-none border h-100" style="background:#fff1f2;border-color:#fecdd3 !important;">
            <div class="fw-bold text-danger mb-1"><i class="fas fa-satellite-dish me-1"></i> 11. Live Scoreboards</div>
            <div class="small text-secondary">Cricbuzz, UEFA Football, Tennis Scoreboards &amp; Ball-By-Ball CRUD</div>
          </a>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
          <a href="{{ route('admin.events') }}" class="d-block p-3 rounded-3 text-decoration-none border h-100" style="background:#fefce8;border-color:#fef08a !important;">
            <div class="fw-bold text-dark mb-1"><i class="fas fa-trophy text-warning me-1"></i> 12. Sports &amp; Casino</div>
            <div class="small text-secondary">Manage all Sports Categories, Match Odds, Bookmaker &amp; 217+ Casino Games</div>
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <!-- Recent Deposits & Withdrawals -->
    <div class="col-lg-6">
      <div class="adm-card h-100">
        <div class="adm-card-header">
          <h6 class="fw-bold m-0"><i class="fas fa-wallet text-warning me-1"></i> Recent Deposit &amp; Withdrawal Requests</h6>
          <a href="{{ route('admin.banking') }}" class="btn btn-sm btn-primary py-0 px-2 fw-bold">Manage Banking</a>
        </div>
        <div class="adm-card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-sm small text-center align-middle mb-0">
              <thead>
                <tr>
                  <th>Ref</th>
                  <th>User</th>
                  <th>Type</th>
                  <th>Method</th>
                  <th>Amount</th>
                  <th>Status</th>
                  <th>Quick Action</th>
                </tr>
              </thead>
              <tbody>
                @forelse($pendingTx as $tx)
                  <tr>
                    <td class="fw-bold">{{ $tx->tx_ref }}</td>
                    <td class="fw-bold">
                      @if($tx->user)
                        <a href="{{ route('admin.users.show', $tx->user_id) }}" class="text-decoration-none">{{ $tx->user->username }}</a>
                      @else
                        -
                      @endif
                    </td>
                    <td>
                      <span class="badge {{ $tx->type === 'deposit' ? 'bg-success' : ($tx->type === 'withdraw' ? 'bg-primary' : 'bg-purple bg-info text-dark') }}">
                        {{ strtoupper($tx->type) }}
                      </span>
                    </td>
                    <td>{{ $tx->method }}</td>
                    <td class="fw-bold">{{ number_format($tx->final_amount, 2) }}</td>
                    <td>
                      <span class="badge {{ $tx->status === 'APPROVED' ? 'bg-success' : ($tx->status === 'REJECTED' ? 'bg-danger' : 'bg-warning text-dark') }}">
                        {{ $tx->status }}
                      </span>
                    </td>
                    <td>
                      @if($tx->status === 'PENDING')
                        <div class="d-flex justify-content-center gap-1">
                          <form action="{{ route('admin.banking.process', $tx->id) }}" method="POST" class="m-0">
                            @csrf
                            <input type="hidden" name="action" value="approve">
                            <button class="btn btn-sm btn-success py-0 px-2 fw-bold">Approve</button>
                          </form>
                          <form action="{{ route('admin.banking.process', $tx->id) }}" method="POST" class="m-0">
                            @csrf
                            <input type="hidden" name="action" value="reject">
                            <button class="btn btn-sm btn-danger py-0 px-2 fw-bold">Reject</button>
                          </form>
                        </div>
                      @else
                        <a href="{{ route('admin.banking') }}" class="btn btn-sm btn-outline-secondary py-0 px-2">Edit</a>
                      @endif
                    </td>
                  </tr>
                @empty
                  <tr><td colspan="7" class="text-muted py-3">No banking transactions yet.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Exchange & Casino Bets -->
    <div class="col-lg-6">
      <div class="adm-card h-100">
        <div class="adm-card-header">
          <h6 class="fw-bold m-0"><i class="fas fa-receipt text-primary me-1"></i> Recent Exchange &amp; Casino Bets</h6>
          <a href="{{ route('admin.bets') }}" class="btn btn-sm btn-primary py-0 px-2 fw-bold">Manage All Bets</a>
        </div>
        <div class="adm-card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-sm small text-center align-middle mb-0">
              <thead>
                <tr>
                  <th>Ref</th>
                  <th>User</th>
                  <th>Event / Game</th>
                  <th>Selection</th>
                  <th>Stake</th>
                  <th>P/L</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                @forelse($recentBets as $b)
                  <tr>
                    <td class="fw-bold">{{ $b->bet_ref }}</td>
                    <td class="fw-bold">
                      @if($b->user)
                        <a href="{{ route('admin.users.show', $b->user_id) }}" class="text-decoration-none">{{ $b->user->username }}</a>
                      @else
                        -
                      @endif
                    </td>
                    <td>{{ Str::limit($b->event_name, 24) }}</td>
                    <td class="fw-bold">{{ $b->selection_name }}</td>
                    <td>{{ number_format($b->stake, 2) }}</td>
                    <td class="fw-bold {{ $b->profit_loss >= 0 ? 'text-success' : 'text-danger' }}">
                      {{ $b->profit_loss >= 0 ? '+' : '' }}{{ number_format($b->profit_loss, 2) }}
                    </td>
                    <td>
                      <span class="badge {{ $b->status === 'WON' ? 'bg-success' : ($b->status === 'LOST' ? 'bg-danger' : 'bg-primary') }}">{{ $b->status }}</span>
                    </td>
                  </tr>
                @empty
                  <tr><td colspan="7" class="text-muted py-3">No bets placed yet.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3">
    <!-- Recent Users with 360 My Account Inspector Link -->
    <div class="col-lg-6">
      <div class="adm-card h-100">
        <div class="adm-card-header">
          <h6 class="fw-bold m-0"><i class="fas fa-users text-success me-1"></i> Accounts &amp; 360° "My Account" Inspector</h6>
          <a href="{{ route('admin.users') }}" class="btn btn-sm btn-success py-0 px-2 fw-bold">All Users</a>
        </div>
        <div class="adm-card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-sm small text-center align-middle mb-0">
              <thead>
                <tr>
                  <th>Username</th>
                  <th>Phone</th>
                  <th>Balance</th>
                  <th>2FA</th>
                  <th>Status</th>
                  <th>360° Inspector</th>
                </tr>
              </thead>
              <tbody>
                @foreach($recentUsers as $u)
                  <tr>
                    <td class="fw-bold">{{ $u->username }}</td>
                    <td>{{ $u->country_code }} {{ $u->phone }}</td>
                    <td class="fw-bold text-success">{{ number_format($u->balance, 2) }} PTI</td>
                    <td>
                      <span class="badge {{ $u->two_factor_enabled ? 'bg-success' : 'bg-secondary' }}">
                        {{ $u->two_factor_enabled ? 'ON' : 'OFF' }}
                      </span>
                    </td>
                    <td>
                      <span class="badge {{ $u->status === 'active' ? 'bg-success' : 'bg-danger' }}">{{ strtoupper($u->status) }}</span>
                    </td>
                    <td>
                      <a href="{{ route('admin.users.show', $u->id) }}" class="btn btn-sm btn-primary py-0 px-2 fw-bold">
                        <i class="fas fa-eye me-1"></i> Full My Account
                      </a>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Activity Logs -->
    <div class="col-lg-6">
      <div class="adm-card h-100">
        <div class="adm-card-header">
          <h6 class="fw-bold m-0"><i class="fas fa-history text-danger me-1"></i> Recent Login &amp; Activity Logs</h6>
          <a href="{{ route('admin.activity.logs') }}" class="btn btn-sm btn-dark py-0 px-2 fw-bold">Manage Logs</a>
        </div>
        <div class="adm-card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-sm small text-center align-middle mb-0">
              <thead>
                <tr>
                  <th>User</th>
                  <th>Login Status</th>
                  <th>IP Address</th>
                  <th>ISP</th>
                  <th>Location</th>
                  <th>Time</th>
                </tr>
              </thead>
              <tbody>
                @forelse($recentLogs as $log)
                  <tr>
                    <td class="fw-bold">{{ $log->user?->username ?? '-' }}</td>
                    <td>
                      <span class="badge {{ str_contains(strtolower($log->login_status), 'fail') ? 'bg-danger' : 'bg-success' }}">
                        {{ $log->login_status }}
                      </span>
                    </td>
                    <td><code>{{ $log->ip_address }}</code></td>
                    <td>{{ $log->isp }}</td>
                    <td>{{ $log->city_state_country }}</td>
                    <td>{{ $log->created_at?->format('d-m H:i') }}</td>
                  </tr>
                @empty
                  <tr><td colspan="6" class="text-muted py-3">No activity logs recorded yet.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
