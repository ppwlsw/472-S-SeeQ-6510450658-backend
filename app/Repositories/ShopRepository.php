<?php

namespace App\Repositories;

use App\Repositories\Traits\SimpleCRUD;
use App\Models\Shop;

class ShopRepository
{
    use SimpleCRUD;

    private string $model = Shop::class;

    // Add any custom repository methods here
}
