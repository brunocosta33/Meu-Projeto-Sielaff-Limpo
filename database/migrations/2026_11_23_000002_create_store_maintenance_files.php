<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Anexos de uma manutenção: a folha de serviço e as fotografias.
 * Antes só cabia um ficheiro por registo (documento_path); agora cabem vários.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_maintenance_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('record_id')->constrained('store_maintenance_records')->cascadeOnDelete();
            // folha | foto
            $table->string('tipo', 10)->default('folha');
            $table->string('path');
            $table->string('nome');
            $table->unsignedBigInteger('tamanho')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['record_id', 'tipo']);
        });

        // As folhas que já estavam gravadas passam para a tabela nova
        $agora = now();
        foreach (DB::table('store_maintenance_records')->whereNotNull('documento_path')->get() as $registo) {
            DB::table('store_maintenance_files')->insert([
                'record_id' => $registo->id,
                'tipo' => 'folha',
                'path' => $registo->documento_path,
                'nome' => $registo->documento_nome ?: basename($registo->documento_path),
                'created_by' => $registo->created_by,
                'created_at' => $agora,
                'updated_at' => $agora,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('store_maintenance_files');
    }
};
