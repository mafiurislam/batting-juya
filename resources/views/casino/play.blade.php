@extends('layouts.app')
@section('title', $game->event_name . ' - Live Casino | winbook24')

@section('content')
  <!-- Top Studio Bar -->
  <div class="d-flex justify-content-between align-items-center text-white px-3 py-2 mt-1"
       style="background:linear-gradient(-180deg, #2E4B5E 0%, #243A48 82%);border-radius:4px 4px 0 0;">
    <div class="d-flex align-items-center gap-2">
      <a href="{{ route('livecasino') }}" class="btn btn-sm btn-outline-light py-0 px-2">
        <i class="fas fa-arrow-left"></i> Lobby
      </a>
      <span class="fw-bold fs-6">{{ $game->event_name }}</span>
      <span class="badge bg-warning text-dark">{{ $tableDef['subtitle'] ?? 'Universe Live Studio' }}</span>
    </div>
    <div class="d-flex align-items-center gap-2 small">
      <span>Round ID: <strong id="studioRoundId">#R{{ now()->format('His') }}</strong></span>
      <span class="badge bg-success">Min: {{ number_format($game->min_bet, 0) }} | Max: {{ number_format($game->max_bet, 0) }}</span>
    </div>
  </div>

  <!-- ================= INTERACTIVE STUDIO STAGE ================= -->
  <div class="wb-studio-stage">
    <!-- Top Overlay: Live Indicator, Recent Result Badges & Countdown Timer -->
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div class="d-flex align-items-center gap-2">
        <span class="badge bg-danger"><i class="fas fa-circle me-1"></i> LIVE STUDIO</span>
        <div id="recentBadgesBar" class="d-flex gap-1">
          @foreach(['A', 'B', 'A', 'A', 'B', 'T', 'A'] as $b)
            <span class="badge rounded-pill {{ $b === 'A' ? 'bg-primary' : ($b === 'B' ? 'bg-warning text-dark' : 'bg-success') }}">{{ $b }}</span>
          @endforeach
        </div>
      </div>
      <div class="d-flex align-items-center gap-2">
        <span class="small text-white-50">Next Auto-Draw:</span>
        <span id="studioTimer" class="badge bg-black border border-warning text-warning fs-6 px-2">18s</span>
      </div>
    </div>

    <!-- Dynamic Game Visual Area -->
    <div id="studioVisualArea" class="text-center py-2">
      @if($tableDef['layout'] === 'crash')
        <!-- VIMAAN CRASH STAGE -->
        <div class="p-3 rounded-3 position-relative overflow-hidden" style="background:radial-gradient(circle,#2b0912 0%,#080a10 100%);border:1px solid #ff3b3b;min-height:210px;">
          <div id="crashMultiplierDisplay" class="fw-bold text-warning" style="font-size:46px;text-shadow:0 0 18px #ff2a2a;">1.00x</div>
          <div id="crashStatusText" class="text-info small fw-bold mb-2">READY FOR TAKEOFF • CHOOSE CASHOUT MULTIPLIER OR CLICK FLY NOW!</div>
          <div class="my-2">
            <img id="vimaanPlaneSprite" src="/assets/casinoslpot/vimaan.png" alt="Vimaan" style="height:54px;transition:transform 0.5s ease;">
          </div>
          <div class="d-flex justify-content-center align-items-center gap-2 mt-2">
            <label class="small fw-bold text-white">Target Cashout (x):</label>
            <input type="number" step="0.1" min="1.1" max="200" id="customCrashTarget" value="2.00" class="form-control form-control-sm text-center fw-bold" style="width:95px;">
            <button type="button" class="btn btn-danger fw-bold px-4" onclick="playCrashCustom()">
              <i class="fas fa-plane-departure me-1"></i> BET &amp; FLY
            </button>
          </div>
        </div>
      @elseif($tableDef['layout'] === 'balloon')
        <!-- BALLOON STAGE -->
        <div class="p-3 rounded-3 text-center" style="background:linear-gradient(180deg,#0f2942,#071320);min-height:210px;">
          <div id="balloonSprite" style="font-size:64px;transition:transform 0.4s ease;">🎈</div>
          <div id="balloonMultDisplay" class="fs-2 fw-bold text-warning">1.00x</div>
          <div class="d-flex justify-content-center align-items-center gap-2 mt-2">
            <label class="small fw-bold">Target Burst Cashout (x):</label>
            <input type="number" step="0.1" min="1.1" max="80" id="customBalloonTarget" value="2.00" class="form-control form-control-sm text-center fw-bold" style="width:95px;">
            <button type="button" class="btn btn-warning fw-bold px-4" onclick="playBalloonCustom()">
              <i class="fas fa-compress-arrows-alt me-1"></i> PUMP &amp; CASHOUT
            </button>
          </div>
        </div>
      @elseif($tableDef['layout'] === 'slot')
        <!-- 5x3 VIDEO SLOT MACHINE STAGE -->
        <div class="p-3 rounded-3 mx-auto" style="max-width:540px;background:linear-gradient(180deg,#1f1135,#0d0618);border:2px solid #d4af37;">
          <div class="d-flex justify-content-between align-items-center mb-2 px-2">
            <span class="badge bg-warning text-dark fw-bold">20 PAYLINES</span>
            <span id="slotWinBanner" class="fw-bold text-warning fs-6">SPIN TO WIN UP TO 250x!</span>
            <span class="badge bg-info text-dark fw-bold">RTP {{ $game->rtp }}%</span>
          </div>
          <div id="slotGridBox" class="p-2 rounded-2" style="background:#07030d;border:1px solid #553c8b;">
            @php $syms = $tableDef['symbols'] ?? ['7️⃣','💎','👑','🔔','🍀','🍒','🔥','⭐']; @endphp
            @for($r = 0; $r < 3; $r++)
              <div class="d-flex justify-content-center gap-2 my-1">
                @for($c = 0; $c < 5; $c++)
                  <div class="slot-cell d-flex align-items-center justify-content-center rounded-2"
                       id="slotCell_{{ $r }}_{{ $c }}"
                       style="width:74px;height:64px;background:linear-gradient(180deg,#2a1b4e,#140b29);border:1px solid #6b4fbb;font-size:30px;">
                    {{ $syms[($r * 2 + $c) % count($syms)] }}
                  </div>
                @endfor
              </div>
            @endfor
          </div>
          <div class="mt-3 d-flex justify-content-center gap-2">
            <button type="button" id="btnSlotSpin" class="btn btn-lg fw-bold px-5 text-dark"
                    style="background:linear-gradient(#A4DC60 0%, #4F9F21 100%);border-radius:30px;box-shadow:0 4px 14px rgba(164,220,96,0.5);"
                    onclick="playCasinoSelection('SPIN', 'BACK')">
              <i class="fas fa-sync-alt me-1"></i> SPIN REELS
            </button>
          </div>
        </div>
      @else
        <!-- LIVE CARD / WHEEL / ROULETTE / DICE STUDIO FELT -->
        <div id="liveTableFelt" class="p-3 rounded-3 mx-auto" style="background:radial-gradient(circle,#145231 0%,#092918 100%);border:3px solid #c9a227;min-height:185px;">
          <div id="tableCardsContainer" class="d-flex flex-wrap justify-content-around align-items-center gap-3 py-2">
            <div>
              <div class="fw-bold text-warning mb-2">{{ $tableDef['sideA'] ?? ($tableDef['sides'][0] ?? 'SIDE A') }}</div>
              <div id="handABox">
                <span class="playing-card">♠A</span>
                @if(in_array($game->game_type, ['teenpatti', 'baccarat', 'blackjack']))
                  <span class="playing-card red-card">♥K</span>
                @endif
                @if($game->game_type === 'teenpatti')
                  <span class="playing-card">♣Q</span>
                @endif
              </div>
            </div>

            <div class="badge bg-dark border border-warning text-warning fs-6 px-3 py-2">VS</div>

            @if(isset($tableDef['sideB']) || isset($tableDef['sides']))
              <div>
                <div class="fw-bold text-info mb-2">{{ $tableDef['sideB'] ?? ($tableDef['sides'][1] ?? 'SIDE B') }}</div>
                <div id="handBBox">
                  <span class="playing-card red-card">♦K</span>
                  @if(in_array($game->game_type, ['teenpatti', 'baccarat', 'blackjack']))
                    <span class="playing-card">♠J</span>
                  @endif
                  @if($game->game_type === 'teenpatti')
                    <span class="playing-card red-card">♥10</span>
                  @endif
                </div>
              </div>
            @endif
          </div>
          <div id="studioResultBanner" class="mt-2 fw-bold text-warning fs-6">
            Select your chip stake below and click any Back (Blue) or Lay (Pink) market to deal!
          </div>
        </div>
      @endif
    </div>

    <!-- Chip Stake Selector Bar -->
    <div class="wb-chip-bar mt-3">
      @foreach([
        ['val' => 100, 'lbl' => '100', 'bg' => '#1e88e5'],
        ['val' => 500, 'lbl' => '500', 'bg' => '#43a047'],
        ['val' => 1000, 'lbl' => '1K', 'bg' => '#e53935'],
        ['val' => 5000, 'lbl' => '5K', 'bg' => '#8e24aa'],
        ['val' => 10000, 'lbl' => '10K', 'bg' => '#fb8c00'],
        ['val' => 25000, 'lbl' => '25K', 'bg' => '#263238'],
      ] as $idx => $chip)
        <div class="wb-casino-chip {{ $idx === 0 ? 'active' : '' }}"
             style="background:{{ $chip['bg'] }};"
             onclick="selectCasinoChip({{ $chip['val'] }}, this)">
          {{ $chip['lbl'] }}
        </div>
      @endforeach
      <div class="d-flex align-items-center ms-2">
        <span class="small text-white me-1 fw-bold">Stake PTI:</span>
        <input type="number" id="casinoStakeInput" value="100" min="10" step="10"
               class="form-control form-control-sm text-center fw-bold" style="width:95px;">
      </div>
    </div>
  </div>

  <!-- ================= WINBOOK24 EXCHANGE ODDS GRID FOR THIS CASINO GAME ================= -->
  <div class="bg-white border mt-2">
    <div class="wb-market-section-head">
      <span><i class="fas fa-Layer-group me-1"></i> {{ $game->event_name }} Betting Markets (Click Any Odds Box To Play Instant Round)</span>
      <span>Min/Max: {{ number_format($game->min_bet, 0) }} - {{ number_format($game->max_bet, 0) }}</span>
    </div>

    <div class="row g-0">
      @foreach(($tableDef['markets'] ?? []) as $m)
        <div class="col-md-6 border-bottom border-end p-2 d-flex justify-content-between align-items-center">
          <div>
            <div class="fw-bold text-dark" style="font-size:12px;">{{ $m['label'] }}</div>
            <small class="text-muted">Instant Settlement</small>
          </div>
          <div class="d-flex gap-1">
            <div class="wb-odd-box wb-odd-back px-3" style="width:72px;height:34px;"
                 onclick="playCasinoSelection('{{ $m['key'] }}', 'BACK')">
              <strong>{{ number_format((float)$m['odds'], 2) }}</strong>
              <small>BACK</small>
            </div>
            @if(!empty($m['lay']))
              <div class="wb-odd-box wb-odd-lay px-3" style="width:72px;height:34px;"
                   onclick="playCasinoSelection('{{ $m['key'] }}', 'LAY')">
                <strong>{{ number_format((float)$m['lay'], 2) }}</strong>
                <small>LAY</small>
              </div>
            @endif
          </div>
        </div>
      @endforeach
    </div>
  </div>

  <!-- ================= RECENT ROUNDS & RELATED CASINO GAMES ================= -->
  <div class="row g-2 mt-2">
    <div class="col-md-6">
      <div class="bg-white border h-100">
        <div class="wb-report-title">My Recent Rounds ({{ $game->event_name }})</div>
        <div class="table-responsive">
          <table class="table table-sm table-bordered mb-0 small text-center">
            <thead class="table-light">
              <tr>
                <th>Round</th>
                <th>Selection</th>
                <th>Stake</th>
                <th>Outcome</th>
                <th>P/L</th>
              </tr>
            </thead>
            <tbody id="myCasinoRoundsBody">
              @forelse($recentRounds as $rr)
                <tr>
                  <td>{{ $rr->bet_ref }}</td>
                  <td>{{ $rr->selection_name }}</td>
                  <td>{{ number_format($rr->stake, 2) }}</td>
                  <td>{{ $rr->result_summary }}</td>
                  <td class="{{ $rr->profit_loss >= 0 ? 'text-success' : 'text-danger' }} fw-bold">
                    {{ $rr->profit_loss >= 0 ? '+' : '' }}{{ number_format($rr->profit_loss, 2) }}
                  </td>
                </tr>
              @empty
                <tr class="js-no-rounds"><td colspan="5" class="text-muted py-2">No rounds played yet in this session.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="bg-white border h-100">
        <div class="wb-report-title">More Popular Casino Games</div>
        <div class="p-2 d-grid" style="grid-template-columns:repeat(4,1fr);gap:6px;">
          @foreach($relatedGames as $rg)
            <a href="{{ route('casino.play', ['eventId' => $rg->event_id]) }}" class="wb-ourcasino-card">
              <img src="{{ $rg->display_image }}" alt="{{ $rg->event_name }}" style="height:75px;" onerror="this.src='/assets/casino/99_0010.webp'">
              <div class="wb-ourcasino-title" style="font-size:9px;padding:3px;">{{ $rg->event_name }}</div>
            </a>
          @endforeach
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
<script>
  const gameEventId = '{{ $game->event_id }}';
  let countdown = 18;
  let isPlayingRound = false;

  setInterval(() => {
    countdown = countdown <= 1 ? 20 : countdown - 1;
    const tEl = document.getElementById('studioTimer');
    if (tEl) tEl.textContent = countdown + 's';
  }, 1000);

  function selectCasinoChip(val, el) {
    document.querySelectorAll('.wb-casino-chip').forEach(c => c.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('casinoStakeInput').value = val;
  }

  function playCrashCustom() {
    const target = parseFloat(document.getElementById('customCrashTarget')?.value || 2.0);
    playCasinoSelection('AUTO_2_0', 'BACK', target);
  }

  function playBalloonCustom() {
    const target = parseFloat(document.getElementById('customBalloonTarget')?.value || 2.0);
    playCasinoSelection('PUMP_2_0', 'BACK', target);
  }

  function renderCardHtml(card) {
    const isRed = card.color === 'RED' || ['♥', '♦'].includes(card.suit);
    return `<span class="playing-card ${isRed ? 'red-card' : ''}">${card.code}</span>`;
  }

  async function playCasinoSelection(selectionKey, side = 'BACK', targetMultiplier = null) {
    if (isPlayingRound) return;
    isPlayingRound = true;

    const stake = parseFloat(document.getElementById('casinoStakeInput')?.value || 100);
    const spinBtn = document.getElementById('btnSlotSpin');
    if (spinBtn) {
      spinBtn.disabled = true;
      spinBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> SPINNING...';
    }

    try {
      const res = await fetch('{{ route("api.casino.play") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': WB24.csrfToken,
        },
        body: JSON.stringify({
          event_id: gameEventId,
          selection_key: selectionKey,
          stake,
          side,
          target_multiplier: targetMultiplier,
        }),
      });

      const data = await res.json();
      if (!res.ok || !data.status) {
        WB24.toast(data.message || 'Unable to play round.', 'danger');
        if (data.redirect) setTimeout(() => (window.location.href = data.redirect), 900);
        return;
      }

      const outcome = data.outcome;
      document.getElementById('studioRoundId').textContent = '#' + outcome.round_id;
      countdown = 20;

      // Update Wallet
      WB24.updateWalletDisplay(data.wallet);

      // Render Visual Stage Update
      renderVisualOutcome(outcome);

      // Prepend badge & history row
      const badgeBar = document.getElementById('recentBadgesBar');
      if (badgeBar) {
        const b = document.createElement('span');
        b.className = `badge rounded-pill ${outcome.won ? 'bg-success' : 'bg-danger'}`;
        b.textContent = outcome.badge || (outcome.won ? 'W' : 'L');
        badgeBar.prepend(b);
        if (badgeBar.children.length > 9) badgeBar.lastElementChild.remove();
      }

      const tbody = document.getElementById('myCasinoRoundsBody');
      if (tbody) {
        tbody.querySelector('.js-no-rounds')?.remove();
        const tr = document.createElement('tr');
        const pl = parseFloat(outcome.net_pl);
        tr.innerHTML = `
          <td>${outcome.round_id}</td>
          <td>${selectionKey}</td>
          <td>${parseFloat(outcome.stake).toFixed(2)}</td>
          <td>${outcome.summary}</td>
          <td class="${pl >= 0 ? 'text-success' : 'text-danger'} fw-bold">${pl >= 0 ? '+' : ''}${pl.toFixed(2)}</td>
        `;
        tbody.prepend(tr);
      }

      WB24.toast(
        `${outcome.summary} | ${outcome.won ? 'WON +' : 'LOST '}${parseFloat(outcome.net_pl).toFixed(2)} PTI`,
        outcome.won ? 'success' : 'danger'
      );
    } catch (e) {
      WB24.toast('Network error while playing round.', 'danger');
    } finally {
      isPlayingRound = false;
      if (spinBtn) {
        spinBtn.disabled = false;
        spinBtn.innerHTML = '<i class="fas fa-sync-alt me-1"></i> SPIN REELS';
      }
    }
  }

  function renderVisualOutcome(outcome) {
    const v = outcome.visual || {};
    const banner = document.getElementById('studioResultBanner');
    if (banner) {
      banner.className = `mt-2 fw-bold fs-6 ${outcome.won ? 'text-warning' : 'text-light'}`;
      banner.textContent = `${outcome.summary} (${outcome.won ? 'YOU WON +' + outcome.net_pl + ' PTI!' : 'Try Again!'})`;
    }

    if (v.type === 'crash') {
      const mEl = document.getElementById('crashMultiplierDisplay');
      const sEl = document.getElementById('crashStatusText');
      const plane = document.getElementById('vimaanPlaneSprite');
      if (mEl) {
        mEl.textContent = v.crash_at + 'x';
        mEl.className = `fw-bold ${outcome.won ? 'text-success' : 'text-danger'}`;
      }
      if (sEl) sEl.textContent = outcome.summary;
      if (plane) {
        plane.style.transform = 'translate(35px, -18px) scale(1.15)';
        setTimeout(() => (plane.style.transform = 'translate(0,0) scale(1)'), 800);
      }
    } else if (v.type === 'balloon') {
      const bEl = document.getElementById('balloonMultDisplay');
      const sp = document.getElementById('balloonSprite');
      if (bEl) bEl.textContent = v.burst_at + 'x';
      if (sp) {
        sp.textContent = outcome.won ? '🎈✨' : '💥';
        setTimeout(() => (sp.textContent = '🎈'), 1400);
      }
    } else if (v.type === 'slot' && v.grid) {
      v.grid.forEach((row, r) => {
        row.forEach((cell, c) => {
          const el = document.getElementById(`slotCell_${r}_${c}`);
          if (el) {
            el.textContent = cell.sym;
            el.style.borderColor = '#6b4fbb';
            el.style.boxShadow = 'none';
          }
        });
      });
      (v.winningLines || []).forEach(wl => {
        wl.coords.forEach((r, c) => {
          const el = document.getElementById(`slotCell_${r}_${c}`);
          if (el && c < wl.count) {
            el.style.borderColor = '#ffd700';
            el.style.boxShadow = '0 0 12px #ffd700';
          }
        });
      });
      const winBanner = document.getElementById('slotWinBanner');
      if (winBanner) {
        winBanner.textContent = outcome.won
          ? `BIG WIN: +${outcome.net_pl} PTI (${v.totalMultiplier}x)!`
          : 'NO WIN THIS SPIN • SPIN AGAIN!';
      }
    } else if (v.type === 'cards_ab') {
      const box = document.getElementById('tableCardsContainer');
      if (box) {
        box.innerHTML = `
          <div>
            <div class="fw-bold text-warning mb-2">${v.labelA || 'SIDE A'}</div>
            <div>${(v.handA || []).map(renderCardHtml).join('')}</div>
          </div>
          <div class="badge bg-dark border border-warning text-warning fs-6 px-3 py-2">VS</div>
          <div>
            <div class="fw-bold text-info mb-2">${v.labelB || 'SIDE B'}</div>
            <div>${(v.handB || []).map(renderCardHtml).join('')}</div>
          </div>
        `;
      }
    } else if (v.type === 'single_card' && v.card) {
      const box = document.getElementById('tableCardsContainer');
      if (box) {
        box.innerHTML = `<div><div class="fw-bold text-warning mb-2">DEALT CARD</div>${renderCardHtml(v.card)}</div>`;
      }
    } else if (v.type === 'cards_3' && v.cards) {
      const box = document.getElementById('tableCardsContainer');
      if (box) {
        box.innerHTML = v.cards.map((c, i) => `
          <div>
            <div class="fw-bold text-warning mb-2">${(v.labels && v.labels[i]) || ('HAND ' + (i + 1))}</div>
            ${renderCardHtml(c)}
          </div>
        `).join('');
      }
    } else if (v.type === 'andarbahar') {
      const box = document.getElementById('tableCardsContainer');
      if (box) {
        box.innerHTML = `
          <div><div class="fw-bold text-warning mb-1">ANDAR</div>${(v.andar || []).map(renderCardHtml).join('')}</div>
          <div><div class="fw-bold text-danger mb-1">JOKER</div>${renderCardHtml(v.joker)}</div>
          <div><div class="fw-bold text-info mb-1">BAHAR</div>${(v.bahar || []).map(renderCardHtml).join('')}</div>
        `;
      }
    } else if (v.type === 'poker') {
      const box = document.getElementById('tableCardsContainer');
      if (box) {
        box.innerHTML = `
          <div><div class="fw-bold text-warning mb-1">Player A (${v.handAName})</div>${(v.holeA || []).map(renderCardHtml).join('')}</div>
          <div><div class="fw-bold text-light mb-1">Community Board</div>${(v.board || []).map(renderCardHtml).join('')}</div>
          <div><div class="fw-bold text-info mb-1">Player B (${v.handBName})</div>${(v.holeB || []).map(renderCardHtml).join('')}</div>
        `;
      }
    } else if (v.type === 'roulette') {
      const box = document.getElementById('tableCardsContainer');
      if (box) {
        const bg = v.color === 'RED' ? '#d32f2f' : (v.color === 'BLACK' ? '#212121' : '#2e7d32');
        box.innerHTML = `<div class="rounded-circle d-inline-flex align-items-center justify-content-center fw-bold fs-1 text-white border border-3 border-warning" style="width:96px;height:96px;background:${bg};">${v.number}</div>`;
      }
    } else if (v.type === 'sicbo' && v.dice) {
      const box = document.getElementById('tableCardsContainer');
      if (box) {
        box.innerHTML = v.dice.map(d => `<div class="bg-white text-dark fw-bold fs-2 rounded-3 d-inline-flex align-items-center justify-content-center border border-2 border-warning mx-2" style="width:62px;height:62px;">${d}</div>`).join('');
      }
    }
  }
</script>
@endpush

