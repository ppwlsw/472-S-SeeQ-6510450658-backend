<?php

namespace App\Repositories;

use App\Repositories\Traits\SimpleCRUD;
use App\Models\Reminder;

class ReminderRepository
{
    use SimpleCRUD;

    private string $model = Reminder::class;

    // Add any custom repository methods here
}
