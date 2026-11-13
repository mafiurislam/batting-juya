@extends('layouts.app')
@section('title', 'In-Play - winbook24')

@section('content')
  <!-- In-Play | Today | Tomorrow | Yesterday Filter Tabs -->
  <div class="wb-day-filter-bar">
    <button type="button" class="wb-day-filter-btn js-ip-tab active" onclick="switchInplayTab('inplay', this)">
      <span class="wb-cb-live-dot"></span> In-Play
      <span class="wb-filter-count">{{ $inplayCount ?? $inplayBySport->flatten(1)->count() }}</span>
    </button>
    <button type="button" class="wb-day-filter-btn js-ip-tab" onclick="switchInplayTab('today', this)">
      Today
      <span class="wb-filter-count">{{ $todayCount ?? $todayBySport->flatten(1)->count() }}</span>
    </button>
    <button type="button" class="wb-day-filter-btn js-ip-tab" onclick="switchInplayTab('tomorrow', this)">
      Tomorrow
      <span class="wb-filter-count">{{ $tomorrowCount ?? $tomorrowBySport->flatten(1)->count() }}</span>
    </button>
    <button type="button" class="wb-day-filter-btn js-ip-tab" onclick="switchInplayTab('yesterday', this)">
      Yesterday
      <span class="wb-filter-count">{{ isset($yesterdayBySport) ? ($yesterdayCount ?? $yesterdayBySport->flatten(1)->count()) : 0 }}</span>
    </button>
  </div>

  @php
    $panes = [
      'inplay' => $inplayBySport,
      'today' => $todayBySport,
      'tomorrow' => $tomorrowBySport,
      'yesterday' => $yesterdayBySport ?? collect(),
    ];
  @endphp

  @foreach($panes as $pKey => $grouped)
    <div id="ipPane_{{ $pKey }}" class="js-ip-pane {{ $pKey === 'inplay' ? '' : 'd-none' }}">
      @if($grouped->isNotEmpty())
        <!-- Top Sport Quick-Filter Bar for this Tab -->
        <div class="wb-sport-tabs-bar mt-1">
          <button type="button" class="wb-sport-tab-btn active js-ip-sport-btn-{{ $pKey }}" onclick="filterInplaySport('{{ $pKey }}', 'all', this)">
            All Sports ({{ $grouped->flatten(1)->count() }})
          </button>
          @foreach($grouped as $sportName => $events)
            <button type="button" class="wb-sport-tab-btn js-ip-sport-btn-{{ $pKey }}" onclick="filterInplaySport('{{ $pKey }}', '{{ \Illuminate\Support\Str::slug($sportName) }}', this)">
              {{ $sportName }} ({{ $events->count() }})
            </button>
          @endforeach
        </div>
      @endif

      @forelse($grouped as $sportName => $events)
        <div class="mt-2 js-ip-sport-section-{{ $pKey }}" data-sport-slug="{{ \Illuminate\Support\Str::slug($sportName) }}">
          <div class="text-white fw-bold px-2 py-1 d-flex justify-content-between align-items-center" style="background:var(--header-bg);font-size:13px;">
            <span>{{ $sportName }}</span>
            <span class="badge bg-dark" style="font-size:10px;">
              @if($pKey === 'inplay')
                LIVE ({{ $events->count() }})
              @else
                {{ $events->count() }} Matches
              @endif
            </span>
          </div>
          <div class="wb-1x2-head">
            <div class="wb-1x2-left"></div>
            <div class="wb-1x2-cols">
              <div>1</div>
              <div>X</div>
              <div>2</div>
            </div>
            <div class="wb-1x2-pin-space"></div>
          </div>
          @foreach($events as $ev)
            @include('partials.match-row', ['ev' => $ev, 'pinnedIds' => $pinnedIds])
          @endforeach
        </div>
      @empty
        <div class="bg-white p-4 text-center text-muted mt-2 border">No matches currently available in {{ ucfirst($pKey) }}.</div>
      @endforelse
    </div>
  @endforeach
@endsection

@push('scripts')
<script>
  function switchInplayTab(key, btn) {
    document.querySelectorAll('.js-ip-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.js-ip-pane').forEach(p => p.classList.add('d-none'));
    document.getElementById('ipPane_' + key)?.classList.remove('d-none');
  }

  function filterInplaySport(paneKey, sportSlug, btn) {
    document.querySelectorAll('.js-ip-sport-btn-' + paneKey).forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.js-ip-sport-section-' + paneKey).forEach(sec => {
      if (sportSlug === 'all' || sec.getAttribute('data-sport-slug') === sportSlug) {
        sec.classList.remove('d-none');
      } else {
        sec.classList.add('d-none');
      }
    });
  }
</script>
@endpush
