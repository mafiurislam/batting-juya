@extends('layouts.admin')
@section('title', 'Live Scoreboards, Ball-By-Ball & Multi-Markets CRUD - Admin Panel')

@section('content')
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
      <h5 class="fw-bolder m-0">
        <i class="fas fa-satellite-dish text-danger me-1"></i> Live Scoreboards (Cricket, Football/UEFA, Tennis), Ball-By-Ball &amp; Multi-Markets
      </h5>
      <small class="text-muted">
        Full Create, Edit, Update &amp; Delete control over Live Score Pills, Full-Market Scoreboards, Ball-By-Ball Odds &amp; Video Results, and Pinned Multi-Markets.
      </small>
    </div>
    <div class="d-flex flex-wrap gap-2">
      <button class="btn btn-sm btn-primary fw-bold px-3 py-2" data-bs-toggle="modal" data-bs-target="#liveScoreModal" onclick="resetLiveScoreModal()">
        <i class="fas fa-plus-circle me-1"></i> + Create / Edit Live Scoreboard
      </button>
      <button class="btn btn-sm btn-warning text-dark fw-bold px-3 py-2" data-bs-toggle="modal" data-bs-target="#bbbRunnerModal" onclick="resetBbbRunnerModal()">
        <i class="fas fa-baseball-ball me-1"></i> + Add Ball By Ball Runner
      </button>
    </div>
  </div>

  <!-- 1. One-Click External Live Feed Sync Strip -->
  <div class="adm-card mb-4">
    <div class="adm-card-header">
      <div>
        <h6 class="fw-bold m-0 text-primary"><i class="fas fa-sync-alt me-1"></i> 1. Real-Time External Live Score Feed Synchronization</h6>
        <small class="text-muted">Pull fresh live matches, scores, and odds from Cricbuzz, UEFA Champions League, and Livescore.in (Football &amp; Tennis).</small>
      </div>
      <div class="d-flex flex-wrap gap-2">
        <form action="{{ route('admin.live.scores.sync') }}" method="POST" class="m-0">
          @csrf
          <input type="hidden" name="feed" value="all">
          <button type="submit" class="btn btn-sm btn-success fw-bold">
            <i class="fas fa-bolt me-1"></i> Sync All Live Sports Now
          </button>
        </form>
        <form action="{{ route('admin.live.scores.sync') }}" method="POST" class="m-0">
          @csrf
          <input type="hidden" name="feed" value="cricket">
          <button type="submit" class="btn btn-sm btn-outline-primary fw-bold">
            <i class="fas fa-baseball-ball me-1"></i> Sync Cricbuzz Cricket
          </button>
        </form>
        <form action="{{ route('admin.live.scores.sync') }}" method="POST" class="m-0">
          @csrf
          <input type="hidden" name="feed" value="football">
          <button type="submit" class="btn btn-sm btn-outline-dark fw-bold">
            <i class="fas fa-futbol me-1"></i> Sync UEFA &amp; Football Live
          </button>
        </form>
        <form action="{{ route('admin.live.scores.sync') }}" method="POST" class="m-0">
          @csrf
          <input type="hidden" name="feed" value="tennis">
          <button type="submit" class="btn btn-sm btn-outline-info text-dark fw-bold">
            <i class="fas fa-table-tennis me-1"></i> Sync Livescore Tennis
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- 2. Ball By Ball (/ballByBall/4/3544687543453) Full CRUD & Instant Result Controller -->
  <div class="row g-3 mb-4">
    <div class="col-lg-8">
      <div class="adm-card h-100">
        <div class="adm-card-header">
          <div>
            <h6 class="fw-bold m-0" style="color:#d97706;">
              <i class="fas fa-video me-1"></i> 2. Ball By Ball (`/ballByBall/4/3544687543453`) Runners &amp; Odds CRUD
            </h6>
            <small class="text-muted">Edit 0 RUNS, 1 RUNS, 2 RUNS, 3 RUNS, 4 RUNS, 6 RUNS, WICKET, EXTRA RUNS odds, liquidity &amp; status.</small>
          </div>
          <a href="{{ route('ball.by.ball', ['sportId' => '4', 'eventId' => '3544687543453']) }}" target="_blank" class="btn btn-sm btn-outline-dark fw-bold">
            <i class="fas fa-external-link-alt me-1"></i> Open Ball By Ball Page
          </a>
        </div>
        <div class="adm-card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-bordered table-sm small text-center align-middle mb-0">
              <thead>
                <tr>
                  <th>Selection ID</th>
                  <th>Runner Name</th>
                  <th>Back Odds</th>
                  <th>Liquidity Size</th>
                  <th>Status</th>
                  <th>Quick Settle</th>
                  <th>Edit / Delete</th>
                </tr>
              </thead>
              <tbody>
                @foreach($bbbRunners as $br)
                  <tr>
                    <td><code>{{ $br['selectionId'] }}</code></td>
                    <td class="fw-bold text-start ps-2">{{ $br['name'] }}</td>
                    <td><span class="badge bg-success fs-6">{{ number_format((float)$br['price'], 2) }}</span></td>
                    <td class="fw-bold">{{ number_format((float)$br['size']) }}</td>
                    <td>
                      <span class="badge {{ $br['status'] === 'ONLINE' ? 'bg-primary' : 'bg-danger' }}">{{ $br['status'] }}</span>
                    </td>
                    <td>
                      <form action="{{ route('admin.live.scores.bbb.settle') }}" method="POST" class="m-0">
                        @csrf
                        <input type="hidden" name="winner_runner" value="{{ $br['name'] }}">
                        <button type="submit" class="btn btn-sm btn-warning text-dark py-0 px-2 fw-bold">
                          Settle {{ $br['name'] }}
                        </button>
                      </form>
                    </td>
                    <td>
                      <div class="d-flex justify-content-center gap-1">
                        <button type="button" class="btn btn-sm btn-info py-0 px-2 fw-bold text-dark"
                                onclick='openEditBbbRunner(@json($br), {{ $bbbMarket["min"] ?? 100 }}, {{ $bbbMarket["max"] ?? 100000 }})'>
                          <i class="fas fa-edit"></i>
                        </button>
                        <form action="{{ route('admin.live.scores.bbb.delete', $br['selectionId']) }}" method="POST" class="m-0" onsubmit="return confirm('Remove runner {{ addslashes($br['name']) }}?');">
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
      </div>
    </div>

    <!-- Ball By Ball Recent Results & Multi-Markets Pinner -->
    <div class="col-lg-4">
      <div class="adm-card mb-3">
        <div class="adm-card-header">
          <h6 class="fw-bold m-0 text-dark"><i class="fas fa-bowling-ball text-warning me-1"></i> Ball By Ball Recent Results</h6>
        </div>
        <div class="adm-card-body">
          <div class="d-flex flex-wrap gap-2 mb-3">
            @foreach($bbbResults as $res)
              @php
                $rBadge = $res['R'] ?? ($res['result'] ?? '0');
                if (strlen($rBadge) > 2) {
                    $rBadge = substr($rBadge, 0, 1);
                }
                $bgCol = $rBadge === 'W' ? '#dc2626' : ($rBadge === '4' || $rBadge === '6' ? '#16a34a' : '#ca8a04');
              @endphp
              <span class="badge rounded-circle d-inline-flex align-items-center justify-content-center fw-bold"
                    style="width:30px;height:30px;background:{{ $bgCol }};color:#fff;font-size:12px;"
                    title="Round {{ $res['marketId'] ?? '' }}: {{ $res['winnerName'] ?? '' }}">
                {{ $rBadge }}
              </span>
            @endforeach
          </div>
          <small class="text-muted d-block">Click any <strong>"Settle ..."</strong> button on the left to record a new ball result and automatically settle open Ball By Ball bets.</small>
        </div>
      </div>

      <!-- 3. User Pinned Multi-Markets CRUD -->
      <div class="adm-card">
        <div class="adm-card-header">
          <h6 class="fw-bold m-0 text-primary"><i class="fas fa-thumbtack me-1"></i> 3. User Multi-Markets Pins</h6>
        </div>
        <div class="adm-card-body">
          <form action="{{ route('admin.live.scores.mm.create') }}" method="POST" class="row g-2 mb-3">
            @csrf
            <div class="col-5">
              <select name="user_id" class="form-select form-select-sm" required>
                @foreach($users as $u)
                  <option value="{{ $u->id }}">{{ $u->username }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-5">
              <select name="event_id" class="form-select form-select-sm" required>
                @foreach($allEventsList->take(40) as $evItem)
                  <option value="{{ $evItem->event_id }}">{{ $evItem->event_name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-2">
              <button type="submit" class="btn btn-sm btn-primary fw-bold w-100">Pin</button>
            </div>
          </form>

          <div class="table-responsive" style="max-height:175px;overflow-y:auto;">
            <table class="table table-sm table-bordered small text-center align-middle mb-0">
              <thead>
                <tr>
                  <th>User</th>
                  <th>Event ID</th>
                  <th>Unpin</th>
                </tr>
              </thead>
              <tbody>
                @forelse($multiMarkets as $mm)
                  <tr>
                    <td class="fw-bold">{{ $mm->user->username ?? ('#' . $mm->user_id) }}</td>
                    <td><code>{{ $mm->event_id }}</code></td>
                    <td>
                      <form action="{{ route('admin.live.scores.mm.delete', $mm->id) }}" method="POST" class="m-0">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger py-0 px-2"><i class="fas fa-times"></i></button>
                      </form>
                    </td>
                  </tr>
                @empty
                  <tr><td colspan="3" class="text-muted">No pinned markets yet.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- 4. All Live Matches Scoreboard Table (Cricket, Football/UEFA, Tennis) -->
  <div class="adm-card">
    <div class="adm-card-header">
      <div class="d-flex flex-wrap gap-1 align-items-center">
        <a href="{{ route('admin.live.scores', ['sport_id' => 'all']) }}" class="btn btn-sm {{ $sportId === 'all' ? 'btn-dark' : 'btn-outline-dark' }} fw-bold">All Sports</a>
        @foreach($sports as $s)
          <a href="{{ route('admin.live.scores', ['sport_id' => $s->sport_id]) }}" class="btn btn-sm {{ (string)$sportId === (string)$s->sport_id ? 'btn-primary' : 'btn-outline-dark' }} fw-bold">
            {{ $s->sport_name }}
          </a>
        @endforeach
      </div>
      <form method="GET" action="{{ route('admin.live.scores') }}" class="d-flex gap-2 m-0">
        <input type="hidden" name="sport_id" value="{{ $sportId }}">
        <input type="text" name="q" value="{{ $q }}" class="form-control form-control-sm" style="width:220px;" placeholder="Search match or tournament...">
        <button type="submit" class="btn btn-sm btn-dark fw-bold">Search</button>
      </form>
    </div>

    <div class="adm-card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover table-bordered table-sm small text-center align-middle mb-0">
          <thead>
            <tr>
              <th>Sport</th>
              <th>Event ID</th>
              <th>Match Name</th>
              <th>Tournament</th>
              <th>Live Score Pill (`.wb-cricbuzz-score-pill`)</th>
              <th>Score 1 / 2</th>
              <th>1 | X | 2 Odds</th>
              <th>Edit Scoreboard / Clear</th>
            </tr>
          </thead>
          <tbody>
            @foreach($events as $ev)
              @php
                $sj = is_array($ev->score_json) ? $ev->score_json : [];
                $meta = $sj['soccer'] ?? ($sj['tennis'] ?? ($sj['cricbuzz'] ?? []));
                $shortPill = $meta['shortScoreSummary'] ?? ($meta['status'] ?? '-');
                $sc1 = $meta['score1'] ?? ($meta['team1']['score'] ?? '0');
                $sc2 = $meta['score2'] ?? ($meta['team2']['score'] ?? '0');
                $b1 = $ev->runners_json[0]['back'][0]['price'] ?? 1.85;
                $b2 = $ev->runners_json[1]['back'][0]['price'] ?? 2.10;
                $bx = $ev->runners_json[2]['back'][0]['price'] ?? '-';
              @endphp
              <tr>
                <td><span class="badge bg-secondary">{{ $ev->sport_name }}</span></td>
                <td><code>{{ $ev->event_id }}</code></td>
                <td class="fw-bold text-start ps-2">
                  <a href="{{ route('full.market', ['sportId' => $ev->sport_id, 'eventId' => $ev->event_id]) }}" target="_blank" class="text-decoration-none">
                    {{ $ev->event_name }}
                  </a>
                </td>
                <td>{{ $ev->tournament_name }}</td>
                <td>
                  @if($shortPill && $shortPill !== '-')
                    <span class="badge bg-dark text-warning px-2 py-1">{{ $shortPill }}</span>
                  @else
                    <span class="text-muted">No Pill</span>
                  @endif
                </td>
                <td class="fw-bold text-success">{{ $sc1 }} &mdash; {{ $sc2 }}</td>
                <td>
                  <span class="badge bg-info text-dark">{{ $b1 }}</span>
                  @if($bx !== '-')
                    <span class="badge bg-warning text-dark">{{ $bx }}</span>
                  @endif
                  <span class="badge bg-info text-dark">{{ $b2 }}</span>
                </td>
                <td>
                  <div class="d-flex justify-content-center gap-1">
                    <button type="button" class="btn btn-sm btn-primary py-0 px-2 fw-bold"
                            onclick='openLiveScoreEditor(@json($ev), @json($meta), "{{ $b1 }}", "{{ $b2 }}", "{{ $bx !== "-" ? $bx : 3.45 }}")'>
                      <i class="fas fa-edit me-1"></i> Scoreboard
                    </button>
                    <form action="{{ route('admin.live.scores.delete', $ev->id) }}" method="POST" class="m-0" onsubmit="return confirm('Clear custom scoreboard for {{ addslashes($ev->event_name) }}?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" title="Clear Scoreboard"><i class="fas fa-eraser"></i></button>
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

  <!-- Create / Edit Live Scoreboard Modal -->
  <div class="modal fade" id="liveScoreModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header py-2 bg-primary text-white">
          <h6 class="modal-title fw-bold m-0"><i class="fas fa-satellite-dish me-1"></i> Create / Edit Match Live Scoreboard &amp; Odds</h6>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <form action="{{ route('admin.live.scores.save') }}" method="POST">
          @csrf
          <div class="modal-body">
            <div class="row g-2">
              <div class="col-md-4">
                <label class="small fw-bold">Sport *</label>
                <select name="sport_id" id="ls_sport_id" class="form-select form-select-sm fw-bold">
                  <option value="1">Soccer / Football (1)</option>
                  <option value="4">Cricket (4)</option>
                  <option value="2">Tennis (2)</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">Event ID *</label>
                <input type="text" name="event_id" id="ls_event_id" value="ucl_{{ rand(10000,99999) }}" class="form-control form-control-sm font-monospace" required>
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">Tournament Name *</label>
                <input type="text" name="tournament_name" id="ls_tournament" value="UEFA Champions League" class="form-control form-control-sm" required>
              </div>
              <div class="col-md-6">
                <label class="small fw-bold">Match Title (Team 1 v Team 2) *</label>
                <input type="text" name="event_name" id="ls_event_name" placeholder="Real Madrid v Bayern Munich" class="form-control form-control-sm fw-bold" required>
              </div>
              <div class="col-md-6">
                <label class="small fw-bold">Live Score Pill Summary (`shortScoreSummary`) *</label>
                <input type="text" name="short_summary" id="ls_short_summary" placeholder="UCL • 78' • 2-1 (HT 1-1)" class="form-control form-control-sm fw-bold text-success" required>
              </div>

              <div class="col-md-3">
                <label class="small fw-bold">Team 1 Score</label>
                <input type="text" name="score1" id="ls_score1" value="2" class="form-control form-control-sm fw-bold">
              </div>
              <div class="col-md-3">
                <label class="small fw-bold">Team 2 Score</label>
                <input type="text" name="score2" id="ls_score2" value="1" class="form-control form-control-sm fw-bold">
              </div>
              <div class="col-md-3">
                <label class="small fw-bold">HT / Set 1 (Team 1)</label>
                <input type="text" name="ht_score1" id="ls_ht1" value="1" class="form-control form-control-sm">
              </div>
              <div class="col-md-3">
                <label class="small fw-bold">HT / Set 1 (Team 2)</label>
                <input type="text" name="ht_score2" id="ls_ht2" value="1" class="form-control form-control-sm">
              </div>

              <div class="col-md-4">
                <label class="small fw-bold">Live Stage / Format</label>
                <input type="text" name="stage_text" id="ls_stage" value="2nd Half • 74'" class="form-control form-control-sm">
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">Minute / Over</label>
                <input type="text" name="minute" id="ls_minute" value="74'" class="form-control form-control-sm">
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">Stadium / Venue / Surface</label>
                <input type="text" name="stadium" id="ls_stadium" value="Estadio Santiago Bernabéu" class="form-control form-control-sm">
              </div>

              <div class="col-md-6">
                <label class="small fw-bold">Team 1 Goal Scorers / Notes</label>
                <input type="text" name="goal_info1" id="ls_goal1" placeholder="Mbappé 24', Vinícius 68'" class="form-control form-control-sm">
              </div>
              <div class="col-md-6">
                <label class="small fw-bold">Team 2 Goal Scorers / Notes</label>
                <input type="text" name="goal_info2" id="ls_goal2" placeholder="Kane 41'" class="form-control form-control-sm">
              </div>

              <div class="col-md-3">
                <label class="small fw-bold">Corners / Games (T1)</label>
                <input type="text" name="corner1" id="ls_c1" value="6" class="form-control form-control-sm">
              </div>
              <div class="col-md-3">
                <label class="small fw-bold">Corners / Games (T2)</label>
                <input type="text" name="corner2" id="ls_c2" value="4" class="form-control form-control-sm">
              </div>
              <div class="col-md-3">
                <label class="small fw-bold">Yellow / Points (T1)</label>
                <input type="text" name="yellow1" id="ls_y1" value="1" class="form-control form-control-sm">
              </div>
              <div class="col-md-3">
                <label class="small fw-bold">Yellow / Points (T2)</label>
                <input type="text" name="yellow2" id="ls_y2" value="2" class="form-control form-control-sm">
              </div>

              <div class="col-md-4">
                <label class="small fw-bold">Team 1 Back Odds (1)</label>
                <input type="number" step="0.01" name="back1" id="ls_b1" value="1.65" class="form-control form-control-sm fw-bold">
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">Draw Back Odds (X)</label>
                <input type="number" step="0.01" name="backX" id="ls_bx" value="3.80" class="form-control form-control-sm fw-bold">
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">Team 2 Back Odds (2)</label>
                <input type="number" step="0.01" name="back2" id="ls_b2" value="4.90" class="form-control form-control-sm fw-bold">
              </div>

              <div class="col-12">
                <label class="small fw-bold">Bottom Commentary / Status Line</label>
                <input type="text" name="status_text" id="ls_status_text" placeholder="UEFA Champions League • 2nd Half 74' • Real Madrid 2 - 1 Bayern Munich" class="form-control form-control-sm">
              </div>
            </div>
          </div>
          <div class="modal-footer py-1">
            <button type="submit" class="btn btn-sm btn-primary fw-bold px-4">Save Live Scoreboard &amp; Odds</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Add / Edit Ball By Ball Runner Modal -->
  <div class="modal fade" id="bbbRunnerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header py-2 bg-warning text-dark">
          <h6 class="modal-title fw-bold m-0"><i class="fas fa-baseball-ball me-1"></i> Create / Edit Ball By Ball Runner</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form action="{{ route('admin.live.scores.bbb.runner') }}" method="POST">
          @csrf
          <div class="modal-body">
            <div class="row g-2">
              <div class="col-md-6">
                <label class="small fw-bold">Selection ID *</label>
                <input type="text" name="selection_id" id="bbb_sel_id" value="304350110" class="form-control form-control-sm font-monospace" required>
              </div>
              <div class="col-md-6">
                <label class="small fw-bold">Runner Name *</label>
                <input type="text" name="runner_name" id="bbb_name" placeholder="e.g. 0 RUNS / WICKET" class="form-control form-control-sm fw-bold" required>
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">Back Odds *</label>
                <input type="number" step="0.01" name="back_price" id="bbb_price" value="2.18" class="form-control form-control-sm fw-bold" required>
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">Liquidity Size *</label>
                <input type="number" name="back_size" id="bbb_size" value="100000" class="form-control form-control-sm" required>
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">Status *</label>
                <select name="status" id="bbb_status" class="form-select form-select-sm fw-bold">
                  <option value="ONLINE">ONLINE</option>
                  <option value="SUSPENDED">SUSPENDED</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="small fw-bold">Market Min Bet</label>
                <input type="number" name="min" id="bbb_min" value="{{ $bbbMarket['min'] ?? 100 }}" class="form-control form-control-sm">
              </div>
              <div class="col-md-6">
                <label class="small fw-bold">Market Max Bet</label>
                <input type="number" name="max" id="bbb_max" value="{{ $bbbMarket['max'] ?? 100000 }}" class="form-control form-control-sm">
              </div>
            </div>
          </div>
          <div class="modal-footer py-1">
            <button type="submit" class="btn btn-sm btn-primary fw-bold px-4">Save Runner</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    function resetLiveScoreModal() {
      document.getElementById('ls_sport_id').value = '1';
      document.getElementById('ls_event_id').value = 'ucl_' + Math.floor(10000 + Math.random() * 90000);
      document.getElementById('ls_tournament').value = 'UEFA Champions League';
      document.getElementById('ls_event_name').value = '';
      document.getElementById('ls_short_summary').value = "UCL • 70' • 2-1 (HT 1-0)";
      document.getElementById('ls_score1').value = '2';
      document.getElementById('ls_score2').value = '1';
      document.getElementById('ls_ht1').value = '1';
      document.getElementById('ls_ht2').value = '0';
      document.getElementById('ls_stage').value = "2nd Half • 70'";
      document.getElementById('ls_minute').value = "70'";
      document.getElementById('ls_stadium').value = 'International Stadium';
      document.getElementById('ls_goal1').value = '';
      document.getElementById('ls_goal2').value = '';
      document.getElementById('ls_status_text').value = '';
    }

    function openLiveScoreEditor(ev, meta, b1, b2, bx) {
      document.getElementById('ls_sport_id').value = String(ev.sport_id || '1');
      document.getElementById('ls_event_id').value = ev.event_id || '';
      document.getElementById('ls_tournament').value = ev.tournament_name || '';
      document.getElementById('ls_event_name').value = ev.event_name || '';
      document.getElementById('ls_short_summary').value = meta.shortScoreSummary || meta.status || "LIVE • 1-0";
      document.getElementById('ls_score1').value = meta.score1 ?? (meta.team1?.score ?? '1');
      document.getElementById('ls_score2').value = meta.score2 ?? (meta.team2?.score ?? '0');
      document.getElementById('ls_ht1').value = meta.htScore1 ?? (meta.set1_1 ?? '1');
      document.getElementById('ls_ht2').value = meta.htScore2 ?? (meta.set1_2 ?? '0');
      document.getElementById('ls_stage').value = meta.stageText || meta.matchFormat || "2nd Half • 70'";
      document.getElementById('ls_minute').value = meta.minute || "70'";
      document.getElementById('ls_stadium').value = meta.stadium || meta.surface || 'International Stadium';
      document.getElementById('ls_goal1').value = meta.goalInfo1 || '';
      document.getElementById('ls_goal2').value = meta.goalInfo2 || '';
      document.getElementById('ls_c1').value = meta.corner1 ?? (meta.game1 ?? '5');
      document.getElementById('ls_c2').value = meta.corner2 ?? (meta.game2 ?? '4');
      document.getElementById('ls_y1').value = meta.yellowCard1 ?? (meta.point1 ?? '1');
      document.getElementById('ls_y2').value = meta.yellowCard2 ?? (meta.point2 ?? '1');
      document.getElementById('ls_b1').value = b1 || 1.80;
      document.getElementById('ls_b2').value = b2 || 2.15;
      document.getElementById('ls_bx').value = bx || 3.45;
      document.getElementById('ls_status_text').value = meta.status || meta.shortScoreSummary || '';
      new bootstrap.Modal(document.getElementById('liveScoreModal')).show();
    }

    function resetBbbRunnerModal() {
      document.getElementById('bbb_sel_id').value = '304350' + Math.floor(110 + Math.random() * 800);
      document.getElementById('bbb_name').value = '';
      document.getElementById('bbb_price').value = '2.50';
      document.getElementById('bbb_size').value = '100000';
      document.getElementById('bbb_status').value = 'ONLINE';
    }

    function openEditBbbRunner(br, minBet, maxBet) {
      document.getElementById('bbb_sel_id').value = br.selectionId;
      document.getElementById('bbb_name').value = br.name;
      document.getElementById('bbb_price').value = br.price;
      document.getElementById('bbb_size').value = br.size;
      document.getElementById('bbb_status').value = br.status || 'ONLINE';
      document.getElementById('bbb_min').value = minBet || 100;
      document.getElementById('bbb_max').value = maxBet || 100000;
      new bootstrap.Modal(document.getElementById('bbbRunnerModal')).show();
    }
  </script>
@endsection
