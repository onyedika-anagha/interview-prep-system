<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * This is a local, single-user tool (see PRD FR14) — there is no login screen.
 * Auto-authenticate as the one seeded user so the existing starter-kit chrome
 * (which expects a logged-in user) keeps working without a login flow.
 */
class AutoLoginLocalUser
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check() && $user = User::first()) {
            Auth::login($user);
        }

        return $next($request);
    }
}
