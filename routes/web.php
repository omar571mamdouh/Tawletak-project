<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DeviceTokenController;

Route::get('/', function () {
    return view('welcome');
});



// Route::middleware(['web'])->group(function () {
//     Route::post('/device-token', [DeviceTokenController::class, 'store']);
//     Route::delete('/device-token', [DeviceTokenController::class, 'destroy']);
// });