@extends('layouts.app')
@section('title', 'Refer & Earn - CRICEXPERTS')

@section('content')
<div class="wb-acct-card" style="border:1px solid #7a8a99;">
  <div class="wb-acct-head justify-content-center" style="font-size:15px;padding:9px 12px;">
    <span>Refer &amp; Earn</span>
  </div>
  <div class="text-center py-4 px-3">
    <div style="font-size:12px;color:#23282c;margin-bottom:4px;">Refer Code:</div>
    <div id="referCodeVal" style="font-size:18px;color:#23282c;margin-bottom:14px;">{{ $user->referral_code ?? 'null' }}</div>
    <button type="button"
            onclick="copyReferCode()"
            style="background:linear-gradient(-180deg, #2E4B5E 0%, #243A48 82%);color:#fff;border:none;border-radius:4px;width:100%;max-width:440px;padding:7px 16px;font-size:14px;cursor:pointer;">
      Copy to Share
    </button>
  </div>
</div>

@push('scripts')
<script>
  function copyReferCode() {
    const code = document.getElementById('referCodeVal')?.innerText || '';
    if (navigator.clipboard) {
      navigator.clipboard.writeText(code);
    }
  }
</script>
@endpush
@endsection
