<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UseRoleGuard
{
    public function handle(Request $request, Closure $next, string $guard, string $role): Response
    {
        Auth::shouldUse($guard);

        if (! Auth::guard($guard)->check()) {
            return redirect('/'.$guard.'/login');
        }

        $user = Auth::guard($guard)->user();

        $allowedRoles = match ($role) {
            'admin' => ['admin'],
            'collector' => ['collector', 'kasir'],
            'technician' => ['technician', 'teknisi'],
            default => [$role],
        };

        if (! in_array($user->role ?? null, $allowedRoles, true)) {
            abort(403, 'Akses tidak sesuai role.');
        }

        return $next($request);
    }
}
