<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sport extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'popular' => 'boolean',
            'highlight' => 'boolean',
            'others' => 'boolean',
            'status' => 'boolean',
        ];
    }
}

