@extends('layouts.app')
@section('title', $activeSportName . ' - winbook24')

@section('content')
  <div class="wb-highlights-header mt-1 d-flex justify-content-between align-items-center">
    <span>{{ $activeSportName }} Highlights</span>
    <span class="badge bg-dark">{{ $events->count() }} Matches</span>
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

  @forelse($events as $ev)
    @include('partials.match-row', ['ev' => $ev, 'pinnedIds' => $pinnedIds])
  @empty
    <div class="bg-white p-4 text-center text-muted border">No active matches found for {{ $activeSportName }}.</div>
  @endforelse
@endsection

