<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Ao fim de semana faz-se outra coisa: cada tarefa passa a dizer em que dias se aplica. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotline_checklist_items', function (Blueprint $table) {
            // todos | semana | fim_de_semana
            $table->string('dias', 16)->default('todos')->after('turno');
        });
    }

    public function down(): void
    {
        Schema::table('hotline_checklist_items', function (Blueprint $table) {
            $table->dropColumn('dias');
        });
    }
};
