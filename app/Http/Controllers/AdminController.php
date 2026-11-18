<?php

namespace App\Http\Controllers;

use App\Models\AccountStatement;
use App\Models\ActivityLog;
use App\Models\BankingTransaction;
use App\Models\Bet;
use App\Models\CasinoGame;
use App\Models\ExchangeEvent;
use App\Models\OtpVerification;
use App\Models\PasswordHistory;
use App\Models\SiteSetting;
use App\Models\Sport;
use App\Models\User;
use App\Models\UserBankAccount;
use App\Models\UserMultiMarket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    /**
     * Master Dashboard Overview with KPIs, My Account Summaries & Recent Activity
     */
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'suspended_users' => User::where('status', '!=', 'active')->count(),
            'two_factor_users' => User::where('two_factor_enabled', true)->count(),
            'total_balance' => round((float) User::sum('balance'), 2),
            'total_exposure' => round((float) User::sum('exposure'), 2),
            'total_bonus' => round((float) User::sum('bonus_balance'), 2),
            'total_events' => ExchangeEvent::count(),
            'inplay_events' => ExchangeEvent::where('in_play', true)->count(),
            'casino_games' => CasinoGame::count(),
            'total_bets' => Bet::count(),
            'open_bets' => Bet::where('status', 'MATCHED')->count(),
            'won_bets' => Bet::where('status', 'WON')->count(),
            'lost_bets' => Bet::where('status', 'LOST')->count(),
            'cashout_bets' => Bet::where('status', 'CASHOUT')->count(),
            'pinned_multimarkets' => UserMultiMarket::count(),
            'total_deposits' => round((float) BankingTransaction::where('type', 'deposit')->where('status', 'APPROVED')->sum('final_amount'), 2),
            'total_withdrawals' => round((float) BankingTransaction::where('type', 'withdraw')->where('status', 'APPROVED')->sum('final_amount'), 2),
            'pending_deposits' => BankingTransaction::where('type', 'deposit')->where('status', 'PENDING')->count(),
            'pending_withdrawals' => BankingTransaction::where('type', 'withdraw')->where('status', 'PENDING')->count(),
            'saved_bank_accounts' => UserBankAccount::count(),
            'total_statements' => AccountStatement::count(),
            'total_activity_logs' => ActivityLog::count(),
            'house_pl' => round(-1 * (float) Bet::whereIn('status', ['WON', 'LOST', 'CASHOUT'])->sum('profit_loss'), 2),
        ];

        $recentBets = Bet::with('user')->latest()->take(10)->get();
        $recentOtps = OtpVerification::latest()->take(8)->get();
        $pendingTx = BankingTransaction::with('user')->latest()->take(8)->get();
        $recentUsers = User::latest()->take(8)->get();
        $recentLogs = ActivityLog::with('user')->latest()->take(8)->get();
        $supportWhatsapp = SiteSetting::getValue('support_whatsapp', '+919831391470');
        $depositQrImage = SiteSetting::getValue('deposit_qr_image', '');
        $depositUpi = SiteSetting::getValue('deposit_upi_id', 's7484819820530667@slc');

        return view('admin.dashboard', compact(
            'stats', 'recentBets', 'recentOtps', 'pendingTx', 'recentUsers', 'recentLogs',
            'supportWhatsapp', 'depositQrImage', 'depositUpi'
        ));
    }

    // =========================================================================
    // 1. MY PROFILE & USERS MANAGEMENT (CRUD + 360° MY ACCOUNT INSPECTOR)
    // =========================================================================

    public function users(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $role = $request->query('role', 'all');
        $query = User::withCount(['bets', 'bankAccounts', 'bankingTransactions', 'statements']);

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('username', 'like', "%{$q}%")
                    ->orWhere('name', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('referral_code', 'like', "%{$q}%");
            });
        }

        if ($role === 'admin') {
            $query->where('is_admin', true);
        } elseif ($role === 'player') {
            $query->where('is_admin', false)->where('is_demo', false);
        } elseif ($role === 'demo') {
            $query->where('is_demo', true);
        } elseif ($role === 'suspended') {
            $query->where('status', '!=', 'active');
        }

        $users = $query->latest()->paginate(25)->withQueryString();

        return view('admin.users', compact('users', 'q', 'role'));
    }

    /**
     * Full 360° "My Account" Inspector for a Single User (All 10 My Account Modules in One View)
     */
    public function showUser(int $id)
    {
        $user = User::findOrFail($id);
        $bankAccounts = UserBankAccount::where('user_id', $user->id)->latest()->get();
        $transactions = BankingTransaction::where('user_id', $user->id)->latest()->take(50)->get();
        $statements = AccountStatement::where('user_id', $user->id)->latest()->take(50)->get();
        $bets = Bet::where('user_id', $user->id)->latest()->take(50)->get();
        $passwordHistories = PasswordHistory::where('user_id', $user->id)->latest()->take(30)->get();
        $activityLogs = ActivityLog::where('user_id', $user->id)->latest()->take(30)->get();
        $referrals = User::where('referred_by', $user->username)->orWhere('referred_by', $user->referral_code)->latest()->get();

        $plSummary = [
            'exchange_pl' => round((float) Bet::where('user_id', $user->id)->where('category', 'exchange')->whereIn('status', ['WON', 'LOST', 'CASHOUT'])->sum('profit_loss'), 2),
            'casino_pl' => round((float) Bet::where('user_id', $user->id)->where('category', 'casino')->whereIn('status', ['WON', 'LOST', 'CASHOUT'])->sum('profit_loss'), 2),
            'total_pl' => round((float) Bet::where('user_id', $user->id)->whereIn('status', ['WON', 'LOST', 'CASHOUT'])->sum('profit_loss'), 2),
            'total_deposits' => round((float) BankingTransaction::where('user_id', $user->id)->where('type', 'deposit')->where('status', 'APPROVED')->sum('final_amount'), 2),
            'total_withdrawals' => round((float) BankingTransaction::where('user_id', $user->id)->where('type', 'withdraw')->where('status', 'APPROVED')->sum('final_amount'), 2),
        ];

        return view('admin.user-show', compact(
            'user',
            'bankAccounts',
            'transactions',
            'statements',
            'bets',
            'passwordHistories',
            'activityLogs',
            'referrals',
            'plSummary'
        ));
    }

    public function createUser(Request $request)
    {
        $request->validate([
            'username' => 'required|string|min:3|max:40|unique:users,username',
            'name' => 'nullable|string|max:80',
            'email' => 'nullable|email|max:120',
            'phone' => 'required|string|min:7|max:18',
            'password' => 'required|string|min:6',
            'balance' => 'required|numeric|min:0',
            'bonus_balance' => 'nullable|numeric|min:0',
            'referral_code' => 'nullable|string|max:30',
            'referred_by' => 'nullable|string|max:30',
            'is_admin' => 'nullable|boolean',
            'two_factor_enabled' => 'nullable|boolean',
        ]);

        $username = trim($request->input('username'));
        $user = User::create([
            'username' => $username,
            'name' => trim((string) $request->input('name')) ?: $username,
            'email' => trim((string) $request->input('email')) ?: (strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $username)) . '@cricexperts.com'),
            'country_code' => $request->input('country_code', '+91'),
            'phone' => preg_replace('/\D/', '', $request->input('phone')),
            'phone_verified_at' => now(),
            'password' => Hash::make($request->input('password')),
            'balance' => round((float) $request->input('balance'), 2),
            'exposure' => 0,
            'bonus_balance' => round((float) $request->input('bonus_balance', 0), 2),
            'referral_code' => trim((string) $request->input('referral_code')) ?: ('CRIC' . strtoupper(Str::random(5))),
            'referred_by' => trim((string) $request->input('referred_by')) ?: null,
            'is_admin' => $request->boolean('is_admin'),
            'two_factor_enabled' => $request->boolean('two_factor_enabled'),
            'two_factor_code' => strtoupper(Str::random(10)),
            'status' => 'active',
            'rolling_commission' => [
                'fancy' => (float) $request->input('rc_fancy', 0),
                'matka' => (float) $request->input('rc_matka', 0),
                'casino' => (float) $request->input('rc_casino', 0),
                'binary' => (float) $request->input('rc_binary', 0),
                'sportbook' => (float) $request->input('rc_sportbook', 0),
                'bookmaker' => (float) $request->input('rc_bookmaker', 0),
            ],
        ]);

        AccountStatement::create([
            'user_id' => $user->id,
            'credit' => $user->balance,
            'debit' => 0,
            'balance' => $user->balance,
            'remark' => 'Admin Created Account Opening Balance',
            'from_to' => 'Admin -> ' . $user->username,
            'ref_type' => 'admin',
        ]);

        PasswordHistory::create([
            'user_id' => $user->id,
            'changed_by' => 'Admin',
            'remark' => 'Account Created & Initial Password Set by Admin',
            'ip_address' => $request->ip() ?? '127.0.0.1',
        ]);

        return back()->with('success', "User {$user->username} created with {$user->balance} PTI!");
    }

    public function updateUser(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'username' => 'required|string|min:3|max:40|unique:users,username,' . $user->id,
            'name' => 'nullable|string|max:80',
            'email' => 'nullable|string|max:120',
            'phone' => 'required|string|min:5|max:20',
            'balance' => 'required|numeric|min:0',
            'exposure' => 'required|numeric|min:0',
            'bonus_balance' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,suspended,locked',
        ]);

        $data = [
            'username' => trim($request->input('username')),
            'name' => trim((string) $request->input('name')) ?: trim($request->input('username')),
            'email' => trim((string) $request->input('email')),
            'country_code' => trim((string) $request->input('country_code', $user->country_code ?: '+91')),
            'phone' => trim($request->input('phone')),
            'balance' => round((float) $request->input('balance'), 2),
            'exposure' => round((float) $request->input('exposure'), 2),
            'bonus_balance' => round((float) $request->input('bonus_balance', $user->bonus_balance), 2),
            'referral_code' => trim((string) $request->input('referral_code', $user->referral_code)),
            'referred_by' => trim((string) $request->input('referred_by')) ?: null,
            'status' => $request->input('status'),
            'is_admin' => $request->boolean('is_admin'),
            'two_factor_enabled' => $request->boolean('two_factor_enabled'),
        ];

        if ($request->has('rc_fancy') || $request->has('rc_casino')) {
            $data['rolling_commission'] = [
                'fancy' => (float) $request->input('rc_fancy', $user->rolling_commission['fancy'] ?? 0),
                'matka' => (float) $request->input('rc_matka', $user->rolling_commission['matka'] ?? 0),
                'casino' => (float) $request->input('rc_casino', $user->rolling_commission['casino'] ?? 0),
                'binary' => (float) $request->input('rc_binary', $user->rolling_commission['binary'] ?? 0),
                'sportbook' => (float) $request->input('rc_sportbook', $user->rolling_commission['sportbook'] ?? 0),
                'bookmaker' => (float) $request->input('rc_bookmaker', $user->rolling_commission['bookmaker'] ?? 0),
            ];
        }

        if ($request->has('one_click_stake') || $request->has('one_click_bet_enabled')) {
            $data['one_click_bet_enabled'] = $request->boolean('one_click_bet_enabled');
            $data['one_click_stake'] = max(10, (int) $request->input('one_click_stake', $user->one_click_stake ?: 500));
        }

        if ($request->has('chip_1')) {
            $chips = [];
            for ($i = 1; $i <= 8; $i++) {
                $val = (int) $request->input("chip_{$i}", $i * 500);
                $lbl = trim((string) $request->input("chip_label_{$i}", (string) $val));
                if ($val > 0) {
                    $chips[$lbl ?: (string) $val] = $val;
                }
            }
            if (!empty($chips)) {
                $data['stake_chips'] = $chips;
            }
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->input('password'));
            PasswordHistory::create([
                'user_id' => $user->id,
                'changed_by' => 'Admin (' . (auth()->user()->username ?? 'Master') . ')',
                'remark' => 'Password Reset by Administrator',
                'ip_address' => $request->ip() ?? '127.0.0.1',
            ]);
        }

        $user->update($data);

        return back()->with('success', "Profile & Account settings updated for {$user->username}!");
    }

    public function deleteUser(int $id)
    {
        $user = User::findOrFail($id);
        if ($user->id === auth()->id()) {
            return back()->withErrors(['user' => 'You cannot delete your own active Admin account.']);
        }
        $uname = $user->username;
        $user->delete();

        return redirect()->route('admin.users')->with('success', "User account {$uname} and associated records deleted.");
    }

    public function adjustUserBalance(Request $request, int $id)
    {
        $request->validate([
            'action' => 'required|in:credit,debit',
            'amount' => 'required|numeric|min:1',
            'remark' => 'nullable|string|max:200',
        ]);

        $user = User::findOrFail($id);
        $amount = round((float) $request->input('amount'), 2);
        $action = $request->input('action');
        $remark = trim((string) $request->input('remark')) ?: ('Admin ' . ucfirst($action) . ' PTI');

        if ($action === 'debit' && $user->balance < $amount) {
            return back()->withErrors(['amount' => 'User balance is lower than debit amount.']);
        }

        DB::transaction(function () use ($user, $amount, $action, $remark) {
            if ($action === 'credit') {
                $user->increment('balance', $amount);
            } else {
                $user->decrement('balance', $amount);
            }

            AccountStatement::create([
                'user_id' => $user->id,
                'credit' => $action === 'credit' ? $amount : 0,
                'debit' => $action === 'debit' ? $amount : 0,
                'balance' => $user->fresh()->balance,
                'remark' => $remark,
                'from_to' => $action === 'credit' ? "Admin -> {$user->username}" : "{$user->username} -> Admin",
                'ref_type' => 'admin',
            ]);
        });

        return back()->with('success', ucfirst($action) . "ed {$amount} PTI for {$user->username}!");
    }

    public function toggleUserStatus(int $id)
    {
        $user = User::findOrFail($id);
        $user->update([
            'status' => $user->status === 'active' ? 'suspended' : 'active',
        ]);
        return back()->with('success', "Updated {$user->username} status to {$user->status}.");
    }

    // =========================================================================
    // 2. BANKING: DEPOSITS, WITHDRAWALS & REWARDS CRUD
    // =========================================================================

    public function bankingRequests(Request $request)
    {
        $type = $request->query('type', 'all');
        $status = $request->query('status', 'all');
        $q = trim((string) $request->query('q', ''));

        $query = BankingTransaction::with('user');
        if ($type !== 'all') {
            $query->where('type', $type);
        }
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('tx_ref', 'like', "%{$q}%")
                    ->orWhere('utr_number', 'like', "%{$q}%")
                    ->orWhere('method', 'like', "%{$q}%")
                    ->orWhereHas('user', fn ($u) => $u->where('username', 'like', "%{$q}%"));
            });
        }

        $transactions = $query->latest()->paginate(25)->withQueryString();
        $users = User::orderBy('username')->get(['id', 'username', 'balance']);

        $summary = [
            'approved_deposits' => round((float) BankingTransaction::where('type', 'deposit')->where('status', 'APPROVED')->sum('final_amount'), 2),
            'approved_withdrawals' => round((float) BankingTransaction::where('type', 'withdraw')->where('status', 'APPROVED')->sum('final_amount'), 2),
            'pending_count' => BankingTransaction::where('status', 'PENDING')->count(),
            'rewards_paid' => round((float) BankingTransaction::where('type', 'reward')->where('status', 'APPROVED')->sum('final_amount'), 2),
        ];

        return view('admin.banking', compact('transactions', 'users', 'type', 'status', 'q', 'summary'));
    }

    public function createBankingTransaction(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:deposit,withdraw,reward',
            'method' => 'required|string|max:40',
            'amount' => 'required|numeric|min:1',
            'charges' => 'nullable|numeric|min:0',
            'utr_number' => 'nullable|string|max:80',
            'status' => 'required|in:PENDING,APPROVED,REJECTED',
            'admin_note' => 'nullable|string|max:255',
            'adjust_wallet' => 'nullable|boolean',
        ]);

        $user = User::findOrFail($request->input('user_id'));
        $type = $request->input('type');
        $amount = round((float) $request->input('amount'), 2);
        $charges = round((float) $request->input('charges', 0), 2);
        $finalAmount = max(0, round($amount - $charges, 2));
        $status = $request->input('status');
        $prefix = $type === 'deposit' ? 'DEP' : ($type === 'withdraw' ? 'WDR' : 'RWD');

        DB::transaction(function () use ($request, $user, $type, $amount, $charges, $finalAmount, $status, $prefix) {
            $tx = BankingTransaction::create([
                'tx_ref' => $prefix . strtoupper(Str::random(8)),
                'user_id' => $user->id,
                'type' => $type,
                'method' => trim($request->input('method')),
                'amount' => $amount,
                'charges' => $charges,
                'final_amount' => $finalAmount,
                'utr_number' => trim((string) $request->input('utr_number')) ?: ('UTR' . random_int(1000000000, 9999999999)),
                'bank_details_json' => [
                    'bank_name' => $request->input('bank_name', 'HDFC Bank'),
                    'account_number' => $request->input('account_number', ''),
                    'ifsc_code' => $request->input('ifsc_code', ''),
                    'holder_name' => $user->name,
                ],
                'status' => $status,
                'admin_note' => trim((string) $request->input('admin_note')) ?: ('Created by Admin (' . $status . ')'),
                'processed_at' => $status !== 'PENDING' ? now() : null,
            ]);

            if ($request->boolean('adjust_wallet', true) && $status === 'APPROVED') {
                if (in_array($type, ['deposit', 'reward'], true)) {
                    $user->increment('balance', $finalAmount);
                    AccountStatement::create([
                        'user_id' => $user->id,
                        'credit' => $finalAmount,
                        'debit' => 0,
                        'balance' => $user->fresh()->balance,
                        'remark' => ucfirst($type) . " Approved ({$tx->method}) Ref: {$tx->tx_ref}",
                        'from_to' => "Admin Banking -> {$user->username}",
                        'ref_type' => $type,
                        'ref_id' => $tx->tx_ref,
                    ]);
                } elseif ($type === 'withdraw') {
                    $deduct = min($user->balance, $amount);
                    $user->decrement('balance', $deduct);
                    AccountStatement::create([
                        'user_id' => $user->id,
                        'credit' => 0,
                        'debit' => $deduct,
                        'balance' => $user->fresh()->balance,
                        'remark' => "Withdrawal Approved ({$tx->method}) Ref: {$tx->tx_ref}",
                        'from_to' => "{$user->username} -> Admin Banking",
                        'ref_type' => 'withdraw',
                        'ref_id' => $tx->tx_ref,
                    ]);
                }
            }
        });

        return back()->with('success', "New {$type} transaction created for {$user->username}!");
    }

    public function processBankingRequest(Request $request, int $id)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'admin_note' => 'nullable|string|max:255',
        ]);

        $tx = BankingTransaction::findOrFail($id);
        if ($tx->status !== 'PENDING') {
            return back()->withErrors(['status' => 'Transaction already processed.']);
        }

        $user = User::findOrFail($tx->user_id);
        $action = $request->input('action');

        DB::transaction(function () use ($tx, $user, $action, $request) {
            if ($action === 'approve') {
                $tx->update([
                    'status' => 'APPROVED',
                    'admin_note' => $request->input('admin_note') ?: 'Approved by Admin',
                    'processed_at' => now(),
                ]);
                if (in_array($tx->type, ['deposit', 'reward'], true)) {
                    $user->increment('balance', $tx->final_amount);
                    AccountStatement::create([
                        'user_id' => $user->id,
                        'credit' => $tx->final_amount,
                        'debit' => 0,
                        'balance' => $user->fresh()->balance,
                        'remark' => ucfirst($tx->type) . " Approved ({$tx->method}) UTR: {$tx->utr_number}",
                        'from_to' => "Admin Banking -> {$user->username}",
                        'ref_type' => $tx->type,
                        'ref_id' => $tx->tx_ref,
                    ]);
                }
            } else {
                $tx->update([
                    'status' => 'REJECTED',
                    'admin_note' => $request->input('admin_note') ?: 'Rejected by Admin',
                    'processed_at' => now(),
                ]);
                if ($tx->type === 'withdraw') {
                    $user->increment('balance', $tx->final_amount);
                    AccountStatement::create([
                        'user_id' => $user->id,
                        'credit' => $tx->final_amount,
                        'debit' => 0,
                        'balance' => $user->fresh()->balance,
                        'remark' => "Withdrawal Rejected & Refunded ({$tx->tx_ref})",
                        'from_to' => "Admin Banking -> {$user->username}",
                        'ref_type' => 'withdraw',
                        'ref_id' => $tx->tx_ref,
                    ]);
                }
            }
        });

        return back()->with('success', "Transaction {$tx->tx_ref} has been " . strtoupper($action) . "D.");
    }

    public function updateBankingTransaction(Request $request, int $id)
    {
        $tx = BankingTransaction::findOrFail($id);
        $request->validate([
            'type' => 'required|in:deposit,withdraw,reward',
            'method' => 'required|string|max:40',
            'amount' => 'required|numeric|min:1',
            'status' => 'required|in:PENDING,APPROVED,REJECTED',
        ]);

        $amount = round((float) $request->input('amount'), 2);
        $charges = round((float) $request->input('charges', $tx->charges), 2);

        $tx->update([
            'type' => $request->input('type'),
            'method' => trim($request->input('method')),
            'amount' => $amount,
            'charges' => $charges,
            'final_amount' => max(0, round($amount - $charges, 2)),
            'utr_number' => trim((string) $request->input('utr_number', $tx->utr_number)),
            'status' => $request->input('status'),
            'admin_note' => trim((string) $request->input('admin_note', $tx->admin_note)),
            'processed_at' => $request->input('status') !== 'PENDING' ? ($tx->processed_at ?? now()) : null,
        ]);

        return back()->with('success', "Updated transaction {$tx->tx_ref}!");
    }

    public function deleteBankingTransaction(int $id)
    {
        $tx = BankingTransaction::findOrFail($id);
        $ref = $tx->tx_ref;
        $tx->delete();

        return back()->with('success', "Banking transaction {$ref} deleted.");
    }

    // =========================================================================
    // 3. WITHDRAWAL DETAILS / USER SAVED BANK ACCOUNTS CRUD
    // =========================================================================

    public function bankAccounts(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $query = UserBankAccount::with('user');
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('holder_name', 'like', "%{$q}%")
                    ->orWhere('bank_name', 'like', "%{$q}%")
                    ->orWhere('account_number', 'like', "%{$q}%")
                    ->orWhere('ifsc_code', 'like', "%{$q}%")
                    ->orWhere('upi_id', 'like', "%{$q}%")
                    ->orWhereHas('user', fn ($u) => $u->where('username', 'like', "%{$q}%"));
            });
        }

        $accounts = $query->latest()->paginate(25)->withQueryString();
        $users = User::orderBy('username')->get(['id', 'username']);

        return view('admin.bank-accounts', compact('accounts', 'users', 'q'));
    }

    public function createBankAccount(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'holder_name' => 'required|string|max:120',
            'bank_name' => 'required|string|max:120',
            'account_number' => 'required|string|max:60',
            'ifsc_code' => 'required|string|max:30',
            'upi_id' => 'nullable|string|max:100',
        ]);

        UserBankAccount::create([
            'user_id' => $request->input('user_id'),
            'holder_name' => trim($request->input('holder_name')),
            'bank_name' => trim($request->input('bank_name')),
            'account_number' => trim($request->input('account_number')),
            'ifsc_code' => strtoupper(trim($request->input('ifsc_code'))),
            'upi_id' => trim((string) $request->input('upi_id')) ?: null,
            'is_default' => $request->boolean('is_default', true),
        ]);

        return back()->with('success', 'Withdrawal Bank/Wallet Account created successfully!');
    }

    public function updateBankAccount(Request $request, int $id)
    {
        $acc = UserBankAccount::findOrFail($id);
        $request->validate([
            'holder_name' => 'required|string|max:120',
            'bank_name' => 'required|string|max:120',
            'account_number' => 'required|string|max:60',
            'ifsc_code' => 'required|string|max:30',
            'upi_id' => 'nullable|string|max:100',
        ]);

        $acc->update([
            'holder_name' => trim($request->input('holder_name')),
            'bank_name' => trim($request->input('bank_name')),
            'account_number' => trim($request->input('account_number')),
            'ifsc_code' => strtoupper(trim($request->input('ifsc_code'))),
            'upi_id' => trim((string) $request->input('upi_id')) ?: null,
            'is_default' => $request->boolean('is_default'),
        ]);

        return back()->with('success', "Updated bank account for {$acc->holder_name}!");
    }

    public function deleteBankAccount(int $id)
    {
        $acc = UserBankAccount::findOrFail($id);
        $acc->delete();

        return back()->with('success', 'Withdrawal bank account deleted.');
    }

    // =========================================================================
    // 4. ACCOUNT STATEMENT LEDGER CRUD
    // =========================================================================

    public function accountStatements(Request $request)
    {
        $refType = $request->query('ref_type', 'all');
        $q = trim((string) $request->query('q', ''));

        $query = AccountStatement::with('user');
        if ($refType !== 'all') {
            $query->where('ref_type', $refType);
        }
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('remark', 'like', "%{$q}%")
                    ->orWhere('from_to', 'like', "%{$q}%")
                    ->orWhere('ref_id', 'like', "%{$q}%")
                    ->orWhereHas('user', fn ($u) => $u->where('username', 'like', "%{$q}%"));
            });
        }

        $statements = $query->latest()->paginate(30)->withQueryString();
        $users = User::orderBy('username')->get(['id', 'username', 'balance']);

        return view('admin.statements', compact('statements', 'users', 'refType', 'q'));
    }

    public function createStatement(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'credit' => 'nullable|numeric|min:0',
            'debit' => 'nullable|numeric|min:0',
            'remark' => 'required|string|max:255',
            'from_to' => 'nullable|string|max:180',
            'ref_type' => 'required|string|max:40',
            'sync_wallet' => 'nullable|boolean',
        ]);

        $user = User::findOrFail($request->input('user_id'));
        $credit = round((float) $request->input('credit', 0), 2);
        $debit = round((float) $request->input('debit', 0), 2);

        DB::transaction(function () use ($request, $user, $credit, $debit) {
            if ($request->boolean('sync_wallet', true)) {
                $newBal = max(0, round($user->balance + $credit - $debit, 2));
                $user->update(['balance' => $newBal]);
            }

            AccountStatement::create([
                'user_id' => $user->id,
                'credit' => $credit,
                'debit' => $debit,
                'balance' => $user->fresh()->balance,
                'remark' => trim($request->input('remark')),
                'from_to' => trim((string) $request->input('from_to')) ?: ("Admin -> {$user->username}"),
                'ref_type' => $request->input('ref_type'),
                'ref_id' => trim((string) $request->input('ref_id')) ?: ('STMT' . strtoupper(Str::random(6))),
            ]);
        });

        return back()->with('success', "Account Statement entry created for {$user->username}!");
    }

    public function updateStatement(Request $request, int $id)
    {
        $st = AccountStatement::findOrFail($id);
        $request->validate([
            'credit' => 'required|numeric|min:0',
            'debit' => 'required|numeric|min:0',
            'balance' => 'required|numeric',
            'remark' => 'required|string|max:255',
            'from_to' => 'required|string|max:180',
            'ref_type' => 'required|string|max:40',
        ]);

        $st->update([
            'credit' => round((float) $request->input('credit'), 2),
            'debit' => round((float) $request->input('debit'), 2),
            'balance' => round((float) $request->input('balance'), 2),
            'remark' => trim($request->input('remark')),
            'from_to' => trim($request->input('from_to')),
            'ref_type' => trim($request->input('ref_type')),
            'ref_id' => trim((string) $request->input('ref_id', $st->ref_id)),
        ]);

        return back()->with('success', "Statement entry #{$st->id} updated!");
    }

    public function deleteStatement(int $id)
    {
        $st = AccountStatement::findOrFail($id);
        $st->delete();

        return back()->with('success', 'Account Statement record deleted.');
    }

    // =========================================================================
    // 5. BETS HISTORY & PROFIT/LOSS CRUD
    // =========================================================================

    public function bets(Request $request)
    {
        $status = $request->query('status', 'all');
        $category = $request->query('category', 'all');
        $q = trim((string) $request->query('q', ''));

        $query = Bet::with('user');
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        if ($category !== 'all') {
            $query->where('category', $category);
        }
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('bet_ref', 'like', "%{$q}%")
                    ->orWhere('event_name', 'like', "%{$q}%")
                    ->orWhere('selection_name', 'like', "%{$q}%")
                    ->orWhereHas('user', fn ($u) => $u->where('username', 'like', "%{$q}%"));
            });
        }

        $bets = $query->latest()->paginate(30)->withQueryString();
        $users = User::orderBy('username')->get(['id', 'username']);

        $plStats = [
            'total_stake' => round((float) Bet::sum('stake'), 2),
            'exchange_pl' => round((float) Bet::where('category', 'exchange')->whereIn('status', ['WON', 'LOST', 'CASHOUT'])->sum('profit_loss'), 2),
            'casino_pl' => round((float) Bet::where('category', 'casino')->whereIn('status', ['WON', 'LOST', 'CASHOUT'])->sum('profit_loss'), 2),
            'open_exposure' => round((float) Bet::where('status', 'MATCHED')->sum('liability'), 2),
            'cashout_count' => Bet::where('status', 'CASHOUT')->count(),
            'cashout_pl' => round((float) Bet::where('status', 'CASHOUT')->sum('profit_loss'), 2),
        ];

        return view('admin.bets', compact('bets', 'users', 'status', 'category', 'q', 'plStats'));
    }

    /**
     * One-Click or Custom Cash Out & Instant Settlement (WON / LOST / VOID / CASHOUT) from Admin Panel
     */
    public function adminCashoutBet(Request $request, int $id)
    {
        $bet = Bet::findOrFail($id);
        $action = strtolower((string) $request->input('action', 'cashout'));
        $user = User::find($bet->user_id);

        $newStatus = 'CASHOUT';
        $cashoutPl = 0.0;

        if ($action === 'won') {
            $newStatus = 'WON';
            $cashoutPl = round((float) $bet->potential_profit, 2);
        } elseif ($action === 'lost') {
            $newStatus = 'LOST';
            $cashoutPl = -round((float) $bet->liability, 2);
        } elseif ($action === 'void') {
            $newStatus = 'VOID';
            $cashoutPl = 0.0;
        } else {
            $newStatus = 'CASHOUT';
            if ($request->filled('cashout_pl')) {
                $cashoutPl = round((float) $request->input('cashout_pl'), 2);
            } else {
                // Default realistic Cash Out profit (e.g. +18% of stake)
                $cashoutPl = round((float) $bet->stake * 0.18, 2);
            }
        }

        $remarkNote = trim((string) $request->input('remark')) ?: ("Admin {$newStatus} [{$bet->event_name} - {$bet->selection_name}]");

        DB::transaction(function () use ($bet, $user, $newStatus, $cashoutPl, $remarkNote) {
            if ($user && $bet->status === 'MATCHED') {
                $newBal = max(0, round((float) $user->balance + $cashoutPl, 2));
                $user->update(['balance' => $newBal]);

                AccountStatement::create([
                    'user_id' => $user->id,
                    'credit' => $cashoutPl > 0 ? $cashoutPl : 0,
                    'debit' => $cashoutPl < 0 ? abs($cashoutPl) : 0,
                    'balance' => $user->fresh()->balance,
                    'remark' => "{$remarkNote} (Ref: {$bet->bet_ref})",
                    'from_to' => $cashoutPl >= 0 ? "CashOut/Exchange -> {$user->username}" : "{$user->username} -> CashOut/Exchange",
                    'ref_type' => $newStatus === 'CASHOUT' ? 'cashout' : 'bet_settle',
                    'ref_id' => $bet->bet_ref,
                ]);
            }

            $bet->update([
                'status' => $newStatus,
                'profit_loss' => $cashoutPl,
                'result_summary' => $remarkNote,
                'settled_at' => now(),
            ]);
        });

        if ($user) {
            app(\App\Services\ExchangeBookService::class)->syncUserWallet($user->fresh());
        }

        return back()->with('success', "Bet {$bet->bet_ref} settled as {$newStatus} (P/L: " . ($cashoutPl >= 0 ? '+' : '') . number_format($cashoutPl, 2) . " PTI)!");
    }

    public function createBet(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'category' => 'required|in:exchange,bookmaker,fancy,sportsbook,casino',
            'sport_name' => 'required|string|max:80',
            'event_name' => 'required|string|max:200',
            'market_name' => 'required|string|max:120',
            'selection_name' => 'required|string|max:150',
            'bet_type' => 'required|in:BACK,LAY,YES,NO',
            'odds' => 'required|numeric|min:1.01',
            'stake' => 'required|numeric|min:1',
            'status' => 'required|in:MATCHED,WON,LOST,CASHOUT,VOID',
        ]);

        $user = User::findOrFail($request->input('user_id'));
        $odds = round((float) $request->input('odds'), 2);
        $stake = round((float) $request->input('stake'), 2);
        $betType = $request->input('bet_type');
        $status = $request->input('status');
        $calc = app(\App\Services\ExchangeBookService::class)->calculateProfitAndLiability(
            (string) $request->input('category', 'exchange'),
            $betType,
            $odds,
            $stake
        );
        $potentialProfit = $calc['profit'];
        $liability = $calc['liability'];
        $pl = $status === 'WON' ? $potentialProfit : ($status === 'LOST' ? -$liability : 0);

        Bet::create([
            'bet_ref' => 'BET' . strtoupper(Str::random(8)),
            'user_id' => $user->id,
            'category' => $request->input('category'),
            'sport_id' => $request->input('category') === 'casino' ? '99' : '4',
            'sport_name' => trim($request->input('sport_name')),
            'event_id' => 'EV' . random_int(10000, 99999),
            'event_name' => trim($request->input('event_name')),
            'market_id' => '1.' . random_int(100000, 999999),
            'market_name' => trim($request->input('market_name')),
            'selection_id' => '101',
            'selection_name' => trim($request->input('selection_name')),
            'bet_type' => $betType,
            'odds' => $odds,
            'stake' => $stake,
            'potential_profit' => $potentialProfit,
            'liability' => $liability,
            'profit_loss' => $pl,
            'status' => $status,
            'result_summary' => $status !== 'MATCHED' ? "Admin Settled ({$status})" : null,
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'settled_at' => $status !== 'MATCHED' ? now() : null,
        ]);

        app(\App\Services\ExchangeBookService::class)->syncUserWallet($user->fresh());

        return back()->with('success', "Bet record created for {$user->username}!");
    }

    public function updateBet(Request $request, int $id)
    {
        $bet = Bet::findOrFail($id);
        $request->validate([
            'event_name' => 'required|string|max:200',
            'market_name' => 'required|string|max:120',
            'selection_name' => 'required|string|max:150',
            'bet_type' => 'required|in:BACK,LAY,YES,NO',
            'odds' => 'required|numeric|min:1.01',
            'stake' => 'required|numeric|min:1',
            'profit_loss' => 'required|numeric',
            'status' => 'required|in:MATCHED,WON,LOST,CASHOUT,VOID',
        ]);

        $prevStatus = $bet->status;
        $newStatus = $request->input('status');
        $newPl = round((float) $request->input('profit_loss'), 2);

        DB::transaction(function () use ($bet, $request, $prevStatus, $newStatus, $newPl) {
            if ($prevStatus === 'MATCHED' && $newStatus !== 'MATCHED' && $request->boolean('settle_wallet', true)) {
                $user = User::find($bet->user_id);
                if ($user) {
                    $user->increment('balance', $newPl);

                    AccountStatement::create([
                        'user_id' => $user->id,
                        'credit' => $newPl > 0 ? $newPl : 0,
                        'debit' => $newPl < 0 ? abs($newPl) : 0,
                        'balance' => $user->fresh()->balance,
                        'remark' => "Admin Settled Bet {$bet->bet_ref} [{$bet->event_name}] ({$newStatus})",
                        'from_to' => $newPl >= 0 ? "Exchange -> {$user->username}" : "{$user->username} -> Exchange",
                        'ref_type' => 'bet_settle',
                        'ref_id' => $bet->bet_ref,
                    ]);
                }
            }

            $bet->update([
                'event_name' => trim($request->input('event_name')),
                'market_name' => trim($request->input('market_name')),
                'selection_name' => trim($request->input('selection_name')),
                'bet_type' => $request->input('bet_type'),
                'odds' => round((float) $request->input('odds'), 2),
                'stake' => round((float) $request->input('stake'), 2),
                'profit_loss' => $newPl,
                'status' => $newStatus,
                'result_summary' => trim((string) $request->input('result_summary', $bet->result_summary)),
                'settled_at' => $newStatus !== 'MATCHED' ? ($bet->settled_at ?? now()) : null,
            ]);
        });

        if ($u = User::find($bet->user_id)) {
            app(\App\Services\ExchangeBookService::class)->syncUserWallet($u);
        }

        return back()->with('success', "Updated Bet {$bet->bet_ref}!");
    }

    public function deleteBet(int $id)
    {
        $bet = Bet::findOrFail($id);
        $userId = $bet->user_id;
        $ref = $bet->bet_ref;
        $bet->delete();

        if ($u = User::find($userId)) {
            app(\App\Services\ExchangeBookService::class)->syncUserWallet($u);
        }

        return back()->with('success', "Bet {$ref} deleted.");
    }

    // =========================================================================
    // 6. ROLLING COMMISSION, REFER & EARN & REWARDS CRUD
    // =========================================================================

    public function commissions(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $query = User::query();
        if ($q !== '') {
            $query->where('username', 'like', "%{$q}%")
                ->orWhere('referral_code', 'like', "%{$q}%")
                ->orWhere('referred_by', 'like', "%{$q}%");
        }
        $users = $query->latest()->paginate(25)->withQueryString();
        $rewardStatements = AccountStatement::with('user')
            ->whereIn('ref_type', ['commission', 'referral', 'reward'])
            ->latest()
            ->take(25)
            ->get();

        return view('admin.commissions', compact('users', 'rewardStatements', 'q'));
    }

    public function updateUserCommission(Request $request, int $userId)
    {
        $user = User::findOrFail($userId);
        $request->validate([
            'referral_code' => 'nullable|string|max:32',
            'referred_by' => 'nullable|string|max:32',
            'bonus_balance' => 'nullable|numeric|min:0',
        ]);

        $user->update([
            'referral_code' => trim((string) $request->input('referral_code', $user->referral_code)),
            'referred_by' => trim((string) $request->input('referred_by')) ?: null,
            'bonus_balance' => round((float) $request->input('bonus_balance', $user->bonus_balance), 2),
            'rolling_commission' => [
                'fancy' => round((float) $request->input('fancy', 0), 2),
                'matka' => round((float) $request->input('matka', 0), 2),
                'casino' => round((float) $request->input('casino', 0), 2),
                'binary' => round((float) $request->input('binary', 0), 2),
                'sportbook' => round((float) $request->input('sportbook', 0), 2),
                'bookmaker' => round((float) $request->input('bookmaker', 0), 2),
            ],
        ]);

        return back()->with('success', "Updated Rolling Commission & Referral settings for {$user->username}!");
    }

    public function payoutCommissionOrReward(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'payout_type' => 'required|in:commission,referral,reward',
            'amount' => 'required|numeric|min:1',
            'remark' => 'nullable|string|max:200',
        ]);

        $user = User::findOrFail($request->input('user_id'));
        $type = $request->input('payout_type');
        $amount = round((float) $request->input('amount'), 2);
        $remark = trim((string) $request->input('remark')) ?: ('Admin ' . ucfirst($type) . ' Payout');

        DB::transaction(function () use ($user, $type, $amount, $remark) {
            $user->increment('balance', $amount);
            $user->increment('bonus_balance', $amount);

            AccountStatement::create([
                'user_id' => $user->id,
                'credit' => $amount,
                'debit' => 0,
                'balance' => $user->fresh()->balance,
                'remark' => $remark,
                'from_to' => strtoupper($type) . " -> {$user->username}",
                'ref_type' => $type,
                'ref_id' => strtoupper(substr($type, 0, 3)) . strtoupper(Str::random(6)),
            ]);
        });

        return back()->with('success', "Credited {$amount} PTI ({$type}) to {$user->username}!");
    }

    // =========================================================================
    // 7. TWO-FACTOR AUTHENTICATION (2FA) & PASSWORD HISTORY CRUD
    // =========================================================================

    public function securityAndPasswords(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $users = User::orderBy('username')->get();

        $pwQuery = PasswordHistory::with('user');
        if ($q !== '') {
            $pwQuery->where(function ($sub) use ($q) {
                $sub->where('remark', 'like', "%{$q}%")
                    ->orWhere('changed_by', 'like', "%{$q}%")
                    ->orWhere('ip_address', 'like', "%{$q}%")
                    ->orWhereHas('user', fn ($u) => $u->where('username', 'like', "%{$q}%"));
            });
        }
        $passwordHistories = $pwQuery->latest()->paginate(25)->withQueryString();

        return view('admin.security', compact('users', 'passwordHistories', 'q'));
    }

    public function updateUserTwoFactor(Request $request, int $userId)
    {
        $user = User::findOrFail($userId);
        $action = $request->input('action', 'toggle');

        if ($action === 'regenerate') {
            $user->update([
                'two_factor_code' => strtoupper(Str::random(10)),
                'two_factor_enabled' => true,
            ]);
            return back()->with('success', "Regenerated 2FA Secret Key & Enabled 2FA for {$user->username}!");
        }

        $user->update([
            'two_factor_enabled' => !$user->two_factor_enabled,
            'two_factor_code' => $user->two_factor_code ?: strtoupper(Str::random(10)),
        ]);

        return back()->with('success', "Updated 2FA status for {$user->username}!");
    }

    public function createPasswordHistory(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'changed_by' => 'required|string|max:60',
            'remark' => 'required|string|max:255',
            'ip_address' => 'nullable|string|max:45',
            'new_password' => 'nullable|string|min:6',
        ]);

        $user = User::findOrFail($request->input('user_id'));
        if ($request->filled('new_password')) {
            $user->update(['password' => Hash::make($request->input('new_password'))]);
        }

        PasswordHistory::create([
            'user_id' => $user->id,
            'changed_by' => trim($request->input('changed_by')),
            'remark' => trim($request->input('remark')),
            'ip_address' => trim((string) $request->input('ip_address')) ?: ($request->ip() ?? '127.0.0.1'),
        ]);

        return back()->with('success', "Password History log created for {$user->username}!");
    }

    public function updatePasswordHistory(Request $request, int $id)
    {
        $ph = PasswordHistory::findOrFail($id);
        $request->validate([
            'changed_by' => 'required|string|max:60',
            'remark' => 'required|string|max:255',
            'ip_address' => 'nullable|string|max:45',
        ]);

        $ph->update([
            'changed_by' => trim($request->input('changed_by')),
            'remark' => trim($request->input('remark')),
            'ip_address' => trim((string) $request->input('ip_address', $ph->ip_address)),
        ]);

        return back()->with('success', "Updated Password History record #{$ph->id}!");
    }

    public function deletePasswordHistory(int $id)
    {
        $ph = PasswordHistory::findOrFail($id);
        $ph->delete();

        return back()->with('success', 'Password History record deleted.');
    }

    // =========================================================================
    // 8. ACTIVITY LOGS & OTP VERIFICATION LOGS CRUD
    // =========================================================================

    public function activityLogs(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $query = ActivityLog::with('user');
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('login_status', 'like', "%{$q}%")
                    ->orWhere('ip_address', 'like', "%{$q}%")
                    ->orWhere('isp', 'like', "%{$q}%")
                    ->orWhere('city_state_country', 'like', "%{$q}%")
                    ->orWhereHas('user', fn ($u) => $u->where('username', 'like', "%{$q}%"));
            });
        }

        $logs = $query->latest()->paginate(25)->withQueryString();
        $otps = OtpVerification::latest()->take(25)->get();
        $users = User::orderBy('username')->get(['id', 'username']);

        return view('admin.activity-logs', compact('logs', 'otps', 'users', 'q'));
    }

    public function createActivityLog(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'login_status' => 'required|string|max:60',
            'ip_address' => 'required|string|max:45',
            'isp' => 'required|string|max:120',
            'city_state_country' => 'required|string|max:150',
        ]);

        ActivityLog::create([
            'user_id' => $request->input('user_id'),
            'login_status' => trim($request->input('login_status')),
            'ip_address' => trim($request->input('ip_address')),
            'isp' => trim($request->input('isp')),
            'city_state_country' => trim($request->input('city_state_country')),
            'user_agent' => substr((string) $request->userAgent(), 0, 250),
        ]);

        return back()->with('success', 'Activity Log entry created!');
    }

    public function updateActivityLog(Request $request, int $id)
    {
        $log = ActivityLog::findOrFail($id);
        $request->validate([
            'login_status' => 'required|string|max:60',
            'ip_address' => 'required|string|max:45',
            'isp' => 'required|string|max:120',
            'city_state_country' => 'required|string|max:150',
        ]);

        $log->update([
            'login_status' => trim($request->input('login_status')),
            'ip_address' => trim($request->input('ip_address')),
            'isp' => trim($request->input('isp')),
            'city_state_country' => trim($request->input('city_state_country')),
        ]);

        return back()->with('success', "Updated Activity Log #{$log->id}!");
    }

    public function deleteActivityLog(int $id)
    {
        $log = ActivityLog::findOrFail($id);
        $log->delete();

        return back()->with('success', 'Activity Log entry deleted.');
    }

    public function deleteOtpLog(int $id)
    {
        $otp = OtpVerification::findOrFail($id);
        $otp->delete();

        return back()->with('success', 'OTP Log deleted.');
    }

    // =========================================================================
    // 9. SPORTS CATEGORIES & MATCH EVENTS CRUD
    // =========================================================================

    public function events(Request $request)
    {
        $sportId = $request->query('sport_id', 'all');
        $query = ExchangeEvent::query();
        if ($sportId !== 'all') {
            $query->where('sport_id', $sportId);
        }
        $events = $query->orderByDesc('in_play')->orderBy('id')->paginate(25)->withQueryString();
        $sports = Sport::orderBy('sequence')->get();

        return view('admin.events', compact('events', 'sports', 'sportId'));
    }

    public function createEvent(Request $request)
    {
        $request->validate([
            'sport_id' => 'required|string',
            'tournament_name' => 'required|string|max:120',
            'event_name' => 'required|string|max:180',
            'runner1' => 'required|string|max:80',
            'runner2' => 'required|string|max:80',
            'back1' => 'required|numeric|min:1.01',
            'back2' => 'required|numeric|min:1.01',
            'in_play' => 'nullable|boolean',
        ]);

        $sportIdStr = (string) $request->input('sport_id');
        $sport = Sport::where('sport_id', $sportIdStr)->first();
        $b1 = round((float) $request->input('back1'), 2);
        $b2 = round((float) $request->input('back2'), 2);
        $bx = round((float) $request->input('backX', 0), 2);
        $runners = [
            [
                'selectionId' => '101',
                'runnerName' => trim($request->input('runner1')),
                'status' => 'ACTIVE',
                'back' => [['price' => $b1, 'size' => 5000], ['price' => max(1.01, $b1 - 0.02), 'size' => 4000], ['price' => max(1.01, $b1 - 0.04), 'size' => 8000]],
                'lay' => [['price' => round($b1 + 0.02, 2), 'size' => 5000], ['price' => round($b1 + 0.04, 2), 'size' => 4000], ['price' => round($b1 + 0.06, 2), 'size' => 8000]],
            ],
            [
                'selectionId' => '102',
                'runnerName' => trim($request->input('runner2')),
                'status' => 'ACTIVE',
                'back' => [['price' => $b2, 'size' => 5000], ['price' => max(1.01, $b2 - 0.02), 'size' => 4000], ['price' => max(1.01, $b2 - 0.04), 'size' => 8000]],
                'lay' => [['price' => round($b2 + 0.02, 2), 'size' => 5000], ['price' => round($b2 + 0.04, 2), 'size' => 4000], ['price' => round($b2 + 0.06, 2), 'size' => 8000]],
            ],
        ];
        if ($sportIdStr === '1' || $bx > 1.0) {
            $drawPrice = $bx > 1.0 ? $bx : 3.45;
            $runners[] = [
                'selectionId' => '103',
                'runnerName' => 'The Draw',
                'status' => 'ACTIVE',
                'back' => [['price' => $drawPrice, 'size' => 5000], ['price' => max(1.01, $drawPrice - 0.05), 'size' => 4000], ['price' => max(1.01, $drawPrice - 0.10), 'size' => 8000]],
                'lay' => [['price' => round($drawPrice + 0.05, 2), 'size' => 5000], ['price' => round($drawPrice + 0.10, 2), 'size' => 4000], ['price' => round($drawPrice + 0.15, 2), 'size' => 8000]],
            ];
        }

        $shortScore = trim((string) $request->input('short_score', ''));
        $scoreJson = [];
        if ($shortScore !== '') {
            $scoreKey = $sportIdStr === '1' ? 'soccer' : ($sportIdStr === '2' ? 'tennis' : 'cricbuzz');
            $scoreJson[$scoreKey] = [
                'isLive' => true,
                'shortScoreSummary' => $shortScore,
                'status' => $shortScore,
            ];
        }

        ExchangeEvent::create([
            'event_id' => trim((string) $request->input('custom_event_id')) ?: ('EV' . now()->format('ymdHis') . rand(10, 99)),
            'market_id' => '1.' . rand(10000000, 99999999),
            'market_name' => 'Match Odds',
            'sport_id' => $sportIdStr,
            'sport_name' => $sport?->sport_name ?? 'Cricket',
            'tournament_id' => '999',
            'tournament_name' => trim($request->input('tournament_name')),
            'event_name' => trim($request->input('event_name')),
            'event_time' => now()->format('Y-m-d H:i:s'),
            'in_play' => $request->boolean('in_play', true),
            'status' => 'OPEN',
            'is_fancy' => $request->boolean('is_fancy', true),
            'is_bookmakers' => $request->boolean('is_bookmakers', true),
            'is_sportsbook' => $request->boolean('is_sportsbook', true),
            'is_streaming' => $request->boolean('is_streaming', true),
            'total_matched' => 250000,
            'runners_json' => $runners,
            'score_json' => $scoreJson ?: null,
        ]);

        return back()->with('success', 'New Exchange Event added successfully!');
    }

    public function updateEvent(Request $request, int $id)
    {
        $ev = ExchangeEvent::findOrFail($id);
        $request->validate([
            'event_name' => 'required|string|max:180',
            'tournament_name' => 'required|string|max:120',
            'status' => 'required|in:OPEN,SUSPENDED,CLOSED',
        ]);

        $runners = $ev->runners_json ?? [];
        if ($request->filled('back1') && isset($runners[0])) {
            $b1 = round((float) $request->input('back1'), 2);
            $runners[0]['back'][0]['price'] = $b1;
            $runners[0]['lay'][0]['price'] = round($b1 + 0.02, 2);
        }
        if ($request->filled('back2') && isset($runners[1])) {
            $b2 = round((float) $request->input('back2'), 2);
            $runners[1]['back'][0]['price'] = $b2;
            $runners[1]['lay'][0]['price'] = round($b2 + 0.02, 2);
        }
        if ($request->filled('backX') && (float) $request->input('backX') > 1.0) {
            $bx = round((float) $request->input('backX'), 2);
            if (isset($runners[2])) {
                $runners[2]['back'][0]['price'] = $bx;
                $runners[2]['lay'][0]['price'] = round($bx + 0.05, 2);
            } else {
                $runners[2] = [
                    'selectionId' => '103',
                    'runnerName' => 'The Draw',
                    'status' => 'ACTIVE',
                    'back' => [['price' => $bx, 'size' => 5000]],
                    'lay' => [['price' => round($bx + 0.05, 2), 'size' => 5000]],
                ];
            }
        }

        $scoreJson = is_array($ev->score_json) ? $ev->score_json : [];
        if ($request->filled('short_score')) {
            $shortScore = trim((string) $request->input('short_score'));
            $scoreKey = (string) $ev->sport_id === '1' ? 'soccer' : ((string) $ev->sport_id === '2' ? 'tennis' : 'cricbuzz');
            $scoreJson[$scoreKey] = array_merge($scoreJson[$scoreKey] ?? [], [
                'isLive' => true,
                'shortScoreSummary' => $shortScore,
                'status' => $shortScore,
            ]);
        }

        $ev->update([
            'event_name' => trim($request->input('event_name')),
            'tournament_name' => trim($request->input('tournament_name')),
            'status' => $request->input('status'),
            'in_play' => $request->boolean('in_play'),
            'is_bookmakers' => $request->has('is_bookmakers') ? $request->boolean('is_bookmakers') : $ev->is_bookmakers,
            'is_fancy' => $request->has('is_fancy') ? $request->boolean('is_fancy') : $ev->is_fancy,
            'is_sportsbook' => $request->has('is_sportsbook') ? $request->boolean('is_sportsbook') : $ev->is_sportsbook,
            'is_streaming' => $request->has('is_streaming') ? $request->boolean('is_streaming') : $ev->is_streaming,
            'runners_json' => $runners,
            'score_json' => $scoreJson,
        ]);

        return back()->with('success', "Updated match {$ev->event_name}!");
    }

    public function deleteEvent(int $id)
    {
        $ev = ExchangeEvent::findOrFail($id);
        $name = $ev->event_name;
        $ev->delete();

        return back()->with('success', "Match event {$name} deleted.");
    }

    public function toggleEventState(Request $request, int $id)
    {
        $ev = ExchangeEvent::findOrFail($id);
        $field = $request->input('field', 'in_play');
        if ($field === 'status') {
            $ev->update(['status' => $ev->status === 'OPEN' ? 'SUSPENDED' : 'OPEN']);
        } else {
            $ev->update(['in_play' => !$ev->in_play]);
        }
        return back()->with('success', "Updated {$ev->event_name}!");
    }

    public function settleEventMarket(Request $request, int $id)
    {
        $request->validate([
            'winner_selection' => 'required|string',
        ]);

        $ev = ExchangeEvent::findOrFail($id);
        $winner = trim($request->input('winner_selection'));
        $openBets = Bet::where('event_id', $ev->event_id)->where('status', 'MATCHED')->get();

        $affectedUserIds = [];
        DB::transaction(function () use ($ev, $winner, $openBets, &$affectedUserIds) {
            $ev->update(['winner_selection' => $winner]);

            foreach ($openBets as $bet) {
                $user = User::find($bet->user_id);
                if (!$user) continue;

                $bt = strtoupper(trim((string) $bet->bet_type));
                if ($bet->category === 'fancy' || in_array($bt, ['YES', 'NO'], true)) {
                    // Only settle Fancy bets if winner_selection is a numeric run score; otherwise leave Fancy bets for Fancy settlement
                    if (!is_numeric($winner)) {
                        continue;
                    }
                    $actualRuns = (float) $winner;
                    $runLine = (float) ($bet->run_line ?? 0);
                    $won = ($bt === 'YES') ? ($actualRuns >= $runLine) : ($actualRuns < $runLine);
                } else {
                    $matchedRunner = strcasecmp(trim($bet->selection_name), $winner) === 0;
                    $won = ($bt === 'BACK' && $matchedRunner) || ($bt === 'LAY' && !$matchedRunner);
                }

                $affectedUserIds[$user->id] = true;
                $pl = $won ? round((float) $bet->potential_profit, 2) : -round((float) $bet->liability, 2);

                $user->increment('balance', $pl);

                $bet->update([
                    'profit_loss' => $pl,
                    'status' => $won ? 'WON' : 'LOST',
                    'result_summary' => "Market Settled: Winner {$winner}",
                    'settled_at' => now(),
                ]);

                AccountStatement::create([
                    'user_id' => $user->id,
                    'credit' => $pl > 0 ? $pl : 0,
                    'debit' => $pl < 0 ? abs($pl) : 0,
                    'balance' => $user->fresh()->balance,
                    'remark' => "Settled [{$ev->event_name}] Winner: {$winner}",
                    'from_to' => $pl >= 0 ? "Exchange -> {$user->username}" : "{$user->username} -> Exchange",
                    'ref_type' => 'bet_settle',
                    'ref_id' => $bet->bet_ref,
                ]);
            }
        });

        $bookSvc = app(\App\Services\ExchangeBookService::class);
        foreach (array_keys($affectedUserIds) as $uid) {
            if ($u = User::find($uid)) {
                $bookSvc->syncUserWallet($u);
            }
        }

        return back()->with('success', "Settled {$openBets->count()} open bets for {$ev->event_name} (Winner: {$winner})!");
    }

    public function createSport(Request $request)
    {
        $request->validate([
            'sport_id' => 'required|string|max:30|unique:sports,sport_id',
            'sport_name' => 'required|string|max:80',
            'sequence' => 'nullable|integer',
        ]);

        Sport::create([
            'sport_id' => trim($request->input('sport_id')),
            'sport_name' => trim($request->input('sport_name')),
            'sequence' => (int) $request->input('sequence', 10),
            'popular' => true,
            'status' => true,
        ]);

        return back()->with('success', 'New Sport category created!');
    }

    public function updateSport(Request $request, int $id)
    {
        $sp = Sport::findOrFail($id);
        $sp->update([
            'sport_name' => trim($request->input('sport_name', $sp->sport_name)),
            'sequence' => (int) $request->input('sequence', $sp->sequence),
            'status' => $request->boolean('status'),
        ]);

        return back()->with('success', "Updated sport {$sp->sport_name}!");
    }

    public function deleteSport(int $id)
    {
        $sp = Sport::findOrFail($id);
        $sp->delete();

        return back()->with('success', 'Sport category deleted.');
    }

    // =========================================================================
    // 10. CASINO GAMES CRUD
    // =========================================================================

    public function casinoGames(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $query = CasinoGame::query();
        if ($q !== '') {
            $query->where('event_name', 'like', "%{$q}%")
                ->orWhere('game_type', 'like', "%{$q}%")
                ->orWhere('event_id', 'like', "%{$q}%");
        }
        $games = $query->orderBy('sequence')->paginate(30)->withQueryString();

        return view('admin.casino', compact('games', 'q'));
    }

    public function createCasinoGame(Request $request)
    {
        $request->validate([
            'event_id' => 'required|string|max:80|unique:casino_games,event_id',
            'event_name' => 'required|string|max:150',
            'game_type' => 'required|string|max:40',
            'menu_name' => 'nullable|string|max:80',
            'min_bet' => 'required|numeric|min:1',
            'max_bet' => 'required|numeric|min:10',
            'rtp' => 'required|numeric|min:50|max:99.9',
            'thumbnail' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $localImage = '/assets/casino/99_0010.webp';
        if ($request->hasFile('thumbnail')) {
            $path = Storage::disk('public')->putFile('casino_thumbs', $request->file('thumbnail'));
            $localImage = '/storage/' . $path;
        }

        $name = trim($request->input('event_name'));
        CasinoGame::create([
            'event_id' => trim($request->input('event_id')),
            'event_name' => strtoupper($name),
            'slug' => Str::slug($name . '-' . $request->input('event_id')),
            'menu_id' => '1',
            'menu_name' => trim((string) $request->input('menu_name')) ?: 'TEENPATTI',
            'category_tab' => $request->input('category_tab', 'OUR CASINO'),
            'company_name' => 'UNIVERSE',
            'game_type' => trim($request->input('game_type')),
            'local_image' => $localImage,
            'popular' => $request->boolean('popular', true),
            'sequence' => (int) $request->input('sequence', 1),
            'min_bet' => (float) $request->input('min_bet', 100),
            'max_bet' => (float) $request->input('max_bet', 100000),
            'rtp' => (float) $request->input('rtp', 98.0),
            'status' => $request->boolean('status', true),
        ]);

        return back()->with('success', "Created new Casino Game: {$name}!");
    }

    public function updateCasinoGame(Request $request, int $id)
    {
        $game = CasinoGame::findOrFail($id);

        $data = [
            'event_name' => $request->input('event_name', $game->event_name),
            'game_type' => $request->input('game_type', $game->game_type),
            'menu_name' => $request->input('menu_name', $game->menu_name),
            'min_bet' => (float) $request->input('min_bet', $game->min_bet),
            'max_bet' => (float) $request->input('max_bet', $game->max_bet),
            'rtp' => (float) $request->input('rtp', $game->rtp),
            'popular' => $request->boolean('popular'),
            'status' => $request->boolean('status'),
        ];

        if ($request->hasFile('thumbnail')) {
            $path = Storage::disk('public')->putFile('casino_thumbs', $request->file('thumbnail'));
            $data['local_image'] = '/storage/' . $path;
        }

        $game->update($data);

        return back()->with('success', "Updated casino game {$game->event_name}!");
    }

    public function deleteCasinoGame(int $id)
    {
        $game = CasinoGame::findOrFail($id);
        $name = $game->event_name;
        $game->delete();

        return back()->with('success', "Casino game {$name} deleted.");
    }

    // =========================================================================
    // 11. WEBSITE, PAYMENT, THEME & BANNER SETTINGS CRUD
    // =========================================================================

    public function settings()
    {
        $settings = [
            'exchange_name' => SiteSetting::getValue('exchange_name', 'cricexperts.com'),
            'exchange_title' => SiteSetting::getValue('exchange_title', 'CRICEXPERTS.COM'),
            'news_marquee' => SiteSetting::getValue('news_marquee', 'Minimum deposit 100 and minimum withdrawal 100. 24x7 Instant Deposit & Withdrawal Available!'),
            'support_whatsapp' => SiteSetting::getValue('support_whatsapp', '+919831391470'),
            'welcome_bonus_pti' => SiteSetting::getValue('welcome_bonus_pti', '1000.00'),
            'min_deposit' => SiteSetting::getValue('min_deposit', '100'),
            'min_withdraw' => SiteSetting::getValue('min_withdraw', '100'),
            'deposit_upi_id' => SiteSetting::getValue('deposit_upi_id', 'cricexperts.pay@axis'),
            'deposit_account_name' => SiteSetting::getValue('deposit_account_name', 'CRICEXPERTS ENTERPRISES'),
            'deposit_bank_name' => SiteSetting::getValue('deposit_bank_name', 'HDFC Bank'),
            'deposit_account_number' => SiteSetting::getValue('deposit_account_number', '50200089412580'),
            'deposit_ifsc' => SiteSetting::getValue('deposit_ifsc', 'HDFC0001245'),
            'deposit_qr_image' => SiteSetting::getValue('deposit_qr_image', ''),
            'msg91_auth_key' => SiteSetting::getValue('msg91_auth_key', ''),
            'msg91_template_id' => SiteSetting::getValue('msg91_template_id', ''),
            'msg91_sender_id' => SiteSetting::getValue('msg91_sender_id', 'CRICEX'),
            'msg91_sandbox_show_otp' => SiteSetting::getValue('msg91_sandbox_show_otp', '1'),
            'sportmonks_api_token' => SiteSetting::getValue('sportmonks_api_token', (string) config('services.sportmonks.token', '')),
            'sportmonks_football_url' => SiteSetting::getValue('sportmonks_football_url', (string) config('services.sportmonks.football_url', 'https://api.sportmonks.com/v3/football')),
            'sportmonks_cricket_url' => SiteSetting::getValue('sportmonks_cricket_url', (string) config('services.sportmonks.cricket_url', 'https://cricket.sportmonks.com/api/v2.0')),
            'sportmonks_f1_url' => SiteSetting::getValue('sportmonks_f1_url', (string) config('services.sportmonks.f1_url', 'https://f1.sportmonks.com/api/v1.0')),
            'banners' => json_decode((string) SiteSetting::getValue('banners', '["/assets/brand/banner-1.jpg"]'), true) ?: ['/assets/brand/banner-1.jpg'],
        ];

        $recentOtps = OtpVerification::latest()->take(15)->get();

        return view('admin.settings', compact('settings', 'recentOtps'));
    }

    public function saveSettings(Request $request)
    {
        $keys = [
            'exchange_name' => 'general',
            'exchange_title' => 'general',
            'news_marquee' => 'general',
            'support_whatsapp' => 'general',
            'welcome_bonus_pti' => 'limits',
            'min_deposit' => 'banking',
            'min_withdraw' => 'banking',
            'deposit_upi_id' => 'banking',
            'deposit_account_name' => 'banking',
            'deposit_bank_name' => 'banking',
            'deposit_account_number' => 'banking',
            'deposit_ifsc' => 'banking',
            'msg91_auth_key' => 'msg91',
            'msg91_template_id' => 'msg91',
            'msg91_sender_id' => 'msg91',
            'msg91_sandbox_show_otp' => 'msg91',
            'sportmonks_api_token' => 'sportmonks',
            'sportmonks_football_url' => 'sportmonks',
            'sportmonks_cricket_url' => 'sportmonks',
            'sportmonks_f1_url' => 'sportmonks',
        ];

        foreach ($keys as $k => $grp) {
            if ($request->has($k)) {
                SiteSetting::setValue($k, (string) $request->input($k), $grp);
            }
        }

        if ($request->hasFile('banner_image')) {
            $path = Storage::disk('public')->putFile('banners', $request->file('banner_image'));
            $banners = json_decode((string) SiteSetting::getValue('banners', '["/assets/brand/banner-1.jpg"]'), true) ?: [];
            array_unshift($banners, '/storage/' . $path);
            SiteSetting::setValue('banners', array_values(array_unique($banners)), 'general');
        }

        if ($request->hasFile('brand_logo')) {
            $request->file('brand_logo')->move(public_path('assets/brand'), 'logo.png');
        }

        if ($request->hasFile('deposit_qr_image')) {
            $request->validate([
                'deposit_qr_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            ]);
            $file = $request->file('deposit_qr_image');
            $ext = $file->getClientOriginalExtension() ?: 'jpg';
            $filename = 'deposit_qr_' . time() . '.' . $ext;
            
            // 1. Store in Laravel public storage
            $file->storeAs('qr', $filename, 'public');

            // 2. Also write directly to public/storage/qr and public/assets/brand/deposit-qr.jpg for 100% fail-safe display
            @mkdir(public_path('storage/qr'), 0777, true);
            @copy($file->getRealPath(), public_path('storage/qr/' . $filename));
            @copy($file->getRealPath(), public_path('assets/brand/deposit-qr.jpg'));

            SiteSetting::setValue('deposit_qr_image', '/storage/qr/' . $filename, 'banking');
        }

        return back()->with('success', 'Website, Payment, Banner & MSG91 Settings saved successfully!');
    }

    public function deleteBanner(Request $request)
    {
        $bannerUrl = $request->input('banner_url');
        $banners = json_decode((string) SiteSetting::getValue('banners', '["/assets/brand/banner-1.jpg"]'), true) ?: [];
        $banners = array_values(array_filter($banners, fn ($b) => $b !== $bannerUrl));
        if (empty($banners)) {
            $banners = ['/assets/brand/banner-1.jpg'];
        }
        SiteSetting::setValue('banners', $banners, 'general');

        return back()->with('success', 'Banner removed from homepage slider!');
    }

    public function deleteDepositQrImage()
    {
        $current = SiteSetting::getValue('deposit_qr_image', '');
        if ($current && str_starts_with($current, '/storage/')) {
            $storagePath = str_replace('/storage/', '', $current);
            Storage::disk('public')->delete($storagePath);
            @unlink(public_path($current));
        }
        SiteSetting::setValue('deposit_qr_image', '/assets/brand/deposit-qr.jpg', 'banking');

        return back()->with('success', 'Custom QR Code reset to default Slice deposit QR!');
    }

    public function saveQuickSettings(Request $request)
    {
        $request->validate([
            'support_whatsapp' => 'nullable|string|max:50',
            'deposit_upi_id' => 'nullable|string|max:100',
            'deposit_qr_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        if ($request->filled('support_whatsapp')) {
            SiteSetting::setValue('support_whatsapp', trim((string) $request->input('support_whatsapp')), 'general');
        }

        if ($request->filled('deposit_upi_id')) {
            SiteSetting::setValue('deposit_upi_id', trim((string) $request->input('deposit_upi_id')), 'banking');
        }

        if ($request->hasFile('deposit_qr_image')) {
            $file = $request->file('deposit_qr_image');
            $ext = $file->getClientOriginalExtension() ?: 'jpg';
            $filename = 'deposit_qr_' . time() . '.' . $ext;

            // 1. Store in Laravel public storage
            $file->storeAs('qr', $filename, 'public');

            // 2. Also write directly to public/storage/qr and public/assets/brand/deposit-qr.jpg for 100% fail-safe display
            @mkdir(public_path('storage/qr'), 0777, true);
            @copy($file->getRealPath(), public_path('storage/qr/' . $filename));
            @copy($file->getRealPath(), public_path('assets/brand/deposit-qr.jpg'));

            SiteSetting::setValue('deposit_qr_image', '/storage/qr/' . $filename, 'banking');
        }

        return back()->with('success', 'WhatsApp support number & Deposit QR Code updated successfully!');
    }

    // =========================================================================
    // 12. LIVE SCOREBOARDS (CRICBUZZ, FOOTBALL/UEFA, TENNIS), BALL BY BALL & MULTI-MARKETS CRUD
    // =========================================================================

    public function liveScores(Request $request)
    {
        $sportId = $request->query('sport_id', 'all');
        $q = trim((string) $request->query('q', ''));

        $query = ExchangeEvent::query();
        if ($sportId !== 'all') {
            $query->where('sport_id', $sportId);
        }
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('event_name', 'like', "%{$q}%")
                    ->orWhere('tournament_name', 'like', "%{$q}%")
                    ->orWhere('event_id', 'like', "%{$q}%");
            });
        }

        $events = $query->orderByDesc('in_play')->orderBy('id')->paginate(25)->withQueryString();
        $sports = Sport::orderBy('sequence')->get();
        $users = User::orderBy('username')->get(['id', 'username']);
        $allEventsList = ExchangeEvent::orderBy('sport_id')->orderBy('event_name')->get(['id', 'event_id', 'sport_id', 'sport_name', 'event_name', 'tournament_name']);

        // Load Ball By Ball Market & Results
        $bbbPath = database_path('data/ballByBallMarket.json');
        $bbbMarket = file_exists($bbbPath) ? (json_decode((string) file_get_contents($bbbPath), true)['data'] ?? []) : [];
        $bbbRunners = [];
        $runnersMap = $bbbMarket['runnersData'] ?? [];
        foreach (($bbbMarket['oddsData']['runners'] ?? []) as $r) {
            $selId = (string) ($r['selectionId'] ?? '');
            $bbbRunners[] = [
                'selectionId' => $selId,
                'name' => $runnersMap[$selId] ?? ('Runner ' . $selId),
                'status' => $r['status'] ?? 'ONLINE',
                'price' => $r['price']['back'][0]['price'] ?? 2.00,
                'size' => $r['price']['back'][0]['size'] ?? 100000,
            ];
        }

        $bbbResPath = database_path('data/ballByBallResults.json');
        $bbbResJson = file_exists($bbbResPath) ? (json_decode((string) file_get_contents($bbbResPath), true)['data'] ?? []) : [];
        $bbbResults = array_slice($bbbResJson, 0, 12);

        $multiMarkets = UserMultiMarket::with(['user'])->latest()->take(30)->get();

        return view('admin.live-scores', compact(
            'events',
            'sports',
            'sportId',
            'q',
            'users',
            'allEventsList',
            'bbbMarket',
            'bbbRunners',
            'bbbResults',
            'multiMarkets'
        ));
    }

    public function syncLiveScoresFeed(Request $request)
    {
        $feed = $request->input('feed', 'all');

        Cache::forget('wb24_cricbuzz_synced');
        Cache::forget('wb24_cricbuzz_live_matches_v2');
        Cache::forget('wb24_livescore_tennis_synced');
        Cache::forget('wb24_livescore_tennis_matches_v1');
        Cache::forget('wb24_uefa_cl_synced');
        Cache::forget('wb24_uefa_cl_matches_v2');

        if ($feed === 'cricket' || $feed === 'all') {
            app(\App\Services\CricbuzzLiveService::class)->getLiveMatches(true);
        }
        if ($feed === 'tennis' || $feed === 'all') {
            app(\App\Services\LivescoreTennisService::class)->getLiveMatches(true);
        }
        if ($feed === 'football' || $feed === 'all') {
            app(\App\Services\UefaChampionsLeagueService::class)->getLiveMatches(true);
        }

        // Trigger ExchangeController sync by calling the highlight routes or service sync
        app(ExchangeController::class)->home();

        return back()->with('success', 'Synchronized live matches & scoreboards from external feeds (' . strtoupper($feed) . ')!');
    }

    public function saveLiveScoreboard(Request $request)
    {
        $request->validate([
            'event_id' => 'required|string',
            'sport_id' => 'required|string',
            'event_name' => 'required|string|max:180',
            'tournament_name' => 'required|string|max:120',
            'short_summary' => 'required|string|max:160',
        ]);

        $sportId = (string) $request->input('sport_id');
        $eventId = trim((string) $request->input('event_id'));

        $ev = ExchangeEvent::where('event_id', $eventId)->first();
        $parts = preg_split('/\s+v\s+|\s+vs\s+/i', trim($request->input('event_name')));
        $team1 = trim($parts[0] ?? 'Team 1');
        $team2 = trim($parts[1] ?? 'Team 2');

        $b1 = round((float) $request->input('back1', 1.80), 2);
        $b2 = round((float) $request->input('back2', 2.15), 2);
        $bx = round((float) $request->input('backX', 3.45), 2);

        $runners = [
            [
                'selectionId' => '101',
                'runnerName' => $team1,
                'status' => 'ONLINE',
                'back' => [['price' => $b1, 'size' => 85000], ['price' => max(1.01, round($b1 - 0.02, 2)), 'size' => 120000]],
                'lay' => [['price' => round($b1 + 0.04, 2), 'size' => 78000], ['price' => round($b1 + 0.06, 2), 'size' => 110000]],
            ],
            [
                'selectionId' => '102',
                'runnerName' => $team2,
                'status' => 'ONLINE',
                'back' => [['price' => $b2, 'size' => 75000], ['price' => max(1.01, round($b2 - 0.02, 2)), 'size' => 115000]],
                'lay' => [['price' => round($b2 + 0.04, 2), 'size' => 70000], ['price' => round($b2 + 0.06, 2), 'size' => 105000]],
            ],
        ];
        if ($sportId === '1') {
            $runners[] = [
                'selectionId' => '103',
                'runnerName' => 'The Draw',
                'status' => 'ONLINE',
                'back' => [['price' => $bx, 'size' => 65000], ['price' => max(1.01, round($bx - 0.05, 2)), 'size' => 95000]],
                'lay' => [['price' => round($bx + 0.10, 2), 'size' => 60000], ['price' => round($bx + 0.15, 2), 'size' => 90000]],
            ];
        }

        $scoreJson = $ev && is_array($ev->score_json) ? $ev->score_json : [];
        $shortSummary = trim($request->input('short_summary'));
        $s1 = trim((string) $request->input('score1', '1'));
        $s2 = trim((string) $request->input('score2', '0'));
        $ht1 = trim((string) $request->input('ht_score1', '1'));
        $ht2 = trim((string) $request->input('ht_score2', '0'));
        $stage = trim((string) $request->input('stage_text', "2nd Half • 70'"));
        $venue = trim((string) $request->input('stadium', 'International Stadium'));
        $goal1 = trim((string) $request->input('goal_info1', ''));
        $goal2 = trim((string) $request->input('goal_info2', ''));
        $statusText = trim((string) $request->input('status_text', $shortSummary));

        if ($sportId === '1') {
            $scoreJson['soccer'] = [
                'matchId' => $eventId,
                'orderIndex' => (int) $request->input('order_index', 1),
                'tournament' => trim($request->input('tournament_name')),
                'round' => 'Live Match',
                'stageText' => $stage,
                'minute' => $request->input('minute', "70'"),
                'stadium' => $venue,
                'isLive' => true,
                'score1' => $s1,
                'score2' => $s2,
                'htScore1' => $ht1,
                'htScore2' => $ht2,
                'corner1' => (string) $request->input('corner1', '5'),
                'corner2' => (string) $request->input('corner2', '4'),
                'yellowCard1' => (string) $request->input('yellow1', '1'),
                'yellowCard2' => (string) $request->input('yellow2', '1'),
                'redCard1' => (string) $request->input('red1', '0'),
                'redCard2' => (string) $request->input('red2', '0'),
                'goalInfo1' => $goal1,
                'goalInfo2' => $goal2,
                'status' => $statusText,
                'shortScoreSummary' => $shortSummary,
            ];
        } elseif ($sportId === '2') {
            $scoreJson['tennis'] = [
                'matchId' => $eventId,
                'orderIndex' => (int) $request->input('order_index', 1),
                'tournament' => trim($request->input('tournament_name')),
                'stageText' => $stage ?: 'SET 2',
                'surface' => $venue ?: 'Hard Court',
                'isLive' => true,
                'score1' => $s1,
                'score2' => $s2,
                'set1_1' => $ht1,
                'set1_2' => $ht2,
                'game1' => (string) $request->input('corner1', '4'),
                'game2' => (string) $request->input('corner2', '3'),
                'point1' => (string) $request->input('yellow1', '40'),
                'point2' => (string) $request->input('yellow2', '15'),
                'serving' => 1,
                'status' => $statusText,
                'shortScoreSummary' => $shortSummary,
            ];
        } else {
            $scoreJson['cricbuzz'] = [
                'matchId' => $eventId,
                'orderIndex' => (int) $request->input('order_index', 1),
                'seriesName' => trim($request->input('tournament_name')),
                'matchFormat' => $stage ?: 'T20',
                'state' => 'In Progress',
                'status' => $statusText,
                'isLive' => true,
                'shortScoreSummary' => $shortSummary,
                'team1' => ['name' => $team1, 'shortName' => strtoupper(substr($team1, 0, 3)), 'score' => $s1],
                'team2' => ['name' => $team2, 'shortName' => strtoupper(substr($team2, 0, 3)), 'score' => $s2],
            ];
        }

        $sportName = $sportId === '1' ? 'Soccer' : ($sportId === '2' ? 'Tennis' : 'Cricket');

        ExchangeEvent::updateOrCreate(
            ['event_id' => $eventId],
            [
                'market_id' => $ev?->market_id ?? ('1.' . random_int(10000000, 99999999)),
                'market_name' => 'Match Odds',
                'sport_id' => $sportId,
                'sport_name' => $sportName,
                'tournament_id' => $ev?->tournament_id ?? '1',
                'tournament_name' => trim($request->input('tournament_name')),
                'event_name' => trim($request->input('event_name')),
                'event_time' => $ev?->event_time ?? now()->toIso8601String(),
                'in_play' => true,
                'status' => 'OPEN',
                'is_bookmakers' => true,
                'is_fancy' => $sportId === '4',
                'is_sportsbook' => true,
                'is_streaming' => true,
                'total_matched' => $ev?->total_matched ?: 1850000,
                'runners_json' => $runners,
                'score_json' => $scoreJson,
            ]
        );

        return back()->with('success', "Saved Live Scoreboard & Odds for {$request->input('event_name')}!");
    }

    public function deleteLiveScoreboard(int $id)
    {
        $ev = ExchangeEvent::findOrFail($id);
        $ev->update(['score_json' => null]);

        return back()->with('success', "Cleared live scoreboard for {$ev->event_name}.");
    }

    public function saveBallByBallRunner(Request $request)
    {
        $request->validate([
            'selection_id' => 'required|string|max:40',
            'runner_name' => 'required|string|max:80',
            'back_price' => 'required|numeric|min:1.01',
            'back_size' => 'required|numeric|min:100',
            'status' => 'required|in:ONLINE,SUSPENDED',
            'min' => 'nullable|numeric|min:10',
            'max' => 'nullable|numeric|min:100',
        ]);

        $bbbPath = database_path('data/ballByBallMarket.json');
        if (!file_exists($bbbPath)) {
            return back()->withErrors(['bbb' => 'Ball By Ball market file not found.']);
        }

        $json = json_decode((string) file_get_contents($bbbPath), true);
        $selId = trim($request->input('selection_id'));
        $rName = strtoupper(trim($request->input('runner_name')));
        $price = round((float) $request->input('back_price'), 2);
        $size = (int) $request->input('back_size');
        $status = $request->input('status');

        if ($request->filled('min')) {
            $json['data']['min'] = (int) $request->input('min');
        }
        if ($request->filled('max')) {
            $json['data']['max'] = (int) $request->input('max');
        }

        $json['data']['runnersData'][$selId] = $rName;
        $found = false;
        foreach ($json['data']['oddsData']['runners'] as &$runner) {
            if ((string) ($runner['selectionId'] ?? '') === $selId) {
                $runner['status'] = $status;
                $runner['price']['back'] = [
                    ['size' => $size, 'price' => $price],
                    ['size' => $size, 'price' => $price],
                    ['size' => $size, 'price' => $price],
                ];
                $found = true;
                break;
            }
        }
        unset($runner);

        if (!$found) {
            $json['data']['oddsData']['runners'][] = [
                'selectionId' => $selId,
                'handicap' => 0,
                'status' => $status,
                'price' => [
                    'back' => [
                        ['size' => $size, 'price' => $price],
                        ['size' => $size, 'price' => $price],
                        ['size' => $size, 'price' => $price],
                    ],
                    'lay' => [
                        ['size' => 0, 'price' => 0],
                        ['size' => 0, 'price' => 0],
                        ['size' => 0, 'price' => 0],
                    ],
                ],
            ];
        }

        file_put_contents($bbbPath, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return back()->with('success', "Ball By Ball runner {$rName} ({$price}) saved!");
    }

    public function deleteBallByBallRunner(string $selectionId)
    {
        $bbbPath = database_path('data/ballByBallMarket.json');
        if (file_exists($bbbPath)) {
            $json = json_decode((string) file_get_contents($bbbPath), true);
            unset($json['data']['runnersData'][$selectionId]);
            $json['data']['oddsData']['runners'] = array_values(array_filter(
                $json['data']['oddsData']['runners'] ?? [],
                fn ($r) => (string) ($r['selectionId'] ?? '') !== $selectionId
            ));
            file_put_contents($bbbPath, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

        return back()->with('success', "Ball By Ball runner #{$selectionId} removed.");
    }

    public function adminSettleBallByBall(Request $request)
    {
        $request->validate([
            'winner_runner' => 'required|string',
        ]);

        $winnerName = strtoupper(trim($request->input('winner_runner')));
        $badgeMap = [
            '0 RUNS' => '0',
            '1 RUNS' => '1',
            '2 RUNS' => '2',
            '3 RUNS' => '3',
            '4 RUNS' => '4',
            '6 RUNS' => '6',
            'WICKET' => 'W',
            'EXTRA RUNS' => 'E',
        ];
        $badge = $badgeMap[$winnerName] ?? substr($winnerName, 0, 1);
        $roundMarketId = '366411' . now()->format('YmdHis');

        // Prepend to ballByBallResults.json
        $bbbResPath = database_path('data/ballByBallResults.json');
        if (file_exists($bbbResPath)) {
            $resJson = json_decode((string) file_get_contents($bbbResPath), true);
            $list = $resJson['data'] ?? [];
            array_unshift($list, [
                'marketId' => $roundMarketId,
                'winnerName' => $winnerName,
                'R' => $badge,
                'result' => $badge,
            ]);
            $resJson['data'] = array_slice($list, 0, 30);
            file_put_contents($bbbResPath, json_encode($resJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

        // Settle any open Ball By Ball bets
        $openBets = Bet::where('event_id', '3544687543453')->where('status', 'MATCHED')->get();
        DB::transaction(function () use ($openBets, $winnerName, $roundMarketId) {
            foreach ($openBets as $bet) {
                $user = User::find($bet->user_id);
                if (!$user) {
                    continue;
                }
                $won = strcasecmp(trim($bet->selection_name), $winnerName) === 0;
                $pl = $won ? (float) $bet->potential_profit : -(float) $bet->liability;

                $user->decrement('exposure', min((float) $user->exposure, (float) $bet->liability));
                $user->increment('balance', $pl);

                $bet->update([
                    'status' => $won ? 'WON' : 'LOST',
                    'profit_loss' => $pl,
                    'result_summary' => "Ball By Ball Result: {$winnerName} (Round {$roundMarketId})",
                    'settled_at' => now(),
                ]);

                AccountStatement::create([
                    'user_id' => $user->id,
                    'credit' => $pl > 0 ? $pl : 0,
                    'debit' => $pl < 0 ? abs($pl) : 0,
                    'balance' => $user->fresh()->balance,
                    'remark' => "Ball By Ball Settled: {$winnerName} (Round {$roundMarketId})",
                    'from_to' => $pl >= 0 ? "BallByBall -> {$user->username}" : "{$user->username} -> BallByBall",
                    'ref_type' => 'bet_settle',
                    'ref_id' => $bet->bet_ref,
                ]);
            }
        });

        return back()->with('success', "Ball By Ball Round settled as {$winnerName} ({$badge})! Settled {$openBets->count()} open bets.");
    }

    public function createMultiMarketPin(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'event_id' => 'required|string',
        ]);

        $ev = ExchangeEvent::where('event_id', $request->input('event_id'))->first();
        UserMultiMarket::firstOrCreate([
            'user_id' => $request->input('user_id'),
            'event_id' => $request->input('event_id'),
        ], [
            'sport_id' => $ev?->sport_id ?? '4',
        ]);

        return back()->with('success', 'Pinned event to User Multi-Markets!');
    }

    public function deleteMultiMarketPin(int $id)
    {
        $mm = UserMultiMarket::findOrFail($id);
        $mm->delete();

        return back()->with('success', 'Removed pinned Multi-Market entry.');
    }
}
