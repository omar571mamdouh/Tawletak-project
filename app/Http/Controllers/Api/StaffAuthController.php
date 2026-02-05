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
use Illuminate\Validation\Rule;


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
    // 1) Validate common staff fields + conditional fields
    $data = $request->validate([
        // common
        'name'     => ['required','string','max:200'],
        'phone'    => ['nullable','string','max:50'],
        'email'    => ['nullable','email','max:200','unique:restaurant_staff,email'],
        'password' => ['required','string','min:8','confirmed'],
        'category' => ['required','string','max:100'],
        'opening_time'  => ['required','date_format:H:i'],
        'closing_time'  => ['required','date_format:H:i'],

        // staff-path (existing restaurant)
        'restaurant_id' => [
            'nullable',
            'integer',
            // Rule::exists('restaurants','id') // فعّلها لو عندك جدول restaurants
        ],
        'branch_id' => [
            'nullable',
            'integer',
            // Rule::exists('restaurant_branches','id') // فعّلها حسب جدول الفروع عندك
        ],

        // owner-path (create restaurant)
        'restaurant_name' => ['nullable','string','max:255'],
        'branch_name'     => ['nullable','string','max:255'],
        'address'         => ['nullable','string','max:255'],
        'lat'             => ['nullable','numeric'],
        'lng'             => ['nullable','numeric'],
    ]);

    // 2) Decide which flow:
    $isOwnerCreate = !empty($data['restaurant_name']);

    // 3) Guardrails: ممنوع تبعت الاتنين مع بعض
    if ($isOwnerCreate && !empty($data['restaurant_id'])) {
        return response()->json([
            'success' => false,
            'message' => 'Do not send restaurant_id when restaurant_name is provided.',
        ], 422);
    }

    // لو مش owner-create يبقى لازم restaurant_id
    if (!$isOwnerCreate && empty($data['restaurant_id'])) {
        return response()->json([
            'success' => false,
            'message' => 'restaurant_id is required when restaurant_name is not provided.',
        ], 422);
    }

    return DB::transaction(function () use ($data, $isOwnerCreate) {

        $restaurant = null;
        $branch = null;

        if ($isOwnerCreate) {
            // ========= OWNER FLOW =========
            // 1) Create Restaurant
            $restaurant = \App\Models\Restaurant::create([
                'name' => $data['restaurant_name'],
                'category' => $data['category'],
            ]);

            // 2) Create Branch (اختياري)
            $branch = \App\Models\RestaurantBranch::create([
                'restaurant_id' => $restaurant->id,
                'name'          => $data['branch_name'] ?? 'Main Branch',
                'address'       => $data['address'] ?? null,
                'lat'           => $data['lat'] ?? null,
                'lng'           => $data['lng'] ?? null,
                'opening_time'  => $data['opening_time'],
                'closing_time'  => $data['closing_time'],
            ]);

            $restaurantId = $restaurant->id;
            $branchId     = $branch->id;

            $roleName = 'owner'; // ✅ internal default
        } else {
            // ========= STAFF FLOW =========
            $restaurantId = $data['restaurant_id'];
            $branchId     = $data['branch_id'] ?? null;

            $roleName = 'staff'; // ✅ internal default (تقدر تخليها manager حسب منطقك)
        }

        // 4) Create staff
        $staff = \App\Models\RestaurantStaff::create([
            'restaurant_id' => $restaurantId,
            'branch_id'     => $branchId,
            'name'          => $data['name'],
            'phone'         => $data['phone'] ?? null,
            'email'         => $data['email'] ?? null,
            'password_hash' => Hash::make($data['password']),
            'is_active'     => true,
        ]);

        // 5) Ensure role exists for that restaurant
        $role = \App\Models\RestaurantRole::query()
            ->where('restaurant_id', $restaurantId)
            ->where('name', $roleName)
            ->first();

        if (!$role) {
            $role = \App\Models\RestaurantRole::create([
                'restaurant_id' => $restaurantId,
                'name' => $roleName,
            ]);
        }

        // 6) Assign role to staff
        \App\Models\RestaurantStaffRoleAssignment::updateOrCreate(
            ['staff_id' => $staff->id],
            ['restaurant_role_id' => $role->id]
        );

        // 7) Token
        $token = $staff->createToken('staff-token')->plainTextToken;

        // 8) Response (بدون role)
        return response()->json([
            'success' => true,
            'message' => $isOwnerCreate ? 'Owner registered successfully' : 'Staff registered successfully',
            'data' => [
                'token' => $token,
                'staff' => [
                    'id' => $staff->id,
                    'name' => $staff->name,
                    'email' => $staff->email,
                    'restaurant_id' => $staff->restaurant_id,
                    'branch_id' => $staff->branch_id,
                ],
                // لو owner-create رجّع restaurant/branch info (اختياري بس مفيد)
                'restaurant' => $restaurant ? [
                    'id' => $restaurant->id,
                    'name' => $restaurant->name,
                ] : null,
                'branch' => $branch ? [
                    'id' => $branch->id,
                    'name' => $branch->name,
                    'address' => $branch->address ?? null,
                    'lat' => $branch->lat ?? null,
                    'lng' => $branch->lng ?? null,
                ] : null,
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
