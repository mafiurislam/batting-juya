@extends('layouts.admin')
@section('title', 'Rolling Commission & Refer & Earn - Admin Panel')

@section('content')
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
      <h5 class="fw-bolder m-0"><i class="fas fa-percentage text-warning me-1"></i> Rolling Commission, Refer &amp; Earn &amp; Rewards (Full CRUD)</h5>
      <small class="text-muted">Edit each user's Fancy, Matka, Casino, Binary, Sportbook &amp; Bookmaker commission rates, referral codes, and credit Rolling Rewards.</small>
    </div>
    <button class="btn btn-sm btn-success fw-bold px-3 py-2" data-bs-toggle="modal" data-bs-target="#payoutCommModal">
      <i class="fas fa-gift me-1"></i> Credit Commission / Referral / Rolling Reward
    </button>
  </div>

  <div class="adm-card mb-4">
    <div class="adm-card-header">
      <form method="GET" action="{{ route('admin.commissions') }}" class="d-flex gap-2 m-0">
        <input type="text" name="q" value="{{ $q }}" class="form-control form-control-sm" style="width:240px;" placeholder="Search username or referral code...">
        <button type="submit" class="btn btn-sm btn-dark fw-bold">Search</button>
      </form>
      <div class="small fw-bold text-secondary">Inline Edit Rolling Commission % &amp; Referral Settings</div>
    </div>

    <div class="adm-card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover table-bordered table-sm small text-center align-middle mb-0">
          <thead>
            <tr>
              <th>User</th>
              <th>Referral Code</th>
              <th>Referred By</th>
              <th>Bonus PTI</th>
              <th>Fancy %</th>
              <th>Matka %</th>
              <th>Casino %</th>
              <th>Binary %</th>
              <th>Sportbook %</th>
              <th>Bookmaker %</th>
              <th>Save</th>
            </tr>
          </thead>
          <tbody>
            @foreach($users as $u)
              @php $rc = $u->rolling_commission ?? []; @endphp
              <tr>
                <form action="{{ route('admin.commissions.update', $u->id) }}" method="POST">
                  @csrf
                  @method('PUT')
                  <td class="fw-bold text-start ps-2">
                    <a href="{{ route('admin.users.show', $u->id) }}" class="text-decoration-none">{{ $u->username }}</a>
                  </td>
                  <td>
                    <input type="text" name="referral_code" value="{{ $u->referral_code }}" class="form-control form-control-sm text-center fw-bold" style="width:110px;">
                  </td>
                  <td>
                    <input type="text" name="referred_by" value="{{ $u->referred_by }}" class="form-control form-control-sm text-center" style="width:100px;" placeholder="-">
                  </td>
                  <td>
                    <input type="number" step="0.01" name="bonus_balance" value="{{ $u->bonus_balance }}" class="form-control form-control-sm text-center fw-bold text-success" style="width:90px;">
                  </td>
                  @foreach(['fancy', 'matka', 'casino', 'binary', 'sportbook', 'bookmaker'] as $rk)
                    <td>
                      <input type="number" step="0.1" name="{{ $rk }}" value="{{ $rc[$rk] ?? 0 }}" class="form-control form-control-sm text-center" style="width:70px;">
                    </td>
                  @endforeach
                  <td>
                    <button type="submit" class="btn btn-sm btn-primary py-0 px-2 fw-bold">Update</button>
                  </td>
                </form>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    <div class="p-2 border-top">{{ $users->links('pagination::bootstrap-5') }}</div>
  </div>

  <!-- Recent Commission / Referral / Reward Payouts -->
  <div class="adm-card">
    <div class="adm-card-header">
      <h6 class="fw-bold m-0"><i class="fas fa-award text-success me-1"></i> Recent Commission, Refer &amp; Earn &amp; Rolling Reward Payouts</h6>
    </div>
    <div class="adm-card-body p-0">
      <div class="table-responsive">
        <table class="table table-sm table-bordered small text-center align-middle mb-0">
          <thead>
            <tr>
              <th>Date</th>
              <th>User</th>
              <th>Type</th>
              <th>Amount Credited (PTI)</th>
              <th>New Balance</th>
              <th>Remark</th>
            </tr>
          </thead>
          <tbody>
            @forelse($rewardStatements as $rs)
              <tr>
                <td>{{ $rs->created_at?->format('d-m-Y H:i') }}</td>
                <td class="fw-bold">{{ $rs->user?->username ?? '-' }}</td>
                <td><span class="badge bg-success">{{ strtoupper($rs->ref_type) }}</span></td>
                <td class="fw-bold text-success">+{{ number_format($rs->credit, 2) }} PTI</td>
                <td class="fw-bold">{{ number_format($rs->balance, 2) }} PTI</td>
                <td>{{ $rs->remark }}</td>
              </tr>
            @empty
              <tr><td colspan="6" class="text-muted py-3">No commission or referral payouts recorded yet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Payout Commission / Reward Modal -->
  <div class="modal fade" id="payoutCommModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header py-2 bg-success text-white">
          <h6 class="modal-title fw-bold m-0"><i class="fas fa-gift me-1"></i> Credit Rolling Commission / Referral Bonus / Rolling Reward</h6>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <form action="{{ route('admin.commissions.payout') }}" method="POST">
          @csrf
          <div class="modal-body">
            <div class="mb-2">
              <label class="small fw-bold">Select User *</label>
              <select name="user_id" class="form-select form-select-sm" required>
                @foreach($users as $u)
                  <option value="{{ $u->id }}">{{ $u->username }} (Bal: {{ number_format($u->balance, 2) }} PTI)</option>
                @endforeach
              </select>
            </div>
            <div class="mb-2">
              <label class="small fw-bold">Payout Category *</label>
              <select name="payout_type" class="form-select form-select-sm fw-bold">
                <option value="commission">Rolling Commission Payout</option>
                <option value="referral">Refer &amp; Earn Bonus</option>
                <option value="reward">Banking Rolling Reward</option>
              </select>
            </div>
            <div class="mb-2">
              <label class="small fw-bold">Amount (PTI) *</label>
              <input type="number" step="0.01" name="amount" value="500" class="form-control form-control-sm fw-bold" required>
            </div>
            <div class="mb-2">
              <label class="small fw-bold">Remark</label>
              <input type="text" name="remark" value="Rolling Commission & Referral Bonus Credited" class="form-control form-control-sm">
            </div>
          </div>
          <div class="modal-footer py-1">
            <button type="submit" class="btn btn-sm btn-success fw-bold px-4">Credit Bonus PTI</button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection

