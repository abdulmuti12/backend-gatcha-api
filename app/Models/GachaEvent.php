<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GachaEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'cost_per_pull',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(GachaItem::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(GachaHistory::class);
    }

    /**
     * Total drop rate (basis points) yang sudah dikonfigurasi untuk event ini.
     * Harus selalu = 10000 (100%) agar event valid ditarik.
     */
    public function totalDropRateBp(): int
    {
        return (int) $this->items()->sum('drop_rate_bp');
    }
}
