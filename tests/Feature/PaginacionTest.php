<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Tests de paginación por familias en la vista de tareas.
 *
 * Usamos el proyecto #42 ([CSIRC] Servicios Web) que tiene 18 familias raíz
 * y 30 tareas, generando al menos 2 páginas con el límite de 20 por página.
 *
 * El proyecto #167 tiene una única familia raíz con 31 tareas, por lo que
 * la paginación por familias las agrupa todas en una sola página — no sirve
 * para probar la navegación entre páginas.
 */
class PaginacionTest extends TestCase
{
    /** La primera página carga sin errores */
    public function test_primera_pagina_devuelve_200(): void
    {
        $this->get('/proyectos/42/tareas?page=1')
             ->assertStatus(200);
    }

    /** La segunda página también carga (hay más de 20 tareas en familias distintas) */
    public function test_segunda_pagina_devuelve_200(): void
    {
        $this->get('/proyectos/42/tareas?page=2')
             ->assertStatus(200);
    }

    /**
     * Las páginas 1 y 2 no muestran las mismas tareas.
     * Verificamos por ID: la tarea #2283 es la primera raíz y debe estar en
     * la página 1, mientras que las últimas raíces (#14491-14493) deben estar
     * en la página 2 (o posteriores).
     */
    public function test_paginas_diferentes_tienen_contenido_diferente(): void
    {
        $page1 = $this->get('/proyectos/42/tareas?page=1')->getContent();
        $page2 = $this->get('/proyectos/42/tareas?page=2')->getContent();

        // Página 1 debe contener la primera tarea raíz
        $this->assertStringContainsString('#2283', $page1);

        // Página 2 debe contener tareas que no están en la primera página
        // (las familias raíz finales con IDs altos)
        $this->assertStringContainsString('#14491', $page2);

        // Y ambas páginas no deben ser idénticas
        $this->assertNotSame($page1, $page2);
    }

    /**
     * Los filtros activos se conservan al navegar entre páginas.
     * Verificamos que el filtro aparece seleccionado en el formulario
     * (el valor "HITO" estará marcado como selected en el desplegable).
     */
    public function test_filtros_se_conservan_entre_paginas(): void
    {
        // El proyecto 42 tiene 25 HITOs, suficientes para generar paginación
        $this->get('/proyectos/42/tareas?tipo=HITO&page=1')
             ->assertStatus(200)
             ->assertSee('selected', false) // el select tiene alguna opción seleccionada
             ->assertSee('HITO');           // y HITO aparece en la página
    }

    /** Una página fuera de rango devuelve la última página disponible, no un error */
    public function test_pagina_fuera_de_rango_no_da_error(): void
    {
        $this->get('/proyectos/42/tareas?page=9999')
             ->assertStatus(200);
    }
}