<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Reset Password - winbook24</title>
  <link rel="icon" type="image/x-icon" href="/favicon.ico">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
  <link href="/css/winbook24.css" rel="stylesheet">
</head>
<body>
  <div class="wb-auth-screen">
    <a href="{{ route('home') }}" class="wb-auth-close" title="Close">
      <i class="fas fa-times"></i>
    </a>

    <div class="wb-auth-card">
      <div class="wb-auth-logo">
        <a href="{{ route('home') }}">
          <img src="/assets/brand/logo.png?v=2" alt="cricexperts.com">
        </a>
      </div>
      <div class="wb-auth-heading">Reset Password</div>

      <div id="fpAlert" class="alert d-none py-1 px-2 small mb-3 fw-bold"></div>

      <div role="region" aria-label="Reset Password">
        <div class="wb-auth-field">
          <input type="text" id="fpUsername" name="fpUsername" placeholder="Username" autocomplete="off" autocapitalize="none" spellcheck="false" data-lpignore="true" readonly onfocus="this.removeAttribute('readonly')" onmousedown="this.removeAttribute('readonly')" required>
        </div>

        <div class="wb-auth-field">
          <div class="flag-prefix">
            <img src="/assets/flags/flag.png" alt="flag">
            <span>+91</span>
          </div>
          <input type="text" id="fpPhone" name="fpPhone" inputmode="numeric" placeholder="12345 67890" autocomplete="off" data-lpignore="true" readonly onfocus="this.removeAttribute('readonly')" onmousedown="this.removeAttribute('readonly')" required>
        </div>

        <button type="button" id="btnFpOtp" class="wb-auth-btn" onclick="sendFpOtp()">
          Generate OTP
        </button>

        <div id="fpOtpSection" class="d-none mt-3 text-start">
          <div class="text-white fw-bold text-center mb-2 small">ENTER OTP</div>
          <div id="fpSandboxHint" class="alert alert-warning py-1 px-2 mb-2 text-center small d-none"></div>

          <div class="wb-otp-row">
            @for($i = 0; $i < 6; $i++)
              <input type="text" maxlength="1" inputmode="numeric" class="wb-otp-box js-fp-digit" autocomplete="off">
            @endfor
          </div>

          <label class="text-white fw-bold small mb-1">New Password</label>
          <div class="wb-auth-field mb-3">
            <input type="password" id="fpPassword" name="fpPassword" placeholder="**********" autocomplete="new-password" data-lpignore="true" readonly onfocus="this.removeAttribute('readonly')" onmousedown="this.removeAttribute('readonly')" required>
          </div>

          <button type="button" class="wb-auth-btn" onclick="verifyFpReset()">
            SUBMIT <i class="fas fa-sign-in-alt ms-1"></i>
          </button>
        </div>
      </div>
    </div>
  </div>

  <script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function showFpAlert(msg, type = 'danger') {
      const el = document.getElementById('fpAlert');
      el.className = `alert alert-${type} py-1 px-2 small mb-3 fw-bold`;
      el.textContent = msg;
      el.classList.remove('d-none');
    }

    async function sendFpOtp() {
      const username = document.getElementById('fpUsername').value.trim();
      const phone = document.getElementById('fpPhone').value.trim();
      if (!username || !phone) return showFpAlert('Enter Username and Phone number.');

      const res = await fetch('{{ route("auth.forgot.otp") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ username, phone, country_code: '+91' }),
      });
      const data = await res.json();
      if (!res.ok || !data.status) return showFpAlert(data.message || 'Error sending OTP');

      showFpAlert(data.message, 'success');
      document.getElementById('fpOtpSection').classList.remove('d-none');
      if (data.sandbox_otp) {
        const h = document.getElementById('fpSandboxHint');
        h.innerHTML = `MSG91 Reset OTP: <strong>${data.sandbox_otp}</strong>`;
        h.classList.remove('d-none');
        const boxes = document.querySelectorAll('.js-fp-digit');
        String(data.sandbox_otp).split('').forEach((d, i) => { if (boxes[i]) boxes[i].value = d; });
      }
    }

    async function verifyFpReset() {
      const username = document.getElementById('fpUsername').value.trim();
      const phone = document.getElementById('fpPhone').value.trim();
      const password = document.getElementById('fpPassword').value;
      const otp = Array.from(document.querySelectorAll('.js-fp-digit')).map(i => i.value).join('');

      const res = await fetch('{{ route("auth.forgot.verify") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ username, phone, otp, password }),
      });
      const data = await res.json();
      if (!res.ok || !data.status) return showFpAlert(data.message || 'Verification failed');

      showFpAlert(data.message, 'success');
      setTimeout(() => (window.location.href = data.redirect || '/home'), 600);
    }
  </script>
</body>
</html>

