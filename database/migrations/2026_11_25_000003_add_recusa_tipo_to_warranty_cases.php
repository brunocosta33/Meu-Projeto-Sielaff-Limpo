<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Fecho sem reparação: ou o cliente recusou, ou cancelámos a cobrança. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warranty_cases', function (Blueprint $table) {
            $table->string('recusa_tipo', 20)->nullable()->after('motivo_recusa');
        });

        // Os que já estavam fechados assim foram todos recusas do cliente
        DB::table('warranty_cases')->whereNotNull('motivo_recusa')->update(['recusa_tipo' => 'cliente']);
    }

    public function down(): void
    {
        Schema::table('warranty_cases', function (Blueprint $table) {
            $table->dropColumn('recusa_tipo');
        });
    }
};
