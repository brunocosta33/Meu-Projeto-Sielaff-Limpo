<?php

use App\Models\Store;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Zona da loja (Norte, Centro, Sul), para nos orientarmos nas manutenções e nas rotas.
 * Preenche-se pelo código postal; o que ficar mal muda-se na ficha da loja.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->string('zona', 10)->nullable()->after('regiao');
        });

        foreach (DB::table('stores')->get(['id', 'codigo_postal', 'morada']) as $loja) {
            $zona = Store::zonaDoCodigoPostal($loja->codigo_postal) ?? Store::zonaDoCodigoPostal($loja->morada);
            if ($zona) {
                DB::table('stores')->where('id', $loja->id)->update(['zona' => $zona]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('zona');
        });
    }
};
