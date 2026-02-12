<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppReservation; // ⬅️ هنا التغيير
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Cache;

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
    $data = $request->validate([
        'lat' => ['nullable', 'numeric'],
        'lng' => ['nullable', 'numeric'],
        'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
    ]);

    $limit = (int) ($data['limit'] ?? 10);

    // ✅ user location for distance calculation
    $userLat = $request->filled('lat') ? (double) $request->lat : null;
    $userLng = $request->filled('lng') ? (double) $request->lng : null;

    // ✅ get logged customer favorites from cache
    $customer = $request->user('customer');
    $favorites = [];

    if ($customer) {
        $key = "favorites:user:{$customer->id}";
        $favorites = Cache::get($key, []);
    }

    // ✅ get recommended restaurants (active only)
    $restaurants = Restaurant::query()
        ->select([
            'id',
            'name',
            'description',
            'phone',
            'category',
            'price_range',
            'is_active',
            'created_at',
            'updated_at',
            // 'logo', // لو عندك
            // 'cover_image', // لو عندك
        ])
        ->with(['branches:id,restaurant_id,address,lat,lng,is_active']) // ✅ شيلت name و city
        ->where('is_active', true)
        ->latest()
        ->limit($limit)
        ->get();

    // ✅ haversine distance calculation
    $haversineKm = function (float $lat1, float $lon1, float $lat2, float $lon2): float {
        $earth = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2)
           + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
           * sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earth * $c;
    };

    // ✅ transform restaurants to match mobile UI
    $recommended = $restaurants->map(function ($restaurant) use ($userLat, $userLng, $haversineKm, $favorites) {
        
        // get first active branch
        $branch = $restaurant->branches->where('is_active', true)->first();

        $branchLat = ($branch && $branch->lat !== null) ? (double) $branch->lat : null;
        $branchLng = ($branch && $branch->lng !== null) ? (double) $branch->lng : null;

        // calculate distance
        $distanceKm = null;
        if ($userLat !== null && $userLng !== null && $branchLat !== null && $branchLng !== null) {
            $distanceKm = (double) round($haversineKm($userLat, $userLng, $branchLat, $branchLng), 1);
        }

        // ✅ check if restaurant is in favorites
        $isFav = collect($favorites)
            ->pluck('restaurant_id')
            ->contains($restaurant->id);

        return [
            'id' => $restaurant->id,
            'name' => $restaurant->name,
            'description' => $restaurant->description,
            'category' => $restaurant->category,
            'price_range' => $restaurant->price_range,
            
            // ✅ images (غيّر حسب نظامك)
            'cover_image' => null, // TODO: لو عندك cover_image في restaurants table
            'logo' => null, // TODO: لو عندك logo في restaurants table
            
            // ✅ rating (لو عندك reviews table)
            'rating' => 4.5, // TODO: احسبه من reviews
            'reviews_count' => 0, // TODO: احسبه من reviews
            
            // ✅ location info
            'location' => [
                'address' => $branch?->address,
                'city' => 'Amman, Jordan', // ✅ static لحد ما تضيف city column
                'lat' => $branchLat,
                'lng' => $branchLng,
            ],
            
            // ✅ distance
            'distance_km' => $distanceKm,
            'distance_text' => $distanceKm ? "{$distanceKm} km away" : null,
            
            // ✅ availability
            'tables_available' => true, // TODO: احسبه من available_time
            'availability_status' => 'Tables Available', // أو 'Few Tables Left' أو 'Fully Booked'
            
            // ✅ favorite status
            'is_fav' => $isFav,
        ];
    });

    return response()->json([
        'success' => true,
        'data' => [
            'title' => 'Recommended for You',
            'restaurants' => $recommended,
            'total' => $recommended->count(),
        ],
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