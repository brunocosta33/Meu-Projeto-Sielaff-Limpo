<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Fotografia ou PDF anexado à ficha da loja. */
class StoreFile extends Model
{
    public const TYPES = ['foto', 'ibn'];

    protected $fillable = ['store_id', 'tipo', 'path', 'nome', 'mime', 'tamanho', 'uploaded_by'];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function isImage(): bool
    {
        return str_starts_with((string) $this->mime, 'image/')
            || in_array(strtolower(pathinfo($this->nome, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'heic'], true);
    }
}
