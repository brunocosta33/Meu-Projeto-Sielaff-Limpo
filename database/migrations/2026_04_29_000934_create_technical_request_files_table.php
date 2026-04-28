<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('technical_request_files', function (Blueprint $table) {
            $table->id();
            $table->integer('technical_request_id');
            $table->string('file_path');
            $table->string('file_name');
            $table->timestamps();

            $table->foreign('technical_request_id')
                ->references('id')
                ->on('technical_requests')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('technical_request_files');
    }
};
