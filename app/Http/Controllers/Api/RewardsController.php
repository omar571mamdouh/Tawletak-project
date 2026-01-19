<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RewardsController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'points_balance' => 800,
                'rewards' => [
                    [
                        'id' => 1,
                        'title' => 'Black Coffee',
                        'restaurant' => 'Haroun Café & Restaurant',
                        'points_required' => 500,
                        'expires_at' => '2026-02-01',
                        'image' => null,
                        'can_redeem' => true
                    ],
                    [
                        'id' => 2,
                        'title' => 'Dessert Coupon',
                        'restaurant' => 'Madoline Restaurant',
                        'points_required' => 900,
                        'expires_at' => '2026-02-10',
                        'image' => null,
                        'can_redeem' => false
                    ],
                ],
            ],
        ]);
    }

    public function active(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'active_rewards' => [
                    [
                        'redeem_id' => 1001,
                        'reward_id' => 1,
                        'title' => 'Black Coffee',
                        'status' => 'active',
                        'expires_at' => '2026-02-01',
                    ]
                ]
            ],
        ]);
    }

    public function redeem(Request $request)
    {
        $data = $request->validate([
            'reward_id' => ['required','integer'],
            'restaurant_id' => ['nullable','integer'],
        ]);

        // بدون DB: هنرجع confirm success
        return response()->json([
            'success' => true,
            'message' => 'Reward redeemed successfully',
            'data' => [
                'redeem_id' => 1002,
                'reward_id' => $data['reward_id'],
                'status' => 'active',
                'qr_code' => 'DUMMY_QR_CODE_STRING'
            ],
        ], 201);
    }

    public function history(Request $request)
    {
        $type = $request->query('type', 'all'); // all | earned | spent

        return response()->json([
            'success' => true,
            'data' => [
                'type' => $type,
                'items' => [
                    ['id' => 1, 'title' => 'Points Earned', 'points' => +500, 'date' => '2026-01-10'],
                    ['id' => 2, 'title' => 'Reward Redeemed', 'points' => -500, 'date' => '2026-01-12'],
                ],
            ],
        ]);
    }
}
