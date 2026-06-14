<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class RoleAuthController extends Controller
{
    private array $roles = [
        'admin' => [
            'title' => 'Login Admin',
            'label' => 'Admin',
            'roles' => ['admin'],
            'dashboard' => '/admin/dashboard',
        ],
        'collector' => [
            'title' => 'Login Kasir',
            'label' => 'Kasir',
            'roles' => ['collector', 'kasir'],
            'dashboard' => '/collector/dashboard',
        ],
        'technician' => [
            'title' => 'Login Teknisi',
            'label' => 'Teknisi',
            'roles' => ['technician', 'teknisi'],
            'dashboard' => '/technician/dashboard',
        ],
    ];

    public function show(string $role)
    {
        abort_unless(isset($this->roles[$role]), 404);

        return view('auth.role-login', [
            'role' => $role,
            'meta' => $this->roles[$role],
            'postUrl' => '/'.$role.'/login',
        ]);
    }

    public function login(Request $request, string $role)
    {
        abort_unless(isset($this->roles[$role]), 404);

        $meta = $this->roles[$role];

        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $login = trim((string) ($request->input('login') ?: $request->input('username') ?: $request->input('email')));

        if ($login === '') {
            return back()
                ->withErrors(['login' => 'Username/email wajib diisi.'])
                ->withInput(['login' => $login]);
        }

        $loginLower = mb_strtolower($login);

        $user = User::query()
            ->where(function ($query) use ($login, $loginLower) {
                if (Schema::hasColumn('users', 'username')) {
                    $query->orWhereRaw('LOWER(username) = ?', [$loginLower]);
                }

                if (Schema::hasColumn('users', 'email')) {
                    $query->orWhereRaw('LOWER(email) = ?', [$loginLower]);
                }

                if (Schema::hasColumn('users', 'phone')) {
                    $query->orWhere('phone', $login);
                }
            })
            ->first();

        if (! $user || ! Hash::check((string) $request->input('password'), (string) $user->password)) {
            return back()
                ->withErrors(['login' => 'Username/email atau password salah.'])
                ->withInput(['login' => $login]);
        }

        if (($user->status ?? 'active') !== 'active') {
            return back()
                ->withErrors(['login' => 'Akun tidak aktif.'])
                ->withInput(['login' => $login]);
        }

        if (! in_array($user->role ?? null, $meta['roles'], true)) {
            return back()
                ->withErrors(['login' => 'Akun ini bukan role '.$meta['label'].'.'])
                ->withInput(['login' => $login]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect($meta['dashboard']);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Berhasil keluar.');
    }
}
