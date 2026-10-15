<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Banco de horas: movimentos de horas (extra, em falta, acertos) e valores (prevenções de piquete)
        Schema::create('hr_time_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hr_staff_id')->constrained('hr_staff')->cascadeOnDelete();
            $table->date('data');
            $table->string('tipo', 30);
            $table->decimal('horas', 6, 2)->default(0);   // sempre positivo; o sinal vem do tipo
            $table->decimal('valor', 8, 2)->default(0);   // euros (prevenções); sempre positivo
            $table->string('descricao')->nullable();
            $table->string('estado', 20)->default('aprovado'); // pendente, aprovado, rejeitado
            $table->string('referencia')->nullable()->unique(); // evita duplicar movimentos gerados automaticamente
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();

            $table->index(['hr_staff_id', 'data']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_time_entries');
    }
};
