<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrTimeEntryFile extends Model
{
    protected $fillable = [
        'hr_time_entry_id',
        'path',
        'nome',
        'mime',
        'tamanho',
        'uploaded_by',
    ];

    public function entry()
    {
        return $this->belongsTo(HrTimeEntry::class, 'hr_time_entry_id');
    }

    public function isImage(): bool
    {
        return str_starts_with((string) $this->mime, 'image/');
    }
}
