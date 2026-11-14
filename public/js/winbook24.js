/**
 * Winbook24 Interactive Exchange & Live Pulse Client Script
 */
window.WB24 = {
  currentSlip: null,
  pendingAuthAction: null,
  pendingAuthUrl: null,
  csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',

  init() {
    this.initHeaderInputs();
    this.initSearch();
    this.startLivePulse();
  },

  initHeaderInputs() {
    const uEl = document.getElementById('wbHeaderUsername');
    const pEl = document.getElementById('wbHeaderPassword');
    const sEl = document.getElementById('myInput');

    const unlockAndClear = (el) => {
      if (!el) return;
      el.value = '';
      el.addEventListener('input', () => {
        el.dataset.userTyped = '1';
      });
      el.addEventListener('keydown', (e) => {
        el.dataset.userTyped = '1';
        if (e.key === 'Enter' && (el === uEl || el === pEl)) {
          e.preventDefault();
          this.handleHeaderLoginClick();
        }
      });
      setTimeout(() => {
        if (!el.dataset.userTyped) {
          el.value = '';
        }
        el.removeAttribute('readonly');
      }, 60);
    };

    unlockAndClear(uEl);
    unlockAndClear(pEl);
    unlockAndClear(sEl);
  },

  showAgeModal() {
    const el = document.getElementById('ageconfirmationModal');
    if (el && window.bootstrap) {
      const modal = bootstrap.Modal.getOrCreateInstance(el);
      modal.show();
      return true;
    }
    return false;
  },

  handleHeaderLoginClick(event) {
    if (event) event.preventDefault();
    const uEl = document.getElementById('wbHeaderUsername');
    const pEl = document.getElementById('wbHeaderPassword');
    const username = (uEl?.value || '').trim();
    const password = pEl?.value || '';

    // Always open the 18+ confirmation modal when clicking Login in header (1:1 with winbook24.com)
    // unless already confirmed in this session
    if (!sessionStorage.getItem('wb24_age_confirmed')) {
      this.pendingAuthAction = (username && password) ? 'submit_header_login' : 'goto_login_portal';
      if (this.showAgeModal()) {
        return false;
      }
    }

    // If username or password is not provided, navigate to /login portal (1:1 with winbook24.com)
    if (!username || !password) {
      window.location.href = '/login';
      return false;
    }

    this.submitHeaderLoginAjax(username, password);
    return false;
  },

  handleHeaderLoginSubmit(event) {
    return this.handleHeaderLoginClick(event);
  },

  handleHeaderSignupClick() {
    if (!sessionStorage.getItem('wb24_age_confirmed')) {
      this.pendingAuthAction = 'goto_signup_portal';
      if (this.showAgeModal()) {
        return false;
      }
    }
    window.location.href = '/signup';
    return false;
  },

  handleMobileAuthClick(event, url) {
    if (!sessionStorage.getItem('wb24_age_confirmed')) {
      if (event) event.preventDefault();
      this.pendingAuthUrl = url;
      if (this.showAgeModal()) {
        return false;
      }
    }
    return true;
  },

  showHeaderLoginError(msg) {
    this.toast(msg || 'Invalid Login Details.', 'danger');
  },

  async submitHeaderLoginAjax(username, password) {
    const u = username ?? (document.getElementById('wbHeaderUsername')?.value || '').trim();
    const p = password ?? (document.getElementById('wbHeaderPassword')?.value || '');
    if (!u || !p) {
      window.location.href = '/login';
      return;
    }

    try {
      const res = await fetch('/auth/login', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': this.csrfToken,
        },
        body: JSON.stringify({ username: u, password: p }),
      });
      const data = await res.json();
      if (!res.ok || !data.status) {
        const msg = data.message || (data.errors ? Object.values(data.errors)[0]?.[0] : 'Invalid Login Details.');
        this.showHeaderLoginError(msg);
        return;
      }
      window.location.href = data.redirect || '/home';
    } catch (e) {
      this.showHeaderLoginError('Invalid Login Details.');
    }
  },

  confirmAge(proceedToUrl = null) {
    sessionStorage.setItem('wb24_age_confirmed', '1');
    const el = document.getElementById('ageconfirmationModal');
    if (el && window.bootstrap) {
      const inst = bootstrap.Modal.getInstance(el);
      if (inst) inst.hide();
    }
    if (proceedToUrl) {
      window.location.href = proceedToUrl;
      return;
    }
    if (this.pendingAuthUrl) {
      const target = this.pendingAuthUrl;
      this.pendingAuthUrl = null;
      window.location.href = target;
      return;
    }
    const action = this.pendingAuthAction;
    this.pendingAuthAction = null;
    if (action === 'submit_header_login') {
      this.submitHeaderLoginAjax();
    } else if (action === 'goto_login_portal') {
      window.location.href = '/login';
    } else if (action === 'goto_signup_portal') {
      window.location.href = '/signup';
    }
  },

  toggleSearch() {
    const box = document.getElementById('wbSearchBox');
    const input = document.getElementById('myInput');
    const dropdown = document.getElementById('wbSearchDropdown');
    if (!box) return;
    box.classList.toggle('open');
    if (box.classList.contains('open')) {
      input?.removeAttribute('readonly');
      input?.focus();
    } else {
      box.classList.remove('has-results');
      dropdown?.classList.remove('show');
    }
  },

  closeSearch() {
    const box = document.getElementById('wbSearchBox');
    const input = document.getElementById('myInput');
    const dropdown = document.getElementById('wbSearchDropdown');
    if (input) input.value = '';
    dropdown?.classList.remove('show');
    box?.classList.remove('has-results', 'open');
  },

  initSearch() {
    const box = document.getElementById('wbSearchBox');
    const input = document.getElementById('myInput');
    const dropdown = document.getElementById('wbSearchDropdown');
    const ul = document.getElementById('wbSearchGameUl');
    if (!input || !dropdown || !ul) return;

    let timer = null;
    input.addEventListener('input', () => {
      clearTimeout(timer);
      const q = input.value.trim();
      if (q.length < 1) {
        dropdown.classList.remove('show');
        box?.classList.remove('has-results');
        return;
      }
      timer = setTimeout(async () => {
        try {
          const res = await fetch(`/api/exchange/search?q=${encodeURIComponent(q)}`);
          const data = await res.json();
          let html = '';
          const nowStr = new Date().toLocaleDateString('en-GB') + ' ' + new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
          (data.events || []).forEach(ev => {
            let dtStr = nowStr;
            if (ev.event_time) {
              const dt = new Date(ev.event_time);
              if (!isNaN(dt.getTime())) {
                dtStr = dt.toLocaleDateString('en-GB') + ' ' + dt.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
              } else {
                dtStr = ev.event_time;
              }
            }
            html += `<li>
              <a href="/fullMarket/${ev.sport_id}/${ev.event_id}">
                <div class="d-flex justify-content-between w-100">
                  <div class="search-game-name d-flex"><b>${ev.sport_name} | MATCH_ODDS</b></div>
                  <div class="text-end">${dtStr}</div>
                </div>
                <div class="game-teams w-100">${ev.event_name}</div>
              </a>
            </li>`;
          });
          (data.casino || []).forEach(cg => {
            html += `<li>
              <a href="/casino/play/${encodeURIComponent(cg.event_id)}">
                <div class="d-flex justify-content-between w-100">
                  <div class="search-game-name d-flex"><b>Casino | LIVE</b></div>
                  <div class="text-end">24x7</div>
                </div>
                <div class="game-teams w-100">${cg.event_name}</div>
              </a>
            </li>`;
          });
          if (!html) {
            html = `<li class="text-muted text-center py-2">No matching events found</li>`;
          }
          ul.innerHTML = html;
          dropdown.classList.add('show');
          box?.classList.add('has-results');
        } catch (e) {}
      }, 200);
    });
  },

  openBetSlip(params) {
    this.currentSlip = {
      event_id: params.eventId,
      event_name: params.eventName || 'Match',
      market_name: params.marketName || 'Match Odds',
      selection_id: params.selectionId || '1',
      selection_name: params.selectionName,
      bet_type: params.betType || 'BACK',
      odds: parseFloat(params.odds) || 2.0,
      category: params.category || 'exchange',
      run_line: params.runLine || null,
      stake: 100,
    };

    const panel = document.getElementById('wbBetSlipPanel');
    if (!panel) return;

    panel.classList.remove('slip-back', 'slip-lay');
    panel.classList.add('open', ['LAY', 'NO'].includes(this.currentSlip.bet_type) ? 'slip-lay' : 'slip-back');

    document.getElementById('slipSelectionName').textContent = this.currentSlip.selection_name;
    document.getElementById('slipMarketName').textContent = `${this.currentSlip.event_name} • ${this.currentSlip.market_name}`;
    document.getElementById('slipBetTypeBadge').textContent = this.currentSlip.bet_type;
    document.getElementById('slipBetTypeBadge').className = `badge ${['LAY', 'NO'].includes(this.currentSlip.bet_type) ? 'bg-danger' : 'bg-primary'}`;
    document.getElementById('slipOddsInput').value = this.currentSlip.odds.toFixed(2);
    document.getElementById('slipStakeInput').value = this.currentSlip.stake;
    this.updateSlipCalculations();

    // If on mobile, open mobile betslip modal unless an inline betslip is active on the page
    if (window.innerWidth < 992 && !params.skipMobileModal) {
      const mblModal = document.getElementById('mobileBetSlipModal');
      if (mblModal && window.bootstrap) {
        document.getElementById('mblSlipSelection').textContent = this.currentSlip.selection_name;
        document.getElementById('mblSlipOdds').value = this.currentSlip.odds.toFixed(2);
        document.getElementById('mblSlipStake').value = this.currentSlip.stake;
        new bootstrap.Modal(mblModal).show();
      }
    }
  },

  closeBetSlip() {
    this.currentSlip = null;
    document.getElementById('wbBetSlipPanel')?.classList.remove('open');
  },

  stepOdds(delta) {
    const inp = document.getElementById('slipOddsInput');
    if (!inp || !this.currentSlip) return;
    let val = Math.max(1.01, parseFloat(inp.value || 2) + delta);
    inp.value = val.toFixed(2);
    this.currentSlip.odds = val;
    this.updateSlipCalculations();
  },

  setStake(amount) {
    const inp = document.getElementById('slipStakeInput');
    if (inp) inp.value = amount;
    const mbl = document.getElementById('mblSlipStake');
    if (mbl) mbl.value = amount;
    if (this.currentSlip) {
      this.currentSlip.stake = parseFloat(amount) || 0;
      this.updateSlipCalculations();
    }
  },

  updateSlipCalculations() {
    if (!this.currentSlip) return;
    const odds = parseFloat(document.getElementById('slipOddsInput')?.value || this.currentSlip.odds);
    const stake = parseFloat(document.getElementById('slipStakeInput')?.value || this.currentSlip.stake);
    this.currentSlip.odds = odds;
    this.currentSlip.stake = stake;

    let profit = 0;
    let liability = 0;
    if (this.currentSlip.category === 'bookmaker') {
      profit = this.currentSlip.bet_type === 'BACK' ? stake * (odds / 100) : stake;
      liability = this.currentSlip.bet_type === 'BACK' ? stake : stake * (odds / 100);
    } else if (this.currentSlip.category === 'fancy') {
      profit = this.currentSlip.bet_type === 'YES' ? stake * (odds / 100) : stake;
      liability = this.currentSlip.bet_type === 'YES' ? stake : stake * (odds / 100);
    } else {
      profit = this.currentSlip.bet_type === 'BACK' ? stake * (odds - 1) : stake;
      liability = this.currentSlip.bet_type === 'BACK' ? stake : stake * (odds - 1);
    }

    const pEl = document.getElementById('slipProfitVal');
    const lEl = document.getElementById('slipLiabilityVal');
    if (pEl) pEl.textContent = profit.toFixed(2);
    if (lEl) lEl.textContent = liability.toFixed(2);

    if (window.FullMkt && typeof window.FullMkt.syncFromSidebarSlip === 'function') {
      window.FullMkt.syncFromSidebarSlip(odds, stake);
    }
  },

  async submitBet(fromMobile = false) {
    if (!this.currentSlip) return;
    if (fromMobile) {
      this.currentSlip.odds = parseFloat(document.getElementById('mblSlipOdds')?.value || this.currentSlip.odds);
      this.currentSlip.stake = parseFloat(document.getElementById('mblSlipStake')?.value || this.currentSlip.stake);
    } else {
      this.updateSlipCalculations();
    }

    const btn = document.getElementById('btnPlaceBetSubmit');
    if (btn) btn.disabled = true;

    try {
      const res = await fetch('/api/exchange/place-bet', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': this.csrfToken,
        },
        body: JSON.stringify(this.currentSlip),
      });
      const data = await res.json();
      if (!res.ok || !data.status) {
        this.toast(data.message || 'Unable to place bet.', 'danger');
        if (data.redirect) setTimeout(() => (window.location.href = data.redirect), 900);
        return;
      }

      this.toast(data.message, 'success');
      this.updateWalletDisplay(data.wallet);
      this.prependOpenBet(data.bet);
      if (window.FullMkt && typeof window.FullMkt.onBetPlaced === 'function') {
        window.FullMkt.onBetPlaced(data.bet);
      }
      this.closeBetSlip();
    } catch (e) {
      this.toast('Network error while placing bet.', 'danger');
    } finally {
      if (btn) btn.disabled = false;
    }
  },

  async cashoutBet(betId, mode = 'cashout') {
    try {
      const res = await fetch('/api/exchange/cashout-bet', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': this.csrfToken,
        },
        body: JSON.stringify({ bet_id: betId, mode }),
      });
      const data = await res.json();
      if (data.status) {
        this.toast(data.message, 'success');
        this.updateWalletDisplay(data.wallet);
        document.getElementById(`openBetRow_${betId}`)?.remove();
        if (window.FullMkt && typeof window.FullMkt.onBetRemoved === 'function') {
          window.FullMkt.onBetRemoved(betId);
        }
      } else {
        this.toast(data.message || 'Cashout failed', 'danger');
      }
    } catch (e) {}
  },

  async togglePin(btn, eventId, sportId = '4') {
    try {
      const res = await fetch('/api/exchange/toggle-pin', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': this.csrfToken,
        },
        body: JSON.stringify({ event_id: eventId, sport_id: sportId }),
      });
      const data = await res.json();
      if (data.status) {
        btn.classList.toggle('active', data.pinned);
        this.toast(data.message, 'info');
      }
    } catch (e) {}
  },

  updateWalletDisplay(wallet) {
    if (!wallet) return;
    document.querySelectorAll('.js-user-balance').forEach(el => {
      el.textContent = parseFloat(wallet.available ?? wallet.balance).toFixed(2);
    });
    document.querySelectorAll('.js-user-exposure').forEach(el => {
      el.textContent = parseFloat(wallet.exposure).toFixed(2);
    });
  },

  prependOpenBet(bet) {
    const list = document.getElementById('wbOpenBetsList');
    if (!list || !bet) return;
    const emptyMsg = list.querySelector('.js-no-open-bets');
    if (emptyMsg) emptyMsg.remove();

    const div = document.createElement('div');
    div.id = `openBetRow_${bet.id}`;
    div.className = `p-2 mb-1 border-bottom small ${bet.bet_type === 'LAY' ? 'bg-danger-subtle' : 'bg-info-subtle'}`;
    div.innerHTML = `
      <div class="d-flex justify-content-between fw-bold">
        <span>${bet.selection_name} <span class="badge bg-dark">${bet.bet_type}</span></span>
        <span>@ ${parseFloat(bet.odds).toFixed(2)}</span>
      </div>
      <div class="text-muted" style="font-size:10px;">${bet.event_name} (${bet.market_name})</div>
      <div class="d-flex justify-content-between align-items-center mt-1">
        <span>Stake: <b>${parseFloat(bet.stake).toFixed(2)}</b> | P/L: <b class="text-success">+${parseFloat(bet.potential_profit).toFixed(2)}</b></span>
        <div class="btn-group btn-group-sm">
          <button type="button" class="btn btn-warning btn-sm py-0 px-1" style="font-size:10px;" onclick="WB24.cashoutBet(${bet.id}, 'cashout')">Cashout</button>
          <button type="button" class="btn btn-success btn-sm py-0 px-1" style="font-size:10px;" onclick="WB24.cashoutBet(${bet.id}, 'instant_settle')">Settle</button>
        </div>
      </div>
    `;
    list.prepend(div);
  },

  startLivePulse() {
    setInterval(async () => {
      try {
        const res = await fetch('/api/live-pulse');
        if (!res.ok) return;
        const data = await res.json();
        if (data.wallet) {
          this.updateWalletDisplay(data.wallet);
        }
        if (data.updates) {
          Object.entries(data.updates).forEach(([eventId, payload]) => {
            const row = document.querySelector(`[data-event-row="${eventId}"]`);
            if (!row) return;

            // In-Play badge sync
            if (payload.in_play !== undefined) {
              const wasInPlay = row.getAttribute('data-is-inplay') === '1';
              const isInPlayNow = Boolean(payload.in_play);
              row.setAttribute('data-is-inplay', isInPlayNow ? '1' : '0');

              // If match ended while inside inplay pane, hide it
              const inplayContainer = row.closest('#ipPane_inplay');
              if (inplayContainer && !isInPlayNow) {
                row.style.display = 'none';
              }
            }

            // Live score summary
            const liveShortSummary = payload.score?.cricbuzz?.shortScoreSummary || payload.score?.tennis?.shortScoreSummary || payload.score?.soccer?.shortScoreSummary;
            if (liveShortSummary) {
              const scorePill = row.querySelector(`[data-cricbuzz-score="${eventId}"] .js-cb-short-score`);
              if (scorePill) {
                scorePill.textContent = liveShortSummary;
              }
            }

            // Badges (BM, F, S)
            const badgesWrap = row.querySelector('.wb-badges');
            if (badgesWrap) {
              if (payload.is_bookmakers && !badgesWrap.querySelector('.wb-badge-bm')) {
                const bm = document.createElement('span');
                bm.className = 'wb-badge-bm';
                bm.title = 'Bookmaker';
                bm.textContent = 'BM';
                badgesWrap.appendChild(bm);
              }
              if (payload.is_fancy && !badgesWrap.querySelector('.wb-badge-f')) {
                const f = document.createElement('span');
                f.className = 'wb-badge-f';
                f.title = 'Fancy';
                f.textContent = 'F';
                badgesWrap.appendChild(f);
              }
              if (payload.is_sportsbook && !badgesWrap.querySelector('.wb-badge-s')) {
                const s = document.createElement('span');
                s.className = 'wb-badge-s';
                s.title = 'Sportsbook';
                s.textContent = 'S';
                badgesWrap.appendChild(s);
              }
            }

            if (!payload.runners) return;
            payload.runners.forEach((runner, idx) => {
              const backBox = row.querySelector(`[data-odd-cell="back-${idx}"]`);
              const layBox = row.querySelector(`[data-odd-cell="lay-${idx}"]`);
              const backEl = backBox ? backBox.querySelector('strong') : null;
              const layEl = layBox ? layBox.querySelector('strong') : null;
              const backPrice = runner.back?.[0]?.price ?? 0;
              const layPrice = runner.lay?.[0]?.price ?? 0;
              const backStr = String(backPrice || '0');
              const layStr = String(layPrice || '0');

              if (backBox) backBox.setAttribute('data-odds', backStr);
              if (layBox) layBox.setAttribute('data-odds', layStr);

              if (backEl && backEl.textContent.trim() !== backStr) {
                backEl.textContent = backStr;
                backBox.classList.add('flash-change');
                setTimeout(() => backBox.classList.remove('flash-change'), 600);
              }
              if (layEl && layEl.textContent.trim() !== layStr) {
                layEl.textContent = layStr;
                layBox.classList.add('flash-change');
                setTimeout(() => layBox.classList.remove('flash-change'), 600);
              }
            });
          });

          // Sync inplay badge counter
          const inplayPane = document.getElementById('ipPane_inplay');
          if (inplayPane) {
            const visibleLive = inplayPane.querySelectorAll('.wb-match-row:not([style*="display: none"])');
            const inplayBadge = document.querySelector('.js-ip-tab[onclick*="inplay"] .wb-filter-count');
            if (inplayBadge && visibleLive.length >= 0) {
              inplayBadge.textContent = visibleLive.length;
            }
          }
        }
      } catch (e) {}
    }, 2500);
  },

  toast(message, type = 'info') {
    let container = document.getElementById('wbToastContainer');
    if (!container) {
      container = document.createElement('div');
      container.id = 'wbToastContainer';
      container.style.cssText = 'position:fixed;top:14px;left:50%;transform:translateX(-50%);z-index:9999;min-width:290px;max-width:440px;';
      document.body.appendChild(container);
    }
    const alert = document.createElement('div');
    const bg = type === 'success' ? '#2e7d32' : (type === 'danger' ? '#c62828' : '#14213D');
    alert.style.cssText = `background:${bg};color:#fff;padding:9px 16px;border-radius:5px;margin-bottom:6px;box-shadow:0 4px 12px rgba(0,0,0,0.4);font-weight:700;font-size:12px;display:flex;justify-content:space-between;align-items:center;`;
    alert.innerHTML = `<span>${message}</span><span style="cursor:pointer;margin-left:10px;" onclick="this.parentElement.remove()">×</span>`;
    container.appendChild(alert);
    setTimeout(() => alert.remove(), 3800);
  }
};

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => WB24.init());
} else {
  WB24.init();
}

