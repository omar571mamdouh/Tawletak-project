<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\RestaurantBranch;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    private function staffRestaurantId(): int
    {
        return (int) auth('staff')->user()->restaurant_id;
    }

    private function assertBranchInStaffRestaurant(int $branchId): void
    {
        $restaurantId = $this->staffRestaurantId();

        $ok = RestaurantBranch::query()
            ->whereKey($branchId)
            ->where('restaurant_id', $restaurantId)
            ->exists();

        abort_unless($ok, 403, 'Forbidden (branch not in your restaurant).');
    }

    private function assertOfferInStaffRestaurant(Offer $offer): void
    {
        $restaurantId = $this->staffRestaurantId();

        $ok = Offer::query()
            ->whereKey($offer->id)
            ->whereHas('branch', fn ($q) => $q->where('restaurant_id', $restaurantId))
            ->exists();

        abort_unless($ok, 403, 'Forbidden (offer not in your restaurant).');
    }

    /* ===========================
       INDEX
    ============================*/
    public function index(Request $request)
    {
        $restaurantId = $this->staffRestaurantId();

        $query = Offer::query()
            ->whereHas('branch', fn ($q) => $q->where('restaurant_id', $restaurantId))
            ->with([
                'branch',
                'redemptions.customer',
                'redemptions.reservation',
                'redemptions.visit',
            ]);

        if ($request->filled('branch_id')) {
            $this->assertBranchInStaffRestaurant((int) $request->branch_id);
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', (bool) $request->is_active);
        }

        if ($request->boolean('active_now')) {
            $now = now();
            $query->where('is_active', true)
                ->where('start_at', '<=', $now)
                ->where('end_at', '>=', $now);
        }

        $query->orderByDesc('start_at');

        $limit = min((int) $request->get('limit', 200), 500);

        return response()->json([
            'success' => true,
            'data' => $query->take($limit)->get(),
        ]);
    }

    /* ===========================
       SHOW
    ============================*/
    public function show(Offer $offer)
    {
        $this->assertOfferInStaffRestaurant($offer);

        $offer->load([
            'branch',
            'redemptions.customer',
            'redemptions.reservation',
            'redemptions.visit',
        ]);

        return response()->json([
            'success' => true,
            'data' => $offer,
        ]);
    }

    /* ===========================
       STORE
    ============================*/
    public function store(Request $request)
    {
        $data = $request->validate([
            'branch_id'              => ['required', 'integer', 'exists:restaurant_branches,id'],
            'title'                  => ['required', 'string', 'max:150'],
            'description'            => ['nullable', 'string', 'max:2000'],
            'discount_type'          => ['required', 'in:percent,fixed'],
            'discount_value'         => ['required', 'numeric', 'min:0'],
            'start_at'               => ['required', 'date'],
            'end_at'                 => ['required', 'date', 'after:start_at'],
            'min_party_size'         => ['nullable', 'integer', 'min:1'],
            'eligible_loyalty_tier'  => ['nullable', 'string', 'max:50'],
            'is_active'              => ['nullable', 'boolean'],
        ]);

        // ✅ branch لازم يكون تبع نفس المطعم
        $this->assertBranchInStaffRestaurant((int) $data['branch_id']);

        $data['is_active'] = $data['is_active'] ?? true;

        $offer = Offer::create($data);

        $offer->load([
            'branch',
            'redemptions.customer',
            'redemptions.reservation',
            'redemptions.visit',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Offer created successfully',
            'data' => $offer,
        ], 201);
    }

    /* ===========================
       UPDATE
    ============================*/
    public function update(Request $request, Offer $offer)
    {
        $this->assertOfferInStaffRestaurant($offer);

        $data = $request->validate([
            'branch_id'              => ['sometimes', 'integer', 'exists:restaurant_branches,id'],
            'title'                  => ['sometimes', 'string', 'max:150'],
            'description'            => ['sometimes', 'nullable', 'string', 'max:2000'],
            'discount_type'          => ['sometimes', 'in:percent,fixed'],
            'discount_value'         => ['sometimes', 'numeric', 'min:0'],
            'start_at'               => ['sometimes', 'date'],
            'end_at'                 => ['sometimes', 'date'],
            'min_party_size'         => ['sometimes', 'integer', 'min:1'],
            'eligible_loyalty_tier'  => ['sometimes', 'nullable', 'string', 'max:50'],
            'is_active'              => ['sometimes', 'boolean'],
        ]);

        if (array_key_exists('branch_id', $data)) {
            $this->assertBranchInStaffRestaurant((int) $data['branch_id']);
        }

        if (isset($data['start_at'], $data['end_at']) &&
            strtotime($data['end_at']) < strtotime($data['start_at'])) {
            return response()->json([
                'success' => false,
                'message' => 'end_at must be after start_at',
            ], 422);
        }

        // منع null يكسر أعمدة NOT NULL
        $data = array_filter($data, fn ($v) => !is_null($v));

        $offer->update($data);

        $offer->load([
            'branch',
            'redemptions.customer',
            'redemptions.reservation',
            'redemptions.visit',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Offer updated successfully',
            'data' => $offer,
        ]);
    }

    /* ===========================
       DESTROY
    ============================*/
    public function destroy(Offer $offer)
    {
        $this->assertOfferInStaffRestaurant($offer);

        if (method_exists($offer, 'redemptions') && $offer->redemptions()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete offer with existing redemptions.',
            ], 409);
        }

        $offer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Offer deleted successfully',
        ]);
    }
}
