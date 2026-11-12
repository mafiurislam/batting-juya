@extends('layouts.app')
@section('title', 'Two Factor Authentication - winbook24')

@section('content')
<style>
  .wb-2fa-outer {
    max-width: 455px;
    margin: 12px auto;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    padding: 18px 20px;
    text-align: center;
  }
  .wb-2fa-title {
    font-size: 20px;
    font-weight: 400;
    color: #23282c;
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
  }
  .wb-2fa-regen-btn {
    background: linear-gradient(-180deg, #2E4B5E 0%, #243A48 82%);
    color: #fff;
    border: none;
    border-radius: 5px;
    width: 100%;
    padding: 9px 14px;
    font-size: 13px;
    cursor: pointer;
  }
  .wb-2fa-expand-box {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 16px;
    margin-top: 14px;
    background: #fff;
  }
  .wb-2fa-qr-card {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 12px;
    max-width: 240px;
    margin: 10px auto 14px auto;
    background: #fff;
  }
  .wb-2fa-otp-row {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin: 10px 0 14px 0;
  }
  .wb-2fa-otp-input {
    width: 40px;
    height: 40px;
    border: 1px solid #ced4da;
    border-radius: 5px;
    text-align: center;
    font-size: 16px;
    font-weight: 700;
    color: #23282c;
  }
  .wb-2fa-verify-btn {
    background: #243A48;
    color: #fff;
    border: none;
    border-radius: 5px;
    padding: 7px 20px;
    font-size: 13px;
    cursor: pointer;
  }
</style>

@php
  $secretKey = $user->two_factor_code ?: 'PVCVIZSNMNWTSVK6MESDQJKFMM';
  if (strlen($secretKey) < 16) {
      $secretKey = 'PVCVIZSNMNWTSVK6MESDQJKFMM';
  }
@endphp

<div class="wb-2fa-outer">
  <div class="wb-2fa-title">
    <i class="fas fa-lock" style="color:#f59e0b;font-size:18px;"></i>
    <span>Two-Factor Authentication</span>
  </div>

  <button type="button" class="wb-2fa-regen-btn" onclick="document.getElementById('twoFaExpandBox').classList.remove('d-none')">
    Re-generate 2-Factor Authentication
  </button>

  <div id="twoFaExpandBox" class="wb-2fa-expand-box {{ $user->two_factor_enabled ? '' : 'd-none' }}">
    <div style="font-size:16px;color:#23282c;margin-bottom:2px;">Scan the QR Code</div>
    <div class="text-muted" style="font-size:12px;">Use an Authenticator App to complete the process</div>

    <div class="wb-2fa-qr-card">
      <img src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data={{ urlencode('otpauth://totp/winbook24:' . $user->username . '?secret=' . $secretKey . '&issuer=winbook24') }}"
           alt="2FA QR Code"
           style="width:150px;height:150px;display:block;margin:0 auto 8px auto;">
      <div class="d-flex align-items-center justify-content-center gap-1" style="font-size:11px;word-break:break-all;color:#23282c;">
        <span id="twoFaSecretText">{{ $secretKey }}</span>
        <a href="javascript:void(0)" class="text-dark ms-1" onclick="navigator.clipboard.writeText('{{ $secretKey }}')" title="Copy Secret">
          <i class="far fa-copy"></i>
        </a>
      </div>
    </div>

    <div style="font-size:13px;color:#23282c;">Enter the 6-digit code from your app:</div>

    <form action="{{ route('two.factor.toggle') }}" method="POST" id="twoFaVerifyForm">
      @csrf
      <input type="hidden" name="otp_code" id="twoFaCombinedOtp" value="123456">
      <div class="wb-2fa-otp-row">
        @for($i = 0; $i < 6; $i++)
          <input type="text" maxlength="1" class="wb-2fa-otp-input js-2fa-digit" data-idx="{{ $i }}" inputmode="numeric">
        @endfor
      </div>
      <button type="submit" class="wb-2fa-verify-btn">Verify Code</button>
    </form>
  </div>
</div>

@push('scripts')
<script>
  document.querySelectorAll('.js-2fa-digit').forEach((inp, idx, arr) => {
    inp.addEventListener('input', (e) => {
      e.target.value = e.target.value.replace(/\D/g, '').slice(0, 1);
      if (e.target.value && idx < arr.length - 1) {
        arr[idx + 1].focus();
      }
      const code = Array.from(arr).map(x => x.value).join('');
      document.getElementById('twoFaCombinedOtp').value = code || '123456';
    });
    inp.addEventListener('keydown', (e) => {
      if (e.key === 'Backspace' && !e.target.value && idx > 0) {
        arr[idx - 1].focus();
      }
    });
  });
</script>
@endpush
@endsection
