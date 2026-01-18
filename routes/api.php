<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RestaurantController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\TableController;
use App\Http\Controllers\Api\OfferController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DeviceTokenController;
use App\Http\Controllers\Api\StaffAuthController;
use App\Services\FcmService;
use App\Services\NotificationService;
use App\Enums\NotificationType;



Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/staff/login', [StaffAuthController::class, 'login']);
Route::middleware('auth:staff')->post('/staff/logout', [StaffAuthController::class, 'logout']);


Route::post('/auth/admin/login', [AuthController::class, 'loginAdmin']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

     Route::post('/device-token', [DeviceTokenController::class, 'store']);
    Route::delete('/device-token', [DeviceTokenController::class, 'destroy']);
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

Route::get('/notifications', function () {
    $user = auth()->user();

    return \App\Models\Notification::forCustomer($user->id)
        ->orderByDesc('sent_at')
        ->get();
});

Route::get('/test-fcm', function () {
    
    // Test 1: إرسال مباشر بالـ token
    try {
        $result = FcmService::sendToAdmin(
            adminId: 1, // غيّر للـ ID بتاعك
            title: 'اختبار الإشعارات',
            body: 'لو شفت الرسالة دي يبقى FCM شغال تمام! 🎉',
            data: [
                'test' => 'true',
                'timestamp' => now()->toString()
            ]
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Notification sent!',
            'result' => $result
        ]);
        
    } catch (\Throwable $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});