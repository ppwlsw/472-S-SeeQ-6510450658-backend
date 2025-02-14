<?php

namespace App\Repositories;

use App\Repositories\Traits\SimpleCRUD;
use App\Models\Shop;

class ShopRepository
{
    use SimpleCRUD;

    private string $model = Shop::class;

    public function getByEmail(string $email) {
        return $this->model::where('email', $email)->first();
    }
}
