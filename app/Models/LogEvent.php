<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogEvent extends Model
{
    public static function NewLoginEvent($user, $ipv4){
        $event = new LogEvent;
        $event->action = "LOGIN";
        $event->user_id = $user->id;
        $event->ipv4 = $ipv4;
        $event->save();
    }

    public static function NewLogoutEvent($user, $ipv4){    
        $event = new LogEvent;
        $event->action = "LOGOUT";
        $event->user_id = $user->id;
        $event->ipv4 = $ipv4;
        $event->save();
    }
}
