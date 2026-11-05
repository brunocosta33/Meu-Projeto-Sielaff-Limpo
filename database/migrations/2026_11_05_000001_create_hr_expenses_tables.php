<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Despesas dos colaboradores: lançadas por quem as teve, aprovadas e pagas pela direção
        Schema::create('hr_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hr_staff_id')->constrained('hr_staff')->cascadeOnDelete();
            $table->date('data')->index();
            $table->string('categoria', 30);
            $table->string('descricao');
            $table->decimal('valor', 10, 2);
            // pendente -> aprovada | rejeitada
            $table->string('estado', 20)->default('pendente')->index();
            $table->text('motivo_rejeicao')->nullable();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            // O pagamento é um passo à parte da aprovação
            $table->date('pago_em')->nullable();
            $table->string('pago_como', 30)->nullable();
            $table->foreignId('pago_por')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Comprovativos: fotografia, PDF, folha de cálculo…
        Schema::create('hr_expense_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hr_expense_id')->constrained('hr_expenses')->cascadeOnDelete();
            $table->string('path');
            $table->string('nome');
            $table->string('mime', 100)->nullable();
            $table->unsignedBigInteger('tamanho')->default(0);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_expense_files');
        Schema::dropIfExists('hr_expenses');
    }
};
