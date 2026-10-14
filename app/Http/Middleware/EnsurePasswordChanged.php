<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        abort_if($user && ! $user->is_active && ! $request->routeIs('logout'), 403);

        if ($user?->must_change_password && ! $request->routeIs('account.password.*', 'logout')) {
            return redirect()->route('account.password.edit');
        }

        return $next($request);
    }
}
