<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('stock_movements') || Schema::hasColumn('stock_movements', 'machine_id')) {
            return;
        }

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreignId('machine_id')
                ->nullable()
                ->after('technician_id')
                ->constrained('machines')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('stock_movements') || !Schema::hasColumn('stock_movements', 'machine_id')) {
            return;
        }

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('machine_id');
        });
    }
};
