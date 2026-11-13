@extends('layouts.app')
@section('title', $activeSportName . ' - winbook24')

@section('content')
  <div class="wb-highlights-header mt-1 d-flex justify-content-between align-items-center">
    <span>{{ $activeSportName }} Highlights</span>
    <span class="badge bg-dark" id="sportVisibleCountBadge">{{ $events->where('in_play', true)->count() }} Live</span>
  </div>

  <!-- In-Play (Live Only) | Today | Tomorrow | Yesterday Filter Bar -->
  <div class="wb-day-filter-bar" id="sportDayFilterBar">
    <button type="button" class="wb-day-filter-btn js-sport-day-btn active" data-day-filter="inplay" onclick="switchSportDayFilter('inplay', this)">
      <span class="wb-cb-live-dot"></span> In-Play
      <span class="wb-filter-count js-sport-count" data-count-for="inplay">0</span>
    </button>
    <button type="button" class="wb-day-filter-btn js-sport-day-btn" data-day-filter="today" onclick="switchSportDayFilter('today', this)">
      Today
      <span class="wb-filter-count js-sport-count" data-count-for="today">0</span>
    </button>
    <button type="button" class="wb-day-filter-btn js-sport-day-btn" data-day-filter="tomorrow" onclick="switchSportDayFilter('tomorrow', this)">
      Tomorrow
      <span class="wb-filter-count js-sport-count" data-count-for="tomorrow">0</span>
    </button>
    <button type="button" class="wb-day-filter-btn js-sport-day-btn" data-day-filter="yesterday" onclick="switchSportDayFilter('yesterday', this)">
      Yesterday
      <span class="wb-filter-count js-sport-count" data-count-for="yesterday">0</span>
    </button>
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

  <div id="sportEventsContainer">
    @forelse($events as $ev)
      @include('partials.match-row', ['ev' => $ev, 'pinnedIds' => $pinnedIds, 'onlyInPlayDefault' => true])
    @empty
      <div class="bg-white p-4 text-center text-muted border">No active matches found for {{ $activeSportName }}.</div>
    @endforelse
    <div id="sportEmptyDayMsg" class="bg-white p-4 text-center text-muted border d-none">
      No {{ $activeSportName }} matches available in this filter.
    </div>
  </div>
@endsection

@push('scripts')
<script>
  let activeSportDayFilter = 'inplay';

  function applySportDayFilter() {
    const container = document.getElementById('sportEventsContainer');
    if (!container) return;

    const rows = container.querySelectorAll('.js-filterable-match-row');
    const counts = { inplay: 0, today: 0, tomorrow: 0, yesterday: 0 };
    let visibleCount = 0;

    rows.forEach(row => {
      if (row.getAttribute('data-is-inplay') === '1') counts.inplay++;
      if (row.getAttribute('data-is-today') === '1') counts.today++;
      if (row.getAttribute('data-is-tomorrow') === '1') counts.tomorrow++;
      if (row.getAttribute('data-is-yesterday') === '1') counts.yesterday++;

      const matchAttr = 'data-is-' + activeSportDayFilter;
      if (row.getAttribute(matchAttr) === '1') {
        row.classList.remove('d-none');
        visibleCount++;
      } else {
        row.classList.add('d-none');
      }
    });

    document.querySelectorAll('.js-sport-count').forEach(badge => {
      const key = badge.getAttribute('data-count-for');
      if (key && counts[key] !== undefined) {
        badge.textContent = counts[key];
      }
    });

    const topBadge = document.getElementById('sportVisibleCountBadge');
    if (topBadge) {
      topBadge.textContent = visibleCount + (activeSportDayFilter === 'inplay' ? ' Live' : ' Matches');
    }

    const emptyMsg = document.getElementById('sportEmptyDayMsg');
    if (emptyMsg && rows.length > 0) {
      emptyMsg.classList.toggle('d-none', visibleCount > 0);
    }
  }

  function switchSportDayFilter(filterKey, btn) {
    activeSportDayFilter = filterKey;
    document.querySelectorAll('.js-sport-day-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    applySportDayFilter();
  }

  document.addEventListener('DOMContentLoaded', () => {
    applySportDayFilter();
  });
</script>
@endpush
