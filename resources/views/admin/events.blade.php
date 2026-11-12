@extends('layouts.admin')
@section('title', 'Sports & Match Events CRUD - Admin Panel')

@section('content')
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
      <h5 class="fw-bolder m-0"><i class="fas fa-baseball-ball text-warning me-1"></i> Sports Categories &amp; Match Events (Full CRUD &amp; Settlement)</h5>
      <small class="text-muted">Create, Edit, Update, Delete matches, toggle In-Play / Open status, or Settle Match Winners to automatically pay matched bets.</small>
    </div>
    <div class="d-flex flex-wrap gap-2">
      <button class="btn btn-sm btn-outline-dark fw-bold px-3 py-2" data-bs-toggle="collapse" data-bs-target="#sportsCrudCollapse">
        <i class="fas fa-list-ol me-1"></i> Manage Sport Categories ({{ $sports->count() }})
      </button>
      <button class="btn btn-sm btn-outline-primary fw-bold px-3 py-2" data-bs-toggle="modal" data-bs-target="#addSportModal">
        <i class="fas fa-trophy me-1"></i> + Add Sport Category
      </button>
      <button class="btn btn-sm btn-primary fw-bold px-3 py-2" data-bs-toggle="modal" data-bs-target="#addEventModal">
        <i class="fas fa-plus me-1"></i> + Add Match Event
      </button>
    </div>
  </div>

  <!-- Collapsible Sports Categories CRUD Table -->
  <div class="collapse mb-3" id="sportsCrudCollapse">
    <div class="adm-card">
      <div class="adm-card-header">
        <h6 class="fw-bold m-0 text-primary"><i class="fas fa-trophy me-1"></i> Sport Categories (Edit Name, Sequence, Active Status or Delete)</h6>
      </div>
      <div class="adm-card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm table-bordered small text-center align-middle mb-0">
            <thead>
              <tr>
                <th>Sport ID</th>
                <th>Sport Name</th>
                <th>Sequence</th>
                <th>Active</th>
                <th>Save / Delete</th>
              </tr>
            </thead>
            <tbody>
              @foreach($sports as $sp)
                <tr>
                  <td class="fw-bold"><code>{{ $sp->sport_id }}</code></td>
                  <td colspan="3" class="p-1">
                    <form action="{{ route('admin.sports.update', $sp->id) }}" method="POST" class="row g-1 align-items-center m-0">
                      @csrf
                      @method('PUT')
                      <div class="col-md-5">
                        <input type="text" name="sport_name" value="{{ $sp->sport_name }}" class="form-control form-control-sm fw-bold">
                      </div>
                      <div class="col-md-3">
                        <input type="number" name="sequence" value="{{ $sp->sequence }}" class="form-control form-control-sm text-center">
                      </div>
                      <div class="col-md-2">
                        <input type="checkbox" name="status" value="1" class="form-check-input" {{ $sp->status ? 'checked' : '' }}>
                      </div>
                      <div class="col-md-2">
                        <button type="submit" class="btn btn-sm btn-primary py-0 px-2 fw-bold w-100">Save</button>
                      </div>
                    </form>
                  </td>
                  <td>
                    <form action="{{ route('admin.sports.delete', $sp->id) }}" method="POST" class="m-0" onsubmit="return confirm('Delete sport {{ addslashes($sp->sport_name) }}?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-danger py-0 px-2"><i class="fas fa-trash"></i></button>
                    </form>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="adm-card">
    <div class="adm-card-header">
      <div class="d-flex flex-wrap gap-1">
        <a href="{{ route('admin.events', ['sport_id' => 'all']) }}" class="btn btn-sm {{ $sportId === 'all' ? 'btn-dark' : 'btn-outline-dark' }} fw-bold">All Sports</a>
        @foreach($sports as $s)
          <a href="{{ route('admin.events', ['sport_id' => $s->sport_id]) }}" class="btn btn-sm {{ $sportId == $s->sport_id ? 'btn-primary' : 'btn-outline-dark' }} fw-bold">
            {{ $s->sport_name }}
          </a>
        @endforeach
      </div>
      <div class="small fw-bold text-secondary">Total Matches: {{ $events->total() }}</div>
    </div>

    <div class="adm-card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover table-bordered table-sm small text-center align-middle mb-0">
          <thead>
            <tr>
              <th>Sport</th>
              <th>Event Name</th>
              <th>Tournament</th>
              <th>Back Odds</th>
              <th>In-Play</th>
              <th>Status</th>
              <th>Settle Winner</th>
              <th>Edit / Delete</th>
            </tr>
          </thead>
          <tbody>
            @foreach($events as $ev)
              @php
                $r1 = $ev->runners_json[0]['back'][0]['price'] ?? 1.90;
                $r2 = $ev->runners_json[1]['back'][0]['price'] ?? 1.90;
              @endphp
              <tr>
                <td><span class="badge bg-secondary">{{ $ev->sport_name }}</span></td>
                <td class="fw-bold text-start ps-2">
                  <a href="{{ route('full.market', ['sportId' => $ev->sport_id, 'eventId' => $ev->event_id]) }}" target="_blank" class="text-decoration-none">
                    {{ $ev->event_name }}
                  </a>
                </td>
                <td>{{ $ev->tournament_name }}</td>
                <td>
                  <span class="badge bg-info text-dark">{{ $r1 }}</span> /
                  <span class="badge bg-info text-dark">{{ $r2 }}</span>
                </td>
                <td>
                  <form action="{{ route('admin.events.toggle', $ev->id) }}" method="POST" class="m-0">
                    @csrf
                    <input type="hidden" name="field" value="in_play">
                    <button type="submit" class="btn btn-sm py-0 px-2 fw-bold {{ $ev->in_play ? 'btn-success' : 'btn-outline-secondary' }}">
                      {{ $ev->in_play ? 'IN-PLAY' : 'UPCOMING' }}
                    </button>
                  </form>
                </td>
                <td>
                  <form action="{{ route('admin.events.toggle', $ev->id) }}" method="POST" class="m-0">
                    @csrf
                    <input type="hidden" name="field" value="status">
                    <button type="submit" class="btn btn-sm py-0 px-2 fw-bold {{ $ev->status === 'OPEN' ? 'btn-primary' : 'btn-danger' }}">
                      {{ $ev->status }}
                    </button>
                  </form>
                </td>
                <td>
                  <form action="{{ route('admin.events.settle', $ev->id) }}" method="POST" class="d-flex gap-1 justify-content-center m-0">
                    @csrf
                    <select name="winner_selection" class="form-select form-select-sm" style="max-width:145px;" required>
                      @foreach(($ev->runners_json ?? []) as $r)
                        <option value="{{ $r['runnerName'] }}">{{ $r['runnerName'] }}</option>
                      @endforeach
                    </select>
                    <button type="submit" class="btn btn-sm btn-warning fw-bold py-0 px-2">Settle</button>
                  </form>
                </td>
                <td>
                  <div class="d-flex justify-content-center gap-1">
                    <button type="button" class="btn btn-sm btn-info py-0 px-2 fw-bold text-dark"
                            onclick='openEditEventModal(@json($ev), {{ $r1 }}, {{ $r2 }})'>
                      <i class="fas fa-edit"></i>
                    </button>
                    <form action="{{ route('admin.events.delete', $ev->id) }}" method="POST" class="m-0" onsubmit="return confirm('Delete match {{ addslashes($ev->event_name) }}?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-danger py-0 px-2"><i class="fas fa-trash"></i></button>
                    </form>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    <div class="p-2 border-top">{{ $events->links('pagination::bootstrap-5') }}</div>
  </div>

  <!-- Add Event Modal -->
  <div class="modal fade" id="addEventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header py-2 bg-primary text-white">
          <h6 class="modal-title fw-bold m-0"><i class="fas fa-plus-circle me-1"></i> Add New Sports Match</h6>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <form action="{{ route('admin.events.create') }}" method="POST">
          @csrf
          <div class="modal-body">
            <div class="row g-2">
              <div class="col-6">
                <label class="small fw-bold">Sport *</label>
                <select name="sport_id" class="form-select form-select-sm">
                  @foreach($sports as $sp)
                    <option value="{{ $sp->sport_id }}">{{ $sp->sport_name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-6">
                <label class="small fw-bold">Tournament *</label>
                <input type="text" name="tournament_name" value="International Series" class="form-control form-control-sm" required>
              </div>
              <div class="col-12">
                <label class="small fw-bold">Match Title *</label>
                <input type="text" name="event_name" placeholder="India v Australia" class="form-control form-control-sm" required>
              </div>
              <div class="col-6">
                <label class="small fw-bold">Team 1 Name *</label>
                <input type="text" name="runner1" placeholder="India" class="form-control form-control-sm" required>
              </div>
              <div class="col-4">
                <label class="small fw-bold">Team 1 Back Odds *</label>
                <input type="number" step="0.01" name="back1" value="1.85" class="form-control form-control-sm" required>
              </div>
              <div class="col-4">
                <label class="small fw-bold">Draw (X) Odds</label>
                <input type="number" step="0.01" name="backX" value="3.45" class="form-control form-control-sm">
              </div>
              <div class="col-4">
                <label class="small fw-bold">Team 2 Back Odds *</label>
                <input type="number" step="0.01" name="back2" value="2.10" class="form-control form-control-sm" required>
              </div>
              <div class="col-6">
                <label class="small fw-bold">Team 2 Name *</label>
                <input type="text" name="runner2" placeholder="Australia" class="form-control form-control-sm" required>
              </div>
              <div class="col-6">
                <label class="small fw-bold">Live Score Pill Summary</label>
                <input type="text" name="short_score" placeholder="e.g. IND 162/3 (17.2) or UCL • 70' • 2-1" class="form-control form-control-sm">
              </div>
              <div class="col-12 d-flex flex-wrap gap-3 mt-2">
                <div class="form-check small">
                  <input class="form-check-input" type="checkbox" name="in_play" value="1" id="chkInPlay" checked>
                  <label class="form-check-label fw-bold" for="chkInPlay">In-Play Live</label>
                </div>
                <div class="form-check small">
                  <input class="form-check-input" type="checkbox" name="is_bookmakers" value="1" id="chkBm" checked>
                  <label class="form-check-label fw-bold" for="chkBm">Bookmaker (BM)</label>
                </div>
                <div class="form-check small">
                  <input class="form-check-input" type="checkbox" name="is_fancy" value="1" id="chkFancy" checked>
                  <label class="form-check-label fw-bold" for="chkFancy">Fancy (F)</label>
                </div>
                <div class="form-check small">
                  <input class="form-check-input" type="checkbox" name="is_sportsbook" value="1" id="chkSb" checked>
                  <label class="form-check-label fw-bold" for="chkSb">Sportsbook (S)</label>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer py-1">
            <button type="submit" class="btn btn-sm btn-primary fw-bold px-4">Create Match</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Edit Event Modal -->
  <div class="modal fade" id="editEventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header py-2 bg-info text-dark">
          <h6 class="modal-title fw-bold m-0"><i class="fas fa-edit me-1"></i> Edit Sports Match</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form id="editEventForm" method="POST">
          @csrf
          @method('PUT')
          <div class="modal-body">
            <div class="row g-2">
              <div class="col-12">
                <label class="small fw-bold">Match Title *</label>
                <input type="text" name="event_name" id="eev_name" class="form-control form-control-sm" required>
              </div>
              <div class="col-md-6">
                <label class="small fw-bold">Tournament *</label>
                <input type="text" name="tournament_name" id="eev_tourn" class="form-control form-control-sm" required>
              </div>
              <div class="col-md-6">
                <label class="small fw-bold">Status *</label>
                <select name="status" id="eev_status" class="form-select form-select-sm fw-bold">
                  <option value="OPEN">OPEN</option>
                  <option value="SUSPENDED">SUSPENDED</option>
                  <option value="CLOSED">CLOSED</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">Runner 1 Back Odds</label>
                <input type="number" step="0.01" name="back1" id="eev_b1" class="form-control form-control-sm fw-bold">
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">Draw (X) Back Odds</label>
                <input type="number" step="0.01" name="backX" id="eev_bx" class="form-control form-control-sm fw-bold">
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">Runner 2 Back Odds</label>
                <input type="number" step="0.01" name="back2" id="eev_b2" class="form-control form-control-sm fw-bold">
              </div>
              <div class="col-12">
                <label class="small fw-bold">Live Score Pill Summary (`shortScoreSummary`)</label>
                <input type="text" name="short_score" id="eev_short_score" class="form-control form-control-sm text-success fw-bold" placeholder="e.g. UCL • 74' • 2-1 (HT 1-0)">
              </div>
              <div class="col-12 mt-2">
                <div class="form-check small">
                  <input class="form-check-input" type="checkbox" name="in_play" value="1" id="eev_inplay">
                  <label class="form-check-label fw-bold" for="eev_inplay">In-Play Live</label>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer py-1">
            <button type="submit" class="btn btn-sm btn-primary fw-bold px-4">Update Match</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Add Sport Category Modal -->
  <div class="modal fade" id="addSportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header py-2 bg-dark text-white">
          <h6 class="modal-title fw-bold m-0">Add Sport Category</h6>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <form action="{{ route('admin.sports.create') }}" method="POST">
          @csrf
          <div class="modal-body">
            <div class="mb-2">
              <label class="small fw-bold">Sport ID *</label>
              <input type="text" name="sport_id" placeholder="e.g. 999" class="form-control form-control-sm" required>
            </div>
            <div class="mb-2">
              <label class="small fw-bold">Sport Name *</label>
              <input type="text" name="sport_name" placeholder="e.g. Kabaddi" class="form-control form-control-sm" required>
            </div>
            <div class="mb-2">
              <label class="small fw-bold">Sequence Order</label>
              <input type="number" name="sequence" value="10" class="form-control form-control-sm">
            </div>
          </div>
          <div class="modal-footer py-1">
            <button type="submit" class="btn btn-sm btn-primary fw-bold w-100">Create Sport</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    function openEditEventModal(ev, b1, b2) {
      document.getElementById('editEventForm').action = `/admin/events/${ev.id}`;
      document.getElementById('eev_name').value = ev.event_name || '';
      document.getElementById('eev_tourn').value = ev.tournament_name || '';
      document.getElementById('eev_status').value = ev.status || 'OPEN';
      document.getElementById('eev_b1').value = b1 || 1.90;
      document.getElementById('eev_b2').value = b2 || 1.90;
      const bx = ev.runners_json && ev.runners_json[2] && ev.runners_json[2].back && ev.runners_json[2].back[0] ? ev.runners_json[2].back[0].price : '';
      document.getElementById('eev_bx').value = bx || '';
      const sj = ev.score_json || {};
      const meta = sj.soccer || sj.tennis || sj.cricbuzz || {};
      document.getElementById('eev_short_score').value = meta.shortScoreSummary || meta.status || '';
      document.getElementById('eev_inplay').checked = !!ev.in_play;
      new bootstrap.Modal(document.getElementById('editEventModal')).show();
    }
  </script>
@endsection
