<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('technical_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('technical_requests', 'created_by')) {
                $table->integer('created_by')->nullable()->after('machine_id');
            }

            if (!Schema::hasColumn('technical_requests', 'updated_by')) {
                $table->integer('updated_by')->nullable()->after('created_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('technical_requests', function (Blueprint $table) {
            if (Schema::hasColumn('technical_requests', 'updated_by')) {
                $table->dropColumn('updated_by');
            }

            if (Schema::hasColumn('technical_requests', 'created_by')) {
                $table->dropColumn('created_by');
            }
        });
    }
};
