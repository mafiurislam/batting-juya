<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bet extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'odds' => 'float',
            'run_line' => 'float',
            'stake' => 'float',
            'potential_profit' => 'float',
            'liability' => 'float',
            'profit_loss' => 'float',
            'game_details_json' => 'array',
            'settled_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

