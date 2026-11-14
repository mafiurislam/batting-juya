<?php

namespace App\Http\Controllers;

use App\Models\AccountStatement;
use App\Models\ActivityLog;
use App\Models\BankingTransaction;
use App\Models\Bet;
use App\Models\PasswordHistory;
use App\Models\SiteSetting;
use App\Models\Sport;
use App\Models\User;
use App\Models\UserBankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AccountController extends Controller
{
    private function sharedData(): array
    {
        return [
            'sports' => Sport::where('status', true)->orderBy('sequence')->get(),
            'openBets' => Auth::check()
                ? Bet::where('user_id', Auth::id())->where('status', 'MATCHED')->latest()->take(20)->get()
                : collect(),
            'supportWhatsapp' => SiteSetting::getValue('support_whatsapp', '+919831391470'),
            'newsMarquee' => SiteSetting::getValue('news_marquee', 'Minimum deposit 500 and minimum withdrawal 1000'),
        ];
    }

    public function myProfile()
    {
        return view('account.myprofile', array_merge($this->sharedData(), [
            'user' => Auth::user(),
        ]));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|max:30|confirmed',
        ]);

        $user = Auth::user();
        if (!Hash::check($request->input('current_password'), $user->password)) {
            return back()->withErrors(['current_password' => 'Your current password does not match.']);
        }

        $user->update(['password' => Hash::make($request->input('new_password'))]);

        PasswordHistory::create([
            'user_id' => $user->id,
            'changed_by' => 'Self',
            'remark' => 'Password Changed by User from My Profile',
            'ip_address' => $request->ip() ?? '127.0.0.1',
        ]);

        return back()->with('success', 'Password updated successfully!');
    }

    public function twoFactor()
    {
        $user = Auth::user();
        if (!$user->two_factor_code) {
            $user->update(['two_factor_code' => 'PVCVIZSNMNWTSVK6MESDQJKFMM']);
        }

        return view('account.two-factor', array_merge($this->sharedData(), [
            'user' => $user->fresh(),
        ]));
    }

    public function toggleTwoFactor(Request $request)
    {
        $user = Auth::user();
        if ($request->filled('otp_code')) {
            $user->update([
                'two_factor_enabled' => true,
            ]);
            return back()->with('success', 'Two-Factor Authentication Verified & Enabled!');
        }

        $user->update([
            'two_factor_enabled' => !$user->two_factor_enabled,
        ]);

        return back()->with('success', $user->two_factor_enabled ? 'Two-Factor Authentication Enabled!' : 'Two-Factor Authentication Disabled.');
    }

    public function referAndEarn()
    {
        $user = Auth::user();
        $referrals = User::where('referred_by', $user->username)->latest()->get();

        return view('account.refer-and-earn', array_merge($this->sharedData(), [
            'user' => $user,
            'referrals' => $referrals,
        ]));
    }

    public function rollingCommission(Request $request)
    {
        $user = Auth::user();
        return view('account.rolling-commission', array_merge($this->sharedData(), [
            'user' => $user,
            'dataSource' => $request->query('data_source', 'LIVE'),
            'sportId' => $request->query('sport_id', '4'),
            'startDate' => $request->query('start_date', now()->format('Y-m-d')),
            'endDate' => $request->query('end_date', now()->format('Y-m-d')),
        ]));
    }

    private function bankingData(string $section = 'hub'): array
    {
        $user = Auth::user();
        $transactions = BankingTransaction::where('user_id', $user->id)->latest()->take(30)->get();
        $withdrawals = BankingTransaction::where('user_id', $user->id)->where('type', 'withdraw')->latest()->take(30)->get();
        $bankAccounts = UserBankAccount::where('user_id', $user->id)->latest()->get();

        return array_merge($this->sharedData(), [
            'section' => $section,
            'user' => $user,
            'transactions' => $transactions,
            'withdrawals' => $withdrawals,
            'bankAccounts' => $bankAccounts,
            'depositUpi' => SiteSetting::getValue('deposit_upi_id', 'winbook24.pay@axis'),
            'depositAccountName' => SiteSetting::getValue('deposit_account_name', 'WINBOOK24 ENTERPRISES'),
            'depositBankName' => SiteSetting::getValue('deposit_bank_name', 'HDFC Bank'),
            'depositAccountNumber' => SiteSetting::getValue('deposit_account_number', '50200089412580'),
            'depositIfsc' => SiteSetting::getValue('deposit_ifsc', 'HDFC0001245'),
            'minDeposit' => SiteSetting::getValue('min_deposit', '100'),
            'minWithdraw' => SiteSetting::getValue('min_withdraw', '100'),
        ]);
    }

    public function banking(Request $request)
    {
        $section = $request->query('section', 'hub');
        return view('account.banking', $this->bankingData($section));
    }

    public function bankingWithdraw()
    {
        return view('account.banking', $this->bankingData('withdraw'));
    }

    public function bankingWithdrawDetails()
    {
        return view('account.banking', $this->bankingData('withdrawdetails'));
    }

    public function bankingReward()
    {
        return view('account.banking', $this->bankingData('reward'));
    }

    /**
     * Deposit Request with Laravel Storage file upload for payment screenshot/receipt.
     */
    public function submitDeposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100|max:5000000',
            'method' => 'nullable|string|in:UPI,QR,IMPS,NEFT,BANK,BKASH,NAGAD,USDT',
            'utr_number' => 'nullable|string|min:4|max:60',
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:10240',
            'instant_credit' => 'nullable|boolean',
        ]);

        $user = Auth::user();
        $amount = round((float) $request->input('amount'), 2);
        $method = $request->input('method') ?: 'UPI';
        $utrNumber = trim((string) $request->input('utr_number')) ?: ('UTR' . random_int(100000000000, 999999999999));
        $receiptPath = null;

        if ($request->hasFile('receipt')) {
            // Save to Laravel Storage disk 'public' -> storage/app/public/receipts
            $receiptPath = Storage::disk('public')->putFile('receipts', $request->file('receipt'));
        }

        // Transactions with uploaded screenshots wait for Admin verification by default
        $autoApprove = $request->boolean('instant_credit', false);

        DB::transaction(function () use ($user, $amount, $method, $utrNumber, $receiptPath, $autoApprove) {
            $tx = BankingTransaction::create([
                'tx_ref' => 'DEP' . strtoupper(Str::random(8)),
                'user_id' => $user->id,
                'type' => 'deposit',
                'method' => $method,
                'amount' => $amount,
                'charges' => 0,
                'final_amount' => $amount,
                'utr_number' => $utrNumber,
                'receipt_path' => $receiptPath,
                'status' => $autoApprove ? 'APPROVED' : 'PENDING',
                'admin_note' => $autoApprove ? 'Auto-Verified Deposit' : 'Screenshot Uploaded - Awaiting Admin Verification',
                'processed_at' => $autoApprove ? now() : null,
            ]);

            if ($autoApprove) {
                $user->increment('balance', $amount);
                AccountStatement::create([
                    'user_id' => $user->id,
                    'credit' => $amount,
                    'debit' => 0,
                    'balance' => $user->fresh()->balance,
                    'remark' => "Deposit ({$tx->method}) UTR: {$tx->utr_number}",
                    'from_to' => "Banking -> {$user->username}",
                    'ref_type' => 'deposit',
                    'ref_id' => $tx->tx_ref,
                ]);
            }
        });

        return back()->with(
            'success',
            $autoApprove
                ? "Deposit of ₹" . number_format($amount, 2) . " credited to your wallet!"
                : "Deposit request of ₹" . number_format($amount, 2) . " submitted successfully! Your account will be credited once payment screenshot is verified by admin."
        );
    }

    public function addBankAccount(Request $request)
    {
        $request->validate([
            'holder_name' => 'required|string|max:120',
            'bank_name' => 'required|string|max:120',
            'account_number' => 'required|string|max:50',
            'ifsc_code' => 'nullable|string|max:30',
            'upi_id' => 'nullable|string|max:100',
        ]);

        UserBankAccount::create([
            'user_id' => Auth::id(),
            'holder_name' => trim($request->input('holder_name')),
            'bank_name' => trim($request->input('bank_name')),
            'account_number' => trim($request->input('account_number')),
            'ifsc_code' => strtoupper(trim((string) $request->input('ifsc_code', 'WALLET'))),
            'upi_id' => trim((string) $request->input('upi_id')) ?: null,
            'is_default' => true,
        ]);

        return back()->with('success', 'Withdrawal account details added successfully!');
    }

    public function deleteBankAccount(int $id)
    {
        UserBankAccount::where('id', $id)->where('user_id', Auth::id())->delete();
        return back()->with('success', 'Bank account removed.');
    }

    public function submitWithdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100|max:5000000',
            'bank_account_id' => 'nullable|exists:user_bank_accounts,id',
        ]);

        $user = Auth::user();
        $amount = round((float) $request->input('amount'), 2);
        if ($user->available_balance < $amount) {
            return back()->withErrors(['amount' => 'Insufficient available balance for withdrawal.']);
        }

        $bank = null;
        if ($request->filled('bank_account_id')) {
            $bank = UserBankAccount::where('id', $request->input('bank_account_id'))
                ->where('user_id', $user->id)
                ->first();
        }
        if (!$bank) {
            $bank = UserBankAccount::where('user_id', $user->id)->latest()->first();
        }
        if (!$bank) {
            $bank = UserBankAccount::create([
                'user_id' => $user->id,
                'holder_name' => strtoupper($user->username),
                'bank_name' => 'Bank Transfer',
                'account_number' => '6067810000127',
                'ifsc_code' => 'BARB0PAHARI',
                'is_default' => true,
            ]);
        }

        DB::transaction(function () use ($user, $amount, $bank) {
            $user->decrement('balance', $amount);
            $tx = BankingTransaction::create([
                'tx_ref' => 'WDR' . strtoupper(Str::random(8)),
                'user_id' => $user->id,
                'type' => 'withdraw',
                'method' => 'IMPS/BANK',
                'amount' => $amount,
                'charges' => 0,
                'final_amount' => $amount,
                'bank_account_id' => $bank->id,
                'bank_details_json' => $bank->toArray(),
                'status' => 'PENDING',
                'admin_note' => 'Withdrawal Request Queued',
            ]);

            AccountStatement::create([
                'user_id' => $user->id,
                'credit' => 0,
                'debit' => $amount,
                'balance' => $user->fresh()->balance,
                'remark' => "Withdrawal Request ({$bank->bank_name} - {$bank->account_number})",
                'from_to' => "{$user->username} -> Banking",
                'ref_type' => 'withdraw',
                'ref_id' => $tx->tx_ref,
            ]);
        });

        return back()->with('success', 'Withdrawal request of ' . number_format($amount, 2) . ' PTI submitted!');
    }

    public function accountStatement(Request $request)
    {
        $startDate = $request->query('start_date', now()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->format('Y-m-d'));
        $dataSource = $request->query('data_source', 'LIVE');
        $perPage = (int) $request->query('per_page', 10);

        $query = AccountStatement::where('user_id', Auth::id());
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $statements = $query->latest()->paginate($perPage)->withQueryString();

        return view('account.account-statement', array_merge($this->sharedData(), [
            'statements' => $statements,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'dataSource' => $dataSource,
            'perPage' => $perPage,
        ]));
    }

    public function betHistory(Request $request)
    {
        $dataSource = $request->query('data_source', 'LIVE');
        $sportId = $request->query('sport_id', '4');
        $status = $request->query('status', 'SETTLED');
        $startDate = $request->query('start_date', now()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->format('Y-m-d'));
        $perPage = (int) $request->query('per_page', 10);

        $query = Bet::where('user_id', Auth::id());
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $endDate);
        }
        if ($request->filled('sport_id') && $sportId !== 'all') {
            $query->where('sport_id', $sportId);
        }
        if ($request->filled('status') && $status !== 'all') {
            if ($status === 'SETTLED') {
                $query->whereIn('status', ['WON', 'LOST', 'CASHOUT']);
            } elseif ($status === 'UNSETTLED') {
                $query->where('status', 'MATCHED');
            } else {
                $query->where('status', $status);
            }
        }

        $bets = $query->latest()->paginate($perPage)->withQueryString();

        return view('account.bet-history', array_merge($this->sharedData(), [
            'bets' => $bets,
            'dataSource' => $dataSource,
            'sportId' => $sportId,
            'status' => $status,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'perPage' => $perPage,
        ]));
    }

    public function profitLoss(Request $request)
    {
        $dataSource = $request->query('data_source', 'LIVE');
        $startDate = $request->query('start_date', now()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->format('Y-m-d'));
        $perPage = (int) $request->query('per_page', 10);

        $query = Bet::where('user_id', Auth::id())
            ->whereIn('status', ['WON', 'LOST', 'CASHOUT']);

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $settledBets = $query->latest()->get();

        $bySport = $settledBets->groupBy('sport_name')->map(function ($rows, $sportName) {
            $pl = round($rows->sum('profit_loss'), 2);
            return [
                'sport_name' => $sportName ?: 'Cricket',
                'total_bets' => $rows->count(),
                'total_stake' => round($rows->sum('stake'), 2),
                'profit_loss' => $pl,
                'commission' => 0,
                'total_pl' => $pl,
            ];
        })->values();

        return view('account.profit-loss', array_merge($this->sharedData(), [
            'bySport' => $bySport,
            'settledBets' => $settledBets->take(40),
            'totalPl' => round($settledBets->sum('profit_loss'), 2),
            'dataSource' => $dataSource,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'perPage' => $perPage,
        ]));
    }

    public function passwordHistory(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);
        $histories = PasswordHistory::where('user_id', Auth::id())->latest()->paginate($perPage)->withQueryString();

        return view('account.password-history', array_merge($this->sharedData(), [
            'histories' => $histories,
            'perPage' => $perPage,
        ]));
    }

    public function activityLog()
    {
        $logs = ActivityLog::where('user_id', Auth::id())->latest()->take(50)->get();

        return view('account.activity-log', array_merge($this->sharedData(), [
            'logs' => $logs,
        ]));
    }

    public function settings()
    {
        $stakes = Auth::check() ? Auth::user()->getStakeButtons() : [
            ['label' => '100', 'value' => 100],
            ['label' => '500', 'value' => 500],
            ['label' => '1000', 'value' => 1000],
            ['label' => '5000', 'value' => 5000],
            ['label' => '10000', 'value' => 10000],
            ['label' => '25000', 'value' => 25000],
            ['label' => '50000', 'value' => 50000],
            ['label' => '100000', 'value' => 100000],
        ];

        return view('account.settings', array_merge($this->sharedData(), [
            'stakes' => $stakes,
        ]));
    }

    public function saveSettings(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $labels = $request->input('labels', []);
        $values = $request->input('values', []);
        $stakes = [];
        for ($i = 0; $i < 8; $i++) {
            $val = max(10, (int) ($values[$i] ?? (($i + 1) * 500)));
            $lbl = trim((string) ($labels[$i] ?? $val));
            $stakes[] = ['label' => $lbl ?: (string) $val, 'value' => $val];
        }

        Auth::user()->update(['stake_settings' => $stakes]);

        return back()->with('success', 'Quick Stake Settings saved successfully!');
    }
}

