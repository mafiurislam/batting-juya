<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CasinoGame extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'popular' => 'boolean',
            'status' => 'boolean',
            'min_bet' => 'float',
            'max_bet' => 'float',
            'rtp' => 'float',
            'config_json' => 'array',
        ];
    }

    public function getDisplayImageAttribute(): string
    {
        if (!empty($this->local_image) && file_exists(public_path(ltrim($this->local_image, '/')))) {
            return $this->local_image;
        }
        return $this->image_url ?: '/assets/brand/banner-1.jpg';
    }

    public function getLaunchUrlAttribute(): string
    {
        if (in_array($this->event_id, ['99.0030', 'oc_lucky7_a', 'oc_fast_lucky7', 'oc_lucky7_b'], true)
            || $this->game_type === 'lucky7') {
            return route('casino.lucky7', ['eventId' => $this->event_id]);
        }

        if (in_array($this->event_id, ['99.0018', '99.0020'], true)
            || in_array(strtoupper((string) $this->event_name), ['DRAGON TIGER', '1 DAY DRAGON TIGER'], true)) {
            return route('casino.room', ['eventId' => $this->event_id]);
        }

        if (in_array($this->event_id, ['99.0019', 'oc_fast_dt'], true)
            || $this->game_type === 'dragontiger'
            || stripos((string) $this->event_name, 'dragon tiger') !== false) {
            return route('casino.teenpatti', ['eventId' => $this->event_id]);
        }

        if (in_array($this->event_id, ['99.0010', '99.0013', '99.0014', '99.0016', 'oc_point_tp', 'oc_dtl_tp'], true)
            || stripos((string) $this->event_name, 'teenpatti') !== false
            || stripos((string) $this->event_name, 'teen patti') !== false) {
            return route('casino.teenpatti', ['eventId' => $this->event_id]);
        }

        if ($this->event_id === '88.0022' || strtolower((string) $this->event_name) === 'vimaan') {
            return route('vimaan', ['eventId' => '88.0022']);
        }

        if ($this->event_id === '88.0023' || strtolower((string) $this->event_name) === 'balloon') {
            return route('casino.balloon', ['eventId' => '88.0023']);
        }

        if ($this->event_id === '88.0019' || stripos((string) $this->event_name, 'lucky 0 to 9') !== false) {
            return route('casino.lucky0to9', ['eventId' => '88.0019']);
        }

        if ($this->event_id === '88.0021' || stripos((string) $this->event_name, 'head') !== false) {
            return route('casino.headntails', ['eventId' => '88.0021']);
        }

        return route('casino.play', ['eventId' => $this->event_id]);
    }
}

