<?php

namespace Database\Factories;

use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Queue>
 */
class QueueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Table ' . $this->faker->randomElement(['A', 'B', 'C']) . '(' . $this->faker->numberBetween(1, 100) . ')',
            'description' => 'โต๊ะ ' . $this->faker->numberBetween(4, 20) . ' คน ' . $this->faker->randomElement(['สอง', 'สี่', 'หก', 'แปด', 'สิบ']) . 'เตา',
            'is_available' => $this->faker->boolean(80), // 80% ที่จะเป็น true
            'tag' => $this->faker->regexify('[A-Z]{1,2}'),
            'shop_id' => $this->faker->unique()->randomElement(Shop::all()->pluck('id')->toArray()), // กำหนดให้ shop_id อยู่ในช่วง 1-10
        ];
    }
}
