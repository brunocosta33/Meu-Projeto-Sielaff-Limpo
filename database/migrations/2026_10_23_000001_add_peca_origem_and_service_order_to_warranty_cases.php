<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warranty_cases', function (Blueprint $table) {
            // Peça confirmada: 'recebida' (chegou da Alemanha) ou 'stock' (já a tínhamos connosco)
            $table->string('peca_origem', 20)->nullable()->after('peca_recebida_em');
            // Ordem de serviço anexada ao fechar a reparação
            $table->string('servico_path')->nullable()->after('fechado_em');
            $table->string('servico_nome')->nullable()->after('servico_path');
        });
        DB::table('warranty_cases')->whereNotNull('peca_recebida_em')->update(['peca_origem' => 'recebida']);
    }

    public function down(): void
    {
        Schema::table('warranty_cases', function (Blueprint $table) {
            $table->dropColumn(['peca_origem', 'servico_path', 'servico_nome']);
        });
    }
};
