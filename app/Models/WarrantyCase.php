<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/** Avaria fora da garantia: cotação Sielaff -> envio ao cliente -> ordem de compra -> reparação. */
class WarrantyCase extends Model
{
    public const OPEN_PHASES = ['registado', 'cotacao_pedida', 'cotacao_recebida', 'enviada_cliente', 'oc_recebida'];

    protected $fillable = [
        'store_id', 'machine_id', 'technical_request_id', 'cliente', 'data_avaria', 'descricao', 'observacoes', 'fase', 'fase_desde',
        'cotacao_pedida_em', 'cotacao_numero', 'cotacao_recebida_em', 'cotacao_path', 'cotacao_nome',
        'enviada_cliente_em', 'enviada_para', 'oc_numero', 'oc_recebida_em', 'oc_path', 'oc_nome',
        'fechado_em', 'motivo_recusa', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'data_avaria' => 'date',
        'fase_desde' => 'date',
        'cotacao_pedida_em' => 'date',
        'cotacao_recebida_em' => 'date',
        'enviada_cliente_em' => 'date',
        'oc_recebida_em' => 'date',
        'fechado_em' => 'date',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    public function technicalRequest()
    {
        return $this->belongsTo(TechnicalRequest::class);
    }

    public function photos()
    {
        return $this->hasMany(WarrantyCasePhoto::class)->orderBy('id');
    }

    public function events()
    {
        return $this->hasMany(WarrantyCaseEvent::class)->orderBy('id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isOpen(): bool
    {
        return in_array($this->fase, self::OPEN_PHASES, true);
    }

    public function phase(): array
    {
        return config('warranty.phases.' . $this->fase, config('warranty.phases.registado'));
    }

    public function clientLabel(): string
    {
        return config('warranty.clients.' . $this->cliente, ucfirst((string) $this->cliente));
    }

    /** Dias na fase atual. */
    public function daysInPhase(): int
    {
        return (int) Carbon::parse($this->fase_desde)->startOfDay()->diffInDays(now()->startOfDay());
    }

    /** null, 'warn' ou 'late' quando estamos há demasiado tempo à espera da Sielaff ou do cliente. */
    public function waitingAlert(): ?string
    {
        $limits = config('warranty.waiting_alert_days.' . $this->fase);
        if (!$limits) {
            return null;
        }
        $days = $this->daysInPhase();

        return $days >= $limits[1] ? 'late' : ($days >= $limits[0] ? 'warn' : null);
    }

    /** Cliente a partir da insígnia da loja (Lidl / Sonae). */
    public static function clientFromStore(?Store $store): ?string
    {
        $insignia = mb_strtolower((string) ($store->insignia ?? ''));
        foreach (array_keys(config('warranty.clients')) as $client) {
            if (str_contains($insignia, $client)) {
                return $client;
            }
        }

        return null;
    }
}
