<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Broadcast;

Route::get('/', function () {
    return view('welcome');
});

// Routes d'authentification
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::post('/postMessages', [MessageController::class, 'store'])->middleware('auth:sanctum');
Route::get('/getMessages', [MessageController::class, 'index'])->middleware('auth:sanctum');

Route::get('/products', [ProductController::class, 'index'])->middleware('auth:sanctum');
Route::post('/products/cart', [ProductController::class,'store'])->middleware('auth:sanctum');
Route::get('/products/{id}', [ProductController::class, 'show'])->middleware('auth:sanctum');
Route::post('/updateProduct/{product}', [ProductController::class,'update'])->middleware('auth:sanctum');
Route::post('/deleteProduct/{id}', [ProductController::class,'destroy'])->middleware('auth:sanctum');

Route::post('/orders', [OrderController::class,'store'])->middleware('auth:sanctum');
Route::get('/getOrders', [OrderController::class,'index'])->middleware('auth:sanctum');
Broadcast::routes();
// Routes de broadcast
Broadcast::routes(['middleware' => ['auth:sanctum']]);
Route::get('/csrf-token', function () {
    return response()->json([
        'csrf_token' => csrf_token()
    ]);
});
