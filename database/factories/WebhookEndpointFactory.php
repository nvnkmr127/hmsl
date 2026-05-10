<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WebhookEndpoint>
 */
class WebhookEndpointFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company . ' Webhook',
            'url' => $this->faker->url,
            'secret' => \Illuminate\Support\Str::random(32),
            'events' => ['patient.registered', 'bill.paid'],
            'is_active' => true,
            'timeout_seconds' => 30,
            'api_version' => 'v1',
            'consecutive_failures' => 0,
            'created_by' => 1,
        ];
    }
}
