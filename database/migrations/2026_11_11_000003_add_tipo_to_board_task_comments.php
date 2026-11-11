<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // As notas de passagem ficam marcadas, e não confundidas com o que as pessoas escrevem:
        // é por elas que se sabe que um ticket passou pelas mãos de alguém, mesmo depois de sair delas.
        Schema::table('board_task_comments', function (Blueprint $table) {
            $table->string('tipo', 12)->default('nota')->after('user_id')->index(); // nota | passagem
        });

        // As que já existem foram escritas por pessoas
        DB::table('board_task_comments')->update(['tipo' => 'nota']);
    }

    public function down(): void
    {
        Schema::table('board_task_comments', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });
    }
};
