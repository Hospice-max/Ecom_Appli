<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Broadcast;

Route::middleware('auth:sanctum')->group(function () {
    // Messages
    Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');
    Route::get('/messages/{userId}', [MessageController::class, 'index'])->name('messages.index');

    // Utilisateurs
    Route::get('/users', [MessageController::class, 'users'])->name('users.index');

    // Statut utilisateur
    Route::post('/status/update', [MessageController::class, 'updateStatus'])->name('status.update');
    Route::post('/status/disconnect', [MessageController::class, 'disconnect'])->name('status.disconnect');

    // User routes
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/user/me', [UserController::class, 'me'])->name('user.me');
});

Route::get('/', function () {
    return view('welcome');
});

Route::get('/products', [ProductController::class, 'index']);
Route::post('/products/cart', [ProductController::class,'store']);
Route::post('/products/{product}', [ProductController::class,'update']);
Route::post('/products/{id}', [ProductController::class,'destroy']);

Broadcast::routes();
// Routes de broadcast
Broadcast::routes(['middleware' => ['auth:sanctum']]);
