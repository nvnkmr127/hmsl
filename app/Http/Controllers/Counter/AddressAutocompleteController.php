<?php

namespace App\Http\Controllers\Counter;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AddressAutocompleteController extends Controller
{
    public function __invoke(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $maxLen = (int) config('services.mappls.autosuggest_max_query_length', 45);
        if ($maxLen > 0 && mb_strlen($q) > $maxLen) {
            return response()->json([]);
        }

        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $cacheKey = 'address_autocomplete:v5:mappls:' . md5(mb_strtolower($q));

        $items = Cache::remember($cacheKey, now()->addHours(6), function () use ($q) {
            $authHeader = $this->mapplsAuthHeader();
            if ($authHeader === null) {
                return [];
            }

            $filter = (string) config('services.mappls.autosuggest_bounds_filter', '');
            $tokenizeAddress = (bool) config('services.mappls.autosuggest_tokenize_address', false);

            $response = Http::timeout(5)
                ->withHeaders([
                    'User-Agent' => config('app.name') . ' (' . config('app.url') . ')',
                    'Accept' => 'application/json',
                    'Authorization' => $authHeader,
                ])
                ->get('https://atlas.mappls.com/api/places/search/json', [
                    'query' => $q,
                    'region' => 'IND',
                    'filter' => $filter !== '' ? $filter : null,
                    'tokenizeAddress' => $tokenizeAddress ? '' : null,
                ]);

            if (!$response->successful()) {
                return [];
            }

            $payload = (array) $response->json();
            $rows = collect($payload['suggestedLocations'] ?? [])
                ->merge($payload['userAddedLocations'] ?? []);

            return $rows
                ->map(function ($row) {
                    $placeAddress = (string) ($row['placeAddress'] ?? '');
                    $placeName = (string) ($row['placeName'] ?? '');
                    $addressTokens = (array) ($row['addressTokens'] ?? []);

                    $parsed = $this->parsePlaceAddress($placeAddress);

                    $city = $addressTokens['city']
                        ?? $addressTokens['locality']
                        ?? $addressTokens['village']
                        ?? $addressTokens['subDistrict']
                        ?? $addressTokens['district']
                        ?? $parsed['city']
                        ?? null;

                    $colony = $addressTokens['subLocality']
                        ?? $addressTokens['subSubLocality']
                        ?? $parsed['colony']
                        ?? null;

                    $state = $addressTokens['state'] ?? $parsed['state'] ?? null;
                    $pincode = $addressTokens['pincode'] ?? $parsed['pincode'] ?? null;

                    $label = $placeName !== '' ? $placeName : $placeAddress;
                    $subLabel = $placeAddress;
                    $fullAddress = $placeAddress;
                    if ($placeName !== '' && $placeAddress !== '') {
                        $name = trim($placeName);
                        $addr = trim($placeAddress);
                        if ($name !== '' && $addr !== '' && !str_starts_with(mb_strtolower($addr), mb_strtolower($name . ',')) && mb_strtolower($addr) !== mb_strtolower($name)) {
                            $fullAddress = $name . ', ' . $addr;
                        }
                    }

                    return [
                        'place_id' => $row['eLoc'] ?? ($row['mapplsPin'] ?? $subLabel),
                        'label' => $label,
                        'subLabel' => $subLabel,
                        'address' => $fullAddress,
                        'colony' => $colony,
                        'city' => $city,
                        'state' => $state,
                        'pincode' => $pincode,
                    ];
                })
                ->take(6)
                ->values()
                ->all();
        });

        return response()->json($items);
    }

    private function mapplsAuthHeader(): ?string
    {
        $cacheKey = 'mappls:oauth:auth_header';
        $cached = Cache::get($cacheKey);
        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $clientId = (string) config('services.mappls.client_id');
        $clientSecret = (string) config('services.mappls.client_secret');
        if ($clientId === '' || $clientSecret === '') {
            return null;
        }

        $response = Http::asForm()
            ->timeout(5)
            ->withHeaders([
                'User-Agent' => config('app.name') . ' (' . config('app.url') . ')',
                'Accept' => 'application/json',
            ])
            ->post('https://outpost.mappls.com/api/security/oauth/token', [
                'grant_type' => 'client_credentials',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
            ]);

        if (!$response->successful()) {
            return null;
        }

        $data = (array) $response->json();
        $accessToken = (string) ($data['access_token'] ?? '');
        $tokenType = (string) ($data['token_type'] ?? 'bearer');
        $expiresIn = (int) ($data['expires_in'] ?? 0);

        if ($accessToken === '') {
            return null;
        }

        $headerValue = trim($tokenType . ' ' . $accessToken);
        $ttl = $expiresIn > 120 ? $expiresIn - 60 : 300;
        Cache::put($cacheKey, $headerValue, now()->addSeconds($ttl));

        return $headerValue;
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
