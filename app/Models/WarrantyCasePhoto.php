<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarrantyCasePhoto extends Model
{
    protected $fillable = ['warranty_case_id', 'path', 'nome', 'uploaded_by'];

    public function warrantyCase()
    {
        return $this->belongsTo(WarrantyCase::class);
    }
}
