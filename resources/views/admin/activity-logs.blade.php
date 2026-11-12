@extends('layouts.admin')
@section('title', 'Activity Logs & OTP Logs - Admin Panel')

@section('content')
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
      <h5 class="fw-bolder m-0"><i class="fas fa-history text-danger me-1"></i> Login Activity Logs &amp; MSG91 OTP Logs (Full CRUD)</h5>
      <small class="text-muted">Manage all user Login Activity Logs (Login Status, IP Address, ISP, City/State/Country) shown in `/activity-log`, plus SMS OTP logs.</small>
    </div>
    <button class="btn btn-sm btn-primary fw-bold px-3 py-2" data-bs-toggle="modal" data-bs-target="#createLogModal">
      <i class="fas fa-plus-circle me-1"></i> Add Activity Log Entry
    </button>
  </div>

  <!-- 1. Activity Logs CRUD Table -->
  <div class="adm-card mb-4">
    <div class="adm-card-header">
      <form method="GET" action="{{ route('admin.activity.logs') }}" class="d-flex gap-2 m-0">
        <input type="text" name="q" value="{{ $q }}" class="form-control form-control-sm" style="width:240px;" placeholder="Search user, IP, ISP, city...">
        <button type="submit" class="btn btn-sm btn-dark fw-bold">Search</button>
      </form>
      <div class="small fw-bold text-secondary">Total Activity Logs: {{ $logs->total() }}</div>
    </div>

    <div class="adm-card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover table-bordered table-sm small text-center align-middle mb-0">
          <thead>
            <tr>
              <th>ID</th>
              <th>Login Date &amp; Time</th>
              <th>User</th>
              <th>Login Status</th>
              <th>IP Address</th>
              <th>ISP</th>
              <th>City / State / Country</th>
              <th>Edit / Delete</th>
            </tr>
          </thead>
          <tbody>
            @forelse($logs as $log)
              <tr>
                <td class="fw-bold">#{{ $log->id }}</td>
                <td>{{ $log->created_at?->format('d-m-Y H:i:s') }}</td>
                <td class="fw-bold">
                  @if($log->user)
                    <a href="{{ route('admin.users.show', $log->user_id) }}" class="text-decoration-none">{{ $log->user->username }}</a>
                  @else
                    -
                  @endif
                </td>
                <td>
                  <span class="badge {{ str_contains(strtolower($log->login_status), 'fail') ? 'bg-danger' : 'bg-success' }}">
                    {{ $log->login_status }}
                  </span>
                </td>
                <td><code>{{ $log->ip_address }}</code></td>
                <td>{{ $log->isp }}</td>
                <td>{{ $log->city_state_country }}</td>
                <td>
                  <div class="d-flex justify-content-center gap-1">
                    <button type="button" class="btn btn-sm btn-info py-0 px-2 fw-bold text-dark"
                            onclick='openEditLogModal(@json($log))'>
                      <i class="fas fa-edit"></i> Edit
                    </button>
                    <form action="{{ route('admin.activity.logs.delete', $log->id) }}" method="POST" class="m-0" onsubmit="return confirm('Delete activity log #{{ $log->id }}?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-danger py-0 px-2"><i class="fas fa-trash"></i></button>
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              <tr><td colspan="8" class="text-muted py-3">No activity logs found.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="p-2 border-top">{{ $logs->links('pagination::bootstrap-5') }}</div>
  </div>

  <!-- 2. Recent MSG91 OTP Verification Logs -->
  <div class="adm-card">
    <div class="adm-card-header">
      <h6 class="fw-bold m-0 text-primary"><i class="fas fa-sms me-1"></i> Recent MSG91 SMS OTP Verification Logs</h6>
    </div>
    <div class="adm-card-body p-0">
      <div class="table-responsive">
        <table class="table table-sm table-bordered small text-center align-middle mb-0">
          <thead>
            <tr>
              <th>ID</th>
              <th>Phone</th>
              <th>Username</th>
              <th>OTP Code</th>
              <th>Purpose</th>
              <th>Status</th>
              <th>Created At</th>
              <th>Delete</th>
            </tr>
          </thead>
          <tbody>
            @forelse($otps as $otp)
              <tr>
                <td>#{{ $otp->id }}</td>
                <td>{{ $otp->country_code }} {{ $otp->phone }}</td>
                <td class="fw-bold">{{ $otp->username ?? '-' }}</td>
                <td class="fw-bold text-primary">{{ $otp->otp_code }}</td>
                <td><span class="badge bg-secondary">{{ strtoupper($otp->purpose) }}</span></td>
                <td>
                  <span class="badge {{ $otp->verified_at ? 'bg-success' : 'bg-warning text-dark' }}">
                    {{ $otp->verified_at ? 'VERIFIED' : 'SENT' }}
                  </span>
                </td>
                <td>{{ $otp->created_at?->format('d-m-Y H:i:s') }}</td>
                <td>
                  <form action="{{ route('admin.otp.logs.delete', $otp->id) }}" method="POST" class="m-0">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2"><i class="fas fa-trash"></i></button>
                  </form>
                </td>
              </tr>
            @empty
              <tr><td colspan="8" class="text-muted py-3">No OTP logs recorded yet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Create Activity Log Modal -->
  <div class="modal fade" id="createLogModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header py-2 bg-primary text-white">
          <h6 class="modal-title fw-bold m-0"><i class="fas fa-plus-circle me-1"></i> Add Activity Log Entry</h6>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <form action="{{ route('admin.activity.logs.create') }}" method="POST">
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
              <label class="small fw-bold">Login Status *</label>
              <select name="login_status" class="form-select form-select-sm">
                <option value="Login Success">Login Success</option>
                <option value="Login Failed">Login Failed</option>
              </select>
            </div>
            <div class="mb-2">
              <label class="small fw-bold">IP Address *</label>
              <input type="text" name="ip_address" value="103.21.124.88" class="form-control form-control-sm" required>
            </div>
            <div class="mb-2">
              <label class="small fw-bold">ISP *</label>
              <input type="text" name="isp" value="Reliance Jio Infocomm" class="form-control form-control-sm" required>
            </div>
            <div class="mb-2">
              <label class="small fw-bold">City / State / Country *</label>
              <input type="text" name="city_state_country" value="Kolkata / West Bengal / India" class="form-control form-control-sm" required>
            </div>
          </div>
          <div class="modal-footer py-1">
            <button type="submit" class="btn btn-sm btn-primary fw-bold px-4">Create Activity Log</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Edit Activity Log Modal -->
  <div class="modal fade" id="editLogModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header py-2 bg-info text-dark">
          <h6 class="modal-title fw-bold m-0"><i class="fas fa-edit me-1"></i> Edit Activity Log Entry</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form id="editLogForm" method="POST">
          @csrf
          @method('PUT')
          <div class="modal-body">
            <div class="mb-2">
              <label class="small fw-bold">Login Status *</label>
              <input type="text" name="login_status" id="elog_status" class="form-control form-control-sm" required>
            </div>
            <div class="mb-2">
              <label class="small fw-bold">IP Address *</label>
              <input type="text" name="ip_address" id="elog_ip" class="form-control form-control-sm" required>
            </div>
            <div class="mb-2">
              <label class="small fw-bold">ISP *</label>
              <input type="text" name="isp" id="elog_isp" class="form-control form-control-sm" required>
            </div>
            <div class="mb-2">
              <label class="small fw-bold">City / State / Country *</label>
              <input type="text" name="city_state_country" id="elog_loc" class="form-control form-control-sm" required>
            </div>
          </div>
          <div class="modal-footer py-1">
            <button type="submit" class="btn btn-sm btn-primary fw-bold px-4">Update Log</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    function openEditLogModal(log) {
      document.getElementById('editLogForm').action = `/admin/activity-logs/${log.id}`;
      document.getElementById('elog_status').value = log.login_status || 'Login Success';
      document.getElementById('elog_ip').value = log.ip_address || '127.0.0.1';
      document.getElementById('elog_isp').value = log.isp || '';
      document.getElementById('elog_loc').value = log.city_state_country || '';
      new bootstrap.Modal(document.getElementById('editLogModal')).show();
    }
  </script>
@endsection

