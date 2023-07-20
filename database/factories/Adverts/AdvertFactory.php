<?php

namespace Database\Factories\Adverts;

use App\Models\Adverts\Advert;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class AdvertFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement([Advert::STATUS_ACTIVE, Advert::STATUS_CLOSED, Advert::STATUS_DRAFT, Advert::STATUS_MODERATION]);
        $rejectReason = null;
        if ($status == Advert::STATUS_DRAFT) {
            $rejectReason = fake()->randomElement([fake()->text(100), null]);
        }

        $publishedAt = fake()->dateTimeBetween('-1 year', 'now');
        $expiresAt = fake()->dateTimeBetween($publishedAt, '+1 month');

        return [
            'category_id' => null,
            'user_id' => null,
            'country_id' => null,
            'division_id' => null,
            'city_id' => null,
            'latitude' => fake()->latitude(44, 52),
            'longitude' => fake()->longitude(22, 39),
            'title' => fake()->word(),
            'content' => fake()->realText(200),
            'status' => $status,
            'reject_reason' => $rejectReason,
            'published_at' => $publishedAt,
            'expires_at' => $expiresAt,
        ];
    }
}
