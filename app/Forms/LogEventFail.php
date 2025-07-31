<?php

namespace App\Forms;

use Illuminate\Database\Eloquent\Model;

class LogEventFail extends Model
{
    //

   
    public static function NewLoginEvent($email, $ipv4){
        $events_fail = new LogEventFail;
        $events_fail->action = "Try Login";
        $events_fail->email = $email;
        $events_fail->ipv4 = $ipv4;
        $events_fail->save();
    }

}
