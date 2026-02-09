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
}
