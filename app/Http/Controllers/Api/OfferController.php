<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    public function index(Request $request)
    {
        $query = Offer::query()
            ->with([
                'branch',
                'redemptions.customer',
                'redemptions.reservation',
                'redemptions.visit',
            ]);

        // Filters اختياري
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', (bool) $request->is_active);
        }

        // active during time window (اختياري)
        if ($request->boolean('active_now')) {
            $now = now();
            $query->where('is_active', true)
                  ->where('start_at', '<=', $now)
                  ->where('end_at', '>=', $now);
        }

        // ترتيب
        $query->orderByDesc('start_at');

        // Limit اختياري بدل pagination
        $limit = min((int) $request->get('limit', 200), 500);
        $offers = $query->take($limit)->get();

        return response()->json([
            'success' => true,
            'data'    => $offers,
        ]);
    }

    public function show(Offer $offer, Request $request)
    {
        $offer->load([
            'branch',
            'redemptions.customer',
            'redemptions.reservation',
            'redemptions.visit',
        ]);

        return response()->json([
            'success' => true,
            'data'    => $offer,
        ]);
    }
}
