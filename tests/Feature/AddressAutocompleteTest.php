<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AddressAutocompleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_autocomplete_requires_at_least_2_chars(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('receptionist');

        Cache::flush();

        Http::fake();

        $this->actingAs($user)
            ->get(route('counter.address.autocomplete', ['q' => 'N']))
            ->assertOk()
            ->assertExactJson([]);

        Http::assertNothingSent();
    }

    public function test_autocomplete_filters_to_telangana_and_uses_bounded_viewbox(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('receptionist');

        Cache::flush();

        config()->set('services.mappls.client_id', 'test-client-id');
        config()->set('services.mappls.client_secret', 'test-client-secret');
        config()->set('services.mappls.autosuggest_bounds_filter', 'bounds: 19.95,77.15; 15.80,81.05');
        config()->set('services.mappls.autosuggest_tokenize_address', false);

        Http::fake([
            'https://outpost.mappls.com/api/security/oauth/token' => Http::response([
                'access_token' => 'test-access-token',
                'token_type' => 'bearer',
                'expires_in' => 3600,
            ], 200),
            'https://atlas.mappls.com/api/places/search/json*' => Http::response([
                'suggestedLocations' => [
                    [
                        'type' => 'VLG',
                        'placeAddress' => 'Nalgonda, Telangana, 508001',
                        'eLoc' => 'AAA111',
                        'placeName' => 'Nalgonda',
                        'orderIndex' => 1,
                    ],
                    [
                        'type' => 'CITY',
                        'placeAddress' => 'Hyderabad, Telangana, 500001',
                        'eLoc' => 'BBB222',
                        'placeName' => 'Hyderabad',
                        'orderIndex' => 2,
                    ],
                    [
                        'type' => 'SLC',
                        'placeAddress' => 'Hyderabad, Telangana, 500072',
                        'eLoc' => 'CCC333',
                        'placeName' => 'Kukatpally',
                        'orderIndex' => 3,
                    ],
                ],
            ], 200),
        ]);

        $this->actingAs($user)
            ->get(route('counter.address.autocomplete', ['q' => 'Nalgonda']))
            ->assertOk()
            ->assertJsonCount(3)
            ->assertJsonFragment(['place_id' => 'AAA111'])
            ->assertJsonFragment(['place_id' => 'BBB222'])
            ->assertJsonFragment(['place_id' => 'CCC333'])
            ->assertJsonFragment(['address' => 'Kukatpally, Hyderabad, Telangana, 500072'])
            ->assertJsonFragment(['state' => 'Telangana']);

        Http::assertSent(function ($request) {
            $data = $request->data();

            return $request->url() === 'https://outpost.mappls.com/api/security/oauth/token'
                && ($data['grant_type'] ?? null) === 'client_credentials'
                && ($data['client_id'] ?? null) === 'test-client-id'
                && ($data['client_secret'] ?? null) === 'test-client-secret';
        });

        Http::assertSent(function ($request) {
            $data = $request->data();

            return str_starts_with($request->url(), 'https://atlas.mappls.com/api/places/search/json')
                && ($request->header('Authorization')[0] ?? null) === 'bearer test-access-token'
                && ($data['query'] ?? null) === 'Nalgonda'
                && ($data['region'] ?? null) === 'IND'
                && ($data['filter'] ?? null) === 'bounds: 19.95,77.15; 15.80,81.05';
        });
    }
}
