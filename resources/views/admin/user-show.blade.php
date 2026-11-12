@extends('layouts.admin')
@section('title', '360° My Account Inspector: ' . $user->username)

@section('content')
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div class="d-flex align-items-center gap-2">
      <a href="{{ route('admin.users') }}" class="btn btn-sm btn-outline-dark fw-bold">
        <i class="fas fa-arrow-left me-1"></i> Back to Users
      </a>
      <div>
        <h5 class="fw-bolder m-0">
          <i class="fas fa-id-badge text-primary me-1"></i> 360° "My Account" Complete Inspector: <span class="text-primary">{{ $user->username }}</span>
        </h5>
        <small class="text-muted">Manage all 10 My Account sections (Profile, 2FA, Refer &amp; Earn, Rolling Commission, Banking, Withdraw Banks, Statements, Bets, P&amp;L, Password &amp; Activity Logs) for this user.</small>
      </div>
    </div>
    <div class="d-flex gap-2">
      <span class="badge {{ $user->status === 'active' ? 'bg-success' : 'bg-danger' }} px-3 py-2">
        STATUS: {{ strtoupper($user->status) }}
      </span>
      <span class="badge {{ $user->is_admin ? 'bg-danger' : 'bg-info text-dark' }} px-3 py-2">
        ROLE: {{ $user->is_admin ? 'ADMIN' : 'PLAYER' }}
      </span>
    </div>
  </div>

  <!-- User Summary KPI Strip -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="adm-stat-card adm-grad-green">
        <div class="stat-icon"><i class="fas fa-wallet"></i></div>
        <div class="stat-label">Main PTI Balance</div>
        <div class="stat-value">{{ number_format($user->balance, 2) }} PTI</div>
        <div class="stat-sub">Exposure: {{ number_format($user->exposure, 2) }} | Bonus: {{ number_format($user->bonus_balance, 2) }}</div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="adm-stat-card adm-grad-blue">
        <div class="stat-icon"><i class="fas fa-university"></i></div>
        <div class="stat-label">Total Deposits</div>
        <div class="stat-value">{{ number_format($plSummary['total_deposits'], 2) }} PTI</div>
        <div class="stat-sub">Withdrawals: {{ number_format($plSummary['total_withdrawals'], 2) }} PTI</div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="adm-stat-card adm-grad-purple">
        <div class="stat-icon"><i class="fas fa-chart-pie"></i></div>
        <div class="stat-label">Net Profit &amp; Loss</div>
        <div class="stat-value">{{ $plSummary['total_pl'] >= 0 ? '+' : '' }}{{ number_format($plSummary['total_pl'], 2) }} PTI</div>
        <div class="stat-sub">Exchange: {{ number_format($plSummary['exchange_pl'], 2) }} | Casino: {{ number_format($plSummary['casino_pl'], 2) }}</div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="adm-stat-card adm-grad-orange">
        <div class="stat-icon"><i class="fas fa-shield-alt"></i></div>
        <div class="stat-label">2FA &amp; Referral</div>
        <div class="stat-value">{{ $user->two_factor_enabled ? '2FA ON' : '2FA OFF' }}</div>
        <div class="stat-sub">Ref Code: {{ $user->referral_code ?? '-' }} ({{ $referrals->count() }} Referrals)</div>
      </div>
    </div>
  </div>

  <!-- Row 1: 1. My Profile + 2. 2FA + 3. Refer & Earn + 4. Rolling Commission -->
  <div class="row g-3 mb-4">
    <!-- 1. My Profile & Account Details Editor -->
    <div class="col-lg-7">
      <div class="adm-card h-100">
        <div class="adm-card-header">
          <h6 class="fw-bold m-0 text-primary"><i class="fas fa-user-edit me-1"></i> 1. My Profile &amp; Rolling Commission (Edit / Update)</h6>
        </div>
        <div class="adm-card-body">
          <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row g-2">
              <div class="col-md-4">
                <label class="small fw-bold">Username *</label>
                <input type="text" name="username" value="{{ $user->username }}" class="form-control form-control-sm fw-bold" required>
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">Full Name</label>
                <input type="text" name="name" value="{{ $user->name }}" class="form-control form-control-sm">
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">Email</label>
                <input type="text" name="email" value="{{ $user->email }}" class="form-control form-control-sm">
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">Mobile Number *</label>
                <input type="text" name="phone" value="{{ $user->phone }}" class="form-control form-control-sm" required>
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">Reset Password</label>
                <input type="text" name="password" class="form-control form-control-sm" placeholder="Enter new password">
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">Status</label>
                <select name="status" class="form-select form-select-sm">
                  <option value="active" {{ $user->status === 'active' ? 'selected' : '' }}>ACTIVE</option>
                  <option value="suspended" {{ $user->status === 'suspended' ? 'selected' : '' }}>SUSPENDED</option>
                  <option value="locked" {{ $user->status === 'locked' ? 'selected' : '' }}>LOCKED</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">Balance (PTI)</label>
                <input type="number" step="0.01" name="balance" value="{{ $user->balance }}" class="form-control form-control-sm fw-bold text-success" required>
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">Exposure (PTI)</label>
                <input type="number" step="0.01" name="exposure" value="{{ $user->exposure }}" class="form-control form-control-sm fw-bold text-danger" required>
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">Bonus Balance (PTI)</label>
                <input type="number" step="0.01" name="bonus_balance" value="{{ $user->bonus_balance }}" class="form-control form-control-sm">
              </div>
              <div class="col-md-6">
                <label class="small fw-bold">Referral Code (Refer &amp; Earn)</label>
                <input type="text" name="referral_code" value="{{ $user->referral_code }}" class="form-control form-control-sm">
              </div>
              <div class="col-md-6">
                <label class="small fw-bold">Referred By</label>
                <input type="text" name="referred_by" value="{{ $user->referred_by }}" class="form-control form-control-sm">
              </div>
            </div>

            <div class="small fw-bold text-primary mt-3 mb-1">4. Rolling Commission Rates (%)</div>
            <div class="row g-2 mb-3">
              @php $rc = $user->rolling_commission ?? []; @endphp
              @foreach(['fancy' => 'Fancy', 'matka' => 'Matka', 'casino' => 'Casino', 'binary' => 'Binary', 'sportbook' => 'Sportbook', 'bookmaker' => 'Bookmaker'] as $rk => $rl)
                <div class="col-4 col-md-2">
                  <label class="small">{{ $rl }}</label>
                  <input type="number" step="0.1" name="rc_{{ $rk }}" value="{{ $rc[$rk] ?? 0 }}" class="form-control form-control-sm text-center">
                </div>
              @endforeach
            </div>

            <div class="small fw-bold text-dark mt-2 mb-1"><i class="fas fa-sliders-h text-info me-1"></i> One-Click Bet &amp; Default Stake Setting</div>
            <div class="row g-2 mb-3 align-items-center">
              <div class="col-md-5">
                <div class="form-check small">
                  <input class="form-check-input" type="checkbox" name="one_click_bet_enabled" value="1" id="u_oneclick" {{ $user->one_click_bet_enabled ? 'checked' : '' }}>
                  <label class="form-check-label fw-bold" for="u_oneclick">Enable One-Click Betting</label>
                </div>
              </div>
              <div class="col-md-7 d-flex align-items-center gap-2">
                <label class="small fw-bold text-nowrap mb-0">One-Click Default Stake (PTI):</label>
                <input type="number" name="one_click_stake" value="{{ $user->one_click_stake ?: 500 }}" class="form-control form-control-sm fw-bold" style="max-width:140px;">
              </div>
            </div>

            <div class="d-flex justify-content-between align-items-center">
              <div class="d-flex gap-3">
                <div class="form-check small">
                  <input class="form-check-input" type="checkbox" name="is_admin" value="1" id="u_admin" {{ $user->is_admin ? 'checked' : '' }}>
                  <label class="form-check-label fw-bold text-danger" for="u_admin">Admin Access</label>
                </div>
                <div class="form-check small">
                  <input class="form-check-input" type="checkbox" name="two_factor_enabled" value="1" id="u_2fa" {{ $user->two_factor_enabled ? 'checked' : '' }}>
                  <label class="form-check-label fw-bold text-success" for="u_2fa">2FA Enabled</label>
                </div>
              </div>
              <button type="submit" class="btn btn-sm btn-primary fw-bold px-4">
                <i class="fas fa-save me-1"></i> Update Profile &amp; Commission
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- 2. Two-Factor Auth + Quick Wallet Credit/Debit + 6. Saved Withdrawal Bank Accounts -->
    <div class="col-lg-5">
      <div class="adm-card mb-3">
        <div class="adm-card-header">
          <h6 class="fw-bold m-0 text-success"><i class="fas fa-shield-alt me-1"></i> 2. Two Factor Authentication (2FA) &amp; Quick Wallet</h6>
        </div>
        <div class="adm-card-body">
          <div class="d-flex justify-content-between align-items-center p-2 rounded border bg-light mb-3">
            <div>
              <div class="small fw-bold">2FA Secret Key: <code>{{ $user->two_factor_code ?: 'PVCVIZSNMN' }}</code></div>
              <div class="small text-muted">Status: <strong>{{ $user->two_factor_enabled ? 'ENABLED' : 'DISABLED' }}</strong></div>
            </div>
            <div class="d-flex gap-1">
              <form action="{{ route('admin.security.2fa', $user->id) }}" method="POST" class="m-0">
                @csrf
                <input type="hidden" name="action" value="toggle">
                <button class="btn btn-sm {{ $user->two_factor_enabled ? 'btn-warning' : 'btn-success' }} fw-bold py-0 px-2">
                  {{ $user->two_factor_enabled ? 'Disable 2FA' : 'Enable 2FA' }}
                </button>
              </form>
              <form action="{{ route('admin.security.2fa', $user->id) }}" method="POST" class="m-0">
                @csrf
                <input type="hidden" name="action" value="regenerate">
                <button class="btn btn-sm btn-dark fw-bold py-0 px-2">Regenerate Key</button>
              </form>
            </div>
          </div>

          <form action="{{ route('admin.users.balance', $user->id) }}" method="POST" class="row g-2 align-items-end">
            @csrf
            <div class="col-4">
              <label class="small fw-bold">Action</label>
              <select name="action" class="form-select form-select-sm fw-bold">
                <option value="credit">+ Credit PTI</option>
                <option value="debit">- Debit PTI</option>
              </select>
            </div>
            <div class="col-4">
              <label class="small fw-bold">Amount (PTI)</label>
              <input type="number" step="0.01" name="amount" value="1000" class="form-control form-control-sm fw-bold" required>
            </div>
            <div class="col-4">
              <button type="submit" class="btn btn-sm btn-success fw-bold w-100">Apply PTI</button>
            </div>
          </form>
        </div>
      </div>

      <!-- 6. Withdrawal Details / Saved Bank Accounts -->
      <div class="adm-card">
        <div class="adm-card-header">
          <h6 class="fw-bold m-0" style="color:#be185d;"><i class="fas fa-university me-1"></i> 6. Withdraw Details (Saved Bank Accounts)</h6>
          <button class="btn btn-sm btn-outline-primary py-0 px-2 fw-bold" data-bs-toggle="collapse" data-bs-target="#addUserBankCollapse">
            + Add Bank
          </button>
        </div>
        <div class="collapse border-bottom p-2 bg-light" id="addUserBankCollapse">
          <form action="{{ route('admin.bank.accounts.create') }}" method="POST" class="row g-2">
            @csrf
            <input type="hidden" name="user_id" value="{{ $user->id }}">
            <div class="col-6">
              <input type="text" name="holder_name" value="{{ $user->name }}" class="form-control form-control-sm" placeholder="Holder Name *" required>
            </div>
            <div class="col-6">
              <input type="text" name="bank_name" class="form-control form-control-sm" placeholder="Bank / Wallet Name *" required>
            </div>
            <div class="col-6">
              <input type="text" name="account_number" class="form-control form-control-sm" placeholder="Account / Mobile No *" required>
            </div>
            <div class="col-6">
              <input type="text" name="ifsc_code" value="HDFC0001234" class="form-control form-control-sm" placeholder="IFSC Code *" required>
            </div>
            <div class="col-8">
              <input type="text" name="upi_id" class="form-control form-control-sm" placeholder="UPI ID (optional)">
            </div>
            <div class="col-4">
              <button type="submit" class="btn btn-sm btn-primary fw-bold w-100">Save Bank</button>
            </div>
          </form>
        </div>
        <div class="adm-card-body p-0">
          <div class="table-responsive">
            <table class="table table-sm table-bordered small text-center align-middle mb-0">
              <thead>
                <tr>
                  <th>Holder</th>
                  <th>Bank</th>
                  <th>A/C No &amp; IFSC</th>
                  <th>UPI ID</th>
                  <th>Del</th>
                </tr>
              </thead>
              <tbody>
                @forelse($bankAccounts as $ba)
                  <tr>
                    <td class="fw-bold">{{ $ba->holder_name }}</td>
                    <td>{{ $ba->bank_name }}</td>
                    <td>{{ $ba->account_number }} <small class="text-muted">({{ $ba->ifsc_code }})</small></td>
                    <td>{{ $ba->upi_id ?? '-' }}</td>
                    <td>
                      <form action="{{ route('admin.bank.accounts.delete', $ba->id) }}" method="POST" class="m-0">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger py-0 px-1"><i class="fas fa-trash"></i></button>
                      </form>
                    </td>
                  </tr>
                @empty
                  <tr><td colspan="5" class="text-muted py-2">No saved withdrawal bank accounts.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Row 2: 5. Banking History (Deposits, Withdrawals, Rewards) + 7. Account Statement -->
  <div class="row g-3 mb-4">
    <div class="col-lg-6">
      <div class="adm-card h-100">
        <div class="adm-card-header">
          <h6 class="fw-bold m-0 text-info"><i class="fas fa-money-check-alt me-1"></i> 5. Banking: Deposits, Withdrawals &amp; Rewards</h6>
          <a href="{{ route('admin.banking', ['q' => $user->username]) }}" class="btn btn-sm btn-outline-primary py-0 px-2">Full CRUD</a>
        </div>
        <div class="adm-card-body p-0">
          <div class="table-responsive" style="max-height:280px;">
            <table class="table table-sm table-bordered small text-center align-middle mb-0">
              <thead>
                <tr>
                  <th>Ref</th>
                  <th>Type</th>
                  <th>Method</th>
                  <th>Amount</th>
                  <th>UTR</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                @forelse($transactions as $tx)
                  <tr>
                    <td class="fw-bold">{{ $tx->tx_ref }}</td>
                    <td><span class="badge {{ $tx->type === 'deposit' ? 'bg-success' : 'bg-primary' }}">{{ strtoupper($tx->type) }}</span></td>
                    <td>{{ $tx->method }}</td>
                    <td class="fw-bold">{{ number_format($tx->final_amount, 2) }}</td>
                    <td>{{ $tx->utr_number ?? '-' }}</td>
                    <td><span class="badge {{ $tx->status === 'APPROVED' ? 'bg-success' : ($tx->status === 'REJECTED' ? 'bg-danger' : 'bg-warning text-dark') }}">{{ $tx->status }}</span></td>
                  </tr>
                @empty
                  <tr><td colspan="6" class="text-muted py-3">No deposits or withdrawals recorded.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="adm-card h-100">
        <div class="adm-card-header">
          <h6 class="fw-bold m-0 text-primary"><i class="fas fa-file-invoice-dollar me-1"></i> 7. Account Statement Ledger</h6>
          <a href="{{ route('admin.statements', ['q' => $user->username]) }}" class="btn btn-sm btn-outline-primary py-0 px-2">Full CRUD</a>
        </div>
        <div class="adm-card-body p-0">
          <div class="table-responsive" style="max-height:280px;">
            <table class="table table-sm table-bordered small text-center align-middle mb-0">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Credit</th>
                  <th>Debit</th>
                  <th>Balance</th>
                  <th>Remark</th>
                </tr>
              </thead>
              <tbody>
                @forelse($statements as $st)
                  <tr>
                    <td>{{ $st->created_at?->format('d-m H:i') }}</td>
                    <td class="fw-bold text-success">{{ $st->credit > 0 ? number_format($st->credit, 2) : '-' }}</td>
                    <td class="fw-bold text-danger">{{ $st->debit > 0 ? number_format($st->debit, 2) : '-' }}</td>
                    <td class="fw-bold">{{ number_format($st->balance, 2) }}</td>
                    <td class="text-start">{{ Str::limit($st->remark, 38) }}</td>
                  </tr>
                @empty
                  <tr><td colspan="5" class="text-muted py-3">No statement entries.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Row 3: 8 & 9. Bets History & Profit/Loss + 10. Password History & 11. Activity Log -->
  <div class="row g-3">
    <div class="col-lg-7">
      <div class="adm-card h-100">
        <div class="adm-card-header">
          <h6 class="fw-bold m-0 text-success"><i class="fas fa-receipt me-1"></i> 8. Bets History &amp; 9. Profit/Loss</h6>
          <a href="{{ route('admin.bets', ['q' => $user->username]) }}" class="btn btn-sm btn-outline-success py-0 px-2">Full CRUD</a>
        </div>
        <div class="adm-card-body p-0">
          <div class="table-responsive" style="max-height:300px;">
            <table class="table table-sm table-bordered small text-center align-middle mb-0">
              <thead>
                <tr>
                  <th>Ref</th>
                  <th>Category</th>
                  <th>Event / Game</th>
                  <th>Selection</th>
                  <th>Odds</th>
                  <th>Stake</th>
                  <th>P/L</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                @forelse($bets as $b)
                  <tr>
                    <td class="fw-bold">{{ $b->bet_ref }}</td>
                    <td><span class="badge bg-secondary">{{ strtoupper($b->category) }}</span></td>
                    <td>{{ Str::limit($b->event_name, 22) }}</td>
                    <td class="fw-bold">{{ $b->selection_name }}</td>
                    <td>{{ number_format($b->odds, 2) }}</td>
                    <td>{{ number_format($b->stake, 2) }}</td>
                    <td class="fw-bold {{ $b->profit_loss >= 0 ? 'text-success' : 'text-danger' }}">
                      {{ $b->profit_loss >= 0 ? '+' : '' }}{{ number_format($b->profit_loss, 2) }}
                    </td>
                    <td><span class="badge {{ $b->status === 'WON' ? 'bg-success' : ($b->status === 'LOST' ? 'bg-danger' : 'bg-primary') }}">{{ $b->status }}</span></td>
                  </tr>
                @empty
                  <tr><td colspan="8" class="text-muted py-3">No bets placed by this user.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="adm-card mb-3">
        <div class="adm-card-header">
          <h6 class="fw-bold m-0 text-warning" style="color:#b45309 !important;"><i class="fas fa-key me-1"></i> 10. Password Change History</h6>
        </div>
        <div class="adm-card-body p-0">
          <div class="table-responsive" style="max-height:130px;">
            <table class="table table-sm table-bordered small text-center align-middle mb-0">
              <thead>
                <tr>
                  <th>Changed By</th>
                  <th>Remark</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                @forelse($passwordHistories as $ph)
                  <tr>
                    <td class="fw-bold">{{ $ph->changed_by }}</td>
                    <td>{{ $ph->remark }}</td>
                    <td>{{ $ph->created_at?->format('d-m-Y H:i') }}</td>
                  </tr>
                @empty
                  <tr><td colspan="3" class="text-muted py-2">No password history records.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="adm-card">
        <div class="adm-card-header">
          <h6 class="fw-bold m-0 text-danger"><i class="fas fa-globe me-1"></i> 11. Login Activity Log</h6>
        </div>
        <div class="adm-card-body p-0">
          <div class="table-responsive" style="max-height:145px;">
            <table class="table table-sm table-bordered small text-center align-middle mb-0">
              <thead>
                <tr>
                  <th>Status</th>
                  <th>IP</th>
                  <th>Location</th>
                  <th>Time</th>
                </tr>
              </thead>
              <tbody>
                @forelse($activityLogs as $al)
                  <tr>
                    <td><span class="badge {{ str_contains(strtolower($al->login_status), 'fail') ? 'bg-danger' : 'bg-success' }}">{{ $al->login_status }}</span></td>
                    <td><code>{{ $al->ip_address }}</code></td>
                    <td>{{ $al->city_state_country }}</td>
                    <td>{{ $al->created_at?->format('d-m H:i') }}</td>
                  </tr>
                @empty
                  <tr><td colspan="4" class="text-muted py-2">No activity logs.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

