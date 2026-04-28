<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TechnicalRequestFile extends Model
{
    protected $fillable = [
        'technical_request_id',
        'file_path',
        'file_name',
    ];

    public function technicalRequest()
    {
        return $this->belongsTo(TechnicalRequest::class);
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
