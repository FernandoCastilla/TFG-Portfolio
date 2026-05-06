<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Services\WorkPackageTreeBuilder;
use App\Services\FamilyPaginator;
use App\Services\KpiCalculator;
use Barryvdh\DomPDF\Facade\Pdf;

class ProjectController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // LISTADO DE PROYECTOS
    // ─────────────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $rutaProyectos = storage_path('app/proyectos_ugr.json');
        $proyectos = [];

        if (File::exists($rutaProyectos)) {
            $data = json_decode(File::get($rutaProyectos), true);
            $proyectos = $data['_embedded']['elements'] ?? [];
        }

        $busqueda  = $request->query('busqueda', '');
        $soloActivos = $request->query('activos', '');

        if (!empty($busqueda)) {
            $proyectos = array_filter($proyectos, fn($p) =>
                stripos($p['name'] ?? '', $busqueda) !== false
            );
        }

        if ($soloActivos === '1') {
            $proyectos = array_filter($proyectos, fn($p) => $p['active'] ?? false);
        }

        return view('proyectos.index', compact('proyectos', 'busqueda', 'soloActivos'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DETALLE DE PROYECTO
    // ─────────────────────────────────────────────────────────────────────────

    public function show($id)
    {
        $proyecto = $this->encontrarProyecto((int) $id);
        abort_unless($proyecto, 404, 'Proyecto no encontrado');

        return view('proyectos.show', compact('proyecto'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // WORK PACKAGES DE UN PROYECTO
    // ─────────────────────────────────────────────────────────────────────────

    public function tareas(Request $request, $id)
    {
        $proyecto = $this->encontrarProyecto((int) $id);
        abort_unless($proyecto, 404, 'Proyecto no encontrado');

        // Filtros activos
        $selectedSort      = $request->query('sort');
        $selectedTipo      = $request->query('tipo');
        $selectedEstado    = $request->query('estado');
        $selectedAsignado  = $request->query('asignado');
        $selectedSearch    = $request->query('search');
        $selectedPrioridad = $request->query('prioridad');
        $selectedEntidad   = $request->query('entidad');
        $selectedYear      = $request->query('year');
        $selectedMonth     = $request->query('month');

        // 1. Cargar todas las tareas del proyecto
        $tareas = $this->cargarTareasDeProyecto((int) $id);

        // 2. Extraer valores únicos para los desplegables (antes de filtrar)
        $tipos      = collect($tareas)->map(fn($t) => $t['_links']['type']['title']     ?? 'Desconocido')->unique()->sort()->values();
        $estados    = collect($tareas)->map(fn($t) => $t['_links']['status']['title']   ?? 'Desconocido')->unique()->sort()->values();
        $asignados  = collect($tareas)->map(fn($t) => $t['_links']['assignee']['title'] ?? 'Sin asignar')->unique()->sort()->values();
        $prioridades = collect($tareas)->map(fn($t) => $t['_links']['priority']['title'] ?? 'Normal')->unique()->sort()->values();
        $entidades  = collect($tareas)->map(fn($t) => $t['customField3'] ?? null)->filter()->unique()->sort()->values();

        // 3. Aplicar filtros
        $tareas = $this->filtrar($tareas, $selectedTipo, $selectedEstado, $selectedAsignado,
                                 $selectedYear, $selectedMonth, $selectedSearch,
                                 $selectedPrioridad, $selectedEntidad);

        // 4. Aplicar ordenación
        $tareas = $this->ordenar($tareas, $selectedSort);

        // 5. Calcular datos para la gráfica (sobre el total filtrado, no la página)
        [$graficaLabels, $graficaDatos] = $this->calcularGrafica($tareas);

        // 6. Construir árbol jerárquico padre-hijo
        $tareas = (new WorkPackageTreeBuilder())->build($tareas);

        // 7. Paginar por familias completas
        $resultado = (new FamilyPaginator(20))->paginate(
            $tareas,
            (int) $request->query('page', 1),
            $request->url(),
            $request->except('page')
        );

        $tareas               = $resultado['paginator'];
        $totalTareasFiltradas = $resultado['total'];

        return view('proyectos.tareas', compact(
            'proyecto', 'tareas', 'graficaLabels', 'graficaDatos',
            'tipos', 'estados', 'asignados', 'prioridades', 'entidades',
            'selectedTipo', 'selectedEstado', 'selectedAsignado', 'selectedSearch',
            'selectedPrioridad', 'selectedSort', 'selectedEntidad',
            'totalTareasFiltradas'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // EXPORTAR PDF
    // ─────────────────────────────────────────────────────────────────────────

    public function exportarPdf($id)
    {
        $proyecto = $this->encontrarProyecto((int) $id);
        abort_unless($proyecto, 404, 'Proyecto no encontrado');

        $tareas = $this->cargarTareasDeProyecto((int) $id);
        $kpis   = KpiCalculator::calcular($tareas);

        $pdf = Pdf::loadView('proyectos.pdf', compact('proyecto', 'tareas', 'kpis'))
                  ->setPaper('a4', 'landscape');

        return $pdf->download('informe_UGR_proyecto_' . $proyecto['id'] . '.pdf');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // EXPORTAR CSV
    // ─────────────────────────────────────────────────────────────────────────

    public function exportarCsv($id)
    {
        $proyecto = $this->encontrarProyecto((int) $id);
        abort_unless($proyecto, 404, 'Proyecto no encontrado');

        $tareas        = $this->cargarTareasDeProyecto((int) $id);
        $nombreFichero = 'ugr_portfolio_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $proyecto['name'] ?? $id)
                       . '_' . date('Ymd') . '.csv';

        $handle = fopen('php://temp', 'r+');
        fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8 para Excel

        fputcsv($handle, [
            'ID', 'Asunto', 'Tipo', 'Estado', 'Progreso (%)',
            'Asignado a', 'T. Estimado', 'T. Dedicado',
            'Entidad Solicitante', 'Persona Contacto', 'Email Contacto',
            'Fecha Inicio', 'Fecha Fin', 'Prioridad', 'Proyecto',
        ], ';');

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
                $tarea['customField3'] ?? '',
                $tarea['customField4'] ?? '',
                $tarea['customField5'] ?? '',
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
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS PRIVADOS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Encuentra un proyecto por ID en el JSON local.
     */
    private function encontrarProyecto(int $id): ?array
    {
        $ruta = storage_path('app/proyectos_ugr.json');
        if (!File::exists($ruta)) return null;

        $data = json_decode(File::get($ruta), true);
        return collect($data['_embedded']['elements'] ?? [])->firstWhere('id', $id);
    }

    /**
     * Devuelve todas las tareas de un proyecto concreto desde el JSON local.
     */
    private function cargarTareasDeProyecto(int $id): array
    {
        $ruta = storage_path('app/tareas_ugr.json');
        if (!File::exists($ruta)) return [];

        $data = json_decode(File::get($ruta), true);
        $todas = $data['_embedded']['elements'] ?? [];

        return array_values(array_filter($todas, fn($t) =>
            str_ends_with($t['_links']['project']['href'] ?? '', '/' . $id)
        ));
    }

    /**
     * Aplica todos los filtros activos sobre un array de tareas.
     */
    private function filtrar(
        array $tareas,
        ?string $tipo, ?string $estado, ?string $asignado,
        ?string $year, ?string $month, ?string $search,
        ?string $prioridad, ?string $entidad
    ): array {
        if (!$tipo && !$estado && !$asignado && !$year && !$month
            && !$search && !$prioridad && !$entidad) {
            return $tareas;
        }

        return array_values(array_filter($tareas, function ($t) use (
            $tipo, $estado, $asignado, $year, $month, $search, $prioridad, $entidad
        ) {
            if ($tipo      && ($t['_links']['type']['title']     ?? '') !== $tipo)      return false;
            if ($estado    && ($t['_links']['status']['title']   ?? '') !== $estado)    return false;
            if ($asignado  && ($t['_links']['assignee']['title'] ?? '') !== $asignado)  return false;
            if ($prioridad && ($t['_links']['priority']['title'] ?? '') !== $prioridad) return false;
            if ($entidad   && ($t['customField3']                ?? '') !== $entidad)   return false;

            if ($search && stripos($t['subject'] ?? '', $search) === false) return false;

            if ($year) {
                $fecha = $t['startDate'] ?? $t['createdAt'] ?? null;
                if (!$fecha) return false;
                if (date('Y', strtotime($fecha)) != $year) return false;
                if ($month && date('m', strtotime($fecha)) != $month) return false;
            }

            return true;
        }));
    }

    /**
     * Ordena las tareas según el criterio seleccionado.
     */
    private function ordenar(array $tareas, ?string $sort): array
    {
        if (!$sort) return $tareas;

        $col = collect($tareas);

        return match($sort) {
            'date_desc'     => $col->sortByDesc(fn($t) => $t['startDate']      ?? '')->values()->all(),
            'date_asc'      => $col->sortBy(fn($t)     => $t['startDate']      ?? '')->values()->all(),
            'progress_desc' => $col->sortByDesc(fn($t) => $t['percentageDone'] ?? 0)->values()->all(),
            'progress_asc'  => $col->sortBy(fn($t)     => $t['percentageDone'] ?? 0)->values()->all(),
            'name_asc'      => $col->sortBy(fn($t)     => strtolower($t['subject'] ?? ''))->values()->all(),
            'name_desc'     => $col->sortByDesc(fn($t) => strtolower($t['subject'] ?? ''))->values()->all(),
            default         => $tareas,
        };
    }

    /**
     * Calcula los datos para la gráfica de distribución por estado.
     * Se ejecuta sobre el total filtrado, no sobre la página actual.
     *
     * @return array{0: array, 1: array}  [labels, datos]
     */
    private function calcularGrafica(array $tareas): array
    {
        $conteo = [];
        foreach ($tareas as $t) {
            $estado = $t['_links']['status']['title'] ?? 'Desconocido';
            $conteo[$estado] = ($conteo[$estado] ?? 0) + 1;
        }
        return [array_keys($conteo), array_values($conteo)];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // UTILIDAD PÚBLICA — usada también desde las vistas Blade
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Convierte una duración ISO 8601 en texto legible.
     * Ej: "P3DT21H30M" → "93h 30m", "PT10H" → "10h", "PT0S" → null
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