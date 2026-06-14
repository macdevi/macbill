<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MikrotikRouter;
use App\Models\Customer;
use App\Models\MikrotikPppoeProfile;
use App\Models\MikrotikPppoeSecret;
use App\Models\MikrotikPppoeActiveSession;
use App\Services\SettingService;
use App\Services\MikrotikApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Throwable;

class SettingController extends Controller
{
    private function ensureAdmin(): void
    {
        abort_unless(auth()->check() && auth()->user()->role === 'admin', 403);
    }

    public function general()
    {
        $this->ensureAdmin();

        $settings = SettingService::allMerged();

        return view('admin.settings.general', compact('settings'));
    }

    public function updateGeneral(Request $request)
    {
        $this->ensureAdmin();

        $data = $request->validate([
            'app_name' => ['required', 'string', 'max:100'],
            'business_name' => ['required', 'string', 'max:150'],
            'owner_name' => ['nullable', 'string', 'max:150'],
            'business_phone' => ['nullable', 'string', 'max:50'],
            'business_whatsapp' => ['nullable', 'string', 'max:50'],
            'business_email' => ['nullable', 'email', 'max:150'],
            'business_address' => ['nullable', 'string', 'max:1000'],
            'business_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'business_favicon' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,ico', 'max:1024'],

            'landing_title' => ['required', 'string', 'max:160'],
            'landing_subtitle' => ['nullable', 'string', 'max:250'],

            'invoice_prefix' => ['required', 'string', 'max:20'],
            'receipt_prefix' => ['required', 'string', 'max:20'],
            'invoice_note' => ['nullable', 'string', 'max:1000'],
            'receipt_footer' => ['nullable', 'string', 'max:1000'],

            'currency' => ['required', Rule::in(['IDR'])],
            'timezone' => ['required', Rule::in(['Asia/Jakarta', 'Asia/Makassar', 'Asia/Jayapura'])],
            'default_payment_method' => ['required', Rule::in(['Tunai', 'Transfer', 'QRIS'])],
            'overdue_months' => ['required', 'integer', 'min:1', 'max:12'],
            'map_default_layer' => ['required', Rule::in(['satellite', 'street'])],

            'admin_username' => ['nullable', 'string', 'max:100', 'alpha_dash', Rule::unique('users', 'username')->ignore(auth()->id())],
            'admin_password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ]);

        foreach (['business_logo', 'business_favicon'] as $fileKey) {
            if ($request->hasFile($fileKey)) {
                $oldPath = SettingService::get($fileKey);

                if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }

                $data[$fileKey] = $request->file($fileKey)->store('settings', 'public');
            } else {
                unset($data[$fileKey]);
            }
        }

        /*
         * UPDATE ADMIN LOGIN FROM GENERAL SETTINGS
         * admin_password kosong = password tidak diubah.
         */
        $adminUsername = trim((string) ($data['admin_username'] ?? ''));
        $adminPassword = (string) ($data['admin_password'] ?? '');

        unset($data['admin_username'], $data['admin_password'], $data['admin_password_confirmation']);

        $adminUser = auth()->user();

        if ($adminUser) {
            if ($adminUsername !== '') {
                $adminUser->username = $adminUsername;
            }

            if ($adminPassword !== '') {
                $adminUser->password = Hash::make($adminPassword);
            }

            $adminUser->save();
        }

        SettingService::setMany($data, 'general');

        return back()->with('success', 'Pengaturan umum berhasil disimpan.');
    }

    public function mikrotik(Request $request)
    {
        $this->ensureAdmin();

        $search = trim((string) $request->query('search', ''));

        $routers = MikrotikRouter::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('host', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('status')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.settings.mikrotik', compact('routers', 'search'));
    }

    public function mikrotikCreate()
    {
        $this->ensureAdmin();

        $router = new MikrotikRouter([
            'api_port' => 8728,
            'use_ssl' => false,
            'status' => 'active',
        ]);

        return view('admin.settings.mikrotik-form', compact('router'));
    }

    public function mikrotikStore(Request $request)
    {
        $this->ensureAdmin();

        $data = $this->validateMikrotik($request);

        MikrotikRouter::create($data);

        return redirect('/admin/settings/mikrotik')->with('success', 'Konfigurasi Mikrotik berhasil dibuat.');
    }

    public function mikrotikEdit(MikrotikRouter $router)
    {
        $this->ensureAdmin();

        return view('admin.settings.mikrotik-form', compact('router'));
    }

    public function mikrotikUpdate(Request $request, MikrotikRouter $router)
    {
        $this->ensureAdmin();

        $data = $this->validateMikrotik($request, true);

        if (empty($data['api_password'])) {
            unset($data['api_password']);
        }

        $router->update($data);

        return redirect('/admin/settings/mikrotik')->with('success', 'Konfigurasi Mikrotik berhasil diperbarui.');
    }

    public function mikrotikDestroy(MikrotikRouter $router)
    {
        $this->ensureAdmin();

        $router->delete();

        return redirect('/admin/settings/mikrotik')->with('success', 'Konfigurasi Mikrotik berhasil dihapus.');
    }

    private function validateMikrotik(Request $request, bool $isUpdate = false): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'host' => ['required', 'string', 'max:150'],
            'api_port' => ['required', 'integer', 'min:1', 'max:65535'],
            'username' => ['required', 'string', 'max:150'],
            'api_password' => [$isUpdate ? 'nullable' : 'required', 'string', 'max:500'],
            'use_ssl' => ['required', Rule::in(['0', '1'])],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
    }


    public function mikrotikTest(MikrotikRouter $router)
    {
        $this->ensureAdmin();

        try {
            $result = (new MikrotikApiClient($router))->test();

            $router->update([
                'last_test_status' => 'success',
                'last_test_message' => $result['message'],
                'last_test_at' => now(),
            ]);

            return back()->with('success', 'Test koneksi berhasil. '.$result['message']);
        } catch (Throwable $e) {
            $router->update([
                'last_test_status' => 'failed',
                'last_test_message' => $e->getMessage(),
                'last_test_at' => now(),
            ]);

            return back()->with('error', 'Test koneksi gagal: '.$e->getMessage());
        }
    }


    public function mikrotikSyncProfiles(MikrotikRouter $router)
    {
        $this->ensureAdmin();

        try {
            $profiles = (new MikrotikApiClient($router))->pppoeProfiles();

            $count = 0;

            foreach ($profiles as $profile) {
                MikrotikPppoeProfile::query()->updateOrCreate(
                    [
                        'mikrotik_router_id' => $router->id,
                        'name' => $profile['name'],
                    ],
                    [
                        'mikrotik_id' => $profile['.id'] ?? null,
                        'local_address' => $profile['local-address'] ?? null,
                        'remote_address' => $profile['remote-address'] ?? null,
                        'rate_limit' => $profile['rate-limit'] ?? null,
                        'only_one' => $profile['only-one'] ?? null,
                        'raw_json' => json_encode($profile, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        'last_synced_at' => now(),
                    ]
                );

                $count++;
            }

            $router->update([
                'last_test_status' => 'success',
                'last_test_message' => "Berhasil sync {$count} PPPoE profile.",
                'last_test_at' => now(),
            ]);

            return back()->with('success', "Berhasil mengambil {$count} PPPoE profile dari Mikrotik.");
        } catch (Throwable $e) {
            $router->update([
                'last_test_status' => 'failed',
                'last_test_message' => $e->getMessage(),
                'last_test_at' => now(),
            ]);

            return back()->with('error', 'Gagal mengambil PPPoE profile: '.$e->getMessage());
        }
    }


    public function mikrotikSyncSecrets(MikrotikRouter $router)
    {
        $this->ensureAdmin();

        try {
            $secrets = (new MikrotikApiClient($router))->pppoeSecrets();

            $count = 0;

            foreach ($secrets as $secret) {
                MikrotikPppoeSecret::query()->updateOrCreate(
                    [
                        'mikrotik_router_id' => $router->id,
                        'name' => $secret['name'],
                    ],
                    [
                        'mikrotik_id' => $secret['.id'] ?? null,
                        'password' => $secret['password'] ?? null,
                        'service' => $secret['service'] ?? null,
                        'profile' => $secret['profile'] ?? null,
                        'local_address' => $secret['local-address'] ?? null,
                        'remote_address' => $secret['remote-address'] ?? null,
                        'disabled' => $secret['disabled'] ?? null,
                        'comment' => $secret['comment'] ?? null,
                        'raw_json' => json_encode($secret, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        'last_synced_at' => now(),
                    ]
                );

                $count++;
            }

            $router->update([
                'last_test_status' => 'success',
                'last_test_message' => "Berhasil sync {$count} PPPoE Secret.",
                'last_test_at' => now(),
            ]);

            return back()->with('success', "Berhasil mengambil {$count} PPPoE Secret dari Mikrotik.");
        } catch (Throwable $e) {
            $router->update([
                'last_test_status' => 'failed',
                'last_test_message' => $e->getMessage(),
                'last_test_at' => now(),
            ]);

            return back()->with('error', 'Gagal mengambil PPPoE Secret: '.$e->getMessage());
        }
    }


    public function mikrotikSyncActiveSessions(MikrotikRouter $router)
    {
        $this->ensureAdmin();

        try {
            $sessions = (new MikrotikApiClient($router))->pppoeActiveSessions();

            $activeNames = [];

            MikrotikPppoeActiveSession::query()
                ->where('mikrotik_router_id', $router->id)
                ->delete();

            foreach ($sessions as $session) {
                $name = $session['name'] ?? null;

                if (!$name) {
                    continue;
                }

                $activeNames[] = $name;

                MikrotikPppoeActiveSession::query()->create([
                    'mikrotik_router_id' => $router->id,
                    'mikrotik_id' => $session['.id'] ?? null,
                    'name' => $name,
                    'service' => $session['service'] ?? null,
                    'caller_id' => $session['caller-id'] ?? null,
                    'address' => $session['address'] ?? null,
                    'uptime' => $session['uptime'] ?? null,
                    'encoding' => $session['encoding'] ?? null,
                    'raw_json' => json_encode($session, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'last_seen_at' => now(),
                ]);
            }

            Customer::query()
                ->where('mikrotik_router_id', $router->id)
                ->whereNotNull('pppoe_username')
                ->update([
                    'pppoe_online_status' => 'Offline',
                ]);

            $onlineCount = 0;

            foreach ($sessions as $session) {
                $name = $session['name'] ?? null;

                if (!$name) {
                    continue;
                }

                $updated = Customer::query()
                    ->where('mikrotik_router_id', $router->id)
                    ->where('pppoe_username', $name)
                    ->update([
                        'pppoe_online_status' => 'Online',
                        'pppoe_online_at' => now(),
                        'pppoe_last_seen_at' => now(),
                        'pppoe_remote_address' => $session['address'] ?? null,
                        'pppoe_caller_id' => $session['caller-id'] ?? null,
                        'pppoe_uptime' => $session['uptime'] ?? null,
                    ]);

                $onlineCount += $updated;
            }

            $router->update([
                'last_test_status' => 'success',
                'last_test_message' => "Active PPPoE: ".count($sessions)." sesi. Pelanggan cocok: {$onlineCount}.",
                'last_test_at' => now(),
            ]);

            return back()->with('success', "Berhasil membaca ".count($sessions)." PPPoE active session. Pelanggan online cocok: {$onlineCount}.");
        } catch (Throwable $e) {
            $router->update([
                'last_test_status' => 'failed',
                'last_test_message' => $e->getMessage(),
                'last_test_at' => now(),
            ]);

            return back()->with('error', 'Gagal membaca PPPoE active: '.$e->getMessage());
        }
    }



    // MACSERVICE MIKROTIK SUBMENU METHODS START
    public function mikrotikProfiles()
    {
        $this->ensureAdmin();

        $routers = MikrotikRouter::query()->orderByDesc('status')->orderBy('name')->get();

        return view('admin.settings.mikrotik-profiles', compact('routers'));
    }

    public function mikrotikSecrets()
    {
        $this->ensureAdmin();

        $routers = MikrotikRouter::query()->orderByDesc('status')->orderBy('name')->get();

        return view('admin.settings.mikrotik-secrets', compact('routers'));
    }

    public function mikrotikActiveSessions()
    {
        $this->ensureAdmin();

        $routers = MikrotikRouter::query()->orderByDesc('status')->orderBy('name')->get();

        return view('admin.settings.mikrotik-active-sessions', compact('routers'));
    }
    // MACSERVICE MIKROTIK SUBMENU METHODS END

    public function olt()
    {
        $this->ensureAdmin();

        return view('admin.settings.olt');
    }
}
