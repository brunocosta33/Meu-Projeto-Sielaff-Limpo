<?php

namespace App\Models;

use Laratrust\Models\Role as RoleModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends RoleModel
{
    use SoftDeletes;

    public function permission()
    {
        return $this->belongsToMany(Permission::class, 'permission_role');
    }

    public function user()
    {
        return $this->belongsToMany(User::class, 'role_user');
    }
}
