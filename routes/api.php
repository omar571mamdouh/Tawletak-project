<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\StaffAuthController;
use App\Http\Controllers\Api\CustomerAuthController;
use App\Http\Controllers\Api\PasswordController;
use App\Http\Controllers\Api\StaffPasswordController;

use App\Http\Controllers\Api\SupportController;
use App\Http\Controllers\Api\AppInfoController;

use App\Http\Controllers\Api\TableController;
use App\Http\Controllers\Api\TableStatusController;
use App\Http\Controllers\Api\OfferController;
use App\Http\Controllers\Api\RestaurantController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\MenuItemController;

use App\Http\Controllers\Api\AppReservationController;   // public reservations
use App\Http\Controllers\Api\ReservationController;      // staff actions / management

use App\Http\Controllers\Api\DeviceTokenController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\CustomerProfileController;
use App\Http\Controllers\Api\CustomerPreferencesController;
use App\Http\Controllers\Api\RewardsController;

use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\LocationStaffController;


use App\Http\Controllers\Api\Rbac\PermissionController;
use App\Http\Controllers\Api\Rbac\RoleController;
use App\Http\Controllers\Api\Rbac\StaffRoleController;
use App\Http\Controllers\Api\RestaurantStaffAuditLogController;

/*
|--------------------------------------------------------------------------
| Public endpoints (No Auth)
|--------------------------------------------------------------------------
*/
Route::get('support/faqs', [SupportController::class, 'faqs']);
Route::get('app/about', [AppInfoController::class, 'about']);

Route::get('location/search', [LocationController::class, 'search']);
Route::get('location/reverse', [LocationController::class, 'reverse']);

/*
|--------------------------------------------------------------------------
| Restaurants (Public - READ ONLY)
|--------------------------------------------------------------------------
*/
Route::get('restaurants', [RestaurantController::class, 'index']);
Route::get('restaurants/{restaurant}', [RestaurantController::class, 'show']);
Route::get('restaurants/{restaurant}/branches', [RestaurantController::class, 'branches']);

Route::prefix('restaurants/{restaurant}')->group(function () {
    Route::get('menu/sections', [MenuController::class, 'sections']);
    Route::get('menu/sections/{section}/items', [MenuController::class, 'sectionItems']);
    Route::get('menu/highlights', [MenuController::class, 'highlights']);

     //  ADD menu item inside a section (scoped to restaurant)
    Route::post('menu/sections/{section}/items', [MenuItemController::class, 'store']);

    //  UPDATE menu item inside a section (scoped to restaurant)
    Route::put('menu/sections/{section}/items/{item}', [MenuItemController::class, 'update']);

    //  DELETE menu item from a section (scoped to restaurant)
    Route::delete('menu/sections/{section}/items/{item}', [MenuItemController::class, 'destroy']);
});


/*
|--------------------------------------------------------------------------
| Public Reservations (Mobile Public Flow)
|--------------------------------------------------------------------------
| NOTE: ده نفس اللي كان عندك تحت prefix public
*/
Route::prefix('public')->group(function () {
    Route::get('reservations/home', [AppReservationController::class, 'home']);
    Route::get('reservations/cancellations', [AppReservationController::class, 'cancellations']);

    Route::get('reservations', [AppReservationController::class, 'index']);
    Route::post('reservations', [AppReservationController::class, 'store']);

    Route::put('reservations/{id}', [AppReservationController::class, 'update']);
    Route::delete('reservations/{id}', [AppReservationController::class, 'destroy']);

    Route::post('reservations/{id}/confirm', [AppReservationController::class, 'confirm']);
    Route::post('reservations/{id}/cancel', [AppReservationController::class, 'cancel']);
});

/*
|--------------------------------------------------------------------------
| Customer Auth (No Auth)
|--------------------------------------------------------------------------
*/
Route::prefix('customer')->group(function () {
    Route::post('register', [CustomerAuthController::class, 'register']);
    Route::post('login', [CustomerAuthController::class, 'login']);

    Route::post('forgot-password', [PasswordController::class, 'forgotPassword']);
    Route::post('verify-otp', [PasswordController::class, 'verifyOtp']);
    Route::post('resend-otp', [PasswordController::class, 'resendOtp']);
    Route::post('reset-password', [PasswordController::class, 'resetPassword']);
});

/*
|--------------------------------------------------------------------------
| Customer Protected (auth:customer)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:customer')->group(function () {

    // Customer Logout
    Route::post('customer/logout', [CustomerAuthController::class, 'logout']);

    // Device token (Customer)
    Route::post('customer/device-token', [DeviceTokenController::class, 'storeCustomer']);
    Route::delete('customer/device-token', [DeviceTokenController::class, 'destroyCustomer']);

    // Favorites
    Route::get('customer/favorites', [FavoriteController::class, 'index']);
    Route::post('customer/favorites', [FavoriteController::class, 'store']);
    Route::delete('customer/favorites/{restaurantId}', [FavoriteController::class, 'destroy']);

    // Profile
    Route::get('customer/profile', [CustomerProfileController::class, 'show']);
    Route::put('customer/profile', [CustomerProfileController::class, 'update']);

    // Preferences
    Route::put('customer/preferences', [CustomerPreferencesController::class, 'update']);

    // Rewards
    Route::get('customer/rewards', [RewardsController::class, 'index']);
    Route::get('customer/rewards/active', [RewardsController::class, 'active']);
    Route::post('customer/rewards/redeem', [RewardsController::class, 'redeem']);
    Route::get('customer/rewards/history', [RewardsController::class, 'history']);

    // Customer notifications (Inbox)
    Route::get('customer/notifications', function (Request $request) {
        $customer = $request->user(); // auth:customer => خلاص

        return \App\Models\Notification::forCustomer($customer->id)
            ->orderByDesc('sent_at')
            ->get();
    });
});

/*
|--------------------------------------------------------------------------
| Staff Auth (No Auth)
|--------------------------------------------------------------------------
*/
Route::prefix('staff/auth')->group(function () {
    Route::post('register', [StaffAuthController::class, 'register']);
    Route::post('login', [StaffAuthController::class, 'login']);

    Route::post('forgot-password', [StaffPasswordController::class, 'forgotPassword']);
    Route::post('verify-otp', [StaffPasswordController::class, 'verifyOtp']);
    Route::post('resend-otp', [StaffPasswordController::class, 'resendOtp']);
    Route::post('reset-password', [StaffPasswordController::class, 'resetPassword']);
});

/*
|--------------------------------------------------------------------------
| Staff Protected (auth:staff)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:staff')->group(function () {

    // Staff Logout
    Route::post('staff/auth/logout', [StaffAuthController::class, 'logout']);

     Route::put('/owner/branch/profile', 
        [StaffAuthController::class, 'updateBranchProfile']
    );
    Route::get('/owner/branch/profile', 
        [StaffAuthController::class, 'getBranchProfile']
    );

    // Owner only
    Route::middleware('owner')->group(function () {
        Route::post('staff/rbac/roles', [RoleController::class, 'store']);
        Route::put('staff/rbac/roles/{role}', [RoleController::class, 'update']);
        Route::delete('staff/rbac/roles/{role}', [RoleController::class, 'destroy']);
        Route::get('staff/audit-logs', [RestaurantStaffAuditLogController::class, 'index']);
        Route::get('staff/audit-logs/{log}', [RestaurantStaffAuditLogController::class, 'show']);

            // Assign role to staff
            Route::put('staff/{staff}/role', [StaffRoleController::class, 'assign']);
        });

    // ====== STAFF ME (اختبار + UI) ======
    Route::get('staff/me', function () {
        $staff = auth('staff')->user();

        $assignment = \App\Models\RestaurantStaffRoleAssignment::with('role.permissions')
            ->where('staff_id', $staff->id)
            ->first();

        return response()->json([
            'data' => [
                'id' => $staff->id,
                'name' => $staff->name,
                'email' => $staff->email,
                'restaurant_id' => $staff->restaurant_id,
                'role' => $assignment?->role?->name,
                'permissions' => $assignment?->role?->permissions
                    ?->pluck('key')
                    ->values() ?? [],
            ],
        ]);
    });

    Route::prefix('staff/rbac')
        ->middleware('staff.permission:rbac.manage')
        ->group(function () {

            // Permissions CRUD
            Route::get('permissions', [PermissionController::class, 'index']);
            Route::post('permissions', [PermissionController::class, 'store']);
            Route::put('permissions/{permission}', [PermissionController::class, 'update']);
            Route::delete('permissions/{permission}', [PermissionController::class, 'destroy']);

            // Roles (داخل مطعم الـ staff الحالي)
            Route::get('roles', [RoleController::class, 'index']);
            Route::get('roles/{role}/permissions', [RoleController::class, 'showPermissions']);
            Route::put('roles/{role}/permissions', [RoleController::class, 'syncPermissions']);

        });


    Route::put('staff/branch/location', [LocationStaffController::class,'locationAdd']);
    Route::delete('staff/branch/location', [LocationStaffController::class,'destroy']);

    Route::get('staff/tables', [TableController::class, 'index']);
    Route::get('staff/tables/{table}', [TableController::class, 'show']);
    Route::post('staff/tables', [TableController::class, 'store']);
    Route::put('staff/tables/{table}', [TableController::class, 'update']);   // أو PATCH
    Route::delete('staff/tables/{table}', [TableController::class, 'destroy']);

    // Table Status

    Route::get('staff/table-status', [TableStatusController::class, 'index']);
    Route::get('staff/tables/{table}/status', [TableStatusController::class, 'showByTable']);
    Route::put('staff/tables/{table}/status', [TableStatusController::class, 'upsertByTable']);


    Route::get('staff/offers', [OfferController::class, 'index']);
    Route::get('staff/offers/{offer}', [OfferController::class, 'show']);

    
   Route::post('staff/offers', [OfferController::class, 'store']);
   Route::put('staff/offers/{offer}', [OfferController::class, 'update']);
   Route::delete('staff/offers/{offer}', [OfferController::class, 'destroy']);


    // Staff Reservations Management (Private)
    Route::apiResource('reservations', ReservationController::class);

    Route::post('reservations/{reservation}/confirm', [ReservationController::class, 'confirm']);
    Route::post('reservations/{reservation}/cancel', [ReservationController::class, 'cancel']);
    Route::post('reservations/{reservation}/seat', [ReservationController::class, 'seat']);
    Route::post('reservations/{reservation}/complete', [ReservationController::class, 'complete']);
});
