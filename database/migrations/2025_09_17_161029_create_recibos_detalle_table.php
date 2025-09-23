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
        Schema::create('recibos_detalle', function (Blueprint $table) {
            $table->id();

            // Relación con el recibo principal
            $table->foreignId('recibo_id')->constrained('recibos')->onUpdate('cascade')->onDelete('cascade');

            // Relación con cobro_placa (si es detalle específico de placa)
            $table->foreignId('cobro_placa_id')->nullable()->constrained('cobro_placas')->onUpdate('cascade')->onDelete('set null');

            // Información de la línea de detalle
            $table->string('concepto', 255)->comment('Descripción del concepto facturado');
            $table->string('placa', 20)->nullable()->comment('Placa específica (si aplica)');

            // Información del período y fechas
            $table->date('fecha_inicio_periodo')->comment('Fecha de inicio del período de esta línea');
            $table->date('fecha_fin_periodo')->comment('Fecha de fin del período de esta línea');
            $table->integer('dias_calculados')->comment('Días del período para esta línea');

            // Información financiera
            $table->decimal('monto_base', 10, 2)->comment('Monto base antes del prorrateo');
            $table->decimal('factor_prorateo', 8, 4)->default(1.0000)->comment('Factor de prorrateo aplicado (1.0000 = sin prorrateo)');
            $table->decimal('monto_calculado', 10, 2)->comment('Monto final después del prorrateo');
            $table->string('moneda', 3)->default('PEN');

            // Información adicional
            $table->text('observaciones')->nullable()->comment('Observaciones específicas de esta línea');
            $table->boolean('es_prorrateo')->default(false)->comment('Indica si esta línea tiene cálculo de prorrateo');

            // Orden de las líneas en el recibo
            $table->integer('orden')->default(1)->comment('Orden de aparición en el recibo');

            $table->timestamps();

            // Índices
            $table->index('recibo_id');
            $table->index('cobro_placa_id');
            $table->index('placa');
            $table->index(['recibo_id', 'orden']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recibos_detalle');
    }
};
