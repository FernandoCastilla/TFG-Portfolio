<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('objetivos', function (Blueprint $table) {
            $table->id(); // Tu ID interno
            
            // Vincula con la API de la UGR
            $table->unsignedBigInteger('openproject_project_id'); 
            
            // Los datos objetivo
            $table->string('titulo');
            $table->text('descripcion')->nullable(); // nullable = puede estar vacío
            $table->string('estado')->default('Nuevo'); // Nuevo, En curso, Cumplido...
            $table->date('fecha_limite')->nullable();
            
            $table->timestamps(); // Crea automáticamente 'created_at' y 'updated_at'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('objetivos');
    }
};
