<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Broadcast;

// Route::middleware('auth:sanctum')->get('/user', function (Reques $request) {
//     return $request->user();
// });

Route::get('/', function () {
    return view('welcome');
});
Route::post('/messages', [MessageController::class, 'store']);

Route::post('/messages', [MessageController::class, 'store']);
Route::get('/messages', [MessageController::class, 'index']);
Route::get('/products', [ProductController::class, 'index']);
Route::post('/products/cart', [ProductController::class,'store']);
Route::post('/products/{product}', [ProductController::class,'update']);
Route::post('/products/{id}', [ProductController::class,'destroy']);

Broadcast::routes();
// Routes de broadcast
Broadcast::routes(['middleware' => ['auth:sanctum']]);

Route::get('/csrf-token', function () {
    return response()->json([
        'csrf_token' => csrf_token()
    ]);
});
