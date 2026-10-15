<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Contrato de manutenção anual por loja: a data contratada repete-se todos os anos
        Schema::create('store_maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->unique()->constrained('stores')->cascadeOnDelete();
            $table->date('data_contratada');
            $table->boolean('ativo')->default(true);
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });

        // Manutenção realizada em cada ano
        Schema::create('store_maintenance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_maintenance_id')->constrained('store_maintenances')->cascadeOnDelete();
            $table->unsignedSmallInteger('ano');
            $table->date('data_realizada');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // técnico
            $table->text('observacoes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['store_maintenance_id', 'ano']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_maintenance_records');
        Schema::dropIfExists('store_maintenances');
    }
};
