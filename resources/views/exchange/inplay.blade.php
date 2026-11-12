@extends('layouts.app')
@section('title', 'In-Play - winbook24')

@section('content')
  <div class="bg-white p-2 border-bottom d-flex gap-1">
    <button type="button" class="btn btn-sm fw-bold px-3 js-ip-tab active" style="border:1px solid #3B5160;background:#3B5160;color:#fff;" onclick="switchInplayTab('inplay', this)">
      In-Play
    </button>
    <button type="button" class="btn btn-sm fw-bold px-3 js-ip-tab" style="border:1px solid #3B5160;background:#fff;color:#3B5160;" onclick="switchInplayTab('today', this)">
      Today
    </button>
    <button type="button" class="btn btn-sm fw-bold px-3 js-ip-tab" style="border:1px solid #3B5160;background:#fff;color:#3B5160;" onclick="switchInplayTab('tomorrow', this)">
      Tomorrow
    </button>
  </div>

  @php
    $panes = [
      'inplay' => $inplayBySport,
      'today' => $todayBySport,
      'tomorrow' => $tomorrowBySport,
    ];
  @endphp

  @foreach($panes as $pKey => $grouped)
    <div id="ipPane_{{ $pKey }}" class="js-ip-pane {{ $pKey === 'inplay' ? '' : 'd-none' }}">
      @forelse($grouped as $sportName => $events)
        <div class="mt-2">
          <div class="text-white fw-bold px-2 py-1" style="background:var(--header-bg);font-size:13px;">
            {{ $sportName }}
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
        <div class="bg-white p-4 text-center text-muted mt-2 border">No events currently scheduled in this tab.</div>
      @endforelse
    </div>
  @endforeach
@endsection

@push('scripts')
<script>
  function switchInplayTab(key, btn) {
    document.querySelectorAll('.js-ip-tab').forEach(b => {
      b.style.background = '#fff';
      b.style.color = '#3B5160';
    });
    btn.style.background = '#3B5160';
    btn.style.color = '#fff';
    document.querySelectorAll('.js-ip-pane').forEach(p => p.classList.add('d-none'));
    document.getElementById('ipPane_' + key)?.classList.remove('d-none');
  }
</script>
@endpush

