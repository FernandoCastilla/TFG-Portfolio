<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Tests sobre las vistas de proyectos y tareas.
 *
 * Trabajan directamente sobre los ficheros JSON del storage
 * (proyectos_ugr.json y tareas_ugr.json), que actúan como
 * la "base de datos" de la aplicación para estos datos.
 *
 * IDs utilizados (extraídos de los datos reales sincronizados):
 *   - 167 → [SW] Cuadros de mando (31 tareas)
 *   - 42  → [CSIRC] Servicios Web (30 tareas)
 */
class ProyectosTest extends TestCase
{
    /** El índice de proyectos carga sin errores */
    public function test_listado_proyectos_devuelve_200(): void
    {
        $this->get('/proyectos')->assertStatus(200);
    }

    /** Un proyecto existente devuelve su vista de detalle */
    public function test_proyecto_existente_devuelve_200(): void
    {
        $this->get('/proyectos/167')->assertStatus(200);
    }

    /** Un ID inexistente devuelve 404 */
    public function test_proyecto_inexistente_devuelve_404(): void
    {
        $this->get('/proyectos/99999')->assertStatus(404);
    }

    /** La vista de tareas de un proyecto real carga correctamente */
    public function test_tareas_de_proyecto_existente_devuelve_200(): void
    {
        $this->get('/proyectos/167/tareas')->assertStatus(200);
    }

    /** La vista de tareas de un proyecto inexistente devuelve 404 */
    public function test_tareas_de_proyecto_inexistente_devuelve_404(): void
    {
        $this->get('/proyectos/99999/tareas')->assertStatus(404);
    }

    /** La vista de tareas contiene el nombre del proyecto */
    public function test_tareas_muestra_nombre_del_proyecto(): void
    {
        $this->get('/proyectos/167/tareas')
             ->assertStatus(200)
             ->assertSee('Cuadros de mando');
    }
}