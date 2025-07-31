<?php

namespace App\Http\Controllers;
use App\Models\User;
use DB;

class DashboardController extends Controller
{    

    public function login(){  
      
        return view('backoffice.dashboard.index');
    }  

    public function index(){  
     
        if(!app()->getLocale()){
            app()->setLocale('pt');        
        }
              
        // teste Chart.JS
        $users = User::select(DB::raw("COUNT(*) as count"), DB::raw("MONTHNAME(created_at) as month_name"))
                    ->orderBy(DB::raw("Month(created_at)"))->groupBy(DB::raw("Month(created_at)"),  "month_name")
                    ->pluck('count', 'month_name');
 
        $labels = $users->keys();
        $data = $users->values();
        return view('backoffice.dashboard.index', compact('labels', 'data'));
    }  


}
