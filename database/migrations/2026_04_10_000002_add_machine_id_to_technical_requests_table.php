<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('technical_requests', 'machine_id')) {
            return;
        }

        Schema::table('technical_requests', function (Blueprint $table) {
            $table->integer('machine_id')->nullable()->after('store_id');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('technical_requests', 'machine_id')) {
            return;
        }

        Schema::table('technical_requests', function (Blueprint $table) {
            $table->dropColumn('machine_id');
        });
    }
};
