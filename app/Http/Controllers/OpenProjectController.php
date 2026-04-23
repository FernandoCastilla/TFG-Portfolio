<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class OpenProjectController extends Controller
{
    public function testConexion()
    {
        // 1. Cogemos las variables seguras del archivo .env
        $urlBase = env('OPENPROJECT_URL');
        $token = env('OPENPROJECT_TOKEN');

        // 2. Hacemos la petición GET a la API usando el "Http Client" de Laravel
        // Es el equivalente moderno y limpio a lo que tu tutor hacía con cURL y HTTP_Request2
        $response = Http::withoutVerifying()
            ->withBasicAuth('apikey', $token)
            ->get($urlBase . '/api/v3/work_packages');

        // 3. Comprobamos si ha ido bien (Status 200)
        if ($response->successful()) {
            // Laravel convierte el JSON en un array automáticamente, sin usar json_decode()
            return $response->json();
        }

        // Si falla, mostramos el error
        return response()->json([
            'mensaje' => 'Error al conectar con la UGR',
            'estado' => $response->status()
        ], $response->status());
    }
}