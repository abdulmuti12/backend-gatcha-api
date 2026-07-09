<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\InsufficientCoinException;
use App\Exceptions\InvalidGachaEventException;
use App\Http\Controllers\Controller;
use App\Http\Resources\GachaEventResource;
use App\Http\Resources\GachaHistoryResource;
use App\Http\Resources\UserResource;
use App\Models\GachaEvent;
use App\Services\GachaService;
use Illuminate\Support\Facades\Auth;

class GachaController extends Controller
{
    public function __construct(private readonly GachaService $gachaService)
    {
    }

    /**
     * Daftar event gacha yang sedang aktif dan bisa ditarik user.
     */
    public function index()
    {
        $events = GachaEvent::query()
            ->where('is_active', true)
            ->with('items')
            ->latest()
            ->get();

        return GachaEventResource::collection($events);
    }

    public function show(GachaEvent $event)
    {
        return new GachaEventResource($event->load('items'));
    }

    /**
     * Eksekusi 1x tarikan gacha pada sebuah event.
     */
    public function pull(GachaEvent $event)
    {
        $user = Auth::guard('api')->user();

        try {
            $history = $this->gachaService->draw($user, $event);
        } catch (InsufficientCoinException|InvalidGachaEventException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $user->refresh();

        return response()->json([
            'message' => 'Gacha berhasil ditarik.',
            'result' => new GachaHistoryResource($history->load(['event', 'item'])),
            'remaining_coins' => $user->coins,
        ]);
    }
}
