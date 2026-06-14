<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class DashboardRedirectController extends Controller
{
    public function __invoke()
    {
        $role = Auth::user()->role ?? 'admin';

        return match ($role) {
            'collector', 'kasir' => redirect('/collector/dashboard'),
            'technician', 'teknisi' => redirect('/technician/dashboard'),
            default => redirect('/admin/dashboard'),
        };
    }
}
