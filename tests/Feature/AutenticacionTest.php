<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests de autenticación: login, login fallido y logout.
 */
class AutenticacionTest extends TestCase
{
    use RefreshDatabase;

    /** Un usuario con credenciales correctas accede al dashboard */
    public function test_login_correcto_redirige_al_dashboard(): void
    {
        $user = User::factory()->create([
            'email'    => 'test@ugr.es',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email'    => 'test@ugr.es',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }

    /** Credenciales incorrectas devuelven al formulario con error */
    public function test_login_incorrecto_devuelve_error(): void
    {
        User::factory()->create(['email' => 'test@ugr.es']);

        $response = $this->post('/login', [
            'email'    => 'test@ugr.es',
            'password' => 'password_erroneo',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    /**
     * El logout destruye la sesión y redirige al dashboard público.
     * La app redirige a '/' (no a '/login') porque el dashboard es público
     * y no tiene sentido forzar al usuario a pasar por el login tras salir.
     */
    public function test_logout_destruye_sesion(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->post('/logout')
             ->assertRedirect('/');

        $this->assertGuest();
    }
}