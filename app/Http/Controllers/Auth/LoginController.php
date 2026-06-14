<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function form()
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $login = trim($request->input('login'));

        $user = User::query()
            ->where('username', $login)
            ->orWhere('email', $login)
            ->first();

        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            return back()
                ->withInput($request->only('login'))
                ->with('error', 'Username/email atau password salah.');
        }

        if (($user->status ?? 'active') !== 'active') {
            return back()->with('error', 'Akun tidak aktif.');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect('/dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
