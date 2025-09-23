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
        Schema::create('cobro_placas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cobro_id')->constrained('cobros')->onUpdate('cascade')->onDelete('cascade');
            $table->date('fecha_inicio')->nullable()->comment('Fecha de inicio de facturación para la placa');
            $table->date('fecha_fin')->nullable()->comment('Fecha de fin de facturación para la placa');
            $table->string('placa', 20)->comment('Número de placa del vehículo');
            $table->decimal('monto_calculado', 10, 2)->comment('Monto calculado para esta placa específica');
            $table->integer('dias_prorrateados')->nullable()->comment('Días de prorateo si aplica');
            $table->decimal('factor_prorateo', 5, 4)->default(1.0)->comment('Factor de cálculo del prorateo (1.0 = mes completo)');
            $table->text('observaciones')->nullable()->comment('Observaciones específicas de la placa');
            $table->timestamps();

            // Índices
            $table->index('cobro_id', 'idx_cobro_placas_cobro_id');
            $table->index('placa', 'idx_cobro_placas_placa');
            $table->unique(['cobro_id', 'placa'], 'uk_cobro_placa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cobro_placas');
    }
};
