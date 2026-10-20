<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fotografias e PDFs da ficha da loja (ex.: fachada, local da máquina, planta)
        Schema::create('store_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
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
        Schema::dropIfExists('store_files');
    }
};
