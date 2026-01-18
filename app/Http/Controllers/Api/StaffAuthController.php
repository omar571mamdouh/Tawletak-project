<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\RestaurantStaff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class StaffAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required','email'],
            'password' => ['required','string'],
        ]);

        $staff = RestaurantStaff::where('email', $request->email)
            ->where('is_active', true)
            ->first();

        if (!$staff || !Hash::check($request->password, $staff->password_hash)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        // (اختياري) امسح توكنات قديمة
        // $staff->tokens()->delete();

        $token = $staff->createToken('staff-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'staff' => [
                'id' => $staff->id,
                'name' => $staff->name,
                'email' => $staff->email,
                'role' => $staff->role,
                'restaurant_id' => $staff->restaurant_id,
                'branch_id' => $staff->branch_id,
            ],
        ]);
    }

   public function logout(Request $request)
{
    $user = auth('staff')->user();
    $user?->currentAccessToken()?->delete();

    return response()->json(['message' => 'Logged out']);
}

}
