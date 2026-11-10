<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A peça sai do armazém aqui, mas também tem de ser passada no ProAlpha — e quase nunca
        // é no mesmo momento. Guarda-se a data em vez de um sim/não: vazio = ainda por lançar,
        // preenchido = lançado (e fica-se a saber quando e por quem).
        Schema::table('tool_deliveries', function (Blueprint $table) {
            $table->dateTime('proalpha_em')->nullable()->after('observacoes')->index();
            $table->foreignId('proalpha_por')->nullable()->after('proalpha_em')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tool_deliveries', function (Blueprint $table) {
            $table->dropForeign(['proalpha_por']);
            $table->dropColumn(['proalpha_em', 'proalpha_por']);
        });
    }
};
