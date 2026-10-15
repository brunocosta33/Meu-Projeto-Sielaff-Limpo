<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Entrada (login) ou saída (logout); os registos existentes são todos logins
        Schema::table('login_activities', function (Blueprint $table) {
            $table->string('tipo', 10)->default('login')->after('user_id');
            $table->index('created_at');
        });

        // Trazer os logouts antigos, que estavam só na tabela log_events
        if (Schema::hasTable('log_events')) {
            DB::table('log_events')->where('action', 'LOGOUT')->orderBy('id')->chunk(500, function ($rows) {
                DB::table('login_activities')->insert($rows->map(fn ($r) => [
                    'user_id' => $r->user_id,
                    'tipo' => 'logout',
                    'user_agent' => '',
                    'ip_address' => substr((string) $r->ipv4, 0, 45),
                    'created_at' => $r->created_at,
                    'updated_at' => $r->created_at,
                ])->all());
            });
        }
    }

    public function down(): void
    {
        DB::table('login_activities')->where('tipo', 'logout')->delete();

        Schema::table('login_activities', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropColumn('tipo');
        });
    }
};
