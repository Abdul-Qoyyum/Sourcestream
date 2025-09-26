<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Source;

class SourceFactory extends Factory
{
    protected $model = Source::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'api_name' => $this->faker->unique()->word(),
            'base_url' => $this->faker->url(),
            'is_active' => true,
            'rate_limit' => $this->faker->numberBetween(50, 100),
            'config' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
