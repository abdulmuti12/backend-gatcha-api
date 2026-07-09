<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GachaHistoryResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function show()
    {
        return new UserResource(Auth::guard('api')->user());
    }

    public function histories(Request $request)
    {
        $query = Auth::guard('api')->user()
            ->gachaHistories()
            ->with(['event', 'item']);

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->whereHas('event', function ($subQuery) use ($search): void {
                    $subQuery->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('item', function ($subQuery) use ($search): void {
                    $subQuery->where('name', 'like', "%{$search}%");
                });
            });
        }

        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $histories = $query->latest()->paginate($request->integer('per_page', 15));

        return GachaHistoryResource::collection($histories);
    }
}
