<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Os piquetes passam a contar-se a partir da escala; os registos gerados sem horas deixam de ser precisos
    public function up(): void
    {
        DB::table('hr_time_entries')
            ->where('tipo', 'piquete')
            ->where('horas', 0)
            ->whereNull('horas_propostas')
            ->whereNotExists(fn ($q) => $q->select(DB::raw(1))->from('hr_time_entry_files')->whereColumn('hr_time_entry_files.hr_time_entry_id', 'hr_time_entries.id'))
            ->delete();
    }

    public function down(): void
    {
        // Não é necessário repor: os piquetes calculam-se a partir da escala
    }
};
