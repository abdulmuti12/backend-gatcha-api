<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\GachaHistoryResource;
use App\Models\GachaHistory;
use Illuminate\Http\Request;

class GachaHistoryController extends Controller
{
    /**
     * Riwayat gacha seluruh user, bisa difilter per event untuk keperluan
     * audit / monitoring drop rate aktual vs konfigurasi.
     */
    public function index(Request $request)
    {
        $histories = GachaHistory::query()
            ->with(['user:id,name,email', 'event', 'item'])
            ->when($request->filled('gacha_event_id'), fn ($q) => $q->where('gacha_event_id', $request->integer('gacha_event_id')))
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->integer('user_id')))
            ->latest()
            ->paginate($request->integer('per_page', 20));

        return GachaHistoryResource::collection($histories);
    }
}
