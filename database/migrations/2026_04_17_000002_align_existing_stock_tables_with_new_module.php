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
                $table->foreignId('item_id')->nullable()->constrained('items')->nullOnDelete();
                $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('movement_type', 50)->nullable();
                $table->integer('quantity')->default(0);
                $table->string('source')->nullable();
                $table->string('destination')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        } else {
            Schema::table('stock_movements', function (Blueprint $table) {
                if (!Schema::hasColumn('stock_movements', 'item_id')) {
                    $table->foreignId('item_id')->nullable()->after('id')->constrained('items')->nullOnDelete();
                }

                if (!Schema::hasColumn('stock_movements', 'technician_id')) {
                    $table->foreignId('technician_id')->nullable()->after('item_id')->constrained('users')->nullOnDelete();
                }

                if (!Schema::hasColumn('stock_movements', 'movement_type')) {
                    $table->string('movement_type', 50)->nullable()->after('technician_id');
                }

                if (!Schema::hasColumn('stock_movements', 'quantity')) {
                    $table->integer('quantity')->default(0)->after('movement_type');
                }

                if (!Schema::hasColumn('stock_movements', 'source')) {
                    $table->string('source')->nullable()->after('quantity');
                }

                if (!Schema::hasColumn('stock_movements', 'destination')) {
                    $table->string('destination')->nullable()->after('source');
                }

                if (!Schema::hasColumn('stock_movements', 'notes')) {
                    $table->text('notes')->nullable()->after('destination');
                }

                if (!Schema::hasColumn('stock_movements', 'created_by')) {
                    $table->foreignId('created_by')->nullable()->after('notes')->constrained('users')->nullOnDelete();
                }

                if (!Schema::hasColumn('stock_movements', 'created_at')) {
                    $table->timestamp('created_at')->nullable();
                }

                if (!Schema::hasColumn('stock_movements', 'updated_at')) {
                    $table->timestamp('updated_at')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        // Migration de compatibilidade; não remove colunas para evitar perda acidental de dados.
    }
};
