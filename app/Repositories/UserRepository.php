<?php

namespace App\Repositories;

use App\Repositories\Traits\SimpleCRUD;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserRepository
{
    use SimpleCRUD;

    private string $model = User::class;

    // Add any custom repository methods here

    public function getByEmail(string $email) {
        return $this->model::where('email', $email)->firstOrFail();
    }
}
