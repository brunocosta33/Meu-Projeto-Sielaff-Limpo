<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Avaria fora da garantia. O processo são cinco blocos com duas metades cada (ver config/warranty.php):
 * visita -> cotação Sielaff -> cliente -> peça da Alemanha -> fecho. Os processos sem peças saltam
 * a Sielaff e a Alemanha. Uma metade por preencher é uma coisa por fazer; a fase é deduzida das datas.
 */
class WarrantyCase extends Model
{
    public const CLOSED_PHASES = ['concluido', 'recusado'];

    /** O tipo só pode ser corrigido enquanto o processo não passou da visita. */
    public const TYPE_LOCK_AFTER = ['registado', 'os_diagnostico'];

    protected $fillable = [
        'store_id', 'machine_id', 'technical_request_id', 'cliente', 'tipo', 'data_avaria', 'descricao', 'observacoes', 'fase', 'fase_desde',
        'os_numero', 'os_em', 'os_texto', 'os_path', 'os_nome',
        'cotacao_pedida_em', 'cotacao_numero', 'cotacao_recebida_em', 'cotacao_path', 'cotacao_nome',
        'enviada_cliente_em', 'enviada_para', 'oc_numero', 'oc_recebida_em', 'oc_path', 'oc_nome', 'artigo_numero', 'cliente_numero', 'alemanha_enviado_em', 'peca_recebida_em', 'peca_origem', 'servico_path', 'servico_nome',
        'fechado_em', 'motivo_recusa', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'data_avaria' => 'date',
        'fase_desde' => 'date',
        'os_em' => 'date',
        'cotacao_pedida_em' => 'date',
        'cotacao_recebida_em' => 'date',
        'enviada_cliente_em' => 'date',
        'oc_recebida_em' => 'date',
        'alemanha_enviado_em' => 'date',
        'peca_recebida_em' => 'date',
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
        return $this->hasMany(WarrantyCaseEvent::class)->orderBy('data')->orderBy('id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Os outros processos abertos a partir do mesmo pedido de assistência (ex.: a visita e a reparação). */
    public function siblings()
    {
        if (!$this->technical_request_id) {
            return collect();
        }

        return static::where('technical_request_id', $this->technical_request_id)
            ->whereKeyNot($this->getKey())
            ->orderBy('id')
            ->get();
    }

    // ------------------------------------------------------------------ tipo

    public function typeConfig(): array
    {
        return config('warranty.types.' . $this->tipo, config('warranty.types.com_pecas'));
    }

    public function typeLabel(): string
    {
        return __($this->typeConfig()['label']);
    }

    /** Precisa de peça: só estes passam pela Sielaff e pela Alemanha. */
    public function needsParts(): bool
    {
        return $this->tipo !== 'sem_pecas';
    }

    public function canChangeType(): bool
    {
        return in_array($this->fase, self::TYPE_LOCK_AFTER, true);
    }

    // ---------------------------------------------------------------- blocos

    /** Os blocos por que este processo passa. */
    public function blocks(): array
    {
        return array_filter(config('warranty.blocks'), fn ($b) => in_array($this->tipo, $b['types'], true));
    }

    /** Todas as metades, por ordem, já com a chave do bloco e a da metade. */
    public function steps(): array
    {
        $steps = [];
        foreach ($this->blocks() as $blockKey => $block) {
            foreach ($block['halves'] as $halfKey => $half) {
                $steps[] = $half + ['block' => $blockKey, 'half' => $halfKey];
            }
        }

        return $steps;
    }

    /** Os dados de uma metade, ou null se não pertence a este tipo de processo. */
    public function step(string $block, string $half): ?array
    {
        $config = config("warranty.blocks.$block");
        if (!$config || !in_array($this->tipo, $config['types'], true) || !isset($config['halves'][$half])) {
            return null;
        }

        return $config['halves'][$half] + ['block' => $block, 'half' => $half];
    }

    /** Uma metade está preenchida quando tem data (no fecho, conta também qual das duas é). */
    public function stepDone(array $step): bool
    {
        if (!$this->{$step['date']}) {
            return false;
        }
        if ($step['half'] === 'recusado') {
            return (bool) $this->motivo_recusa;
        }
        if ($step['half'] === 'concluido') {
            return !$this->motivo_recusa;
        }

        return true;
    }

    /**
     * A próxima coisa a fazer: a primeira metade por preencher depois da última preenchida.
     * Assim, uma metade deixada de propósito em branco (peça já em stock) não fica eternamente a pedir atenção.
     */
    public function nextStep(): ?array
    {
        $steps = $this->steps();
        $lastDone = -1;
        foreach ($steps as $i => $step) {
            if ($this->stepDone($step)) {
                $lastDone = $i;
            }
        }
        foreach ($steps as $i => $step) {
            if ($i > $lastDone && $step['half'] !== 'recusado' && !$this->stepDone($step)) {
                return $step;
            }
        }

        return null;
    }

    /** O que falta fazer, em texto. */
    public function todoLabel(): ?string
    {
        $next = $this->nextStep();

        return $next && isset($next['todo']) ? __($next['todo']) : null;
    }

    /** A fase e a data em que entrámos nela, deduzidas das datas preenchidas. */
    public function syncPhase(): void
    {
        $fase = 'registado';
        $desde = $this->data_avaria;
        foreach ($this->steps() as $step) {
            if ($this->stepDone($step)) {
                $fase = $step['fase'];
                $desde = $this->{$step['date']};
            }
        }
        $this->fase = $fase;
        $this->fase_desde = $desde;
    }

    // ------------------------------------------------------------------ vista

    public function phase(): array
    {
        return config('warranty.phases.' . $this->fase, config('warranty.phases.registado'));
    }

    /** "Recebemos a peça" / "Já tínhamos a peça connosco". */
    public function partOriginLabel(): ?string
    {
        return $this->peca_origem ? __(config('warranty.part_origins.' . $this->peca_origem, $this->peca_origem)) : null;
    }

    public function isOpen(): bool
    {
        return !in_array($this->fase, self::CLOSED_PHASES, true);
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
