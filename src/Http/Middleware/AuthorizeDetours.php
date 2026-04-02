<?php

namespace JustBetter\Detour\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Statamic\Facades\User;
use Symfony\Component\HttpFoundation\Response;

class AuthorizeDetours
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = User::current();
        $permission = config()->string('justbetter.statamic-detour.permissions.access');

        abort_unless(
            $user && ($user->isSuper() || $user->hasPermission($permission)),
            403
        );

        return $next($request);
    }
}
