<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\GachaEvent;
use App\Models\GachaHistory;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{

    public function stats(): JsonResponse
    {
        $totalUsers = User::count();
        $totalEvents = GachaEvent::count();

        // Top 3 event paling sering di-pull dihitung dari gacha_histories.
        $topEvents = GachaEvent::query()
            ->select('gacha_events.id', 'gacha_events.name', 'gacha_events.is_active')
            ->selectRaw('COUNT(gacha_histories.id) AS pulls_count')
            ->leftJoin('gacha_histories', 'gacha_events.id', '=', 'gacha_histories.gacha_event_id')
            ->groupBy('gacha_events.id', 'gacha_events.name', 'gacha_events.is_active')
            ->orderByDesc('pulls_count')
            ->orderByDesc('gacha_events.id')
            ->limit(3)
            ->get()
            ->map(fn ($event) => [
                'id' => (int) $event->id,
                'name' => $event->name,
                'is_active' => (bool) $event->is_active,
                'pulls' => (int) $event->pulls_count,
            ])
            ->all();

        return response()->json([
            'total_users' => $totalUsers,
            'total_events' => $totalEvents,
            'top_events' => $topEvents,
        ]);
    }
}
