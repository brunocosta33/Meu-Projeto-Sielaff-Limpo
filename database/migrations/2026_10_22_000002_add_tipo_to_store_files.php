<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ficheiros da loja separados em fotografias e IBN (PDF)
        Schema::table('store_files', function (Blueprint $table) {
            $table->string('tipo', 10)->default('foto')->after('store_id')->index();
        });
        DB::table('store_files')->where('mime', 'like', '%pdf%')->orWhere('nome', 'like', '%.pdf')->update(['tipo' => 'ibn']);
    }

    public function down(): void
    {
        Schema::table('store_files', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });
    }
};
