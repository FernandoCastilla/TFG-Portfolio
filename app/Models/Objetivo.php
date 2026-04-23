<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Objetivo
 *
 * Representa un objetivo de seguimiento propio de la aplicación,
 * vinculado a un proyecto concreto de OpenProject mediante su ID.
 *
 * Esta entidad fue diseñada para permitir que los gestores del
 * Vicerrectorado definan objetivos específicos sobre los proyectos
 * sincronizados desde la API, complementando la información que
 * OpenProject proporciona con metas y estados propios.
 *
 * Estado: pendiente de implementación (previsto para una versión futura).
 * La tabla 'objetivos' existe en base de datos pero aún no está conectada
 * a ninguna ruta ni vista de la aplicación.
 *
 * Campos de la tabla (ver migration 2026_03_10_150947_create_objetivos_table):
 *   - openproject_project_id : ID del proyecto en OpenProject al que pertenece
 *   - titulo                 : Título del objetivo
 *   - descripcion            : Descripción detallada (opcional)
 *   - estado                 : Estado del objetivo (Nuevo, En curso, Cumplido...)
 *   - fecha_limite           : Fecha límite para alcanzar el objetivo (opcional)
 */
class Objetivo extends Model
{
    protected $table = 'objetivos';

    protected $fillable = [
        'openproject_project_id',
        'titulo',
        'descripcion',
        'estado',
        'fecha_limite',
    ];

    protected $casts = [
        'fecha_limite' => 'date',
    ];
}