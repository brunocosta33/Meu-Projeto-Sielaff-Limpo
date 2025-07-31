<?php

namespace App\Forms;

use Laratrust\Models\Role as LaratrustRole;

class Role extends LaratrustRole
{
    public function users()
    {
        return $this->belongsToMany(User::class, 'role_user');
    }
}
