<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $guarded = ['id'];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        try {
            $all = Cache::remember('winbook24_site_settings', 120, function () {
                return static::pluck('value', 'key')->toArray();
            });
            return array_key_exists($key, $all) ? $all[$key] : $default;
        } catch (\Throwable) {
            return $default;
        }
    }

    public static function setValue(string $key, mixed $value, string $group = 'general'): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => is_array($value) ? json_encode($value) : (string) $value, 'group' => $group]
        );
        Cache::forget('winbook24_site_settings');
    }
}

