<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AppReservationController extends Controller
{
    private bool $mockMode = true;

    public function __construct()
    {
        if (!session()->has('mock_reservations')) {
            session(['mock_reservations' => []]);
        }
    }

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

        $all = session('mock_reservations');
        $items = array_filter($all, fn($r) => $r['date'] === $date);

        return $this->ok('Home reservations', [
            'date' => $date,
            'stats' => [
                'bookings' => count($items),
                'pending'  => count(array_filter($items, fn($r) => $r['status'] === 'pending')),
            ],
            'items' => array_values($items),
        ]);
    }

    public function index(Request $request)
    {
        $status = $request->query('status');
        $date   = $request->query('date');

        $items = session('mock_reservations');

        if ($status) {
            $items = array_filter($items, fn($r) => $r['status'] === $status);
        }
        if ($date) {
            $items = array_filter($items, fn($r) => $r['date'] === $date);
        }

        return $this->ok('Reservations list', [
            'filters' => [
                'status' => $status,
                'date' => $date,
            ],
            'pagination' => [
                'page' => (int)($request->query('page', 1)),
                'per_page' => (int)($request->query('per_page', 10)),
                'total' => count($items),
            ],
            'items' => array_values($items),
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
        $data['id'] = random_int(1000, 9999);
        $data['created_at'] = now()->toDateTimeString();
        $data['updated_at'] = now()->toDateTimeString();

        // حفظ في session
        $mock = session('mock_reservations');
        $mock[] = $data;
        session(['mock_reservations' => $mock]);

        return $this->ok('Reservation created successfully (MOCK)', $data, 201);
    }

    public function update(Request $request, $id)
    {
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

        $mock = session('mock_reservations');
        foreach ($mock as &$r) {
            if ($r['id'] == $id) {
                $r = array_merge($r, $data);
                $r['updated_at'] = now()->toDateTimeString();
                session(['mock_reservations' => $mock]);
                return $this->ok('Reservation updated successfully (MOCK)', $r);
            }
        }

        return $this->fail('Reservation not found', null, 404);
    }

    public function destroy($id)
    {
        $mock = session('mock_reservations');
        $mock = array_filter($mock, fn($r) => $r['id'] != $id);
        session(['mock_reservations' => array_values($mock)]);

        return $this->ok('Reservation deleted successfully (MOCK)', ['id' => (int)$id]);
    }

    public function confirm($id)
    {
        $mock = session('mock_reservations');
        foreach ($mock as &$r) {
            if ($r['id'] == $id) {
                $r['status'] = 'confirmed';
                $r['updated_at'] = now()->toDateTimeString();
                session(['mock_reservations' => $mock]);
                return $this->ok('Reservation confirmed (MOCK)', $r);
            }
        }

        return $this->fail('Reservation not found', null, 404);
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

        $mock = session('mock_reservations');
        foreach ($mock as &$r) {
            if ($r['id'] == $id) {
                $r['status'] = 'cancelled';
                $r['reason'] = $data['reason'] ?? null;
                $r['cancelled_at'] = now()->toDateTimeString();
                session(['mock_reservations' => $mock]);
                return $this->ok('Reservation cancelled (MOCK)', $r);
            }
        }

        return $this->fail('Reservation not found', null, 404);
    }
}
