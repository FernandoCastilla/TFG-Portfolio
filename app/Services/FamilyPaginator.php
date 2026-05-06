<?php

namespace App\Services;

use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Paginador que respeta familias completas de Work Packages.
 *
 * La paginación estándar por número fijo de elementos corta los grupos
 * padre-hijo entre páginas. Este paginador garantiza que un padre y todos
 * sus descendientes siempre aparecen en la misma página, cortando solo
 * entre familias raíz (nivel === 0).
 */
class FamilyPaginator
{
    private int $perPage;

    public function __construct(int $perPage = 20)
    {
        $this->perPage = $perPage;
    }

    /**
     * Pagina un array de tareas ordenado jerárquicamente.
     *
     * @param  array  $tareas       Array ya ordenado por WorkPackageTreeBuilder
     * @param  int    $currentPage  Página solicitada
     * @param  string $url          URL base para los enlaces de paginación
     * @param  array  $query        Query string actual (filtros activos)
     * @return array{paginator: LengthAwarePaginator, total: int}
     */
    public function paginate(array $tareas, int $currentPage, string $url, array $query): array
    {
        // Agrupar en familias: nueva familia cada vez que aparece nivel === 0
        $familias      = [];
        $familiaActual = [];

        foreach ($tareas as $t) {
            if (($t['nivel'] ?? 0) === 0 && !empty($familiaActual)) {
                $familias[]    = $familiaActual;
                $familiaActual = [];
            }
            $familiaActual[] = $t;
        }
        if (!empty($familiaActual)) {
            $familias[] = $familiaActual;
        }

        // Distribuir familias en páginas sin partirlas
        $paginas      = [];
        $paginaActual = [];
        $countActual  = 0;

        foreach ($familias as $familia) {
            if ($countActual > 0 && $countActual + count($familia) > $this->perPage) {
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
        $totalReal    = array_sum(array_map('count', $paginas));

        // LengthAwarePaginator con trick para que lastPage() devuelva
        // el número real de páginas calculado por familias
        $paginator = new LengthAwarePaginator(
            $tareasPagina,
            $totalPaginas * $this->perPage,
            $this->perPage,
            $currentPage,
            ['path' => $url, 'query' => $query]
        );

        return [
            'paginator' => $paginator,
            'total'     => $totalReal,
        ];
    }
}