<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Um ponto de situação de um processo fora da garantia. */
class WarrantyCaseNote extends Model
{
    protected $fillable = ['warranty_case_id', 'user_id', 'texto'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
