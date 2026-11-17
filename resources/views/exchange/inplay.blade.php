@extends('layouts.app')
@section('title', 'In-Play - CRICEXPERTS')

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
          <div class="wb-matches-scroll-pane">
            @foreach($events as $ev)
              @include('partials.match-row', ['ev' => $ev, 'pinnedIds' => $pinnedIds])
            @endforeach
          </div>
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

  // Real-time In-Play synchronization with winbook24.com
  // Automatically removes any match from In-Play as soon as it ends
  async function syncLiveInplayStatus() {
    try {
      const res = await fetch('/api/exchange/live-inplay-events', {
        headers: { 'Accept': 'application/json' }
      });
      if (!res.ok) return;
      const data = await res.json();
      if (!data || !data.inplayIds) return;

      const liveSet = new Set(data.inplayIds.map(String));

      // 1. Process all match rows in In-Play pane
      const inplayPane = document.getElementById('ipPane_inplay');
      if (inplayPane) {
        inplayPane.querySelectorAll('.js-filterable-match-row').forEach(row => {
          const eId = String(row.getAttribute('data-event-id') || row.getAttribute('data-event-row') || '');
          if (!eId) return;

          if (!liveSet.has(eId)) {
            // Match has ENDED! Smoothly fade out and remove from In-Play section
            row.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            row.style.opacity = '0';
            row.style.transform = 'translateX(-15px)';
            setTimeout(() => {
              row.remove();
              // Check if sport section is now empty
              document.querySelectorAll('.js-ip-sport-section-inplay').forEach(sec => {
                const remaining = sec.querySelectorAll('.js-filterable-match-row').length;
                if (remaining === 0) {
                  sec.classList.add('d-none');
                }
              });
            }, 400);
          }
        });
      }

      // 2. Update Live counts
      if (data.counts && data.counts.inplay !== undefined) {
        const cnt = data.counts.inplay;
        document.querySelectorAll('.wb-filter-count').forEach((badge, idx) => {
          if (idx === 0) badge.textContent = cnt;
        });
      }
    } catch (e) {
      // Safe fallback
    }
  }

  // Poll every 4 seconds for live match status
  document.addEventListener('DOMContentLoaded', () => {
    setInterval(syncLiveInplayStatus, 4000);
  });
</script>
@endpush
