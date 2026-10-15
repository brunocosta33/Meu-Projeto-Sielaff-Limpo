<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Piquete deixa de ter valor em euros: passa a "Pagamento de piquete" com horas de trabalho suplementar
    public function up(): void
    {
        DB::table('hr_time_entries')->where('tipo', 'pagamento_prevencao')->delete();
        DB::table('hr_time_entries')->where('tipo', 'prevencao_piquete')->update(['tipo' => 'piquete', 'horas' => 0]);
        DB::table('hr_time_entries')->where('tipo', 'intervencao_piquete')->update(['tipo' => 'piquete']);
        DB::table('hr_time_entries')->where('referencia', 'like', 'prevencao:%')
            ->update(['referencia' => DB::raw("REPLACE(referencia, 'prevencao:', 'piquete:')")]);

        Schema::table('hr_time_entries', function (Blueprint $table) {
            $table->dropColumn('valor');
        });
    }

    public function down(): void
    {
        Schema::table('hr_time_entries', function (Blueprint $table) {
            $table->decimal('valor', 8, 2)->default(0)->after('horas');
        });
    }
};
