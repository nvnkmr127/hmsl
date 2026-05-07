<?php

namespace App\Http\Controllers\Counter;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use App\Models\Patient;

class AddressAutocompleteController extends Controller
{
    public function __invoke(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $maxLen = (int) config('services.mappls.autosuggest_max_query_length', 100);
        if ($maxLen > 0 && mb_strlen($q) > $maxLen) {
            return response()->json([]);
        }

        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $cacheKey = 'address_autocomplete:v6:google:' . md5(mb_strtolower($q));

        $items = Cache::remember($cacheKey, now()->addHours(6), function () use ($q) {
            $localResults = Patient::where('address', 'like', "%{$q}%")
                ->orWhere('city', 'like', "%{$q}%")
                ->limit(5)
                ->get()
                ->toBase()
                ->map(function($p) {
                    return [
                        'place_id' => 'local_' . $p->id,
                        'label' => $p->address,
                        'subLabel' => trim("{$p->city}, {$p->state}"),
                        'address' => $p->address,
                        'city' => $p->city,
                        'state' => $p->state,
                        'pincode' => $p->pincode,
                        'source' => 'local'
                    ];
                });

            $apiKey = (string) config('services.google.maps_api_key');
            if ($apiKey === '') {
                return $localResults->all();
            }

            $response = Http::timeout(5)
                ->get('https://maps.googleapis.com/maps/api/place/autocomplete/json', [
                    'input' => $q,
                    'key' => $apiKey,
                    'components' => 'country:in',
                    'location' => '18.1124,79.0193', // Center of Telangana
                    'radius' => '250000',           // 250km radius to cover Telangana
                    'strictbounds' => 'true',
                    'types' => 'geocode',           // Better for villages, towns and specific addresses
                    'language' => 'en',
                ]);

            if (!$response->successful()) {
                return $localResults->all();
            }

            $payload = (array) $response->json();
            $predictions = collect($payload['predictions'] ?? []);

            $googleResults = $predictions
                ->map(function ($row) {
                    $description = (string) ($row['description'] ?? '');
                    $mainText = (string) ($row['structured_formatting']['main_text'] ?? $description);
                    $placeId = (string) ($row['place_id'] ?? '');

                    $parsed = $this->parsePlaceAddress($description);

                    return [
                        'place_id' => $placeId,
                        'label' => $mainText,
                        'subLabel' => $description,
                        'address' => $description,
                        'city' => $parsed['city'],
                        'state' => $parsed['state'],
                        'pincode' => $parsed['pincode'],
                        'source' => 'google'
                    ];
                });

            return $localResults->merge($googleResults)
                ->unique('address')
                ->take(6)
                ->values()
                ->all();
        });

        return response()->json($items);
    }

    public function details(Request $request)
    {
        $placeId = (string) $request->query('place_id', '');
        if ($placeId === '' || str_starts_with($placeId, 'local_')) {
            return response()->json([]);
        }

        $apiKey = (string) config('services.google.maps_api_key');
        if ($apiKey === '') {
            return response()->json([]);
        }

        $cacheKey = 'address_details:v1:' . md5($placeId);

        $details = Cache::remember($cacheKey, now()->addDays(7), function () use ($placeId, $apiKey) {
            $response = Http::timeout(5)
                ->get('https://maps.googleapis.com/maps/api/place/details/json', [
                    'place_id' => $placeId,
                    'key' => $apiKey,
                    'fields' => 'address_component,formatted_address',
                    'language' => 'en',
                ]);

            if (!$response->successful()) {
                return null;
            }

            $payload = (array) $response->json();
            $result = $payload['result'] ?? null;
            if (!$result) return null;

            $components = collect($result['address_components'] ?? []);
            
            $pincode = null;
            foreach ($components as $component) {
                if (in_array('postal_code', $component['types'])) {
                    $pincode = $component['long_name'];
                    break;
                }
            }

            $city = null;
            foreach ($components as $component) {
                if (in_array('locality', $component['types'])) {
                    $city = $component['long_name'];
                    break;
                }
            }
            if (!$city) {
                foreach ($components as $component) {
                    if (in_array('administrative_area_level_2', $component['types'])) {
                        $city = $component['long_name'];
                        break;
                    }
                }
            }

            $state = null;
            foreach ($components as $component) {
                if (in_array('administrative_area_level_1', $component['types'])) {
                    $state = $component['long_name'];
                    break;
                }
            }

            return [
                'address' => $result['formatted_address'] ?? '',
                'city' => $city,
                'state' => $state,
                'pincode' => $pincode,
            ];
        });

        return response()->json($details);
    }

    private function parsePlaceAddress(string $placeAddress): array
    {
        $placeAddress = trim($placeAddress);
        if ($placeAddress === '') {
            return ['colony' => null, 'city' => null, 'state' => null, 'pincode' => null];
        }

        $pincode = null;
        if (preg_match('/\b(\d{6})\b/', $placeAddress, $m)) {
            $pincode = $m[1];
        }

        $parts = array_values(array_filter(array_map('trim', explode(',', $placeAddress)), fn ($v) => $v !== ''));
        $n = count($parts);

        $state = null;
        $city = null;
        $colony = null;

        if ($n >= 2) {
            $last = $parts[$n - 1];
            $secondLast = $parts[$n - 2];

            if (preg_match('/^\d{6}$/', $last)) {
                $state = $secondLast;
                if ($n >= 3) {
                    $city = $parts[$n - 3];
                    if ($n >= 4) {
                        $colony = $parts[$n - 4];
                    }
                }
            } else {
                $state = $last;
                if ($n >= 2) {
                    $city = $secondLast;
                    if ($n >= 3) {
                        $colony = $parts[$n - 3];
                    }
                }
            }
        }

        return [
            'colony' => $colony,
            'city' => $city,
            'state' => $state,
            'pincode' => $pincode,
        ];
    }
}
