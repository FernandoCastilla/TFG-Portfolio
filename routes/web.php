<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;
use App\Models\SyncLog;
use Illuminate\Support\Facades\Hash;

// ─────────────────────────────────────────────────────────────────────────────
// RUTAS PÚBLICAS DE AUTENTICACIÓN (solo para usuarios no autenticados)
// ─────────────────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {

    Route::view('/login', 'auth.login')->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/registro', fn() => view('auth.register'))->name('registro');
    Route::post('/registro', function (Request $request) {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.unique'       => 'Este correo ya está registrado.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres.',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);
        return redirect('/');
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// RUTAS QUE REQUIEREN AUTENTICACIÓN
// Solo logout y el endpoint de sincronización requieren login:
//   - logout: manipula la sesión del usuario autenticado.
//   - /api/sincronizar: lanza ugr:update contra la API institucional;
//     se restringe para evitar que cualquier visitante dispare
//     sincronizaciones no controladas.
// ─────────────────────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::post('/api/sincronizar', function () {
        try {
            Artisan::call('ugr:update');
            return response()->json(['success' => true, 'message' => 'Sincronización completada con éxito']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    })->name('api.sync');
});

// ─────────────────────────────────────────────────────────────────────────────
// RUTAS PÚBLICAS — los datos son accesibles sin autenticación.
// El historial de sincronizaciones se incluye solo si hay sesión activa.
// ─────────────────────────────────────────────────────────────────────────────

// Dashboard principal
Route::get('/', function () {
    $rutaProyectos = storage_path('app/proyectos_ugr.json');
    $rutaTareas    = storage_path('app/tareas_ugr.json');

    $totalProyectos   = 0;
    $totalTareas      = 0;
    $ultimosProyectos = [];
    $graficaLabels    = [];
    $graficaDatos     = [];
    $tareasCompactas  = [];
    $saludPorProyecto = [];

    // 1. PROYECTOS
    if (File::exists($rutaProyectos)) {
        $proyectos      = json_decode(File::get($rutaProyectos), true)['_embedded']['elements'] ?? [];
        $totalProyectos = count($proyectos);
        $ultimosProyectos = collect($proyectos)
            ->sortByDesc(fn($p) => $p['createdAt'] ?? '')
            ->take(5)->values()->all();
    }

    // 2. TAREAS
    if (File::exists($rutaTareas)) {
        $tareas      = json_decode(File::get($rutaTareas), true)['_embedded']['elements'] ?? [];
        $totalTareas = count($tareas);

        // Datos para la gráfica de distribución
        $conteoEstados = [];
        foreach ($tareas as $tarea) {
            $estado = $tarea['_links']['status']['title'] ?? 'Desconocido';
            $conteoEstados[$estado] = ($conteoEstados[$estado] ?? 0) + 1;
        }
        $graficaLabels = array_keys($conteoEstados);
        $graficaDatos  = array_values($conteoEstados);

        // Array compacto para el panel lateral (evita pasar el JSON completo al cliente)
        $tareasCompactas = collect($tareas)->map(fn($t) => [
            'id'       => $t['id'],
            'subject'  => $t['subject'],
            'status'   => $t['_links']['status']['title'] ?? 'Desconocido',
            'project'  => $t['_links']['project']['title'] ?? '—',
            'assignee' => $t['_links']['assignee']['title'] ?? null,
            'url'      => url('/tareas/' . $t['id']),
        ])->values()->all();

        // Semáforo de salud por proyecto (estados específicos de la instancia UGR)
        $estadosCompletados = ['H_Validado_100%', 'S_Validada_100%', 'T_Finalizada_90%', 'H_Finalizado_90%'];
        $estadosEnProgreso  = ['In progress', 'H_Progreso_25%', 'H_Progreso_50%', 'H_Progreso_75%',
                               'S_Progreso_50%', 'T_Progreso_25%', 'T_Progreso_75%'];

        collect($tareas)
            ->groupBy(fn($t) => basename($t['_links']['project']['href'] ?? '0'))
            ->each(function ($ts, $pid) use (&$saludPorProyecto, $estadosCompletados, $estadosEnProgreso) {
                $total   = count($ts);
                $pctComp = round($ts->filter(fn($t) => in_array($t['_links']['status']['title'] ?? '', $estadosCompletados))->count() / $total * 100);
                $pctProg = round($ts->filter(fn($t) => in_array($t['_links']['status']['title'] ?? '', $estadosEnProgreso))->count()  / $total * 100);
                $saludPorProyecto[$pid] = $pctComp >= 60 ? 'verde' : ($pctComp >= 30 || $pctProg >= 40 ? 'amarillo' : 'rojo');
            });
    }

    // 3. HISTORIAL DE SINCRONIZACIONES
    // Solo se carga si el usuario está autenticado; para visitantes públicos
    // no tiene sentido mostrar métricas internas de operación.
    $ultimaSync    = null;
    $historialSync = collect();

    if (Auth::check()) {
        $ultimaSyncData = SyncLog::ultima();
        $ultimaSync = $ultimaSyncData ? (object) array_merge($ultimaSyncData, [
            'tiempoTranscurrido' => SyncLog::tiempoTranscurrido($ultimaSyncData['ejecutado_en']),
        ]) : null;
        $historialSync = collect(SyncLog::recientes(10))->map(fn($e) => (object) array_merge($e, [
            'tiempoTranscurrido' => SyncLog::tiempoTranscurrido($e['ejecutado_en']),
            'ejecutado_en_fmt'   => \Carbon\Carbon::parse($e['ejecutado_en'])->format('d/m/Y H:i:s'),
        ]));
    }

    // 4. ACTIVIDAD RECIENTE
    // Las 8 tareas modificadas más recientemente de todos los proyectos.
    // updatedAt refleja cualquier cambio: estado, asignación, progreso, etc.
    $actividadReciente = collect($tareas ?? [])
        ->sortByDesc(fn($t) => $t['updatedAt'] ?? '')
        ->take(8)
        ->map(fn($t) => [
            'id'        => $t['id'],
            'subject'   => $t['subject'],
            'proyecto'  => $t['_links']['project']['title'] ?? '—',
            'estado'    => $t['_links']['status']['title'] ?? '—',
            'updatedAt' => $t['updatedAt'],
            'url'       => url('/tareas/' . $t['id']),
        ])
        ->values()
        ->all();

    return view('welcome', compact(
        'totalProyectos', 'totalTareas', 'ultimosProyectos',
        'graficaLabels', 'graficaDatos', 'tareasCompactas', 'saludPorProyecto',
        'ultimaSync', 'historialSync', 'actividadReciente'
    ));
});

// Proyectos y tareas — acceso público
Route::get('/proyectos',             [ProjectController::class, 'index'])->name('proyectos.index');
Route::get('/proyectos/{id}',        [ProjectController::class, 'show'])->name('proyectos.show');
Route::get('/proyectos/{id}/tareas', [ProjectController::class, 'tareas'])->name('proyectos.tareas');
Route::get('/proyectos/{id}/pdf',    [ProjectController::class, 'exportarPdf'])->name('proyectos.pdf');
Route::get('/proyectos/{id}/csv',    [ProjectController::class, 'exportarCsv'])->name('proyectos.csv');

Route::get('/tareas/{id}', function ($id) {
    $rutaTareas = storage_path('app/tareas_ugr.json');
    $tareas = File::exists($rutaTareas)
        ? json_decode(File::get($rutaTareas), true)['_embedded']['elements'] ?? []
        : [];

    $tarea = collect($tareas)->firstWhere('id', (int) $id);
    abort_unless($tarea, 404, 'Tarea no encontrada');

    return view('proyectos.tarea', compact('tarea'));
})->name('tareas.show');

// Calendario — solo tareas con startDate definida
// La fecha fin suma +1 día porque FullCalendar trata el end como exclusivo.
Route::get('/calendario', function () {
    $rutaTareas = storage_path('app/tareas_ugr.json');
    $eventos = [];

    if (File::exists($rutaTareas)) {
        $tareas = json_decode(File::get($rutaTareas), true)['_embedded']['elements'] ?? [];

        foreach ($tareas as $tarea) {
            if (empty($tarea['startDate'])) continue;

            $proyectoId = basename($tarea['_links']['project']['href'] ?? '0');
            $fechaFin   = !empty($tarea['dueDate'])
                ? date('Y-m-d', strtotime($tarea['dueDate'] . ' +1 day'))
                : $tarea['startDate'];

            $estado = $tarea['_links']['status']['title'] ?? '';

            // Colores por estados estándar de OpenProject
            $color = match($estado) {
                'New', 'Nuev@'                  => '#64748B', // gris — pendiente de inicio
                'In progress', 'In specification',
                'Specified', 'Confirmed'         => '#10B981', // verde — activo
                'Scheduled', 'To be scheduled'   => '#F59E0B', // ámbar — planificado
                'Closed'                         => '#374151', // gris oscuro — cerrado
                default => null,                               // se evalúa por prefijo UGR abajo
            };

            // Si no matcheó un estado estándar, coloreamos por prefijo UGR.
            // Dentro de cada grupo, el porcentaje del nombre guía la intensidad:
            // _5%/_25% → tono claro · _50% → tono medio · _75%/_90%/_100% → tono oscuro
            if ($color === null) {
                $pct = 0;
                if (preg_match('/(\d+)%/', $estado, $m)) {
                    $pct = (int) $m[1];
                }
                $intensidad = $pct <= 25 ? 0 : ($pct <= 50 ? 1 : 2); // 0=claro, 1=medio, 2=oscuro

                if (str_starts_with($estado, 'H_')) {
                    // Hitos → púrpura
                    $color = ['#C4B5FD', '#8B5CF6', '#6D28D9'][$intensidad];
                } elseif (str_starts_with($estado, 'S_')) {
                    // Solicitudes → ámbar/naranja
                    $color = ['#FCD34D', '#F59E0B', '#D97706'][$intensidad];
                } elseif (str_starts_with($estado, 'T_')) {
                    // Tareas → azul
                    $color = ['#93C5FD', '#3B82F6', '#1D4ED8'][$intensidad];
                } else {
                    $color = '#6366F1'; // índigo para cualquier otro caso desconocido
                }
            }

            $eventos[] = [
                'title'           => $tarea['subject'],
                'start'           => $tarea['startDate'],
                'end'             => $fechaFin,
                'url'             => url('/proyectos/' . $proyectoId),
                'backgroundColor' => $color,
                'borderColor'     => $color,
            ];
        }
    }

    return view('calendario', compact('eventos'));
})->name('calendario');

// Buscador global — búsqueda local sobre JSON, sin consultas a la API
Route::get('/buscar', function (Request $request) {
    $query = strtolower($request->query('q', ''));
    $resultadosProyectos = [];
    $resultadosTareas    = [];

    if (!empty($query)) {
        $rutaProyectos = storage_path('app/proyectos_ugr.json');
        if (File::exists($rutaProyectos)) {
            $proyectos = json_decode(File::get($rutaProyectos), true)['_embedded']['elements'] ?? [];
            $resultadosProyectos = array_filter($proyectos, fn($p) =>
                str_contains(strtolower($p['name'] ?? ''), $query) ||
                str_contains((string) $p['id'], $query)
            );
        }

        $rutaTareas = storage_path('app/tareas_ugr.json');
        if (File::exists($rutaTareas)) {
            $tareas = json_decode(File::get($rutaTareas), true)['_embedded']['elements'] ?? [];
            $resultadosTareas = array_filter($tareas, fn($t) =>
                str_contains(strtolower($t['subject'] ?? ''), $query) ||
                str_contains((string) $t['id'], $query)
            );
        }
    }

    return view('buscar', compact('query', 'resultadosProyectos', 'resultadosTareas'));
})->name('buscar');