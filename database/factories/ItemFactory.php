<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['หมูปิ้ง', 'ไก่ย่าง', 'ลูกชิ้นทอด', 'ข้าวเหนียว', 'ส้มตำ']),
            'description' => $this->faker->randomElement(['อร่อยสุด', 'เผ็ดแซ่บ', 'นุ่มละมุน', 'หอมมาก', 'คุ้มค่าที่สุด']),
            'price' => $this->faker->randomFloat(2, 5, 100), // สุ่มราคาตั้งแต่ 5 ถึง 100 บาท
            'shop_id' => $this->faker->numberBetween(1, 10), // ให้ shop_id อยู่ในช่วง 1-10
            'is_available' => $this->faker->boolean(70), // 70% ที่จะเป็น true
        ];
    }
}
