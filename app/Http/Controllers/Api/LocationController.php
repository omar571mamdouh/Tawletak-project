<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LocationController extends Controller
{
   
    public function search(Request $request)
    {
        $data = $request->validate([
            'q' => 'required|string|min:2|max:150',
            'limit' => 'nullable|integer|min:1|max:20',
            'country' => 'nullable|string|size:2', // eg, jo, sa
        ]);

        $params = [
            'q' => $data['q'],
            'format' => 'jsonv2',
            'addressdetails' => 1,
            'limit' => $data['limit'] ?? 8,
        ];

        // Restrict to country if provided
        if (!empty($data['country'])) {
            $params['countrycodes'] = strtolower($data['country']);
        }

        $res = Http::timeout(10)
            ->withHeaders([
                // Nominatim بيحب يكون فيه User-Agent واضح
                'User-Agent' => 'Tawletak/1.0 (support@tawletak.com)',
            ])
            ->get('https://nominatim.openstreetmap.org/search', $params);

        if (!$res->ok()) {
            return response()->json([
                'success' => false,
                'message' => 'Location search failed',
            ], 422);
        }

        $items = collect($res->json() ?? [])->map(function ($i) {
            return [
                'place_id' => $i['place_id'] ?? null,
                'display_name' => $i['display_name'] ?? null,
                'lat' => isset($i['lat']) ? (float)$i['lat'] : null,
                'lng' => isset($i['lon']) ? (float)$i['lon'] : null,
                'type' => $i['type'] ?? null,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    // GET /api/v1/location/reverse?lat=30.0123&lng=31.2345
    public function reverse(Request $request)
    {
        $data = $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $res = Http::timeout(10)
            ->withHeaders([
                'User-Agent' => 'Tawletak/1.0 (support@tawletak.com)',
            ])
            ->get('https://nominatim.openstreetmap.org/reverse', [
                'lat' => $data['lat'],
                'lon' => $data['lng'],
                'format' => 'jsonv2',
                'addressdetails' => 1,
            ]);

        if (!$res->ok()) {
            return response()->json([
                'success' => false,
                'message' => 'Reverse geocode failed',
            ], 422);
        }

        $j = $res->json();

        return response()->json([
            'success' => true,
            'data' => [
                'display_name' => $j['display_name'] ?? null,
                'lat' => $data['lat'],
                'lng' => $data['lng'],
                'address' => $j['address'] ?? null,
            ],
        ]);
    }
}
