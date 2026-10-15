<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoginActivityController extends Controller
{
    public function __construct(){
        $this->middleware(['auth', 'isActive']);
    }

    public function index(Request $request){
        $loginActivities = DB::table('login_activities')->join('users', 'users.id', '=', 'login_activities.user_id')
                        ->select('login_activities.*', 'users.name')
                        ->when(in_array($request->tipo, ['login', 'logout'], true), fn ($q) => $q->where('login_activities.tipo', $request->tipo))
                        ->when($request->filled('user_id'), fn ($q) => $q->where('login_activities.user_id', $request->user_id))
                        ->orderByDesc('login_activities.created_at')->orderByDesc('login_activities.id')
                        ->paginate(30)->withQueryString();

        $users = DB::table('users')->whereNull('deleted_at')->orderBy('name')->get(['id', 'name']);

        return view('backoffice.login-activity.index', compact('loginActivities', 'users'));
    }
}
