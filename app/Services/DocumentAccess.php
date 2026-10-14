<?php

namespace App\Services;

use App\Models\DocumentPermission;
use App\Models\HrStaff;
use App\Models\User;

/**
 * Acesso à Documentação pela função do colaborador (Recursos Humanos → Colaboradores).
 * O Coordenador tem sempre acesso total e decide que outras funções podem ver/gerir.
 */
class DocumentAccess
{
    private static array $functionCache = [];

    public static function functionOf(?User $user): ?string
    {
        if (!$user) {
            return null;
        }

        return self::$functionCache[$user->id] ??= HrStaff::where('user_id', $user->id)->where('ativo', true)->value('funcao');
    }

    /** Coordenador: acesso total e configura quem tem acesso. */
    public static function canConfigure(?User $user): bool
    {
        return self::functionOf($user) === config('documents.owner_function');
    }

    public static function canView(?User $user): bool
    {
        return self::canConfigure($user) || (bool) self::permission($user)?->pode_ver;
    }

    public static function canManage(?User $user): bool
    {
        return self::canConfigure($user) || (bool) self::permission($user)?->pode_gerir;
    }

    private static function permission(?User $user): ?DocumentPermission
    {
        $funcao = self::functionOf($user);

        return $funcao ? DocumentPermission::where('funcao', $funcao)->first() : null;
    }
}
