@extends('layouts.admin')
@section('title', 'Bets History & Profit/Loss CRUD - Admin Panel')

@section('content')
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
      <h5 class="fw-bolder m-0"><i class="fas fa-receipt text-pink me-1" style="color:#ec4899;"></i> Bets History &amp; Profit/Loss Management (Full CRUD)</h5>
      <small class="text-muted">Create, Edit, Settle (WON/LOST/VOID) or Delete any Exchange or Casino bet and inspect live Profit &amp; Loss totals.</small>
    </div>
    <button class="btn btn-sm btn-primary fw-bold px-3 py-2" data-bs-toggle="modal" data-bs-target="#createBetModal">
      <i class="fas fa-plus-circle me-1"></i> Create Bet Record
    </button>
  </div>

  <!-- Profit & Loss Summary Cards -->
  <div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
      <div class="adm-stat-card adm-grad-blue">
        <div class="stat-icon"><i class="fas fa-coins"></i></div>
        <div class="stat-label">Total Bet Volume</div>
        <div class="stat-value">{{ number_format($plStats['total_stake'] ?? 0, 2) }} PTI</div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="adm-stat-card adm-grad-green">
        <div class="stat-icon"><i class="fas fa-baseball-ball"></i></div>
        <div class="stat-label">User Exchange P/L</div>
        <div class="stat-value">{{ ($plStats['exchange_pl'] ?? 0) >= 0 ? '+' : '' }}{{ number_format($plStats['exchange_pl'] ?? 0, 2) }} PTI</div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="adm-stat-card adm-grad-purple">
        <div class="stat-icon"><i class="fas fa-dice"></i></div>
        <div class="stat-label">User Casino P/L</div>
        <div class="stat-value">{{ ($plStats['casino_pl'] ?? 0) >= 0 ? '+' : '' }}{{ number_format($plStats['casino_pl'] ?? 0, 2) }} PTI</div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="adm-stat-card adm-grad-red">
        <div class="stat-icon"><i class="fas fa-hand-holding-usd"></i></div>
        <div class="stat-label">Open Exposure &amp; Cash Outs</div>
        <div class="stat-value">{{ number_format($plStats['open_exposure'] ?? 0, 2) }} PTI</div>
        <div class="stat-sub">Cash Outs: {{ $plStats['cashout_count'] ?? 0 }} ({{ ($plStats['cashout_pl'] ?? 0) >= 0 ? '+' : '' }}{{ number_format($plStats['cashout_pl'] ?? 0, 2) }} PTI)</div>
      </div>
    </div>
  </div>

  <div class="adm-card">
    <div class="adm-card-header">
      <form method="GET" action="{{ route('admin.bets') }}" class="d-flex flex-wrap gap-2 align-items-center m-0">
        <input type="text" name="q" value="{{ $q ?? '' }}" class="form-control form-control-sm" style="width:210px;" placeholder="Search Ref, User, Event...">
        <select name="category" class="form-select form-select-sm" style="width:130px;">
          <option value="all" {{ ($category ?? 'all') === 'all' ? 'selected' : '' }}>All Categories</option>
          <option value="exchange" {{ ($category ?? '') === 'exchange' ? 'selected' : '' }}>Sports Exchange</option>
          <option value="casino" {{ ($category ?? '') === 'casino' ? 'selected' : '' }}>Live Casino</option>
        </select>
        <select name="status" class="form-select form-select-sm" style="width:140px;">
          <option value="all" {{ ($status ?? 'all') === 'all' ? 'selected' : '' }}>All Status</option>
          <option value="MATCHED" {{ ($status ?? '') === 'MATCHED' ? 'selected' : '' }}>MATCHED (Open)</option>
          <option value="WON" {{ ($status ?? '') === 'WON' ? 'selected' : '' }}>WON</option>
          <option value="LOST" {{ ($status ?? '') === 'LOST' ? 'selected' : '' }}>LOST</option>
          <option value="CASHOUT" {{ ($status ?? '') === 'CASHOUT' ? 'selected' : '' }}>CASHOUT</option>
        </select>
        <button type="submit" class="btn btn-sm btn-dark fw-bold">Filter</button>
      </form>
      <div class="small fw-bold text-secondary">Total Bets: {{ $bets->total() }}</div>
    </div>

    <div class="adm-card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover table-bordered table-sm small text-center align-middle mb-0">
          <thead>
            <tr>
              <th>Ref</th>
              <th>User</th>
              <th>Category</th>
              <th>Event / Game</th>
              <th>Market</th>
              <th>Selection</th>
              <th>Type</th>
              <th>Odds</th>
              <th>Stake</th>
              <th>P/L</th>
              <th>Status</th>
              <th>Placed At</th>
              <th>Edit / Delete</th>
            </tr>
          </thead>
          <tbody>
            @forelse($bets as $b)
              <tr>
                <td class="fw-bold">{{ $b->bet_ref }}</td>
                <td class="fw-bold">
                  @if($b->user)
                    <a href="{{ route('admin.users.show', $b->user_id) }}" class="text-decoration-none">{{ $b->user->username }}</a>
                  @else
                    -
                  @endif
                </td>
                <td><span class="badge bg-secondary">{{ strtoupper($b->category) }}</span></td>
                <td>{{ $b->event_name }}</td>
                <td>{{ $b->market_name }}</td>
                <td class="fw-bold">{{ $b->selection_name }}</td>
                <td><span class="badge {{ $b->bet_type === 'BACK' ? 'bg-info text-dark' : 'bg-danger' }}">{{ $b->bet_type }}</span></td>
                <td>{{ number_format($b->odds, 2) }}</td>
                <td class="fw-bold">{{ number_format($b->stake, 2) }}</td>
                <td class="fw-bold {{ $b->profit_loss >= 0 ? 'text-success' : 'text-danger' }}">
                  {{ $b->profit_loss >= 0 ? '+' : '' }}{{ number_format($b->profit_loss, 2) }}
                </td>
                <td>
                  <span class="badge {{ $b->status === 'WON' ? 'bg-success' : ($b->status === 'LOST' ? 'bg-danger' : ($b->status === 'CASHOUT' ? 'bg-warning text-dark' : 'bg-primary')) }}">{{ $b->status }}</span>
                </td>
                <td>{{ $b->created_at?->format('d-m H:i:s') }}</td>
                <td>
                  <div class="d-flex flex-wrap justify-content-center gap-1">
                    @if($b->status === 'MATCHED')
                      <button type="button" class="btn btn-sm btn-warning text-dark py-0 px-2 fw-bold"
                              onclick='openCashoutModal(@json($b))' title="Cash Out Bet">
                        <i class="fas fa-hand-holding-usd"></i> Cash Out
                      </button>
                      <form action="{{ route('admin.bets.cashout', $b->id) }}" method="POST" class="m-0">
                        @csrf
                        <input type="hidden" name="action" value="won">
                        <button type="submit" class="btn btn-sm btn-success py-0 px-2 fw-bold" title="Settle as WON">Win</button>
                      </form>
                      <form action="{{ route('admin.bets.cashout', $b->id) }}" method="POST" class="m-0">
                        @csrf
                        <input type="hidden" name="action" value="lost">
                        <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2 fw-bold" title="Settle as LOST">Lose</button>
                      </form>
                    @endif
                    <button type="button" class="btn btn-sm btn-info py-0 px-2 fw-bold text-dark"
                            onclick='openEditBetModal(@json($b))'>
                      <i class="fas fa-edit"></i>
                    </button>
                    <form action="{{ route('admin.bets.delete', $b->id) }}" method="POST" class="m-0" onsubmit="return confirm('Delete bet {{ $b->bet_ref }}?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-danger py-0 px-2"><i class="fas fa-trash"></i></button>
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              <tr><td colspan="13" class="text-muted py-3">No bets found.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="p-2 border-top">{{ $bets->links('pagination::bootstrap-5') }}</div>
  </div>

  <!-- Create Bet Modal -->
  <div class="modal fade" id="createBetModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header py-2 bg-primary text-white">
          <h6 class="modal-title fw-bold m-0"><i class="fas fa-plus-circle me-1"></i> Create Manual Bet Record</h6>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <form action="{{ route('admin.bets.create') }}" method="POST">
          @csrf
          <div class="modal-body">
            <div class="row g-2">
              <div class="col-md-6">
                <label class="small fw-bold">Select User *</label>
                <select name="user_id" class="form-select form-select-sm" required>
                  @foreach($users as $u)
                    <option value="{{ $u->id }}">{{ $u->username }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-6">
                <label class="small fw-bold">Category *</label>
                <select name="category" class="form-select form-select-sm">
                  <option value="exchange">Sports Exchange</option>
                  <option value="casino">Live Casino</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="small fw-bold">Sport / Game Group *</label>
                <input type="text" name="sport_name" value="Cricket" class="form-control form-control-sm" required>
              </div>
              <div class="col-md-6">
                <label class="small fw-bold">Market Name *</label>
                <input type="text" name="market_name" value="Match Odds" class="form-control form-control-sm" required>
              </div>
              <div class="col-12">
                <label class="small fw-bold">Event / Casino Game Name *</label>
                <input type="text" name="event_name" placeholder="India v Australia / HEADS & TAILS" class="form-control form-control-sm" required>
              </div>
              <div class="col-md-6">
                <label class="small fw-bold">Selection / Runner *</label>
                <input type="text" name="selection_name" placeholder="India / HEADS" class="form-control form-control-sm" required>
              </div>
              <div class="col-md-6">
                <label class="small fw-bold">Bet Type *</label>
                <select name="bet_type" class="form-select form-select-sm">
                  <option value="BACK">BACK</option>
                  <option value="LAY">LAY</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">Odds *</label>
                <input type="number" step="0.01" name="odds" value="1.90" class="form-control form-control-sm fw-bold" required>
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">Stake (PTI) *</label>
                <input type="number" step="0.01" name="stake" value="500" class="form-control form-control-sm fw-bold" required>
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">Status *</label>
                <select name="status" class="form-select form-select-sm fw-bold">
                  <option value="MATCHED">MATCHED</option>
                  <option value="WON">WON</option>
                  <option value="LOST">LOST</option>
                </select>
              </div>
            </div>
          </div>
          <div class="modal-footer py-1">
            <button type="submit" class="btn btn-sm btn-primary fw-bold px-4">Create Bet</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Edit / Settle Bet Modal -->
  <div class="modal fade" id="editBetModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header py-2 bg-info text-dark">
          <h6 class="modal-title fw-bold m-0"><i class="fas fa-edit me-1"></i> Edit / Settle Bet Record</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form id="editBetForm" method="POST">
          @csrf
          @method('PUT')
          <div class="modal-body">
            <div class="row g-2">
              <div class="col-12">
                <label class="small fw-bold">Event / Game Name *</label>
                <input type="text" name="event_name" id="ebet_event" class="form-control form-control-sm" required>
              </div>
              <div class="col-md-6">
                <label class="small fw-bold">Market Name *</label>
                <input type="text" name="market_name" id="ebet_market" class="form-control form-control-sm" required>
              </div>
              <div class="col-md-6">
                <label class="small fw-bold">Selection Name *</label>
                <input type="text" name="selection_name" id="ebet_sel" class="form-control form-control-sm" required>
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">Type *</label>
                <select name="bet_type" id="ebet_type" class="form-select form-select-sm">
                  <option value="BACK">BACK</option>
                  <option value="LAY">LAY</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">Odds *</label>
                <input type="number" step="0.01" name="odds" id="ebet_odds" class="form-control form-control-sm fw-bold" required>
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">Stake (PTI) *</label>
                <input type="number" step="0.01" name="stake" id="ebet_stake" class="form-control form-control-sm fw-bold" required>
              </div>
              <div class="col-md-6">
                <label class="small fw-bold">Profit / Loss (PTI) *</label>
                <input type="number" step="0.01" name="profit_loss" id="ebet_pl" class="form-control form-control-sm fw-bold" required>
              </div>
              <div class="col-md-6">
                <label class="small fw-bold">Status *</label>
                <select name="status" id="ebet_status" class="form-select form-select-sm fw-bold">
                  <option value="MATCHED">MATCHED (Open)</option>
                  <option value="WON">WON</option>
                  <option value="LOST">LOST</option>
                  <option value="CASHOUT">CASHOUT</option>
                  <option value="VOID">VOID</option>
                </select>
              </div>
              <div class="col-12">
                <label class="small fw-bold">Result Summary</label>
                <input type="text" name="result_summary" id="ebet_res" class="form-control form-control-sm">
              </div>
              <div class="col-12 mt-2">
                <div class="form-check small">
                  <input class="form-check-input" type="checkbox" name="settle_wallet" value="1" id="chkSettleWallet" checked>
                  <label class="form-check-label fw-bold text-success" for="chkSettleWallet">If changing from MATCHED to WON/LOST, auto-settle User Wallet &amp; Statement</label>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer py-1">
            <button type="submit" class="btn btn-sm btn-primary fw-bold px-4">Update Bet</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Custom Cash Out Modal -->
  <div class="modal fade" id="cashoutBetModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header py-2 bg-warning text-dark">
          <h6 class="modal-title fw-bold m-0"><i class="fas fa-hand-holding-usd me-1"></i> Cash Out Bet</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form id="cashoutBetForm" method="POST">
          @csrf
          <input type="hidden" name="action" value="cashout">
          <div class="modal-body">
            <div class="small fw-bold text-primary mb-2" id="co_bet_title"></div>
            <div class="mb-2">
              <label class="small fw-bold">Cash Out Net Profit / Loss (PTI) *</label>
              <input type="number" step="0.01" name="cashout_pl" id="co_pl" class="form-control form-control-sm fw-bold text-success" required>
              <small class="text-muted" style="font-size:11px;">Positive credits user wallet; negative deducts from stake.</small>
            </div>
            <div class="mb-2">
              <label class="small fw-bold">Statement Remark</label>
              <input type="text" name="remark" id="co_remark" class="form-control form-control-sm" value="Instant Cash Out Settled by Admin">
            </div>
          </div>
          <div class="modal-footer py-1">
            <button type="submit" class="btn btn-sm btn-warning text-dark fw-bold w-100">Confirm Cash Out &amp; Settle Wallet</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    function openCashoutModal(b) {
      document.getElementById('cashoutBetForm').action = `/admin/bets/${b.id}/cashout`;
      document.getElementById('co_bet_title').textContent = `${b.bet_ref}: ${b.event_name} (${b.selection_name} @ ${b.odds})`;
      const autoPl = Math.round((parseFloat(b.stake || 100) * 0.20) * 100) / 100;
      document.getElementById('co_pl').value = autoPl;
      document.getElementById('co_remark').value = `Cash Out Settled [${b.event_name} - ${b.selection_name}]`;
      new bootstrap.Modal(document.getElementById('cashoutBetModal')).show();
    }

    function openEditBetModal(b) {
      document.getElementById('editBetForm').action = `/admin/bets/${b.id}`;
      document.getElementById('ebet_event').value = b.event_name || '';
      document.getElementById('ebet_market').value = b.market_name || '';
      document.getElementById('ebet_sel').value = b.selection_name || '';
      document.getElementById('ebet_type').value = b.bet_type || 'BACK';
      document.getElementById('ebet_odds').value = b.odds ?? 2.00;
      document.getElementById('ebet_stake').value = b.stake ?? 100;
      document.getElementById('ebet_pl').value = b.profit_loss ?? 0;
      document.getElementById('ebet_status').value = b.status || 'MATCHED';
      document.getElementById('ebet_res').value = b.result_summary || '';
      new bootstrap.Modal(document.getElementById('editBetModal')).show();
    }
  </script>
@endsection
