<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // O código fica escrito no registo, tal como o nome: assim a linha vale por si
        // mesmo que o artigo mude de nome ou saia do catálogo
        Schema::table('tool_deliveries', function (Blueprint $table) {
            $table->string('codigo', 100)->nullable()->after('artigo')->index();
        });
    }

    public function down(): void
    {
        Schema::table('tool_deliveries', function (Blueprint $table) {
            $table->dropColumn('codigo');
        });
    }
};
