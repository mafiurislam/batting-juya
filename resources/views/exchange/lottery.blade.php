@extends('layouts.app')
@section('title', 'Lottery - ' . ($event->event_name ?? 'Daily Matka'))

@section('content')
  <div class="wb-report-title mt-1 d-flex justify-content-between align-items-center">
    <span><i class="fas fa-ticket-alt me-1"></i> {{ $event->event_name ?? 'Official Daily Lottery' }}</span>
    <span class="badge bg-success">OPEN • Min 10 / Max 25000</span>
  </div>

  <div class="bg-white border p-3">
    <!-- Lottery Market Tabs matching Winbook24 Lottery Component -->
    <div class="d-flex flex-wrap gap-2 mb-3">
      <button type="button" class="btn btn-sm text-white fw-bold px-3" style="background:#0b59d6;" onclick="selectLotteryTab('ANK OPEN', 9.5, 10)">ANK OPEN (9.5x)</button>
      <button type="button" class="btn btn-sm text-white fw-bold px-3" style="background:#fa386b;" onclick="selectLotteryTab('PANA OPEN', 140.0, 20)">PANA OPEN (140x)</button>
      <button type="button" class="btn btn-sm text-white fw-bold px-3" style="background:#ff7800;" onclick="selectLotteryTab('JODI (OC)', 95.0, 20)">JODI (OC) (95x)</button>
      <button type="button" class="btn btn-sm text-white fw-bold px-3" style="background:#7840b7;" onclick="selectLotteryTab('ANK CLOSE', 9.5, 10)">ANK CLOSE (9.5x)</button>
      <button type="button" class="btn btn-sm text-white fw-bold px-3" style="background:#15926d;" onclick="selectLotteryTab('PANA CLOSE', 140.0, 20)">PANA CLOSE (140x)</button>
    </div>

    <h6 class="fw-bold mb-2">Market: <span id="lotMarketLabel" class="text-primary">ANK OPEN</span> (Odds: <span id="lotOddsLabel">9.50</span>x)</h6>
    <p class="text-muted small mb-2">Click any number below to open the Bet Slip and place your Lottery ticket.</p>

    <div id="lotNumberGrid" class="row g-2">
      @for($n = 0; $n <= 9; $n++)
        <div class="col-3 col-md-2">
          <button type="button" class="btn btn-outline-primary w-100 py-3 fw-bold fs-5"
                  onclick="pickLotteryNumber('{{ $n }}')">
            {{ $n }}
          </button>
        </div>
      @endfor
    </div>
  </div>
@endsection

@push('scripts')
<script>
  let currentLotTab = 'ANK OPEN';
  let currentLotOdds = 9.5;

  function selectLotteryTab(tab, odds, count) {
    currentLotTab = tab;
    currentLotOdds = odds;
    document.getElementById('lotMarketLabel').textContent = tab;
    document.getElementById('lotOddsLabel').textContent = odds.toFixed(2);
    const grid = document.getElementById('lotNumberGrid');
    let html = '';
    for (let i = 0; i < count; i++) {
      const label = count > 10 ? String(i).padStart(2, '0') : String(i);
      html += `<div class="col-3 col-md-2">
        <button type="button" class="btn btn-outline-dark w-100 py-2 fw-bold fs-6" onclick="pickLotteryNumber('${label}')">${label}</button>
      </div>`;
    }
    grid.innerHTML = html;
  }

  function pickLotteryNumber(num) {
    WB24.openBetSlip({
      eventId: '{{ $event->event_id ?? "66104001" }}',
      eventName: '{{ addslashes($event->event_name ?? "Daily Lottery") }}',
      marketName: currentLotTab,
      selectionName: `${currentLotTab} #${num}`,
      betType: 'BACK',
      odds: currentLotOdds,
      category: 'lottery'
    });
  }
</script>
@endpush

