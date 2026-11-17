<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Sign up - CRICEXPERTS</title>
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
      <div class="wb-auth-heading">Sign up</div>

      <div id="signupAlert" class="alert d-none py-1 px-2 small mb-3 fw-bold"></div>

      <div id="signupForm" role="region" aria-label="Sign up">
        <!-- Hidden decoy inputs to block browser password manager autofill -->
        <input type="text" name="prevent_autofill_user" tabindex="-1" autocomplete="username" style="position:absolute;opacity:0;height:0;width:0;pointer-events:none;">
        <input type="password" name="prevent_autofill_pass" tabindex="-1" autocomplete="current-password" style="position:absolute;opacity:0;height:0;width:0;pointer-events:none;">

        <div class="wb-auth-field">
          <input type="text" id="suUsername" name="suUsername" placeholder="Username" autocomplete="off" autocapitalize="none" spellcheck="false" data-lpignore="true" readonly onfocus="this.removeAttribute('readonly')" onmousedown="this.removeAttribute('readonly')" required>
        </div>

        <div class="wb-auth-field">
          <input type="text" id="suPromoCode" name="promo_code" placeholder="Promo/Refer Code" autocomplete="off" data-lpignore="true">
        </div>

        <div class="wb-auth-field">
          <div class="flag-prefix" onclick="cycleCountryCode()" style="cursor:pointer;" title="Click to change country code (+91 / +880 / +971)">
            <img src="/assets/flags/flag.png" id="suFlagImg" alt="flag">
            <span id="suCountryCodeText">+91</span>
            <input type="hidden" id="suCountryCode" value="+91">
          </div>
          <input type="text" id="suPhone" name="suPhone" inputmode="numeric" placeholder="1234 567890" maxlength="15" autocomplete="off" data-lpignore="true" readonly onfocus="this.removeAttribute('readonly')" onmousedown="this.removeAttribute('readonly')" required>
        </div>

        <button type="button" id="btnGenerateOtp" class="wb-auth-btn" onclick="sendSignupOtp()">
          Generate OTP
        </button>

        <!-- Step 2: 6-Box OTP + Password (Revealed after Generate OTP) -->
        <div id="otpSection" class="d-none mt-3 text-start">
          <div class="text-white fw-bold text-center mb-2 small">ENTER 6-DIGIT MSG91 SMS OTP</div>
          <div id="sandboxOtpHint" class="alert alert-warning py-1 px-2 mb-2 text-center small d-none"></div>

          <div class="wb-otp-row">
            @for($i = 0; $i < 6; $i++)
              <input type="text" maxlength="1" inputmode="numeric" class="wb-otp-box js-otp-digit" data-idx="{{ $i }}" autocomplete="off">
            @endfor
          </div>

          <label class="text-white fw-bold small mb-1">Set Password</label>
          <div class="wb-auth-field mb-2">
            <input type="password" id="suPassword" name="suPassword" placeholder="*********" minlength="6" autocomplete="new-password" data-lpignore="true" readonly onfocus="this.removeAttribute('readonly')" onmousedown="this.removeAttribute('readonly')" required>
            <span style="cursor:pointer;" onclick="toggleSuPw()">
              <i id="suPwIcon" class="fas fa-eye text-dark ms-2"></i>
            </span>
          </div>

          <ul class="text-white-50 ps-3 mb-3" style="font-size:11px;line-height:1.4;">
            <li>Password must have 6 to 20 characters without spaces.</li>
            <li>Include letters and numbers for account security.</li>
          </ul>

          <button type="button" id="btnVerifySignup" class="wb-auth-btn" onclick="verifyAndCreateAccount()">
            SUBMIT <i class="fas fa-sign-in-alt ms-1"></i>
          </button>
        </div>
      </div>

      <form action="{{ route('auth.demo') }}" method="POST">
        @csrf
        <button type="submit" class="wb-auth-btn">Login with Demo ID</button>
      </form>

      <div class="mt-2">
        <span class="text-white-50 small">Already registered?</span>
        <a href="{{ route('login') }}" class="text-warning fw-bold small ms-1">Login</a>
      </div>
    </div>
  </div>

  <script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const codes = ['+91', '+880', '+971', '+44'];
    let codeIdx = 0;

    function cycleCountryCode() {
      codeIdx = (codeIdx + 1) % codes.length;
      document.getElementById('suCountryCode').value = codes[codeIdx];
      document.getElementById('suCountryCodeText').textContent = codes[codeIdx];
    }

    function showAlert(msg, type = 'danger') {
      const el = document.getElementById('signupAlert');
      el.className = `alert alert-${type} py-1 px-2 small mb-3 fw-bold`;
      el.textContent = msg;
      el.classList.remove('d-none');
    }

    function toggleSuPw() {
      const inp = document.getElementById('suPassword');
      const ic = document.getElementById('suPwIcon');
      inp.type = inp.type === 'password' ? 'text' : 'password';
      ic.className = inp.type === 'password' ? 'fas fa-eye text-dark ms-2' : 'fas fa-eye-slash text-dark ms-2';
    }

    // Auto-advance & paste for 6 OTP boxes
    document.querySelectorAll('.js-otp-digit').forEach((box, idx, arr) => {
      box.addEventListener('input', (e) => {
        e.target.value = e.target.value.replace(/\D/g, '');
        if (e.target.value && idx < arr.length - 1) {
          arr[idx + 1].focus();
        }
      });
      box.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace' && !box.value && idx > 0) {
          arr[idx - 1].focus();
        }
      });
      box.addEventListener('paste', (e) => {
        e.preventDefault();
        const pasted = (e.clipboardData.getData('text') || '').replace(/\D/g, '').slice(0, 6);
        pasted.split('').forEach((ch, i) => {
          if (arr[i]) arr[i].value = ch;
        });
        if (pasted.length > 0) arr[Math.min(5, pasted.length - 1)].focus();
      });
    });

    async function sendSignupOtp() {
      const username = document.getElementById('suUsername').value.trim();
      const phone = document.getElementById('suPhone').value.trim();
      const country_code = document.getElementById('suCountryCode').value;

      if (!username || username.length < 3) {
        return showAlert('Please enter a valid Username (at least 3 characters).');
      }
      if (!phone || phone.replace(/\D/g, '').length < 7) {
        return showAlert('Please enter a valid mobile number.');
      }

      const btn = document.getElementById('btnGenerateOtp');
      btn.disabled = true;
      btn.textContent = 'Sending MSG91 SMS OTP...';

      try {
        const res = await fetch('{{ route("auth.signup.otp") }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
          },
          body: JSON.stringify({ username, phone, country_code }),
        });
        const data = await res.json();
        if (!res.ok || !data.status) {
          showAlert(data.message || 'Could not send OTP.');
          btn.disabled = false;
          btn.textContent = 'Generate OTP';
          return;
        }

        showAlert(data.message, 'success');
        document.getElementById('otpSection').classList.remove('d-none');
        btn.textContent = 'Resend OTP';
        btn.disabled = false;

        if (data.sandbox_otp) {
          const hint = document.getElementById('sandboxOtpHint');
          hint.innerHTML = `<i class="fas fa-sms me-1"></i> MSG91 OTP Code: <strong class="fs-6">${data.sandbox_otp}</strong> <button type="button" class="btn btn-sm btn-dark py-0 px-2 ms-2" onclick="fillOtp('${data.sandbox_otp}')">Auto-Fill</button>`;
          hint.classList.remove('d-none');
        }
        document.querySelector('.js-otp-digit')?.focus();
      } catch (e) {
        showAlert('Network error while contacting MSG91 gateway.');
        btn.disabled = false;
        btn.textContent = 'Generate OTP';
      }
    }

    function fillOtp(code) {
      const boxes = document.querySelectorAll('.js-otp-digit');
      String(code).split('').forEach((d, i) => {
        if (boxes[i]) boxes[i].value = d;
      });
      document.getElementById('suPassword')?.focus();
    }

    async function verifyAndCreateAccount() {
      const username = document.getElementById('suUsername').value.trim();
      const phone = document.getElementById('suPhone').value.trim();
      const country_code = document.getElementById('suCountryCode').value;
      const promo_code = document.getElementById('suPromoCode').value.trim();
      const password = document.getElementById('suPassword').value;
      const otp = Array.from(document.querySelectorAll('.js-otp-digit')).map(i => i.value).join('');

      if (otp.length !== 6) {
        return showAlert('Please enter all 6 digits of the OTP code.');
      }
      if (!password || password.length < 6) {
        return showAlert('Please enter a password (minimum 6 characters).');
      }

      const btn = document.getElementById('btnVerifySignup');
      btn.disabled = true;
      btn.textContent = 'Verifying OTP & Creating Account...';

      try {
        const res = await fetch('{{ route("auth.signup.verify") }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
          },
          body: JSON.stringify({ username, phone, country_code, otp, password, promo_code }),
        });
        const data = await res.json();
        if (!res.ok || !data.status) {
          showAlert(data.message || 'Verification failed.');
          btn.disabled = false;
          btn.innerHTML = 'SUBMIT <i class="fas fa-sign-in-alt ms-1"></i>';
          return;
        }

        showAlert(data.message, 'success');
        setTimeout(() => {
          window.location.href = data.redirect || '/home';
        }, 500);
      } catch (e) {
        showAlert('Network error while verifying OTP.');
        btn.disabled = false;
        btn.innerHTML = 'SUBMIT <i class="fas fa-sign-in-alt ms-1"></i>';
      }
    }
  </script>
</body>
</html>

