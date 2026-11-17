<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CasinoController;
use App\Http\Controllers\ExchangeController;
use Illuminate\Support\Facades\Route;

// Root redirects to /home exactly like winbook24.com
Route::get('/', fn () => redirect()->route('home'));
Route::get('/index.html', fn () => redirect()->route('home'));
Route::get('/inplay.html', fn () => redirect()->route('inplay'));
Route::get('/livecasino.html', fn () => redirect()->route('livecasino'));
Route::get('/fullMarket.html', fn () => redirect()->route('home'));

// Web App Manifest & Service Worker (PWA Install & Home Screen Shortcut)
Route::get('/manifest.json', function () {
    $title = \App\Models\SiteSetting::getValue('exchange_title', 'CRICEXPERTS');
    if (empty($title) || strtolower($title) === 'winbook24') {
        $title = 'CRICEXPERTS';
    }
    return response()->json([
        'name' => $title,
        'short_name' => $title,
        'start_url' => '/home',
        'scope' => '/',
        'display' => 'standalone',
        'background_color' => '#081225',
        'theme_color' => '#081225',
        'orientation' => 'portrait-primary',
        'description' => $title . ' - Live Sports Exchange, In-Play & Casino',
        'categories' => ['sports', 'games', 'entertainment'],
        'icons' => [
            [
                'src' => '/assets/brand/app-icon-192.png',
                'sizes' => '192x192',
                'type' => 'image/png',
                'purpose' => 'any'
            ],
            [
                'src' => '/assets/brand/app-icon-192.png',
                'sizes' => '192x192',
                'type' => 'image/png',
                'purpose' => 'maskable'
            ],
            [
                'src' => '/assets/brand/app-icon-512.png',
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'any'
            ],
            [
                'src' => '/assets/brand/app-icon-512.png',
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'maskable'
            ],
            [
                'src' => '/assets/brand/apple-touch-icon.png',
                'sizes' => '180x180',
                'type' => 'image/png',
                'purpose' => 'any'
            ]
        ]
    ])->header('Content-Type', 'application/manifest+json');
});
Route::get('/manifest.webmanifest', fn () => redirect('/manifest.json'));

// Public Exchange Pages
Route::get('/home', [ExchangeController::class, 'home'])->name('home');
Route::get('/inplay', [ExchangeController::class, 'inplay'])->name('inplay');
Route::get('/inPlay', [ExchangeController::class, 'inplay']);
Route::get('/multimarket', [ExchangeController::class, 'multimarket'])->name('multimarket');
Route::get('/multiMarkets', [ExchangeController::class, 'multimarket']);
Route::get('/allsport-highlight/{sportId}', [ExchangeController::class, 'sportHighlight'])->name('sport.highlight');
Route::get('/horse-racing', [ExchangeController::class, 'horseRacing'])->name('horse.racing');
Route::get('/horse-racing/inplayHorseGame/{eventId}/{sportId}', fn ($eventId, $sportId) => redirect()->route('full.market', ['sportId' => $sportId, 'eventId' => $eventId]));
Route::get('/fullMarket/{sportId}/{eventId}', [ExchangeController::class, 'fullMarket'])->name('full.market');
Route::get('/fullMarket/virtual-sports/{sportId}/{eventId}', [ExchangeController::class, 'fullMarket']);
Route::get('/ballByBall/{sportId}/{eventId}', [ExchangeController::class, 'ballByBall'])->name('ball.by.ball');
Route::get('/lottery/{sportId}/{eventId}', [ExchangeController::class, 'lotteryMarket'])->name('lottery.market');
Route::get('/virtualsports', [ExchangeController::class, 'virtualSports'])->name('virtual.sports');
Route::get('/tips-previews', [ExchangeController::class, 'tipsPreviews'])->name('tips.previews');
Route::get('/sports', [ExchangeController::class, 'inplay'])->name('sports.mobile');

// Casino Pages & Game Launcher
Route::get('/livecasino', [CasinoController::class, 'liveCasino'])->name('livecasino');
Route::get('/newCasino', [CasinoController::class, 'newCasino'])->name('newcasino');
Route::get('/vimaan/{eventId?}', [CasinoController::class, 'vimaan'])->where('eventId', '.*')->name('vimaan');
Route::get('/balloon/{eventId?}', [CasinoController::class, 'balloon'])->where('eventId', '.*')->name('casino.balloon');
Route::get('/lucky0To9/{eventId?}', [CasinoController::class, 'lucky0To9'])->where('eventId', '.*')->name('casino.lucky0to9');
Route::get('/headNtails/{eventId?}', [CasinoController::class, 'headNtails'])->where('eventId', '.*')->name('casino.headntails');
Route::get('/casino3Patti/{eventId?}', [CasinoController::class, 'teenPatti2020'])->where('eventId', '.*')->name('casino.teenpatti');
Route::get('/dragontiger/{eventId?}', [CasinoController::class, 'dragonTiger2020'])->where('eventId', '.*')->name('casino.dragontiger');
Route::get('/lucky7/{eventId?}', [CasinoController::class, 'lucky7'])->where('eventId', '.*')->name('casino.lucky7');
Route::get('/casino/play/{eventId}', [CasinoController::class, 'playGame'])->where('eventId', '.*')->name('casino.play');
Route::get('/casino/{eventId}', [CasinoController::class, 'playGame'])->where('eventId', '.*')->name('casino.room');
Route::get('/casino-detail/{eventId}', [CasinoController::class, 'playGame'])->where('eventId', '.*')->name('casino.detail');

// Authentication & MSG91 OTP Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::get('/authLogin', [AuthController::class, 'showLogin']);
Route::get('/signup', [AuthController::class, 'showSignup'])->name('signup');
Route::get('/forgetPassword', [AuthController::class, 'showForgotPassword'])->name('forgot.password');

Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login');
Route::post('/auth/demo-login', [AuthController::class, 'demoLogin'])->name('auth.demo');
Route::post('/auth/admin-demo', [AuthController::class, 'adminDemoLogin'])->name('auth.admin.demo');
Route::post('/auth/signup/send-otp', [AuthController::class, 'sendSignupOtp'])->name('auth.signup.otp');
Route::post('/auth/signup/verify', [AuthController::class, 'verifySignupAndCreate'])->name('auth.signup.verify');
Route::post('/auth/forgot/send-otp', [AuthController::class, 'sendForgotOtp'])->name('auth.forgot.otp');
Route::post('/auth/forgot/verify', [AuthController::class, 'verifyForgotAndReset'])->name('auth.forgot.verify');
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

// Also alias original Winbook24 /app/user/* endpoints to the same MSG91 OTP handlers
Route::post('/app/user/userRegisterOtpSent', [AuthController::class, 'sendSignupOtp']);
Route::post('/app/user/userRegisterVerify', [AuthController::class, 'verifySignupAndCreate']);
Route::post('/app/users/sendForgotPasswordOtp', [AuthController::class, 'sendForgotOtp']);
Route::post('/app/user/userForgotPasswordVerify', [AuthController::class, 'verifyForgotAndReset']);
Route::post('/app/users/userLogin', [AuthController::class, 'login']);

// Exchange & Casino JSON APIs
Route::get('/api/live-pulse', [ExchangeController::class, 'livePulse'])->name('api.live.pulse');
Route::get('/api/exchange/search', [ExchangeController::class, 'search'])->name('api.search');
Route::post('/api/exchange/place-bet', [ExchangeController::class, 'placeBet'])->name('api.place.bet');
Route::post('/api/exchange/cashout-bet', [ExchangeController::class, 'cashoutBet'])->name('api.cashout.bet');
Route::post('/api/exchange/toggle-pin', [ExchangeController::class, 'togglePin'])->name('api.toggle.pin');
Route::get('/api/exchange/ballbyball/state', [ExchangeController::class, 'ballByBallState'])->name('api.ballbyball.state');
Route::get('/api/exchange/ballbyball/results', [ExchangeController::class, 'ballByBallResults'])->name('api.ballbyball.results');
Route::post('/api/exchange/ballbyball/settle', [ExchangeController::class, 'ballByBallSettle'])->name('api.ballbyball.settle');
Route::get('/api/exchange/market/live/{sportId}/{eventId}', [ExchangeController::class, 'liveMarketData'])->name('api.market.live');
Route::post('/api/exchange/markets/getBallByBallMarket', [ExchangeController::class, 'ballByBallState']);
Route::post('/api/exchange/results/getMarketEventResults', [ExchangeController::class, 'ballByBallResults']);
Route::post('/api/exchange/markets/getMarketsEventList', [ExchangeController::class, 'liveMarketDataPost']);
Route::match(['get', 'post'], '/api/exchange/market/matchodds/allEventsList', [ExchangeController::class, 'allEventsListApi'])->name('api.exchange.allEventsList');
Route::get('/api/exchange/live-inplay-events', [ExchangeController::class, 'liveInplayEventsApi'])->name('api.exchange.liveInplay');
Route::post('/api/casino/play-round', [CasinoController::class, 'playRoundApi'])->name('api.casino.play');

// Sportmonks Live Score Backend APIs (Token stays strictly in Laravel Backend, never exposed in Frontend JS)
Route::get('/api/sportmonks/status', [ExchangeController::class, 'sportmonksStatus'])->name('api.sportmonks.status');
Route::get('/api/sportmonks/football/inplay', [ExchangeController::class, 'sportmonksFootballInplay'])->name('api.sportmonks.football.inplay');
Route::get('/api/sportmonks/cricket/livescores', [ExchangeController::class, 'sportmonksCricketLivescores'])->name('api.sportmonks.cricket.livescores');
Route::get('/api/sportmonks/cricket/balls', [ExchangeController::class, 'sportmonksCricketBalls'])->name('api.sportmonks.cricket.balls');
Route::get('/api/sportmonks/f1/live', [ExchangeController::class, 'sportmonksF1Live'])->name('api.sportmonks.f1.live');
Route::get('/api/cricbuzz/live', [ExchangeController::class, 'cricbuzzLive'])->name('api.cricbuzz.live');
Route::get('/api/livescore/tennis', [ExchangeController::class, 'livescoreTennis'])->name('api.livescore.tennis');
Route::get('/api/uefa/champions-league', [ExchangeController::class, 'uefaChampionsLeague'])->name('api.uefa.champions.league');

// Real-time VIMAAN & BALLOON Crash Game Endpoints (SQLite backed)
Route::post('/api/vimaan/place-bet', [CasinoController::class, 'vimaanPlaceBet'])->name('api.vimaan.place');
Route::post('/api/vimaan/cancel-bet', [CasinoController::class, 'vimaanCancelBet'])->name('api.vimaan.cancel');
Route::post('/api/vimaan/cashout', [CasinoController::class, 'vimaanCashout'])->name('api.vimaan.cashout');
Route::post('/api/vimaan/crash', [CasinoController::class, 'vimaanCrash'])->name('api.vimaan.crash');

Route::post('/api/balloon/place-bet', [CasinoController::class, 'vimaanPlaceBet'])->name('api.balloon.place');
Route::post('/api/balloon/cancel-bet', [CasinoController::class, 'vimaanCancelBet'])->name('api.balloon.cancel');
Route::post('/api/balloon/cashout', [CasinoController::class, 'vimaanCashout'])->name('api.balloon.cashout');
Route::post('/api/balloon/crash', [CasinoController::class, 'vimaanCrash'])->name('api.balloon.crash');

// Real-time 20-20 TEENPATTI (casino3Patti/99.0010) & LUCKY 7 (lucky7/99.0030) Endpoints (SQLite backed)
Route::post('/api/casino/teenpatti/place-bet', [CasinoController::class, 'teenpattiPlaceBet'])->name('api.teenpatti.place');
Route::post('/api/casino/teenpatti/settle-round', [CasinoController::class, 'teenpattiSettleRound'])->name('api.teenpatti.settle');
Route::post('/api/casino/lucky7/settle-round', [CasinoController::class, 'lucky7SettleRound'])->name('api.lucky7.settle');
Route::post('/api/casino/dragontiger/settle-round', [CasinoController::class, 'dragonTigerSettleRound'])->name('api.dragontiger.settle');
Route::post('/api/casino/lucky0to9/settle-round', [CasinoController::class, 'lucky0To9SettleRound'])->name('api.lucky0to9.settle');
Route::post('/api/casino/headntails/settle-round', [CasinoController::class, 'headNtailsSettleRound'])->name('api.headntails.settle');

// Settings accessible to everyone (saves to DB if logged in)
Route::get('/settings', [AccountController::class, 'settings'])->name('settings');
Route::get('/setting', [AccountController::class, 'settings']);
Route::post('/settings', [AccountController::class, 'saveSettings'])->name('settings.save');

// Authenticated User Account Routes
Route::middleware('auth')->group(function () {
    Route::get('/account', [AccountController::class, 'myProfile'])->name('account.hub');
    Route::get('/myprofile', [AccountController::class, 'myProfile'])->name('myprofile');
    Route::post('/myprofile/password', [AccountController::class, 'updatePassword'])->name('myprofile.password');
    Route::get('/2fa', [AccountController::class, 'twoFactor'])->name('two.factor');
    Route::post('/2fa/toggle', [AccountController::class, 'toggleTwoFactor'])->name('two.factor.toggle');
    Route::get('/refer-and-earn', [AccountController::class, 'referAndEarn'])->name('refer.earn');
    Route::get('/rolling-commission', [AccountController::class, 'rollingCommission'])->name('rolling.commission');

    // Banking & Laravel Storage Receipt Upload
    Route::get('/banking', [AccountController::class, 'banking'])->name('banking');
    Route::get('/banking/withdraw', [AccountController::class, 'bankingWithdraw'])->name('banking.withdraw.page');
    Route::get('/banking/withdrawdetails', [AccountController::class, 'bankingWithdrawDetails'])->name('banking.withdraw.details');
    Route::get('/banking/reward', [AccountController::class, 'bankingReward'])->name('banking.reward');
    Route::post('/banking/deposit', [AccountController::class, 'submitDeposit'])->name('banking.deposit');
    Route::post('/banking/bank-account', [AccountController::class, 'addBankAccount'])->name('banking.bank.add');
    Route::delete('/banking/bank-account/{id}', [AccountController::class, 'deleteBankAccount'])->name('banking.bank.delete');
    Route::post('/banking/withdraw', [AccountController::class, 'submitWithdraw'])->name('banking.withdraw');

    // Reports & Statements
    Route::get('/account-statement', [AccountController::class, 'accountStatement'])->name('account.statement');
    Route::get('/bet-history', [AccountController::class, 'betHistory'])->name('bet.history');
    Route::get('/profit-loss', [AccountController::class, 'profitLoss'])->name('profit.loss');
    Route::get('/password-history', [AccountController::class, 'passwordHistory'])->name('password.history');
    Route::get('/activity-log', [AccountController::class, 'activityLog'])->name('activity.log');
});

// Laravel Admin Panel Routes (Full A-to-Z Website & My Account CRUD Suite)
Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

    // 1. My Profile & Users CRUD + 360° My Account Inspector
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/users/{id}', [AdminController::class, 'showUser'])->name('users.show');
    Route::post('/users', [AdminController::class, 'createUser'])->name('users.create');
    Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('users.delete');
    Route::post('/users/{id}/balance', [AdminController::class, 'adjustUserBalance'])->name('users.balance');
    Route::post('/users/{id}/status', [AdminController::class, 'toggleUserStatus'])->name('users.status');

    // 2. Banking: Deposits, Withdrawals & Rewards CRUD
    Route::get('/banking', [AdminController::class, 'bankingRequests'])->name('banking');
    Route::post('/banking/create', [AdminController::class, 'createBankingTransaction'])->name('banking.create');
    Route::post('/banking/{id}', [AdminController::class, 'processBankingRequest'])->name('banking.process');
    Route::put('/banking/{id}', [AdminController::class, 'updateBankingTransaction'])->name('banking.update');
    Route::delete('/banking/{id}', [AdminController::class, 'deleteBankingTransaction'])->name('banking.delete');

    // 3. Withdrawal Details / Saved User Bank Accounts CRUD
    Route::get('/bank-accounts', [AdminController::class, 'bankAccounts'])->name('bank.accounts');
    Route::post('/bank-accounts', [AdminController::class, 'createBankAccount'])->name('bank.accounts.create');
    Route::put('/bank-accounts/{id}', [AdminController::class, 'updateBankAccount'])->name('bank.accounts.update');
    Route::delete('/bank-accounts/{id}', [AdminController::class, 'deleteBankAccount'])->name('bank.accounts.delete');

    // 4. Account Statement Ledger CRUD
    Route::get('/statements', [AdminController::class, 'accountStatements'])->name('statements');
    Route::post('/statements', [AdminController::class, 'createStatement'])->name('statements.create');
    Route::put('/statements/{id}', [AdminController::class, 'updateStatement'])->name('statements.update');
    Route::delete('/statements/{id}', [AdminController::class, 'deleteStatement'])->name('statements.delete');

    // 5. Bets History, Cash Out & Profit/Loss CRUD
    Route::get('/bets', [AdminController::class, 'bets'])->name('bets');
    Route::post('/bets', [AdminController::class, 'createBet'])->name('bets.create');
    Route::put('/bets/{id}', [AdminController::class, 'updateBet'])->name('bets.update');
    Route::post('/bets/{id}/cashout', [AdminController::class, 'adminCashoutBet'])->name('bets.cashout');
    Route::delete('/bets/{id}', [AdminController::class, 'deleteBet'])->name('bets.delete');

    // 6. Rolling Commission, Refer & Earn & Rolling Rewards CRUD
    Route::get('/commissions', [AdminController::class, 'commissions'])->name('commissions');
    Route::put('/commissions/{userId}', [AdminController::class, 'updateUserCommission'])->name('commissions.update');
    Route::post('/commissions/payout', [AdminController::class, 'payoutCommissionOrReward'])->name('commissions.payout');

    // 7. Two Factor Authentication (2FA) & Password History CRUD
    Route::get('/security', [AdminController::class, 'securityAndPasswords'])->name('security');
    Route::post('/security/2fa/{userId}', [AdminController::class, 'updateUserTwoFactor'])->name('security.2fa');
    Route::post('/security/password-history', [AdminController::class, 'createPasswordHistory'])->name('security.pw.create');
    Route::put('/security/password-history/{id}', [AdminController::class, 'updatePasswordHistory'])->name('security.pw.update');
    Route::delete('/security/password-history/{id}', [AdminController::class, 'deletePasswordHistory'])->name('security.pw.delete');

    // 8. Activity Logs & OTP Logs CRUD
    Route::get('/activity-logs', [AdminController::class, 'activityLogs'])->name('activity.logs');
    Route::post('/activity-logs', [AdminController::class, 'createActivityLog'])->name('activity.logs.create');
    Route::put('/activity-logs/{id}', [AdminController::class, 'updateActivityLog'])->name('activity.logs.update');
    Route::delete('/activity-logs/{id}', [AdminController::class, 'deleteActivityLog'])->name('activity.logs.delete');
    Route::delete('/otp-logs/{id}', [AdminController::class, 'deleteOtpLog'])->name('otp.logs.delete');

    // 9. Sports & Match Events CRUD
    Route::get('/events', [AdminController::class, 'events'])->name('events');
    Route::post('/events', [AdminController::class, 'createEvent'])->name('events.create');
    Route::put('/events/{id}', [AdminController::class, 'updateEvent'])->name('events.update');
    Route::delete('/events/{id}', [AdminController::class, 'deleteEvent'])->name('events.delete');
    Route::post('/events/{id}/toggle', [AdminController::class, 'toggleEventState'])->name('events.toggle');
    Route::post('/events/{id}/settle', [AdminController::class, 'settleEventMarket'])->name('events.settle');
    Route::post('/sports', [AdminController::class, 'createSport'])->name('sports.create');
    Route::put('/sports/{id}', [AdminController::class, 'updateSport'])->name('sports.update');
    Route::delete('/sports/{id}', [AdminController::class, 'deleteSport'])->name('sports.delete');

    // 10. Casino Games CRUD
    Route::get('/casino', [AdminController::class, 'casinoGames'])->name('casino');
    Route::post('/casino', [AdminController::class, 'createCasinoGame'])->name('casino.create');
    Route::post('/casino/{id}', [AdminController::class, 'updateCasinoGame'])->name('casino.update');
    Route::delete('/casino/{id}', [AdminController::class, 'deleteCasinoGame'])->name('casino.delete');

    // 11. Website, Payment & Banner Settings CRUD
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::post('/settings', [AdminController::class, 'saveSettings'])->name('settings.save');
    Route::delete('/settings/banner', [AdminController::class, 'deleteBanner'])->name('settings.banner.delete');
    Route::post('/settings/qr/delete', [AdminController::class, 'deleteDepositQrImage'])->name('settings.qr.delete');
    Route::post('/dashboard/quick-settings', [AdminController::class, 'saveQuickSettings'])->name('dashboard.quick-settings');

    // 12. Live Scoreboards (Cricbuzz, Football/UEFA, Tennis), Ball-By-Ball & Multi-Markets CRUD
    Route::get('/live-scores', [AdminController::class, 'liveScores'])->name('live.scores');
    Route::post('/live-scores/sync', [AdminController::class, 'syncLiveScoresFeed'])->name('live.scores.sync');
    Route::post('/live-scores/match', [AdminController::class, 'saveLiveScoreboard'])->name('live.scores.save');
    Route::delete('/live-scores/match/{id}', [AdminController::class, 'deleteLiveScoreboard'])->name('live.scores.delete');
    Route::post('/live-scores/ballbyball/runner', [AdminController::class, 'saveBallByBallRunner'])->name('live.scores.bbb.runner');
    Route::delete('/live-scores/ballbyball/runner/{selectionId}', [AdminController::class, 'deleteBallByBallRunner'])->name('live.scores.bbb.delete');
    Route::post('/live-scores/ballbyball/settle', [AdminController::class, 'adminSettleBallByBall'])->name('live.scores.bbb.settle');
    Route::post('/live-scores/multimarket', [AdminController::class, 'createMultiMarketPin'])->name('live.scores.mm.create');
    Route::delete('/live-scores/multimarket/{id}', [AdminController::class, 'deleteMultiMarketPin'])->name('live.scores.mm.delete');
});
