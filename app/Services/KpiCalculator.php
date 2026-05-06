<?php

namespace App\Services;

/**
 * Calcula los indicadores KPI de salud de un proyecto.
 *
 * Centraliza la lógica de semáforo y métricas de progreso que antes
 * estaba dispersa entre el controlador y las vistas.
 */
class KpiCalculator
{
    /**
     * Estados personalizados de la instancia UGR de OpenProject
     * que se consideran como "completado".
     */
    private const ESTADOS_COMPLETADOS = [
        'H_Validado_100%',
        'H_Finalizado_90%',
        'S_Validada_100%',
        'T_Finalizada_90%',
    ];

    /**
     * Estados que se consideran "en progreso".
     */
    private const ESTADOS_EN_PROGRESO = [
        'In progress',
        'H_Progreso_25%',
        'H_Progreso_50%',
        'H_Progreso_75%',
        'S_Progreso_50%',
        'T_Progreso_25%',
        'T_Progreso_75%',
    ];

    /**
     * Calcula los KPIs de un proyecto a partir de su lista de tareas.
     *
     * @param  array $tareas  Array de Work Packages del proyecto
     * @return array{
     *   progresoMedio: float,
     *   pctCompletadas: int,
     *   pctEnProgreso: int,
     *   pctSinAsignar: int,
     *   semaforo: string
     * }
     */
    public static function calcular(array $tareas): array
    {
        $total = count($tareas);

        if ($total === 0) {
            return [
                'progresoMedio'  => 0,
                'pctCompletadas' => 0,
                'pctEnProgreso'  => 0,
                'pctSinAsignar'  => 0,
                'semaforo'       => 'rojo',
            ];
        }

        $sumaProgreso  = 0;
        $completadas   = 0;
        $enProgreso    = 0;
        $sinAsignar    = 0;

        foreach ($tareas as $t) {
            $estado   = $t['_links']['status']['title']   ?? '';
            $asignado = $t['_links']['assignee']['title'] ?? '';

            $sumaProgreso += (int) ($t['percentageDone'] ?? 0);

            if (in_array($estado, self::ESTADOS_COMPLETADOS)) {
                $completadas++;
            } elseif (in_array($estado, self::ESTADOS_EN_PROGRESO)) {
                $enProgreso++;
            }

            if (empty($asignado)) {
                $sinAsignar++;
            }
        }

        $pctCompletadas = (int) round($completadas / $total * 100);
        $pctEnProgreso  = (int) round($enProgreso  / $total * 100);

        $semaforo = match(true) {
            $pctCompletadas >= 60                               => 'verde',
            $pctCompletadas >= 30 || $pctEnProgreso >= 40      => 'amarillo',
            default                                             => 'rojo',
        };

        return [
            'progresoMedio'  => round($sumaProgreso / $total, 1),
            'pctCompletadas' => $pctCompletadas,
            'pctEnProgreso'  => $pctEnProgreso,
            'pctSinAsignar'  => (int) round($sinAsignar / $total * 100),
            'semaforo'       => $semaforo,
        ];
    }
}