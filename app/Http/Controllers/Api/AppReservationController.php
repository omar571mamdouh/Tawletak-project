<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AppReservationController extends Controller
{
    /**
     * Toggle this:
     * - true  => mock mode (no DB)
     * - false => DB mode (we'll add later when you create table)
     */
    private bool $mockMode = true;

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
        // Mock dynamic-like payload
        $date = $request->query('date') ?? now()->toDateString();

        return $this->ok('Home reservations', [
            'date' => $date,
            'stats' => [
                'bookings' => 0,
                'pending'  => 0,
            ],
            'items' => [], // later from DB
        ]);
    }

    public function cancellations(Request $request)
    {
        return $this->ok('Recent cancellations', [
            'items' => [], // later from DB
        ]);
    }

    public function index(Request $request)
    {
        // filters (ready for mobile)
        $status = $request->query('status'); // pending/confirmed/cancelled
        $date   = $request->query('date');

        return $this->ok('Reservations list', [
            'filters' => [
                'status' => $status,
                'date' => $date,
            ],
            'pagination' => [
                'page' => (int)($request->query('page', 1)),
                'per_page' => (int)($request->query('per_page', 10)),
                'total' => 0,
            ],
            'items' => [], // later from DB
        ]);
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'restaurant_id'  => ['nullable','integer'], // خليه nullable دلوقتي
                'customer_name'  => ['required','string','max:255'],
                'customer_phone' => ['nullable','string','max:50'],
                'date'           => ['required','date'],
                'time'           => ['required'], // keep simple
                'guests_count'   => ['required','integer','min:1'],
                'table_id'       => ['nullable','integer'],
            ]);
        } catch (ValidationException $e) {
            return $this->fail('Validation error', $e->errors(), 422);
        }

        // generate code like CODE-XXXXXX
        $data['code'] = 'CODE-' . strtoupper(Str::random(6));
        $data['status'] = 'pending';

        // mock "id"
        $data['id'] = random_int(1000, 9999);
        $data['created_at'] = now()->toDateTimeString();
        $data['updated_at'] = now()->toDateTimeString();

        // ✅ In mock mode: return what mobile needs
        if ($this->mockMode) {
            return $this->ok('Reservation created successfully (MOCK)', $data, 201);
        }

        // DB mode will be added later (when you create table)
        return $this->fail('DB mode not enabled yet', null, 501);
    }

    public function update(Request $request, $id)
    {
        // validate partial update
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

        // mock response
        return $this->ok('Reservation updated successfully (MOCK)', [
            'id' => (int)$id,
            'updated_fields' => $data,
            'updated_at' => now()->toDateTimeString(),
        ]);
    }

    public function destroy($id)
    {
        return $this->ok('Reservation deleted successfully (MOCK)', [
            'id' => (int)$id,
        ]);
    }

    public function confirm($id)
    {
        return $this->ok('Reservation confirmed (MOCK)', [
            'id' => (int)$id,
            'status' => 'confirmed',
            'updated_at' => now()->toDateTimeString(),
        ]);
    }

    public function cancel(Request $request, $id)
    {
        try {
            $data = $request->validate([
                'reason' => ['nullable','string','max:1000'],
            ]);
        } catch (ValidationException $e) {
            return $this->fail('Validation error', $e->errors(), 422);
        }

        return $this->ok('Reservation cancelled (MOCK)', [
            'id' => (int)$id,
            'status' => 'cancelled',
            'reason' => $data['reason'] ?? null,
            'cancelled_at' => now()->toDateTimeString(),
        ]);
    }
}
