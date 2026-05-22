<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleDistanceMatrixService
{
    private const ENDPOINT = 'https://maps.googleapis.com/maps/api/distancematrix/json';

    public function __construct() {}

    /**
     * Returns the driving distance in kilometres between origin and
     * destination, or null when Google can't resolve a route (bad input,
     * quota exhausted, network failure, etc.). Results are cached.
     *
     * Origin/destination can be either a "lat,lng" pair or a free-text
     * address — Google handles both.
     */
    public function distanceKm(string $origin, string $destination): ?float
    {
        $origin = trim($origin);
        $destination = trim($destination);

        if ($origin === '' || $destination === '') {
            return null;
        }

        $cacheKey = $this->cacheKey($origin, $destination);
        $ttl = (int) config('services.google_maps.distance_cache_ttl', 60 * 60 * 24 * 30);

        return Cache::remember($cacheKey, $ttl, function () use ($origin, $destination) {
            return $this->fetchFromGoogle($origin, $destination);
        });
    }

    private function fetchFromGoogle(string $origin, string $destination): ?float
    {
        $apiKey = config('services.google_maps.api_key');
        if (! $apiKey) {
            Log::warning('GoogleDistanceMatrixService: missing GOOGLE_MAPS_API_KEY.');

            return null;
        }

        try {
            $response = Http::timeout(8)->get(self::ENDPOINT, [
                'origins' => $origin,
                'destinations' => $destination,
                'units' => 'metric',
                'mode' => 'driving',
                'region' => 'zw',
                'key' => $apiKey,
            ]);
        } catch (\Throwable $e) {
            Log::warning('GoogleDistanceMatrixService HTTP failure', ['error' => $e->getMessage()]);

            return null;
        }

        if (! $response->successful()) {
            Log::warning('GoogleDistanceMatrixService non-2xx', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        }

        $payload = $response->json();
        if (($payload['status'] ?? '') !== 'OK') {
            Log::warning('GoogleDistanceMatrixService API status', ['payload' => $payload]);

            return null;
        }

        $element = $payload['rows'][0]['elements'][0] ?? null;
        if ($element === null || ($element['status'] ?? '') !== 'OK') {
            Log::warning('GoogleDistanceMatrixService element status', ['payload' => $payload]);

            return null;
        }

        $meters = (int) ($element['distance']['value'] ?? 0);
        if ($meters <= 0) {
            return null;
        }

        return round($meters / 1000, 2);
    }

    private function cacheKey(string $origin, string $destination): string
    {
        return 'gdm:'.md5(mb_strtolower($origin).'|'.mb_strtolower($destination));
    }
}
