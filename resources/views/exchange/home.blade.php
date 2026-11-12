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
    <button type="button" class="wb-sport-tab-btn active" onclick="switchHighlightSport('cricket', this)">Cricket</button>
    <button type="button" class="wb-sport-tab-btn" onclick="switchHighlightSport('soccer', this)">Soccer</button>
    <button type="button" class="wb-sport-tab-btn" onclick="switchHighlightSport('tennis', this)">Tennis</button>
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

  <!-- Cricket Matches Pane -->
  <div id="hlPane_cricket" class="js-hl-pane">
    @foreach($cricketEvents as $ev)
      @include('partials.match-row', ['ev' => $ev, 'pinnedIds' => $pinnedIds])
    @endforeach
  </div>

  <!-- Soccer Matches Pane -->
  <div id="hlPane_soccer" class="js-hl-pane d-none">
    @foreach($soccerEvents as $ev)
      @include('partials.match-row', ['ev' => $ev, 'pinnedIds' => $pinnedIds])
    @endforeach
  </div>

  <!-- Tennis Matches Pane -->
  <div id="hlPane_tennis" class="js-hl-pane d-none">
    @foreach($tennisEvents as $ev)
      @include('partials.match-row', ['ev' => $ev, 'pinnedIds' => $pinnedIds])
    @endforeach
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
  function switchHighlightSport(key, btn) {
    document.querySelectorAll('.wb-sport-tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.js-hl-pane').forEach(p => p.classList.add('d-none'));
    document.getElementById('hlPane_' + key)?.classList.remove('d-none');
  }

  function switchHomeCasinoTab(key, btn) {
    document.querySelectorAll('.wb-casino-tab-link').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.js-home-casino-pane').forEach(p => p.classList.add('d-none'));
    document.getElementById('homeCasinoPane_' + key)?.classList.remove('d-none');
  }
</script>
@endpush

