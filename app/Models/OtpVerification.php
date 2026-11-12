<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpVerification extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'provider_response' => 'array',
            'verified_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }
}

