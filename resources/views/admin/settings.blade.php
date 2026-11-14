@extends('layouts.admin')
@section('title', 'Website, Payment & Banner Settings - Admin Panel')

@section('content')
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
      <h5 class="fw-bolder m-0"><i class="fas fa-sliders-h text-teal me-1" style="color:#0d9488;"></i> Website, Payment Gateway, Banners &amp; MSG91 Settings</h5>
      <small class="text-muted">Customize the entire website A-to-Z: Brand Logo, Site Title, News Marquee, Banking Payment Details, Hero Slider Banners &amp; SMS OTP.</small>
    </div>
  </div>

  <form action="{{ route('admin.settings.save') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="row g-3 mb-4">
      <!-- 1. General Website & Banking Payment Setup -->
      <div class="col-lg-6">
        <div class="adm-card h-100">
          <div class="adm-card-header">
            <h6 class="fw-bold text-primary m-0"><i class="fas fa-globe me-1"></i> Website Brand &amp; Banking Payment Details</h6>
          </div>
          <div class="adm-card-body">
            <div class="row g-2 mb-2">
              <div class="col-md-6">
                <label class="small fw-bold">Exchange Domain / Brand Name</label>
                <input type="text" name="exchange_name" value="{{ $settings['exchange_name'] }}" class="form-control form-control-sm fw-bold">
              </div>
              <div class="col-md-6">
                <label class="small fw-bold">Browser Page Title</label>
                <input type="text" name="exchange_title" value="{{ $settings['exchange_title'] }}" class="form-control form-control-sm">
              </div>
            </div>

            <div class="mb-2">
              <label class="small fw-bold">Top News Marquee Announcement</label>
              <input type="text" name="news_marquee" value="{{ $settings['news_marquee'] }}" class="form-control form-control-sm">
            </div>

            <div class="row g-2 mb-2">
              <div class="col-md-6">
                <label class="small fw-bold">WhatsApp Customer Support</label>
                <input type="text" name="support_whatsapp" value="{{ $settings['support_whatsapp'] }}" class="form-control form-control-sm">
              </div>
              <div class="col-md-6">
                <label class="small fw-bold">Signup Welcome Bonus (PTI)</label>
                <input type="number" step="10" name="welcome_bonus_pti" value="{{ $settings['welcome_bonus_pti'] }}" class="form-control form-control-sm fw-bold text-success">
              </div>
            </div>

            <hr class="my-2">
            <div class="small fw-bold text-success mb-1"><i class="fas fa-university me-1"></i> Frontend "Banking &rarr; Deposit" Payment Details</div>
            <div class="row g-2 mb-2">
              <div class="col-md-6">
                <label class="small fw-bold">Deposit UPI ID</label>
                <input type="text" name="deposit_upi_id" value="{{ $settings['deposit_upi_id'] }}" class="form-control form-control-sm fw-bold">
              </div>
              <div class="col-md-6">
                <label class="small fw-bold">Deposit Account Holder Name</label>
                <input type="text" name="deposit_account_name" value="{{ $settings['deposit_account_name'] }}" class="form-control form-control-sm">
              </div>
              <div class="col-md-6">
                <label class="small fw-bold">Deposit Bank Name</label>
                <input type="text" name="deposit_bank_name" value="{{ $settings['deposit_bank_name'] }}" class="form-control form-control-sm">
              </div>
              <div class="col-md-6">
                <label class="small fw-bold">Deposit Account Number</label>
                <input type="text" name="deposit_account_number" value="{{ $settings['deposit_account_number'] }}" class="form-control form-control-sm">
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">Deposit IFSC Code</label>
                <input type="text" name="deposit_ifsc" value="{{ $settings['deposit_ifsc'] }}" class="form-control form-control-sm">
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">Min Deposit (PTI)</label>
                <input type="number" name="min_deposit" value="{{ $settings['min_deposit'] }}" class="form-control form-control-sm">
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">Min Withdraw (PTI)</label>
                <input type="number" name="min_withdraw" value="{{ $settings['min_withdraw'] }}" class="form-control form-control-sm">
              </div>
            </div>

            <div class="row g-2 mb-2 p-2 border rounded bg-light align-items-center">
              <div class="col-md-8">
                <label class="small fw-bold text-dark d-flex align-items-center gap-1 mb-1">
                  <i class="fas fa-qrcode text-primary"></i> Upload / Exchange Deposit QR Code Image
                </label>
                <input type="file" name="deposit_qr_image" class="form-control form-control-sm" accept="image/*">
                <small class="text-muted" style="font-size:11px;">Upload custom QR code (PNG, JPG, WEBP). Overrides dynamic QR.</small>
              </div>
              <div class="col-md-4 text-center">
                @if(!empty($settings['deposit_qr_image']))
                  <div class="d-flex flex-column align-items-center gap-1">
                    <img src="{{ $settings['deposit_qr_image'] }}" alt="Custom QR" style="width:55px;height:55px;object-fit:contain;border:1px solid #ccc;border-radius:4px;background:#fff;">
                    <button type="submit" form="delQrForm" class="btn btn-sm btn-outline-danger py-0 px-2 fw-bold" style="font-size:10.5px;">Remove QR</button>
                  </div>
                @else
                  <span class="badge bg-secondary" style="font-size:10px;">Dynamic UPI QR</span>
                @endif
              </div>
            </div>

            <hr class="my-2">
            <div class="row g-2">
              <div class="col-md-6">
                <label class="small fw-bold">Replace Website Brand Logo (`logo.png`)</label>
                <input type="file" name="brand_logo" class="form-control form-control-sm" accept="image/*">
              </div>
              <div class="col-md-6">
                <label class="small fw-bold">Upload New Homepage Slider Banner</label>
                <input type="file" name="banner_image" class="form-control form-control-sm" accept="image/*">
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 2. MSG91 SMS OTP Gateway + Active Slider Banners -->
      <div class="col-lg-6">
        <div class="adm-card mb-3">
          <div class="adm-card-header">
            <h6 class="fw-bold text-success m-0"><i class="fas fa-sms me-1"></i> MSG91 SMS OTP Gateway Configuration</h6>
          </div>
          <div class="adm-card-body">
            <div class="mb-2">
              <label class="small fw-bold">MSG91 Auth Key (`authkey`)</label>
              <input type="text" name="msg91_auth_key" value="{{ $settings['msg91_auth_key'] }}" class="form-control form-control-sm" placeholder="e.g. 418923Ax92kLp...">
            </div>
            <div class="mb-2">
              <label class="small fw-bold">MSG91 DLT Template ID (`template_id`)</label>
              <input type="text" name="msg91_template_id" value="{{ $settings['msg91_template_id'] }}" class="form-control form-control-sm" placeholder="e.g. 65f82a91d6fc0541...">
            </div>
            <div class="row g-2 mb-3">
              <div class="col-6">
                <label class="small fw-bold">Sender ID (6 Chars)</label>
                <input type="text" name="msg91_sender_id" value="{{ $settings['msg91_sender_id'] }}" class="form-control form-control-sm">
              </div>
              <div class="col-6">
                <label class="small fw-bold">Show Sandbox OTP Helper</label>
                <select name="msg91_sandbox_show_otp" class="form-select form-select-sm">
                  <option value="1" {{ $settings['msg91_sandbox_show_otp'] === '1' ? 'selected' : '' }}>Yes (Show OTP on Screen)</option>
                  <option value="0" {{ $settings['msg91_sandbox_show_otp'] === '0' ? 'selected' : '' }}>No (Live SMS Only)</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <div class="adm-card mb-3">
          <div class="adm-card-header d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-warning m-0" style="color:#d97706 !important;"><i class="fas fa-satellite-dish me-1"></i> Sportmonks Live Score API (Backend Secured)</h6>
            <span class="badge bg-dark">Football v3 / Cricket v2 / F1 v1</span>
          </div>
          <div class="adm-card-body">
            <div class="mb-2">
              <label class="small fw-bold">SPORTMONKS_API_TOKEN (Kept strictly on Laravel Backend)</label>
              <input type="password" name="sportmonks_api_token" value="{{ $settings['sportmonks_api_token'] ?? '' }}" class="form-control form-control-sm font-monospace" placeholder="Paste your Sportmonks API Token here (or in .env)...">
              <small class="text-muted d-block mt-1" style="font-size:11px;">Never exposed in Frontend JavaScript. Used by <code>Http::get(...)</code> in <code>SportmonksService</code>.</small>
            </div>
            <div class="row g-2 mb-2">
              <div class="col-md-4">
                <label class="small fw-bold">Football API v3 URL</label>
                <input type="text" name="sportmonks_football_url" value="{{ $settings['sportmonks_football_url'] ?? 'https://api.sportmonks.com/v3/football' }}" class="form-control form-control-sm font-monospace" style="font-size:11px;">
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">Cricket API v2 URL</label>
                <input type="text" name="sportmonks_cricket_url" value="{{ $settings['sportmonks_cricket_url'] ?? 'https://cricket.sportmonks.com/api/v2.0' }}" class="form-control form-control-sm font-monospace" style="font-size:11px;">
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">Formula 1 API v1 URL</label>
                <input type="text" name="sportmonks_f1_url" value="{{ $settings['sportmonks_f1_url'] ?? 'https://f1.sportmonks.com/api/v1.0' }}" class="form-control form-control-sm font-monospace" style="font-size:11px;">
              </div>
            </div>

            <button type="submit" class="btn btn-primary fw-bold w-100 py-2 mt-1">
              <i class="fas fa-save me-1"></i> Save All Website, Payment, SMS &amp; Sportmonks Settings
            </button>
          </div>
        </div>
      </div>
    </div>
  </form>

  <!-- Active Homepage Slider Banners CRUD -->
  <div class="adm-card">
    <div class="adm-card-header">
      <h6 class="fw-bold m-0"><i class="fas fa-images text-primary me-1"></i> Active Homepage Hero Banners</h6>
    </div>
    <div class="adm-card-body">
      <div class="row g-3">
        @foreach(($settings['banners'] ?? []) as $bUrl)
          <div class="col-md-4">
            <div class="border rounded p-2 bg-light text-center">
              <img src="{{ $bUrl }}" alt="Banner" style="width:100%;height:110px;object-fit:cover;border-radius:4px;" onerror="this.src='/assets/brand/banner-1.jpg'">
              <div class="d-flex justify-content-between align-items-center mt-2">
                <code class="small text-truncate" style="max-width:190px;">{{ $bUrl }}</code>
                <form action="{{ route('admin.settings.banner.delete') }}" method="POST" class="m-0">
                  @csrf
                  @method('DELETE')
                  <input type="hidden" name="banner_url" value="{{ $bUrl }}">
                  <button type="submit" class="btn btn-sm btn-danger py-0 px-2"><i class="fas fa-trash"></i> Remove</button>
                </form>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </div>

  <form id="delQrForm" action="{{ route('admin.settings.qr.delete') }}" method="POST" style="display:none;" onsubmit="return confirm('Remove custom QR code and revert to default dynamic UPI QR?')">
    @csrf
  </form>
@endsection
