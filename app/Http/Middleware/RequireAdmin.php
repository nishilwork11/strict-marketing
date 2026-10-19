<?php

namespace App\Http\Middleware;

use App\Support\AdminSession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// The admin panel's gatekeeper: signed-out visitors go to the login page, for
// every page and every change, since a change can be posted without the page.
class RequireAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! AdminSession::check($request)) {
            return $request->expectsJson()
                ? response()->json(['redirect' => route('admin.login')], 401)
                : redirect()->route('admin.login');
        }

        return $next($request);
    }
}
