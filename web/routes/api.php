<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::get('health', [\App\Http\Controllers\Api\HealthController::class, 'index']);

Route::middleware(\App\Http\Middleware\ApiKeyMiddleware::class)->group(function() {

    Route::get('customers', [\App\Http\Controllers\Api\CustomersController::class, 'index']);
    Route::post('customers', [\App\Http\Controllers\Api\CustomersController::class, 'store']);
    Route::get('customers/{id}', [\App\Http\Controllers\Api\CustomersController::class, 'show']);
    Route::put('customers/{id}', [\App\Http\Controllers\Api\CustomersController::class, 'update']);
    Route::delete('customers/{id}', [\App\Http\Controllers\Api\CustomersController::class, 'destroy']);
    Route::get('customers/{id}/hosting-subscriptions', [\App\Http\Controllers\Api\CustomersController::class, 'getHostingSubscriptionsByCustomerId']);

    Route::get('hosting-subscriptions', [\App\Http\Controllers\Api\HostingSubscriptionsController::class, 'index']);
    Route::post('hosting-subscriptions', [\App\Http\Controllers\Api\HostingSubscriptionsController::class, 'store']);
    Route::put('hosting-subscriptions/{id}', [\App\Http\Controllers\Api\HostingSubscriptionsController::class, 'update']);
    Route::delete('hosting-subscriptions/{id}', [\App\Http\Controllers\Api\HostingSubscriptionsController::class, 'destroy']);

    Route::get('hosting-plans', [\App\Http\Controllers\Api\HostingPlansController::class, 'index']);

});

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
