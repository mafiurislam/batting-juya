<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $guarded = ['id'];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_code',
    ];

    protected function casts(): array
    {
        return [
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'balance' => 'float',
            'exposure' => 'float',
            'bonus_balance' => 'float',
            'is_admin' => 'boolean',
            'is_demo' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'stake_settings' => 'array',
            'rolling_commission' => 'array',
        ];
    }

    public function getLockedProfitAttribute(): float
    {
        if (!$this->exists) {
            return 0.0;
        }
        $portfolio = app(\App\Services\ExchangeBookService::class)->computePortfolio((int) $this->id);

        return round((float) ($portfolio['total_locked_profit'] ?? 0.0), 2);
    }

    public function getAvailableBalanceAttribute(): float
    {
        if (!$this->exists) {
            return round((float) $this->balance - (float) $this->exposure, 2);
        }

        $portfolio = app(\App\Services\ExchangeBookService::class)->computePortfolio((int) $this->id);
        $hasOpenBets = !empty($portfolio['markets']);
        $effectiveExposure = $hasOpenBets ? (float) $portfolio['total_exposure'] : 0.0;
        $lockedProfit = (float) ($portfolio['total_locked_profit'] ?? 0.0);

        if (abs((float) $this->exposure - $effectiveExposure) >= 0.01) {
            $this->forceFill(['exposure' => $effectiveExposure])->saveQuietly();
        }

        return round((float) $this->balance - $effectiveExposure + $lockedProfit, 2);
    }

    public function syncBookWallet(): array
    {
        return app(\App\Services\ExchangeBookService::class)->syncUserWallet($this);
    }

    public function getStakeButtons(): array
    {
        return $this->stake_settings ?: [
            ['label' => '100', 'value' => 100],
            ['label' => '500', 'value' => 500],
            ['label' => '1000', 'value' => 1000],
            ['label' => '5000', 'value' => 5000],
            ['label' => '10000', 'value' => 10000],
            ['label' => '25000', 'value' => 25000],
            ['label' => '50000', 'value' => 50000],
            ['label' => '100000', 'value' => 100000],
        ];
    }

    public function bets()
    {
        return $this->hasMany(Bet::class);
    }

    public function statements()
    {
        return $this->hasMany(AccountStatement::class);
    }

    public function bankingTransactions()
    {
        return $this->hasMany(BankingTransaction::class);
    }

    public function bankAccounts()
    {
        return $this->hasMany(UserBankAccount::class);
    }

    public function multiMarkets()
    {
        return $this->hasMany(UserMultiMarket::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function passwordHistories()
    {
        return $this->hasMany(PasswordHistory::class);
    }
}
