<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Passo entre o pedido à Alemanha e a reparação: peça recebida
        Schema::table('warranty_cases', function (Blueprint $table) {
            $table->date('peca_recebida_em')->nullable()->after('alemanha_enviado_em');
        });
    }

    public function down(): void
    {
        Schema::table('warranty_cases', function (Blueprint $table) {
            $table->dropColumn('peca_recebida_em');
        });
    }
};
