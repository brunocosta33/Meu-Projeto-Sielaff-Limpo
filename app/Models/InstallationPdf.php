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

    public function isImage(): bool
    {
        return in_array(strtolower(pathinfo($this->file_name, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
    }

    public function isPdf(): bool
    {
        return strtolower(pathinfo($this->file_name, PATHINFO_EXTENSION)) === 'pdf';
    }
}
