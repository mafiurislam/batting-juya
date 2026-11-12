@extends('layouts.admin')
@section('title', 'Banking: Deposits, Withdrawals & Rewards - Admin Panel')

@section('content')
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
      <h5 class="fw-bolder m-0"><i class="fas fa-wallet text-warning me-1"></i> Banking: Deposits, Withdrawals &amp; Rolling Rewards (Full CRUD)</h5>
      <small class="text-muted">Approve/Reject pending requests, view uploaded UTR payment receipts, or create/edit/delete deposit, withdrawal &amp; reward records.</small>
    </div>
    <button class="btn btn-sm btn-primary fw-bold px-3 py-2" data-bs-toggle="modal" data-bs-target="#createTxModal">
      <i class="fas fa-plus-circle me-1"></i> Create Deposit / Withdraw / Reward
    </button>
  </div>

  <!-- Summary KPI Row -->
  <div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
      <div class="adm-stat-card adm-grad-green">
        <div class="stat-icon"><i class="fas fa-arrow-down"></i></div>
        <div class="stat-label">Approved Deposits</div>
        <div class="stat-value">{{ number_format($summary['approved_deposits'] ?? 0, 2) }} PTI</div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="adm-stat-card adm-grad-blue">
        <div class="stat-icon"><i class="fas fa-arrow-up"></i></div>
        <div class="stat-label">Approved Withdrawals</div>
        <div class="stat-value">{{ number_format($summary['approved_withdrawals'] ?? 0, 2) }} PTI</div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="adm-stat-card adm-grad-orange">
        <div class="stat-icon"><i class="fas fa-clock"></i></div>
        <div class="stat-label">Pending Approval</div>
        <div class="stat-value">{{ $summary['pending_count'] ?? 0 }} Requests</div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="adm-stat-card adm-grad-purple">
        <div class="stat-icon"><i class="fas fa-gift"></i></div>
        <div class="stat-label">Rolling Rewards Paid</div>
        <div class="stat-value">{{ number_format($summary['rewards_paid'] ?? 0, 2) }} PTI</div>
      </div>
    </div>
  </div>

  <div class="adm-card">
    <div class="adm-card-header">
      <form method="GET" action="{{ route('admin.banking') }}" class="d-flex flex-wrap gap-2 align-items-center m-0">
        <input type="text" name="q" value="{{ $q ?? '' }}" class="form-control form-control-sm" style="width:210px;" placeholder="Search Ref, UTR, Username...">
        <select name="type" class="form-select form-select-sm" style="width:130px;">
          <option value="all" {{ ($type ?? 'all') === 'all' ? 'selected' : '' }}>All Types</option>
          <option value="deposit" {{ ($type ?? '') === 'deposit' ? 'selected' : '' }}>Deposits</option>
          <option value="withdraw" {{ ($type ?? '') === 'withdraw' ? 'selected' : '' }}>Withdrawals</option>
          <option value="reward" {{ ($type ?? '') === 'reward' ? 'selected' : '' }}>Rewards</option>
        </select>
        <select name="status" class="form-select form-select-sm" style="width:135px;">
          <option value="all" {{ ($status ?? 'all') === 'all' ? 'selected' : '' }}>All Status</option>
          <option value="PENDING" {{ ($status ?? '') === 'PENDING' ? 'selected' : '' }}>PENDING</option>
          <option value="APPROVED" {{ ($status ?? '') === 'APPROVED' ? 'selected' : '' }}>APPROVED</option>
          <option value="REJECTED" {{ ($status ?? '') === 'REJECTED' ? 'selected' : '' }}>REJECTED</option>
        </select>
        <button type="submit" class="btn btn-sm btn-dark fw-bold"><i class="fas fa-filter me-1"></i> Filter</button>
      </form>
      <a href="{{ route('admin.bank.accounts') }}" class="btn btn-sm btn-outline-primary fw-bold">
        <i class="fas fa-university me-1"></i> Manage User Saved Banks
      </a>
    </div>

    <div class="adm-card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover table-bordered table-sm small text-center align-middle mb-0">
          <thead>
            <tr>
              <th>Ref</th>
              <th>Date</th>
              <th>User</th>
              <th>Type</th>
              <th>Method</th>
              <th>Amount (PTI)</th>
              <th>UTR / Bank Info</th>
              <th>Receipt</th>
              <th>Status</th>
              <th>Approve / Edit / Delete</th>
            </tr>
          </thead>
          <tbody>
            @forelse($transactions as $tx)
              <tr>
                <td class="fw-bold">{{ $tx->tx_ref }}</td>
                <td>{{ $tx->created_at?->format('d-m-Y H:i') }}</td>
                <td class="fw-bold">
                  @if($tx->user)
                    <a href="{{ route('admin.users.show', $tx->user_id) }}" class="text-decoration-none">{{ $tx->user->username }}</a>
                  @else
                    -
                  @endif
                </td>
                <td>
                  <span class="badge {{ $tx->type === 'deposit' ? 'bg-success' : ($tx->type === 'withdraw' ? 'bg-primary' : 'bg-info text-dark') }}">
                    {{ strtoupper($tx->type) }}
                  </span>
                </td>
                <td class="fw-bold">{{ $tx->method }}</td>
                <td class="fw-bold">{{ number_format($tx->final_amount, 2) }}</td>
                <td>
                  @if($tx->type === 'deposit')
                    UTR: <strong>{{ $tx->utr_number ?? '-' }}</strong>
                  @else
                    {{ $tx->bank_details_json['bank_name'] ?? $tx->utr_number ?? '-' }}
                    {{ !empty($tx->bank_details_json['account_number']) ? ' (' . $tx->bank_details_json['account_number'] . ')' : '' }}
                  @endif
                </td>
                <td>
                  @if($tx->receipt_url)
                    <a href="{{ $tx->receipt_url }}" target="_blank" class="btn btn-sm btn-outline-info py-0 px-2">Receipt</a>
                  @else
                    -
                  @endif
                </td>
                <td>
                  <span class="badge {{ $tx->status === 'APPROVED' ? 'bg-success' : ($tx->status === 'REJECTED' ? 'bg-danger' : 'bg-warning text-dark') }}">
                    {{ $tx->status }}
                  </span>
                </td>
                <td>
                  <div class="d-flex flex-wrap justify-content-center gap-1">
                    @if($tx->status === 'PENDING')
                      <form action="{{ route('admin.banking.process', $tx->id) }}" method="POST" class="m-0">
                        @csrf
                        <input type="hidden" name="action" value="approve">
                        <button type="submit" class="btn btn-sm btn-success py-0 px-2 fw-bold">Approve</button>
                      </form>
                      <form action="{{ route('admin.banking.process', $tx->id) }}" method="POST" class="m-0">
                        @csrf
                        <input type="hidden" name="action" value="reject">
                        <button type="submit" class="btn btn-sm btn-danger py-0 px-2 fw-bold">Reject</button>
                      </form>
                    @endif
                    <button type="button" class="btn btn-sm btn-info py-0 px-2 fw-bold text-dark"
                            onclick='openEditTxModal(@json($tx))'>
                      <i class="fas fa-edit"></i>
                    </button>
                    <form action="{{ route('admin.banking.delete', $tx->id) }}" method="POST" class="m-0" onsubmit="return confirm('Delete transaction {{ $tx->tx_ref }}?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2"><i class="fas fa-trash"></i></button>
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              <tr><td colspan="10" class="text-muted py-3">No banking transactions recorded yet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="p-2 border-top">{{ $transactions->links('pagination::bootstrap-5') }}</div>
  </div>

  <!-- Create Transaction Modal -->
  <div class="modal fade" id="createTxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header py-2 bg-primary text-white">
          <h6 class="modal-title fw-bold m-0"><i class="fas fa-plus-circle me-1"></i> Create Deposit / Withdrawal / Reward</h6>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <form action="{{ route('admin.banking.create') }}" method="POST">
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
                <label class="small fw-bold">Transaction Type *</label>
                <select name="type" class="form-select form-select-sm fw-bold">
                  <option value="deposit">Deposit</option>
                  <option value="withdraw">Withdrawal</option>
                  <option value="reward">Rolling Reward</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="small fw-bold">Payment Method *</label>
                <select name="method" class="form-select form-select-sm">
                  <option value="UPI">UPI</option>
                  <option value="BANK">Bank Transfer / IMPS</option>
                  <option value="QR">QR Pay</option>
                  <option value="BKASH">bKash</option>
                  <option value="NAGAD">Nagad</option>
                  <option value="USDT">USDT</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="small fw-bold">Amount (PTI) *</label>
                <input type="number" step="0.01" name="amount" value="1000" class="form-control form-control-sm fw-bold" required>
              </div>
              <div class="col-md-6">
                <label class="small fw-bold">UTR / Reference Number</label>
                <input type="text" name="utr_number" class="form-control form-control-sm" placeholder="Auto-generated if blank">
              </div>
              <div class="col-md-6">
                <label class="small fw-bold">Status *</label>
                <select name="status" class="form-select form-select-sm fw-bold">
                  <option value="APPROVED">APPROVED (Instant)</option>
                  <option value="PENDING">PENDING</option>
                  <option value="REJECTED">REJECTED</option>
                </select>
              </div>
              <div class="col-12">
                <label class="small fw-bold">Admin Note</label>
                <input type="text" name="admin_note" value="Verified & Processed by Admin" class="form-control form-control-sm">
              </div>
              <div class="col-12 mt-2">
                <div class="form-check small">
                  <input class="form-check-input" type="checkbox" name="adjust_wallet" value="1" id="chkSyncWallet" checked>
                  <label class="form-check-label fw-bold text-success" for="chkSyncWallet">Automatically update User PTI Wallet &amp; Account Statement</label>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer py-1">
            <button type="submit" class="btn btn-sm btn-primary fw-bold px-4">Create Transaction</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Edit Transaction Modal -->
  <div class="modal fade" id="editTxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header py-2 bg-info text-dark">
          <h6 class="modal-title fw-bold m-0"><i class="fas fa-edit me-1"></i> Edit Banking Transaction</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form id="editTxForm" method="POST">
          @csrf
          @method('PUT')
          <div class="modal-body">
            <div class="row g-2">
              <div class="col-md-6">
                <label class="small fw-bold">Type *</label>
                <select name="type" id="etx_type" class="form-select form-select-sm">
                  <option value="deposit">Deposit</option>
                  <option value="withdraw">Withdrawal</option>
                  <option value="reward">Rolling Reward</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="small fw-bold">Method *</label>
                <input type="text" name="method" id="etx_method" class="form-control form-control-sm" required>
              </div>
              <div class="col-md-6">
                <label class="small fw-bold">Amount (PTI) *</label>
                <input type="number" step="0.01" name="amount" id="etx_amount" class="form-control form-control-sm fw-bold" required>
              </div>
              <div class="col-md-6">
                <label class="small fw-bold">Status *</label>
                <select name="status" id="etx_status" class="form-select form-select-sm fw-bold">
                  <option value="PENDING">PENDING</option>
                  <option value="APPROVED">APPROVED</option>
                  <option value="REJECTED">REJECTED</option>
                </select>
              </div>
              <div class="col-12">
                <label class="small fw-bold">UTR Number</label>
                <input type="text" name="utr_number" id="etx_utr" class="form-control form-control-sm">
              </div>
              <div class="col-12">
                <label class="small fw-bold">Admin Note</label>
                <input type="text" name="admin_note" id="etx_note" class="form-control form-control-sm">
              </div>
            </div>
          </div>
          <div class="modal-footer py-1">
            <button type="submit" class="btn btn-sm btn-primary fw-bold px-4">Update Transaction</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    function openEditTxModal(tx) {
      document.getElementById('editTxForm').action = `/admin/banking/${tx.id}`;
      document.getElementById('etx_type').value = tx.type || 'deposit';
      document.getElementById('etx_method').value = tx.method || 'UPI';
      document.getElementById('etx_amount').value = tx.amount ?? 0;
      document.getElementById('etx_status').value = tx.status || 'PENDING';
      document.getElementById('etx_utr').value = tx.utr_number || '';
      document.getElementById('etx_note').value = tx.admin_note || '';
      new bootstrap.Modal(document.getElementById('editTxModal')).show();
    }
  </script>
@endsection
