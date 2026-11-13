@extends('layouts.app')
@section('title', 'winbook24 - Home')

@section('content')
  @auth
    <!-- News Marquee Bar (Screenshot 15) -->
    <div class="wb-news-bar">
      <div class="wb-news-badge">
        <i class="fas fa-microphone"></i> News
      </div>
      <marquee direction="left" scrollamount="5" class="flex-grow-1 px-2">
        {{ $newsMarquee }}
      </marquee>
    </div>
  @endauth

  <!-- Hero Banner Slider (Universe Casino Banner) -->
  <div id="wbHeroCarousel" class="carousel slide wb-banner-wrap" data-bs-ride="carousel">
    <div class="carousel-inner">
      @foreach($banners as $idx => $bannerUrl)
        <div class="carousel-item {{ $idx === 0 ? 'active' : '' }}">
          <a href="{{ route('livecasino') }}">
            <img src="{{ $bannerUrl }}" alt="Universe Casino">
          </a>
        </div>
      @endforeach
    </div>
  </div>

  <!-- Highlights Section -->
  <div class="wb-highlights-header">
    Highlights
  </div>

  <!-- Sport Tabs: Cricket | Soccer | Tennis -->
  <div class="wb-sport-tabs-bar">
    <button type="button" class="wb-sport-tab-btn active" onclick="switchHighlightSport('cricket', this)">
      Cricket
    </button>
    <button type="button" class="wb-sport-tab-btn" onclick="switchHighlightSport('soccer', this)">
      Soccer
    </button>
    <button type="button" class="wb-sport-tab-btn" onclick="switchHighlightSport('tennis', this)">
      Tennis
    </button>
  </div>

  <!-- In-Play (Live Only) | Today | Tomorrow | Yesterday Filter Bar -->
  <div class="wb-day-filter-bar" id="homeDayFilterBar">
    <button type="button" class="wb-day-filter-btn js-home-day-btn active" data-day-filter="inplay" onclick="switchHomeDayFilter('inplay', this)">
      <span class="wb-cb-live-dot"></span> In-Play
      <span class="wb-filter-count js-home-count" data-count-for="inplay">0</span>
    </button>
    <button type="button" class="wb-day-filter-btn js-home-day-btn" data-day-filter="today" onclick="switchHomeDayFilter('today', this)">
      Today
      <span class="wb-filter-count js-home-count" data-count-for="today">0</span>
    </button>
    <button type="button" class="wb-day-filter-btn js-home-day-btn" data-day-filter="tomorrow" onclick="switchHomeDayFilter('tomorrow', this)">
      Tomorrow
      <span class="wb-filter-count js-home-count" data-count-for="tomorrow">0</span>
    </button>
    <button type="button" class="wb-day-filter-btn js-home-day-btn" data-day-filter="yesterday" onclick="switchHomeDayFilter('yesterday', this)">
      Yesterday
      <span class="wb-filter-count js-home-count" data-count-for="yesterday">0</span>
    </button>
  </div>

  <!-- 1 | X | 2 Header -->
  <div class="wb-1x2-head">
    <div class="wb-1x2-left"></div>
    <div class="wb-1x2-cols">
      <div>1</div>
      <div>X</div>
      <div>2</div>
    </div>
    <div class="wb-1x2-pin-space"></div>
  </div>

  <!-- Cricket Matches Pane (Defaults to showing ONLY In-Play live matches at the top) -->
  <div id="hlPane_cricket" class="js-hl-pane" data-sport-pane="cricket">
    @foreach($cricketEvents as $ev)
      @include('partials.match-row', ['ev' => $ev, 'pinnedIds' => $pinnedIds, 'onlyInPlayDefault' => true])
    @endforeach
    <div class="bg-white p-3 text-center text-muted border d-none js-empty-day-msg">No Cricket matches in this filter.</div>
  </div>

  <!-- Soccer Matches Pane -->
  <div id="hlPane_soccer" class="js-hl-pane d-none" data-sport-pane="soccer">
    @foreach($soccerEvents as $ev)
      @include('partials.match-row', ['ev' => $ev, 'pinnedIds' => $pinnedIds, 'onlyInPlayDefault' => true])
    @endforeach
    <div class="bg-white p-3 text-center text-muted border d-none js-empty-day-msg">No Soccer matches in this filter.</div>
  </div>

  <!-- Tennis Matches Pane -->
  <div id="hlPane_tennis" class="js-hl-pane d-none" data-sport-pane="tennis">
    @foreach($tennisEvents as $ev)
      @include('partials.match-row', ['ev' => $ev, 'pinnedIds' => $pinnedIds, 'onlyInPlayDefault' => true])
    @endforeach
    <div class="bg-white p-3 text-center text-muted border d-none js-empty-day-msg">No Tennis matches in this filter.</div>
  </div>

  <!-- Home Casino Tabs & Grid (Screenshots 01_home_desktop_full & 08_live_casino_page) -->
  <div class="wb-casino-tab-bar">
    <button type="button" class="wb-casino-tab-link active" onclick="switchHomeCasinoTab('popular', this)">Popular</button>
    <button type="button" class="wb-casino-tab-link" onclick="switchHomeCasinoTab('live', this)">Universe Live</button>
    <button type="button" class="wb-casino-tab-link" onclick="switchHomeCasinoTab('original', this)">Universe Original</button>
    <button type="button" class="wb-casino-tab-link" onclick="switchHomeCasinoTab('intl', this)">International</button>
    <button type="button" class="wb-casino-tab-link" onclick="switchHomeCasinoTab('slot', this)">Slot</button>
  </div>

  @php
    $homeCasinoPanes = [
      'popular' => $popularCasino,
      'live' => $universeLive,
      'original' => $universeOriginal,
      'intl' => $internationalCasino,
      'slot' => $slotGames,
    ];
  @endphp

  @foreach($homeCasinoPanes as $paneKey => $gamesList)
    <div id="homeCasinoPane_{{ $paneKey }}" class="wb-casino-grid js-home-casino-pane {{ $paneKey === 'popular' ? '' : 'd-none' }}">
      @foreach($gamesList as $cg)
        <a href="{{ $cg->launch_url }}" class="wb-casino-card" title="Play {{ $cg->event_name }}">
          <img src="{{ $cg->display_image }}" alt="{{ $cg->event_name }}" loading="lazy" onerror="this.src='/assets/casino/99_0010.webp'">
          <div class="wb-casino-card-overlay">
            <div class="text-white fw-bold text-center mb-1" style="font-size:11px;text-shadow:0 1px 3px #000;">{{ $cg->event_name }}</div>
            <span class="wb-casino-play-pill"><i class="fas fa-play me-1"></i> PLAY NOW</span>
          </div>
        </a>
      @endforeach
    </div>
  @endforeach
@endsection

@push('scripts')
<script>
  let activeHomeSport = 'cricket';
  let activeHomeDayFilter = 'inplay';

  function applyHomeDayFilter() {
    const pane = document.getElementById('hlPane_' + activeHomeSport);
    if (!pane) return;

    const rows = pane.querySelectorAll('.js-filterable-match-row');
    const counts = { inplay: 0, today: 0, tomorrow: 0, yesterday: 0 };
    let visibleCount = 0;

    rows.forEach(row => {
      if (row.getAttribute('data-is-inplay') === '1') counts.inplay++;
      if (row.getAttribute('data-is-today') === '1') counts.today++;
      if (row.getAttribute('data-is-tomorrow') === '1') counts.tomorrow++;
      if (row.getAttribute('data-is-yesterday') === '1') counts.yesterday++;

      const matchAttr = 'data-is-' + activeHomeDayFilter;
      if (row.getAttribute(matchAttr) === '1') {
        row.classList.remove('d-none');
        visibleCount++;
      } else {
        row.classList.add('d-none');
      }
    });

    document.querySelectorAll('.js-home-count').forEach(badge => {
      const key = badge.getAttribute('data-count-for');
      if (key && counts[key] !== undefined) {
        badge.textContent = counts[key];
      }
    });

    const emptyMsg = pane.querySelector('.js-empty-day-msg');
    if (emptyMsg) {
      emptyMsg.classList.toggle('d-none', visibleCount > 0);
    }
  }

  function switchHighlightSport(key, btn) {
    activeHomeSport = key;
    document.querySelectorAll('.wb-sport-tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.js-hl-pane').forEach(p => p.classList.add('d-none'));
    document.getElementById('hlPane_' + key)?.classList.remove('d-none');
    applyHomeDayFilter();
  }

  function switchHomeDayFilter(filterKey, btn) {
    activeHomeDayFilter = filterKey;
    document.querySelectorAll('.js-home-day-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    applyHomeDayFilter();
  }

  function switchHomeCasinoTab(key, btn) {
    document.querySelectorAll('.wb-casino-tab-link').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.js-home-casino-pane').forEach(p => p.classList.add('d-none'));
    document.getElementById('homeCasinoPane_' + key)?.classList.remove('d-none');
  }

  document.addEventListener('DOMContentLoaded', () => {
    applyHomeDayFilter();
  });
</script>
@endpush
