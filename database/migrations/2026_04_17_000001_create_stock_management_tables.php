<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('items')) {
            Schema::create('items', function (Blueprint $table) {
                $table->id();
                $table->string('reference')->unique();
                $table->string('name');
                $table->text('description')->nullable();
                $table->integer('warehouse_stock')->default(0);
                $table->integer('minimum_stock')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('technician_item_stocks')) {
            Schema::create('technician_item_stocks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('technician_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
                $table->integer('quantity')->default(0);
                $table->timestamps();

                $table->unique(['technician_id', 'item_id']);
            });
        }

        if (!Schema::hasTable('stock_movements')) {
            Schema::create('stock_movements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
                $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('movement_type', 50);
                $table->integer('quantity');
                $table->string('source')->nullable();
                $table->string('destination')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['movement_type', 'created_at']);
                $table->index(['technician_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('technician_item_stocks');
        Schema::dropIfExists('items');
    }
};
