<?php

namespace App\Services;

use App\Models\HrStaff;
use App\Models\User;

/**
 * Quem vê o quê em Recursos Humanos:
 * - gestão de RH (funções em hr.manager_functions): vê e gere tudo;
 * - restantes colaboradores: só veem a sua própria parte (só consulta).
 * Enquanto não houver ninguém da gestão de RH ativo, os administradores ficam como gestão
 * (configuração inicial).
 */
class HrAccess
{
    private static array $staffCache = [];
    private static ?bool $hasManagers = null;

    public static function staffOf(?User $user): ?HrStaff
    {
        if (!$user) {
            return null;
        }
        if (!array_key_exists($user->id, self::$staffCache)) {
            self::$staffCache[$user->id] = HrStaff::where('user_id', $user->id)->where('ativo', true)->first();
        }

        return self::$staffCache[$user->id];
    }

    public static function isManager(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        $functions = config('hr.manager_functions', []);
        self::$hasManagers ??= HrStaff::where('ativo', true)->whereIn('funcao', $functions)->exists();

        if (!self::$hasManagers) {
            return $user->hasRole('admin');
        }

        return in_array(self::staffOf($user)?->funcao, $functions, true);
    }

    /** Tem acesso a Recursos Humanos (gestão ou, pelo menos, a sua parte)? */
    public static function canAccess(?User $user): bool
    {
        return self::isManager($user) || self::staffOf($user) !== null;
    }

    /** Id do colaborador do utilizador, ou null se for gestão (vê todos). */
    public static function ownStaffIdFilter(?User $user): ?int
    {
        return self::isManager($user) ? null : (self::staffOf($user)?->id ?? 0);
    }
}
