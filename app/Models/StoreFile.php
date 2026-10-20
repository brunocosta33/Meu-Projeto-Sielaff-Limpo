<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Fotografia ou PDF anexado à ficha da loja. */
class StoreFile extends Model
{
    protected $fillable = ['store_id', 'path', 'nome', 'mime', 'tamanho', 'uploaded_by'];

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
