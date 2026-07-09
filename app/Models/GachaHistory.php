<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GachaHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'gacha_event_id',
        'gacha_item_id',
        'coins_spent',
        'drop_rate_bp_snapshot',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(GachaEvent::class, 'gacha_event_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(GachaItem::class, 'gacha_item_id');
    }
}
