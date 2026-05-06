<?php

namespace App\Services;

/**
 * Construye el árbol jerárquico padre-hijo de Work Packages.
 *
 * Toma un array plano de tareas y devuelve el mismo array reordenado
 * de forma que cada tarea aparece inmediatamente seguida de sus hijos,
 * con el campo 'nivel' indicando la profundidad en el árbol (0 = raíz).
 *
 * Se instancia en el controlador para mantener la lógica de árbol
 * separada de la lógica de enrutamiento y presentación.
 */
class WorkPackageTreeBuilder
{
    private array $tareasPorId   = [];
    private array $hijosPorPadre = [];
    private array $tareasOrdenadas = [];
    private array $procesados    = [];

    /**
     * Toma un array plano de Work Packages y devuelve el array
     * reordenado con jerarquía, añadiendo los campos:
     *   - 'nivel'       : profundidad en el árbol (0 = raíz)
     *   - 'padre_id'    : ID del padre, o null si es raíz
     *   - 'tiene_hijos' : true si la tarea tiene al menos un hijo
     */
    public function build(array $tareas): array
    {
        $this->tareasPorId   = [];
        $this->hijosPorPadre = [];
        $this->tareasOrdenadas = [];
        $this->procesados    = [];

        // Paso 1: identificar qué IDs actúan como padre
        $idsPadres = [];
        foreach ($tareas as $t) {
            $hrefPadre = $t['_links']['parent']['href'] ?? null;
            if ($hrefPadre) {
                $idsPadres[] = (int) basename($hrefPadre);
            }
        }

        // Paso 2: construir diccionarios por ID y por padre
        foreach ($tareas as $t) {
            $hrefPadre       = $t['_links']['parent']['href'] ?? null;
            $t['padre_id']   = $hrefPadre ? (int) basename($hrefPadre) : null;
            $t['tiene_hijos'] = in_array($t['id'], $idsPadres);

            $this->tareasPorId[$t['id']] = $t;

            if ($t['padre_id']) {
                $this->hijosPorPadre[$t['padre_id']][] = $t['id'];
            }
        }

        // Paso 3: recorrer recursivamente desde las raíces
        foreach ($this->tareasPorId as $id => $t) {
            if (!$t['padre_id'] || !isset($this->tareasPorId[$t['padre_id']])) {
                $this->insertarNodo($id, 0);
            }
        }

        return $this->tareasOrdenadas;
    }

    private function insertarNodo(int $idTarea, int $nivel): void
    {
        if (in_array($idTarea, $this->procesados)) {
            return;
        }

        $this->procesados[] = $idTarea;
        $tarea = $this->tareasPorId[$idTarea];
        $tarea['nivel'] = $nivel;
        $this->tareasOrdenadas[] = $tarea;

        if (isset($this->hijosPorPadre[$idTarea])) {
            foreach ($this->hijosPorPadre[$idTarea] as $idHijo) {
                if (isset($this->tareasPorId[$idHijo])) {
                    $this->insertarNodo($idHijo, $nivel + 1);
                }
            }
        }
    }
}