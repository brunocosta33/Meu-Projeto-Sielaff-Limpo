<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Passo entre a ordem de compra e a reparação: pedido de peças enviado para a Sielaff Alemanha
        Schema::table('warranty_cases', function (Blueprint $table) {
            $table->string('artigo_numero')->nullable()->after('oc_nome');
            $table->string('cliente_numero', 100)->nullable()->after('artigo_numero');
            $table->date('alemanha_enviado_em')->nullable()->after('cliente_numero');
        });
    }

    public function down(): void
    {
        Schema::table('warranty_cases', function (Blueprint $table) {
            $table->dropColumn(['artigo_numero', 'cliente_numero', 'alemanha_enviado_em']);
        });
    }
};
