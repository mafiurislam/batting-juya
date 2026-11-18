@extends('layouts.app')
@section('title', 'Bets History - CRICEXPERTS')

@section('content')
@if(isset($openBets) && $openBets->count() > 0)
<div class="wb-acct-card mb-3">
  <div class="wb-acct-head d-flex justify-content-between align-items-center">
    <span>Open / Active Bets ({{ $openBets->count() }})</span>
    <span class="badge bg-warning text-dark">Live Exposure</span>
  </div>
  <div class="p-2">
    <div class="table-responsive">
      <table class="wb-dt-table">
        <thead>
          <tr>
            <th>Event</th>
            <th>Market</th>
            <th>Selection</th>
            <th>Type</th>
            <th>Odds</th>
            <th>Stake</th>
            <th>To Win</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          @foreach($openBets as $ob)
            <tr id="openBetRow_{{ $ob->id }}" style="background:{{ in_array($ob->bet_type, ['LAY', 'NO']) ? '#faa9ba' : '#72bbef' }};">
              <td>{{ $ob->event_name }}</td>
              <td>{{ $ob->market_name }}</td>
              <td class="fw-bold">{{ $ob->selection_name }}</td>
              <td class="fw-bold">{{ $ob->bet_type }}</td>
              <td class="fw-bold">{{ number_format($ob->odds, 2) }}</td>
              <td class="fw-bold">{{ number_format($ob->stake, 2) }}</td>
              <td class="fw-bold text-success">+{{ number_format($ob->potential_profit, 2) }}</td>
              <td>
                <div class="d-flex justify-content-center gap-1">
                  <button type="button" class="btn btn-warning btn-sm py-0 px-2 fw-bold" style="font-size:11px;" onclick="WB24.cashoutBet({{ $ob->id }}, 'cashout')">Cashout</button>
                  <button type="button" class="btn btn-success btn-sm py-0 px-2 fw-bold" style="font-size:11px;" onclick="WB24.cashoutBet({{ $ob->id }}, 'instant_settle')">Settle</button>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endif

<div class="wb-filter-card">
  <form action="{{ route('bet.history') }}" method="GET" id="bhFilterForm">
    <input type="hidden" name="per_page" id="bhPerPageInput" value="{{ $perPage ?? 10 }}">
    <div class="row g-2 mb-2">
      <div class="col-md-4">
        <select name="data_source" class="form-select">
          <option value="" disabled>Data Source</option>
          <option value="LIVE" {{ ($dataSource ?? 'LIVE') === 'LIVE' ? 'selected' : '' }}>LIVE DATA</option>
          <option value="BACKUP" {{ ($dataSource ?? '') === 'BACKUP' ? 'selected' : '' }}>BACKUP DATA</option>
          <option value="OLD" {{ ($dataSource ?? '') === 'OLD' ? 'selected' : '' }}>OLD DATA</option>
        </select>
      </div>
      <div class="col-md-4">
        <select name="sport_id" class="form-select">
          <option value="all" {{ ($sportId ?? '4') === 'all' ? 'selected' : '' }}>Choose Sport</option>
          <option value="4" {{ ($sportId ?? '4') == '4' ? 'selected' : '' }}>Cricket</option>
          <option value="2" {{ ($sportId ?? '') == '2' ? 'selected' : '' }}>Tennis</option>
          <option value="1" {{ ($sportId ?? '') == '1' ? 'selected' : '' }}>Soccer</option>
          <option value="99" {{ ($sportId ?? '') == '99' ? 'selected' : '' }}>Casino</option>
        </select>
      </div>
      <div class="col-md-4">
        <select name="status" class="form-select">
          <option value="all" {{ ($status ?? 'SETTLED') === 'all' ? 'selected' : '' }}>Choose Type</option>
          <option value="SETTLED" {{ ($status ?? 'SETTLED') === 'SETTLED' ? 'selected' : '' }}>Settle</option>
          <option value="UNSETTLED" {{ ($status ?? '') === 'UNSETTLED' ? 'selected' : '' }}>Unsettle</option>
          <option value="VOID" {{ ($status ?? '') === 'VOID' ? 'selected' : '' }}>Void</option>
        </select>
      </div>
    </div>
    <div class="row g-2">
      <div class="col-md-4">
        <div class="input-group">
          <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
          <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
        </div>
      </div>
      <div class="col-md-4">
        <div class="input-group">
          <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
          <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
        </div>
      </div>
      <div class="col-md-4">
        <button type="submit" class="wb-btn-get">Get History</button>
      </div>
    </div>
  </form>
</div>

<div class="wb-acct-card">
  <div class="wb-acct-head">
    <span>Bet History</span>
  </div>
  <div class="p-3">
    <div class="wb-dt-controls">
      <span>Show</span>
      <select onchange="document.getElementById('bhPerPageInput').value=this.value;document.getElementById('bhFilterForm').submit();">
        @foreach([10, 25, 50, 100] as $n)
          <option value="{{ $n }}" {{ ($perPage ?? 10) == $n ? 'selected' : '' }}>{{ $n }}</option>
        @endforeach
      </select>
      <span>entries</span>
    </div>

    <div class="table-responsive">
      <table class="wb-dt-table">
        <thead>
          <tr>
            <th>Sport Name <span class="dt-sort">⇅</span></th>
            <th>Event Name <span class="dt-sort">⇅</span></th>
            <th>Market name <span class="dt-sort">⇅</span></th>
            <th>Selection <span class="dt-sort">⇅</span></th>
            <th>Type <span class="dt-sort">⇅</span></th>
            <th>Odds Req. <span class="dt-sort">⇅</span></th>
            <th>Stack <span class="dt-sort">⇅</span></th>
            <th>Place Time <span class="dt-sort">⇅</span></th>
            <th>Settle Time <span class="dt-sort">⇅</span></th>
          </tr>
        </thead>
        <tbody>
          @forelse($bets as $bet)
            <tr style="background:{{ $bet->bet_type === 'LAY' ? '#faa9ba' : '#72bbef' }};">
              <td>{{ $bet->sport_name }}</td>
              <td>{{ $bet->event_name }}</td>
              <td>{{ $bet->market_name }}</td>
              <td class="fw-bold">{{ $bet->selection_name }}</td>
              <td class="fw-bold">{{ $bet->bet_type }}</td>
              <td class="fw-bold">{{ number_format($bet->odds, 2) }}</td>
              <td class="fw-bold">{{ number_format($bet->stake, 2) }}</td>
              <td>{{ $bet->created_at->format('M j, Y, g:i:s A') }}</td>
              <td>{{ $bet->settled_at ? $bet->settled_at->format('M j, Y, g:i:s A') : '-' }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="9" class="text-center py-2">No data!</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="wb-dt-footer">
      <div>
        Showing {{ $bets->firstItem() ?? 0 }} to {{ $bets->lastItem() ?? 0 }} of {{ $bets->total() }} entries
      </div>
      <div class="wb-dt-pagination">
        <a href="{{ $bets->url(1) }}">First</a>
        <a href="{{ $bets->previousPageUrl() ?? 'javascript:void(0)' }}">Previous</a>
        @if($bets->total() > 0)
          <a href="{{ $bets->url($bets->currentPage()) }}" class="active-page">{{ $bets->currentPage() }}</a>
        @endif
        <a href="{{ $bets->nextPageUrl() ?? 'javascript:void(0)' }}">Next</a>
        <a href="{{ $bets->url($bets->lastPage()) }}">Last</a>
      </div>
    </div>
  </div>
</div>
@endsection
