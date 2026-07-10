<?php

namespace App\Http\Controllers\Api\Admin;

use App\Exceptions\InvalidGachaEventException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreGachaEventRequest;
use App\Http\Requests\Admin\UpdateGachaEventRequest;
use App\Http\Requests\Admin\UpdateGachaItemsRequest;
use App\Http\Resources\GachaEventResource;
use App\Models\GachaEvent;
use App\Services\GachaEventAdminService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class GachaEventController extends Controller
{
    public function __construct(private readonly GachaEventAdminService $adminService)
    {
    }

    public function index()
    {
        $events = GachaEvent::query()->with('items')->latest()->paginate(15);

        return GachaEventResource::collection($events);
    }

    public function store(StoreGachaEventRequest $request)
    {
        $data = $request->validated();
        $items = $this->normalizeItems($data['items']);
        unset($data['items']);

        try {
            $event = $this->adminService->createEventWithItems($data, $items);
        } catch (InvalidGachaEventException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return (new GachaEventResource($event))
            ->response()
            ->setStatusCode(201);
    }

    public function show($id)
    {
        $event = GachaEvent::with('items')->find($id);

        if (!$event) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event gacha tidak ditemukan.'
            ], 404);
        }

        return new GachaEventResource($event);
    }

    /**
     * Update event + items sekaligus (patch/partial update).
     * Field opsional: name, description, cost_per_pull, is_active, starts_at, ends_at, items.
     */
    public function update(UpdateGachaEventRequest $request, GachaEvent $gacha_event)
{
    $event = $gacha_event;
    $data = $request->validated();
    $itemsInput = $data['items'] ?? null;
    unset($data['items']);



    Log::info('GachaEvent update: request received', [
        'event_id' => $event->id,
        'fields' => array_keys($data),
        'data' => $data,
        'items_count' => is_array($itemsInput) ? count($itemsInput) : null,
    ]);

    if (! empty($data)) {
        $event->update($data);
        Log::info('GachaEvent update: base fields updated', [
            'event_id' => $event->id,
            'updated_fields' => array_keys($data),
        ]);
    }

    if ($itemsInput !== null) {
        try {
            $normalized = $this->normalizeItems($itemsInput);
            $event = $this->adminService->replaceItems($event, $normalized);

            Log::info('GachaEvent update: items replaced successfully', [
                'event_id' => $event->id,
                'items_count' => count($normalized),
            ]);
        } catch (InvalidGachaEventException $e) {
            Log::error('GachaEvent update: failed to replace items', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
                'items_input' => $itemsInput,
            ]);

            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    $event->refresh();

    Log::info('GachaEvent update: success', [
        'event_id' => $event->id,
    ]);

    return new GachaEventResource($event->load('items'));
}
    /**
     * Ganti seluruh daftar item + drop rate pada event ini (wajib total 100%).
     */
    public function updateItems(UpdateGachaItemsRequest $request, GachaEvent $gacha_event)
    {
        $event = $gacha_event;
        $items = $this->normalizeItems($request->validated('items'));

        try {
            $event = $this->adminService->replaceItems($event, $items);
        } catch (InvalidGachaEventException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return new GachaEventResource($event);
    }


   public function destroy($id)
    {
        if (! GachaEvent::where('id', $id)->delete()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event gacha tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Event gacha berhasil dihapus.'
        ]);
    }
    /**
     * Konversi drop_rate (persen, misal 1.5) dari request menjadi drop_rate_bp (integer, 150)
     * agar disimpan presisi tanpa floating point di database.
     */
    private function normalizeItems(array $items): array
    {
        return array_map(fn (array $item) => [
            'name' => $item['name'],
            'rarity' => $item['rarity'],
            'drop_rate_bp' => (int) round($item['drop_rate'] * 100),
            'image_url' => $item['image_url'] ?? null,
        ], $items);
    }
}
