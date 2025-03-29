<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Reminder;
use App\Models\Shop;

class ReminderSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure there are shops to associate reminders with
            Reminder::factory(20)->create();
    }
}
