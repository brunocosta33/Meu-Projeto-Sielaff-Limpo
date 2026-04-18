<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('technical_requests')) {
            return;
        }

        if (Schema::hasColumn('technical_requests', 'data_agendamento')) {
            DB::statement('ALTER TABLE technical_requests MODIFY data_agendamento DATETIME NULL');
        }

        if (Schema::hasColumn('technical_requests', 'data_resolucao')) {
            DB::statement('ALTER TABLE technical_requests MODIFY data_resolucao DATETIME NULL');
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('technical_requests')) {
            return;
        }

        if (Schema::hasColumn('technical_requests', 'data_agendamento')) {
            DB::statement('ALTER TABLE technical_requests MODIFY data_agendamento DATE NULL');
        }

        if (Schema::hasColumn('technical_requests', 'data_resolucao')) {
            DB::statement('ALTER TABLE technical_requests MODIFY data_resolucao DATE NULL');
        }
    }
};
