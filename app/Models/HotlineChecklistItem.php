<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Uma linha da checklist diária da Hotline. Só a chefia a cria, altera ou arquiva. */
class HotlineChecklistItem extends Model
{
    /** A que turno se aplica. Quem entra às 08:00 não faz o fecho das 20:00. */
    public const TURNOS = [
        'ambos' => ['label' => 'Os dois turnos', 'curto' => 'Sempre', 'icon' => 'fa-clock', 'cor' => '#475569'],
        'manha' => ['label' => 'Turno da manhã', 'curto' => 'Manhã', 'icon' => 'fa-sun', 'cor' => '#b45309'],
        'tarde' => ['label' => 'Turno da tarde', 'curto' => 'Tarde', 'icon' => 'fa-moon', 'cor' => '#4338ca'],
    ];

    protected $fillable = ['titulo', 'nota', 'turno', 'ordem', 'ativo', 'created_by'];

    protected $casts = ['ativo' => 'boolean'];

    public function checks()
    {
        return $this->hasMany(HotlineChecklistCheck::class, 'item_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    /** Pela ordem que a chefia lhe deu. */
    public function scopeOrdenados($query)
    {
        return $query->orderBy('ordem')->orderBy('id');
    }

    /** Esta tarefa é para quem está neste turno? */
    public function doTurno(?string $turno): bool
    {
        return $turno !== null && ($this->turno === 'ambos' || $this->turno === $turno);
    }

    public function turnoConfig(): array
    {
        return self::TURNOS[$this->turno] ?? self::TURNOS['ambos'];
    }

    public function turnoLabel(): string
    {
        return __($this->turnoConfig()['label']);
    }
}
