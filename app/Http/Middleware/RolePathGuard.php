<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RolePathGuard
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('admin/login') || $request->is('collector/login') || $request->is('technician/login')) {
            return $next($request);
        }

        if ($request->is('admin/*')) {
            return $this->guard($request, $next, ['admin'], '/admin/login');
        }

        if ($request->is('collector/*')) {
            return $this->guard($request, $next, ['collector', 'kasir'], '/collector/login');
        }

        if ($request->is('technician/*')) {
            return $this->guard($request, $next, ['technician', 'teknisi'], '/technician/login');
        }

        return $next($request);
    }

    private function guard(Request $request, Closure $next, array $roles, string $loginUrl): Response
    {
        if (! Auth::check()) {
            return redirect($loginUrl);
        }

        $user = Auth::user();

        if (! in_array($user->role ?? null, $roles, true)) {
            abort(403, 'Akses tidak sesuai role.');
        }

        return $next($request);
    }
}
