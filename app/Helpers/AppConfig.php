<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;

/**
 * Helper para leer la configuración de la aplicación.
 *
 * Lee primero de storage/app/config.json (configurado por el usuario
 * desde el panel /configuracion), y si no existe el valor cae al .env.
 * Esto permite configurar los tokens sin tocar ficheros del servidor.
 */
class AppConfig
{
    private static ?array $cache = null;

    /**
     * Obtiene un valor de configuración.
     * Prioridad: config.json > .env > valor por defecto
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $config = self::load();
        return $config[$key] ?? env($key, $default);
    }

    /**
     * Guarda los valores de configuración en config.json
     */
    public static function save(array $values): bool
    {
        try {
            $ruta   = storage_path('app/config.json');
            $actual = self::load();
            $nuevo  = array_merge($actual, $values);
            File::put($ruta, json_encode($nuevo, JSON_PRETTY_PRINT));
            self::$cache = $nuevo;
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Carga el config.json en caché
     */
    private static function load(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $ruta = storage_path('app/config.json');
        if (File::exists($ruta)) {
            self::$cache = json_decode(File::get($ruta), true) ?? [];
        } else {
            self::$cache = [];
        }

        return self::$cache;
    }

    /**
     * Devuelve todos los valores configurados (para mostrar en el panel)
     */
    public static function all(): array
    {
        return self::load();
    }
}