<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Zona do técnico (norte/sul) para a escala de piquete
        Schema::table('hr_staff', function (Blueprint $table) {
            $table->string('zona', 10)->nullable()->after('rotation_group');
        });
    }

    public function down(): void
    {
        Schema::table('hr_staff', function (Blueprint $table) {
            $table->dropColumn('zona');
        });
    }
};
