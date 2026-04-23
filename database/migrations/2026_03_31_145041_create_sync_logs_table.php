<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MIGRATION OBSOLETA — NO SE EJECUTA EN PRODUCCIÓN
 *
 * Esta migration fue creada para almacenar el historial de sincronizaciones
 * con la API de OpenProject en base de datos MySQL.
 *
 * Durante el desarrollo se detectó que la conexión a MySQL se bloquea cuando
 * la VPN de la UGR (Cisco AnyConnect) está activa, debido a conflictos con
 * las reglas de iptables que establece el cliente VPN. Esto hacía que el
 * comando ugr:update se colgara indefinidamente al intentar guardar el log.
 *
 * Solución adoptada: el historial se migró a almacenamiento en fichero JSON
 * (storage/app/sync_log.json), gestionado por el modelo App\Models\SyncLog
 * que no depende de ninguna conexión a base de datos.
 *
 * Esta migration se conserva por trazabilidad del proceso de desarrollo.
 * La tabla sync_logs NO se usa en ninguna parte de la aplicación.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Migration obsoleta — ver comentario de cabecera.
        // La tabla se crea igualmente para no romper el estado de migraciones,
        // pero no es utilizada por ningún modelo o controlador de la aplicación.
        Schema::create('sync_logs', function (Blueprint $table) {
            $table->id();
            $table->dateTime('ejecutado_en');
            $table->float('duracion_segundos');
            $table->integer('proyectos_api');
            $table->integer('tareas_api');
            $table->integer('proyectos_nuevos');
            $table->integer('tareas_nuevas');
            $table->integer('proyectos_total');
            $table->integer('tareas_total');
            $table->string('estado');
            $table->text('mensaje_error')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_logs');
    }
};