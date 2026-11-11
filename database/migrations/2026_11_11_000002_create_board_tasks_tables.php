<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Quadro de tarefas da equipa: dar uma coisa a alguém, saber que essa pessoa pegou
        // nela, e a pessoa responder ou dar por concluída. Nada a ver com as tarefas
        // recorrentes (task_schedules), que são outra coisa.
        Schema::create('board_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->string('estado', 12)->default('por_fazer')->index(); // por_fazer | em_curso | feito
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->date('prazo')->nullable()->index();

            // Quem pegou nela e quem a deu por feita: é o que distingue "foi-lhe dada" de "está a tratar"
            $table->dateTime('iniciado_em')->nullable();
            $table->foreignId('iniciado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('concluido_em')->nullable()->index();
            $table->foreignId('concluido_por')->nullable()->constrained('users')->nullOnDelete();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // O feedback de quem está a tratar da tarefa
        Schema::create('board_task_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_task_id')->constrained('board_tasks')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('texto');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('board_task_comments');
        Schema::dropIfExists('board_tasks');
    }
};
