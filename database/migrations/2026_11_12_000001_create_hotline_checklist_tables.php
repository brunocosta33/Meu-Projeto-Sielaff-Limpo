<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Checklist da Hotline: a lista do que se faz todos os dias (só a chefia mexe)
 * e as validações, uma por pessoa / tarefa / dia (só os hotliners as fazem).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotline_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('nota')->nullable();
            // manha | tarde | ambos — o turno a que a tarefa se aplica
            $table->string('turno', 10)->default('ambos');
            $table->unsignedInteger('ordem')->default(0);
            $table->boolean('ativo')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['ativo', 'ordem']);
        });

        Schema::create('hotline_checklist_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('hotline_checklist_items')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('data');
            // feito | problema — o problema obriga a escrever o que se passou
            $table->string('estado', 10)->default('feito');
            $table->text('nota')->nullable();
            $table->timestamps();

            // Uma validação por pessoa, por tarefa, por dia
            $table->unique(['item_id', 'user_id', 'data'], 'checklist_item_pessoa_dia');
            $table->index(['data', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotline_checklist_checks');
        Schema::dropIfExists('hotline_checklist_items');
    }
};
