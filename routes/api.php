<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RestaurantController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\TableController;
use App\Http\Controllers\Api\OfferController;
use App\Http\Controllers\Api\AuthController;



Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/auth/admin/login', [AuthController::class, 'loginAdmin']);
Route::post('/auth/staff/login', [AuthController::class, 'loginStaff']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
});

Route::get('customers', [CustomerController::class, 'index']);
Route::get('customers/{customer}', [CustomerController::class, 'show']);

Route::get('/tables', [TableController::class, 'index']);
Route::get('/tables/{table}', [TableController::class, 'show']);

Route::get('/offers', [OfferController::class, 'index']);
Route::get('/offers/{offer}', [OfferController::class, 'show']);

Route::apiResource('restaurants', RestaurantController::class);
Route::get('restaurants/{restaurant}/branches', [RestaurantController::class, 'branches']);

// Reservations
Route::apiResource('reservations', ReservationController::class);

Route::post('reservations/{reservation}/confirm', [ReservationController::class, 'confirm']);
Route::post('reservations/{reservation}/cancel', [ReservationController::class, 'cancel']);
Route::post('reservations/{reservation}/seat', [ReservationController::class, 'seat']);
Route::post('reservations/{reservation}/complete', [ReservationController::class, 'complete']);