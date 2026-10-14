<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Colaboradores de RH (hotliners e técnicos de piquete), ligados a utilizadores existentes
        Schema::create('hr_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->boolean('is_hotliner')->default(false);
            $table->boolean('is_piquete')->default(false);
            $table->char('rotation_group', 1)->nullable(); // A ou B (rotação dos hotliners)
            $table->string('contacto')->nullable();
            $table->boolean('ativo')->default(true);
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });

        // Substituições / trocas de um dia da rotação
        Schema::create('hr_shift_overrides', function (Blueprint $table) {
            $table->id();
            $table->date('data');
            $table->foreignId('hr_staff_id')->constrained('hr_staff')->cascadeOnDelete();
            $table->foreignId('substitute_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fim')->nullable();
            $table->string('motivo', 30);
            $table->text('observacoes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['data', 'hr_staff_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_shift_overrides');
        Schema::dropIfExists('hr_staff');
    }
};
