<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class AppInfoController extends Controller
{
    public function about()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'app_name' => 'Tawletak',
                'version' => '1.0.0',
                'description' => 'Tawletak helps you discover restaurants and reserve tables easily.',
                'privacy_url' => 'https://example.com/privacy',
                'terms_url' => 'https://example.com/terms',
            ],
        ]);
    }
}
