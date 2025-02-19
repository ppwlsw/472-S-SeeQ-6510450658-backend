<?php

namespace App\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UserQueueRepository
{
    // Add any custom repository methods here

    public function checkQueueNumberAndQueueInfo(int $user_id, int $queueID){
        $record = DB::table('users_queues')
            ->join('queues', 'users_queues.queue_id', '=', 'queues.id') // เชื่อมกับตาราง queues
            ->join('shops', 'queues.shop_id', '=', 'shops.id') // เชื่อมกับตาราง shops
            ->where('users_queues.user_id', $user_id)
            ->where('users_queues.queue_id', $queueID)
            ->where('users_queues.status', "waiting")
            ->select([
                'users_queues.*',
                'queues.name as queue_name',
                'queues.tag as queue_tag',
                'shops.name as shop_name',
                'shops.description as shop_description',
            ])
            ->first();

        return $record;
    }

    public function create(array $attributes){
        $now = now();
        $attributes["created_at"] = $now;
        $attributes["updated_at"] = $now;
        return DB::table('users_queues')->insertGetId($attributes);
    }

    public function checkUserAlreadyJoinQueue(int $user_id, int $queueID){
        $exist =  DB::table('users_queues')->where('user_id', $user_id)->where('queue_id', $queueID)->where("status", "waiting")->first();
        return $exist;
    }

    public function updateStatusToCancel(int $user_id, int $queueID){
        return DB::table('users_queues')->where('user_id', $user_id)->where('queue_id', $queueID)->update(['status' => "cancel"]);
    }

    public function updateStatusToComplete(int $user_id, int $queueID){
        return DB::table('users_queues')->where('user_id', $user_id)->where('queue_id', $queueID)->update(['status' => "completed"]);
    }
}
