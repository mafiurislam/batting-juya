@extends('layouts.admin')
@section('title', 'Withdrawal Details (Saved Bank Accounts) - Admin Panel')

@section('content')
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
      <h5 class="fw-bolder m-0"><i class="fas fa-university text-info me-1"></i> Withdrawal Details: User Saved Bank &amp; UPI Accounts (Full CRUD)</h5>
      <small class="text-muted">Manage all withdrawal bank accounts and wallet/UPI details added by users in their "Banking &rarr; Withdraw Details" section.</small>
    </div>
    <button class="btn btn-sm btn-primary fw-bold px-3 py-2" data-bs-toggle="modal" data-bs-target="#createBankModal">
      <i class="fas fa-plus-circle me-1"></i> Add User Bank Account
    </button>
  </div>

  <div class="adm-card">
    <div class="adm-card-header">
      <form method="GET" action="{{ route('admin.bank.accounts') }}" class="d-flex gap-2 m-0">
        <input type="text" name="q" value="{{ $q }}" class="form-control form-control-sm" style="width:260px;" placeholder="Search holder, bank, A/C no, IFSC, UPI...">
        <button type="submit" class="btn btn-sm btn-dark fw-bold">Search</button>
      </form>
      <div class="small fw-bold text-secondary">Total Saved Accounts: {{ $accounts->total() }}</div>
    </div>

    <div class="adm-card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover table-bordered table-sm small text-center align-middle mb-0">
          <thead>
            <tr>
              <th>ID</th>
              <th>User</th>
              <th>Account Holder Name</th>
              <th>Bank / Wallet Name</th>
              <th>Account / Phone Number</th>
              <th>IFSC Code</th>
              <th>UPI ID</th>
              <th>Default</th>
              <th>Created At</th>
              <th>Edit / Delete</th>
            </tr>
          </thead>
          <tbody>
            @forelse($accounts as $ba)
              <tr>
                <td class="fw-bold">#{{ $ba->id }}</td>
                <td class="fw-bold">
                  @if($ba->user)
                    <a href="{{ route('admin.users.show', $ba->user_id) }}" class="text-decoration-none">{{ $ba->user->username }}</a>
                  @else
                    -
                  @endif
                </td>
                <td class="fw-bold">{{ $ba->holder_name }}</td>
                <td>{{ $ba->bank_name }}</td>
                <td><code>{{ $ba->account_number }}</code></td>
                <td><span class="badge bg-dark">{{ $ba->ifsc_code }}</span></td>
                <td>{{ $ba->upi_id ?? '-' }}</td>
                <td>
                  <span class="badge {{ $ba->is_default ? 'bg-success' : 'bg-secondary' }}">{{ $ba->is_default ? 'PRIMARY' : 'NO' }}</span>
                </td>
                <td>{{ $ba->created_at?->format('d-m-Y H:i') }}</td>
                <td>
                  <div class="d-flex justify-content-center gap-1">
                    <button type="button" class="btn btn-sm btn-info py-0 px-2 fw-bold text-dark"
                            onclick='openEditBankModal(@json($ba))'>
                      <i class="fas fa-edit"></i> Edit
                    </button>
                    <form action="{{ route('admin.bank.accounts.delete', $ba->id) }}" method="POST" class="m-0" onsubmit="return confirm('Delete this bank account?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-danger py-0 px-2"><i class="fas fa-trash"></i></button>
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              <tr><td colspan="10" class="text-muted py-3">No withdrawal bank accounts found.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="p-2 border-top">{{ $accounts->links('pagination::bootstrap-5') }}</div>
  </div>

  <!-- Create Bank Account Modal -->
  <div class="modal fade" id="createBankModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header py-2 bg-primary text-white">
          <h6 class="modal-title fw-bold m-0"><i class="fas fa-university me-1"></i> Add Withdrawal Bank / Wallet Account</h6>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <form action="{{ route('admin.bank.accounts.create') }}" method="POST">
          @csrf
          <div class="modal-body">
            <div class="row g-2">
              <div class="col-12">
                <label class="small fw-bold">Select User *</label>
                <select name="user_id" class="form-select form-select-sm" required>
                  @foreach($users as $u)
                    <option value="{{ $u->id }}">{{ $u->username }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-6">
                <label class="small fw-bold">Account Holder Name *</label>
                <input type="text" name="holder_name" class="form-control form-control-sm" required>
              </div>
              <div class="col-md-6">
                <label class="small fw-bold">Bank / Wallet Name *</label>
                <input type="text" name="bank_name" placeholder="HDFC Bank / Paytm / PhonePe" class="form-control form-control-sm" required>
              </div>
              <div class="col-md-6">
                <label class="small fw-bold">Account / Mobile Number *</label>
                <input type="text" name="account_number" class="form-control form-control-sm" required>
              </div>
              <div class="col-md-6">
                <label class="small fw-bold">IFSC Code *</label>
                <input type="text" name="ifsc_code" value="HDFC0001234" class="form-control form-control-sm" required>
              </div>
              <div class="col-12">
                <label class="small fw-bold">UPI ID (Optional)</label>
                <input type="text" name="upi_id" placeholder="user@okhdfcbank" class="form-control form-control-sm">
              </div>
              <div class="col-12 mt-2">
                <div class="form-check small">
                  <input class="form-check-input" type="checkbox" name="is_default" value="1" id="chkDefaultBank" checked>
                  <label class="form-check-label fw-bold" for="chkDefaultBank">Set as Primary Withdrawal Account</label>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer py-1">
            <button type="submit" class="btn btn-sm btn-primary fw-bold px-4">Save Bank Account</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Edit Bank Account Modal -->
  <div class="modal fade" id="editBankModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header py-2 bg-info text-dark">
          <h6 class="modal-title fw-bold m-0"><i class="fas fa-edit me-1"></i> Edit Withdrawal Bank Account</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form id="editBankForm" method="POST">
          @csrf
          @method('PUT')
          <div class="modal-body">
            <div class="row g-2">
              <div class="col-md-6">
                <label class="small fw-bold">Account Holder Name *</label>
                <input type="text" name="holder_name" id="eb_holder" class="form-control form-control-sm" required>
              </div>
              <div class="col-md-6">
                <label class="small fw-bold">Bank / Wallet Name *</label>
                <input type="text" name="bank_name" id="eb_bank" class="form-control form-control-sm" required>
              </div>
              <div class="col-md-6">
                <label class="small fw-bold">Account / Mobile Number *</label>
                <input type="text" name="account_number" id="eb_acc" class="form-control form-control-sm" required>
              </div>
              <div class="col-md-6">
                <label class="small fw-bold">IFSC Code *</label>
                <input type="text" name="ifsc_code" id="eb_ifsc" class="form-control form-control-sm" required>
              </div>
              <div class="col-12">
                <label class="small fw-bold">UPI ID</label>
                <input type="text" name="upi_id" id="eb_upi" class="form-control form-control-sm">
              </div>
              <div class="col-12 mt-2">
                <div class="form-check small">
                  <input class="form-check-input" type="checkbox" name="is_default" value="1" id="eb_default">
                  <label class="form-check-label fw-bold" for="eb_default">Set as Primary Withdrawal Account</label>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer py-1">
            <button type="submit" class="btn btn-sm btn-primary fw-bold px-4">Update Bank Account</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    function openEditBankModal(ba) {
      document.getElementById('editBankForm').action = `/admin/bank-accounts/${ba.id}`;
      document.getElementById('eb_holder').value = ba.holder_name || '';
      document.getElementById('eb_bank').value = ba.bank_name || '';
      document.getElementById('eb_acc').value = ba.account_number || '';
      document.getElementById('eb_ifsc').value = ba.ifsc_code || '';
      document.getElementById('eb_upi').value = ba.upi_id || '';
      document.getElementById('eb_default').checked = !!ba.is_default;
      new bootstrap.Modal(document.getElementById('editBankModal')).show();
    }
  </script>
@endsection

