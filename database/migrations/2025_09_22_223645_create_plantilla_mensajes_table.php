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
        Schema::create('plantilla_mensajes', function (Blueprint $table) {
            $table->id();
            $table->string('tipo')->unique(); // 'creacion_recibo', 'recordatorio_pago'
            $table->string('nombre'); // Nombre descriptivo
            $table->text('asunto')->nullable(); // Para futuras funcionalidades de email
            $table->text('mensaje'); // Plantilla del mensaje
            $table->text('variables_disponibles')->nullable(); // JSON con variables disponibles
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plantilla_mensajes');
    }
};
