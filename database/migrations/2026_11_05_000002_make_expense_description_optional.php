<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // ALTER direto: o ->change() do Laravel 10 precisa do doctrine/dbal, que o projeto não tem
    public function up(): void
    {
        // A categoria e o comprovativo já dizem o essencial: a descrição passa a ser opcional
        DB::statement('ALTER TABLE hr_expenses MODIFY descricao VARCHAR(255) NULL');
    }

    public function down(): void
    {
        DB::statement("UPDATE hr_expenses SET descricao = '' WHERE descricao IS NULL");
        DB::statement('ALTER TABLE hr_expenses MODIFY descricao VARCHAR(255) NOT NULL');
    }
};
