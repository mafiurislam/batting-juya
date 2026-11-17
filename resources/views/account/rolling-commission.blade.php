@extends('layouts.app')
@section('title', 'Rolling Commission - CRICEXPERTS')

@section('content')
<div class="wb-filter-card">
  <form action="{{ route('rolling.commission') }}" method="GET">
    <div class="row g-2 mb-2">
      <div class="col-md-4">
        <select name="data_source" class="form-select">
          <option value="" disabled {{ empty($dataSource) ? 'selected' : '' }}>Data Source</option>
          <option value="LIVE" {{ ($dataSource ?? 'LIVE') === 'LIVE' ? 'selected' : '' }}>LIVE DATA</option>
          <option value="BACKUP" {{ ($dataSource ?? '') === 'BACKUP' ? 'selected' : '' }}>BACKUP DATA</option>
          <option value="OLD" {{ ($dataSource ?? '') === 'OLD' ? 'selected' : '' }}>OLD DATA</option>
        </select>
      </div>
      <div class="col-md-4">
        <select name="sport_id" class="form-select">
          <option value="4" {{ ($sportId ?? '4') == '4' ? 'selected' : '' }}>Cricket</option>
          <option value="2" {{ ($sportId ?? '') == '2' ? 'selected' : '' }}>Tennis</option>
          <option value="1" {{ ($sportId ?? '') == '1' ? 'selected' : '' }}>Soccer</option>
          <option value="99" {{ ($sportId ?? '') == '99' ? 'selected' : '' }}>Casino</option>
        </select>
      </div>
      <div class="col-md-4">
        <div class="input-group">
          <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
          <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
        </div>
      </div>
    </div>
    <div class="row g-2">
      <div class="col-md-4">
        <div class="input-group">
          <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
          <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
        </div>
      </div>
      <div class="col-md-4">
        <button type="submit" class="wb-btn-get">Get Commission</button>
      </div>
    </div>
  </form>
</div>

<div class="wb-acct-card">
  <div class="wb-acct-head">
    <span>Rolling Commission</span>
  </div>
  <div class="p-3">
    <div class="table-responsive">
      <table class="wb-dt-table mb-0">
        <thead>
          <tr>
            <th>Type</th>
            <th>Total Stack</th>
            <th>Total Commission</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td colspan="3" class="text-center py-2">No data!</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
