<?php

namespace Database\Factories;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $active = fake()->boolean();
        $phoneActive = fake()->boolean();

        return [
            'login' => fake()->word() . Str::random(3),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'phone' => fake()->unique()->phoneNumber,
            'phone_verified' => $phoneActive,
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'phone_verify_token' => $phoneActive ? null : Str::uuid(),
            'phone_verify_token_expire' => $phoneActive ? null : Carbon::now()->addSeconds(300),
            'role' => $active ? fake()->randomElement([User::ROLE_USER, User::ROLE_ADMIN]) : User::ROLE_USER,
            'status' => $active ? User::STATUS_ACTIVE : User::STATUS_WAIT,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
