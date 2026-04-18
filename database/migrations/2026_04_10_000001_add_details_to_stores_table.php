<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->string('insignia')->nullable()->after('regiao');
            $table->string('cidade')->nullable()->after('morada');
            $table->string('contacto_loja')->nullable()->after('codigo_postal');
            $table->string('telefone')->nullable()->after('contacto_loja');
            $table->string('email')->nullable()->after('telefone');
            $table->text('observacoes')->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn([
                'insignia',
                'cidade',
                'contacto_loja',
                'telefone',
                'email',
                'observacoes',
            ]);
        });
    }
};
