<?php
use App\Http\Controllers\API\Auth\UserAuthController;
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

Route::post('auth/users/login', [UserAuthController::class, 'login'])->name('auth.user.login');
Route::post('auth/shop/login', [ShopAuthController::class, 'login'])->name('auth.shop.login');
Route::post('auth/users/register', [UserAuthController::class, 'register'])->name('auth.user.register');
Route::get('auth/google', [UserAuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('auth/google/callback', [UserAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
Route::post('auth/decrypt', [UserAuthController::class, 'decrypt'])->name('auth.decrypt');
Route::get('auth/shops/{id}/{token}/verify', [ShopAuthController::class, 'verify'])->name('auth.shop.verify');
Route::get('auth/users/{id}/{token}/verify', [UserAuthController::class, 'verify'])->name('auth.user.verify');

Route::apiResource('users', UserController::class)->middleware('auth:sanctum');
Route::put('users/{user}/password', [UserController::class, 'updatePassword'])
    ->middleware('auth:sanctum')
    ->name('users.update.password');
Route::put('users/{user}/avatar', [UserController::class, 'updateAvatar'])
    ->middleware('auth:sanctum')
    ->name('users.update.avatar');

Route::get('shops/filter', [ShopController::class, 'filterShop']);

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

Route::get('redis_key', function (){
    return Redis::keys("*");
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get("/test/redisConnection", [QueueController::class, 'testConnection']);
