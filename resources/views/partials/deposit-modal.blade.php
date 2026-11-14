@php
  $depositUpi = \App\Models\SiteSetting::getValue('deposit_upi_id', 'winbook24.pay@axis');
  $depositAccountName = \App\Models\SiteSetting::getValue('deposit_account_name', 'WINBOOK24 ENTERPRISES');
  $depositBankName = \App\Models\SiteSetting::getValue('deposit_bank_name', 'HDFC Bank');
  $depositAccountNumber = \App\Models\SiteSetting::getValue('deposit_account_number', '50200089412580');
  $depositIfsc = \App\Models\SiteSetting::getValue('deposit_ifsc', 'HDFC0001245');
  $minDeposit = (int) \App\Models\SiteSetting::getValue('min_deposit', '100');
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

              <!-- Quick Selection Chips -->
              <div class="d-flex flex-wrap justify-content-start gap-1 mb-2">
                @foreach([100, 500, 1000, 2000, 5000, 10000] as $chip)
                  <button type="button" 
                          class="btn btn-sm py-0 px-2 fw-bold text-dark" 
                          style="background:#ffffff;border:1px solid #b7dfbf;border-radius:12px;font-size:11px;"
                          onclick="setDepositChipAmount({{ $chip }})">+{{ $chip }}</button>
                @endforeach
              </div>

              <!-- Minimum Deposit Message (Matching exact screenshot: bold green, left-aligned) -->
              <div class="fw-bold mb-2 text-start" style="font-size:13.5px;color:#0d542b;line-height:1.4;">
                Minimum deposit Amount is {{ $minDeposit }}.
              </div>

              <div id="step1ValidationAlert" class="alert alert-danger py-1 px-2 small fw-bold d-none text-start my-2"></div>

              <!-- Deposit Button (Matching exact screenshot: centered, dark slate, white text) -->
              <div class="text-center pt-2">
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

              <!-- Dynamic UPI QR Code Image -->
              <div class="p-2 d-inline-block bg-white border rounded" style="box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                <img id="dynamicDepositQrImg" 
                     src="" 
                     alt="UPI QR Code" 
                     style="width:185px;height:185px;display:block;margin:0 auto;object-fit:contain;">
              </div>

              <div class="small text-muted mt-2">
                Scan with Google Pay, PhonePe, Paytm, BHIM or any UPI app
              </div>
            </div>

            <!-- UPI ID Section (Below QR Box) -->
            <div class="bg-white border rounded p-2 px-3 mb-3 shadow-sm">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <div class="text-muted" style="font-size:10.5px;font-weight:700;text-transform:uppercase;">UPI ID</div>
                  <div class="fw-bolder text-dark" style="font-size:14px;letter-spacing:0.2px;" id="valDepositUpiId">{{ $depositUpi }}</div>
                </div>
                <button type="button" 
                        class="btn btn-sm btn-outline-dark fw-bold d-inline-flex align-items-center gap-1" 
                        id="btnCopyDepositUpi" 
                        onclick="copyDepositUpi('{{ $depositUpi }}')">
                  <i class="fas fa-copy"></i> <span>Copy</span>
                </button>
              </div>
              @if(!empty($depositAccountName))
                <div class="mt-1 pt-1 border-top" style="font-size:11.5px;color:#555;">
                  <strong>A/C Name:</strong> {{ $depositAccountName }}
                </div>
              @endif
            </div>

            <!-- Upload Payment Screenshot Section (Below UPI ID) -->
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

    // Build dynamic UPI QR code URL
    const upiId = '{{ $depositUpi }}';
    const upiUri = 'upi://pay?pa=' + encodeURIComponent(upiId) + '&pn=WINBOOK24&am=' + val.toFixed(2) + '&cu=INR&tn=Deposit';
    const qrImg = document.getElementById('dynamicDepositQrImg');
    if (qrImg) {
      qrImg.src = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&margin=8&data=' + encodeURIComponent(upiUri);
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
