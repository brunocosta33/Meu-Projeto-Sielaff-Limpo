<?php
// database/migrations/xxxx_xx_xx_xxxxxx_add_unique_to_parts_referencia.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('parts', function (Blueprint $table) {
            $table->unique('referencia');
        });
    }
    public function down(): void {
        Schema::table('parts', function (Blueprint $table) {
            $table->dropUnique(['referencia']);
        });
    }
};
