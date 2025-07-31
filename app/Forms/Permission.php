<?php

namespace App\Forms;

use Laratrust\Models\Permission as LaratrustPermission;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends LaratrustPermission
{
    use SoftDeletes;
}
