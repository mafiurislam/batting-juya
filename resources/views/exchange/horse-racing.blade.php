@extends('layouts.app')
@section('title', 'Horse Racing - CRICEXPERTS')

@section('content')
  <div class="text-white fw-bold px-3 py-2 mt-1" style="background:var(--header-bg);">
    <i class="fas fa-horse-head me-1"></i> Today's Horse Racing Schedule
  </div>

  <!-- Country Tabs (GB, AUS, FRA, RSA...) -->
  <div class="d-flex gap-1 px-2 pt-2" style="background:#e2e2e2;border-bottom:1px solid #c8ced3;">
    @foreach($countries as $idx => $countryCode)
      <button type="button" class="btn btn-sm fw-bold px-3 rounded-top js-race-tab {{ $idx === 0 ? 'bg-white border' : '' }}"
              onclick="switchRaceCountry('{{ $countryCode }}', this)">
        {{ $countryCode }}
      </button>
    @endforeach
  </div>

  @foreach($byCountry as $countryCode => $races)
    <div id="raceCountry_{{ $countryCode }}" class="bg-white p-2 border js-race-pane {{ $loop->first ? '' : 'd-none' }}">
      @foreach($races as $race)
        <div class="d-flex flex-wrap align-items-center justify-content-between py-2 px-2 border-bottom">
          <div class="fw-bold text-dark" style="min-width:180px;">
            <i class="fas fa-flag-checkered text-secondary me-1"></i> {{ $race->event_name }}
          </div>
          <div class="d-flex flex-wrap gap-1">
            @for($slot = 0; $slot < 5; $slot++)
              @php
                $raceTime = \Carbon\Carbon::parse($race->event_time)->addMinutes($slot * 35)->format('H:i');
              @endphp
              <a href="{{ route('full.market', ['sportId' => '7', 'eventId' => $race->event_id]) }}"
                 class="badge text-dark text-decoration-none px-2 py-1"
                 style="background:#e2e2e2;border:1px solid #ccc;font-size:11px;">
                {{ $raceTime }}
              </a>
            @endfor
          </div>
        </div>
      @endforeach
    </div>
  @endforeach
@endsection

@push('scripts')
<script>
  function switchRaceCountry(code, btn) {
    document.querySelectorAll('.js-race-tab').forEach(b => b.classList.remove('bg-white', 'border'));
    btn.classList.add('bg-white', 'border');
    document.querySelectorAll('.js-race-pane').forEach(p => p.classList.add('d-none'));
    document.getElementById('raceCountry_' + code)?.classList.remove('d-none');
  }
</script>
@endpush

