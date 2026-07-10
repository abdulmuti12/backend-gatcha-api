<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\InsufficientCoinException;
use App\Exceptions\InvalidGachaEventException;
use App\Http\Controllers\Controller;
use App\Http\Resources\GachaEventResource;
use App\Http\Resources\GachaHistoryResource;
use App\Models\GachaEvent;
use App\Services\GachaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

class GachaController extends Controller
{
    /**
     * Maksimum tarikan yang boleh dilakukan satu user dalam {@see self::RATE_LIMIT_DECAY_SECONDS} detik.
     */
    private const RATE_LIMIT_MAX_ATTEMPTS = 10;

    /**
     * Window waktu (detik) untuk rate limiter di atas.
     */
    private const RATE_LIMIT_DECAY_SECONDS = 60;

    /**
     * Lock otomatis dilepas setelah request selesai (atau timeout ini sebagai safety net).
     */
    private const GACHA_PULL_LOCK_TTL_SECONDS = 10;

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
     *
     * Dua layer perlindungan diterapkan untuk mencegah double-pull dan spam pull:
     *
     * 1. {@see RateLimiter} -> Mencegah user melakukan lebih dari
     *    {@see self::RATE_LIMIT_MAX_ATTEMPTS} tarikan dalam
     *    {@see self::RATE_LIMIT_DECAY_SECONDS} detik. Berlaku global per user.
     * 2. {@see Cache::lock()} -> Mutex/semaphore pada level aplikasi. Jika ada 2
     *    request pull dari user yang sama & event yang sama dikirim bersamaan,
     *    hanya 1 yang lolos masuk ke transaksi DB; yang lain langsung ditolak
     *    dengan HTTP 429.
     */
    public function pull(GachaEvent $event): JsonResponse
    {
        $user = Auth::guard('api')->user();

        // ---------- Layer 1: Rate limit (anti-spam) ----------
        $rateLimitKey = sprintf('gacha-pull:user:%d', $user->getKey());

        if (RateLimiter::tooManyAttempts($rateLimitKey, self::RATE_LIMIT_MAX_ATTEMPTS)) {
            $retryAfter = RateLimiter::availableIn($rateLimitKey);

            return response()->json([
                'message' => 'Terlalu banyak percobaan gacha. Silakan tunggu beberapa saat.',
                'retry_after' => $retryAfter,
            ], 429)->header('Retry-After', (string) $retryAfter);
        }

        RateLimiter::hit($rateLimitKey, self::RATE_LIMIT_DECAY_SECONDS);

        // ---------- Layer 2: Distributed mutex lock (anti double-pull) ----------
        // Lock key berisi user_id DAN event_id sehingga user bisa menarik event
        // berbeda secara paralel, tapi tidak bisa menarik event yang sama 2x bersamaan.
        $lockKey = sprintf('gacha-pull-lock:user:%d:event:%d', $user->getKey(), $event->getKey());

        $lock = Cache::lock($lockKey, self::GACHA_PULL_LOCK_TTL_SECONDS);

        // get() non-blocking -> langsung false kalau lock sedang dipegang proses lain
        if (! $lock->get()) {
            return response()->json([
                'message' => 'Tarikan gacha sedang diproses. Harap tunggu sebentar.',
            ], 429);
        }

        try {
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
        } finally {
            // Selalu lepas lock, bahkan jika exception terjadi di luar transaction
            optional($lock)->release();
        }
    }
}