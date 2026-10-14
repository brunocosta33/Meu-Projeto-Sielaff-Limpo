<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Feriados próprios da empresa (além dos nacionais, que são calculados)
        Schema::create('hr_holidays', function (Blueprint $table) {
            $table->id();
            $table->date('data');
            $table->string('nome');
            $table->boolean('recorrente')->default(false); // repete todos os anos no mesmo dia
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_holidays');
    }
};
