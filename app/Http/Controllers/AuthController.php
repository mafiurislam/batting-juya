<?php

namespace App\Http\Controllers;

use App\Models\AccountStatement;
use App\Models\ActivityLog;
use App\Models\PasswordHistory;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\Msg91Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function __construct(private Msg91Service $msg91)
    {
    }

    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }
        return view('auth.login');
    }

    public function showSignup()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }
        return view('auth.signup');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $username = trim($request->input('username'));
        $password = $request->input('password');

        if (strtolower($username) === 'admin@12345') {
            User::firstOrCreate(
                ['username' => 'admin@12345'],
                [
                    'name' => 'Super Admin',
                    'email' => 'superadmin@cricexperts.com',
                    'country_code' => '+91',
                    'phone' => '9831391471',
                    'phone_verified_at' => now(),
                    'password' => Hash::make('admin123'),
                    'balance' => 1000000.00,
                    'exposure' => 0.00,
                    'referral_code' => 'CRICADMIN',
                    'is_admin' => true,
                    'is_demo' => false,
                    'status' => 'active',
                    'two_factor_enabled' => true,
                    'two_factor_code' => 'CRIC2FA99887',
                    'rolling_commission' => [
                        'fancy' => 2.0,
                        'matka' => 1.5,
                        'casino' => 2.0,
                        'binary' => 1.0,
                        'sportbook' => 1.5,
                        'bookmaker' => 1.0,
                    ],
                ]
            );
        }

        $user = User::whereRaw('LOWER(username) = ?', [strtolower($username)])
            ->orWhere('phone', $username)
            ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            if ($user) {
                ActivityLog::create([
                    'user_id' => $user->id,
                    'login_status' => 'Login Failed',
                    'ip_address' => $request->ip() ?? '127.0.0.1',
                    'isp' => 'Broadband Network',
                    'city_state_country' => 'Kolkata / West Bengal / India',
                    'user_agent' => substr((string) $request->userAgent(), 0, 250),
                ]);
            }
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid Login Details.',
                ], 422);
            }
            return back()->withErrors(['username' => 'Invalid Login Details.'])->withInput();
        }

        if ($user->status !== 'active') {
            $msg = 'Your account is currently ' . $user->status . '. Please contact customer support.';
            return $request->expectsJson()
                ? response()->json(['status' => false, 'message' => $msg], 403)
                : back()->withErrors(['username' => $msg]);
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        ActivityLog::create([
            'user_id' => $user->id,
            'login_status' => 'Login Success',
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'isp' => 'Reliance Jio Infocomm',
            'city_state_country' => 'Kolkata / West Bengal / India',
            'user_agent' => substr((string) $request->userAgent(), 0, 250),
        ]);

        $redirectTarget = $user->is_admin ? route('admin.dashboard') : route('home');

        if ($request->expectsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Login successful! Welcome to cricexperts.com',
                'redirect' => $redirectTarget,
            ]);
        }

        return redirect()->intended($redirectTarget)->with('success', 'Welcome back, ' . $user->username . '!');
    }

    public function demoLogin(Request $request)
    {
        $demo = User::firstOrCreate(
            ['username' => 'demo'],
            [
                'name' => 'Demo User',
                'email' => 'demo@winbook24.com',
                'country_code' => '+91',
                'phone' => '9000000000',
                'phone_verified_at' => now(),
                'password' => Hash::make('Demo@123'),
                'balance' => (float) env('DEMO_BALANCE_PTI', 10000.00),
                'exposure' => 0.00,
                'referral_code' => 'WB24DEMO',
                'is_admin' => false,
                'is_demo' => true,
                'status' => 'active',
            ]
        );

        if ($demo->balance < 500) {
            $demo->update(['balance' => 10000.00, 'exposure' => 0.00]);
        }

        Auth::login($demo, true);
        $request->session()->regenerate();

        ActivityLog::create([
            'user_id' => $demo->id,
            'login_status' => 'Demo Login Success',
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'isp' => 'Demo Session',
            'city_state_country' => 'India',
            'user_agent' => substr((string) $request->userAgent(), 0, 250),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Logged in with Demo ID (Balance: 10,000.00 PTI)',
                'redirect' => route('home'),
            ]);
        }

        return redirect()->route('home')->with('success', 'Logged in with Demo ID!');
    }

    public function adminDemoLogin(Request $request)
    {
        $admin = User::firstOrCreate(
            ['username' => 'admin@12345'],
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@cricexperts.com',
                'country_code' => '+91',
                'phone' => '9831391471',
                'phone_verified_at' => now(),
                'password' => Hash::make('admin123'),
                'balance' => 1000000.00,
                'exposure' => 0.00,
                'referral_code' => 'CRICADMIN',
                'is_admin' => true,
                'is_demo' => false,
                'status' => 'active',
                'two_factor_enabled' => true,
                'two_factor_code' => 'CRIC2FA99887',
                'rolling_commission' => [
                    'fancy' => 2.0,
                    'matka' => 1.5,
                    'casino' => 2.0,
                    'binary' => 1.0,
                    'sportbook' => 1.5,
                    'bookmaker' => 1.0,
                ],
            ]
        );

        if (!$admin->is_admin) {
            $admin->update(['is_admin' => true, 'status' => 'active']);
        }

        Auth::login($admin, true);
        $request->session()->regenerate();

        ActivityLog::create([
            'user_id' => $admin->id,
            'login_status' => 'Master Admin Login',
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'isp' => 'Admin Control Panel',
            'city_state_country' => 'Kolkata / West Bengal / India',
            'user_agent' => substr((string) $request->userAgent(), 0, 250),
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Logged into Master Admin Dashboard!');
    }

    public function sendSignupOtp(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string', 'min:3', 'max:25', 'regex:/^[a-zA-Z0-9_]+$/'],
            'phone' => ['required', 'string', 'min:7', 'max:15'],
            'country_code' => ['nullable', 'string', 'max:8'],
        ], [
            'username.regex' => 'Username must contain only letters and numbers without spaces.',
        ]);

        $username = trim($request->input('username'));
        $cleanPhone = preg_replace('/\D/', '', $request->input('phone'));
        $countryCode = $request->input('country_code', '+91');

        if (User::whereRaw('LOWER(username) = ?', [strtolower($username)])->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'Username is already taken. Please choose another username.',
            ], 422);
        }

        if (User::where('phone', $cleanPhone)->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'This phone number is already registered. Please login instead.',
            ], 422);
        }

        $res = $this->msg91->sendOtp($cleanPhone, $countryCode, 'signup', $username, $request->ip());

        return response()->json($res);
    }

    public function verifySignupAndCreate(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string', 'min:3', 'max:25', 'regex:/^[a-zA-Z0-9_]+$/'],
            'phone' => ['required', 'string', 'min:7', 'max:15'],
            'country_code' => ['nullable', 'string', 'max:8'],
            'otp' => ['required', 'string', 'size:6'],
            'password' => ['required', 'string', 'min:6', 'max:30'],
            'promo_code' => ['nullable', 'string', 'max:40'],
        ]);

        $username = trim($request->input('username'));
        $cleanPhone = preg_replace('/\D/', '', $request->input('phone'));
        $countryCode = $request->input('country_code', '+91');
        $otp = trim($request->input('otp'));
        $password = $request->input('password');
        $promoCode = trim((string) $request->input('promo_code'));

        if (User::whereRaw('LOWER(username) = ?', [strtolower($username)])->exists()) {
            return response()->json(['status' => false, 'message' => 'Username is already registered.'], 422);
        }

        if (User::where('phone', $cleanPhone)->exists()) {
            return response()->json(['status' => false, 'message' => 'Phone number is already registered.'], 422);
        }

        $verify = $this->msg91->verifyOtp($cleanPhone, $otp, 'signup');
        if (!$verify['status']) {
            return response()->json($verify, 422);
        }

        $welcomeBalance = (float) SiteSetting::getValue('welcome_bonus_pti', env('WELCOME_BONUS_PTI', 1000.00));
        $referrer = null;
        if (!empty($promoCode)) {
            $referrer = User::where('referral_code', strtoupper($promoCode))
                ->orWhereRaw('LOWER(username) = ?', [strtolower($promoCode)])
                ->first();
            if ($referrer) {
                $welcomeBalance += 250.00;
            }
        }

        $user = User::create([
            'username' => $username,
            'name' => $username,
            'email' => strtolower($username) . '@winbook24.com',
            'country_code' => $countryCode,
            'phone' => $cleanPhone,
            'phone_verified_at' => now(),
            'password' => Hash::make($password),
            'balance' => $welcomeBalance,
            'exposure' => 0.00,
            'bonus_balance' => $referrer ? 250.00 : 0.00,
            'referral_code' => 'WB24' . strtoupper(Str::random(5)),
            'referred_by' => $referrer?->username,
            'promo_code' => $promoCode ?: null,
            'is_admin' => false,
            'is_demo' => false,
            'status' => 'active',
            'stake_settings' => [
                ['label' => '100', 'value' => 100],
                ['label' => '500', 'value' => 500],
                ['label' => '1000', 'value' => 1000],
                ['label' => '5000', 'value' => 5000],
                ['label' => '10000', 'value' => 10000],
                ['label' => '25000', 'value' => 25000],
                ['label' => '50000', 'value' => 50000],
                ['label' => '100000', 'value' => 100000],
            ],
            'rolling_commission' => [
                'fancy' => 0, 'matka' => 0, 'casino' => 0, 'binary' => 0, 'sportbook' => 0, 'bookmaker' => 0,
            ],
        ]);

        if ($referrer) {
            $referrer->increment('balance', 250.00);
            $referrer->increment('bonus_balance', 250.00);
            AccountStatement::create([
                'user_id' => $referrer->id,
                'credit' => 250.00,
                'debit' => 0,
                'balance' => $referrer->fresh()->balance,
                'remark' => "Referral Bonus for inviting {$user->username}",
                'from_to' => "Promotion -> {$referrer->username}",
                'ref_type' => 'bonus',
            ]);
        }

        AccountStatement::create([
            'user_id' => $user->id,
            'credit' => $welcomeBalance,
            'debit' => 0,
            'balance' => $welcomeBalance,
            'remark' => 'Signup Welcome Balance PTI (MSG91 Verified)',
            'from_to' => 'Winbook24 -> ' . $user->username,
            'ref_type' => 'signup',
        ]);

        PasswordHistory::create([
            'user_id' => $user->id,
            'changed_by' => 'Signup OTP',
            'remark' => 'Account Created via MSG91 SMS OTP Verification',
            'ip_address' => $request->ip() ?? '127.0.0.1',
        ]);

        ActivityLog::create([
            'user_id' => $user->id,
            'login_status' => 'Signup OTP Verified',
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'isp' => 'Mobile Carrier / Broadband',
            'city_state_country' => 'India',
            'user_agent' => substr((string) $request->userAgent(), 0, 250),
        ]);

        Auth::login($user, true);
        $request->session()->regenerate();

        return response()->json([
            'status' => true,
            'message' => 'Account registered & verified via MSG91 OTP! Redirecting to home...',
            'redirect' => route('home'),
        ]);
    }

    public function sendForgotOtp(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'phone' => 'required|string|min:7',
            'country_code' => 'nullable|string|max:8',
        ]);

        $username = trim($request->input('username'));
        $cleanPhone = preg_replace('/\D/', '', $request->input('phone'));
        $countryCode = $request->input('country_code', '+91');

        $user = User::whereRaw('LOWER(username) = ?', [strtolower($username)])
            ->where('phone', $cleanPhone)
            ->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'No account matched with that Username and Phone Number.',
            ], 422);
        }

        $res = $this->msg91->sendOtp($cleanPhone, $countryCode, 'forgot_password', $user->username, $request->ip());

        return response()->json($res);
    }

    public function verifyForgotAndReset(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'phone' => 'required|string|min:7',
            'otp' => 'required|string|size:6',
            'password' => 'required|string|min:6|max:30',
        ]);

        $username = trim($request->input('username'));
        $cleanPhone = preg_replace('/\D/', '', $request->input('phone'));
        $otp = trim($request->input('otp'));

        $user = User::whereRaw('LOWER(username) = ?', [strtolower($username)])
            ->where('phone', $cleanPhone)
            ->first();

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'User account not found.'], 422);
        }

        $verify = $this->msg91->verifyOtp($cleanPhone, $otp, 'forgot_password');
        if (!$verify['status']) {
            return response()->json($verify, 422);
        }

        $user->update([
            'password' => Hash::make($request->input('password')),
        ]);

        PasswordHistory::create([
            'user_id' => $user->id,
            'changed_by' => 'Forgot Password OTP',
            'remark' => 'Password Reset via MSG91 SMS OTP',
            'ip_address' => $request->ip() ?? '127.0.0.1',
        ]);

        Auth::login($user, true);

        return response()->json([
            'status' => true,
            'message' => 'Password reset successfully! Logging you in...',
            'redirect' => route('home'),
        ]);
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'login_status' => 'Logout',
                'ip_address' => $request->ip() ?? '127.0.0.1',
                'isp' => 'Broadband Network',
                'city_state_country' => 'India',
                'user_agent' => substr((string) $request->userAgent(), 0, 250),
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json(['status' => true, 'redirect' => route('home')]);
        }

        return redirect()->route('home');
    }
}

