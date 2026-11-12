@extends('layouts.app')
@section('title', 'Password History - winbook24')

@section('content')
<div class="wb-acct-card">
  <div class="wb-acct-head">
    <span>Password Change History</span>
  </div>
  <div class="p-3">
    <form action="{{ route('password.history') }}" method="GET" id="pwHistForm">
      <div class="wb-dt-controls">
        <span>Show</span>
        <select name="per_page" onchange="document.getElementById('pwHistForm').submit();">
          @foreach([10, 25, 50, 100] as $n)
            <option value="{{ $n }}" {{ ($perPage ?? 10) == $n ? 'selected' : '' }}>{{ $n }}</option>
          @endforeach
        </select>
        <span>entries</span>
      </div>
    </form>

    <div class="table-responsive">
      <table class="wb-dt-table">
        <thead>
          <tr>
            <th>Date/Time <span class="dt-sort">⇅</span></th>
            <th>Remark <span class="dt-sort">⇅</span></th>
          </tr>
        </thead>
        <tbody>
          @forelse($histories as $h)
            <tr>
              <td>{{ $h->created_at->format('M j, Y, g:i:s A') }}</td>
              <td>{{ $h->remark }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="2" class="text-center py-2">No data!</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="wb-dt-footer">
      <div>
        Showing {{ $histories->firstItem() ?? 0 }} to {{ $histories->lastItem() ?? 0 }} of {{ $histories->total() }} entries
      </div>
      <div class="wb-dt-pagination">
        <a href="{{ $histories->url(1) }}">First</a>
        <a href="{{ $histories->previousPageUrl() ?? 'javascript:void(0)' }}">Previous</a>
        @if($histories->total() > 0)
          <a href="{{ $histories->url($histories->currentPage()) }}" class="active-page">{{ $histories->currentPage() }}</a>
        @endif
        <a href="{{ $histories->nextPageUrl() ?? 'javascript:void(0)' }}">Next</a>
        <a href="{{ $histories->url($histories->lastPage()) }}">Last</a>
      </div>
    </div>
  </div>
</div>
@endsection
