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
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('telefono_1', 20)->nullable()->after('telefono');
            $table->string('telefono_2', 20)->nullable()->after('telefono_1');
            $table->string('telefono_3', 20)->nullable()->after('telefono_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn(['telefono_1', 'telefono_2', 'telefono_3']);
        });
    }
};
