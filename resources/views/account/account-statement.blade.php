@extends('layouts.app')
@section('title', 'Account Statement - winbook24')

@section('content')
<div class="wb-filter-card">
  <form action="{{ route('account.statement') }}" method="GET" id="stmtFilterForm">
    <input type="hidden" name="per_page" id="stmtPerPageInput" value="{{ $perPage ?? 10 }}">
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
        <button type="submit" class="wb-btn-get">Get Statement</button>
      </div>
    </div>
  </form>
</div>

<div class="wb-acct-card">
  <div class="wb-acct-head">
    <span>Account Statement</span>
  </div>
  <div class="p-3">
    <div class="wb-dt-controls">
      <span>Show</span>
      <select onchange="document.getElementById('stmtPerPageInput').value=this.value;document.getElementById('stmtFilterForm').submit();">
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
            <th>Date/Time <span class="dt-sort">⇅</span></th>
            <th>Deposit <span class="dt-sort">⇅</span></th>
            <th>Withdraw <span class="dt-sort">⇅</span></th>
            <th>Balance <span class="dt-sort">⇅</span></th>
            <th>Remarks <span class="dt-sort">⇅</span></th>
            <th>From/To <span class="dt-sort">⇅</span></th>
          </tr>
        </thead>
        <tbody>
          @forelse($statements as $row)
            <tr>
              <td>{{ $row->created_at->format('M j, Y, g:i:s A') }}</td>
              <td class="fw-bold text-success">{{ $row->credit > 0 ? number_format($row->credit, 2) : '-' }}</td>
              <td class="fw-bold text-danger">{{ $row->debit > 0 ? '(' . number_format($row->debit, 2) . ')' : '-' }}</td>
              <td class="fw-bold">{{ number_format($row->balance, 2) }}</td>
              <td>{{ $row->remark }}</td>
              <td>{{ $row->from_to }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center py-2">No data!</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="wb-dt-footer">
      <div>
        Showing {{ $statements->firstItem() ?? 0 }} to {{ $statements->lastItem() ?? 0 }} of {{ $statements->total() }} entries
      </div>
      <div class="wb-dt-pagination">
        <a href="{{ $statements->url(1) }}">First</a>
        <a href="{{ $statements->previousPageUrl() ?? 'javascript:void(0)' }}">Previous</a>
        @if($statements->total() > 0)
          <a href="{{ $statements->url($statements->currentPage()) }}" class="active-page">{{ $statements->currentPage() }}</a>
        @endif
        <a href="{{ $statements->nextPageUrl() ?? 'javascript:void(0)' }}">Next</a>
        <a href="{{ $statements->url($statements->lastPage()) }}">Last</a>
      </div>
    </div>
  </div>
</div>
@endsection
