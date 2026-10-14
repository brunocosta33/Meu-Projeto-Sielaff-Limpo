<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Função do colaborador (hotliner, tecnico, coordenador, country_manager, administrativa)
        Schema::table('hr_staff', function (Blueprint $table) {
            $table->string('funcao', 30)->nullable()->after('user_id');
        });

        DB::table('hr_staff')->where('is_hotliner', true)->update(['funcao' => 'hotliner']);

        Schema::table('hr_staff', function (Blueprint $table) {
            $table->dropColumn('is_hotliner');
        });
    }

    public function down(): void
    {
        Schema::table('hr_staff', function (Blueprint $table) {
            $table->boolean('is_hotliner')->default(false)->after('user_id');
        });

        DB::table('hr_staff')->where('funcao', 'hotliner')->update(['is_hotliner' => true]);

        Schema::table('hr_staff', function (Blueprint $table) {
            $table->dropColumn('funcao');
        });
    }
};
