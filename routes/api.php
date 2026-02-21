<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;


Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware([
    'auth:sanctum',
])->group(function () {
    Route::prefix('category')->group(function () {
        Route::middleware(['role:admin'])->group(function () {
            Route::post('add-edit', [CategoriesController::class, 'addEdit']);
            Route::delete('destroy/{id}', [CategoriesController::class, 'destroy']);
            Route::post('force-delete', [CategoriesController::class, 'forceDelete']);
        });
        Route::get('get', [CategoriesController::class, 'fetch']);
    });
    Route::prefix('product')->group(function () {
        Route::post('add-edit', [ProductController::class, 'addEdit']);
    });
    Route::prefix('store')->group(function () {
        Route::get('get', [StoreController::class, 'fetch']);
    });
});
