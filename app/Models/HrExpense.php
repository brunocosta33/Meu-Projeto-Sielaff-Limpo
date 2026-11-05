<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Despesa de um colaborador: lançada por ele, aprovada e paga pela direção. */
class HrExpense extends Model
{
    public const STATES = [
        'pendente' => ['label' => 'Pendente', 'color' => '#f9a825', 'bg' => '#fff4cc', 'text' => '#7a5600', 'icon' => 'fa-hourglass-half'],
        'aprovada' => ['label' => 'Aprovada', 'color' => '#2e7d32', 'bg' => '#dff0e0', 'text' => '#1b5e20', 'icon' => 'fa-check-circle'],
        'rejeitada' => ['label' => 'Rejeitada', 'color' => '#c62828', 'bg' => '#fbdcdc', 'text' => '#a31515', 'icon' => 'fa-times-circle'],
    ];

    protected $fillable = [
        'hr_staff_id', 'data', 'categoria', 'descricao', 'valor',
        'estado', 'motivo_rejeicao', 'decided_by', 'decided_at',
        'pago_em', 'pago_como', 'pago_por', 'created_by',
    ];

    protected $casts = [
        'data' => 'date',
        'valor' => 'decimal:2',
        'decided_at' => 'datetime',
        'pago_em' => 'date',
    ];

    public function staff()
    {
        return $this->belongsTo(HrStaff::class, 'hr_staff_id');
    }

    public function files()
    {
        return $this->hasMany(HrExpenseFile::class)->orderBy('id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function decider()
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function payer()
    {
        return $this->belongsTo(User::class, 'pago_por');
    }

    public function scopePending($query)
    {
        return $query->where('estado', 'pendente');
    }

    /** Aprovadas que ainda não foram pagas: o que a direção tem por liquidar. */
    public function scopeToPay($query)
    {
        return $query->where('estado', 'aprovada')->whereNull('pago_em');
    }

    public function state(): array
    {
        return self::STATES[$this->estado] ?? self::STATES['pendente'];
    }

    public function stateLabel(): string
    {
        return __($this->state()['label']);
    }

    public function categoriaLabel(): string
    {
        return __(config('hr.expense_categories.' . $this->categoria, $this->categoria));
    }

    public function paymentLabel(): ?string
    {
        return $this->pago_como ? __(config('hr.expense_payment_methods.' . $this->pago_como, $this->pago_como)) : null;
    }

    public function isPaid(): bool
    {
        return $this->pago_em !== null;
    }

    /** Só se paga o que já foi aprovado. */
    public function canBePaid(): bool
    {
        return $this->estado === 'aprovada' && !$this->isPaid();
    }

    /** Enquanto ninguém decidiu, quem a lançou ainda lhe pode mexer. */
    public function isOpen(): bool
    {
        return $this->estado === 'pendente';
    }
}
