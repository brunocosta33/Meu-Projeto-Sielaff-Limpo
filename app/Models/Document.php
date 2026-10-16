<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'titulo',
        'tipo',
        'modelos',
        'idioma',
        'descricao',
        'file_path',
        'file_name',
        'file_size',
        'uploaded_by',
    ];

    protected $casts = [
        'modelos' => 'array',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getTypeAttribute(): array
    {
        $type = config('documents.types.' . $this->tipo, config('documents.types.outros'));
        if (isset($type['label'])) {
            $type['label'] = __($type['label']);
        }

        return $type;
    }

    public function getSizeLabelAttribute(): string
    {
        $kb = $this->file_size / 1024;

        return $kb >= 1024 ? number_format($kb / 1024, 1, ',', '') . ' MB' : max(1, round($kb)) . ' KB';
    }

    /** Nome de ficheiro para descarregar: título + .pdf */
    public function getDownloadNameAttribute(): string
    {
        $name = trim(preg_replace('/[\\\\\/:*?"<>|]+/', '-', $this->titulo));

        return ($name ?: 'documento') . '.pdf';
    }
}
