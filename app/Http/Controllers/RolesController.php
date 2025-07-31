<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Kris\LaravelFormBuilder\FormBuilderTrait;
use Illuminate\Support\Facades\Validator;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Permission_role;

class RolesController extends Controller
{
    use FormBuilderTrait;

    public function __construct(){
        $this->middleware(['auth', 'isActive']);
    }
	
    public function index() {
        $roles = Role::paginate(20);
        return view('backoffice.roles.index', compact('roles'));
    }   

    public function create(){
        $permissions = Permission::all();
        return view('backoffice.roles.create', compact('permissions'));
    }

    public function store(Request $request)    {

        $validated = $request->validate([
            'name' => 'required|max:255|unique:roles,name,NULL,id,deleted_at,NULL',
            'display_name' => 'required|max:255',
        ]);

        $role = new Role();
        $role->name = $request->name;
        $role->display_name = $request->display_name;
        $role->description = $request->msg;
        $role->is_active = $request->is_active == 1 ? 1 : 0;
        $role->save();

        if($request->input('permissions_role') == null){
            $role->permissions()->sync(Permission::all());
        }
        else{
            $role->permissions()->sync($request->input('permissions_role'));
        }
        
        flash(__('Role created successfully!'))->success();
       
        return redirect()->route('backoffice.roles.index');
    }

    public function edit( $id){        
        $roles = DB::table('roles')->where('id',  $id)->first();

        $permissions = Permission::all();
   
        $permissions_role = Permission_role::where('role_id', $roles->id)->pluck('permission_id')->toArray();        

        return view('backoffice.roles.edit', compact('roles', 'permissions', 'permissions_role'));
    }

    public function update(Request $request, $id){ 
        
        $validated = $request->validate([
            'name' => "required|max:255|unique:roles,name,$id,id,deleted_at,NULL",
            'display_name' => 'required|max:255',
        ]);
        
        $role = Role::find($id);
        $role->is_active = $request->has('is_active') ? true : false;
        $role->name = $request->name;
        $role->description = $request->msg;
        $role->display_name = $request->display_name;        
        $role->save();
        
        $role->permissions()->sync($request->input('permissions_role'));
        
        if($request->input('permissions_role') == null){
            $role->permissions()->sync(Permission::all());
        }
        else{
            $role->permissions()->sync($request->input('permissions_role'));
        }
                   
        flash(__('Role updated successfully!'))->success();

        return redirect()->route('backoffice.roles.index');
    }

    public function delete($role_id){
        $role = Role::find($role_id); 
        
        if($role->users->isNotEmpty()){
            flash(__('It is not possible to delete the role while there are associated users.'))->error();
        }
        else{
            Role::destroy($role->id);
            flash(__('Role deleted successfully!'))->success();
        }    

        return redirect()->route('backoffice.roles.index');
    }
}
