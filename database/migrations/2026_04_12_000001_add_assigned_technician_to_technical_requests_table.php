<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('technical_requests', 'assigned_technician_id')) {
            return;
        }

        Schema::table('technical_requests', function (Blueprint $table) {
            $table->foreignId('assigned_technician_id')
                ->nullable()
                ->after('updated_by')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('technical_requests', 'assigned_technician_id')) {
            return;
        }

        Schema::table('technical_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_technician_id');
        });
    }
};
