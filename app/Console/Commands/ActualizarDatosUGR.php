<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use App\Models\SyncLog;

class ActualizarDatosUGR extends Command
{
    protected $signature = 'ugr:update';
    protected $description = 'Descarga datos de la API de la UGR y los fusiona con los locales (sin borrar los de prueba)';

    public function handle()
    {
        $baseUrl = env('OPENPROJECT_URL') . '/api/v3';
        $token   = env('OPENPROJECT_TOKEN');

        if (!$token) {
            $this->error('❌ Token incorrecto o no definido en el archivo .env');
            return;
        }

        $this->info('Iniciando sincronización con la UGR...');
        $inicio = microtime(true);

        // 1. DESCARGAR DATOS DE LA API
        $this->info('Descargando proyectos y tareas...');
        $resProyectos = Http::withBasicAuth('apikey', $token)->withoutVerifying()->get("$baseUrl/projects");
        $resTareas    = Http::withBasicAuth('apikey', $token)->withoutVerifying()->get("$baseUrl/work_packages");

        if (!$resProyectos->successful() || !$resTareas->successful()) {
            $duracion = round(microtime(true) - $inicio, 3);
            $this->error('❌ Hubo un error al conectar con la API de la UGR.');
            Cache::forever('api_conectada', false);

            SyncLog::create([
                'ejecutado_en'      => now()->toIso8601String(),
                'duracion_segundos' => $duracion,
                'proyectos_api'     => 0,
                'tareas_api'        => 0,
                'proyectos_nuevos'  => 0,
                'tareas_nuevas'     => 0,
                'proyectos_total'   => 0,
                'tareas_total'      => 0,
                'estado'            => 'error',
                'mensaje_error'     => 'HTTP: proyectos=' . $resProyectos->status() . ' tareas=' . $resTareas->status(),
            ]);
            return;
        }

        $proyectosApi = $resProyectos->json()['_embedded']['elements'] ?? [];
        $tareasApi    = $resTareas->json()['_embedded']['elements']    ?? [];
        $this->info('-> Recibidos ' . count($proyectosApi) . ' proyectos y ' . count($tareasApi) . ' tareas de la API.');

        // 2. LEER DATOS LOCALES
        $this->info('Leyendo datos locales...');
        $rutaProyectos = storage_path('app/proyectos_ugr.json');
        $rutaTareas    = storage_path('app/tareas_ugr.json');

        $dataProyectosLocales = File::exists($rutaProyectos)
            ? json_decode(File::get($rutaProyectos), true)
            : ['_embedded' => ['elements' => []]];

        $dataTareasLocales = File::exists($rutaTareas)
            ? json_decode(File::get($rutaTareas), true)
            : ['_embedded' => ['elements' => []]];

        $listaProyectosLocales = $dataProyectosLocales['_embedded']['elements'] ?? [];
        $listaTareasLocales    = $dataTareasLocales['_embedded']['elements']    ?? [];

        // Contar nuevos
        $idsProyectosAnteriores = collect($listaProyectosLocales)->pluck('id')->flip();
        $idsTareasAnteriores    = collect($listaTareasLocales)->pluck('id')->flip();
        $proyectosNuevos = collect($proyectosApi)->filter(fn($p) => !isset($idsProyectosAnteriores[$p['id']]))->count();
        $tareasNuevas    = collect($tareasApi)->filter(fn($t) => !isset($idsTareasAnteriores[$t['id']]))->count();

        // 3. FUSIONAR
        $this->info('Fusionando datos sin duplicados...');
        $coleccionProyectos = collect($listaProyectosLocales)->merge($proyectosApi)->unique('id')->values()->toArray();
        $coleccionTareas    = collect($listaTareasLocales)->merge($tareasApi)->unique('id')->values()->toArray();

        // 4. GUARDAR JSON
        $this->info('Guardando archivos...');
        $dataProyectosLocales['_embedded']['elements'] = $coleccionProyectos;
        $dataProyectosLocales['total']                  = count($coleccionProyectos);
        $dataTareasLocales['_embedded']['elements'] = $coleccionTareas;
        $dataTareasLocales['total']                  = count($coleccionTareas);

        File::put($rutaProyectos, json_encode($dataProyectosLocales, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        File::put($rutaTareas,    json_encode($dataTareasLocales,    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        Cache::forever('api_conectada', true);

        // 5. GUARDAR LOG (en JSON, sin MySQL)
        $duracion = round(microtime(true) - $inicio, 3);

        SyncLog::create([
            'ejecutado_en'      => now()->toIso8601String(),
            'duracion_segundos' => $duracion,
            'proyectos_api'     => count($proyectosApi),
            'tareas_api'        => count($tareasApi),
            'proyectos_nuevos'  => $proyectosNuevos,
            'tareas_nuevas'     => $tareasNuevas,
            'proyectos_total'   => count($coleccionProyectos),
            'tareas_total'      => count($coleccionTareas),
            'estado'            => 'ok',
        ]);

        $this->info("✅ Proceso finalizado en {$duracion}s.");
        $this->line('Total: ' . count($coleccionProyectos) . ' proyectos y ' . count($coleccionTareas) . ' tareas.');
        $this->line("Nuevos: {$proyectosNuevos} proyectos y {$tareasNuevas} tareas.");
    }
}