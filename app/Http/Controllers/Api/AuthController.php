<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\RestaurantStaff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function loginAdmin(Request $request)
    {
        $data = $request->validate([
            'email' => ['required','email'],
            'password' => ['required','string'],
        ]);

        $admin = User::where('email', $data['email'])->first();

        if (!$admin || !$admin->is_active || !Hash::check($data['password'], $admin->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        // abilities للادمن
        $token = $admin->createToken('admin-token', ['admin'])->plainTextToken;

        return response()->json([
            'type' => 'admin',
            'token' => $token,
            'user' => $admin,
        ]);
    }

    public function loginStaff(Request $request)
    {
        $data = $request->validate([
            'email_or_phone' => ['required','string'],
            'password' => ['required','string'],
        ]);

        $staff = RestaurantStaff::query()
            ->where('email', $data['email_or_phone'])
            ->orWhere('phone', $data['email_or_phone'])
            ->first();

        if (!$staff || !$staff->is_active || empty($staff->password_hash) || !Hash::check($data['password'], $staff->password_hash)) {
            throw ValidationException::withMessages([
                'email_or_phone' => ['Invalid credentials.'],
            ]);
        }

        // abilities حسب role
        $abilities = match ($staff->role) {
            'owner'   => ['staff', 'staff:owner'],
            'manager' => ['staff', 'staff:manager'],
            'host'    => ['staff', 'staff:host'],
            default   => ['staff'],
        };

        $token = $staff->createToken('staff-token', $abilities)->plainTextToken;

        return response()->json([
            'type' => 'staff',
            'token' => $token,
            'staff' => $staff,
        ]);
    }

    public function me(Request $request)
    {
        $u = $request->user();

        return response()->json([
            'model' => class_basename($u), // User أو RestaurantStaff
            'data' => $u,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out',
        ]);
    }
}
