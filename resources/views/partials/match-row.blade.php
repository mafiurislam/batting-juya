@php
  $isBallByBall = ($ev->event_id === '3544687543453' || strtolower(trim($ev->event_name)) === 'ball by ball');
  $runners = $ev->runners_json ?? [];
  $r0 = $runners[0] ?? ['runnerName' => 'Team 1', 'back' => [['price' => 0]], 'lay' => [['price' => 0]]];
  $r1 = $runners[1] ?? ['runnerName' => 'Team 2', 'back' => [['price' => 0]], 'lay' => [['price' => 0]]];
  $hasThirdRunner = isset($runners[2]);
  $rDraw = $hasThirdRunner ? $runners[2] : ['runnerName' => 'The Draw', 'back' => [['price' => 0]], 'lay' => [['price' => 0]]];

  $b1 = $r0['back'][0]['price'] ?? 0;
  $l1 = $r0['lay'][0]['price'] ?? 0;
  $bx = $rDraw['back'][0]['price'] ?? 0;
  $lx = $rDraw['lay'][0]['price'] ?? 0;
  $b2 = $r1['back'][0]['price'] ?? 0;
  $l2 = $r1['lay'][0]['price'] ?? 0;

  $dispBx = $hasThirdRunner ? ($bx ?: '0') : ($bx ? number_format((float)$bx, 2) : '0.0');
  $dispLx = $hasThirdRunner ? ($lx ?: '0') : ($lx ? number_format((float)$lx, 2) : '0.0');

  $preBet = !empty($ev->score_json['preBet']);
  $todayDateStr = \Carbon\Carbon::now('Asia/Kolkata')->format('Y-m-d');
  $evDateStr = $todayDateStr;
  $formattedTime = '';
  try {
      $dt = \Carbon\Carbon::parse($ev->event_time)->setTimezone('Asia/Kolkata');
      $formattedTime = $dt->format('d-m-Y G:i');
      $evDateStr = $dt->format('Y-m-d');
  } catch (\Throwable $e) {
      $formattedTime = $ev->event_time;
  }

  $isInPlay = (bool) $ev->in_play;
  $isToday = $isInPlay || ($evDateStr === $todayDateStr);
  $isTomorrow = !$isInPlay && ($evDateStr > $todayDateStr);
  $isYesterday = !$isInPlay && ($evDateStr < $todayDateStr);
  $hideInitially = !empty($onlyInPlayDefault) && !$isInPlay;

  $isPinned = in_array($ev->event_id, $pinnedIds ?? []);
  if ($isBallByBall) {
      $targetRoute = route('ball.by.ball', ['sportId' => $ev->sport_id, 'eventId' => $ev->event_id]);
  } elseif ($ev->sport_id === '66104') {
      $targetRoute = route('lottery.market', ['sportId' => $ev->sport_id, 'eventId' => $ev->event_id]);
  } else {
      $targetRoute = route('full.market', ['sportId' => $ev->sport_id, 'eventId' => $ev->event_id]);
  }
  $cricbuzzMeta = $ev->score_json['cricbuzz'] ?? ($ev->score_json['tennis'] ?? ($ev->score_json['soccer'] ?? null));
  $cricbuzzShortScore = trim((string) ($cricbuzzMeta['shortScoreSummary'] ?? ''));
  $cricbuzzStatus = trim((string) ($cricbuzzMeta['status'] ?? ($cricbuzzMeta['tournament'] ?? '')));
@endphp

<div class="wb-match-row js-filterable-match-row {{ $hideInitially ? 'd-none' : '' }}"
     data-event-row="{{ $ev->event_id }}"
     data-sport-id="{{ $ev->sport_id }}"
     data-is-inplay="{{ $isInPlay ? '1' : '0' }}"
     data-is-today="{{ $isToday ? '1' : '0' }}"
     data-is-tomorrow="{{ $isTomorrow ? '1' : '0' }}"
     data-is-yesterday="{{ $isYesterday ? '1' : '0' }}">
  <div class="wb-match-info" style="cursor:pointer;" onclick="window.location.href='{{ $targetRoute }}'">
    <div class="wb-match-title-wrap">
      <a href="{{ $targetRoute }}" class="wb-match-link" onclick="event.stopPropagation();">{{ $ev->event_name }}</a>
      @if($ev->in_play)
        <span class="wb-inplay-tag" onclick="event.stopPropagation(); window.location.href='{{ $targetRoute }}'" title="Click to view Live Scoreboard & Markets">In-Play</span>
        @if($cricbuzzShortScore !== '')
          <span class="wb-cricbuzz-score-pill d-none" style="display:none !important;" data-cricbuzz-score="{{ $ev->event_id }}" title="{{ $cricbuzzStatus ?: 'Live Score' }}">
            <span class="wb-cb-live-dot"></span>
            <span class="js-cb-short-score">{{ $cricbuzzShortScore }}</span>
          </span>
        @endif
      @else
        @if($preBet)
          <span class="wb-prebet-clock" title="Pre-Bet Open">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" style="vertical-align:-2px;margin-left:4px;">
              <circle cx="12" cy="12" r="9.5" stroke="#008000" stroke-width="2.4" fill="#fff"/>
              <path d="M12 7v5.2l3.4 2" stroke="#008000" stroke-width="2.4" stroke-linecap="round"/>
            </svg>
          </span>
        @endif
        <span class="wb-match-time">{{ $formattedTime }}</span>
      @endif
    </div>

    <div class="wb-badges">
      @if($ev->is_bookmakers)
        <span class="wb-badge-bm" title="Bookmaker">BM</span>
      @endif
      @if($ev->is_fancy)
        <span class="wb-badge-f" title="Fancy">F</span>
      @endif
      @if($ev->is_sportsbook)
        <span class="wb-badge-s" title="Sportsbook">S</span>
      @endif
    </div>
  </div>

  <!-- 1 | X | 2 Odds Columns (Blue Back / Pink Lay) -->
  <div class="wb-odds-group">
    <!-- 1 (Runner 1) -->
    <div class="wb-odds-pair">
      <div class="wb-odd-box wb-odd-back" data-odd-cell="back-0"
           onclick="{{ $isBallByBall ? "window.location.href='" . $targetRoute . "'" : "WB24.openBetSlip({eventId:'" . $ev->event_id . "', eventName:'" . addslashes($ev->event_name) . "', marketName:'Match Odds', selectionId:'" . ($r0['selectionId'] ?? '1') . "', selectionName:'" . addslashes($r0['runnerName'] ?? 'Team 1') . "', betType:'BACK', odds:" . ($b1 > 0 ? $b1 : 1.90) . "})" }}">
        <strong>{{ $b1 ?: '0' }}</strong>
      </div>
      <div class="wb-odd-box wb-odd-lay" data-odd-cell="lay-0"
           onclick="{{ $isBallByBall ? "window.location.href='" . $targetRoute . "'" : "WB24.openBetSlip({eventId:'" . $ev->event_id . "', eventName:'" . addslashes($ev->event_name) . "', marketName:'Match Odds', selectionId:'" . ($r0['selectionId'] ?? '1') . "', selectionName:'" . addslashes($r0['runnerName'] ?? 'Team 1') . "', betType:'LAY', odds:" . ($l1 > 0 ? $l1 : 1.92) . "})" }}">
        <strong>{{ $l1 ?: '0' }}</strong>
      </div>
    </div>

    <!-- X (The Draw / Runner 3) -->
    <div class="wb-odds-pair">
      <div class="wb-odd-box wb-odd-back" data-odd-cell="back-2"
           onclick="{{ $isBallByBall ? "window.location.href='" . $targetRoute . "'" : "WB24.openBetSlip({eventId:'" . $ev->event_id . "', eventName:'" . addslashes($ev->event_name) . "', marketName:'Match Odds', selectionId:'" . ($rDraw['selectionId'] ?? '3') . "', selectionName:'" . addslashes($rDraw['runnerName'] ?? 'The Draw') . "', betType:'BACK', odds:" . ($bx > 0 ? $bx : 3.40) . "})" }}">
        <strong>{{ $dispBx }}</strong>
      </div>
      <div class="wb-odd-box wb-odd-lay" data-odd-cell="lay-2"
           onclick="{{ $isBallByBall ? "window.location.href='" . $targetRoute . "'" : "WB24.openBetSlip({eventId:'" . $ev->event_id . "', eventName:'" . addslashes($ev->event_name) . "', marketName:'Match Odds', selectionId:'" . ($rDraw['selectionId'] ?? '3') . "', selectionName:'" . addslashes($rDraw['runnerName'] ?? 'The Draw') . "', betType:'LAY', odds:" . ($lx > 0 ? $lx : 3.50) . "})" }}">
        <strong>{{ $dispLx }}</strong>
      </div>
    </div>

    <!-- 2 (Runner 2) -->
    <div class="wb-odds-pair">
      <div class="wb-odd-box wb-odd-back" data-odd-cell="back-1"
           onclick="{{ $isBallByBall ? "window.location.href='" . $targetRoute . "'" : "WB24.openBetSlip({eventId:'" . $ev->event_id . "', eventName:'" . addslashes($ev->event_name) . "', marketName:'Match Odds', selectionId:'" . ($r1['selectionId'] ?? '2') . "', selectionName:'" . addslashes($r1['runnerName'] ?? 'Team 2') . "', betType:'BACK', odds:" . ($b2 > 0 ? $b2 : 2.10) . "})" }}">
        <strong>{{ $b2 ?: '0' }}</strong>
      </div>
      <div class="wb-odd-box wb-odd-lay" data-odd-cell="lay-1"
           onclick="{{ $isBallByBall ? "window.location.href='" . $targetRoute . "'" : "WB24.openBetSlip({eventId:'" . $ev->event_id . "', eventName:'" . addslashes($ev->event_name) . "', marketName:'Match Odds', selectionId:'" . ($r1['selectionId'] ?? '2') . "', selectionName:'" . addslashes($r1['runnerName'] ?? 'Team 2') . "', betType:'LAY', odds:" . ($l2 > 0 ? $l2 : 2.14) . "})" }}">
        <strong>{{ $l2 ?: '0' }}</strong>
      </div>
    </div>
  </div>

  <!-- Pin to Multi-Market -->
  <div class="wb-pin-cell">
    <button type="button" class="wb-pin-btn {{ $isPinned ? 'active' : '' }}"
            onclick="WB24.togglePin(this, '{{ $ev->event_id }}', '{{ $ev->sport_id }}')"
            title="Pin to Multi Markets">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
        <path d="M16 9V4h1c.55 0 1-.45 1-1s-.45-1-1-1H7c-.55 0-1 .45-1 1s.45 1 1 1h1v5c0 1.66-1.34 3-3 3v2h5.97v7l1 1 1-1v-7H19v-2c-1.66 0-3-1.34-3-3z"/>
      </svg>
    </button>
  </div>
</div>
