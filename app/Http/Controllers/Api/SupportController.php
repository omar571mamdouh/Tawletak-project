<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class SupportController extends Controller
{
    public function faqs()
    {
        return response()->json([
            'success' => true,
            'data' => [
                [
                    'section' => 'Reservations',
                    'items' => [
                        ['q' => 'How can I reserve a table?', 'a' => 'Go to a restaurant and tap Book a table.'],
                        ['q' => 'Can I cancel a reservation?', 'a' => 'Yes, from My Reservations.'],
                    ],
                ],
                [
                    'section' => 'Rewards & Points',
                    'items' => [
                        ['q' => 'How do I earn points?', 'a' => 'You earn points after completing reservations.'],
                    ],
                ],
                [
                    'section' => 'Contact Support',
                    'items' => [
                        ['q' => 'How can I contact support?', 'a' => 'Email support@yourapp.com'],
                    ],
                ],
            ],
        ]);
    }
}
