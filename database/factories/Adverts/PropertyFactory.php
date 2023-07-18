<?php

namespace Database\Factories\Adverts;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class PropertyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => null,
            'name' => fake()->unique()->name,
            'slug' => fake()->unique()->slug(2),
            'frontend_type' => fake()->randomElement(['checkbox', 'radio', 'text']),
            'required' => fake()->boolean(),
            'filterable' => fake()->boolean()
        ];
    }
}
