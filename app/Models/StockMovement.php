<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class StockMovement extends Model
{
    use HasFactory;

    public const TYPES = [
        'warehouse_in' => 'Entrada em armazém',
        'to_technician' => 'Enviado para técnico',
        'from_technician' => 'Devolvido ao armazém',
        'consumed' => 'Consumido',
        'adjustment' => 'Ajuste manual',
    ];

    /**
     * Metadados visuais por tipo de movimento.
     * flow: in (aumenta stock total), out (reduz), transfer (interno), adjustment (depende do sinal).
     */
    public const TYPE_META = [
        'warehouse_in' => ['flow' => 'in', 'color' => 'success', 'icon' => 'fa-arrow-down'],
        'to_technician' => ['flow' => 'transfer', 'color' => 'info', 'icon' => 'fa-truck'],
        'from_technician' => ['flow' => 'transfer', 'color' => 'info', 'icon' => 'fa-undo'],
        'consumed' => ['flow' => 'out', 'color' => 'danger', 'icon' => 'fa-wrench'],
        'adjustment' => ['flow' => 'adjustment', 'color' => 'warning', 'icon' => 'fa-sliders-h'],
    ];

    protected $fillable = [
        'item_id',
        'part_id',
        'technician_id',
        'movement_type',
        'quantity',
        'source',
        'destination',
        'notes',
        'moved_at',
        'created_by',
    ];

    protected $casts = [
        'moved_at' => 'datetime',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Aplica os filtros da página de movimentos (partilhado com a exportação).
     */
    public function scopeApplyFilters(Builder $query, array $filters): Builder
    {
        if (!empty($filters['item_id'])) {
            $query->where('item_id', $filters['item_id']);
        }

        if (!empty($filters['technician_id'])) {
            $query->where('technician_id', $filters['technician_id']);
        }

        if (!empty($filters['movement_type'])) {
            $query->where('movement_type', $filters['movement_type']);
        }

        [$dateFrom, $dateTo] = self::resolvePeriod($filters);

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        if (!empty($filters['q'])) {
            $term = trim((string) $filters['q']);

            $query->where(function (Builder $builder) use ($term) {
                $builder->where('source', 'like', '%' . $term . '%')
                    ->orWhere('destination', 'like', '%' . $term . '%')
                    ->orWhere('notes', 'like', '%' . $term . '%')
                    ->orWhereHas('item', function (Builder $itemQuery) use ($term) {
                        $itemQuery->where('reference', 'like', '%' . $term . '%')
                            ->orWhere('name', 'like', '%' . $term . '%');
                    })
                    ->orWhereHas('technician', function (Builder $techQuery) use ($term) {
                        $techQuery->where('name', 'like', '%' . $term . '%')
                            ->orWhere('email', 'like', '%' . $term . '%');
                    });
            });
        }

        return $query;
    }

    /**
     * Converte o atalho de período (today|week|month) em datas, ou usa as datas explícitas.
     */
    private static function resolvePeriod(array $filters): array
    {
        switch ($filters['period'] ?? null) {
            case 'today':
                return [Carbon::today()->toDateString(), Carbon::today()->toDateString()];
            case 'week':
                return [Carbon::now()->startOfWeek()->toDateString(), Carbon::now()->endOfWeek()->toDateString()];
            case 'month':
                return [Carbon::now()->startOfMonth()->toDateString(), Carbon::now()->endOfMonth()->toDateString()];
        }

        return [$filters['date_from'] ?? null, $filters['date_to'] ?? null];
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->movement_type] ?? $this->movement_type;
    }

    public function getTypeIconAttribute(): string
    {
        return self::TYPE_META[$this->movement_type]['icon'] ?? 'fa-exchange-alt';
    }

    /**
     * Efeito real deste movimento no stock total: in | out | transfer.
     */
    public function getFlowAttribute(): string
    {
        $flow = self::TYPE_META[$this->movement_type]['flow'] ?? 'transfer';

        if ($flow === 'adjustment') {
            return $this->quantity < 0 ? 'out' : 'in';
        }

        return $flow;
    }

    /**
     * Cor do badge: ajustes verdes/vermelhos conforme o sinal, restantes conforme o tipo.
     */
    public function getTypeColorAttribute(): string
    {
        if ($this->movement_type === 'adjustment') {
            return $this->quantity < 0 ? 'danger' : 'success';
        }

        return self::TYPE_META[$this->movement_type]['color'] ?? 'secondary';
    }

    /**
     * Quantidade com sinal: + entrada, - saída, magnitude para transferências.
     */
    public function getSignedQuantityAttribute(): string
    {
        $magnitude = abs((int) $this->quantity);

        return match ($this->flow) {
            'in' => '+' . $magnitude,
            'out' => '-' . $magnitude,
            default => (string) $magnitude,
        };
    }
}
