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

        $token = $staff->createToken('staff-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'token' => $token,
                'staff' => [
                    'id' => $staff->id,
                    'name' => $staff->name,
                    'email' => $staff->email,
                    'role' => $staff->role,
                    'restaurant_id' => $staff->restaurant_id,
                    'branch_id' => $staff->branch_id,
                ],
            ],
        ]);
    }

    public function register(Request $request)
{
    $data = $request->validate([
        'restaurant_id' => ['required','integer'],
        'branch_id'     => ['nullable','integer'],
        'name'          => ['required','string','max:200'],
        'phone'         => ['nullable','string','max:50'],
        'email'         => ['nullable','email','max:200','unique:restaurant_staff,email'],
        'password'      => ['required','string','min:8','confirmed'],
        'role'          => ['required','in:owner,manager,staff'],
    ]);

    $staff = RestaurantStaff::create([
        'restaurant_id' => $data['restaurant_id'],
        'branch_id'     => $data['branch_id'] ?? null,
        'name'          => $data['name'],
        'phone'         => $data['phone'] ?? null,
        'email'         => $data['email'] ?? null,
        'password_hash' => Hash::make($data['password']),
        'role'          => $data['role'],
        'is_active'     => true,
    ]);

    $token = $staff->createToken('staff-token')->plainTextToken;

    return response()->json([
        'success' => true,
        'message' => 'Staff registered successfully',
        'data' => [
            'token' => $token,
            'staff' => [
                'id' => $staff->id,
                'name' => $staff->name,
                'email' => $staff->email,
                'role' => $staff->role,
                'restaurant_id' => $staff->restaurant_id,
                'branch_id' => $staff->branch_id,
            ],
        ],
    ], 201);
}


    public function logout(Request $request)
{
    $staff = auth('staff')->user();

    if (!$staff) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthenticated (missing/invalid staff token)'
        ], 401);
    }

    $staff->currentAccessToken()?->delete();

    return response()->json([
        'success' => true,
        'message' => 'Logged out'
    ]);
}

}
