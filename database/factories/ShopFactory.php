<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shop>
 */
class ShopFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company,
            'address' => $this->faker->address,
            'phone' => $this->faker->phoneNumber,
            'description' => $this->faker->sentence,
            'image_url' => $this->faker->imageUrl(640, 480, 'business'),
            'is_open' => $this->faker->boolean,
            'approve_status' => $this->faker->randomElement(['P', 'A', 'R']), // P = Pending, A = Approved, R = Rejected
            'latitude' => $this->faker->latitude(-90, 90),
            'longitude' => $this->faker->longitude(-180, 180),
            'user_id' => $this->faker->randomElement(User::all()->pluck('id')->toArray()),
        ];
    }
}
