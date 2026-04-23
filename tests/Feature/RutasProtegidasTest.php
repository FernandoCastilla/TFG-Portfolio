<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verifica que las rutas protegidas rechazan peticiones no autenticadas.
 *
 * Solo dos rutas requieren login en UGR Portfolio:
 *   - POST /api/sincronizar  (lanza ugr:update contra la API institucional)
 *   - POST /logout           (manipula la sesión del usuario)
 *
 * El estándar de Laravel es redirigir a /login con código 302.
 */
class RutasProtegidasTest extends TestCase
{
    use RefreshDatabase;

    /** Un visitante no puede disparar una sincronización */
    public function test_sincronizar_sin_login_redirige_a_login(): void
    {
        $this->post('/api/sincronizar')
             ->assertRedirect('/login');
    }

    /** El logout no tiene sentido sin sesión activa */
    public function test_logout_sin_login_redirige_a_login(): void
    {
        $this->post('/logout')
             ->assertRedirect('/login');
    }

    /** Con login, la sincronización sí es accesible */
    public function test_sincronizar_con_login_es_accesible(): void
    {
        $user = User::factory()->create();

        // Artisan::call('ugr:update') fallará sin API real,
        // pero el middleware debe dejar pasar la petición (no 302)
        $response = $this->actingAs($user)->post('/api/sincronizar');

        $this->assertNotEquals(302, $response->getStatusCode());
    }
}