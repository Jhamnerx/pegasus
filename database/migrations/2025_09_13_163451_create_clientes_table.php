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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_cliente', 255);
            $table->string('ruc_dni', 20)->unique()->nullable()->comment('RUC o DNI del cliente');
            $table->string('telefono', 50)->nullable();
            $table->string('correo_electronico', 255)->nullable();
            $table->text('direccion')->nullable();
            $table->enum('estado', ['Activo', 'Inactivo', 'Pendiente', 'Atrasado'])
                ->default('Activo')
                ->comment('Estado general del cliente, podría derivarse de sus pagos');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
