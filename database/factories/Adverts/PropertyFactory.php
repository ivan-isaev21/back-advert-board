<?php

namespace Database\Factories\Adverts;

use App\Models\Adverts\Property;
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
        $frontendType = fake()->randomElement(Property::getAvailableFrontendTypes());
        $variants = (in_array($frontendType, Property::SELECT_FRONTEND_TYPES)
            or in_array($frontendType, Property::MULTISELECT_FRONTEND_TYPES)) ? [1 => 'one',  2 => 'two', 3 => 'three']
            : null;

        return [
            'name' => fake()->unique()->name,
            'slug' => fake()->unique()->slug(2),
            'frontend_type' =>  $frontendType,
            'required' => fake()->boolean(),
            'filterable' => fake()->boolean(),
            'variants' => $variants
        ];
    }
}
