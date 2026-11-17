@extends('layouts.app')
@section('title', 'Tips & Previews - CRICEXPERTS')

@section('content')
  <div class="wb-highlights-header mt-1">
    Sports Tips &amp; Match Previews
  </div>

  <div class="row g-2 mt-1">
    @forelse($tips as $tip)
      <div class="col-md-6">
        <div class="bg-white border rounded-1 h-100 overflow-hidden shadow-sm">
          @if(!empty($tip['url']))
            <img src="{{ $tip['url'] }}" alt="{{ $tip['title'] ?? 'Preview' }}" style="width:100%;height:165px;object-fit:cover;" onerror="this.src='/assets/brand/banner-1.jpg'">
          @endif
          <div class="p-2">
            <h6 class="fw-bold text-dark mb-1" style="font-size:13px;">{{ $tip['title'] ?? 'Sports Preview' }}</h6>
            <p class="text-muted small mb-0" style="font-size:11px;max-height:76px;overflow:hidden;">
              {{ $tip['description'] ?? '' }}
            </p>
          </div>
        </div>
      </div>
    @empty
      <div class="col-12">
        <div class="bg-white p-4 text-center text-muted border">No previews available.</div>
      </div>
    @endforelse
  </div>
@endsection

