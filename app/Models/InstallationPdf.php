<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstallationPdf extends Model
{
    protected $fillable = [
        'installation_id',
        'file_path',
        'file_name',
    ];

    public function installation()
    {
        return $this->belongsTo(Installation::class);
    }
}
