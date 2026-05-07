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

        $cacheKey = 'address_autocomplete:v7:' . md5(mb_strtolower($q));

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
            if ($apiKey !== '') {
                $response = Http::timeout(5)
                    ->get('https://maps.googleapis.com/maps/api/place/autocomplete/json', [
                        'input' => $q,
                        'key' => $apiKey,
                        'components' => 'country:in',
                        'location' => '18.1124,79.0193', // Center of Telangana
                        'radius' => '250000',           // 250km radius to cover Telangana
                        'strictbounds' => 'true',
                        'types' => 'geocode',
                        'language' => 'en',
                    ]);

                if ($response->successful()) {
                    $payload = (array) $response->json();
                    $predictions = collect($payload['predictions'] ?? []);

                    $googleResults = $predictions->map(function ($row) {
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

                    return $localResults->merge($googleResults)->unique('address')->take(6)->values()->all();
                }
            }

            // Fallback to Mappls if Google is not set or failed
            $mapplsHeader = $this->getMapplsAuthHeader();
            if ($mapplsHeader) {
                $response = Http::timeout(5)
                    ->withHeaders(['Authorization' => $mapplsHeader])
                    ->get('https://atlas.mappls.com/api/places/search/json', [
                        'query' => $q,
                        'region' => 'IND',
                        'filter' => config('services.mappls.autosuggest_bounds_filter'),
                        'tokenize_address' => config('services.mappls.autosuggest_tokenize_address', false),
                    ]);

                if ($response->successful()) {
                    $payload = (array) $response->json();
                    $mapplsResults = collect($payload['suggestedLocations'] ?? [])->map(function ($row) {
                        $placeName = (string) ($row['placeName'] ?? '');
                        $placeAddress = (string) ($row['placeAddress'] ?? '');
                        
                        $parsed = $this->parsePlaceAddress($placeAddress);
                        $label = $placeName !== '' ? $placeName : $placeAddress;

                        return [
                            'place_id' => $row['eLoc'] ?? $row['mapplsPin'] ?? md5($placeAddress),
                            'label' => $label,
                            'subLabel' => $placeAddress,
                            'address' => $placeAddress,
                            'city' => $parsed['city'],
                            'state' => $parsed['state'],
                            'pincode' => $parsed['pincode'],
                            'source' => 'mappls'
                        ];
                    });

                    return $localResults->merge($mapplsResults)->unique('address')->take(6)->values()->all();
                }
            }

            return $localResults->all();
        });

        return response()->json($items);
    }

    private function getMapplsAuthHeader(): ?string
    {
        $clientId = (string) config('services.mappls.client_id');
        $clientSecret = (string) config('services.mappls.client_secret');

        if ($clientId === '' || $clientSecret === '') {
            return null;
        }

        return Cache::remember('mappls_auth_token', 3500, function () use ($clientId, $clientSecret) {
            $response = Http::asForm()->post('https://outpost.mappls.com/api/security/oauth/token', [
                'grant_type' => 'client_credentials',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return ($data['token_type'] ?? 'bearer') . ' ' . $data['access_token'];
            }

            return null;
        });
    }

    public function details(Request $request)
    {
        $placeId = (string) $request->query('place_id', '');
        if ($placeId === '' || str_starts_with($placeId, 'local_')) {
            return response()->json([]);
        }

        // For Mappls, we already have details in the search result, so we return empty or basic
        if (strlen($placeId) > 20 && !str_contains($placeId, '-')) { // Likely Google Place ID
             $apiKey = (string) config('services.google.maps_api_key');
             if ($apiKey === '') return response()->json([]);

             $cacheKey = 'address_details:v1:' . md5($placeId);
             $details = Cache::remember($cacheKey, now()->addDays(7), function () use ($placeId, $apiKey) {
                $response = Http::timeout(5)
                    ->get('https://maps.googleapis.com/maps/api/place/details/json', [
                        'place_id' => $placeId,
                        'key' => $apiKey,
                        'fields' => 'address_component,formatted_address',
                        'language' => 'en',
                    ]);

                if (!$response->successful()) return null;

                $payload = (array) $response->json();
                $result = $payload['result'] ?? null;
                if (!$result) return null;

                $components = collect($result['address_components'] ?? []);
                
                $pincode = null;
                $city = null;
                $state = null;

                foreach ($components as $component) {
                    if (in_array('postal_code', $component['types'])) $pincode = $component['long_name'];
                    if (in_array('locality', $component['types'])) $city = $component['long_name'];
                    if (in_array('administrative_area_level_1', $component['types'])) $state = $component['long_name'];
                }

                if (!$city) {
                    foreach ($components as $component) {
                        if (in_array('administrative_area_level_2', $component['types'])) {
                            $city = $component['long_name'];
                            break;
                        }
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

        return response()->json([]);
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
