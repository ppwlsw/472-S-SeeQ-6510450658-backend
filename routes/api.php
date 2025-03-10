<?php

use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\Auth\ShopAuthController;
use App\Http\Controllers\API\ImageController;
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

Route::post('auth/login', [AuthController::class, 'login'])->name('auth.login');
Route::post('auth/register', [AuthController::class, 'register'])->name('auth.register');
Route::get('auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
Route::post('auth/decrypt', [AuthController::class, 'decrypt'])->name('auth.decrypt');
Route::get('auth/emails/{user}/{token}/verify', [AuthController::class, 'verify'])->name('auth.emails.verify');

Route::get('users/withTrashedPaginate', [UserController::class, 'getAllCustomerWithTrashedPaginate'])->middleware('auth:sanctum')
    ->name('users.withTrashedPaginate');
Route::get('users/withTrashed', [UserController::class, 'getAllCustomerWithTrashed'])->middleware('auth:sanctum')
    ->name('users.withTrashed');
Route::apiResource('users', UserController::class)->middleware('auth:sanctum');
Route::put('users/{user}/password', [UserController::class, 'updatePassword'])
    ->middleware('auth:sanctum')
    ->name('users.update.password');
Route::put('users/{user}/avatar', [UserController::class, 'updateAvatar'])
    ->middleware('auth:sanctum')
    ->name('users.update.avatar');
Route::get('users/{user}/shop', [UserController::class, 'showShop'])->middleware('auth:sanctum')
    ->name('users.show.shop');


Route::get('shops/filter', [ShopController::class, 'filterShop']);
Route::patch('shops/{id}/restore', [ShopController::class, 'restore'])->middleware('auth:sanctum')
    ->name('shops.restore');
Route::get('shops/withTrashed', [ShopController::class, 'getAllShopWithTrashed'])->middleware('auth:sanctum')
    ->name('shops.withTrashed');

Route::apiResource('shops', ShopController::class)->middleware('auth:sanctum');
Route::put('shops/{shop}/password', [ShopController::class, 'updatePassword'])
    ->middleware('auth:sanctum')
    ->name('shops.update.password');
Route::put('shops/{shop}/avatar', [ShopController::class, 'updateAvatar'])->middleware('auth:sanctum')
    ->name('shops.update.avatar');
Route::put('shops/{shop}/is-open', [ShopController::class, 'updateIsOpen'])->middleware('auth:sanctum')
    ->name('shops.update.is-open');
Route::put('shops/{id}/location', [ShopController::class, 'updateLocation'])->middleware('auth:sanctum')
    ->name('shops.update.location');

Route::middleware('auth:sanctum')->group(function () {
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

    Route::get('images/{image}', [ImageController::class, 'show'])->name('images.show');

    Route::apiResource('items', ItemController::class);
    Route::apiResource('reminders', ReminderCollection::class);
});

Route::get('/queues/{queue_id}/subscribe', [QueueSubscriptionController::class, 'subscribe']);

Route::get('redis_key', function () {
    return Redis::keys("*");
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get("/test/redisConnection", [QueueController::class, 'testConnection']);
