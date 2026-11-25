<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** A cotação que se envia ao cliente (com peças), depois de chegar a da Alemanha. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warranty_cases', function (Blueprint $table) {
            $table->string('cotacao_cliente_path')->nullable()->after('enviada_para');
            $table->string('cotacao_cliente_nome')->nullable()->after('cotacao_cliente_path');
        });
    }

    public function down(): void
    {
        Schema::table('warranty_cases', function (Blueprint $table) {
            $table->dropColumn(['cotacao_cliente_path', 'cotacao_cliente_nome']);
        });
    }
};
