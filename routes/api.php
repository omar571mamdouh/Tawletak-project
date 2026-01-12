<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RestaurantController;
use App\Http\Controllers\Api\ReservationController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::apiResource('restaurants', RestaurantController::class);
Route::get('restaurants/{restaurant}/branches', [RestaurantController::class, 'branches']);

// Reservations
Route::apiResource('reservations', ReservationController::class);


Route::post('reservations/{reservation}/confirm', [ReservationController::class, 'confirm']);
Route::post('reservations/{reservation}/cancel', [ReservationController::class, 'cancel']);
Route::post('reservations/{reservation}/seat', [ReservationController::class, 'seat']);
Route::post('reservations/{reservation}/complete', [ReservationController::class, 'complete']);