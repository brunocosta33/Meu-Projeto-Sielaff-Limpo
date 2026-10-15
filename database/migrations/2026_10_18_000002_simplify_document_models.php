<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Modelos da Documentação deixam de distinguir housings: DualSort / MonoSort
    public function up(): void
    {
        DB::table('documents')->whereNotNull('modelos')->orderBy('id')->each(function ($doc) {
            $models = json_decode($doc->modelos, true) ?: [];
            $mapped = collect($models)->map(function ($m) {
                if (stripos($m, 'dualsort') !== false) {
                    return 'SiVario DualSort';
                }
                if (stripos($m, 'monosort') !== false) {
                    return 'SiVario MonoSort';
                }
                return $m;
            })->unique()->values()->all();

            if ($mapped !== $models) {
                DB::table('documents')->where('id', $doc->id)->update(['modelos' => json_encode($mapped, JSON_UNESCAPED_UNICODE)]);
            }
        });
    }

    public function down(): void
    {
        // Não é possível saber a variante original de housings
    }
};
