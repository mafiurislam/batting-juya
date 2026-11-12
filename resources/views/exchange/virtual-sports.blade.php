@extends('layouts.app')
@section('title', 'Virtual Sports - winbook24')

@section('content')
  <div class="wb-highlights-header mt-1">
    Virtual Sports &amp; Instant Games
  </div>

  <div class="wb-casino-grid">
    @foreach($virtualCasino as $cg)
      <a href="{{ route('casino.play', ['eventId' => $cg->event_id]) }}" class="wb-ourcasino-card">
        <img src="{{ $cg->display_image }}" alt="{{ $cg->event_name }}">
        <div class="wb-ourcasino-title">{{ $cg->event_name }}</div>
      </a>
    @endforeach
  </div>

  <div class="wb-highlights-header mt-2">
    Ball By Ball &amp; Virtual Cricket Markets
  </div>
  @foreach($virtualEvents as $ev)
    @include('partials.match-row', ['ev' => $ev, 'pinnedIds' => $pinnedIds])
  @endforeach
@endsection

