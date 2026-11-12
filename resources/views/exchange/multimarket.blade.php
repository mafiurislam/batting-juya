@extends('layouts.app')
@section('title', 'Multi Markets - winbook24')

@section('content')
  <div class="wb-report-title mt-1">
    Multi Markets
  </div>

  @if($pinnedEvents->count() > 0)
    <div class="wb-1x2-head">
      <div class="wb-1x2-left">Pinned Events</div>
      <div class="wb-1x2-cols">
        <div>1</div>
        <div>X</div>
        <div>2</div>
      </div>
      <div class="wb-1x2-pin-space"></div>
    </div>
    @foreach($pinnedEvents as $ev)
      @include('partials.match-row', ['ev' => $ev, 'pinnedIds' => $pinnedIds])
    @endforeach
  @else
    <div class="bg-white p-5 text-center border mt-2" style="color:#7e97a7;">
      <i class="fas fa-thumbtack fs-2 mb-2"></i>
      <h6 class="fw-bold">There are currently no followed multi markets.</h6>
      <p class="small mb-2">Click the pin icon <i class="fas fa-thumbtack"></i> on any match row in Home or In-Play to follow markets here.</p>
      <a href="{{ route('home') }}" class="btn btn-sm text-white fw-bold" style="background:var(--header-bg);">Browse Live Markets</a>
    </div>
  @endif
@endsection

