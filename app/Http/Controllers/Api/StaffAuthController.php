<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RestaurantStaff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\RestaurantRole;
use App\Models\RestaurantStaffRoleAssignment;
use Illuminate\Support\Facades\DB;
use App\Support\RestaurantStaffAudit;


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

        // (اختياري) log لفشل الدخول لو لقيت staff بالإيميل
        if ($staff) {
            RestaurantStaffAudit::logForStaff(
                staff: $staff,
                action: 'staff.auth.login_failed',
                meta: [
                    'email' => $request->input('email'),
                    'device' => $request->header('X-Device-Id'),
                    'app_version' => $request->header('X-App-Version'),
                ],
                statusCode: 422
            );
        }

        throw ValidationException::withMessages([
            'email' => ['Invalid credentials.'],
        ]);
    }

    $token = $staff->createToken('staff-token')->plainTextToken;

    // role من assignments (مش من column)
    $assignment = RestaurantStaffRoleAssignment::with('role')
        ->where('staff_id', $staff->id)
        ->first();

    // ✅ log نجاح الدخول
    RestaurantStaffAudit::logForStaff(
        staff: $staff,
        action: 'staff.auth.login',
        meta: [
            'email' => $request->input('email'),
            'device' => $request->header('X-Device-Id'),
            'app_version' => $request->header('X-App-Version'),
        ],
        after: [
            'staff_id' => $staff->id,
            'role' => $assignment?->role?->name,
        ],
        statusCode: 200
    );

    return response()->json([
        'success' => true,
        'message' => 'Login successful',
        'data' => [
            'token' => $token,
            'staff' => [
                'id' => $staff->id,
                'name' => $staff->name,
                'email' => $staff->email,
                'role' => $assignment?->role?->name,
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

    return DB::transaction(function () use ($data) {

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

        // ✅ هات role record لنفس المطعم
        $role = RestaurantRole::query()
            ->where('restaurant_id', $staff->restaurant_id)
            ->where('name', $staff->role) // owner/manager/staff
            ->first();

        // لو الدور مش موجود (احتياط)
        if (!$role) {
            $role = RestaurantRole::create([
                'restaurant_id' => $staff->restaurant_id,
                'name' => $staff->role,
            ]);
        }

        // ✅ اعمل assignment للستاف
        RestaurantStaffRoleAssignment::updateOrCreate(
            ['staff_id' => $staff->id],
            ['restaurant_role_id' => $role->id]
        );

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
    });
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
