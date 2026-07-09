<?php

namespace App\Services;

use App\Exceptions\InvalidGachaEventException;
use App\Models\GachaEvent;
use App\Models\GachaItem;
use Illuminate\Support\Facades\DB;

class GachaEventAdminService
{
    private const TOTAL_DROP_RATE_BP = 10000;


    public function createEventWithItems(array $eventData, array $items): GachaEvent
    {
        $this->assertTotalDropRateIsValid($items);

        return DB::transaction(function () use ($eventData, $items) {
            $event = GachaEvent::create($eventData);

            foreach ($items as $item) {
                $event->items()->create($item);
            }

            return $event->load('items');
        });
    }


    public function replaceItems(GachaEvent $event, array $items): GachaEvent
    {
        $this->assertTotalDropRateIsValid($items);

        return DB::transaction(function () use ($event, $items) {
            $event->items()->delete();

            foreach ($items as $item) {
                $event->items()->create($item);
            }

            return $event->load('items');
        });
    }

    private function assertTotalDropRateIsValid(array $items): void
    {
        $total = array_sum(array_column($items, 'drop_rate_bp'));

        if ($total !== self::TOTAL_DROP_RATE_BP) {
            $percent = $total / 100;
            throw new InvalidGachaEventException(
                "Total drop rate harus tepat 100%, saat ini {$percent}%."
            );
        }
    }
}
