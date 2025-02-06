<?php

namespace App\Repositories;

use App\Repositories\Traits\SimpleCRUD;
use App\Models\User;

class UserRepository
{
    use SimpleCRUD;

    private string $model = User::class;

    // Add any custom repository methods here
}
