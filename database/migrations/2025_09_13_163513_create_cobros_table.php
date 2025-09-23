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
        Schema::create('cobros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('servicio_id')->nullable()->constrained('servicios')->onUpdate('cascade')->onDelete('set null');
            $table->string('descripcion_servicio_personalizado', 255)->nullable()
                ->comment('Para el caso de servicio Otro o descripción específica');
            $table->decimal('monto_base', 10, 2)->comment('Monto base del cobro');
            $table->unsignedInteger('cantidad_placas')->default(0)->comment('Número total de placas en este cobro');
            $table->decimal('monto_unitario', 10, 2)->nullable()->comment('Precio base por placa (copiado del servicio)');
            $table->boolean('tiene_prorateo')->default(false)->comment('Indica si el cobro tiene cálculos de prorateo');
            $table->string('periodo_facturacion', 50)->nullable()->comment('Período de facturación (ej: Agosto 2025, Q3 2025)');
            $table->string('moneda', 3)->default('PEN')->comment('Código ISO de la moneda (Ej: PEN, USD)');
            $table->date('fecha_inicio_periodo')->comment('Fecha de inicio del período a facturar');
            $table->date('fecha_fin_periodo')->comment('Fecha de fin del período a facturar');
            $table->integer('dias_para_vencimiento')->default(30)->comment('Días para calcular vencimiento desde creación del recibo');
            $table->enum('estado', ['activo', 'procesado', 'anulado'])->default('activo')
                ->comment('activo: pendiente de procesar recibos, procesado: recibos generados, anulado: cobro cancelado');
            $table->timestamp('fecha_procesamiento')->nullable()->comment('Fecha cuando se generaron los recibos');
            $table->text('notas')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('cliente_id');
            $table->index('servicio_id');
            $table->index('estado');
            $table->index('fecha_inicio_periodo');
            $table->index('fecha_fin_periodo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cobros');
    }
};
