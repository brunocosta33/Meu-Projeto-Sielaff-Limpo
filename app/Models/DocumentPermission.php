<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentPermission extends Model
{
    protected $fillable = [
        'funcao',
        'pode_ver',
        'pode_gerir',
        'updated_by',
    ];

    protected $casts = [
        'pode_ver' => 'boolean',
        'pode_gerir' => 'boolean',
    ];
}
