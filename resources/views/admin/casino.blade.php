@extends('layouts.admin')
@section('title', 'Casino Games CRUD - Admin Panel')

@section('content')
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
      <h5 class="fw-bolder m-0"><i class="fas fa-dice text-purple me-1" style="color:#8b5cf6;"></i> Casino Games Management (217+ Games Full CRUD)</h5>
      <small class="text-muted">Create new Casino Games, edit Game Name, Engine Type, RTP %, Min/Max Stakes, Popular/Active status, upload custom thumbnails, or delete games.</small>
    </div>
    <button class="btn btn-sm btn-primary fw-bold px-3 py-2" data-bs-toggle="modal" data-bs-target="#createCasinoModal">
      <i class="fas fa-plus-circle me-1"></i> + Add New Casino Game
    </button>
  </div>

  <div class="adm-card">
    <div class="adm-card-header">
      <form method="GET" action="{{ route('admin.casino') }}" class="d-flex gap-2 m-0">
        <input type="text" name="q" value="{{ $q }}" class="form-control form-control-sm" style="width:260px;" placeholder="Search game name, event ID, engine type...">
        <button type="submit" class="btn btn-sm btn-dark fw-bold">Search</button>
      </form>
      <div class="small fw-bold text-secondary">Total Casino Games: {{ $games->total() }}</div>
    </div>

    <div class="adm-card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover table-bordered table-sm small text-center align-middle mb-0">
          <thead>
            <tr>
              <th>Thumb</th>
              <th>Event ID</th>
              <th>Game Name</th>
              <th>Engine Type</th>
              <th>Menu</th>
              <th>RTP %</th>
              <th>Min / Max Bet</th>
              <th>Popular</th>
              <th>Status</th>
              <th>Update / Delete</th>
            </tr>
          </thead>
          <tbody>
            @foreach($games as $g)
              <tr>
                <td>
                  <img src="{{ $g->display_image }}" alt="{{ $g->event_name }}" style="width:44px;height:44px;object-fit:cover;border-radius:6px;" onerror="this.src='/assets/casino/99_0010.webp'">
                </td>
                <td class="fw-bold">
                  <a href="{{ $g->launch_url }}" target="_blank" class="text-decoration-none">{{ $g->event_id }}</a>
                </td>
                <td colspan="7" class="p-1">
                  <form action="{{ route('admin.casino.update', $g->id) }}" method="POST" enctype="multipart/form-data" class="row g-1 align-items-center m-0">
                    @csrf
                    <div class="col-md-3">
                      <input type="text" name="event_name" value="{{ $g->event_name }}" class="form-control form-control-sm fw-bold">
                    </div>
                    <div class="col-md-2">
                      <input type="text" name="game_type" value="{{ $g->game_type }}" class="form-control form-control-sm text-center">
                    </div>
                    <div class="col-md-2">
                      <input type="text" name="menu_name" value="{{ $g->menu_name }}" class="form-control form-control-sm text-center">
                    </div>
                    <div class="col-md-1">
                      <input type="number" step="0.1" name="rtp" value="{{ $g->rtp }}" class="form-control form-control-sm text-center">
                    </div>
                    <div class="col-md-2 d-flex gap-1">
                      <input type="number" name="min_bet" value="{{ (int)$g->min_bet }}" class="form-control form-control-sm text-center" title="Min Bet">
                      <input type="number" name="max_bet" value="{{ (int)$g->max_bet }}" class="form-control form-control-sm text-center" title="Max Bet">
                    </div>
                    <div class="col-md-1">
                      <label class="small d-block">Pop</label>
                      <input type="checkbox" name="popular" value="1" class="form-check-input" {{ $g->popular ? 'checked' : '' }}>
                    </div>
                    <div class="col-md-1">
                      <label class="small d-block">Live</label>
                      <input type="checkbox" name="status" value="1" class="form-check-input" {{ $g->status ? 'checked' : '' }}>
                    </div>
                    <div class="col-12 d-flex justify-content-end gap-2 mt-1">
                      <input type="file" name="thumbnail" class="form-control form-control-sm" style="max-width:210px;" accept="image/*" title="Upload new thumbnail">
                      <button type="submit" class="btn btn-sm btn-primary py-0 px-3 fw-bold">Save</button>
                    </div>
                  </form>
                </td>
                <td>
                  <form action="{{ route('admin.casino.delete', $g->id) }}" method="POST" class="m-0" onsubmit="return confirm('Delete casino game {{ addslashes($g->event_name) }}?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger py-1 px-2"><i class="fas fa-trash"></i></button>
                  </form>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    <div class="p-2 border-top">{{ $games->links('pagination::bootstrap-5') }}</div>
  </div>

  <!-- Create Casino Game Modal -->
  <div class="modal fade" id="createCasinoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header py-2 bg-primary text-white">
          <h6 class="modal-title fw-bold m-0"><i class="fas fa-plus-circle me-1"></i> Create New Casino Game</h6>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <form action="{{ route('admin.casino.create') }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="modal-body">
            <div class="row g-2">
              <div class="col-md-6">
                <label class="small fw-bold">Event ID *</label>
                <input type="text" name="event_id" placeholder="e.g. 99.0099" class="form-control form-control-sm" required>
              </div>
              <div class="col-md-6">
                <label class="small fw-bold">Game Name *</label>
                <input type="text" name="event_name" placeholder="e.g. SUPER TEENPATTI" class="form-control form-control-sm" required>
              </div>
              <div class="col-md-6">
                <label class="small fw-bold">Engine Type *</label>
                <select name="game_type" class="form-select form-select-sm">
                  <option value="teenpatti">Teenpatti</option>
                  <option value="lucky7">Lucky 7</option>
                  <option value="dragontiger">Dragon Tiger</option>
                  <option value="crash">Crash (Vimaan/Balloon)</option>
                  <option value="roulette">Spin / Roulette</option>
                  <option value="cards">Cards / Head &amp; Tails</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="small fw-bold">Category Menu</label>
                <input type="text" name="menu_name" value="TEENPATTI" class="form-control form-control-sm">
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">Min Bet *</label>
                <input type="number" name="min_bet" value="100" class="form-control form-control-sm" required>
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">Max Bet *</label>
                <input type="number" name="max_bet" value="100000" class="form-control form-control-sm" required>
              </div>
              <div class="col-md-4">
                <label class="small fw-bold">RTP % *</label>
                <input type="number" step="0.1" name="rtp" value="98.0" class="form-control form-control-sm" required>
              </div>
              <div class="col-12">
                <label class="small fw-bold">Game Thumbnail Image</label>
                <input type="file" name="thumbnail" class="form-control form-control-sm" accept="image/*">
              </div>
              <div class="col-12 d-flex gap-3 mt-2">
                <div class="form-check small">
                  <input class="form-check-input" type="checkbox" name="popular" value="1" id="chkCasPop" checked>
                  <label class="form-check-label fw-bold" for="chkCasPop">Show in Popular Games</label>
                </div>
                <div class="form-check small">
                  <input class="form-check-input" type="checkbox" name="status" value="1" id="chkCasStatus" checked>
                  <label class="form-check-label fw-bold" for="chkCasStatus">Active</label>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer py-1">
            <button type="submit" class="btn btn-sm btn-primary fw-bold px-4">Create Game</button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection
