<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Kris\LaravelFormBuilder\FormBuilderTrait;
use App\Models\Permission;
use Illuminate\Support\Facades\Validator;

class PermissionsController extends Controller
{
    use FormBuilderTrait;

    public function __construct(){
        $this->middleware(['auth', 'isActive']);
    }
	
    public function index()    {
        $permissions = Permission::all();
        return view('backoffice.permissions.index', compact('permissions'));
    }   

    public function create(){
       return view('backoffice.permissions.create');
    }

    public function store(Request $request){

        $validated = $request->validate([
            'name' => 'required|max:255|unique:permissions,name,NULL,id,deleted_at,NULL',
            'display_name' => 'required|max:255',
        ]);

        $result = DB::table('permissions')->insert(['is_active' =>  $request['is_active'] == 1 ? 1: 0,
                                                'name' => $request['name'], 
                                                'description' =>  $request['msg'],  
                                                'display_name' => $request['display_name'],  
                                                'created_at' => now()]);
        
        if($result):
            flash(__('Permission created successfully!'))->success();
        else:
            flash(__('An error occurred while creating permission!'))->error();
        endif;

        return redirect()->route('backoffice.permissions.index');
    }

    public function edit( $id){        
        $permissions = DB::table('permissions')->where('id',  $id)->first();
        return view('backoffice.permissions.edit', compact('permissions'));
    }

    public function update(Request $request, $id){ 
        
        $validated = $request->validate([
            'name' => "required|max:255|unique:permissions,name,$id,id,deleted_at,NULL",
            'display_name' => 'required|max:255',
        ]);
        
        $result = DB::table('permissions')->where('id',  $id)->update(['is_active' =>  $request['is_active']==1 ? 1: 0,
                                                                    'name' => $request->input('name'), 
                                                                    'display_name' => $request->input('display_name'),   
                                                                    'description' =>  $request['msg'], 
                                                                    'updated_at' => now()]);

        if($result):
            flash(__('Permission updated successfully!'))->success();
        else:
            flash(__('An error occurred while updating permission!'))->error();
        endif;

        return redirect()->route('backoffice.permissions.index');
    }

    public function delete($permission_id){
        $permission = Permission::find($permission_id);
        
        if($permission->roles->isNotEmpty()){
            flash(__('It is not possible to delete the permission while there are associated roles.'))->error();
        }
        else{
            Permission::destroy($permission->id);
            flash(__('Permission deleted successfully!'))->success();
        }   

        return redirect()->route('backoffice.permissions.index');
    }
}
