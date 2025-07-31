<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laratrust\Contracts\LaratrustUser;
use Laratrust\Traits\HasRolesAndPermissions;

class Configuration extends Model
{
    use SoftDeletes;
    use HasRolesAndPermissions;

    public function image()
    {
        return $this->belongsTo(Image::class);
    }

}
