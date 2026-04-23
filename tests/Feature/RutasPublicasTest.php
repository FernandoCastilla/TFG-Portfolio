<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Verifica que las rutas de datos son accesibles sin autenticación.
 *
 * UGR Portfolio expone los datos del Vicerrectorado de forma pública
 * (decisión del tutor). Estos tests garantizan que ningún middleware
 * de auth se haya colado en rutas que deben permanecer abiertas.
 */
class RutasPublicasTest extends TestCase
{
    public function test_dashboard_accesible_sin_login(): void
    {
        $this->get('/')->assertStatus(200);
    }

    public function test_listado_proyectos_accesible_sin_login(): void
    {
        $this->get('/proyectos')->assertStatus(200);
    }

    public function test_calendario_accesible_sin_login(): void
    {
        $this->get('/calendario')->assertStatus(200);
    }

    public function test_buscador_accesible_sin_login(): void
    {
        $this->get('/buscar')->assertStatus(200);
    }

    public function test_tareas_de_proyecto_real_accesibles_sin_login(): void
    {
        // Proyecto #167 ([SW] Cuadros de mando) existe en los datos de prueba
        $this->get('/proyectos/167/tareas')->assertStatus(200);
    }
}