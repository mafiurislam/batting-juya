<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>winbook24</title>
  <link rel="icon" type="image/x-icon" href="/favicon.ico">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
  <link href="/css/winbook24.css" rel="stylesheet">
</head>
<body>
  <div class="main-Box-login wb-auth-screen">
    <a href="{{ route('home') }}" class="crossIcon wb-auth-close" title="Close">
      <i class="fas fa-times"></i>
    </a>

    <div class="centered-container wb-auth-card">
      <div class="loginlogo wb-auth-logo">
        <a href="{{ route('home') }}">
          <img src="/assets/brand/logo.png?v=2" alt="cricexperts.com">
        </a>
      </div>

      @if($errors->any())
        <div class="alert alert-danger py-1 px-2 small mt-2 mb-0 fw-bold">
          {{ $errors->first() }}
        </div>
      @endif

      <div class="mt-4">
        <form id="portalLoginForm" action="{{ route('auth.login') }}" method="POST" novalidate onsubmit="return submitPortalLogin(event)">
          @csrf
          <!-- Hidden decoy inputs to block browser password manager autofill -->
          <input type="text" name="prevent_autofill_user" tabindex="-1" autocomplete="username" style="position:absolute;opacity:0;height:0;width:0;pointer-events:none;">
          <input type="password" name="prevent_autofill_pass" tabindex="-1" autocomplete="current-password" style="position:absolute;opacity:0;height:0;width:0;pointer-events:none;">

          <div class="mb-4">
            <div class="d-flex align-items-center phoneNumberDiv wb-auth-field mb-0">
              <input type="text" tabindex="1" required name="username" id="userName" value="" placeholder="Username" class="form-control phoneNumberInput" maxlength="30" autocomplete="off" autocapitalize="none" spellcheck="false" data-lpignore="true" data-form-type="other" readonly onfocus="this.removeAttribute('readonly')" onmousedown="this.removeAttribute('readonly')">
              <button type="button" class="btn btn-outline-secondary showPasswordbtn" tabindex="-1">
                <i class="fa fa-user inputicon fa-lg user-icon"></i>
              </button>
            </div>
            <span id="errUserName" class="error-message d-none"><div>Please enter username.</div></span>
          </div>

          <div class="mb-4">
            <div class="d-flex align-items-center phoneNumberDiv wb-auth-field mb-0">
              <input type="password" tabindex="2" required minlength="6" id="loginPassword" name="password" value="" placeholder="Password" class="form-control phoneNumberInput" autocomplete="new-password" data-lpignore="true" data-form-type="other" readonly onfocus="this.removeAttribute('readonly')" onmousedown="this.removeAttribute('readonly')">
              <button type="button" class="btn btn-outline-secondary showPasswordbtn" onclick="toggleLoginPw()">
                <i id="loginPwIcon" class="fa inputicon fa-eye-slash"></i>
              </button>
            </div>
            <span id="errPassword" class="error-message d-none"><div>Please enter password.</div></span>
          </div>

          <div>
            <button type="submit" class="btn-send ui-link loginBtn wb-auth-btn">Login</button>
            <button type="button" class="btn-send ui-link loginBtn wb-auth-btn mt-1" onclick="document.getElementById('demoLoginForm').submit()">Login with Demo ID</button>
            <button type="button" class="btn-send ui-link loginBtn wb-auth-btn mt-1" style="background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;border:none;" onclick="document.getElementById('adminDemoLoginForm').submit()">
              <i class="fas fa-crown me-1"></i> Open Admin Dashboard
            </button>
          </div>

          <div class="text-center mt-4">
            <a href="{{ route('forgot.password') }}" class="forget-pass">Forgot Password?</a>
          </div>
        </form>

        <form id="demoLoginForm" action="{{ route('auth.demo') }}" method="POST" class="d-none">
          @csrf
        </form>
        <form id="adminDemoLoginForm" action="{{ route('auth.admin.demo') }}" method="POST" class="d-none">
          @csrf
        </form>
      </div>
    </div>
  </div>

  <script>
    (function() {
      const uEl = document.getElementById('userName');
      const pEl = document.getElementById('loginPassword');
      [uEl, pEl].forEach(el => {
        if (!el) return;
        el.value = '';
        el.addEventListener('input', () => {
          el.dataset.userTyped = '1';
          if (el === uEl) document.getElementById('errUserName')?.classList.add('d-none');
          if (el === pEl) document.getElementById('errPassword')?.classList.add('d-none');
        });
        setTimeout(() => {
          if (!el.dataset.userTyped) el.value = '';
          el.removeAttribute('readonly');
        }, 60);
      });
    })();

    function toggleLoginPw() {
      const inp = document.getElementById('loginPassword');
      const ic = document.getElementById('loginPwIcon');
      if (inp.type === 'password') {
        inp.type = 'text';
        ic.className = 'fa inputicon fa-eye';
      } else {
        inp.type = 'password';
        ic.className = 'fa inputicon fa-eye-slash';
      }
    }

    function showPortalToast(message) {
      let container = document.getElementById('wbToastContainer');
      if (!container) {
        container = document.createElement('div');
        container.id = 'wbToastContainer';
        container.style.cssText = 'position:fixed;top:14px;left:50%;transform:translateX(-50%);z-index:9999;min-width:290px;max-width:440px;';
        document.body.appendChild(container);
      }
      const alert = document.createElement('div');
      alert.style.cssText = 'background:#c62828;color:#fff;padding:9px 16px;border-radius:5px;margin-bottom:6px;box-shadow:0 4px 12px rgba(0,0,0,0.4);font-weight:700;font-size:12px;display:flex;justify-content:space-between;align-items:center;';
      alert.innerHTML = `<span>${message}</span><span style="cursor:pointer;margin-left:10px;" onclick="this.parentElement.remove()">×</span>`;
      container.appendChild(alert);
      setTimeout(() => alert.remove(), 3800);
    }

    async function submitPortalLogin(event) {
      if (event) event.preventDefault();
      const uEl = document.getElementById('userName');
      const pEl = document.getElementById('loginPassword');
      const u = (uEl?.value || '').trim();
      const p = pEl?.value || '';

      let valid = true;
      if (!u) {
        document.getElementById('errUserName')?.classList.remove('d-none');
        valid = false;
      } else {
        document.getElementById('errUserName')?.classList.add('d-none');
      }
      if (!p) {
        document.getElementById('errPassword')?.classList.remove('d-none');
        valid = false;
      } else {
        document.getElementById('errPassword')?.classList.add('d-none');
      }
      if (!valid) return false;

      try {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const res = await fetch('/auth/login', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf,
          },
          body: JSON.stringify({ username: u, password: p }),
        });
        const data = await res.json();
        if (!res.ok || !data.status) {
          showPortalToast(data.message || 'Invalid Login Details.');
          return false;
        }
        window.location.href = data.redirect || '/home';
      } catch (e) {
        document.getElementById('portalLoginForm')?.submit();
      }
      return false;
    }
  </script>
</body>
</html>
