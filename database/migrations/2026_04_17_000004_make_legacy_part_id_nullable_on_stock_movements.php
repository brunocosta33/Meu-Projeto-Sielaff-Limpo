<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('stock_movements') || !Schema::hasColumn('stock_movements', 'part_id')) {
            return;
        }

        DB::statement('ALTER TABLE stock_movements MODIFY part_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        // Compatibilidade com esquema antigo; não voltamos a tornar obrigatório.
    }
};
