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

    /** Zonas do país, para nos orientarmos (manutenções, rotas). */
    public const ZONAS = [
        'norte' => ['label' => 'Norte', 'color' => '#6d28d9', 'bg' => '#f3e8ff', 'icon' => 'fa-arrow-up'],
        'centro' => ['label' => 'Centro', 'color' => '#0f766e', 'bg' => '#ccfbf1', 'icon' => 'fa-dot-circle'],
        'sul' => ['label' => 'Sul', 'color' => '#c2410c', 'bg' => '#ffedd5', 'icon' => 'fa-arrow-down'],
    ];

    protected $fillable = [
        'regiao',
        'zona',
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

    protected static function booted(): void
    {
        // Sem zona escolhida, vai pelo código postal (ou pela morada, se o código estiver lá)
        static::saving(function (Store $store) {
            if (!$store->zona) {
                $store->zona = self::zonaDoCodigoPostal($store->codigo_postal) ?? self::zonaDoCodigoPostal($store->morada);
            }
        });
    }

    /**
     * Zona pelo código postal. É uma aproximação: o que ficar mal muda-se na ficha.
     *   1xxx Lisboa, 21xx Benavente/Coruche, 26xx–29xx Sintra/Almada/Setúbal, 7xxx, 8xxx -> Sul
     *   20xx Santarém, 22xx–25xx Abrantes/Leiria/Caldas, 3xxx, 6xxx                     -> Centro
     *   370x–373x S. João da Madeira/Oliveira de Azeméis/Vale de Cambra, 4xxx, 5xxx         -> Norte
     *   9xxx (ilhas) fica sem zona.
     */
    public static function zonaDoCodigoPostal(?string $texto): ?string
    {
        // "2735-521", "2735–521", "2735 521" ou só "3850"
        if (!preg_match('/\b(\d{4})\s*[-–—]?\s*\d{3}\b/u', (string) $texto, $m)
            && !preg_match('/^\s*(\d{4})\s*$/', (string) $texto, $m)) {
            return null;
        }
        $cp = (int) $m[1];

        return match (true) {
            $cp >= 9000 => null,
            $cp >= 7000 => 'sul',
            $cp >= 6000 => 'centro',
            $cp >= 4000 => 'norte',
            $cp >= 3700 && $cp < 3740 => 'norte',
            $cp >= 3000 => 'centro',
            $cp >= 2600 => 'sul',
            $cp >= 2200 => 'centro',
            $cp >= 2100 => 'sul',
            $cp >= 2000 => 'centro',
            $cp >= 1000 => 'sul',
            default => null,
        };
    }

    /** Etiqueta, cor e ícone da zona; null se não tiver. */
    public function zonaConfig(): ?array
    {
        return self::ZONAS[$this->zona] ?? null;
    }

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
