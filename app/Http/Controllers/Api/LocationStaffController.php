<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RestaurantBranch;
use Illuminate\Http\Request;

class LocationStaffController extends Controller
{
    /**
     * PUT /staff/branch/location
     * Updates (or sets) the location for the authenticated staff's own branch.
     */
    public function locationAdd(Request $request)
    {
        $staff = auth('staff')->user();

        if (!$staff) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        if (!$staff->branch_id) {
            return response()->json([
                'success' => false,
                'message' => 'This staff account is not assigned to any branch.',
            ], 422);
        }

        // Get the staff's branch and ensure it belongs to the same restaurant
        $branch = RestaurantBranch::where('id', $staff->branch_id)
            ->where('restaurant_id', $staff->restaurant_id)
            ->first();

        if (!$branch) {
            return response()->json([
                'success' => false,
                'message' => 'Branch not found for this staff.',
            ], 404);
        }

        // Validate incoming location data
        $data = $request->validate([
            'address' => ['nullable', 'string', 'max:255'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        // Update branch location
        $branch->update([
            'address' => $data['address'] ?? $branch->address,
            'lat' => (string) $data['lat'],
            'lng' => (string) $data['lng'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Branch location updated successfully.',
            'data' => [
                'restaurant_id' => $branch->restaurant_id,
                'branch' => [
                    'id' => $branch->id,
                    'name' => $branch->name,
                    'address' => $branch->address,
                    'lat' => $branch->lat,
                    'lng' => $branch->lng,
                    'timezone' => $branch->timezone,
                    'is_active' => (bool) $branch->is_active,
                    'updated_at' => $branch->updated_at,
                ],
            ],
        ]);
    }

    /**
     * DELETE /staff/branch/location
     * Optional: clears the location fields for the authenticated staff's branch.
     */
    public function destroy(Request $request)
    {
        $staff = auth('staff')->user();

        if (!$staff) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        if (!$staff->branch_id) {
            return response()->json([
                'success' => false,
                'message' => 'This staff account is not assigned to any branch.',
            ], 422);
        }

        $branch = RestaurantBranch::where('id', $staff->branch_id)
            ->where('restaurant_id', $staff->restaurant_id)
            ->first();

        if (!$branch) {
            return response()->json([
                'success' => false,
                'message' => 'Branch not found for this staff.',
            ], 404);
        }

        $branch->update([
            'address' => '',
            'lat' => 0,
            'lng' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Branch location cleared successfully.',
            'data' => [
                'restaurant_id' => $branch->restaurant_id,
                'branch_id' => $branch->id,
            ],
        ]);
    }
}
