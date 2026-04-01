<?php

namespace App\Http\Controllers\Counter;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AddressAutocompleteController extends Controller
{
    private const ALLOWED_STATE = 'Telangana';

    public function __invoke(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        if (mb_strlen($q) < 3) {
            return response()->json([]);
        }

        $cacheKey = 'address_autocomplete:' . md5(mb_strtolower($q));

        $items = Cache::remember($cacheKey, now()->addHours(6), function () use ($q) {
            $response = Http::timeout(5)
                ->withHeaders([
                    'User-Agent' => config('app.name') . ' (' . config('app.url') . ')',
                    'Accept' => 'application/json',
                ])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $q,
                    'format' => 'jsonv2',
                    'addressdetails' => 1,
                    'countrycodes' => 'in',
                    'limit' => 6,
                ]);

            if (!$response->successful()) {
                return [];
            }

            return collect($response->json())
                ->map(function ($row) {
                    $address = $row['address'] ?? [];

                    $city = $address['city']
                        ?? $address['town']
                        ?? $address['village']
                        ?? $address['county']
                        ?? null;

                    $label = (string) ($row['name'] ?? $row['display_name'] ?? '');
                    $subLabel = (string) ($row['display_name'] ?? '');

                    return [
                        'place_id' => $row['place_id'] ?? ($row['osm_id'] ?? $subLabel),
                        'label' => $label !== '' ? $label : $subLabel,
                        'subLabel' => $subLabel,
                        'address' => $subLabel,
                        'city' => $city,
                        'state' => $address['state'] ?? null,
                        'pincode' => $address['postcode'] ?? null,
                    ];
                })
                ->filter(function ($item) {
                    return mb_strtolower((string) ($item['state'] ?? '')) === mb_strtolower(self::ALLOWED_STATE);
                })
                ->values()
                ->all();
        });

        return response()->json($items);
    }
}
