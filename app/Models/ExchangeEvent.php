<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeEvent extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'in_play' => 'boolean',
            'is_fancy' => 'boolean',
            'is_bookmakers' => 'boolean',
            'is_sportsbook' => 'boolean',
            'is_streaming' => 'boolean',
            'is_virtual' => 'boolean',
            'total_matched' => 'float',
            'runners_json' => 'array',
            'bookmaker_json' => 'array',
            'fancy_json' => 'array',
            'sportsbook_json' => 'array',
            'score_json' => 'array',
        ];
    }
}

