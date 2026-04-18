<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('technical_requests') || Schema::hasColumn('technical_requests', 'zona')) {
            return;
        }

        Schema::table('technical_requests', function (Blueprint $table) {
            $table->string('zona', 50)->nullable()->after('tipo_servico');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('technical_requests') || !Schema::hasColumn('technical_requests', 'zona')) {
            return;
        }

        Schema::table('technical_requests', function (Blueprint $table) {
            $table->dropColumn('zona');
        });
    }
};
