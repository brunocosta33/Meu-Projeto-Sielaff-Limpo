<?php

namespace App\Http\Middleware;

use App\Services\HrAccess;
use Closure;
use Illuminate\Http\Request;

class EnsureHrAccess
{
    /** Uso: 'hr.access' (ver a sua parte) ou 'hr.access:manager' (só gestão de RH). */
    public function handle(Request $request, Closure $next, string $level = 'view')
    {
        $user = $request->user();
        $allowed = $level === 'manager' ? HrAccess::isManager($user) : HrAccess::canAccess($user);

        abort_unless($allowed, 403, 'Não tem acesso a esta área de Recursos Humanos.');

        return $next($request);
    }
}
