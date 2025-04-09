<?php

namespace App\Http\Controllers;

use App\Models\AmoToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function redirectToAmo() {
        $query = http_build_query([
            'client_id'     => env('AMO_CLIENT_ID'),
            'redirect_uri'  => env('AMO_REDIRECT_URI'),
            'response_type' => 'code',
            'state'         => Str::random(40),
        ]);

        return redirect("https://www.amocrm.ru/oauth?{$query}");
    }

    public function handleCallback(Request $request)
    {
        $baseDomain = env('AMO_BASE_DOMAIN');
        $response = Http::post("https://{$baseDomain}/oauth2/access_token", [
            'client_id'     => env('AMO_CLIENT_ID'),
            'client_secret' => env('AMO_CLIENT_SECRET'),
            'grant_type'    => 'authorization_code',
            'code'          => $request->code,
            'redirect_uri'  => env('AMO_REDIRECT_URI'),
        ]);

        $data = $response->json();

        if (!isset($data['access_token'])) {
            return response()->json([
                'error' => 'Ошибка авторизации в AMO',
                'details' => $data
            ], 400);
        }

        AmoToken::updateOrCreate([], [
            'access_token'  => $data['access_token'],
            'refresh_token' => $data['refresh_token'],
            'expires_at'    => now()->addSeconds($data['expires_in']),
        ]);

        return redirect()->route('leads.index')->with('success', 'Успешная авторизация!');
    }
}
