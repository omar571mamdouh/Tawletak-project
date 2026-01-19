<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CustomerPreferencesController extends Controller
{
    public function update(Request $request)
    {
        $u = $request->user();

        $data = $request->validate([
            'language' => ['required','in:en,ar'],
            'notifications_enabled' => ['nullable','boolean'],
        ]);

        // لو هتخزن:
        // $u->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Preferences updated',
            'data' => $data,
        ]);
    }
}
