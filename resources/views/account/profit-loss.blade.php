@extends('layouts.app')
@section('title', 'Profit & Loss - winbook24')

@section('content')
<div class="wb-filter-card">
  <form action="{{ route('profit.loss') }}" method="GET" id="plFilterForm">
    <input type="hidden" name="per_page" id="plPerPageInput" value="{{ $perPage ?? 10 }}">
    <div class="row g-2 align-items-center">
      <div class="col-md-3">
        <select name="data_source" class="form-select">
          <option value="" disabled>Data Source</option>
          <option value="LIVE" {{ ($dataSource ?? 'LIVE') === 'LIVE' ? 'selected' : '' }}>LIVE DATA</option>
          <option value="BACKUP" {{ ($dataSource ?? '') === 'BACKUP' ? 'selected' : '' }}>BACKUP DATA</option>
          <option value="OLD" {{ ($dataSource ?? '') === 'OLD' ? 'selected' : '' }}>OLD DATA</option>
        </select>
      </div>
      <div class="col-md-3">
        <div class="input-group">
          <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
          <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
        </div>
      </div>
      <div class="col-md-3">
        <div class="input-group">
          <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
          <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
        </div>
      </div>
      <div class="col-md-3">
        <button type="submit" class="wb-btn-get">Get P&amp;L</button>
      </div>
    </div>
  </form>
</div>

<div class="wb-acct-card">
  <div class="wb-acct-head">
    <span>Profit/Loss</span>
  </div>
  <div class="p-3">
    <div class="wb-dt-controls">
      <span>Show</span>
      <select onchange="document.getElementById('plPerPageInput').value=this.value;document.getElementById('plFilterForm').submit();">
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
            <th>Profit/Loss <span class="dt-sort">⇅</span></th>
            <th>Commission <span class="dt-sort">⇅</span></th>
            <th>Total P&amp;L <span class="dt-sort">⇅</span></th>
          </tr>
        </thead>
        <tbody>
          @forelse($bySport as $sp)
            <tr>
              <td class="fw-bold">{{ $sp['sport_name'] }}</td>
              <td class="fw-bold {{ $sp['profit_loss'] >= 0 ? 'text-success' : 'text-danger' }}">
                {{ number_format($sp['profit_loss'], 2) }}
              </td>
              <td>{{ number_format($sp['commission'] ?? 0, 2) }}</td>
              <td class="fw-bold {{ $sp['profit_loss'] >= 0 ? 'text-success' : 'text-danger' }}">
                {{ number_format($sp['profit_loss'], 2) }}
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="text-center py-2">No data!</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="wb-dt-footer">
      <div>
        Showing {{ $bySport->count() > 0 ? 1 : 0 }} to {{ $bySport->count() }} of {{ $bySport->count() }} entries
      </div>
      <div class="wb-dt-pagination">
        <span>First</span>
        <span>Previous</span>
        @if($bySport->count() > 0)
          <a href="javascript:void(0)" class="active-page">1</a>
        @endif
        <span>Next</span>
        <span>Last</span>
      </div>
    </div>
  </div>
</div>
@endsection
