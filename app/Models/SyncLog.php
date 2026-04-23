<?php

namespace App\Models;

use Illuminate\Support\Facades\File;

class SyncLog
{
    protected static string $ruta;

    protected static function ruta(): string
    {
        return storage_path('app/sync_log.json');
    }

    /**
     * Guarda una nueva entrada en el historial JSON.
     */
    public static function create(array $datos): void
    {
        $datos['ejecutado_en'] = $datos['ejecutado_en'] ?? now()->toIso8601String();

        $historial = static::todos();
        array_unshift($historial, $datos); // Más reciente primero

        // Guardamos solo las últimas 50 entradas para no crecer indefinidamente
        $historial = array_slice($historial, 0, 50);

        File::put(static::ruta(), json_encode($historial, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * Devuelve todos los registros del historial.
     */
    public static function todos(): array
    {
        if (!File::exists(static::ruta())) return [];
        return json_decode(File::get(static::ruta()), true) ?? [];
    }

    /**
     * Devuelve el registro más reciente con estado 'ok'.
     */
    public static function ultima(): ?array
    {
        foreach (static::todos() as $entry) {
            if (($entry['estado'] ?? '') === 'ok') return $entry;
        }
        return null;
    }

    /**
     * Devuelve los N registros más recientes.
     */
    public static function recientes(int $limit = 10): array
    {
        return array_slice(static::todos(), 0, $limit);
    }

    /**
     * Texto legible de "hace X tiempo".
     */
    public static function tiempoTranscurrido(string $fechaIso): string
    {
        $diff = now()->diffInMinutes(\Carbon\Carbon::parse($fechaIso));

        if ($diff < 1)   return 'hace unos segundos';
        if ($diff < 60)  return "hace {$diff} min";
        $horas = now()->diffInHours(\Carbon\Carbon::parse($fechaIso));
        if ($horas < 24) return "hace {$horas}h";
        $dias  = now()->diffInDays(\Carbon\Carbon::parse($fechaIso));
        return "hace {$dias}d";
    }
}