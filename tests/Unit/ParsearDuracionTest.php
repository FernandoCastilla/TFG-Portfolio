<?php

namespace Tests\Unit;

use App\Http\Controllers\ProjectController;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitarios para el helper ProjectController::parsearDuracion().
 *
 * Valida la conversión de duraciones ISO 8601 (formato devuelto por la API
 * de OpenProject) a texto legible en horas y minutos.
 *
 * No requiere base de datos ni framework — son tests puros de lógica.
 */
class ParsearDuracionTest extends TestCase
{
    /** PT0S representa cero tiempo — no debe mostrarse */
    public function test_cero_devuelve_null(): void
    {
        $this->assertNull(ProjectController::parsearDuracion('PT0S'));
    }

    /** null se da cuando la API no devuelve el campo */
    public function test_null_devuelve_null(): void
    {
        $this->assertNull(ProjectController::parsearDuracion(null));
    }

    /** Caso simple: solo horas sin días ni minutos */
    public function test_solo_horas(): void
    {
        $this->assertSame('10h', ProjectController::parsearDuracion('PT10H'));
    }

    /** Caso con días: deben convertirse a horas (1 día = 24 h) */
    public function test_dias_y_horas_se_suman(): void
    {
        // 1 día + 18 horas = 42 horas
        $this->assertSame('42h', ProjectController::parsearDuracion('P1DT18H'));
    }

    /** Caso completo con días, horas y minutos */
    public function test_dias_horas_y_minutos(): void
    {
        // 3 días + 21 horas + 30 minutos = 93 horas + 30 minutos
        $this->assertSame('93h 30m', ProjectController::parsearDuracion('P3DT21H30M'));
    }

    /** Solo minutos, sin horas ni días */
    public function test_solo_minutos(): void
    {
        $this->assertSame('30m', ProjectController::parsearDuracion('PT30M'));
    }

    /** Cadena vacía debe comportarse como null */
    public function test_cadena_vacia_devuelve_null(): void
    {
        $this->assertNull(ProjectController::parsearDuracion(''));
    }
}