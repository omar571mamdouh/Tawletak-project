<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CustomerProfileController extends Controller
{
    public function show(Request $request)
    {
        $u = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $u->id,
                'name' => $u->name ?? null,
                'email' => $u->email ?? null,
                'phone' => $u->phone ?? null,
                'language' => $u->language ?? 'en',
            ],
        ]);
    }

    public function update(Request $request)
    {
        $u = $request->user();

        $data = $request->validate([
            'name' => ['nullable','string','max:255'],
            'email' => ['nullable','email','max:255'],
            'phone' => ['nullable','string','max:30'],
        ]);

        // لو مش عايز DB دلوقتي: رجّع نفس اللي اتبعت
        // لو عايز تحفظ فعليًا: $u->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated',
            'data' => array_merge([
                'id' => $u->id,
                'language' => $u->language ?? 'en',
            ], $data),
        ]);
    }
}
