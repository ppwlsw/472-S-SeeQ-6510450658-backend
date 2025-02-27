<?php

namespace App\Repositories;

use App\Repositories\Traits\SimpleCRUD;
use App\Models\Reminder;

class ReminderRepository
{
    use SimpleCRUD;

    private string $model = Reminder::class;

    // Add any custom repository methods here

    public function getAllRemindersByShopId(int $shopId){
        return $this->model::where('shop_id', $shopId)->orderBy('due_date', 'desc')->get();
    }

    public function markAsDone(int $id){
        return $this->model::where('id', $id)->update(['status' => 'completed']);
    }
}
