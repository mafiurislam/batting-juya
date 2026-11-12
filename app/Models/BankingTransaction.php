<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class BankingTransaction extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'charges' => 'float',
            'final_amount' => 'float',
            'bank_details_json' => 'array',
            'processed_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getReceiptUrlAttribute(): ?string
    {
        if (!$this->receipt_path) {
            return null;
        }
        return Storage::disk('public')->url($this->receipt_path);
    }
}

