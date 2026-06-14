<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class UserSettingController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureAdmin();

        $search = trim((string) $request->query('search', ''));

        $users = User::query()
            ->whereIn('role', ['collector', 'kasir', 'technician', 'teknisi'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");

                    if (Schema::hasColumn('users', 'username')) {
                        $q->orWhere('username', 'like', "%{$search}%");
                    }

                    if (Schema::hasColumn('users', 'phone')) {
                        $q->orWhere('phone', 'like', "%{$search}%");
                    }
                });
            })
            ->orderBy('role')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.settings.users.index', compact('users', 'search'));
    }

    public function create()
    {
        $this->ensureAdmin();

        $user = new User([
            'role' => 'collector',
            'status' => 'active',
        ]);

        return view('admin.settings.users.form', compact('user'));
    }

    public function store(Request $request)
    {
        $this->ensureAdmin();

        $data = $this->validatedData($request);
        $plainPassword = (string) $data['password'];

        $data['role'] = $this->normalizeRole($data['role']);
        $data['status'] = $data['status'] ?? 'active';

        if (Schema::hasColumn('users', 'email')) {
            $data['email'] = $this->safeEmail($data['username']);
        }

        $hash = Hash::make($plainPassword);
        $data['password'] = $hash;

        $user = User::create($data);

        // Paksa simpan hash mentah langsung ke database agar tidak ada masalah double-hash/cast.
        DB::table('users')->where('id', $user->id)->update([
            'password' => $hash,
            'updated_at' => now(),
        ]);

        $user->refresh();

        if (! Hash::check($plainPassword, (string) $user->password)) {
            return back()
                ->withInput($request->except('password'))
                ->withErrors(['password' => 'Password gagal tersimpan dengan benar. Silakan coba lagi.']);
        }

        return redirect('/admin/settings/users')->with('success', 'Pegawai berhasil dibuat. Username: '.$user->username);
    }

    public function edit(User $user)
    {
        $this->ensureAdmin();

        abort_unless(in_array($user->role, ['collector', 'kasir', 'technician', 'teknisi'], true), 404);

        return view('admin.settings.users.form', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $this->ensureAdmin();

        abort_unless(in_array($user->role, ['collector', 'kasir', 'technician', 'teknisi'], true), 404);

        $data = $this->validatedData($request, $user->id);
        $plainPassword = $data['password'] ?? null;

        $data['role'] = $this->normalizeRole($data['role']);
        $data['status'] = $data['status'] ?? 'active';

        if (Schema::hasColumn('users', 'email')) {
            $data['email'] = $this->safeEmail($data['username']);
        }

        if ($plainPassword === null || $plainPassword === '') {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make((string) $plainPassword);
        }

        $user->update($data);

        if ($plainPassword !== null && $plainPassword !== '') {
            $hash = Hash::make((string) $plainPassword);

            DB::table('users')->where('id', $user->id)->update([
                'password' => $hash,
                'updated_at' => now(),
            ]);
        }

        return redirect('/admin/settings/users')->with('success', 'Pegawai berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $this->ensureAdmin();

        abort_unless(in_array($user->role, ['collector', 'kasir', 'technician', 'teknisi'], true), 404);

        if (auth()->id() === $user->id) {
            return back()->with('error', 'Akun yang sedang login tidak boleh dihapus.');
        }

        $user->delete();

        return redirect('/admin/settings/users')->with('success', 'Pegawai berhasil dihapus.');
    }

    private function ensureAdmin(): void
    {
        abort_unless(auth()->check() && auth()->user()->role === 'admin', 403);
    }

    private function validatedData(Request $request, ?int $ignoreId = null): array
    {
        $passwordRule = $ignoreId ? ['nullable', 'string', 'min:6'] : ['required', 'string', 'min:6'];

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:100',
                'alpha_dash',
                Rule::unique('users', 'username')->ignore($ignoreId),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:1000'],
            'role' => ['required', Rule::in(['collector', 'kasir', 'technician', 'teknisi'])],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'password' => $passwordRule,
        ]);
    }

    private function normalizeRole(string $role): string
    {
        return match ($role) {
            'kasir' => 'collector',
            'teknisi' => 'technician',
            default => $role,
        };
    }

    private function safeEmail(string $username): string
    {
        return strtolower($username).'@macservice.local';
    }
}
