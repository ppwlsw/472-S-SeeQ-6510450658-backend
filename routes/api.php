<?php

use App\Http\Controllers\API\Auth\AuthenticateController;
use App\Http\Controllers\API\ItemController;
use App\Http\Controllers\API\QueueController;
use App\Http\Controllers\API\QueueSubscriptionController;
use App\Http\Controllers\API\ShopController;
use App\Http\Controllers\API\UserController;
use App\Http\Resources\ReminderCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redis;

Route::middleware('throttle:api')->group(function () {
    Route::get('/', function () {
        return [
            'success' => true,
            'version' => '1.0.0',
        ];
    });
});

Route::apiResource('users', UserController::class)->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('shops', ShopController::class);
    Route::get('queues/getAllQueuesReserved', [QueueController::class, 'getQueueReserved']);
    Route::apiResource('queues', QueueController::class);

    Route::prefix('queues/{queue_id}')->group(function () {
        Route::post('join', [QueueController::class, 'joinQueue']);
        Route::post('status', [QueueController::class, 'status']);
        Route::post('cancel', [QueueController::class, 'cancel']);
        Route::post('next', [QueueController::class, 'next']);
        Route::get('getAllQueue', [QueueController::class, 'getAllQueues']);
        Route::get('getQueueNumber', [QueueController::class, 'getQueueNumber']);
    });

    Route::apiResource('items', ItemController::class);
    Route::apiResource('reminders', ReminderCollection::class);
});

Route::get('/queues/{queue_id}/subscribe', [QueueSubscriptionController::class, 'subscribe']);


Route::post('login', [AuthenticateController::class, 'login'])->name('user.login');
Route::post('register', [AuthenticateController::class, 'register'])->name('user.register');


Route::get('redis_key', function (){
    return Redis::keys("*");
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get("/test/redisConnection", [QueueController::class, 'testConnection']);
