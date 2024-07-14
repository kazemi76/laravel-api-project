<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Middleware\UserIsAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::group(['middleware' => ['auth:sanctum',UserIsAdmin::class]], function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/brands/{brand}/products', [BrandController::class, 'products']);

    Route::apiResource('brands', BrandController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('categories', CategoryController::class);
    
    Route::get('/categories/{category}/products', [CategoryController::class, 'products']);
    Route::get('/categories/{category}/children', [CategoryController::class, 'children']);
    Route::get('/categories/{category}/parent', [CategoryController::class, 'parent']);

});

Route::post('/payment/send', [PaymentController::class, 'send'])->middleware('auth:sanctum');

Route::post('/payment/verify', [PaymentController::class, 'verify']);
