<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * GET /customers
     */
    public function index(Request $request)
    {
        $q = $request->query('q');
        $perPage = min($request->integer('per_page', 15), 100);

        $paginator = Customer::query()
            ->with(['loyalties', 'reservations', 'visits', 'waitlists'])
            ->when($q, function ($query) use ($q) {
                $query->where(function ($q2) use ($q) {
                    $q2->where('name', 'like', "%{$q}%")
                       ->orWhere('phone', 'like', "%{$q}%")
                       ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('id')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Customers fetched successfully',
            'data'    => $paginator->items(),
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
            ],
        ]);
    }

    /**
     * GET /customers/{id}
     */
    public function show(Customer $customer)
    {
        $customer->load(['loyalties', 'reservations', 'visits', 'waitlists']);

        return response()->json([
            'success' => true,
            'message' => 'Customer fetched successfully',
            'data'    => $customer,
        ]);
    }
}
