@extends('layouts.app')
@section('title', 'Live Casino - winbook24')

@section('content')
  <!-- Live Casino Tab Bar (Screenshot 08_live_casino_page.png) -->
  <div class="wb-casino-tab-bar mt-0 d-flex justify-content-between align-items-center flex-wrap">
    <div class="d-flex align-items-center gap-1 overflow-auto">
      @foreach(array_keys($tabs) as $tabName)
        <button type="button"
                class="wb-casino-tab-link {{ $tabName === $activeTab ? 'active' : '' }}"
                onclick="switchLiveCasinoPane('{{ Str::slug($tabName) }}', this)">
          {{ $tabName }}
          <span class="badge bg-dark ms-1" style="font-size:9px;">{{ $tabs[$tabName]->count() }}</span>
        </button>
      @endforeach
    </div>
    <div class="pe-1">
      <input type="text" id="liveCasinoFilter" class="form-control form-control-sm"
             style="height:26px;font-size:11px;width:170px;"
             placeholder="Filter {{ $allCount }} games..."
             oninput="filterCasinoCards(this.value)">
    </div>
  </div>

  @foreach($tabs as $tabName => $gamesList)
    @php $slug = Str::slug($tabName); @endphp
    <div id="lcPane_{{ $slug }}" class="wb-casino-grid js-lc-pane {{ $tabName === $activeTab ? '' : 'd-none' }}">
      @foreach($gamesList as $cg)
        <a href="{{ $cg->launch_url }}"
           class="wb-casino-card js-casino-card-item"
           data-gname="{{ strtolower($cg->event_name) }}"
           title="Play {{ $cg->event_name }}">
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
  function switchLiveCasinoPane(slug, btn) {
    document.querySelectorAll('.wb-casino-tab-link').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.js-lc-pane').forEach(p => p.classList.add('d-none'));
    document.getElementById('lcPane_' + slug)?.classList.remove('d-none');
  }

  function filterCasinoCards(q) {
    const term = q.trim().toLowerCase();
    document.querySelectorAll('.js-casino-card-item').forEach(el => {
      el.style.display = (!term || el.dataset.gname.includes(term)) ? '' : 'none';
    });
  }
</script>
@endpush

