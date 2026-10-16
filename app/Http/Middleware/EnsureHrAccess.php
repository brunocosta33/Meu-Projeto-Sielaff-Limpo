<?php

namespace App\Http\Middleware;

use App\Services\HrAccess;
use Closure;
use Illuminate\Http\Request;

class EnsureHrAccess
{
    /** Uso: 'hr.access' (ver a sua parte), 'hr.access:manager' (só gestão de RH) ou 'hr.access:warranty' (Fora da Garantia). */
    public function handle(Request $request, Closure $next, string $level = 'view')
    {
        $user = $request->user();
        $allowed = match ($level) {
            'manager' => HrAccess::isManager($user),
            'warranty' => HrAccess::canManageWarranty($user),
            default => HrAccess::canAccess($user),
        };

        abort_unless($allowed, 403, $level === 'warranty' ? __('Não tem acesso a esta área.') : 'Não tem acesso a esta área de Recursos Humanos.');

        return $next($request);
    }
}
