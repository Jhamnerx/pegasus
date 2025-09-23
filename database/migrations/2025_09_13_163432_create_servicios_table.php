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
        Schema::create('servicios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_servicio', 150)->unique();
            $table->text('descripcion')->nullable();
            $table->decimal('precio_base', 10, 2)->nullable()->comment('Precio estándar del servicio si aplica');
            $table->boolean('activo')->default(true)->comment('Si el servicio está disponible para nuevos cobros');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servicios');
    }
};
