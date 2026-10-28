<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warranty_cases', function (Blueprint $table) {
            // Com peças (cotação Sielaff + encomenda à Alemanha) ou sem peças (só deslocação e mão de obra)
            $table->string('tipo', 20)->default('com_pecas')->after('cliente')->index();
            // Ordem de serviço da visita de diagnóstico: o que o técnico encontrou e o documento assinado
            $table->string('os_numero', 100)->nullable()->after('tipo');
            $table->date('os_em')->nullable()->after('os_numero');
            $table->text('os_texto')->nullable()->after('os_em');
            $table->string('os_path')->nullable()->after('os_texto');
            $table->string('os_nome')->nullable()->after('os_path');
        });
    }

    public function down(): void
    {
        Schema::table('warranty_cases', function (Blueprint $table) {
            $table->dropColumn(['tipo', 'os_numero', 'os_em', 'os_texto', 'os_path', 'os_nome']);
        });    }
};
