<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectAuthenticatedAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard('admin');
        $administrator = $guard->user();

        if ($administrator !== null && ! $administrator->is_active) {
            $guard->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return $next($request);
        }

        if ($administrator !== null) {
            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}
