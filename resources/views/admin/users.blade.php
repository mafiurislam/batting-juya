@extends('layouts.admin')
@section('title', 'My Profile & Users Management - Admin Panel')

@section('content')
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
      <h5 class="fw-bolder m-0"><i class="fas fa-user-circle text-primary me-1"></i> My Profile &amp; Users Management (Full CRUD)</h5>
      <small class="text-muted">Create, Edit, Update, Delete users, adjust PTI wallet balances, or open any user's 360° "My Account" Inspector.</small>
    </div>
    <button class="btn btn-sm btn-primary fw-bold px-3 py-2" data-bs-toggle="modal" data-bs-target="#createUserModal">
      <i class="fas fa-user-plus me-1"></i> Create New Account
    </button>
  </div>

  <div class="adm-card mb-3">
    <div class="adm-card-header">
      <form method="GET" action="{{ route('admin.users') }}" class="d-flex flex-wrap gap-2 align-items-center m-0">
        <input type="text" name="q" value="{{ $q }}" class="form-control form-control-sm" style="width:240px;" placeholder="Search username, phone, referral...">
        <select name="role" class="form-select form-select-sm" style="width:140px;">
          <option value="all" {{ ($role ?? 'all') === 'all' ? 'selected' : '' }}>All Roles</option>
          <option value="player" {{ ($role ?? '') === 'player' ? 'selected' : '' }}>Players</option>
          <option value="admin" {{ ($role ?? '') === 'admin' ? 'selected' : '' }}>Admins</option>
          <option value="demo" {{ ($role ?? '') === 'demo' ? 'selected' : '' }}>Demo IDs</option>
          <option value="suspended" {{ ($role ?? '') === 'suspended' ? 'selected' : '' }}>Suspended</option>
        </select>
        <button type="submit" class="btn btn-sm btn-dark fw-bold"><i class="fas fa-search me-1"></i> Filter</button>
      </form>
      <div class="small fw-bold text-secondary">Total Users: {{ $users->total() }}</div>
    </div>

    <div class="adm-card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover table-bordered table-sm small text-center align-middle mb-0">
          <thead>
            <tr>
              <th>ID</th>
              <th>Username / Name</th>
              <th>Mobile</th>
              <th>Balance (PTI)</th>
              <th>Exposure</th>
              <th>Referral Code</th>
              <th>2FA</th>
              <th>Role</th>
              <th>Status</th>
              <th>Full My Account &amp; CRUD Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($users as $u)
              <tr>
                <td class="fw-bold">#{{ $u->id }}</td>
                <td class="text-start ps-2">
                  <div class="fw-bold text-primary">{{ $u->username }}</div>
                  <div class="text-muted" style="font-size:11px;">{{ $u->name }} | {{ $u->email }}</div>
                </td>
                <td>{{ $u->country_code }} {{ $u->phone }}</td>
                <td class="fw-bold text-success">{{ number_format($u->balance, 2) }}</td>
                <td class="fw-bold text-danger">{{ number_format($u->exposure, 2) }}</td>
                <td><code>{{ $u->referral_code ?? '-' }}</code></td>
                <td>
                  <span class="badge {{ $u->two_factor_enabled ? 'bg-success' : 'bg-secondary' }}">
                    {{ $u->two_factor_enabled ? 'ENABLED' : 'OFF' }}
                  </span>
                </td>
                <td>
                  @if($u->is_admin)
                    <span class="badge bg-danger">ADMIN</span>
                  @elseif($u->is_demo)
                    <span class="badge bg-secondary">DEMO</span>
                  @else
                    <span class="badge bg-info text-dark">PLAYER</span>
                  @endif
                </td>
                <td>
                  <span class="badge {{ $u->status === 'active' ? 'bg-success' : 'bg-danger' }}">{{ strtoupper($u->status) }}</span>
                </td>
                <td>
                  <div class="d-flex flex-wrap justify-content-center gap-1">
                    <a href="{{ route('admin.users.show', $u->id) }}" class="btn btn-sm btn-primary py-0 px-2 fw-bold" title="Open Full My Account Hub">
                      <i class="fas fa-id-badge me-1"></i> My Account
                    </a>
                    <button type="button" class="btn btn-sm btn-success py-0 px-2 fw-bold"
                            onclick="openBalanceModal({{ $u->id }}, '{{ addslashes($u->username) }}', 'credit')">
                      + Credit
                    </button>
                    <button type="button" class="btn btn-sm btn-warning py-0 px-2 fw-bold"
                            onclick="openBalanceModal({{ $u->id }}, '{{ addslashes($u->username) }}', 'debit')">
                      - Debit
                    </button>
                    <button type="button" class="btn btn-sm btn-info py-0 px-2 fw-bold text-dark"
                            onclick='openEditUserModal(@json($u))'>
                      <i class="fas fa-edit"></i> Edit
                    </button>
                    @if($u->id !== auth()->id())
                      <form action="{{ route('admin.users.delete', $u->id) }}" method="POST" class="m-0" onsubmit="return confirm('Delete user {{ addslashes($u->username) }} and all records?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger py-0 px-2 fw-bold">
                          <i class="fas fa-trash"></i>
                        </button>
                      </form>
                    @endif
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    <div class="p-2 border-top">{{ $users->links('pagination::bootstrap-5') }}</div>
  </div>

  <!-- Create User Modal -->
  <div class="modal fade" id="createUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header py-2 bg-primary text-white">
          <h6 class="modal-title fw-bold m-0"><i class="fas fa-user-plus me-1"></i> Create New Account (With My Profile &amp; Rolling Commission)</h6>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <form action="{{ route('admin.users.create') }}" method="POST">
          @csrf
          <div class="modal-body">
            <div class="row g-2">
              <div class="col-md-4">
                <label class="small fw-bold">Username / Login ID *</label>
                <input type="text" name="username" class="form-control form-control-sm" placeholder="e.g. player99" required>
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">Full Name</label>
                <input type="text" name="name" class="form-control form-control-sm" placeholder="Full Name">
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">Email</label>
                <input type="email" name="email" class="form-control form-control-sm" placeholder="user@cricexperts.com">
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">Mobile Number *</label>
                <input type="text" name="phone" class="form-control form-control-sm" placeholder="9876543210" required>
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">Password *</label>
                <input type="text" name="password" value="Player@123" class="form-control form-control-sm" required>
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">Opening Balance (PTI) *</label>
                <input type="number" step="0.01" name="balance" value="5000" class="form-control form-control-sm fw-bold" required>
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">Bonus Balance (PTI)</label>
                <input type="number" step="0.01" name="bonus_balance" value="0" class="form-control form-control-sm">
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">Referral Code</label>
                <input type="text" name="referral_code" class="form-control form-control-sm" placeholder="Auto-generated if blank">
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">Referred By</label>
                <input type="text" name="referred_by" class="form-control form-control-sm" placeholder="Referrer username">
              </div>
            </div>

            <hr class="my-2">
            <div class="small fw-bold text-primary mb-1">Rolling Commission Rates (%)</div>
            <div class="row g-2 mb-2">
              @foreach(['fancy' => 'Fancy', 'matka' => 'Matka', 'casino' => 'Casino', 'binary' => 'Binary', 'sportbook' => 'Sportbook', 'bookmaker' => 'Bookmaker'] as $rk => $rl)
                <div class="col-4 col-md-2">
                  <label class="small">{{ $rl }}</label>
                  <input type="number" step="0.1" name="rc_{{ $rk }}" value="0" class="form-control form-control-sm text-center">
                </div>
              @endforeach
            </div>

            <div class="d-flex gap-4 mt-2">
              <div class="form-check small">
                <input class="form-check-input" type="checkbox" name="is_admin" value="1" id="chkCreateAdmin">
                <label class="form-check-label fw-bold text-danger" for="chkCreateAdmin">Grant Master Admin Access</label>
              </div>
              <div class="form-check small">
                <input class="form-check-input" type="checkbox" name="two_factor_enabled" value="1" id="chkCreate2fa">
                <label class="form-check-label fw-bold text-success" for="chkCreate2fa">Enable 2-Factor Auth (2FA)</label>
              </div>
            </div>
          </div>
          <div class="modal-footer py-1">
            <button type="submit" class="btn btn-sm btn-primary fw-bold px-4">Create Account</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Edit User Modal -->
  <div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header py-2 bg-info text-dark">
          <h6 class="modal-title fw-bold m-0"><i class="fas fa-user-edit me-1"></i> Edit User Profile, Wallet &amp; My Account Settings</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form id="editUserForm" method="POST">
          @csrf
          @method('PUT')
          <div class="modal-body">
            <div class="row g-2">
              <div class="col-md-4">
                <label class="small fw-bold">Username / Login ID *</label>
                <input type="text" name="username" id="eu_username" class="form-control form-control-sm" required>
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">Full Name</label>
                <input type="text" name="name" id="eu_name" class="form-control form-control-sm">
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">Email</label>
                <input type="text" name="email" id="eu_email" class="form-control form-control-sm">
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">Mobile Number *</label>
                <input type="text" name="phone" id="eu_phone" class="form-control form-control-sm" required>
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">New Password (leave blank to keep)</label>
                <input type="text" name="password" class="form-control form-control-sm" placeholder="Enter new password to reset">
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">Account Status *</label>
                <select name="status" id="eu_status" class="form-select form-select-sm">
                  <option value="active">ACTIVE</option>
                  <option value="suspended">SUSPENDED</option>
                  <option value="locked">LOCKED</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">Balance (PTI) *</label>
                <input type="number" step="0.01" name="balance" id="eu_balance" class="form-control form-control-sm fw-bold text-success" required>
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">Exposure (PTI) *</label>
                <input type="number" step="0.01" name="exposure" id="eu_exposure" class="form-control form-control-sm fw-bold text-danger" required>
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">Bonus Balance (PTI)</label>
                <input type="number" step="0.01" name="bonus_balance" id="eu_bonus" class="form-control form-control-sm">
              </div>
              <div class="col-md-6">
                <label class="small fw-bold">Referral Code</label>
                <input type="text" name="referral_code" id="eu_referral_code" class="form-control form-control-sm">
              </div>
              <div class="col-md-6">
                <label class="small fw-bold">Referred By</label>
                <input type="text" name="referred_by" id="eu_referred_by" class="form-control form-control-sm">
              </div>
            </div>

            <hr class="my-2">
            <div class="small fw-bold text-primary mb-1">Rolling Commission Rates (%)</div>
            <div class="row g-2 mb-2">
              @foreach(['fancy' => 'Fancy', 'matka' => 'Matka', 'casino' => 'Casino', 'binary' => 'Binary', 'sportbook' => 'Sportbook', 'bookmaker' => 'Bookmaker'] as $rk => $rl)
                <div class="col-4 col-md-2">
                  <label class="small">{{ $rl }}</label>
                  <input type="number" step="0.1" name="rc_{{ $rk }}" id="eu_rc_{{ $rk }}" value="0" class="form-control form-control-sm text-center">
                </div>
              @endforeach
            </div>

            <div class="d-flex gap-4 mt-2">
              <div class="form-check small">
                <input class="form-check-input" type="checkbox" name="is_admin" value="1" id="eu_is_admin">
                <label class="form-check-label fw-bold text-danger" for="eu_is_admin">Grant Master Admin Access</label>
              </div>
              <div class="form-check small">
                <input class="form-check-input" type="checkbox" name="two_factor_enabled" value="1" id="eu_two_factor">
                <label class="form-check-label fw-bold text-success" for="eu_two_factor">Two-Factor Auth (2FA) Enabled</label>
              </div>
            </div>
          </div>
          <div class="modal-footer py-1">
            <button type="submit" class="btn btn-sm btn-primary fw-bold px-4">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Credit / Debit Balance Modal -->
  <div class="modal fade" id="balanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header py-2 bg-dark text-white">
          <h6 class="modal-title fw-bold m-0" id="balanceModalTitle">Adjust PTI</h6>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <form id="balanceModalForm" method="POST">
          @csrf
          <input type="hidden" name="action" id="balanceModalAction" value="credit">
          <div class="modal-body">
            <div class="mb-2">
              <label class="small fw-bold">Amount (PTI) *</label>
              <input type="number" step="0.01" name="amount" value="1000" min="1" class="form-control form-control-sm fw-bold" required>
            </div>
            <div class="mb-2">
              <label class="small fw-bold">Remark</label>
              <input type="text" name="remark" class="form-control form-control-sm" placeholder="Admin Credit / Chip Deposit">
            </div>
          </div>
          <div class="modal-footer py-1">
            <button type="submit" class="btn btn-sm btn-success fw-bold w-100">Confirm</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    function openBalanceModal(userId, username, action) {
      document.getElementById('balanceModalTitle').textContent = `${action.toUpperCase()} PTI - ${username}`;
      document.getElementById('balanceModalAction').value = action;
      document.getElementById('balanceModalForm').action = `/admin/users/${userId}/balance`;
      new bootstrap.Modal(document.getElementById('balanceModal')).show();
    }

    function openEditUserModal(u) {
      document.getElementById('editUserForm').action = `/admin/users/${u.id}`;
      document.getElementById('eu_username').value = u.username || '';
      document.getElementById('eu_name').value = u.name || '';
      document.getElementById('eu_email').value = u.email || '';
      document.getElementById('eu_phone').value = u.phone || '';
      document.getElementById('eu_status').value = u.status || 'active';
      document.getElementById('eu_balance').value = u.balance ?? 0;
      document.getElementById('eu_exposure').value = u.exposure ?? 0;
      document.getElementById('eu_bonus').value = u.bonus_balance ?? 0;
      document.getElementById('eu_referral_code').value = u.referral_code || '';
      document.getElementById('eu_referred_by').value = u.referred_by || '';
      document.getElementById('eu_is_admin').checked = !!u.is_admin;
      document.getElementById('eu_two_factor').checked = !!u.two_factor_enabled;

      const rc = u.rolling_commission || {};
      ['fancy', 'matka', 'casino', 'binary', 'sportbook', 'bookmaker'].forEach(k => {
        const el = document.getElementById('eu_rc_' + k);
        if (el) el.value = rc[k] ?? 0;
      });

      new bootstrap.Modal(document.getElementById('editUserModal')).show();
    }
  </script>
@endsection
