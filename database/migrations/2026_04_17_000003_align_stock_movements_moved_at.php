<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('stock_movements')) {
            return;
        }

        Schema::table('stock_movements', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_movements', 'moved_at')) {
                $table->timestamp('moved_at')->nullable()->after('notes');
            }
        });
    }

    public function down(): void
    {
        // Compatibilidade com base antiga; não removemos a coluna.
    }
};
