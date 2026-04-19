<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RestrictTechnicianAccess
{
    private const ALLOWED_ROUTE_NAMES = [
        'backoffice.index',
        'backoffice.technical_requests.index',
        'backoffice.technical_requests.show',
        'backoffice.technical_requests.edit',
        'backoffice.technical_requests.update',
        'backoffice.technical_requests.my_open',
        'backoffice.task_schedules.minhas',
        'backoffice.task_schedules.minhas.show',
        'backoffice.task_schedules.minhas.update',
        'backoffice.task_schedules.minhas.concluir_todas',
        'backoffice.stores.index',
        'backoffice.profile.index',
        'backoffice.myprofile.index',
        'backoffice.profile.change-password',
        'backoffice.profile.change-password.save',
        'user.locale',
    ];

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || !$user->hasRole('user')) {
            return $next($request);
        }

        $routeName = optional($request->route())->getName();

        if ($routeName && in_array($routeName, self::ALLOWED_ROUTE_NAMES, true)) {
            return $next($request);
        }

        abort(403, 'Não tem acesso a esta área.');
    }
}
