<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Registo de peças e ferramentas entregues aos técnicos. Não é stock: é só o papel
        // de quem levou o quê, quando e porquê. Propositadamente à parte dos movimentos de stock.
        Schema::create('tool_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('technician_id')->constrained('users')->cascadeOnDelete();
            $table->string('tipo', 12)->default('entrega')->index(); // entrega | devolucao
            // O nome fica escrito aqui para o registo valer por si, mesmo que o artigo mude de nome
            $table->string('artigo');
            // Só indexado: o catálogo de artigos é do módulo de stock e este registo não depende dele
            $table->unsignedBigInteger('item_id')->nullable()->index();
            $table->unsignedInteger('quantidade')->default(1);
            $table->dateTime('ocorrido_em')->index();
            $table->text('observacoes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tool_deliveries');
    }
};
