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

        // Coluna simples e indexada (sem foreign key, pois machines.id pode ter
        // um tipo incompatível — int vs bigint — e a integridade é garantida na app).
        if (!Schema::hasColumn('stock_movements', 'machine_id')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->unsignedBigInteger('machine_id')->nullable()->after('technician_id');
            });
        }

        if (!$this->hasIndex('stock_movements', 'stock_movements_machine_id_index')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->index('machine_id');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('stock_movements') || !Schema::hasColumn('stock_movements', 'machine_id')) {
            return;
        }

        Schema::table('stock_movements', function (Blueprint $table) {
            if ($this->hasIndex('stock_movements', 'stock_movements_machine_id_index')) {
                $table->dropIndex('stock_movements_machine_id_index');
            }
            $table->dropColumn('machine_id');
        });
    }

    private function hasIndex(string $table, string $index): bool
    {
        return count(Schema::getConnection()->select(
            "SHOW INDEX FROM `{$table}` WHERE Key_name = ?",
            [$index]
        )) > 0;
    }
};
