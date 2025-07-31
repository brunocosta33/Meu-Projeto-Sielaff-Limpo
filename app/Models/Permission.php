<?php

namespace App\Models;

use Laratrust\Models\Permission as PermissionModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends PermissionModel
{
    use SoftDeletes;

    public function role()
    {
        return $this->belongsToMany(Role::class, 'permission_role');
    }
}
