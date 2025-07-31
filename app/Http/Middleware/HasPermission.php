<?php

namespace App\Http\Middleware;

use App\Models\Permission;
use App\Models\Permission_role;
use App\Models\Role;
use Closure;

class HasPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, ...$permissions_array)
    {
        if ($request->user() != null && $request->user()->hasPermissionsOrRole($permissions_array)) {
            return $next($request);
        } else {
            flash(__('You do not have permissions to execute this action.'));
            return redirect()->back();
        }
        flash(__('You do not have permissions to execute this action.'));
        return redirect()->back();
    }
}
  