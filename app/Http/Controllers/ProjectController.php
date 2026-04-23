<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Barryvdh\DomPDF\Facade\Pdf;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        // 1. Recogemos todos los filtros del usuario
        $search = $request->input('search');
        $statusFilter = $request->input('status'); // 'active' o 'inactive'
        $visibilityFilter = $request->input('visibility'); // 'public' o 'private'

        $rutaArchivo = storage_path('app/proyectos_ugr.json');
        $proyectos = [];

        if (\Illuminate\Support\Facades\File::exists($rutaArchivo)) {
            $jsonCrudo = \Illuminate\Support\Facades\File::get($rutaArchivo);
            $datos = json_decode($jsonCrudo, true);
            $proyectos = $datos['_embedded']['elements'] ?? [];
        }

        // 2. Aplicamos TODOS los filtros a la vez
        $proyectos = array_filter($proyectos, function($proyecto) use ($search, $statusFilter, $visibilityFilter) {
            
            // Filtro 1: Texto (Nombre)
            $coincideTexto = true;
            if ($search) {
                $coincideTexto = stripos($proyecto['name'], $search) !== false;
            }

            // Filtro 2: Estado (Activo/Inactivo)
            $coincideEstado = true;
            if ($statusFilter === 'active') {
                $coincideEstado = $proyecto['active'] === true;
            } elseif ($statusFilter === 'inactive') {
                $coincideEstado = $proyecto['active'] === false;
            }

            // Filtro 3: Visibilidad (Público/Privado)
            $coincideVisibilidad = true;
            if ($visibilityFilter === 'public') {
                $coincideVisibilidad = $proyecto['public'] === true;
            } elseif ($visibilityFilter === 'private') {
                $coincideVisibilidad = $proyecto['public'] === false;
            }

            // El proyecto solo sobrevive si cumple TODOS los filtros que el usuario haya puesto
            return $coincideTexto && $coincideEstado && $coincideVisibilidad;
        });

        // 3. Enviamos los proyectos filtrados y las variables a la vista (para que los selects no se borren)
        return view('proyectos.index', compact('proyectos', 'search', 'statusFilter', 'visibilityFilter'));
    }

    public function show($id)
    {
        $rutaArchivo = storage_path('app/proyectos_ugr.json');
        
        if (\Illuminate\Support\Facades\File::exists($rutaArchivo)) {
            $jsonCrudo = \Illuminate\Support\Facades\File::get($rutaArchivo);
            $datos = json_decode($jsonCrudo, true);
            $proyectos = $datos['_embedded']['elements'] ?? [];

            // Usamos las "Collections" de Laravel para buscar el proyecto con ese ID
            $proyecto = collect($proyectos)->firstWhere('id', (int)$id);

            // Si lo encontramos, cargamos la vista de detalles
            if ($proyecto) {
                return view('proyectos.show', compact('proyecto'));
            }
        }

        // Si no existe el ID o el archivo, devolvemos un error 404
        abort(404, 'Proyecto no encontrado');
    }

    public function tareas(Request $request, $id)
    {
        $rutaProyectos = storage_path('app/proyectos_ugr.json');
        $proyecto = null;
        if (\Illuminate\Support\Facades\File::exists($rutaProyectos)) {
            $proyectosData = json_decode(\Illuminate\Support\Facades\File::get($rutaProyectos), true);
            $proyecto = collect($proyectosData['_embedded']['elements'] ?? [])->firstWhere('id', (int)$id);
        }

        if (!$proyecto) {
            abort(404, 'Proyecto no encontrado');
        }

        $rutaTareas = storage_path('app/tareas_ugr.json');
        $tareas = [];
        $graficaLabels = [];
        $graficaDatos = [];
        
        // Variables para los filtros
        $tipos = collect();
        $estados = collect();
        $asignados = collect();
        
        $selectedSort = $request->query('sort');

        $selectedTipo = $request->query('tipo');
        $selectedEstado = $request->query('estado');
        $selectedAsignado = $request->query('asignado');
        $selectedSearch = $request->query('search');
        $selectedPrioridad = $request->query('prioridad');
        $selectedEntidad = $request->query('entidad');

        
        // Variables para el filtro de fechas
        $selectedYear = $request->query('year');
        $selectedMonth = $request->query('month');

        if (\Illuminate\Support\Facades\File::exists($rutaTareas)) {
            $tareasData = json_decode(\Illuminate\Support\Facades\File::get($rutaTareas), true);
            $todasLasTareas = $tareasData['_embedded']['elements'] ?? [];

            // Filtramos las tareas de este proyecto
            $tareas = array_filter($todasLasTareas, function($tarea) use ($id) {
                $enlaceProyecto = $tarea['_links']['project']['href'] ?? '';
                return str_ends_with($enlaceProyecto, '/' . $id);
            });

            // Extraemos todos los valores ÚNICOS para los desplegables
            $tipos = collect($tareas)->map(fn($t) => $t['_links']['type']['title'] ?? 'Desconocido')->unique()->sort()->values();
            $estados = collect($tareas)->map(fn($t) => $t['_links']['status']['title'] ?? 'Desconocido')->unique()->sort()->values();
            $asignados = collect($tareas)->map(fn($t) => $t['_links']['assignee']['title'] ?? 'Sin asignar')->unique()->sort()->values();
            $prioridades = collect($tareas)->map(fn($t) => $t['_links']['priority']['title'] ?? 'Normal')->unique()->sort()->values();
            $entidades = collect($tareas)->map(fn($t) => $t['customField3'] ?? null)->filter()->unique()->sort()->values();

            // Aplicamos los filtros si el usuario ha seleccionado alguno
            if ($selectedTipo || $selectedEstado || $selectedAsignado || $selectedYear || $selectedMonth || $selectedSearch || $selectedPrioridad || $selectedEntidad) {
                $tareas = array_filter($tareas, function($tarea) use ($selectedTipo, $selectedEstado, $selectedAsignado, $selectedYear, $selectedMonth, $selectedSearch, $selectedPrioridad, $selectedEntidad) {
                    
                    // Comprobaciones de los filtros
                    $matchTipo = !$selectedTipo || ($tarea['_links']['type']['title'] ?? 'Desconocido') === $selectedTipo;
                    $matchEstado = !$selectedEstado || ($tarea['_links']['status']['title'] ?? 'Desconocido') === $selectedEstado;
                    $matchAsignado = !$selectedAsignado || ($tarea['_links']['assignee']['title'] ?? 'Sin asignar') === $selectedAsignado;
                    $matchPrioridad = !$selectedPrioridad || ($tarea['_links']['priority']['title'] ?? 'Normal') === $selectedPrioridad;
                    $matchEntidad = !$selectedEntidad || ($tarea['customField3'] ?? '') === $selectedEntidad;

                    // Busqueda de texto en el nombre de la tarea
                    $matchSearch = true;
                    if (!empty($selectedSearch)) {
                        $subject = $tarea['subject'] ?? '';
                        // stripos devuelve false si no encuentra la palabra
                        if (stripos($subject, $selectedSearch) === false) {
                            $matchSearch = false;
                        }
                    }
                    
                    
                    // Comprobación de la lógica de fechas
                    $matchFecha = true;
                    if ($selectedYear) {
                        $fechaString = $tarea['startDate'] ?? $tarea['createdAt'] ?? null;
                        
                        if (!$fechaString) {
                            $matchFecha = false; // Si no tiene fecha, no la mostramos
                        } else {
                            $taskYear = date('Y', strtotime($fechaString));
                            $taskMonth = date('m', strtotime($fechaString));
                            
                            if ($selectedYear != $taskYear) {
                                $matchFecha = false;
                            } elseif ($selectedMonth && $selectedMonth != $taskMonth) {
                                $matchFecha = false;
                            }
                        }
                    }
                    
                    // Solo mantenemos la tarea si cumple TODOS los filtros activos
                    return $matchTipo && $matchEstado && $matchAsignado && $matchFecha && $matchSearch && $matchPrioridad && $matchEntidad;
                });
            }

            // Lógica de ordenación
            if (!empty($selectedSort)) {
                $tareasCollection = collect($tareas);

                switch ($selectedSort) {
                    case 'date_desc': // Más recientes primero
                        $tareas = $tareasCollection->sortByDesc(function($t) {
                            return $t['startDate'] ?? '0000-00-00';
                        })->values()->all();
                        break;
                    case 'date_asc': // Más antiguas primero
                        $tareas = $tareasCollection->sortBy(function($t) {
                            return $t['startDate'] ?? '9999-12-31';
                        })->values()->all();
                        break;
                    case 'progress_desc': // 100% a 0%
                        $tareas = $tareasCollection->sortByDesc(function($t) {
                            return $t['percentageDone'] ?? 0;
                        })->values()->all();
                        break;
                    case 'progress_asc': // 0% a 100%
                        $tareas = $tareasCollection->sortBy(function($t) {
                            return $t['percentageDone'] ?? 0;
                        })->values()->all();
                        break;
                    case 'name_asc': // A - Z
                        $tareas = $tareasCollection->sortBy(function($t) {
                            return strtolower($t['subject'] ?? '');
                        })->values()->all();
                        break;
                    case 'name_desc': // Z - A
                        $tareas = $tareasCollection->sortByDesc(function($t) {
                            return strtolower($t['subject'] ?? '');
                        })->values()->all();
                        break;
                }
            }

            // Gráfica — calculada sobre el total filtrado, NO sobre la página actual
            $conteoEstados = [];
            foreach ($tareas as $tarea) {
                $estado = $tarea['_links']['status']['title'] ?? 'Desconocido';
                if (!isset($conteoEstados[$estado])) {
                    $conteoEstados[$estado] = 0;
                }
                $conteoEstados[$estado]++;
            }

            $graficaLabels = array_keys($conteoEstados);
            $graficaDatos  = array_values($conteoEstados);

            // ÁRBOL DE JERARQUÍA
            // Se construye aquí en el controlador para tener acceso al conjunto
            // completo de tareas (con todas sus relaciones padre-hijo) antes de paginar.
            // Si se hiciera en la vista sobre el paginator, los hijos de una tarea padre
            // que estén en otra página quedarían huérfanos visualmente.
            $tareasPorId   = [];
            $hijosPorPadre = [];
            $idsPadres     = [];

            foreach ($tareas as $t) {
                $hrefPadre = $t['_links']['parent']['href'] ?? null;
                if ($hrefPadre) $idsPadres[] = (int) basename($hrefPadre);
            }

            foreach ($tareas as $t) {
                $hrefPadre       = $t['_links']['parent']['href'] ?? null;
                $t['padre_id']   = $hrefPadre ? (int) basename($hrefPadre) : null;
                $t['tiene_hijos'] = in_array($t['id'], $idsPadres);
                $tareasPorId[$t['id']] = $t;
                if ($t['padre_id']) $hijosPorPadre[$t['padre_id']][] = $t['id'];
            }

            $tareasOrdenadas = [];
            $procesados      = [];

            $buildTree = function($idTarea, $nivel = 0) use (
                &$buildTree, &$tareasOrdenadas, &$procesados, &$tareasPorId, &$hijosPorPadre
            ) {
                if (in_array($idTarea, $procesados)) return;
                $procesados[] = $idTarea;
                $tarea = $tareasPorId[$idTarea];
                $tarea['nivel'] = $nivel;
                $tareasOrdenadas[] = $tarea;
                if (isset($hijosPorPadre[$idTarea])) {
                    foreach ($hijosPorPadre[$idTarea] as $idHijo) {
                        if (isset($tareasPorId[$idHijo])) $buildTree($idHijo, $nivel + 1);
                    }
                }
            };

            foreach ($tareasPorId as $id => $t) {
                if (!$t['padre_id'] || !isset($tareasPorId[$t['padre_id']])) {
                    $buildTree($id, 0);
                }
            }

            $tareas = $tareasOrdenadas;

            // PAGINACIÓN POR FAMILIAS COMPLETAS
            // No cortamos en medio de un grupo padre-hijos. Acumulamos tareas raíz
            // (nivel === 0) hasta superar el límite, pero solo cortamos al llegar
            // a la siguiente tarea raíz. Así una familia siempre aparece completa
            // en la misma página.
            $perPage     = 20;
            $currentPage = (int) $request->query('page', 1);

            // Agrupamos el array plano en "familias": cada vez que encontramos
            // una tarea de nivel 0 empezamos un nuevo grupo.
            $familias      = [];
            $familiaActual = [];

            foreach ($tareas as $t) {
                if (($t['nivel'] ?? 0) === 0 && !empty($familiaActual)) {
                    $familias[] = $familiaActual;
                    $familiaActual = [];
                }
                $familiaActual[] = $t;
            }
            if (!empty($familiaActual)) {
                $familias[] = $familiaActual;
            }

            // Distribuimos familias en páginas: añadimos familias completas
            // hasta superar $perPage, luego empezamos una nueva página.
            $paginas      = [];
            $paginaActual = [];
            $countActual  = 0;

            foreach ($familias as $familia) {
                // Si la página actual ya tiene tareas y añadir esta familia la haría
                // superar el límite, abrimos una página nueva.
                if ($countActual > 0 && $countActual + count($familia) > $perPage) {
                    $paginas[]    = $paginaActual;
                    $paginaActual = [];
                    $countActual  = 0;
                }
                foreach ($familia as $t) {
                    $paginaActual[] = $t;
                    $countActual++;
                }
            }
            if (!empty($paginaActual)) {
                $paginas[] = $paginaActual;
            }

            $totalPaginas = count($paginas);
            $currentPage  = max(1, min($currentPage, $totalPaginas ?: 1));
            $tareasPagina = $paginas[$currentPage - 1] ?? [];

            // El total que mostramos en "X de N tareas" es el total filtrado real.
            $tareas = new \Illuminate\Pagination\LengthAwarePaginator(
                $tareasPagina,
                count($tareas),
                $perPage,          // referencial — el tamaño real varía por familias
                $currentPage,
                ['path' => $request->url(), 'query' => $request->except('page')]
            );

            // Sobreescribimos lastPage con el número real de páginas calculado,
            // ya que LengthAwarePaginator lo calcularía mal con familias variables.
            $tareas = new \Illuminate\Pagination\LengthAwarePaginator(
                $tareasPagina,
                $totalPaginas * $perPage, // trick para que lastPage() == $totalPaginas
                $perPage,
                $currentPage,
                ['path' => $request->url(), 'query' => $request->except('page')]
            );

            // Guardamos el total real aparte para mostrarlo en la vista
            $totalTareasFiltradas = array_sum(array_map('count', $paginas));
        }

        // Enviamos todo a la vista
        return view('proyectos.tareas', compact(
            'proyecto', 'tareas', 'graficaLabels', 'graficaDatos',
            'tipos', 'estados', 'asignados', 'prioridades', 'entidades',
            'selectedTipo', 'selectedEstado', 'selectedAsignado', 'selectedSearch',
            'selectedPrioridad', 'selectedSort', 'selectedEntidad',
            'totalTareasFiltradas'
        ));
    }

    public function exportarPdf($id)
    {
        $rutaProyectos = storage_path('app/proyectos_ugr.json');
        $proyecto = null;
        if (\Illuminate\Support\Facades\File::exists($rutaProyectos)) {
            $proyectosData = json_decode(\Illuminate\Support\Facades\File::get($rutaProyectos), true);
            $proyecto = collect($proyectosData['_embedded']['elements'] ?? [])->firstWhere('id', (int)$id);
        }

        if (!$proyecto) {
            abort(404, 'Proyecto no encontrado');
        }

        $rutaTareas = storage_path('app/tareas_ugr.json');
        $tareas = [];
        if (\Illuminate\Support\Facades\File::exists($rutaTareas)) {
            $tareasData = json_decode(\Illuminate\Support\Facades\File::get($rutaTareas), true);
            $todasLasTareas = $tareasData['_embedded']['elements'] ?? [];

            $tareas = array_filter($todasLasTareas, function($tarea) use ($id) {
                $enlaceProyecto = $tarea['_links']['project']['href'] ?? '';
                return str_ends_with($enlaceProyecto, '/' . $id);
            });
        }

        // Cargamos una vista especial para el PDF y le pasamos los datos
        $pdf = Pdf::loadView('proyectos.pdf', compact('proyecto', 'tareas'));
        
        // Forzamos la descarga del archivo con un nombre dinámico
        return $pdf->download('informe_UGR_proyecto_' . $proyecto['id'] . '.pdf');
    }

    /**
     * Exporta todas las tareas de un proyecto a un fichero CSV descargable.
     *
     * Incluye los mismos campos que la vista de tareas, más los campos
     * customField de la instancia UGR (entidad solicitante, contacto, email),
     * para que el Vicerrectorado pueda cruzar los datos con otras fuentes.
     *
     * El fichero usa codificación UTF-8 con BOM para que Excel lo abra
     * correctamente sin problemas de acentos y caracteres especiales.
     */
    public function exportarCsv($id)
    {
        $rutaProyectos = storage_path('app/proyectos_ugr.json');
        $proyecto = null;
        if (\Illuminate\Support\Facades\File::exists($rutaProyectos)) {
            $proyectosData = json_decode(\Illuminate\Support\Facades\File::get($rutaProyectos), true);
            $proyecto = collect($proyectosData['_embedded']['elements'] ?? [])->firstWhere('id', (int) $id);
        }

        abort_unless($proyecto, 404, 'Proyecto no encontrado');

        $rutaTareas = storage_path('app/tareas_ugr.json');
        $tareas = [];
        if (\Illuminate\Support\Facades\File::exists($rutaTareas)) {
            $tareasData     = json_decode(\Illuminate\Support\Facades\File::get($rutaTareas), true);
            $todasLasTareas = $tareasData['_embedded']['elements'] ?? [];

            $tareas = array_values(array_filter($todasLasTareas, function ($tarea) use ($id) {
                return str_ends_with($tarea['_links']['project']['href'] ?? '', '/' . $id);
            }));
        }

        // Nombre del fichero: sin espacios ni caracteres especiales
        $nombreProyecto = preg_replace('/[^a-zA-Z0-9_-]/', '_', $proyecto['name'] ?? $id);
        $nombreFichero  = 'ugr_portfolio_' . $nombreProyecto . '_' . date('Ymd') . '.csv';

        // Generamos el CSV en memoria con un stream temporal
        $handle = fopen('php://temp', 'r+');

        // BOM UTF-8: necesario para que Excel abra el fichero con acentos correctamente
        fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Cabecera de columnas
        fputcsv($handle, [
            'ID',
            'Asunto',
            'Tipo',
            'Estado',
            'Progreso (%)',
            'Asignado a',
            'T. Estimado',
            'T. Dedicado',
            'Entidad Solicitante',
            'Persona Contacto',
            'Email Contacto',
            'Fecha Inicio',
            'Fecha Fin',
            'Prioridad',
            'Proyecto',
        ], ';'); // separador ; para compatibilidad con Excel en español

        // Filas de datos
        foreach ($tareas as $tarea) {
            fputcsv($handle, [
                $tarea['id'],
                $tarea['subject'] ?? '',
                $tarea['_links']['type']['title']     ?? '',
                $tarea['_links']['status']['title']   ?? '',
                $tarea['percentageDone']              ?? 0,
                $tarea['_links']['assignee']['title'] ?? '',
                self::parsearDuracion($tarea['estimatedTime'] ?? null) ?? '',
                self::parsearDuracion($tarea['spentTime']     ?? null) ?? '',
                $tarea['customField3'] ?? '',   // Entidad solicitante
                $tarea['customField4'] ?? '',   // Persona de contacto
                $tarea['customField5'] ?? '',   // Email de contacto
                $tarea['startDate']    ?? '',
                $tarea['dueDate']      ?? '',
                $tarea['_links']['priority']['title'] ?? '',
                $proyecto['name'] ?? '',
            ], ';');
        }

        rewind($handle);
        $contenido = stream_get_contents($handle);
        fclose($handle);

        return response($contenido, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $nombreFichero . '"',
            'Pragma'              => 'no-cache',
            'Expires'             => '0',
        ]);
    }

    /**
     * Convierte una duración ISO 8601 (ej. "P3DT21H30M", "PT10H") en texto
     * legible en horas y minutos (ej. "93h 30m", "10h").
     * Devuelve null si el valor es nulo, vacío o cero (PT0S).
     */
    public static function parsearDuracion(?string $iso): ?string
    {
        if (!$iso || $iso === 'PT0S') return null;

        preg_match('/P(?:(\d+)D)?(?:T(?:(\d+)H)?(?:(\d+)M)?)?/', $iso, $m);

        $dias    = (int) ($m[1] ?? 0);
        $horas   = (int) ($m[2] ?? 0);
        $minutos = (int) ($m[3] ?? 0);

        $totalHoras = $dias * 24 + $horas;

        if ($totalHoras === 0 && $minutos === 0) return null;
        if ($minutos === 0) return $totalHoras . 'h';
        if ($totalHoras === 0) return $minutos . 'm';
        return $totalHoras . 'h ' . $minutos . 'm';
    }
}