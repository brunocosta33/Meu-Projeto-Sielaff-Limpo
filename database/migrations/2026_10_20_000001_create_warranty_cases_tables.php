<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Avarias fora da garantia: cotação pedida à Sielaff, enviada ao cliente e ordem de compra
        Schema::create('warranty_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            // machines/technical_requests têm id INT (tabelas antigas): só índice, sem chave estrangeira
            $table->unsignedBigInteger('machine_id')->nullable()->index();
            $table->unsignedBigInteger('technical_request_id')->nullable()->index();
            $table->string('cliente', 20);
            $table->text('descricao');
            $table->text('observacoes')->nullable();
            $table->string('fase', 30)->default('registado')->index();
            $table->date('fase_desde');
            // Cotação Sielaff
            $table->date('cotacao_pedida_em')->nullable();
            $table->string('cotacao_numero', 100)->nullable();
            $table->date('cotacao_recebida_em')->nullable();
            $table->string('cotacao_path')->nullable();
            $table->string('cotacao_nome')->nullable();
            // Envio ao cliente e ordem de compra
            $table->date('enviada_cliente_em')->nullable();
            $table->string('enviada_para')->nullable();
            $table->string('oc_numero', 100)->nullable();
            $table->date('oc_recebida_em')->nullable();
            $table->string('oc_path')->nullable();
            $table->string('oc_nome')->nullable();
            // Fecho
            $table->date('fechado_em')->nullable();
            $table->text('motivo_recusa')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('warranty_case_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warranty_case_id')->constrained('warranty_cases')->cascadeOnDelete();
            $table->string('path');
            $table->string('nome');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Histórico: cada passo dado (quem, quando, nota)
        Schema::create('warranty_case_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warranty_case_id')->constrained('warranty_cases')->cascadeOnDelete();
            $table->string('fase', 30);
            $table->date('data');
            $table->text('nota')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warranty_case_events');
        Schema::dropIfExists('warranty_case_photos');
        Schema::dropIfExists('warranty_cases');
    }
};
