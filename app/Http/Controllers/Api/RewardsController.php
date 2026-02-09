<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RewardsController extends Controller
{
    // قائمة rewards المتاحة للشراء بالنقاط (صفحة Rewards الرئيسية فوق)
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

    // ✅ My Rewards tabs: active | used | expired
    public function active(Request $request)
    {
        $data = $request->validate([
            'status' => ['nullable', Rule::in(['active', 'used', 'expired'])],
        ]);

        $status = $data['status'] ?? 'active';

        // Dummy dataset (بعد كده هتبدله بـ DB)
        $all = [
            [
                'redeem_id' => 1001,
                'reward_id' => 1,
                'title' => 'Black Coffee',
                'status' => 'active',
                'expires_at' => '2026-02-01',
            ],
            [
                'redeem_id' => 1002,
                'reward_id' => 2,
                'title' => 'Dessert Coupon',
                'status' => 'used',
                'expires_at' => '2026-02-10',
            ],
            [
                'redeem_id' => 1003,
                'reward_id' => 3,
                'title' => 'Free Drink',
                'status' => 'expired',
                'expires_at' => '2026-01-01',
            ],
        ];

        $filtered = array_values(array_filter($all, fn ($r) => $r['status'] === $status));

        return response()->json([
            'success' => true,
            'data' => [
                'tab_key' => $status,
                'tabs' => [
                    ['key' => 'active',  'name' => 'Active'],
                    ['key' => 'used',    'name' => 'Used'],
                    ['key' => 'expired', 'name' => 'Expired'],
                ],
                'items' => $filtered,
            ],
        ]);
    }

   public function redeem(Request $request)
{
    $data = $request->validate([
        'reward_id' => ['required','integer'],
        'restaurant_id' => ['nullable','integer'],
    ]);

    $rewardId = (int) $data['reward_id'];

    // Dummy rewards (نفس confirm/index)
    $rewards = [
        1 => [
            'id' => 1,
            'title' => 'Black Coffee',
            'restaurant_id' => 8,
            'points_required' => 500,
            'expires_at' => '2026-02-01',
            'is_active' => true,
        ],
        2 => [
            'id' => 2,
            'title' => 'Dessert Coupon',
            'restaurant_id' => 9,
            'points_required' => 900,
            'expires_at' => '2026-02-10',
            'is_active' => true,
        ],
    ];

    if (!isset($rewards[$rewardId])) {
        return response()->json([
            'success' => false,
            'message' => 'Reward not found',
            'code' => 'reward_not_found',
        ], 404);
    }

    $reward = $rewards[$rewardId];
    $pointsBalance = 800; // Dummy
    $required = (int) $reward['points_required'];

    $isExpired = !empty($reward['expires_at']) && now()->gt($reward['expires_at']);

    if (!$reward['is_active']) {
        return response()->json([
            'success' => false,
            'message' => 'Reward is not active',
            'code' => 'reward_inactive',
        ], 422);
    }

    if ($isExpired) {
        return response()->json([
            'success' => false,
            'message' => 'Reward is expired',
            'code' => 'reward_expired',
        ], 422);
    }

    if ($pointsBalance < $required) {
        return response()->json([
            'success' => false,
            'message' => 'Insufficient points',
            'code' => 'insufficient_points',
            'data' => [
                'points_balance' => $pointsBalance,
                'points_required' => $required,
            ],
        ], 422);
    }

    // Dummy: خصم نقاط (بعد كده DB)
    $newBalance = $pointsBalance - $required;

    return response()->json([
        'success' => true,
        'message' => 'Reward redeemed successfully',
        'data' => [
            'redeem_id' => rand(1000, 9999),
            'reward_id' => $rewardId,
            'status' => 'active',
            'expires_at' => $reward['expires_at'],
            'qr_code' => 'DUMMY_QR_' . $rewardId,
            'points_balance' => $newBalance,
        ],
    ], 201);
}


    // ✅ History tabs: all | earned | spent
    public function history(Request $request)
    {
        $data = $request->validate([
            'type' => ['nullable', Rule::in(['all', 'earned', 'spent'])],
        ]);

        $type = $data['type'] ?? 'all';

        $items = [
            ['id' => 1, 'title' => 'First Reservation Bonus', 'points' =>  500, 'date' => '2026-01-20'],
            ['id' => 2, 'title' => 'Discount Applied',        'points' => -500, 'date' => '2026-01-20'],
            ['id' => 3, 'title' => 'Reservation Completed',   'points' =>  100, 'date' => '2026-01-20'],
            ['id' => 4, 'title' => 'Redeem Rewards',          'points' => -300, 'date' => '2026-01-20'],
        ];

        if ($type === 'earned') {
            $items = array_values(array_filter($items, fn ($i) => $i['points'] > 0));
        } elseif ($type === 'spent') {
            $items = array_values(array_filter($items, fn ($i) => $i['points'] < 0));
        }

        return response()->json([
            'success' => true,
            'data' => [
                'tab_key' => $type,
                'tabs' => [
                    ['key' => 'all',    'name' => 'All'],
                    ['key' => 'earned', 'name' => 'Earned'],
                    ['key' => 'spent',  'name' => 'Spent'],
                ],
                'items' => $items,
            ],
        ]);
    }


    public function confirm(Request $request, $rewardId)
{
    // Dummy rewards (نفس اللي في index)
    $rewards = [
        1 => [
            'id' => 1,
            'title' => 'Black Coffee',
            'restaurant_id' => 8,
            'restaurant' => 'Haroun Café & Restaurant',
            'points_required' => 500,
            'expires_at' => '2026-02-01',
            'image' => null,
            'is_active' => true,
        ],
        2 => [
            'id' => 2,
            'title' => 'Dessert Coupon',
            'restaurant_id' => 9,
            'restaurant' => 'Madoline Restaurant',
            'points_required' => 900,
            'expires_at' => '2026-02-10',
            'image' => null,
            'is_active' => true,
        ],
    ];

    if (!isset($rewards[$rewardId])) {
        return response()->json([
            'success' => false,
            'message' => 'Reward not found',
        ], 404);
    }

    $reward = $rewards[$rewardId];
    $pointsBalance = 800; // Dummy (بعد كده من DB)

    $required = (int) $reward['points_required'];
    $isExpired = !empty($reward['expires_at']) && now()->gt($reward['expires_at']);
    $canRedeem = $reward['is_active'] && !$isExpired && $pointsBalance >= $required;

    $reason = null;
    if (!$reward['is_active']) $reason = 'reward_inactive';
    elseif ($isExpired) $reason = 'reward_expired';
    elseif ($pointsBalance < $required) $reason = 'insufficient_points';

    return response()->json([
        'success' => true,
        'data' => [
            'points_balance' => $pointsBalance,
            'reward' => [
                'id' => $reward['id'],
                'title' => $reward['title'],
                'restaurant_id' => $reward['restaurant_id'],
                'restaurant' => $reward['restaurant'],
                'points_required' => $required,
                'expires_at' => $reward['expires_at'],
                'image' => $reward['image'],
                'is_active' => (bool) $reward['is_active'],
            ],
            'can_redeem' => $canRedeem,
            'deny_reason' => $canRedeem ? null : $reason,
        ],
    ]);
}

}
