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
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PasswordController;
use App\Http\Controllers\Api\CustomerAuthController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\CustomerProfileController;
use App\Http\Controllers\Api\CustomerPreferencesController;
use App\Http\Controllers\Api\CustomerNotificationController;
use App\Http\Controllers\Api\SupportController;
use App\Http\Controllers\Api\AppInfoController;
use App\Http\Controllers\Api\RewardsController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\FavoriteController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('auth')->group(function () {

    // SignUp
    Route::post('register', [CustomerAuthController::class, 'register']);

    // Login
    Route::post('login', [CustomerAuthController::class, 'login']);

    Route::post('logout', [CustomerAuthController::class, 'logout'])
    ->middleware('auth:sanctum');


    // Logout (Sanctum)

    // Forgot Password -> send OTP
    Route::post('forgot-password', [PasswordController::class, 'forgotPassword']);

    // Verify Code (OTP)
    Route::post('verify-otp', [PasswordController::class, 'verifyOtp']);

    // Resend OTP
    Route::post('resend-otp', [PasswordController::class, 'resendOtp']);

    // New Password
    Route::post('reset-password', [PasswordController::class, 'resetPassword']);
});

Route::get('/support/faqs', [SupportController::class, 'faqs']);

Route::post('/staff/login', [StaffAuthController::class, 'login']);
Route::middleware('auth:staff')->post('/staff/logout', [StaffAuthController::class, 'logout']);


Route::post('/auth/admin/login', [AuthController::class, 'loginAdmin']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

     Route::post('/device-token', [DeviceTokenController::class, 'store']);
    Route::delete('/device-token', [DeviceTokenController::class, 'destroy']);
    
});

Route::middleware('auth:customer')->group(function () {
    Route::post('/customer/device-token', [DeviceTokenController::class, 'storeCustomer']);
    Route::delete('/customer/device-token', [DeviceTokenController::class, 'destroyCustomer']);

    Route::get('/notifications', function () {
        $customer = Auth::guard('customer')->user();

        return \App\Models\Notification::forCustomer($customer->id)
            ->orderByDesc('sent_at')
            ->get();
    });
});



Route::get('customers', [CustomerController::class, 'index']);
Route::get('customers/{customer}', [CustomerController::class, 'show']);

Route::get('/tables', [TableController::class, 'index']);
Route::get('/tables/{table}', [TableController::class, 'show']);

Route::get('/offers', [OfferController::class, 'index']);
Route::get('/offers/{offer}', [OfferController::class, 'show']);

Route::apiResource('restaurants', RestaurantController::class);
Route::get('restaurants/{restaurant}/branches', [RestaurantController::class, 'branches']);

Route::prefix('restaurants')->group(function () {
    // test endpoint: send menu in body and get normalized response
    Route::post('{restaurant}/menu/preview', [MenuController::class, 'preview']);

    // optional: same for highlights preview
    Route::post('{restaurant}/menu/highlights/preview', [MenuController::class, 'highlightsPreview']);
});
// Reservations
Route::apiResource('reservations', ReservationController::class);

Route::post('reservations/{reservation}/confirm', [ReservationController::class, 'confirm']);
Route::post('reservations/{reservation}/cancel', [ReservationController::class, 'cancel']);
Route::post('reservations/{reservation}/seat', [ReservationController::class, 'seat']);
Route::post('reservations/{reservation}/complete', [ReservationController::class, 'complete']);


Route::middleware(['auth:sanctum'])->group(function () {
   Route::get('me/profile', [CustomerProfileController::class, 'show']);
    Route::put('me/profile', [CustomerProfileController::class, 'update']);
});

    Route::get('location/search', [LocationController::class, 'search']);      // autocomplete
    Route::get('location/reverse', [LocationController::class, 'reverse']);    // confirm pin

    Route::middleware('auth:sanctum')->group(function () {
    Route::get('favorites', [FavoriteController::class, 'index']);
    Route::post('favorites', [FavoriteController::class, 'store']);
    Route::delete('favorites/{restaurantId}', [FavoriteController::class, 'destroy']);
});

   

    // Change Language / Preferences
    Route::put('me/preferences', [CustomerPreferencesController::class, 'update']);

    // Rewards (My Rewards)
    Route::get('rewards', [RewardsController::class, 'index']);
    Route::get('rewards/active', [RewardsController::class, 'active']);
    Route::post('rewards/redeem', [RewardsController::class, 'redeem']);
    Route::get('rewards/history', [RewardsController::class, 'history']);
 

    Route::get('/app/about', [AppInfoController::class, 'about']);

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

Route::middleware('auth:sanctum')->group(function () {
    
    // Send notification to single customer
    Route::post('/notifications/send-to-customer', [
        \App\Http\Controllers\Api\NotificationController::class, 
        'sendToCustomer'
    ]);
    
    // Send notification to multiple customers
    Route::post('/notifications/send-to-multiple-customers', [
        \App\Http\Controllers\Api\NotificationController::class, 
        'sendToMultipleCustomers'
    ]);
    
    // Broadcast to all customers
    Route::post('/notifications/broadcast-to-all-customers', [
        \App\Http\Controllers\Api\NotificationController::class, 
        'sendToAllCustomers'
    ]);
    
    // Get customer notifications
    Route::get('/notifications/customer/{customerId}', [
        \App\Http\Controllers\Api\NotificationController::class, 
        'getCustomerNotifications'
    ]);
    
    // Mark notification as read
    Route::post('/notifications/{notificationId}/mark-as-read', [
        \App\Http\Controllers\Api\NotificationController::class, 
        'markAsRead'
    ]);
    
    // Get unread count
    Route::get('/notifications/customer/{customerId}/unread-count', [
        \App\Http\Controllers\Api\NotificationController::class, 
        'getUnreadCount'
    ]);
});