<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DeviceTokenController extends Controller
{
    /**
     * Store FCM device token (Admin - Filament/Web)
     * NOTE: الأفضل تخلي Route دي في web.php مع session auth، مش api.php
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'token'    => 'required|string|max:512',
            'platform' => 'nullable|string|max:20',
        ]);

        // ✅ Filament session first
        $user = Auth::guard('filament')->user() ?? Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $existing = DeviceToken::where('token', $data['token'])->first();

        $deviceToken = DeviceToken::updateOrCreate(
            ['token' => $data['token']],
            [
                'owner_type' => 'admin',
                'owner_id'   => $user->id,
                'platform'   => $data['platform'] ?? 'web',
            ]
        );

        // لو التوكن كان مملوك لحد تاني قبل كده
        if ($existing && ($existing->owner_type !== 'admin' || (int) $existing->owner_id !== (int) $user->id)) {
            Log::info('FCM token ownership transferred (admin)', [
                'token'     => substr($data['token'], 0, 20) . '...',
                'from_type' => $existing->owner_type,
                'from_id'   => $existing->owner_id,
                'to_type'   => 'admin',
                'to_id'     => $user->id,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Device token saved successfully',
            'data' => [
                'id'       => $deviceToken->id,
                'owner_id' => $deviceToken->owner_id,
                'platform' => $deviceToken->platform,
            ],
        ]);
    }

    /**
     * Remove FCM device token (Admin logout)
     */
    public function destroy(Request $request)
    {
        $data = $request->validate([
            'token' => 'required|string|max:512',
        ]);

        $user = Auth::guard('filament')->user() ?? Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $deleted = DeviceToken::where('token', $data['token'])
            ->where('owner_type', 'admin')
            ->where('owner_id', $user->id)
            ->delete();

        if ($deleted) {
            Log::info('FCM token removed successfully (admin)', [
                'owner_type' => 'admin',
                'owner_id'   => $user->id,
                'token'      => substr($data['token'], 0, 20) . '...',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Device token removed successfully',
        ]);
    }

    /**
     * Store FCM device token (Customer - Mobile)
     */
    public function storeCustomer(Request $request)
{
    $data = $request->validate([
        'token'    => 'required|string|max:512',
        'platform' => 'nullable|string|max:20',
    ]);

    $customer = $request->user(); // ✅ Sanctum

    if (!$customer || !($customer instanceof \App\Models\Customer)) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthenticated',
        ], 401);
    }

    $deviceToken = \App\Models\DeviceToken::updateOrCreate(
        ['token' => $data['token']],
        [
            'owner_type' => 'customer',
            'owner_id'   => $customer->id,
            'platform'   => $data['platform'] ?? 'mobile',
        ]
    );

    return response()->json([
        'success' => true,
        'message' => 'Device token saved successfully',
    ]);
}


    /**
     * Remove FCM device token (Customer logout)
     */
    public function destroyCustomer(Request $request)
    {
        $data = $request->validate([
            'token' => 'required|string|max:512',
        ]);

        $customer = Auth::guard('customer')->user();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $deleted = DeviceToken::where('token', $data['token'])
            ->where('owner_type', 'customer')
            ->where('owner_id', $customer->id)
            ->delete();

        if ($deleted) {
            Log::info('FCM token removed successfully (customer)', [
                'owner_type' => 'customer',
                'owner_id'   => $customer->id,
                'token'      => substr($data['token'], 0, 20) . '...',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Device token removed successfully',
        ]);
    }
}
