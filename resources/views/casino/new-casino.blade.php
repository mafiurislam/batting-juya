@extends('layouts.app')
@section('title', 'Our Casino - CRICEXPERTS')

@section('content')
  <!-- Our Casino / New Casino Tab Bar (Screenshot 26_newCasino.png) -->
  <div class="wb-casino-tab-bar mt-0">
    @foreach(array_keys($tabs) as $tabName)
      <button type="button"
              class="wb-casino-tab-link {{ $tabName === $activeTab ? 'active' : '' }}"
              onclick="switchNewCasinoPane('{{ Str::slug($tabName) }}', this)">
        {{ $tabName }}
      </button>
    @endforeach
  </div>

  @foreach($tabs as $tabName => $gamesList)
    @php $slug = Str::slug($tabName); @endphp
    <div id="ncPane_{{ $slug }}" class="wb-casino-grid js-nc-pane {{ $tabName === $activeTab ? '' : 'd-none' }}">
      @foreach($gamesList as $cg)
        <a href="{{ $cg->launch_url }}" class="wb-ourcasino-card" title="Play {{ $cg->event_name }}">
          <img src="{{ $cg->display_image }}" alt="{{ $cg->event_name }}" loading="lazy" onerror="this.src='/assets/casino/99_0010.webp'">
          <div class="wb-ourcasino-title">{{ $cg->event_name }}</div>
        </a>
      @endforeach
    </div>
  @endforeach
@endsection

@push('scripts')
<script>
  function switchNewCasinoPane(slug, btn) {
    document.querySelectorAll('.wb-casino-tab-link').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.js-nc-pane').forEach(p => p.classList.add('d-none'));
    document.getElementById('ncPane_' + slug)?.classList.remove('d-none');
  }
</script>
@endpush

