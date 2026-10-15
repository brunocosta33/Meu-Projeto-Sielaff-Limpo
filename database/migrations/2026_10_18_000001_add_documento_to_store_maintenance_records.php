<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Folha de obra / relatório de serviço da manutenção anual
        Schema::table('store_maintenance_records', function (Blueprint $table) {
            $table->string('documento_path')->nullable()->after('observacoes');
            $table->string('documento_nome')->nullable()->after('documento_path');
        });
    }

    public function down(): void
    {
        Schema::table('store_maintenance_records', function (Blueprint $table) {
            $table->dropColumn(['documento_path', 'documento_nome']);
        });
    }
};
