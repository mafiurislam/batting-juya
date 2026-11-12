@extends('layouts.admin')
@section('title', 'Account Statements Ledger - Admin Panel')

@section('content')
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
      <h5 class="fw-bolder m-0"><i class="fas fa-file-invoice-dollar text-primary me-1"></i> Account Statement Ledger (Full CRUD)</h5>
      <small class="text-muted">Create, Edit, Update &amp; Delete Credit/Debit/Balance ledger entries shown in the user's "Account Statement" section.</small>
    </div>
    <button class="btn btn-sm btn-primary fw-bold px-3 py-2" data-bs-toggle="modal" data-bs-target="#createStmtModal">
      <i class="fas fa-plus-circle me-1"></i> Add Statement Entry
    </button>
  </div>

  <div class="adm-card">
    <div class="adm-card-header">
      <form method="GET" action="{{ route('admin.statements') }}" class="d-flex flex-wrap gap-2 align-items-center m-0">
        <input type="text" name="q" value="{{ $q }}" class="form-control form-control-sm" style="width:230px;" placeholder="Search user, remark, ref...">
        <select name="ref_type" class="form-select form-select-sm" style="width:150px;">
          <option value="all" {{ $refType === 'all' ? 'selected' : '' }}>All Types</option>
          <option value="admin" {{ $refType === 'admin' ? 'selected' : '' }}>Admin Credit/Debit</option>
          <option value="deposit" {{ $refType === 'deposit' ? 'selected' : '' }}>Deposit</option>
          <option value="withdraw" {{ $refType === 'withdraw' ? 'selected' : '' }}>Withdrawal</option>
          <option value="casino" {{ $refType === 'casino' ? 'selected' : '' }}>Casino Settlement</option>
          <option value="bet_settle" {{ $refType === 'bet_settle' ? 'selected' : '' }}>Sports Settlement</option>
          <option value="commission" {{ $refType === 'commission' ? 'selected' : '' }}>Commission / Reward</option>
        </select>
        <button type="submit" class="btn btn-sm btn-dark fw-bold">Filter</button>
      </form>
      <div class="small fw-bold text-secondary">Total Ledger Entries: {{ $statements->total() }}</div>
    </div>

    <div class="adm-card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover table-bordered table-sm small text-center align-middle mb-0">
          <thead>
            <tr>
              <th>ID</th>
              <th>Date/Time</th>
              <th>User</th>
              <th>Credit (PTI)</th>
              <th>Debit (PTI)</th>
              <th>Balance (PTI)</th>
              <th>Remark</th>
              <th>From &rarr; To</th>
              <th>Type</th>
              <th>Edit / Delete</th>
            </tr>
          </thead>
          <tbody>
            @forelse($statements as $st)
              <tr>
                <td class="fw-bold">#{{ $st->id }}</td>
                <td>{{ $st->created_at?->format('d-m-Y H:i:s') }}</td>
                <td class="fw-bold">
                  @if($st->user)
                    <a href="{{ route('admin.users.show', $st->user_id) }}" class="text-decoration-none">{{ $st->user->username }}</a>
                  @else
                    -
                  @endif
                </td>
                <td class="fw-bold text-success">{{ $st->credit > 0 ? number_format($st->credit, 2) : '-' }}</td>
                <td class="fw-bold text-danger">{{ $st->debit > 0 ? number_format($st->debit, 2) : '-' }}</td>
                <td class="fw-bold">{{ number_format($st->balance, 2) }}</td>
                <td class="text-start">{{ $st->remark }}</td>
                <td><small class="fw-bold">{{ $st->from_to }}</small></td>
                <td><span class="badge bg-secondary">{{ strtoupper($st->ref_type) }}</span></td>
                <td>
                  <div class="d-flex justify-content-center gap-1">
                    <button type="button" class="btn btn-sm btn-info py-0 px-2 fw-bold text-dark"
                            onclick='openEditStmtModal(@json($st))'>
                      <i class="fas fa-edit"></i>
                    </button>
                    <form action="{{ route('admin.statements.delete', $st->id) }}" method="POST" class="m-0" onsubmit="return confirm('Delete statement entry #{{ $st->id }}?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-danger py-0 px-2"><i class="fas fa-trash"></i></button>
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              <tr><td colspan="10" class="text-muted py-3">No account statement records found.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="p-2 border-top">{{ $statements->links('pagination::bootstrap-5') }}</div>
  </div>

  <!-- Create Statement Modal -->
  <div class="modal fade" id="createStmtModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header py-2 bg-primary text-white">
          <h6 class="modal-title fw-bold m-0"><i class="fas fa-plus-circle me-1"></i> Create Account Statement Entry</h6>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <form action="{{ route('admin.statements.create') }}" method="POST">
          @csrf
          <div class="modal-body">
            <div class="row g-2">
              <div class="col-md-6">
                <label class="small fw-bold">Select User *</label>
                <select name="user_id" class="form-select form-select-sm" required>
                  @foreach($users as $u)
                    <option value="{{ $u->id }}">{{ $u->username }} ({{ number_format($u->balance, 2) }} PTI)</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-6">
                <label class="small fw-bold">Entry Type *</label>
                <select name="ref_type" class="form-select form-select-sm">
                  <option value="admin">Admin Adjustment</option>
                  <option value="deposit">Deposit</option>
                  <option value="withdraw">Withdrawal</option>
                  <option value="commission">Rolling Commission</option>
                  <option value="reward">Rolling Reward</option>
                  <option value="casino">Casino Settlement</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="small fw-bold text-success">Credit Amount (PTI)</label>
                <input type="number" step="0.01" name="credit" value="500" class="form-control form-control-sm fw-bold">
              </div>
              <div class="col-md-6">
                <label class="small fw-bold text-danger">Debit Amount (PTI)</label>
                <input type="number" step="0.01" name="debit" value="0" class="form-control form-control-sm fw-bold">
              </div>
              <div class="col-12">
                <label class="small fw-bold">Remark *</label>
                <input type="text" name="remark" value="Admin Manual Ledger Credit" class="form-control form-control-sm" required>
              </div>
              <div class="col-12">
                <label class="small fw-bold">From &rarr; To</label>
                <input type="text" name="from_to" placeholder="Admin -> Player" class="form-control form-control-sm">
              </div>
              <div class="col-12 mt-2">
                <div class="form-check small">
                  <input class="form-check-input" type="checkbox" name="sync_wallet" value="1" id="chkStmtSync" checked>
                  <label class="form-check-label fw-bold text-success" for="chkStmtSync">Also update User's Live PTI Wallet Balance</label>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer py-1">
            <button type="submit" class="btn btn-sm btn-primary fw-bold px-4">Create Statement</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Edit Statement Modal -->
  <div class="modal fade" id="editStmtModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header py-2 bg-info text-dark">
          <h6 class="modal-title fw-bold m-0"><i class="fas fa-edit me-1"></i> Edit Statement Entry</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form id="editStmtForm" method="POST">
          @csrf
          @method('PUT')
          <div class="modal-body">
            <div class="row g-2">
              <div class="col-md-4">
                <label class="small fw-bold text-success">Credit (PTI) *</label>
                <input type="number" step="0.01" name="credit" id="est_credit" class="form-control form-control-sm fw-bold" required>
              </div>
              <div class="col-md-4">
                <label class="small fw-bold text-danger">Debit (PTI) *</label>
                <input type="number" step="0.01" name="debit" id="est_debit" class="form-control form-control-sm fw-bold" required>
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">Balance (PTI) *</label>
                <input type="number" step="0.01" name="balance" id="est_balance" class="form-control form-control-sm fw-bold" required>
              </div>
              <div class="col-12">
                <label class="small fw-bold">Remark *</label>
                <input type="text" name="remark" id="est_remark" class="form-control form-control-sm" required>
              </div>
              <div class="col-md-6">
                <label class="small fw-bold">From &rarr; To *</label>
                <input type="text" name="from_to" id="est_from_to" class="form-control form-control-sm" required>
              </div>
              <div class="col-md-6">
                <label class="small fw-bold">Ref Type *</label>
                <input type="text" name="ref_type" id="est_ref_type" class="form-control form-control-sm" required>
              </div>
            </div>
          </div>
          <div class="modal-footer py-1">
            <button type="submit" class="btn btn-sm btn-primary fw-bold px-4">Update Statement</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    function openEditStmtModal(st) {
      document.getElementById('editStmtForm').action = `/admin/statements/${st.id}`;
      document.getElementById('est_credit').value = st.credit ?? 0;
      document.getElementById('est_debit').value = st.debit ?? 0;
      document.getElementById('est_balance').value = st.balance ?? 0;
      document.getElementById('est_remark').value = st.remark || '';
      document.getElementById('est_from_to').value = st.from_to || '';
      document.getElementById('est_ref_type').value = st.ref_type || 'system';
      new bootstrap.Modal(document.getElementById('editStmtModal')).show();
    }
  </script>
@endsection

