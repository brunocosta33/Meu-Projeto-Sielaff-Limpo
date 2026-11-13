<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Uma linha da checklist diária da Hotline. Só a chefia a cria, altera ou arquiva. */
class HotlineChecklistItem extends Model
{
    /** A que turno se aplica. Quem entra às 08:00 não faz o fecho das 20:00. */
    public const TURNOS = [
        'ambos' => ['label' => 'Os dois turnos', 'curto' => 'Sempre', 'icon' => 'fa-clock', 'cor' => '#0ea5e9'],
        'manha' => ['label' => 'Turno da manhã', 'curto' => 'Manhã', 'icon' => 'fa-sun', 'cor' => '#f59e0b'],
        'tarde' => ['label' => 'Turno da tarde', 'curto' => 'Tarde', 'icon' => 'fa-moon', 'cor' => '#8b5cf6'],
    ];

    /** Em que dias é que a tarefa se faz. Ao fim de semana o trabalho é outro. */
    public const DIAS = [
        'todos' => ['label' => 'Todos os dias', 'curto' => 'Todos os dias', 'icon' => 'fa-calendar-day', 'cor' => '#475569'],
        'semana' => ['label' => 'Só de segunda a sexta', 'curto' => 'Dias de semana', 'icon' => 'fa-briefcase', 'cor' => '#0284c7'],
        'fim_de_semana' => ['label' => 'Só ao fim de semana', 'curto' => 'Fim de semana', 'icon' => 'fa-umbrella-beach', 'cor' => '#14b8a6'],
    ];

    protected $fillable = ['titulo', 'nota', 'turno', 'dias', 'ordem', 'ativo', 'created_by'];

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

    /** Esta tarefa faz-se neste dia? */
    public function noDia(\Carbon\Carbon $dia): bool
    {
        return match ($this->dias) {
            'semana' => $dia->dayOfWeekIso <= 5,
            'fim_de_semana' => $dia->dayOfWeekIso >= 6,
            default => true,
        };
    }

    public function diasConfig(): array
    {
        return self::DIAS[$this->dias] ?? self::DIAS['todos'];
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
