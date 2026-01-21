<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReservationResource;
use App\Models\Reservation;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReservationController extends Controller
{
    private function allowedStatuses(): array
    {
       
        return ['pending', 'confirmed', 'cancelled', 'seated', 'completed', 'no_show'];
    }

    public function index(Request $request)
{

$staffRestaurantId = auth('staff')->user()->restaurant_id;

    $with = ['customer', 'branch', 'table'];

    if ($request->boolean('include_events')) {
        $with[] = 'events';
    }

   
    if ($request->boolean('include_redemptions')) {
        $with[] = 'offerRedemptions';
    }

    $q = Reservation::query()->with($with);

    $q->whereHas('table', fn($query) => $query->where('restaurant_id', $staffRestaurantId));

    if ($request->filled('status')) {
        $q->where('status', $request->status);
    }

    if ($request->filled('branch_id')) {
        $q->where('branch_id', $request->branch_id);
    }

    if ($request->filled('customer_id')) {
        $q->where('customer_id', $request->customer_id);
    }

    if ($request->filled('from')) {
        $q->where('reservation_time', '>=', $request->from);
    }

    if ($request->filled('to')) {
        $q->where('reservation_time', '<=', $request->to);
    }

    $reservations = $q->latest('reservation_time')
        ->paginate($request->integer('per_page', 15));

    return ReservationResource::collection($reservations);
}


    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'branch_id'   => ['required', 'exists:restaurant_branches,id'],
            'table_id'    => ['required', 'exists:tables,id'],
            'party_size'  => ['required', 'integer', 'min:1'],
            'reservation_time'          => ['required', 'date'],
            'expected_duration_minutes' => ['nullable', 'integer', 'min:1'],
            'status'      => ['nullable', Rule::in($this->allowedStatuses())],
            'source'      => ['nullable', 'string', 'max:50'],
        ]);

        // default status لو مش مبعوت
        $data['status'] = $data['status'] ?? 'pending';


        $staffRestaurantId = auth('staff')->user()->restaurant_id;

$table = Table::findOrFail($data['table_id']);
if ($table->restaurant_id != $staffRestaurantId) {
    abort(403, 'Forbidden: table not in your restaurant.');
}


        $reservation = Reservation::create($data);
        $reservation->load(['customer', 'branch', 'table']);

        return (new ReservationResource($reservation))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Reservation $reservation)
    {
        $reservation->load(['customer', 'branch', 'table', 'events', 'offerRedemptions']);
        return new ReservationResource($reservation);
    }

    public function update(Request $request, Reservation $reservation)
    {


    $staffRestaurantId = auth('staff')->user()->restaurant_id;

        $data = $request->validate([
            'customer_id' => ['sometimes', 'required', 'exists:customers,id'],
            'branch_id'   => ['sometimes', 'required', 'exists:restaurant_branches,id'],
            'table_id'    => ['sometimes', 'required', 'exists:tables,id'],
            'party_size'  => ['sometimes', 'required', 'integer', 'min:1'],
            'reservation_time'          => ['sometimes', 'required', 'date'],
            'expected_duration_minutes' => ['nullable', 'integer', 'min:1'],
            'status'      => ['nullable', Rule::in($this->allowedStatuses())],
            'source'      => ['nullable', 'string', 'max:50'],
        ]);


        if (isset($data['table_id'])) {
    $table = Table::findOrFail($data['table_id']);
    if ($table->restaurant_id != $staffRestaurantId) {
        abort(403, 'Forbidden: table not in your restaurant.');
    }
}


        $reservation->update($data);
        $reservation->load(['customer', 'branch', 'table']);

        return new ReservationResource($reservation);
    }

    public function destroy(Reservation $reservation)
    {
        $reservation->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }

    // ------- Status actions -------
    public function confirm(Reservation $reservation)
    {
        $reservation->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
            'cancelled_at' => null,
        ]);

        return new ReservationResource($reservation->fresh());
    }

    public function cancel(Request $request, Reservation $reservation)
    {
        $reservation->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return new ReservationResource($reservation->fresh());
    }

    public function seat(Reservation $reservation)
    {
        $reservation->update([
            'status' => 'seated',
            'seated_at' => now(),
        ]);

        return new ReservationResource($reservation->fresh());
    }

    public function complete(Reservation $reservation)
    {
        $reservation->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return new ReservationResource($reservation->fresh());
    }
}
