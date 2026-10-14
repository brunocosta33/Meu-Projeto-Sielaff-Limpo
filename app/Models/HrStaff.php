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

    public function scopeActiveHotliners($query)
    {
        return $query->where('ativo', true)->where('funcao', 'hotliner')->whereNotNull('rotation_group');
    }
}
