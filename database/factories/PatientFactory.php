<?php

namespace Database\Factories;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientFactory extends Factory
{
    protected $model = Patient::class;

    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'uhid' => 'UHID-' . $this->faker->unique()->numberBetween(1000, 9999),
            'phone' => $this->faker->phoneNumber(),
            'gender' => $this->faker->randomElement(['Male', 'Female', 'Other']),
            'date_of_birth' => $this->faker->date(),
        ];
    }
}
