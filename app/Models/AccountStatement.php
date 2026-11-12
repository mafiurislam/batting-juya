<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountStatement extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'credit' => 'float',
            'debit' => 'float',
            'balance' => 'float',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

