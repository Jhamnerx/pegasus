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
        Schema::table('recibos', function (Blueprint $table) {
            $table->foreign('cobro_placa_id', 'fk_recibos_cobro_placa_id')
                ->references('id')->on('cobro_placas')
                ->onUpdate('cascade')->onDelete('set null');

            $table->index('cobro_placa_id', 'idx_recibo_placa_cobro');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recibos', function (Blueprint $table) {
            $table->dropForeign('fk_recibos_cobro_placa_id');
            $table->dropIndex('idx_recibo_placa_cobro');
        });
    }
};
