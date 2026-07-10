<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GachaHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ]),
            'event' => $this->whenLoaded('event', fn () => [
                'id' => $this->event->id,
                'name' => $this->event->name,
                'is_active' => $this->event->is_active,
            ]),
            'item' => [
                'name' => $this->whenLoaded('item', fn () => $this->item->name),
                'rarity' => $this->whenLoaded('item', fn () => $this->item->rarity),
            ],
            'coins_spent' => $this->coins_spent,
            'drop_rate_percent_at_draw' => $this->drop_rate_bp_snapshot / 100,
            'drawn_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
