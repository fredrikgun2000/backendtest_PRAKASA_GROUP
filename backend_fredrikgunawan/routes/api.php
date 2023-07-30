<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Functional\Auth\AuthController AS Auth;
use App\Http\Controllers\Functional\User\AdminController AS Admin;
use App\Http\Controllers\Functional\Product\FoodController AS Food;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function (){
    Route::middleware('auth:api')->group(function () {
        // Auth
        Route::prefix('auth')->group(function () {
            Route::post('logout', [Auth::class, 'Logout']);
            Route::post('refresh', [Auth::class, 'Refresh']);
            Route::get('me', [Auth::class, 'Me']);
        });

        Route::middleware('role:admin')->group(function () {
            // Admin
            Route::prefix('admin')->group(function () {
                Route::post('create', [Admin::class, 'Create']);
                Route::get('read', [Admin::class, 'Read']);
                Route::post('update', [Admin::class, 'Update']);
                Route::delete('delete', [Admin::class, 'Delete']);
            });
            // Food
            Route::prefix('food')->group(function () {
                Route::post('create', [Food::class, 'Create']);
                Route::get('read', [Food::class, 'Read']);
                Route::post('update', [Food::class, 'Update']);
                Route::delete('delete', [Food::class, 'Delete']);
            });
        });
    });

    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('login', [Auth::class, 'Login']);
    });

    // Food
    Route::prefix('food')->group(function () {
        Route::get('read', [Food::class, 'Read']);
    });

});