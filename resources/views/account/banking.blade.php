@extends('layouts.app')
@section('title', 'Banking - winbook24')

@section('content')
<style>
  .wb-banking-hub-card {
    background: #fff;
    border: 1px solid #c8ced3;
    border-radius: 4px;
    padding: 20px;
    margin-top: 6px;
  }
  .wb-brand-card {
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fff;
    border: 1px solid #f0f0f0;
    border-radius: 6px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.14);
    height: 112px;
    text-decoration: none;
    color: #000;
    cursor: pointer;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
  }
  .wb-brand-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
    color: #000;
  }
  .wb-brand-card h2 {
    margin: 0;
    font-size: 26px;
    font-weight: 800;
    color: #000;
  }
  /* Deposit Modal (05a_deposit_modal.png) */
  .wb-deposit-alert-box {
    background-color: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
    border-radius: 4px;
    padding: 20px 24px;
    text-align: center;
  }
  .wb-deposit-btn {
    background: linear-gradient(-180deg, #2E4B5E 0%, #243A48 82%);
    color: #fff !important;
    border: 1px solid #1d2f3a;
    border-radius: 4px;
    padding: 6px 18px;
    font-size: 13px;
    font-weight: 600;
    margin-top: 12px;
    cursor: pointer;
  }
  /* Withdraw 24*7 (05_withdraw.png) */
  .wb-add-bank-btn {
    background: transparent;
    color: #ccc;
    border: 1px solid #6c757d;
    border-radius: 3px;
    padding: 2px 10px;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
  }
  .wb-add-bank-btn:hover {
    background: rgba(255,255,255,0.1);
    color: #fff;
  }
  .wb-withdraw-coins-btn {
    background: linear-gradient(-180deg, #2E4B5E 0%, #243A48 82%);
    color: #fff !important;
    border: 1px solid #1d2f3a;
    border-radius: 4px;
    padding: 6px 16px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
  }
  /* Withdrawal Details (05b_withdrawdetails.png) */
  .wb-wd-bar {
    background: #141414;
    color: #fff;
    border-radius: 8px;
    padding: 12px 18px;
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
  }
  .wb-wd-bar-left {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 14px;
    font-weight: 500;
  }
  .wb-wd-icon-circle {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 14px;
  }
  .wb-wd-addnew-btn {
    background: #f5b800;
    color: #000;
    border: none;
    border-radius: 4px;
    padding: 6px 26px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
  }
  /* Reward (05_reward.png) */
  .wb-gold-circle {
    width: 96px;
    height: 96px;
    border-radius: 50%;
    background: radial-gradient(circle at 35% 35%, #fff9e6 0%, #f3d79b 60%, #d9a74a 100%);
    border: 2px solid #c9933b;
    box-shadow: 0 3px 8px rgba(0,0,0,0.18), inset 0 0 8px rgba(255,255,255,0.8);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    font-weight: 700;
    color: #111;
    margin: 0 12px;
  }
  .wb-settle-btn {
    background: #4dbd74;
    color: #fff;
    border: none;
    border-radius: 4px;
    padding: 5px 14px;
    font-size: 13px;
    cursor: pointer;
  }
</style>

@if(($section ?? 'hub') === 'withdraw')
  <!-- ================= WITHDRAW 24*7 VIEW (/banking/withdraw - 05_withdraw.png) ================= -->
  <div class="wb-acct-card">
    <div class="wb-acct-head">
      <span>Withdraw 24*7</span>
      <a href="{{ route('banking.withdraw.details') }}" class="wb-add-bank-btn">Add Bank Account</a>
    </div>
    <div class="p-3">
      <form action="{{ route('banking.withdraw') }}" method="POST">
        @csrf
        <div class="row g-2 mb-3">
          <div class="col-md-4">
            <label class="form-label mb-1" style="font-size:13px;">Withdrawal Amount <span class="text-danger">*</span></label>
            <input type="number" name="amount" class="form-control" style="height:35px;border-color:#c8ced3;" required min="100">
          </div>
          @if($bankAccounts->count() > 0)
            <div class="col-md-4">
              <label class="form-label mb-1" style="font-size:13px;">Select Bank / Wallet</label>
              <select name="bank_account_id" class="form-select" style="height:35px;font-size:13px;border-color:#c8ced3;">
                @foreach($bankAccounts as $ba)
                  <option value="{{ $ba->id }}">{{ $ba->bank_name }} - {{ $ba->account_number }} ({{ $ba->holder_name }})</option>
                @endforeach
              </select>
            </div>
          @endif
        </div>
        <div class="mb-3">
          <button type="submit" class="wb-withdraw-coins-btn">Withdraw Coins</button>
        </div>
      </form>

      <div class="table-responsive">
        <table class="wb-dt-table mb-0">
          <thead>
            <tr>
              <th>Amount</th>
              <th>Withdrawal Type</th>
              <th>Request Time</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            @forelse($withdrawals as $w)
              <tr>
                <td class="fw-bold">{{ number_format($w->amount, 2) }}</td>
                <td>{{ $w->method ?: 'IMPS/BANK' }}</td>
                <td>{{ $w->created_at->format('M j, Y, g:i:s A') }}</td>
                <td>
                  <span class="badge {{ $w->status === 'APPROVED' ? 'bg-success' : ($w->status === 'REJECTED' ? 'bg-danger' : 'bg-warning text-dark') }}">
                    {{ $w->status }}
                  </span>
                </td>
                <td>-</td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="text-center py-2 text-muted">No withdrawal requests found</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

@elseif(($section ?? 'hub') === 'withdrawdetails')
  <!-- ================= WITHDRAWAL DETAILS VIEW (/banking/withdrawdetails - 05b_withdrawdetails.png) ================= -->
  <div class="wb-acct-card">
    <div class="wb-acct-head">
      <span>Withdrawal Details</span>
      <a href="{{ route('banking.withdraw.page') }}" class="wb-add-bank-btn">Back to Withdraw</a>
    </div>
    <div class="p-3">
      <!-- 1. Bank Details -->
      <div class="wb-wd-bar" onclick="document.getElementById('wdBankList').classList.toggle('d-none')">
        <div class="wb-wd-bar-left">
          <span class="wb-wd-icon-circle" style="background:#f59e0b;color:#fff;"><i class="fas fa-university"></i></span>
          <span>Bank Details</span>
        </div>
        <div class="d-flex align-items-center gap-2">
          <button type="button" class="wb-wd-addnew-btn py-1 px-3" onclick="event.stopPropagation();" data-bs-toggle="modal" data-bs-target="#addBankModal">Add New</button>
          <span class="wb-wd-icon-circle" style="width:24px;height:24px;background:#ccc;color:#111;font-size:12px;">
            <i class="fas fa-chevron-right"></i>
          </span>
        </div>
      </div>

      <div id="wdBankList" class="mb-3 {{ $bankAccounts->count() > 0 ? '' : 'd-none' }}">
        @foreach($bankAccounts as $ba)
          <div class="border rounded p-2 mb-2 bg-light d-flex justify-content-between align-items-center">
            <div style="font-size:12px;">
              <div><strong>Bank Name:</strong> {{ $ba->bank_name }}</div>
              <div><strong>Account No:</strong> {{ $ba->account_number }}</div>
              <div><strong>IFSC Code:</strong> {{ $ba->ifsc_code }}</div>
              <div><strong>Account Holder Name:</strong> {{ $ba->holder_name }}</div>
            </div>
            <form action="{{ route('banking.bank.delete', $ba->id) }}" method="POST">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-warning btn-sm fw-bold">Delete</button>
            </form>
          </div>
        @endforeach
      </div>

      <!-- 2. Paytm Wallet -->
      <div class="wb-wd-bar">
        <div class="wb-wd-bar-left">
          <span class="wb-wd-icon-circle" style="background:#fff;color:#002970;font-size:10px;font-weight:900;">Paytm</span>
          <span>Paytm Wallet</span>
        </div>
        <button type="button" class="wb-wd-addnew-btn" data-bs-toggle="modal" data-bs-target="#addPaytmModal">Add New</button>
      </div>

      <!-- 3. Google Pay -->
      <div class="wb-wd-bar">
        <div class="wb-wd-bar-left">
          <span class="wb-wd-icon-circle" style="background:#fff;color:#4285F4;font-size:10px;font-weight:900;">G Pay</span>
          <span>Google Pay</span>
        </div>
        <button type="button" class="wb-wd-addnew-btn" data-bs-toggle="modal" data-bs-target="#addGpayModal">Add New</button>
      </div>

      <!-- 4. PhonePe -->
      <div class="wb-wd-bar mb-0">
        <div class="wb-wd-bar-left">
          <span class="wb-wd-icon-circle" style="background:#5f259f;color:#fff;font-size:14px;">पे</span>
          <span>PhonePe</span>
        </div>
        <button type="button" class="wb-wd-addnew-btn" data-bs-toggle="modal" data-bs-target="#addPhonepeModal">Add New</button>
      </div>
    </div>
  </div>

  <!-- Add Bank Account Modal -->
  <div class="modal fade" id="addBankModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="wb-acct-head">
          <span>Add Your Bank Account</span>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <form action="{{ route('banking.bank.add') }}" method="POST">
          @csrf
          <div class="modal-body">
            <div class="mb-2 row align-items-center">
              <label class="col-4 fw-bold">Bank Name <span class="text-danger">*</span></label>
              <div class="col-8"><input type="text" name="bank_name" class="form-control form-control-sm" placeholder="Bank Name.." required></div>
            </div>
            <div class="mb-2 row align-items-center">
              <label class="col-4 fw-bold">Account Number <span class="text-danger">*</span></label>
              <div class="col-8"><input type="text" name="account_number" class="form-control form-control-sm" placeholder="Account Number.." required></div>
            </div>
            <div class="mb-2 row align-items-center">
              <label class="col-4 fw-bold">IFSC <span class="text-danger">*</span></label>
              <div class="col-8"><input type="text" name="ifsc_code" class="form-control form-control-sm text-uppercase" placeholder="IFSC.." required></div>
            </div>
            <div class="mb-2 row align-items-center">
              <label class="col-4 fw-bold">Holder Name <span class="text-danger">*</span></label>
              <div class="col-8"><input type="text" name="holder_name" class="form-control form-control-sm" placeholder="Account Holder Name.." required></div>
            </div>
          </div>
          <div class="modal-footer py-2">
            <button type="submit" class="btn btn-sm text-white fw-bold" style="background:#243A48;">Submit</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Add Paytm / GPay / PhonePe Modals -->
  @foreach([
    ['id' => 'addPaytmModal', 'title' => 'Add New Paytm Wallet Number', 'bank' => 'Paytm Wallet', 'label' => 'Paytm Number'],
    ['id' => 'addGpayModal', 'title' => 'Add New Google Pay Number', 'bank' => 'Google Pay', 'label' => 'G Pay Number'],
    ['id' => 'addPhonepeModal', 'title' => 'Add New PhonePe Number', 'bank' => 'PhonePe', 'label' => 'PhonePe Number'],
  ] as $wm)
    <div class="modal fade" id="{{ $wm['id'] }}" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="wb-acct-head">
            <span>{{ $wm['title'] }}</span>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <form action="{{ route('banking.bank.add') }}" method="POST">
            @csrf
            <input type="hidden" name="bank_name" value="{{ $wm['bank'] }}">
            <input type="hidden" name="ifsc_code" value="WALLET">
            <div class="modal-body">
              <div class="mb-2 row align-items-center">
                <label class="col-4 fw-bold">Name <span class="text-danger">*</span></label>
                <div class="col-8"><input type="text" name="holder_name" class="form-control form-control-sm" placeholder="{{ $wm['bank'] }} Name.." required></div>
              </div>
              <div class="mb-2 row align-items-center">
                <label class="col-4 fw-bold">{{ $wm['label'] }} <span class="text-danger">*</span></label>
                <div class="col-8"><input type="text" name="account_number" class="form-control form-control-sm" placeholder="{{ $wm['label'] }}.." required></div>
              </div>
            </div>
            <div class="modal-footer py-2">
              <button type="submit" class="btn btn-sm text-white fw-bold" style="background:#243A48;">Submit</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  @endforeach

@elseif(($section ?? 'hub') === 'reward')
  <!-- ================= REWARD VIEW (/banking/reward - 05_reward.png) ================= -->
  <div class="wb-acct-card">
    <div class="wb-acct-head justify-content-center" style="font-size:14px;">
      <span>Player Level</span>
    </div>
    <div class="py-4 text-center">
      <div class="wb-gold-circle">None</div>
      <div class="wb-gold-circle">0%</div>
    </div>
  </div>

  <div class="wb-acct-card">
    <div class="wb-acct-head" style="font-size:14px;">
      <span>Rolling Reward</span>
    </div>
    <div class="p-3">
      <table class="wb-dt-table mb-0">
        <thead>
          <tr>
            <th>Total Turnover</th>
            <th>Total Reward</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>0</td>
            <td>0</td>
            <td>
              <button type="button" class="wb-settle-btn" onclick="alert('No pending rolling reward to settle.')">Settle</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

@else
  <!-- ================= MAIN BANKING 3-CARD HUB (/banking - 05_banking.png) ================= -->
  <div class="wb-banking-hub-card">
    <div class="row g-4">
      <div class="col-sm-6 col-lg-3">
        <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#DepositModal" class="wb-brand-card">
          <h2><b>Deposit</b></h2>
        </a>
      </div>
      <div class="col-sm-6 col-lg-3">
        <a href="{{ route('banking.withdraw.page') }}" class="wb-brand-card">
          <h2><b>Withdraw</b></h2>
        </a>
      </div>
      <div class="col-sm-6 col-lg-3">
        <a href="{{ route('banking.reward') }}" class="wb-brand-card">
          <h2><b>Reward</b></h2>
        </a>
      </div>
    </div>
  </div>

@endif
@endsection
