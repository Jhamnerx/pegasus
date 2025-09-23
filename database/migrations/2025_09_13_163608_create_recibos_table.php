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
        Schema::create('recibos', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable();
            $table->string('numero_recibo', 50)->unique();

            $table->foreignId('cliente_id')->constrained('clientes')->onUpdate('cascade')->onDelete('restrict');
            $table->json('data_cliente');

            $table->foreignId('servicio_id')->nullable()->constrained('servicios')->onUpdate('cascade')->onDelete('set null');
            $table->json('data_servicio');

            $table->foreignId('cobro_id')->constrained('cobros')->onUpdate('cascade')->onDelete('cascade');
            $table->json('data_cobro');
            $table->unsignedBigInteger('cobro_placa_id')->nullable();
            $table->integer('cantidad_placas')->default(1);
            $table->decimal('monto_recibo', 10, 2);
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento');
            $table->date('fecha_inicio_periodo')->nullable()->comment('Fecha de inicio del período de facturación');
            $table->date('fecha_fin_periodo')->nullable()->comment('Fecha de fin del período de facturación');
            $table->timestamp('fecha_generacion')->useCurrent();

            $table->enum('estado_recibo', ['pendiente', 'pagado', 'vencido', 'anulado'])->default('pendiente');
            $table->date('fecha_pago')->nullable();
            $table->string('metodo_pago', 100)->nullable();
            $table->string('numero_referencia', 100)->nullable();
            $table->decimal('monto_pagado', 10, 2)->nullable();

            $table->string('moneda', 3)->default('PEN');
            $table->text('observaciones')->nullable();

            $table->json('notificaciones_enviadas')->nullable();
            $table->timestamp('proxima_notificacion')->nullable();

            $table->unsignedBigInteger('usuario_generador_id')->nullable();
            $table->timestamp('fecha_anulacion')->nullable();
            $table->text('motivo_anulacion')->nullable();
            $table->boolean('enviado_whatsapp')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index('numero_recibo', 'idx_recibo_numero');
            $table->index('cobro_id', 'idx_recibo_cobro');
            $table->index('cliente_id', 'idx_recibo_cliente');
            $table->index('servicio_id', 'idx_recibo_servicio');
            $table->index('estado_recibo', 'idx_recibo_estado');
            $table->index('fecha_vencimiento', 'idx_recibo_vencimiento');
            $table->index('fecha_emision', 'idx_recibo_emision');
            $table->index('proxima_notificacion', 'idx_recibo_proxima_notif');
            $table->index(['cobro_id', 'fecha_inicio_periodo', 'fecha_fin_periodo'], 'idx_recibo_cobro_periodo');

            $table->foreign('cobro_placa_id')->references('id')->on('cobro_placas')
                ->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recibos');
    }
};
