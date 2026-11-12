@extends('layouts.app')
@section('title', 'My Profile - winbook24')

@section('content')
<style>
  .wb-profile-row {
    display: flex;
    align-items: center;
    border-bottom: 1px solid #c8ced3;
    padding: 9px 14px;
    font-size: 13px;
    color: #23282c;
    background: #fff;
  }
  .wb-profile-row:last-child {
    border-bottom: none;
  }
  .wb-profile-label {
    width: 240px;
    font-weight: 700;
    color: #111;
  }
  .wb-profile-val {
    flex: 1;
    color: #23282c;
  }
  .wb-rolling-box {
    border: 1px solid #000;
    padding: 6px 12px;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    font-size: 13px;
    color: #111;
  }
  .wb-rolling-box span:first-child {
    width: 140px;
  }
  .wb-rolling-box span:last-child {
    font-weight: 700;
  }
  .wb-modal-header-dark {
    background: linear-gradient(-180deg, #2E4B5E 0%, #243A48 82%);
    color: #fff;
    padding: 8px 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  .wb-modal-header-dark h5 {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
  }
  .wb-modal-close {
    background: transparent;
    border: none;
    color: #fff;
    font-size: 20px;
    line-height: 1;
    font-weight: 700;
    cursor: pointer;
  }
  .wb-pw-field-wrap {
    position: relative;
  }
  .wb-pw-field-wrap input {
    padding-right: 34px;
    font-size: 13px;
    height: 35px;
    border: 1px solid #c8ced3;
  }
  .wb-pw-toggle {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: #23282c;
    cursor: pointer;
    font-size: 14px;
  }
</style>

<div class="wb-acct-card">
  <div class="wb-acct-head">
    <span>Account Details</span>
  </div>
  <div>
    <div class="wb-profile-row">
      <div class="wb-profile-label">Name</div>
      <div class="wb-profile-val">{{ $user->username }}</div>
    </div>
    <div class="wb-profile-row">
      <div class="wb-profile-label">Commission</div>
      <div class="wb-profile-val">0.2</div>
    </div>
    <div class="wb-profile-row">
      <div class="wb-profile-label">Rolling Commission</div>
      <div class="wb-profile-val">
        <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#rollingModal" class="text-dark" title="View Rolling Commission">
          <i class="fas fa-eye"></i>
        </a>
      </div>
    </div>
    <div class="wb-profile-row">
      <div class="wb-profile-label">Exposure Limit</div>
      <div class="wb-profile-val">{{ (int) ($user->exposure_limit ?: 5000000) }}</div>
    </div>
    <div class="wb-profile-row">
      <div class="wb-profile-label">Password</div>
      <div class="wb-profile-val">
        <span>*********</span>
        <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#changePasswordModal" class="text-dark ms-4" style="text-decoration:none;">
          Edit <i class="far fa-edit ms-1"></i>
        </a>
      </div>
    </div>
  </div>
</div>

<!-- Rolling Commission Modal (01a_myprofile_rolling_modal.png) -->
<div class="modal fade" id="rollingModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content" style="border-radius:4px;overflow:hidden;">
      <div class="wb-modal-header-dark">
        <h5>Rolling Commission</h5>
        <button type="button" class="wb-modal-close" data-bs-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body p-3">
        <div class="wb-rolling-box"><span>Fancy</span><span>0.5</span></div>
        <div class="wb-rolling-box"><span>Matka</span><span></span></div>
        <div class="wb-rolling-box"><span>Lottery</span><span>0.5</span></div>
        <div class="wb-rolling-box"><span>Casino</span><span>0</span></div>
        <div class="wb-rolling-box"><span>Binary</span><span></span></div>
        <div class="wb-rolling-box"><span>Virtual</span><span></span></div>
        <div class="wb-rolling-box"><span>Sportbook</span><span>0</span></div>
        <div class="wb-rolling-box mb-0"><span>Bookmaker</span><span>0.2</span></div>
      </div>
    </div>
  </div>
</div>

<!-- Change Password Modal (01b_myprofile_password_modal.png) -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content" style="border-radius:4px;overflow:hidden;">
      <div class="wb-modal-header-dark">
        <h5>Change Password</h5>
        <button type="button" class="wb-modal-close" data-bs-dismiss="modal">&times;</button>
      </div>
      <form action="{{ route('myprofile.password') }}" method="POST">
        @csrf
        <div class="modal-body p-3">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label mb-1" style="font-size:13px;">Old Passwords <span class="text-danger">*</span></label>
              <div class="wb-pw-field-wrap">
                <input type="password" name="current_password" id="mpOldPassword" class="form-control" required>
                <i class="fas fa-eye-slash wb-pw-toggle" onclick="toggleMpPassword('mpOldPassword', this)"></i>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label mb-1" style="font-size:13px;">New Password <span class="text-danger">*</span></label>
              <div class="wb-pw-field-wrap">
                <input type="password" name="new_password" id="mpNewPassword" class="form-control" required minlength="6">
                <i class="fas fa-eye-slash wb-pw-toggle" onclick="toggleMpPassword('mpNewPassword', this)"></i>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label mb-1" style="font-size:13px;">Confirm Password <span class="text-danger">*</span></label>
              <div class="wb-pw-field-wrap">
                <input type="password" name="new_password_confirmation" id="mpConfirmPassword" class="form-control" required minlength="6">
                <i class="fas fa-eye-slash wb-pw-toggle" onclick="toggleMpPassword('mpConfirmPassword', this)"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer py-2 px-3" style="border-top:1px solid #dee2e6;">
          <button type="submit" class="btn btn-sm px-3 py-1 fw-bold" style="background:#5c707d;color:#fff;border-radius:4px;">Confirm</button>
          <button type="button" class="btn btn-sm px-3 py-1" style="background:#ced4da;color:#000;border-radius:4px;" data-bs-dismiss="modal">No</button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
  function toggleMpPassword(inputId, icon) {
    const el = document.getElementById(inputId);
    if (!el) return;
    if (el.type === 'password') {
      el.type = 'text';
      icon.classList.remove('fa-eye-slash');
      icon.classList.add('fa-eye');
    } else {
      el.type = 'password';
      icon.classList.remove('fa-eye');
      icon.classList.add('fa-eye-slash');
    }
  }
</script>
@endpush
@endsection
