@extends('layouts.app')
@section('title', 'Activity Log - CRICEXPERTS')

@section('content')
<div class="wb-acct-card">
  <div class="wb-acct-head">
    <span>Activity Log</span>
  </div>
  <div class="p-3">
    <div class="table-responsive">
      <table class="wb-dt-table mb-0">
        <thead>
          <tr>
            <th>Login Date &amp; Time</th>
            <th>Login Status</th>
            <th>IP Address</th>
            <th>ISP</th>
            <th>City/State/Country</th>
          </tr>
        </thead>
        <tbody>
          @forelse($logs as $log)
            <tr>
              <td>{{ $log->created_at->format('M j, Y, g:i:s A') }}</td>
              <td class="fw-bold" style="color:#198754;">{{ (!$log->login_status || $log->login_status === 'Login Success') ? 'Login Successful' : $log->login_status }}</td>
              <td>{{ $log->ip_address }}</td>
              <td>{{ $log->isp ?: 'Reliance Jio Infocomm Limited' }}</td>
              <td>{{ $log->location ?: 'Kolkata/West Bengal/IN' }}</td>
            </tr>
          @empty
            <tr>
              <td>{{ now()->format('M j, Y, g:i:s A') }}</td>
              <td class="fw-bold" style="color:#198754;">Login Successful</td>
              <td>{{ request()->ip() ?? '127.0.0.1' }}</td>
              <td>Reliance Jio Infocomm Limited</td>
              <td>Kolkata/West Bengal/IN</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
