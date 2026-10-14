<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Documentação técnica: manuais, especificações, esquemas (PDF)
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->string('tipo', 30);
            $table->json('modelos')->nullable(); // vazio = Geral (serve todos os modelos)
            $table->string('idioma', 5)->nullable();
            $table->text('descricao')->nullable();
            $table->string('file_path');
            $table->string('file_name');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
