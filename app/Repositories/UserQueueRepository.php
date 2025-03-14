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

    public function getAllQueueReservedComplete(int $user_id){
        $queues = DB::table('users_queues')
            ->join('queues', 'users_queues.queue_id', '=', 'queues.id') // เชื่อมกับตาราง queues
            ->join('shops', 'queues.shop_id', '=', 'shops.id') // เชื่อมกับตาราง shops
            ->where('users_queues.user_id', $user_id)
            ->where("users_queues.status", "completed")
            ->select([
                'users_queues.created_at',
                'shops.name as shop_name',
                'shops.image_url as shop_image_url'
            ])
            ->get();
        return $queues;
    }

    public function updateStatusToCancel(int $user_id, int $queueID, string $queue_number){
        return DB::table('users_queues')->where('user_id', $user_id)->where('queue_id', $queueID)->where('queue_number', $queue_number)->update(['status' => "canceled"]);
    }

    public function updateStatusToComplete(int $user_id, int $queueID, string $queue_number){
        return DB::table('users_queues')->where('user_id', $user_id)->where('queue_id', $queueID)->where('queue_number', $queue_number)->update(['status' => "completed"]);
    }

    public function userWaitingQueue(int $queueID){
        $queues = DB::table('users_queues')
            ->join('queues', 'users_queues.queue_id', '=', 'queues.id') // เชื่อมกับตาราง queues
            ->join('users', 'users_queues.user_id', '=', 'users.id') // เชื่อมกับตาราง users
            ->where('users_queues.queue_id', $queueID)
            ->where("users_queues.status", "waiting")
            ->select([
                'queues.*',
                'users.name as user_name',
                'users.id as user_id',
                'users.phone as user_phone',
            ])
            ->get();
        return $queues;
    }
}
