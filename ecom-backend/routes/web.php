<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Broadcast;

Route::get('/', function () {
    return view('welcome');
});

// Routes d'authentification
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Routes des messages
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/postMessages', [MessageController::class, 'store']);
    Route::get('/getMessages', [MessageController::class, 'index']);
    Route::post('/messages', [MessageController::class, 'store']);
    Route::get('/messages/{userId}', [MessageController::class, 'index']);
});

// Routes des utilisateurs
Route::get('/users', [ProfileController::class, 'users']);
Route::get('/users/{id}', [ProfileController::class, 'user']);
Route::delete('/delete-account/{id}', action: [ProfileController::class,'destroy']);
Route::post('/update-avatar/{id}', action: [ProfileController::class,'updateAvatar']);
Route::post('/update-profile/{id}', action: [ProfileController::class,'updateProfile']);

// Routes des produits
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products/cart', [ProductController::class,'store']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::post('/updateProduct/{product}', [ProductController::class,'update']);
    Route::post('/deleteProduct/{id}', [ProductController::class,'destroy']);
});

// Route du panier
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/cart', [ProductController::class,'cart']);
    Route::post('/addToCart/{product}', [ProductController::class,'store']);
    Route::delete('/removeFromCart/{product}', [ProductController::class,'destroy']);
});

// Routes de broadcast
Broadcast::routes(['middleware' => ['auth:sanctum']]);

Route::get('/csrf-token', function () {
    return response()->json([
        'csrf_token' => csrf_token()
    ]);
});
