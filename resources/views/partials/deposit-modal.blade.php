@php
  $depositUpi = \App\Models\SiteSetting::getValue('deposit_upi_id', 's7484819820530667@slc');
  $depositAccountName = \App\Models\SiteSetting::getValue('deposit_account_name', 'Slice UPI (s7484819820530667@slc)');
  $depositBankName = \App\Models\SiteSetting::getValue('deposit_bank_name', 'HDFC Bank');
  $depositAccountNumber = \App\Models\SiteSetting::getValue('deposit_account_number', '50200089412580');
  $depositIfsc = \App\Models\SiteSetting::getValue('deposit_ifsc', 'HDFC0001245');
  $minDeposit = (int) \App\Models\SiteSetting::getValue('min_deposit', '100');
  $customQrImage = \App\Models\SiteSetting::getValue('deposit_qr_image', '/assets/brand/deposit-qr.jpg');
  $userDepositHistory = auth()->check()
      ? \App\Models\BankingTransaction::where('user_id', auth()->id())->where('type', 'deposit')->latest()->take(10)->get()
      : collect();
@endphp

<!-- Deposit Modal (#DepositModal - Matches 1:1 with User Specification & Image) -->
<div class="modal fade" id="DepositModal" tabindex="-1" aria-labelledby="depositModalLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 520px;">
    <div class="modal-content" style="border:none;border-radius:8px;overflow:hidden;box-shadow:0 12px 36px rgba(0,0,0,0.35);">
      
      <!-- Modal Top Header -->
      <div class="modal-header py-2 px-3 text-white" style="background:linear-gradient(135deg, #1b2838 0%, #243a48 100%);border-bottom:1px solid #334e68;">
        <h6 class="modal-title fw-bold m-0 d-flex align-items-center gap-2" id="depositModalLabel" style="font-size:14.5px;">
          <i class="fas fa-wallet text-warning"></i> Deposit
        </h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" onclick="resetDepositFlow()"></button>
      </div>

      <div class="modal-body p-3" style="background:#f4f6f9;">
        <form action="{{ route('banking.deposit') }}" method="POST" enctype="multipart/form-data" id="depositForm" onsubmit="return handleDepositSubmit(event)">
          @csrf
          <input type="hidden" name="method" value="UPI">

          <!-- ================= STEP 1: AMOUNT SELECTION (Exact match to provided screenshot) ================= -->
          <div id="depositStep1">
            <div class="wb-deposit-alert-box text-center" style="background-color:#d4edda;border:1px solid #c3e6cb;border-radius:4px;padding:22px 24px;">
              <div class="mb-2">
                <input type="number" 
                       name="amount" 
                       id="depositAmountInput" 
                       class="form-control" 
                       placeholder="Deposit Amount*" 
                       min="{{ $minDeposit }}" 
                       step="any"
                       required 
                       style="height:38px;font-size:14px;background:#ffffff;border:1px solid #ced4da;border-radius:4px;box-shadow:none;">
              </div>

              <!-- Minimum Deposit Message (Matching exact screenshot: bold green, left-aligned) -->
              <div class="fw-bold mb-3 text-start" style="font-size:13.5px;color:#0d542b;line-height:1.4;">
                Minimum deposit Amount is {{ $minDeposit }}.
              </div>

              <div id="step1ValidationAlert" class="alert alert-danger py-1 px-2 small fw-bold d-none text-start my-2"></div>

              <!-- Deposit Button (Matching exact screenshot: centered, dark slate, white text) -->
              <div class="text-center pt-1">
                <button type="button" 
                        class="wb-deposit-btn" 
                        id="btnDepositStep1"
                        onclick="proceedToDepositStep2()" 
                        style="background:linear-gradient(-180deg, #2E4B5E 0%, #243A48 82%);color:#fff !important;border:1px solid #1d2f3a;border-radius:4px;padding:6px 24px;font-size:13.5px;font-weight:700;cursor:pointer;">
                  Deposit
                </button>
              </div>
            </div>
          </div>

          <!-- ================= STEP 2: QR CODE BOX, UPI ID & SCREENSHOT UPLOAD ================= -->
          <div id="depositStep2" class="d-none">
            
            <!-- Amount Summary & Back Button -->
            <div class="d-flex justify-content-between align-items-center bg-white p-2 px-3 border rounded mb-3 shadow-sm">
              <div>
                <span class="text-muted small">Deposit Amount:</span>
                <span class="fw-bolder fs-5 text-success ms-1">₹<span id="displayDepositAmountText">0</span></span>
              </div>
              <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size:11.5px;" onclick="backToDepositStep1()">
                <i class="fas fa-edit me-1"></i> Change Amount
              </button>
            </div>

            <!-- QR Box Card -->
            <div class="bg-white border rounded p-3 mb-3 text-center shadow-sm">
              <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                <span class="badge bg-success-subtle text-success border border-success px-2 py-1" style="font-size:11px;">
                  <i class="fas fa-qrcode me-1"></i> Scan to Pay
                </span>
                <span class="small text-muted fw-bold">Instant UPI QR</span>
              </div>

              <!-- QR Code Image (Custom Uploaded QR or Dynamic UPI QR) -->
              <div class="p-2 d-inline-block bg-white border rounded shadow-sm text-center" style="max-width:270px;width:100%;">
                <img id="dynamicDepositQrImg" 
                     src="{{ !empty($customQrImage) ? $customQrImage : '/assets/brand/deposit-qr.jpg' }}" 
                     alt="UPI QR Code" 
                     data-custom-qr="{{ !empty($customQrImage) ? $customQrImage : '/assets/brand/deposit-qr.jpg' }}"
                     style="max-width:250px;width:100%;height:auto;max-height:330px;display:block;margin:0 auto;object-fit:contain;border-radius:6px;">
                <div class="mt-2 d-flex justify-content-center gap-2">
                  <a href="{{ !empty($customQrImage) ? $customQrImage : '/assets/brand/deposit-qr.jpg' }}" 
                     target="_blank" 
                     download="slice-deposit-qr.jpg" 
                     class="btn btn-sm btn-outline-primary py-1 px-2" 
                     style="font-size:11px;font-weight:600;">
                    <i class="fas fa-download me-1"></i> Download QR Image
                  </a>
                </div>
              </div>

              <div class="small text-muted mt-2">
                Scan with Google Pay, PhonePe, Paytm, BHIM, Slice or any UPI app
              </div>
            </div>

            <!-- Exact UPI ID Box (Matches user screenshot 1:1) -->
            <div class="bg-white border rounded p-3 mb-3 shadow-sm" style="border-color:#dee2e6 !important;">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="small fw-bold text-muted text-uppercase" style="letter-spacing:0.5px;font-size:12.5px;">UPI ID</span>
                <button type="button" 
                        class="btn btn-sm btn-outline-dark d-inline-flex align-items-center gap-1 py-1 px-3" 
                        id="btnCopyDepositUpi"
                        onclick="copyDepositUpi('{{ $depositUpi }}')"
                        style="font-size:13px;font-weight:600;border:1.5px solid #212529;border-radius:4px;background:#ffffff;color:#212529;">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1">
                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                  </svg>
                  Copy
                </button>
              </div>

              <div class="fw-bolder mb-2 text-dark" id="displayUpiIdText" style="font-size:18px;letter-spacing:-0.2px;">
                {{ $depositUpi }}
              </div>

              <hr class="my-2" style="border-top:1px solid #e9ecef;opacity:1;">

              <div class="small" style="font-size:13px;color:#212529;">
                <span class="fw-bold">A/C Name:</span> <span class="text-uppercase fw-semibold text-secondary">{{ $depositAccountName }}</span>
              </div>
            </div>

            <!-- Upload Payment Screenshot Section (Below QR Box) -->
            <div class="bg-white border rounded p-3 mb-3 shadow-sm">
              <div class="mb-3">
                <label class="form-label fw-bold text-dark d-flex justify-content-between align-items-center mb-1" style="font-size:13px;">
                  <span><i class="fas fa-camera text-primary me-1"></i> Upload Payment Screenshot <span class="text-danger">*</span></span>
                  <span class="text-muted fw-normal" style="font-size:11px;">(JPG, PNG, WEBP)</span>
                </label>
                <input type="file" 
                       name="receipt" 
                       id="depositReceiptFileInput" 
                       class="form-control" 
                       accept="image/*,.pdf" 
                       required 
                       onchange="handleDepositScreenshotSelected(this)" 
                       style="font-size:12.5px;">

                <!-- Screenshot Preview Container -->
                <div id="depositScreenshotPreviewWrap" class="d-none mt-2 p-2 border rounded bg-light text-center">
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small text-success fw-bold"><i class="fas fa-check-circle me-1"></i> Screenshot Attached</span>
                    <button type="button" class="btn btn-sm btn-link text-danger p-0 text-decoration-none" onclick="removeDepositScreenshot()">Remove</button>
                  </div>
                  <img id="depositScreenshotPreviewImg" src="" alt="Payment Receipt" style="max-height:160px;max-width:100%;border-radius:4px;box-shadow:0 2px 6px rgba(0,0,0,0.15);">
                </div>
              </div>

              <!-- Optional UTR Input Field -->
              <div class="mb-1">
                <label class="form-label fw-bold text-dark mb-1" style="font-size:12.5px;">
                  <i class="fas fa-receipt text-secondary me-1"></i> UTR / Transaction Ref No. <span class="text-muted fw-normal" style="font-size:11px;">(Optional)</span>
                </label>
                <input type="text" 
                       name="utr_number" 
                       id="depositUtrField" 
                       class="form-control form-control-sm" 
                       placeholder="e.g. 412345678901 (from payment app)" 
                       maxlength="30" 
                       style="font-size:12px;">
              </div>
            </div>

            <!-- Submit Button (Below Upload Screenshot) -->
            <div class="d-grid gap-2">
              <button type="submit" 
                      id="btnSubmitDepositForm" 
                      class="btn text-white fw-bold py-2 shadow-sm" 
                      style="background:linear-gradient(135deg, #16a34a 0%, #15803d 100%);font-size:14px;border-radius:4px;">
                <i class="fas fa-check-circle me-1"></i> Submit
              </button>
            </div>

            <div class="text-center text-muted mt-2" style="font-size:11px;">
              <i class="fas fa-info-circle text-info me-1"></i> After submission, admin will verify your screenshot and credit PTI balance to your wallet.
            </div>

          </div>

        </form>

        <!-- Deposit History Table (1:1 winbook24.com below Deposit card) -->
        <div class="mt-3 bg-white border rounded overflow-hidden shadow-sm">
          <div class="px-3 py-2 fw-bold text-dark border-bottom d-flex justify-content-between align-items-center" style="background:#e9ecef;font-size:12.5px;">
            <span>Deposit History</span>
            <span class="badge bg-secondary">{{ $userDepositHistory->count() }}</span>
          </div>
          <div class="table-responsive" style="max-height:210px;overflow-y:auto;">
            <table class="table table-bordered table-sm mb-0 text-center align-middle" style="font-size:12px;">
              <thead class="table-light">
                <tr>
                  <th>Amount</th>
                  <th>UTR</th>
                  <th>Note</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                @forelse($userDepositHistory as $dep)
                  <tr>
                    <td class="fw-bold">{{ number_format($dep->amount, 0, '.', '') }}</td>
                    <td>{{ $dep->utr_number ?: $dep->tx_ref }}</td>
                    <td class="small text-muted">{{ \Illuminate\Support\Str::limit($dep->admin_note ?: '-', 24) }}</td>
                    <td>
                      <span class="badge {{ $dep->status === 'APPROVED' ? 'bg-success' : ($dep->status === 'REJECTED' ? 'bg-danger' : 'bg-warning text-dark') }}">
                        {{ $dep->status }}
                      </span>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="4" class="text-muted py-2">No deposit records yet</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<script>
  window.setDepositChipAmount = function (chipVal) {
    const inp = document.getElementById('depositAmountInput');
    if (inp) {
      inp.value = chipVal;
      const err = document.getElementById('step1ValidationAlert');
      if (err) err.classList.add('d-none');
    }
  };

  window.proceedToDepositStep2 = function () {
    const inp = document.getElementById('depositAmountInput');
    const err = document.getElementById('step1ValidationAlert');
    const minVal = {{ $minDeposit }};
    const val = parseFloat(inp ? inp.value : 0);

    if (isNaN(val) || val < minVal) {
      if (err) {
        err.textContent = 'Minimum deposit Amount is ' + minVal + '.';
        err.classList.remove('d-none');
      }
      if (inp) inp.focus();
      return;
    }

    if (err) err.classList.add('d-none');

    // Update Amount in Step 2
    const dispEl = document.getElementById('displayDepositAmountText');
    if (dispEl) {
      dispEl.textContent = val.toLocaleString('en-IN', { minimumFractionDigits: 2 });
    }

    // Build dynamic UPI QR or show custom QR
    const qrImg = document.getElementById('dynamicDepositQrImg');
    if (qrImg) {
      const customQr = qrImg.getAttribute('data-custom-qr');
      if (customQr && customQr.trim() !== '') {
        qrImg.src = customQr;
      } else {
        const upiId = '{{ $depositUpi }}';
        const upiUri = 'upi://pay?pa=' + encodeURIComponent(upiId) + '&pn=CRICEXPERTS&am=' + val.toFixed(2) + '&cu=INR&tn=Deposit';
        qrImg.src = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&margin=8&data=' + encodeURIComponent(upiUri);
      }
    }

    // Switch views
    document.getElementById('depositStep1')?.classList.add('d-none');
    document.getElementById('depositStep2')?.classList.remove('d-none');
  };

  window.backToDepositStep1 = function () {
    document.getElementById('depositStep2')?.classList.add('d-none');
    document.getElementById('depositStep1')?.classList.remove('d-none');
    document.getElementById('depositAmountInput')?.focus();
  };

  window.copyDepositUpi = function (upi) {
    if (!navigator.clipboard) {
      const ta = document.createElement('textarea');
      ta.value = upi;
      document.body.appendChild(ta);
      ta.select();
      document.execCommand('copy');
      document.body.removeChild(ta);
      showCopiedFeedback();
      return;
    }
    navigator.clipboard.writeText(upi).then(function () {
      showCopiedFeedback();
    }).catch(function () {
      alert('UPI ID: ' + upi);
    });

    function showCopiedFeedback() {
      const btn = document.getElementById('btnCopyDepositUpi');
      if (btn) {
        const orig = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check text-success"></i> <span class="text-success">Copied!</span>';
        setTimeout(function () {
          btn.innerHTML = orig;
        }, 2000);
      }
    }
  };

  window.handleDepositScreenshotSelected = function (input) {
    if (input.files && input.files[0]) {
      const reader = new FileReader();
      reader.onload = function (e) {
        const container = document.getElementById('depositScreenshotPreviewWrap');
        const img = document.getElementById('depositScreenshotPreviewImg');
        if (img && container) {
          img.src = e.target.result;
          container.classList.remove('d-none');
        }
      };
      reader.readAsDataURL(input.files[0]);
    }
  };

  window.removeDepositScreenshot = function () {
    const input = document.getElementById('depositReceiptFileInput');
    const container = document.getElementById('depositScreenshotPreviewWrap');
    const img = document.getElementById('depositScreenshotPreviewImg');
    if (input) input.value = '';
    if (img) img.src = '';
    if (container) container.classList.add('d-none');
  };

  window.resetDepositFlow = function () {
    backToDepositStep1();
    removeDepositScreenshot();
  };

  window.handleDepositSubmit = function (e) {
    const fileInp = document.getElementById('depositReceiptFileInput');
    if (!fileInp || !fileInp.files || fileInp.files.length === 0) {
      alert('Please upload your payment screenshot before clicking Submit.');
      if (fileInp) fileInp.focus();
      e.preventDefault();
      return false;
    }
    const btn = document.getElementById('btnSubmitDepositForm');
    if (btn) {
      btn.disabled = true;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Submitting...';
    }
    return true;
  };
</script>
