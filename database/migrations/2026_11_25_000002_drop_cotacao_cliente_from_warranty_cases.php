<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Com peças já não se anexa a cotação ao enviar ao cliente: é a mesma de "Cotação recebida". */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('warranty_cases', 'cotacao_cliente_path')) {
            Schema::table('warranty_cases', function (Blueprint $table) {
                $table->dropColumn(['cotacao_cliente_path', 'cotacao_cliente_nome']);
            });
        }
    }

    public function down(): void
    {
        // Nada a repor: as colunas deixaram de ser usadas.
    }
};
