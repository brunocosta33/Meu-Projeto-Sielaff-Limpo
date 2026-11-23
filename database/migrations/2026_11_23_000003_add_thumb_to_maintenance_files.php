<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Miniatura das fotografias, para a lista não ter de carregar as originais. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_maintenance_files', function (Blueprint $table) {
            $table->string('thumb_path')->nullable()->after('path');
        });
    }

    public function down(): void
    {
        Schema::table('store_maintenance_files', function (Blueprint $table) {
            $table->dropColumn('thumb_path');
        });
    }
};
