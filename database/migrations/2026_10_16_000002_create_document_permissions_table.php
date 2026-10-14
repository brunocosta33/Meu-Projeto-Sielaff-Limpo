<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Funções (de Recursos Humanos) com acesso à Documentação, além do Coordenador
        Schema::create('document_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('funcao', 30)->unique();
            $table->boolean('pode_ver')->default(false);
            $table->boolean('pode_gerir')->default(false);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_permissions');
    }
};
