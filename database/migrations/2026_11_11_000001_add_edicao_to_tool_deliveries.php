<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Passa a dar para corrigir uma entrega. Para o registo continuar a valer como prova,
        // fica escrito quem a corrigiu e quando.
        Schema::table('tool_deliveries', function (Blueprint $table) {
            $table->dateTime('editado_em')->nullable()->after('proalpha_por');
            $table->foreignId('editado_por')->nullable()->after('editado_em')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tool_deliveries', function (Blueprint $table) {
            $table->dropForeign(['editado_por']);
            $table->dropColumn(['editado_em', 'editado_por']);
        });
    }
};
