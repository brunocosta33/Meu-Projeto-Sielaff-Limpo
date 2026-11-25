<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Ponto de situação dos processos fora da garantia: o último é o que se vê na lista; os outros ficam como histórico. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warranty_case_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warranty_case_id')->constrained('warranty_cases')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('texto');
            $table->timestamps();
            $table->index(['warranty_case_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warranty_case_notes');
    }
};
