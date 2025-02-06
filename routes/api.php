<?php

use App\Http\Controllers\API\QueueController;
use App\Http\Controllers\API\QueueSubscriptionController;
use App\Http\Controllers\API\ShopController;
use App\Http\Controllers\API\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:api')->group(function () {
    Route::get('/', function () {
        return [
            'success' => true,
            'version' => '1.0.0',
        ];
    });

    // ... Other routes
});

Route::apiResource('users', UserController::class);

Route::apiResource('shops', ShopController::class);


Route::get('queues/{queue_id}', [QueueController::class, 'getAllQueues']);
Route::apiResource('queues', QueueController::class);

Route::post('/queues/{queue_id}/join', [QueueController::class, 'joinQueue']);
Route::get('queues/{queue_id}/status', [QueueController::class, 'status']);
Route::post('queues/{queue_id}/cancel', [QueueController::class, 'cancel']);
Route::post('queues/{queue_id}/next', [QueueController::class, 'next']);

Route::get('/subscribe', [QueueSubscriptionController::class, 'subscribe']);

//Route::get('/add-to-queue', function () {
//    $redis = new Redis();
//    $redis->connect('redis', 6379); // เชื่อมต่อกับ Redis
//    $redis->lpush('TableA(5-7)', 7);
//    return "Added to Redis queue";
//});
//
//Route::get('/get-queue', function () {
//    $redis = new Redis();
//    $redis->connect('redis', 6379); // เชื่อมต่อกับ Redis
//    return $redis->lrange('TableA(5-7)', 0, -1);
//});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
