<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Role;
use App\Rules\Old_Password;
use Cache;
use Session;

class UsersController extends Controller
{
    public function __construct(){
        $this->middleware(['auth', 'isActive']);
    }
	
    public function index(){    
        $users = User::orderBy('name', 'ASC')->paginate(20);

    	return view('backoffice.users.index', compact('users'));
    }

    public function create(){
        $roles = Role::all();        

        return view('backoffice.users.create', compact('roles'));
    }

    public function store(Request $request){

        $validated = $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|max:255|unique:users,email,NULL,id,deleted_at,NULL|email',
            'role'=> 'required'
        ]);

        $user = new User;
        $user->name = $request->name;
        $user->role_id = $request->role;
        $user->email = $request->email;
        $user->is_active = $request->is_active == 1 ? 1 : 0;
        $user->password = bcrypt('QWERTY123');
        $user->save();

        $user->roles()->attach($request->role);

        flash(__('User created successfully!'))->success();

        return redirect()->route('backoffice.users.index');
    }

    public function edit($user_id){
        $user = User::find($user_id);
        $roles = Role::all();        

        return view('backoffice.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, $user_id){   

        $validated = $request->validate([
            'name' => 'required|max:255',
            'email' => "required|max:255|unique:users,email,$user_id,id,deleted_at,NULL|email",
            'role'=> 'required'
        ]);

        $user = User::find($user_id);
        $user->name = $request->name;
        $user->role_id = $request->role;
        $user->email = $request->email;
        $user->is_active = $request->has('is_active') ? true : false;
        $user->save();

        $roles = Role::all();
        $admin_count = $roles[0]->users()->count();

        $userbase = 2;       

        $user->roles()->sync($request->input('role'));
       
        if ($request->role == $userbase){   
            $user->roles()->sync($userbase);
        } 
        else{
            if ($request->user == 1 && $admin_count >= 1){
                flash(__('You are the only system administrator, it is not possible to change!'))->error();
                return redirect()->back();
            }
        }       
            
        flash(__('User updated successfully!'))->success();

        return redirect()->route('backoffice.users.index');
    }

    public function destroy($user_id){
        User::destroy($user_id);

        flash(__('User deleted successfully!'))->success();
    
        return redirect()->route('backoffice.users.index');
    }

    public function pass(User $user){
        return view('backoffice.users.pass', compact('user'));
    }

    public function passstore(Request $request){

        $validated = $request->validate([
            'password' => 'required|min:8|string',
        ]);

        $user = User::find($request->id);
        $user->password = bcrypt($request->password);
        $user->save();

       

        flash(__('Password successfully updated!'))->success();

        return redirect()->route('backoffice.users.index');
    }

    public function locale(Request $request, $user_id)
    {
        $user = User::find($user_id);
        $user->lang = $request->lang;
        $user->save();

        Session::put('locale', $request->lang);

        return redirect()->back();
    }
    
    public function userOnlineStatus(){
        $users = User::all();

        foreach ($users as $user) {
            if (Cache::has('user-is-online-' . $user->id))
                echo $user->name . " is online. Last seen: " . Carbon::parse($user->last_seen)->diffForHumans() . " <br>";
            else
                echo $user->name . " is offline. Last seen: " . Carbon::parse($user->last_seen)->diffForHumans() . " <br>";
        }
    }

    public function password(Request $request, $user_id){

        $validated = $request->validate([
            'current' => ['required', new Old_Password],
            'password' => 'required|min:8|string',
            'password_confirmation' => 'required|min:8|string|same:password'
        ]);

        $user = User::find($user_id);
        $user->password = bcrypt($request->password);
        $user->save();

        flash(__('Password successfully updated!'))->success();

        return redirect()->back();
    }
}