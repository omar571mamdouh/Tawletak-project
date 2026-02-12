<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppReservation; // ⬅️ هنا التغيير
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AppReservationController extends Controller
{
    private function ok(string $message, $data = null, int $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    private function fail(string $message, $errors = null, int $code = 422)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }

    public function home(Request $request)
    {
        $date = $request->query('date') ?? now()->toDateString();

        $items = AppReservation::where('date', $date)->get();

        return $this->ok('Home reservations', [
            'date' => $date,
            'stats' => [
                'bookings' => $items->count(),
                'pending'  => $items->where('status', 'pending')->count(),
            ],
            'items' => $items,
        ]);
    }

    public function index(Request $request)
    {
        $query = AppReservation::query();

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($date = $request->query('date')) {
            $query->where('date', $date);
        }

        $items = $query->get();

        return $this->ok('Reservations list', [
            'filters' => [
                'status' => $status ?? null,
                'date' => $date ?? null,
            ],
            'pagination' => [
                'page' => (int)($request->query('page', 1)),
                'per_page' => (int)($request->query('per_page', 10)),
                'total' => $items->count(),
            ],
            'items' => $items,
        ]);
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'restaurant_id'  => ['nullable','integer'],
                'customer_name'  => ['required','string','max:255'],
                'customer_phone' => ['nullable','string','max:50'],
                'date'           => ['required','date'],
                'time'           => ['required'],
                'guests_count'   => ['required','integer','min:1'],
                'table_id'       => ['nullable','integer'],
            ]);
        } catch (ValidationException $e) {
            return $this->fail('Validation error', $e->errors(), 422);
        }

        $data['code'] = 'CODE-' . strtoupper(Str::random(6));
        $data['status'] = 'pending';

        $reservation = AppReservation::create($data);

        return $this->ok('Reservation created successfully', $reservation, 201);
    }

    public function update(Request $request, $id)
    {
        $reservation = AppReservation::find($id);
        
        if (!$reservation) {
            return $this->fail('Reservation not found', null, 404);
        }

        try {
            $data = $request->validate([
                'customer_name'  => ['sometimes','string','max:255'],
                'customer_phone' => ['sometimes','nullable','string','max:50'],
                'date'           => ['sometimes','date'],
                'time'           => ['sometimes'],
                'guests_count'   => ['sometimes','integer','min:1'],
                'table_id'       => ['sometimes','nullable','integer'],
                'status'         => ['sometimes','in:pending,confirmed,cancelled'],
            ]);
        } catch (ValidationException $e) {
            return $this->fail('Validation error', $e->errors(), 422);
        }

        $reservation->update($data);

        return $this->ok('Reservation updated successfully', $reservation);
    }

    public function destroy($id)
    {
        $reservation = AppReservation::find($id);
        
        if (!$reservation) {
            return $this->fail('Reservation not found', null, 404);
        }

        $reservation->delete();

        return $this->ok('Reservation deleted successfully', ['id' => (int)$id]);
    }

    public function confirm($id)
    {
        $reservation = AppReservation::find($id);
        
        if (!$reservation) {
            return $this->fail('Reservation not found', null, 404);
        }

        $reservation->update(['status' => 'confirmed']);

        return $this->ok('Reservation confirmed', $reservation);
    }

    public function cancel(Request $request, $id)
    {
        $reservation = AppReservation::find($id);
        
        if (!$reservation) {
            return $this->fail('Reservation not found', null, 404);
        }

        try {
            $data = $request->validate([
                'reason' => ['nullable','string','max:1000'],
            ]);
        } catch (ValidationException $e) {
            return $this->fail('Validation error', $e->errors(), 422);
        }

        $reservation->update([
            'status' => 'cancelled',
            'reason' => $data['reason'] ?? null,
            'cancelled_at' => now(),
        ]);

        return $this->ok('Reservation cancelled', $reservation);
    }
}