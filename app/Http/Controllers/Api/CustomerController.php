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
    $perPage = $request->integer('per_page', 15);

    $paginator = Customer::query()->with(['Loyalties','reservations','visits','waitlists'])
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
        'data' => $paginator->items(), 
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
            'data' => $customer,
        ]);
    }
}
