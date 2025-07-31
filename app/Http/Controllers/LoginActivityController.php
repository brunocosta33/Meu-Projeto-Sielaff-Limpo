<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class LoginActivityController extends Controller
{
    public function __construct(){
        $this->middleware(['auth', 'isActive']);
    }
    
    public function index(){
        $loginActivities = DB::table('login_activities')->join('users', 'users.id', '=', 'login_activities.user_id')
                        ->select('login_activities.*', 'users.name')->latest()->paginate(20);
                            
        return view('backoffice.login-activity.index', compact('loginActivities'));
    }
}
