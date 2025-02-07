<?php

namespace App\Repositories;

use App\Repositories\Traits\SimpleCRUD;
use App\Models\Queue;

class QueueRepository
{
    use SimpleCRUD;

    private string $model = Queue::class;

    // Add any custom repository methods here
}
