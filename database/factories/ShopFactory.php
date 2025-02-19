<?php

namespace Database\Factories;

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
            'name' => $this->faker->company, // สร้างชื่อบริษัท
            'address' => $this->faker->address, // สร้างที่อยู่
            'shop_phone' => $this->faker->phoneNumber, // สร้างเบอร์โทรศัพท์
            'description' => $this->faker->sentence, // สร้างคำอธิบาย
            'is_open' => $this->faker->boolean, // สร้างสถานะเปิดร้าน (true/false)
            'approve_status' => $this->faker->boolean, // สร้างสถานะอนุมัติ
            'user_id' => $this->faker->numberBetween(1, 10), // สร้าง user_id แบบสุ่ม
        ];
    }
}
