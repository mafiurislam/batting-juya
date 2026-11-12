@extends('layouts.app')
@section('title', 'winbook24')

@push('styles')
<style>
  .wb-settings-card {
    background: #fff;
    border: 1px solid #c8ced3;
    border-radius: 0;
    margin-top: 4px;
    font-family: Tahoma, Helvetica, Arial, sans-serif;
  }
  .wb-settings-top-head {
    background: var(--sportsbar-bg);
    color: #000;
    padding: 6px 12px;
    font-weight: 700;
    font-size: 14px;
    display: flex;
    align-items: center;
  }
  .wb-settings-sec-head {
    background: linear-gradient(-180deg, #2E4B5E 0%, #243A48 82%);
    color: #fff;
    padding: 6px 12px;
    font-weight: 700;
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  .wb-theme-options {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    padding: 10px 12px;
  }
  .wb-theme-circle {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 1px 4px rgba(0,0,0,0.35);
    cursor: pointer;
    display: inline-block;
    transition: transform 0.15s;
  }
  .wb-theme-circle:hover {
    transform: scale(1.1);
  }
  .wb-quick-stake-input {
    height: 34px;
    border: 1px solid #315195;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 600;
    color: #212529;
    padding: 4px 10px;
  }
  .wb-settings-save-btn {
    background: var(--header-bg);
    color: #fff;
    border: none;
    border-radius: 4px;
    width: 100%;
    height: 35px;
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
  }
  .wb-settings-save-btn:hover {
    filter: brightness(1.08);
  }
</style>
@endpush

@section('content')
  <div class="wb-settings-card">
    <div class="wb-settings-top-head">
      <i class="fa fa-cog fa-lg me-2"></i>
      <span>Setting</span>
    </div>

    <div class="wb-settings-sec-head">
      <span>Theme</span>
      <span style="cursor:pointer;font-weight:400;font-size:13px;" onclick="applyWbTheme('#315195', '#14213D', '#A4DC60', '#4F9F21')">
        Default <i class="fas fa-sync-alt ms-1"></i>
      </span>
    </div>

    <div class="wb-theme-options">
      <span class="wb-theme-circle" style="background:linear-gradient(135deg, #5d2fb2 50%, #24c6dc 50%);" onclick="applyWbTheme('#5d2fb2', '#32156b', '#24c6dc', '#118a9b')" title="Theme 1"></span>
      <span class="wb-theme-circle" style="background:linear-gradient(135deg, #c31432 50%, #f7971e 50%);" onclick="applyWbTheme('#c31432', '#6f091b', '#f7971e', '#c97206')" title="Theme 2"></span>
      <span class="wb-theme-circle" style="background:linear-gradient(135deg, #8e54e9 50%, #4776e6 50%);" onclick="applyWbTheme('#4776e6', '#1e3c8c', '#8e54e9', '#5e2ab5')" title="Theme 3"></span>
      <span class="wb-theme-circle" style="background:linear-gradient(135deg, #b993d6 50%, #8ca6db 50%);" onclick="applyWbTheme('#6b528c', '#3c2b52', '#8ca6db', '#5974ab')" title="Theme 4"></span>
      <span class="wb-theme-circle" style="background:linear-gradient(135deg, #1d976c 50%, #3a7bd5 50%);" onclick="applyWbTheme('#1d976c', '#0e543b', '#93f9b9', '#29b36a')" title="Theme 5"></span>
      <span class="wb-theme-circle" style="background:linear-gradient(135deg, #f857a6 50%, #a8ff78 50%);" onclick="applyWbTheme('#c92a76', '#6e113d', '#a8ff78', '#5cc227')" title="Theme 6"></span>
      <span class="wb-theme-circle" style="background:linear-gradient(135deg, #000000 50%, #f7b733 50%);" onclick="applyWbTheme('#232526', '#000000', '#f7b733', '#d48e06')" title="Theme 7"></span>
      <span class="wb-theme-circle" style="background:linear-gradient(135deg, #4b6cb7 50%, #182848 50%);" onclick="applyWbTheme('#4b6cb7', '#182848', '#83a4d4', '#4b6cb7')" title="Theme 8"></span>
      <span class="wb-theme-circle" style="background:linear-gradient(135deg, #d66d75 50%, #e29587 50%);" onclick="applyWbTheme('#b0464e', '#631f24', '#e29587', '#b8685a')" title="Theme 9"></span>
      <span class="wb-theme-circle" style="background:linear-gradient(135deg, #e52d27 50%, #b31217 50%);" onclick="applyWbTheme('#e52d27', '#7a090d', '#ffb347', '#cc7a00')" title="Theme 10"></span>
    </div>

    <div class="wb-settings-sec-head">
      <span>Stake</span>
    </div>

    <div class="p-3">
      <div class="mb-2">
        <strong style="font-size:14px;color:#000;">Quick Stakes</strong>
      </div>

      <form action="{{ route('settings.save') }}" method="POST">
        @csrf
        <div class="row g-2 mb-3" style="max-width:520px;">
          @foreach($stakes as $idx => $st)
            <div class="col-3">
              <input type="hidden" name="labels[]" value="{{ $st['value'] }}">
              <input type="text" inputmode="numeric" name="values[]" value="{{ $st['value'] }}" maxlength="9" class="form-control wb-quick-stake-input" required>
            </div>
          @endforeach
        </div>

        <div style="max-width:520px;">
          <button type="submit" class="wb-settings-save-btn">Save</button>
        </div>
      </form>
    </div>
  </div>
@endsection

@push('scripts')
<script>
  function applyWbTheme(h1, h2, s1, s2) {
    document.documentElement.style.setProperty('--header-bg', `linear-gradient(-180deg, ${h1} 0%, ${h2} 100%)`);
    document.documentElement.style.setProperty('--sportsbar-bg', `linear-gradient(${s1} 0%, ${s2} 100%)`);
    if (window.WB24) WB24.toast('Theme updated!', 'info');
  }
</script>
@endpush
