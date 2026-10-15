<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrStaff extends Model
{
    protected $table = 'hr_staff';

    protected $fillable = [
        'user_id',
        'funcao',
        'rotation_group',
        'zona',
        'contacto',
        'ativo',
        'observacoes',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function overrides()
    {
        return $this->hasMany(HrShiftOverride::class);
    }

    public function getFuncaoLabelAttribute(): ?string
    {
        return config('hr.functions.' . $this->funcao);
    }

    public function getFuncaoColorAttribute(): string
    {
        return config('hr.function_colors.' . $this->funcao, '#6c757d');
    }

    /**
     * O utilizador pode ver e gerir os Colaboradores (coordenador ou country manager ativo)?
     * Enquanto não houver nenhum registado, os administradores mantêm acesso para a configuração inicial.
     */
    public static function canManageStaff(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        $functions = config('hr.staff_manager_functions', []);
        $managers = static::where('ativo', true)->whereIn('funcao', $functions);

        if (!(clone $managers)->exists()) {
            return $user->hasRole('admin');
        }

        return $managers->where('user_id', $user->id)->exists();
    }

    /** O utilizador pode aprovar férias (é coordenador ou country manager ativo)? */
    public static function canApproveVacations(?User $user): bool
    {
        return $user && static::where('user_id', $user->id)
            ->where('ativo', true)
            ->whereIn('funcao', config('hr.vacation_approver_functions', []))
            ->exists();
    }

    public function vacations()
    {
        return $this->hasMany(HrVacation::class);
    }

    public function vacationBalances()
    {
        return $this->hasMany(HrVacationBalance::class);
    }

    /** Técnicos com grupo e zona definidos (entram na escala de piquete). */
    public function scopeActivePiquete($query)
    {
        return $query->where('ativo', true)->where('funcao', 'tecnico')->whereNotNull('rotation_group')->whereNotNull('zona');
    }

    public function scopeActiveHotliners($query)
    {
        return $query->where('ativo', true)->where('funcao', 'hotliner')->whereNotNull('rotation_group');
    }
}
