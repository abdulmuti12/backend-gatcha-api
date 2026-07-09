<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GachaItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'gacha_event_id',
        'name',
        'rarity',
        'drop_rate_bp',
        'image_url',
    ];

    protected function casts(): array
    {
        return [
            'drop_rate_bp' => 'integer',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(GachaEvent::class, 'gacha_event_id');
    }

    /**
     * Representasi persentase untuk ditampilkan di response (drop_rate_bp / 100).
     */
    public function dropRatePercent(): float
    {
        return $this->drop_rate_bp / 100;
    }
}
