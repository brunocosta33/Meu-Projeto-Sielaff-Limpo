<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Store extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'stores';

    protected $fillable = [
        'regiao',
        'insignia',
        'codigo_loja',
        'nome_loja',
        'morada',
        'cidade',
        'codigo_postal',
        'contacto_loja',
        'telefone',
        'email',
        'observacoes',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function machines()
    {
        return $this->hasMany(Machine::class, 'store_id');
    }

    /** Fotografias e PDFs da ficha da loja. */
    public function files()
    {
        return $this->hasMany(StoreFile::class)->orderBy('id');
    }

    public function photos()
    {
        return $this->files()->where('tipo', 'foto');
    }

    /** IBN da loja (PDF). */
    public function ibnFiles()
    {
        return $this->files()->where('tipo', 'ibn');
    }
}
