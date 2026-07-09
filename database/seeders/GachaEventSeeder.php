<?php

namespace Database\Seeders;

use App\Models\GachaEvent;
use Illuminate\Database\Seeder;

class GachaEventSeeder extends Seeder
{
    public function run(): void
    {
        $event = GachaEvent::updateOrCreate(
            ['name' => 'Summer Gacha Event'],
            [
                'description' => 'Event gacha musim panas dengan hadiah eksklusif.',
                'cost_per_pull' => 10,
                'is_active' => true,
                'starts_at' => now(),
                'ends_at' => now()->addMonth(),
            ]
        );

        $event->items()->delete();

        $event->items()->createMany([
            [
                'name' => 'Excalibur Legendary Sword',
                'rarity' => 'legendary',
                'drop_rate_bp' => 100, // 1%
                'image_url' => null,
            ],
            [
                'name' => 'Rare Mystic Shield',
                'rarity' => 'rare',
                'drop_rate_bp' => 1900, // 19%
                'image_url' => null,
            ],
            [
                'name' => 'Common Health Potion',
                'rarity' => 'common',
                'drop_rate_bp' => 8000, // 80%
                'image_url' => null,
            ],
        ]);
    }
}
