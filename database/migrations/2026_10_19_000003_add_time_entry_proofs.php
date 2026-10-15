<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Horas de trabalho suplementar propostas pelo técnico num piquete já registado (aguardam aprovação)
        Schema::table('hr_time_entries', function (Blueprint $table) {
            $table->decimal('horas_propostas', 6, 2)->nullable()->after('horas');
        });

        // Comprovativos (fotografias, PDFs) dos movimentos do banco de horas
        Schema::create('hr_time_entry_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hr_time_entry_id')->constrained('hr_time_entries')->cascadeOnDelete();
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
        Schema::dropIfExists('hr_time_entry_files');
        Schema::table('hr_time_entries', function (Blueprint $table) {
            $table->dropColumn('horas_propostas');
        });
    }
};
