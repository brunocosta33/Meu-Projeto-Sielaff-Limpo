<?php

namespace App\Models;
use Illuminate\Support\Facades\DB;

class Base{

    public static  function logo(){
      $logo = DB::table('images')->where('type','like','logo')->whereNull('deleted_at')->orderBy('created_at', 'desc')->first();
      return $logo;
    }

    public static  function bg()
    {
      $bg = DB::table('images')->where('type','like','bg')->whereNull('deleted_at')->orderBy('created_at', 'desc')->first();
        return $bg;
    }

    public static  function configs(){
      $configs = DB::table('configurations')->orderBy('created_at', 'desc')->first();
      return $configs;
    }
}
