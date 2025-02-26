<?php

use App\Http\Controllers\API\Auth\UserAuthController;
use App\Http\Controllers\API\Auth\ShopAuthController;
use App\Http\Controllers\API\ImageController;
use App\Http\Controllers\API\QueueController;
use App\Http\Controllers\API\QueueSubscriptionController;
use App\Http\Controllers\API\ShopController;
use App\Http\Controllers\API\UserController;
use Illuminate\Support\Facades\Route;

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

Route::get('images/{image}', [ImageController::class, 'show'])->name('images.show');

Route::get('queues/{queue_id}', [QueueController::class, 'getAllQueues'])->middleware('auth:sanctum');
Route::apiResource('queues', QueueController::class)->middleware('auth:sanctum');

Route::post('queues/{queue_id}/join', [QueueController::class, 'joinQueue'])->middleware('auth:sanctum');
Route::get('queues/{queue_id}/status', [QueueController::class, 'status'])->middleware('auth:sanctum');
Route::post('queues/{queue_id}/cancel', [QueueController::class, 'cancel'])->middleware('auth:sanctum');
Route::post('queues/{queue_id}/next', [QueueController::class, 'next'])->middleware('auth:sanctum');

Route::get('/subscribe', [QueueSubscriptionController::class, 'subscribe'])->middleware('auth:sanctum');










