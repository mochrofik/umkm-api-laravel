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
            // Route::prefix('store')->group(function () {
            //     Route::get('get', [StoreController::class, 'fetch']);
            //     Route::post('add-edit', [StoreController::class, 'addEdit']);
            //     Route::delete('destroy/{id}', [StoreController::class, 'destroy']);
            // });
        });
        Route::get('get', [CategoriesController::class, 'fetch']);
        Route::middleware(['role:store'])->group(function () {
            Route::get('get-categories', [StoreController::class, 'getCategory']);
            Route::post('add-edit-menu-categories', [StoreController::class, 'addEditMenuCategory']);
            Route::delete('destroy-menu-categories/{id}', [StoreController::class, 'destroyMenuCategories']);
        });
    });

    Route::prefix('store')->group(function () {
        Route::get('get', [StoreController::class, 'fetch']);
        Route::post('add-edit', [StoreController::class, 'addEdit']);
        Route::delete('destroy/{id}', [StoreController::class, 'destroy']);
    });

    Route::middleware(['role:store'])->group(function () {
        Route::prefix('product')->group(function () {
            Route::get('get-product', [ProductController::class, 'getProduct']);
            Route::get('detail-product/{id}', [ProductController::class, 'detail']);
            Route::post('add-edit', [ProductController::class, 'addEdit']);
            Route::delete('destroy/{id}', [ProductController::class, 'destroy']);
        });
    });
});
