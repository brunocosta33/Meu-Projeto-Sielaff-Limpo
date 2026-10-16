<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Data em que a avaria aconteceu (pode ser registada mais tarde)
        Schema::table('warranty_cases', function (Blueprint $table) {
            $table->date('data_avaria')->nullable()->after('cliente');
        });
        DB::table('warranty_cases')->update(['data_avaria' => DB::raw('DATE(created_at)')]);
    }

    public function down(): void
    {
        Schema::table('warranty_cases', function (Blueprint $table) {
            $table->dropColumn('data_avaria');
        });
    }
};
