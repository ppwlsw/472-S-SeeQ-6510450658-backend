<?php

namespace Database\Seeders;

use App\Models\Queue;
use App\Models\Shop;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class QueueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Queue::factory(Shop::all()->count())->create();
    }
}
