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

    /** Cliente da loja (sonae, lidl, ...), lido da insígnia. Null se não for nenhum dos conhecidos. */
    public function clienteKey(): ?string
    {
        $insignia = mb_strtolower((string) $this->insignia);

        foreach (array_keys(config('maintenance.clients', [])) as $cliente) {
            if (str_contains($insignia, $cliente)) {
                return $cliente;
            }
        }

        return null;
    }

    /** Cor e etiqueta do cliente, para o destacar nas listas. */
    public function clienteConfig(): ?array
    {
        $key = $this->clienteKey();

        return $key ? config('maintenance.clients.' . $key) : null;
    }

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
