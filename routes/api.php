<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\CustomerAddressController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;

Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::get('categories-user', [CategoriesController::class, 'fetch']);
Route::get('get-nearby', [CustomerController::class, 'getNearby']);
Route::get('get-store-by-category', [CustomerController::class, 'storeByCategory']);
Route::get('get-store-by-slug/{slug}', [CustomerController::class, 'showStore']);
Route::get('getStorebySearching', [CustomerController::class, 'getStoreBySearching']);

Route::get('auth/google/login', [GoogleController::class, 'redirectLogin']);
Route::get('auth/google/customer', [GoogleController::class, 'redirectCustomerToGoogle']);

Route::post('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);
Route::post('auth/google/login-app', [GoogleController::class, 'checkLoginGoogleApp']);
Route::post('register-customer', [RegisterController::class, 'registerCustomer']);
Route::post('register-google-customer', [RegisterController::class, 'registerGoogleCustomer']);

// Route::get('auth/google/store', [GoogleController::class, 'redirectStoreToGoogle']);
// Route::post('register-from-google', [RegisterController::class, 'registerFromGoogle']);



Route::middleware([
    'auth:sanctum',
])->group(function () {
    Route::get('get-profile', [ProfileController::class, 'getProfile']);
    Route::post('update-profile', [ProfileController::class, 'update']);
    
    Route::post('update-user', [ProfileController::class, 'updateUser']);
    Route::prefix('category')->group(function () {
        Route::middleware(['role:admin'])->group(function () {
            Route::post('add-edit', [CategoriesController::class, 'addEdit']);
            Route::delete('destroy/{id}', [CategoriesController::class, 'destroy']);
            Route::post('force-delete', [CategoriesController::class, 'forceDelete']);
        });
        Route::get('get', [CategoriesController::class, 'fetch']);
        Route::middleware(['role:store'])->group(function () {
            Route::get('get-categories', [StoreController::class, 'getCategory']);
            Route::post('add-edit-menu-categories', [StoreController::class, 'addEditMenuCategory']);
            Route::delete('destroy-menu-categories/{id}', [StoreController::class, 'destroyMenuCategories']);
        });
    });

    Route::middleware(['role:admin'])->group(function () {
        Route::prefix('store')->group(function () {
            Route::get('get', [StoreController::class, 'fetch']);
            Route::post('add-edit', [StoreController::class, 'addEdit']);
            Route::delete('destroy/{id}', [StoreController::class, 'destroy']);
        });

        Route::prefix('customer')->group(function () {
            Route::get('get', [CustomerController::class, 'fetch']);
            Route::post('add-edit', [CustomerController::class, 'addEdit']);
            Route::delete('destroy/{id}', [CustomerController::class, 'destroy']);
        });
    });

    Route::middleware(['role:customer'])->group(function () {
        Route::prefix('order-customer')->group(function () {
            Route::post('checkout', [OrderController::class, 'checkout']);
            Route::get('history', [OrderController::class, 'customerOrderHistory']);
        });

        Route::prefix('cart')->group(function () {
            Route::get('/', [CartController::class, 'index']);
            Route::post('add', [CartController::class, 'store']);
            Route::put('update/{id}', [CartController::class, 'update']);
            Route::delete('remove/{id}', [CartController::class, 'destroy']);
            Route::delete('clear', [CartController::class, 'clear']);
        });

        Route::prefix('address')->group(function () {
            Route::get('/', [CustomerAddressController::class, 'index']);
            Route::post('store', [CustomerAddressController::class, 'store']);
            Route::put('update/{id}', [CustomerAddressController::class, 'update']);
            Route::delete('destroy/{id}', [CustomerAddressController::class, 'destroy']);
            Route::post('set-primary/{id}', [CustomerAddressController::class, 'setPrimary']);
        });
    });

    Route::middleware(['role:store'])->group(function () {
        Route::prefix('product')->group(function () {
            Route::get('get-product', [ProductController::class, 'getProduct']);
            Route::get('detail-product/{id}', [ProductController::class, 'detail']);
            Route::post('add-edit', [ProductController::class, 'addEdit']);
            Route::delete('destroy/{id}', [ProductController::class, 'destroy']);
        });

        Route::prefix('order')->group(function () {
            Route::get('incoming', [OrderController::class, 'getIncomingOrders']);
            Route::post('update-status/{id}', [OrderController::class, 'updateStatus']);
        });
    });
});
