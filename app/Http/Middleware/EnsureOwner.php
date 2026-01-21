<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\RestaurantRole;

class EnsureOwner
{
    /**
     * Ensure authenticated staff is an owner in their restaurant.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $staff = auth('staff')->user();

        // لو مش عامل auth صح
        if (!$staff) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        /**
         * خيار 1 (لو العلاقات متظبطة):
         * $roleName = optional($staff->role)->name;
         *
         * خيار 2 (مضمون حتى لو العلاقات مش متظبطة):
         */
        $roleName = RestaurantRole::query()
            ->join('restaurant_staff_role_assignments as a', 'a.restaurant_role_id', '=', 'restaurant_roles.id')
            ->where('a.staff_id', $staff->id)
            ->value('restaurant_roles.name');

        if ($roleName !== 'owner') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return $next($request);
    }
}
