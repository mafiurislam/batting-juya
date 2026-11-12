@extends('layouts.admin')
@section('title', '2FA Security & Password History - Admin Panel')

@section('content')
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
      <h5 class="fw-bolder m-0"><i class="fas fa-shield-alt text-success me-1"></i> Two-Factor Authentication (2FA) &amp; Password History (Full CRUD)</h5>
      <small class="text-muted">Manage 2FA status &amp; secret keys for all users, and Create, Edit, Update or Delete Password Change History records.</small>
    </div>
    <button class="btn btn-sm btn-primary fw-bold px-3 py-2" data-bs-toggle="modal" data-bs-target="#createPwHistoryModal">
      <i class="fas fa-key me-1"></i> Add Password History / Reset Password
    </button>
  </div>

  <!-- 1. Two-Factor Authentication (2FA) Control Table -->
  <div class="adm-card mb-4">
    <div class="adm-card-header">
      <h6 class="fw-bold m-0 text-success"><i class="fas fa-lock me-1"></i> User Two-Factor Authentication (2FA) Control</h6>
      <span class="small text-muted">Corresponds to the user's "Two Factor Authentication" page (`/2fa`)</span>
    </div>
    <div class="adm-card-body p-0">
      <div class="table-responsive" style="max-height:260px;">
        <table class="table table-hover table-bordered table-sm small text-center align-middle mb-0">
          <thead>
            <tr>
              <th>ID</th>
              <th>Username</th>
              <th>Mobile</th>
              <th>2FA Secret Key</th>
              <th>2FA Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($users as $u)
              <tr>
                <td class="fw-bold">#{{ $u->id }}</td>
                <td class="fw-bold">
                  <a href="{{ route('admin.users.show', $u->id) }}" class="text-decoration-none">{{ $u->username }}</a>
                </td>
                <td>{{ $u->country_code }} {{ $u->phone }}</td>
                <td><code>{{ $u->two_factor_code ?: 'PVCVIZSNMN' }}</code></td>
                <td>
                  <span class="badge {{ $u->two_factor_enabled ? 'bg-success' : 'bg-secondary' }}">
                    {{ $u->two_factor_enabled ? 'ENABLED' : 'DISABLED' }}
                  </span>
                </td>
                <td>
                  <div class="d-flex justify-content-center gap-1">
                    <form action="{{ route('admin.security.2fa', $u->id) }}" method="POST" class="m-0">
                      @csrf
                      <input type="hidden" name="action" value="toggle">
                      <button type="submit" class="btn btn-sm {{ $u->two_factor_enabled ? 'btn-warning' : 'btn-success' }} py-0 px-2 fw-bold">
                        {{ $u->two_factor_enabled ? 'Disable 2FA' : 'Enable 2FA' }}
                      </button>
                    </form>
                    <form action="{{ route('admin.security.2fa', $u->id) }}" method="POST" class="m-0">
                      @csrf
                      <input type="hidden" name="action" value="regenerate">
                      <button type="submit" class="btn btn-sm btn-dark py-0 px-2 fw-bold">
                        <i class="fas fa-sync-alt me-1"></i> Regenerate Key
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- 2. Password History CRUD Table -->
  <div class="adm-card">
    <div class="adm-card-header">
      <form method="GET" action="{{ route('admin.security') }}" class="d-flex gap-2 m-0">
        <input type="text" name="q" value="{{ $q }}" class="form-control form-control-sm" style="width:240px;" placeholder="Search username, remark, IP...">
        <button type="submit" class="btn btn-sm btn-dark fw-bold">Search</button>
      </form>
      <div class="small fw-bold text-secondary">Password Change History Records (`/password-history`)</div>
    </div>

    <div class="adm-card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover table-bordered table-sm small text-center align-middle mb-0">
          <thead>
            <tr>
              <th>ID</th>
              <th>User</th>
              <th>Changed By</th>
              <th>Remark</th>
              <th>IP Address</th>
              <th>Date / Time</th>
              <th>Edit / Delete</th>
            </tr>
          </thead>
          <tbody>
            @forelse($passwordHistories as $ph)
              <tr>
                <td class="fw-bold">#{{ $ph->id }}</td>
                <td class="fw-bold">
                  @if($ph->user)
                    <a href="{{ route('admin.users.show', $ph->user_id) }}" class="text-decoration-none">{{ $ph->user->username }}</a>
                  @else
                    -
                  @endif
                </td>
                <td><span class="badge bg-info text-dark">{{ $ph->changed_by }}</span></td>
                <td>{{ $ph->remark }}</td>
                <td><code>{{ $ph->ip_address ?? '127.0.0.1' }}</code></td>
                <td>{{ $ph->created_at?->format('d-m-Y H:i:s') }}</td>
                <td>
                  <div class="d-flex justify-content-center gap-1">
                    <button type="button" class="btn btn-sm btn-info py-0 px-2 fw-bold text-dark"
                            onclick='openEditPwModal(@json($ph))'>
                      <i class="fas fa-edit"></i> Edit
                    </button>
                    <form action="{{ route('admin.security.pw.delete', $ph->id) }}" method="POST" class="m-0" onsubmit="return confirm('Delete password history #{{ $ph->id }}?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-danger py-0 px-2"><i class="fas fa-trash"></i></button>
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              <tr><td colspan="7" class="text-muted py-3">No password change history records found.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="p-2 border-top">{{ $passwordHistories->links('pagination::bootstrap-5') }}</div>
  </div>

  <!-- Create Password History Modal -->
  <div class="modal fade" id="createPwHistoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header py-2 bg-primary text-white">
          <h6 class="modal-title fw-bold m-0"><i class="fas fa-key me-1"></i> Create Password History / Reset User Password</h6>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <form action="{{ route('admin.security.pw.create') }}" method="POST">
          @csrf
          <div class="modal-body">
            <div class="mb-2">
              <label class="small fw-bold">Select User *</label>
              <select name="user_id" class="form-select form-select-sm" required>
                @foreach($users as $u)
                  <option value="{{ $u->id }}">{{ $u->username }}</option>
                @endforeach
              </select>
            </div>
            <div class="mb-2">
              <label class="small fw-bold">New Password (Optional - if filled, resets user's login password)</label>
              <input type="text" name="new_password" class="form-control form-control-sm" placeholder="Leave blank to only log history">
            </div>
            <div class="mb-2">
              <label class="small fw-bold">Changed By *</label>
              <input type="text" name="changed_by" value="Admin" class="form-control form-control-sm" required>
            </div>
            <div class="mb-2">
              <label class="small fw-bold">Remark *</label>
              <input type="text" name="remark" value="Password Changed Successfully." class="form-control form-control-sm" required>
            </div>
            <div class="mb-2">
              <label class="small fw-bold">IP Address</label>
              <input type="text" name="ip_address" value="127.0.0.1" class="form-control form-control-sm">
            </div>
          </div>
          <div class="modal-footer py-1">
            <button type="submit" class="btn btn-sm btn-primary fw-bold px-4">Save Record</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Edit Password History Modal -->
  <div class="modal fade" id="editPwModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header py-2 bg-info text-dark">
          <h6 class="modal-title fw-bold m-0"><i class="fas fa-edit me-1"></i> Edit Password History Record</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form id="editPwForm" method="POST">
          @csrf
          @method('PUT')
          <div class="modal-body">
            <div class="mb-2">
              <label class="small fw-bold">Changed By *</label>
              <input type="text" name="changed_by" id="epw_by" class="form-control form-control-sm" required>
            </div>
            <div class="mb-2">
              <label class="small fw-bold">Remark *</label>
              <input type="text" name="remark" id="epw_remark" class="form-control form-control-sm" required>
            </div>
            <div class="mb-2">
              <label class="small fw-bold">IP Address</label>
              <input type="text" name="ip_address" id="epw_ip" class="form-control form-control-sm">
            </div>
          </div>
          <div class="modal-footer py-1">
            <button type="submit" class="btn btn-sm btn-primary fw-bold px-4">Update Record</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    function openEditPwModal(ph) {
      document.getElementById('editPwForm').action = `/admin/security/password-history/${ph.id}`;
      document.getElementById('epw_by').value = ph.changed_by || 'Self';
      document.getElementById('epw_remark').value = ph.remark || '';
      document.getElementById('epw_ip').value = ph.ip_address || '127.0.0.1';
      new bootstrap.Modal(document.getElementById('editPwModal')).show();
    }
  </script>
@endsection

