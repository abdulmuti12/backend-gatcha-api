<?php

namespace App\Services;

use App\Exceptions\InsufficientCoinException;
use App\Exceptions\InvalidGachaEventException;
use App\Models\GachaEvent;
use App\Models\GachaHistory;
use App\Models\GachaItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GachaService
{
    /** Total drop rate basis points harus tepat 10000 (= 100%) */
    private const TOTAL_DROP_RATE_BP = 10000;

    /**

     * @throws InvalidGachaEventException
     * @throws InsufficientCoinException
     */
    public function draw(User $user, GachaEvent $event): GachaHistory
    {
        return DB::transaction(function () use ($user, $event) {
            // Lock row user supaya saldo tidak bisa "dobel pakai" jika ada
            // request bersamaan (concurrent) untuk user yang sama.
            /** @var User $lockedUser */
            $lockedUser = User::query()
                ->whereKey($user->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $lockedEvent = GachaEvent::query()
                ->whereKey($event->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $items = GachaItem::query()
                ->where('gacha_event_id', $lockedEvent->id)
                ->orderBy('id')
                ->get();

            $this->assertEventIsDrawable($lockedEvent, $items);
            $this->assertUserHasEnoughCoins($lockedUser, $lockedEvent->cost_per_pull);

            $selectedItem = $this->pickWeightedRandomItem($items);

            $lockedUser->decrement('coins', $lockedEvent->cost_per_pull);

            return GachaHistory::create([
                'user_id' => $lockedUser->id,
                'gacha_event_id' => $lockedEvent->id,
                'gacha_item_id' => $selectedItem->id,
                'coins_spent' => $lockedEvent->cost_per_pull,
                'drop_rate_bp_snapshot' => $selectedItem->drop_rate_bp,
            ]);
        });
    }

    private function assertEventIsDrawable(GachaEvent $event, \Illuminate\Support\Collection $items): void
    {
        if (! $event->is_active) {
            throw new InvalidGachaEventException('Event gacha ini sedang tidak aktif.');
        }

        if ($items->isEmpty()) {
            throw new InvalidGachaEventException('Event gacha ini belum memiliki item.');
        }

        $totalBp = (int) $items->sum('drop_rate_bp');

        if ($totalBp !== self::TOTAL_DROP_RATE_BP) {
            throw new InvalidGachaEventException('Konfigurasi drop rate event tidak valid (total harus 100%).');
        }
    }

    private function assertUserHasEnoughCoins(User $user, int $costPerPull): void
    {
        if ($user->coins < $costPerPull) {
            throw new InsufficientCoinException(
                "Koin tidak cukup. Dibutuhkan {$costPerPull} koin, sisa koin Anda {$user->coins}."
            );
        }
    }


    private function pickWeightedRandomItem(\Illuminate\Support\Collection $items): GachaItem
    {
        $totalBp = (int) $items->sum('drop_rate_bp');

        // random_int = cryptographically secure, tidak bisa ditebak/diprediksi user
        $roll = random_int(0, $totalBp - 1);

        $cumulative = 0;
        foreach ($items as $item) {
            $cumulative += $item->drop_rate_bp;
            if ($roll < $cumulative) {
                return $item;
            }
        }

        return $items->last();
    }
}
