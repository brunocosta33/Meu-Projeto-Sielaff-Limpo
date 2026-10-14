<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Faltas dos colaboradores (dias inteiros ou parte do dia)
        Schema::create('hr_absences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hr_staff_id')->constrained('hr_staff')->cascadeOnDelete();
            $table->date('data_inicio');
            $table->date('data_fim');
            $table->time('hora_inicio')->nullable(); // preenchido só em faltas de parte do dia
            $table->time('hora_fim')->nullable();
            $table->unsignedSmallInteger('dias_uteis')->default(0);
            $table->decimal('horas', 5, 2)->nullable();
            $table->string('motivo', 30);
            $table->string('situacao', 20)->default('aguarda'); // aguarda, justificada, injustificada
            $table->text('observacoes')->nullable();
            $table->string('documento_path')->nullable();
            $table->string('documento_nome')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();

            $table->index(['data_inicio', 'data_fim']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_absences');
    }
};
