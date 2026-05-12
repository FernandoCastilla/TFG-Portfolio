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
});

// Sincronización pública — los datos del Vicerrectorado son accesibles sin login
Route::post('/api/sincronizar', function () {
    try {
        Artisan::call('ugr:update');
        return response()->json(['success' => true, 'message' => 'Sincronización completada con éxito']);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
    }
})->name('api.sync');

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
// Busqueda en tiempo real -- devuelve JSON para el dropdown del aside
Route::get('/api/buscar', function (Request $request) {
    $query = strtolower(trim($request->query('q', '')));

    if (strlen($query) < 2) {
        return response()->json(['proyectos' => [], 'tareas' => []]);
    }

    $proyectos = [];
    $tareas    = [];

    $rutaProyectos = storage_path('app/proyectos_ugr.json');
    if (File::exists($rutaProyectos)) {
        $todos = json_decode(File::get($rutaProyectos), true)['_embedded']['elements'] ?? [];
        $filtrados = array_values(array_slice(array_filter($todos, fn($p) =>
            str_contains(strtolower($p['name'] ?? ''), $query) ||
            str_contains((string) $p['id'], $query)
        ), 0, 5));
        $proyectos = array_map(fn($p) => [
            'id'   => $p['id'],
            'name' => $p['name'],
            'url'  => route('proyectos.tareas', $p['id']),
        ], $filtrados);
    }

    $rutaTareas = storage_path('app/tareas_ugr.json');
    if (File::exists($rutaTareas)) {
        $todas = json_decode(File::get($rutaTareas), true)['_embedded']['elements'] ?? [];
        $filtradas = array_values(array_slice(array_filter($todas, fn($t) =>
            str_contains(strtolower($t['subject'] ?? ''), $query) ||
            str_contains((string) $t['id'], $query)
        ), 0, 5));
        $tareas = array_map(fn($t) => [
            'id'      => $t['id'],
            'subject' => $t['subject'],
            'estado'  => $t['_links']['status']['title'] ?? '',
            'url'     => route('tareas.show', $t['id']),
        ], $filtradas);
    }

    return response()->json(compact('proyectos', 'tareas'));
})->name('api.buscar');

Route::get('/buscar', function (Request $request) {
    $query           = strtolower($request->query('q', ''));
    $selectedEntidad = $request->query('entidad', '');
    $selectedAnio    = $request->query('anio', '');
    $resultadosProyectos = [];
    $resultadosTareas    = [];
    $entidades           = [];
    $anios               = [];

    // Cargamos todas las entidades y años para los desplegables independientemente del query
    $rutaTareas = storage_path('app/tareas_ugr.json');
    if (File::exists($rutaTareas)) {
        $todasLasTareas = json_decode(File::get($rutaTareas), true)['_embedded']['elements'] ?? [];

        $entidades = collect($todasLasTareas)
            ->map(fn($t) => $t['customField3'] ?? null)
            ->filter()->unique()->sort()->values()->all();

        // Años extraídos de startDate; si no tiene, se usa createdAt como fallback
        $anios = collect($todasLasTareas)
            ->map(function ($t) {
                $fecha = $t['startDate'] ?? $t['createdAt'] ?? null;
                return $fecha ? substr($fecha, 0, 4) : null;
            })
            ->filter()->unique()->sort()->values()->all();

        if (!empty($query) || !empty($selectedEntidad) || !empty($selectedAnio)) {
            $resultadosTareas = array_values(array_filter($todasLasTareas, function ($t) use ($query, $selectedEntidad, $selectedAnio) {
                $matchTexto   = empty($query) ||
                    str_contains(strtolower($t['subject'] ?? ''), $query) ||
                    str_contains((string) $t['id'], $query);
                $matchEntidad = empty($selectedEntidad) ||
                    ($t['customField3'] ?? '') === $selectedEntidad;
                $fechaTarea   = $t['startDate'] ?? $t['createdAt'] ?? '';
                $matchAnio    = empty($selectedAnio) ||
                    str_starts_with($fechaTarea, $selectedAnio);
                return $matchTexto && $matchEntidad && $matchAnio;
            }));
        }
    }

    if (!empty($query)) {
        $rutaProyectos = storage_path('app/proyectos_ugr.json');
        if (File::exists($rutaProyectos)) {
            $proyectos = json_decode(File::get($rutaProyectos), true)['_embedded']['elements'] ?? [];
            $resultadosProyectos = array_values(array_filter($proyectos, fn($p) =>
                str_contains(strtolower($p['name'] ?? ''), $query) ||
                str_contains((string) $p['id'], $query)
            ));
        }
    }

    return view('buscar', compact('query', 'selectedEntidad', 'selectedAnio', 'entidades', 'anios', 'resultadosProyectos', 'resultadosTareas'));
})->name('buscar');
// =============================================================================
// ASISTENTE IA — POST /api/asistente
// Recibe una pregunta en lenguaje natural, construye un resumen compacto de los
// datos locales del Vicerrectorado y consulta a Groq (Llama 3.3 70B) para responder.
// =============================================================================
Route::post('/api/asistente', function (Request $request) {
    $pregunta = trim($request->input('pregunta', ''));

    if (empty($pregunta)) {
        return response()->json(['error' => 'La pregunta no puede estar vacía.'], 422);
    }

    // 1. Cargar datos locales
    $tareas    = [];
    $proyectos = [];

    $rutaProyectos = storage_path('app/proyectos_ugr.json');
    $rutaTareas    = storage_path('app/tareas_ugr.json');

    if (File::exists($rutaProyectos)) {
        $proyectos = json_decode(File::get($rutaProyectos), true)['_embedded']['elements'] ?? [];
    }
    if (File::exists($rutaTareas)) {
        $tareas = json_decode(File::get($rutaTareas), true)['_embedded']['elements'] ?? [];
    }

    // 2. RESÚMENES PRECALCULADOS
    // Calculamos métricas concretas antes de pasarlas al modelo para que no tenga
    // que inferir nada — los datos ya llegan masticados al prompt.
    $totalProyectos = count($proyectos);
    $totalTareas    = count($tareas);

    $estadosFin     = ['H_Validado_100%', 'H_Finalizado_90%', 'S_Validada_100%', 'T_Finalizada_90%'];
    $estadosInicio  = ['Nuev@', 'New', 'S_Planificada_5%', 'H_Planificado_5%', 'T_Planificado_5%'];

    $sinAsignar   = array_filter($tareas, fn($t) => empty($t['_links']['assignee']['title'] ?? ''));
    $finalizadas  = array_filter($tareas, fn($t) => in_array($t['_links']['status']['title'] ?? '', $estadosFin));
    $sinIniciar   = array_filter($tareas, fn($t) => in_array($t['_links']['status']['title'] ?? '', $estadosInicio));

    // Progreso medio por proyecto
    $progresosPorProyecto = [];
    foreach ($proyectos as $p) {
        $tp = array_filter($tareas, fn($t) => str_ends_with($t['_links']['project']['href'] ?? '', '/'.$p['id']));
        if (count($tp) > 0) {
            $media = round(array_sum(array_column(array_values($tp), 'percentageDone')) / count($tp), 1);
            $progresosPorProyecto[$p['name']] = $media;
        }
    }
    asort($progresosPorProyecto); // de menor a mayor progreso

    // Asignaciones por persona
    $porPersona = [];
    foreach ($tareas as $t) {
        $nombre = $t['_links']['assignee']['title'] ?? null;
        if ($nombre) $porPersona[$nombre] = ($porPersona[$nombre] ?? 0) + 1;
    }
    arsort($porPersona);

    $resumen  = "=== RESUMEN GLOBAL ===\n";
    $resumen .= "Proyectos totales: {$totalProyectos}\n";
    $resumen .= "Tareas totales: {$totalTareas}\n";
    $resumen .= "Tareas sin asignar: " . count($sinAsignar) . "\n";
    $resumen .= "Tareas finalizadas: " . count($finalizadas) . "\n";
    $resumen .= "Tareas sin iniciar: " . count($sinIniciar) . "\n\n";

    $resumen .= "=== PROGRESO POR PROYECTO (de menor a mayor) ===\n";
    foreach ($progresosPorProyecto as $nombre => $pct) {
        $resumen .= "  {$nombre}: {$pct}% de media\n";
    }

    $resumen .= "\n=== TAREAS POR PERSONA (top asignados) ===\n";
    foreach (array_slice($porPersona, 0, 10, true) as $persona => $n) {
        $resumen .= "  {$persona}: {$n} tareas\n";
    }

    // 3. DETALLE POR PROYECTO
    $contexto = '';
    foreach ($proyectos as $p) {
        $idProyecto     = $p['id'];
        $nombreProyecto = $p['name'];
        $tareasProyecto = array_values(array_filter($tareas, fn($t) =>
            str_ends_with($t['_links']['project']['href'] ?? '', '/' . $idProyecto)
        ));

        if (empty($tareasProyecto)) continue;

        $contexto .= "--- Proyecto: {$nombreProyecto} (ID:{$idProyecto}, " . count($tareasProyecto) . " tareas) ---\n";
        foreach ($tareasProyecto as $t) {
            $estado   = $t['_links']['status']['title']   ?? 'Sin estado';
            $asignado = $t['_links']['assignee']['title'] ?? 'Sin asignar';
            $entidad  = $t['customField3']                ?? '';
            $progreso = $t['percentageDone']              ?? 0;
            $inicio   = $t['startDate']                   ?? '';
            $fin      = $t['dueDate']                     ?? '';
            $linea    = "  #{$t['id']} [{$estado}] {$t['subject']} | asignado: {$asignado} | progreso: {$progreso}%";
            if ($inicio) $linea .= " | inicio: {$inicio}";
            if ($fin)    $linea .= " | fin: {$fin}";
            if ($entidad) $linea .= " | entidad: {$entidad}";
            $contexto .= $linea . "\n";
        }
        $contexto .= "\n";
    }

   // 4. TAREAS RETRASADAS PRECALCULADAS
    $hoy = date('Y-m-d');
    $retrasadas = array_values(array_filter($tareas, fn($t) =>
        !empty($t['dueDate']) &&
        $t['dueDate'] < $hoy &&
        ($t['percentageDone'] ?? 0) < 100
    ));
    $resumen .= "\n=== TAREAS RETRASADAS (dueDate < hoy y progreso < 100%) ===\n";
    $resumen .= "Total retrasadas: " . count($retrasadas) . "\n";
    foreach ($retrasadas as $t) {
        $proyecto = $t['_links']['project']['title'] ?? 'Sin proyecto';
        $asignado = $t['_links']['assignee']['title'] ?? 'Sin asignar';
        
        $estadoRetrasada = $t['_links']['status']['title'] ?? 'Sin estado';
        
        $resumen .= "  #{$t['id']} [{$estadoRetrasada}] {$t['subject']} | proyecto: {$proyecto} | vencía: {$t['dueDate']} | asignado: {$asignado}\n";
    }

    // 5. Llamar a Groq API — modelo 70B con reintento automático ante rate limit (429)
    $apiKey = env('GROQ_API_KEY');

    $systemPrompt = <<<PROMPT
Eres un asistente del Vicerrectorado de Transformación Digital de la Universidad de Granada.
Tu única fuente de información son los datos proporcionados a continuación. Sigue estas reglas SIN EXCEPCIÓN:

REGLAS:
1. Responde SOLO con datos que aparezcan literalmente en el contexto.
2. Si la información no está en el contexto, responde exactamente: "No tengo esa información en los datos actuales."
3. NUNCA uses palabras como "aproximadamente", "al menos", "podría", "parece" o "estimo".
4. Cuando cites un número, indica exactamente de qué sección del contexto lo sacas.
5. Si confirmas que algo existe (una tarea, persona o estado), cítalo con su ID y nombre completo.
6. No expliques tu razonamiento. Da solo el resultado final.
7. Sé conciso. Máximo 5 líneas salvo que se pida un listado.
8. No saludes ni te presentes. Ve directo a la respuesta.

{$resumen}

{$contexto}
PROMPT;

    $payload = [
        'model'       => 'llama-3.3-70b-versatile',
        'max_tokens'  => 512,
        'temperature' => 0.1,
        'messages'    => [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user',   'content' => $pregunta],
        ],
    ];

    // Reintento con backoff exponencial ante rate limit (429)
    $maxIntentos = 3;
    $esperas     = [3, 7]; // segundos entre reintento 1→2 y 2→3
    $response    = null;

    for ($intento = 1; $intento <= $maxIntentos; $intento++) {
        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type'  => 'application/json',
        ])->timeout(30)->post('https://api.groq.com/openai/v1/chat/completions', $payload);

        if ($response->status() !== 429) break;

        // Rate limit — esperar antes del siguiente intento
        if ($intento < $maxIntentos) {
            sleep($esperas[$intento - 1]);
        }
    }

    if (!$response->successful()) {
        $codigo = $response->status();
        $msg    = $codigo === 429
            ? 'El asistente está ocupado por límite de peticiones. Espera unos segundos e inténtalo de nuevo.'
            : 'Error al conectar con el asistente.';
        return response()->json(['error' => $msg], 500);
    }

    $respuesta = $response->json('choices.0.message.content', 'Sin respuesta.');

    return response()->json(['respuesta' => $respuesta]);
})->name('api.asistente');