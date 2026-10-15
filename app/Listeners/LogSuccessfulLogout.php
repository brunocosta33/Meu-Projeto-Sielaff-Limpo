<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class LogSuccessfulLogout
{
    /**
     * Regista a saída (logout) na mesma tabela das entradas.
     */
    public function handle(Logout $event)
    {
        if (!$event->user) {
            return;
        }

        DB::table('login_activities')->insert([
            'user_id'    => $event->user->id,
            'tipo'       => 'logout',
            'user_agent' => (string) Request::header('User-Agent'),
            'ip_address' => (string) Request::ip(),
            'created_at' => now(),
        ]);
    }
}
