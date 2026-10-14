<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // O piquete fica para mais tarde
        Schema::table('hr_staff', function (Blueprint $table) {
            $table->dropColumn('is_piquete');
        });

        // Dias de férias por colaborador e por ano
        Schema::create('hr_vacation_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hr_staff_id')->constrained('hr_staff')->cascadeOnDelete();
            $table->unsignedSmallInteger('ano');
            $table->unsignedTinyInteger('dias_direito');
            $table->unsignedTinyInteger('dias_transitados')->nullable(); // null = calculado do ano anterior
            $table->timestamps();

            $table->unique(['hr_staff_id', 'ano']);
        });

        // Pedidos / marcações de férias
        Schema::create('hr_vacations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hr_staff_id')->constrained('hr_staff')->cascadeOnDelete();
            $table->date('data_inicio');
            $table->date('data_fim');
            $table->unsignedSmallInteger('dias_uteis');
            $table->string('estado', 20)->default('pendente'); // pendente, aprovado, rejeitado
            $table->text('observacoes')->nullable();
            $table->string('motivo_rejeicao')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();

            $table->index(['data_inicio', 'data_fim']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_vacations');
        Schema::dropIfExists('hr_vacation_balances');

        Schema::table('hr_staff', function (Blueprint $table) {
            $table->boolean('is_piquete')->default(false)->after('is_hotliner');
        });
    }
};
