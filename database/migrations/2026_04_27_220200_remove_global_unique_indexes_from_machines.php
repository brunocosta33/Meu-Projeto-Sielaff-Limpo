<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('machines')) {
            return;
        }

        $this->dropUniqueIndexesForColumn('serial_number');
        $this->dropUniqueIndexesForColumn('ip_address');
    }

    public function down(): void
    {
        //
    }

    private function dropUniqueIndexesForColumn(string $column): void
    {
        if (!Schema::hasColumn('machines', $column)) {
            return;
        }

        $indexes = DB::select(
            'SHOW INDEX FROM machines WHERE Non_unique = 0 AND Column_name = ?',
            [$column]
        );

        foreach ($indexes as $index) {
            $indexName = $index->Key_name ?? null;

            if (!$indexName || $indexName === 'PRIMARY') {
                continue;
            }

            DB::statement('ALTER TABLE machines DROP INDEX `' . str_replace('`', '``', $indexName) . '`');
        }
    }
};
