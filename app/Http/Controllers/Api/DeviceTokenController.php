<?php

namespace App\Http\Controllers;

use App\Models\DeviceToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class DeviceTokenController extends Controller
{
    /**
     * Store FCM device token (Filament session)
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
            'message' => 'Unauthenticated'
        ], 401);
    }

    DeviceToken::updateOrCreate(
        ['token' => $data['token']],
        [
            'owner_type' => 'admin',
            'owner_id'   => $user->id,
            'platform'   => $data['platform'] ?? 'web',
        ]
    );

    return response()->json(['success' => true]);
}

    /**
     * Remove FCM device token (on logout)
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
                'message' => 'Unauthenticated'
            ], 401);
        }

        try {
            // ✅ احذف توكن المستخدم الحالي فقط (أأمن)
            $deleted = DeviceToken::where('token', $data['token'])
                ->where('owner_type', 'admin')
                ->where('owner_id', $user->id)
                ->delete();

            if ($deleted) {
                Log::info('FCM token removed successfully', [
                    'owner_type' => 'admin',
                    'owner_id'   => $user->id,
                    'token'      => substr($data['token'], 0, 20) . '...',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Device token removed successfully',
            ]);

        } catch (\Throwable $e) {
            Log::error('Failed to remove FCM token', [
                'error'   => $e->getMessage(),
                'user_id' => $user?->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove device token',
            ], 500);
        }
    }
}
