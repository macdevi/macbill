<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\MikrotikPppoeActiveSession;
use App\Models\MikrotikPppoeProfile;
use App\Models\MikrotikPppoeSecret;
use App\Models\MikrotikRouter;
use App\Services\MikrotikApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class MikrotikController extends Controller
{
    private function ensureAdmin(): void
    {
        $role = strtolower((string) (auth()->user()->role ?? ''));
        abort_unless($role === 'admin', 403);
    }

    public function index()
    {
        $this->ensureAdmin();

        return redirect('/admin/mikrotik/integrasi');
    }

    public function integration(Request $request)
    {
        $this->ensureAdmin();

        $routers = MikrotikRouter::query()
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $editRouter = null;

        if ($request->filled('edit')) {
            $editRouter = MikrotikRouter::query()->find($request->input('edit'));
        }

        if (!$editRouter && !$request->boolean('new')) {
            $editRouter = MikrotikRouter::query()->latest('id')->first();
        }

        return view('admin.mikrotik.integration', compact('routers', 'editRouter'));
    }

    public function storeIntegration(Request $request)
    {
        $this->ensureAdmin();

        $payload = $this->routerPayload($request, true);

        MikrotikRouter::query()->create($payload);

        return redirect('/admin/mikrotik/integrasi')->with('success', 'Integrasi Mikrotik berhasil ditambahkan.');
    }

    public function updateIntegration(Request $request, MikrotikRouter $router)
    {
        $this->ensureAdmin();

        $payload = $this->routerPayload($request, false);

        if (empty($payload['api_password'])) {
            unset($payload['api_password']);
        }

        $router->forceFill($payload)->save();

        return redirect('/admin/mikrotik/integrasi?edit='.$router->id)->with('success', 'Integrasi Mikrotik berhasil diperbarui.');
    }

    public function destroyIntegration(MikrotikRouter $router)
    {
        $this->ensureAdmin();

        try {
            if (Schema::hasTable('customers')) {
                $update = [];

                foreach (['mikrotik_router_id', 'mikrotik_pppoe_profile_id', 'mikrotik_pppoe_secret_id'] as $column) {
                    if (Schema::hasColumn('customers', $column)) {
                        $update[$column] = null;
                    }
                }

                if ($update) {
                    Customer::query()
                        ->where('mikrotik_router_id', $router->id)
                        ->update($update);
                }
            }

            $router->delete();

            return redirect('/admin/mikrotik/integrasi')->with('success', 'Integrasi Mikrotik berhasil dihapus. Data pelanggan tidak dihapus.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal hapus integrasi Mikrotik: '.$e->getMessage());
        }
    }

    public function testConnection(MikrotikRouter $router)
    {
        $this->ensureAdmin();

        try {
            $client = new MikrotikApiClient($router);

            $client->test();

            $activeCount = 0;

            try {
                $activeCount = count($client->pppoeActiveSessions());
            } catch (\Throwable $e) {
                $activeCount = 0;
            }

            $message = 'PPPoE Active: '.$activeCount;

            $this->safeUpdateRouter($router, [
                'status' => 'active',
                'last_test_at' => now(),
                'last_test_status' => 'success',
                'last_test_message' => $message,
            ]);

            return back()->with('success', $message);
        } catch (\Throwable $e) {
            $this->safeUpdateRouter($router, [
                'last_test_at' => now(),
                'last_test_status' => 'failed',
                'last_test_message' => 'Gagal koneksi',
            ]);

            return back()->with('error', 'Test koneksi gagal: '.$e->getMessage());
        }
    }

    public function pppoeActive(Request $request)
    {
        $this->ensureAdmin();

        $routers = MikrotikRouter::query()->orderBy('name')->get();
        $routerId = $request->input('router_id');

        $sessions = MikrotikPppoeActiveSession::query()
            ->with('router')
            ->when($routerId, fn ($q) => $q->where('mikrotik_router_id', $routerId))
            ->orderByRaw('LOWER(name) ASC')
            ->orderBy('id', 'ASC')
            ->get();

        $linkedCustomersByUsername = Customer::query()
            ->whereNotNull('pppoe_username')
            ->where('pppoe_username', '!=', '')
            ->get(['id', 'name', 'pppoe_username', 'mikrotik_sync_status'])
            ->keyBy(fn ($customer) => mb_strtolower(trim((string) $customer->pppoe_username)));

        return view('admin.mikrotik.pppoe-active', compact('routers', 'routerId', 'sessions', 'linkedCustomersByUsername'));
    }

    public function refreshPppoeActive(Request $request)
    {
        $this->ensureAdmin();

        $routers = $this->selectedRouters($request);

        if ($routers->isEmpty()) {
            return back()->with('error', 'Belum ada router Mikrotik untuk direfresh.');
        }

        $total = 0;
        $errors = [];

        foreach ($routers as $router) {
            try {
                $sessions = (new MikrotikApiClient($router))->pppoeActiveSessions();

                foreach ($sessions as $session) {
                    $name = $session['name'] ?? null;

                    if (!$name) {
                        continue;
                    }

                    $payload = [
                        'mikrotik_router_id' => $router->id,
                        'mikrotik_id' => $session['.id'] ?? null,
                        'name' => $name,
                        'service' => $session['service'] ?? 'pppoe',
                        'caller_id' => $session['caller-id'] ?? null,
                        'address' => $session['address'] ?? null,
                        'uptime' => $session['uptime'] ?? null,
                        'encoding' => $session['encoding'] ?? null,
                        'session_id' => $session['session-id'] ?? null,
                        'raw_json' => json_encode($session, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        'last_seen_at' => now(),
                    ];

                    MikrotikPppoeActiveSession::query()->updateOrCreate(
                        [
                            'mikrotik_router_id' => $router->id,
                            'name' => $name,
                        ],
                        $this->onlyExistingColumns('mikrotik_pppoe_active_sessions', $payload)
                    );

                    $total++;
                }

                $this->safeUpdateRouter($router, [
                    'last_test_at' => now(),
                    'last_test_status' => 'success',
                    'last_test_message' => 'PPPoE Active: '.count($sessions),
                ]);
            } catch (\Throwable $e) {
                $errors[] = $router->name.': '.$e->getMessage();

                $this->safeUpdateRouter($router, [
                    'last_test_at' => now(),
                    'last_test_status' => 'failed',
                    'last_test_message' => 'Gagal refresh',
                ]);
            }
        }

        if ($errors) {
            return back()->with('error', 'Sebagian refresh gagal: '.implode(' | ', array_slice($errors, 0, 3)));
        }

        return back()->with('success', 'Refresh PPPoE active selesai. Data masuk: '.$total.'.');
    }

    public function pppoeSecrets(Request $request)
    {
        $this->ensureAdmin();

        $routers = MikrotikRouter::query()->orderBy('name')->get();
        $routerId = $request->input('router_id');

        $secrets = MikrotikPppoeSecret::query()
            ->with('router')
            ->when($routerId, fn ($q) => $q->where('mikrotik_router_id', $routerId))
            ->orderByRaw('LOWER(name) ASC')
            ->orderBy('id', 'ASC')
            ->get();

        $linkedCustomersBySecretId = Customer::query()
            ->whereNotNull('mikrotik_pppoe_secret_id')
            ->get(['id', 'name', 'pppoe_username', 'mikrotik_sync_status', 'mikrotik_pppoe_secret_id'])
            ->keyBy('mikrotik_pppoe_secret_id');

        $linkedCustomersByUsername = Customer::query()
            ->whereNotNull('pppoe_username')
            ->where('pppoe_username', '!=', '')
            ->get(['id', 'name', 'pppoe_username', 'mikrotik_sync_status', 'mikrotik_pppoe_secret_id'])
            ->keyBy(fn ($customer) => mb_strtolower(trim((string) $customer->pppoe_username)));

        return view('admin.mikrotik.pppoe-secrets', compact('routers', 'routerId', 'secrets', 'linkedCustomersBySecretId', 'linkedCustomersByUsername'));
    }

    public function syncPppoeSecrets(Request $request)
    {
        $this->ensureAdmin();

        $routers = $this->selectedRouters($request);

        if ($routers->isEmpty()) {
            return back()->with('error', 'Belum ada router Mikrotik untuk sync secret.');
        }

        $total = 0;
        $errors = [];

        foreach ($routers as $router) {
            try {
                $secrets = (new MikrotikApiClient($router))->pppoeSecrets();

                foreach ($secrets as $secret) {
                    $name = $secret['name'] ?? null;

                    if (!$name) {
                        continue;
                    }

                    $payload = [
                        'mikrotik_router_id' => $router->id,
                        'mikrotik_id' => $secret['.id'] ?? null,
                        'name' => $name,
                        'password' => $secret['password'] ?? null,
                        'service' => $secret['service'] ?? null,
                        'profile' => $secret['profile'] ?? null,
                        'local_address' => $secret['local-address'] ?? null,
                        'remote_address' => $secret['remote-address'] ?? null,
                        'disabled' => $secret['disabled'] ?? null,
                        'comment' => $secret['comment'] ?? null,
                        'raw_json' => json_encode($secret, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        'last_synced_at' => now(),
                    ];

                    MikrotikPppoeSecret::query()->updateOrCreate(
                        [
                            'mikrotik_router_id' => $router->id,
                            'name' => $name,
                        ],
                        $this->onlyExistingColumns('mikrotik_pppoe_secrets', $payload)
                    );

                    $total++;
                }

                $this->safeUpdateRouter($router, [
                    'last_test_at' => now(),
                    'last_test_status' => 'success',
                    'last_test_message' => 'PPPoE Secret: '.count($secrets),
                ]);
            } catch (\Throwable $e) {
                $errors[] = $router->name.': '.$e->getMessage();

                $this->safeUpdateRouter($router, [
                    'last_test_at' => now(),
                    'last_test_status' => 'failed',
                    'last_test_message' => 'Gagal sync secret',
                ]);
            }
        }

        if ($errors) {
            return back()->with('error', 'Sebagian sync secret gagal: '.implode(' | ', array_slice($errors, 0, 3)));
        }

        return back()->with('success', 'Sync PPPoE Secret selesai. Data masuk: '.$total.'.');
    }

    public function pppoeProfiles(Request $request)
    {
        $this->ensureAdmin();

        $routers = MikrotikRouter::query()->orderBy('name')->get();
        $routerId = $request->input('router_id');

        $profiles = MikrotikPppoeProfile::query()
            ->with('router')
            ->when($routerId, fn ($q) => $q->where('mikrotik_router_id', $routerId))
            ->orderByRaw('LOWER(name) ASC')
            ->paginate(50)
            ->withQueryString();

        $packagesByName = collect();

        if (Schema::hasTable('packages')) {
            $packagesByName = \Illuminate\Support\Facades\DB::table('packages')
                ->get()
                ->keyBy(fn ($package) => mb_strtolower(trim((string) $package->name)));
        }

        return view('admin.mikrotik.pppoe-profiles', compact('routers', 'routerId', 'profiles', 'packagesByName'));
    }

    public function syncPppoeProfiles(Request $request)
    {
        $this->ensureAdmin();

        $routers = $this->selectedRouters($request);

        if ($routers->isEmpty()) {
            return back()->with('error', 'Belum ada router Mikrotik untuk sync profile.');
        }

        $total = 0;
        $packageCreated = 0;
        $packageUpdated = 0;
        $packageSkipped = 0;
        $errors = [];

        foreach ($routers as $router) {
            try {
                $profiles = (new MikrotikApiClient($router))->pppoeProfiles();

                foreach ($profiles as $profile) {
                    $name = trim((string) ($profile['name'] ?? ''));

                    if ($name === '') {
                        continue;
                    }

                    $payload = [
                        'mikrotik_router_id' => $router->id,
                        'mikrotik_id' => $profile['.id'] ?? null,
                        'name' => $name,
                        'local_address' => $profile['local-address'] ?? null,
                        'remote_address' => $profile['remote-address'] ?? null,
                        'rate_limit' => $profile['rate-limit'] ?? null,
                        'only_one' => $profile['only-one'] ?? null,
                        'raw_json' => json_encode($profile, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        'last_synced_at' => now(),
                    ];

                    $profileModel = MikrotikPppoeProfile::query()->updateOrCreate(
                        [
                            'mikrotik_router_id' => $router->id,
                            'name' => $name,
                        ],
                        $this->onlyExistingColumns('mikrotik_pppoe_profiles', $payload)
                    );

                    $packageResult = $this->syncPppoeProfileToPackage($profileModel);

                    if ($packageResult === 'created') {
                        $packageCreated++;
                    } elseif ($packageResult === 'updated') {
                        $packageUpdated++;
                    } else {
                        $packageSkipped++;
                    }

                    $total++;
                }

                $this->safeUpdateRouter($router, [
                    'last_test_at' => now(),
                    'last_test_status' => 'success',
                    'last_test_message' => 'PPPoE Profile: '.count($profiles),
                ]);
            } catch (\Throwable $e) {
                $errors[] = $router->name.': '.$e->getMessage();

                $this->safeUpdateRouter($router, [
                    'last_test_at' => now(),
                    'last_test_status' => 'failed',
                    'last_test_message' => 'Gagal sync profile',
                ]);
            }
        }

        if ($errors) {
            return back()->with('error', 'Sebagian sync profile gagal: '.implode(' | ', array_slice($errors, 0, 3)));
        }

        return back()->with(
            'success',
            'Sync PPPoE Profile selesai. Profile: '.$total.
            '. Paket dibuat: '.$packageCreated.
            '. Paket diupdate: '.$packageUpdated.
            '. Diskip: '.$packageSkipped.'.'
        );
    }

    private function routerPayload(Request $request, bool $requirePassword): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:100'],
            'host' => ['required', 'string', 'max:150'],
            'api_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'username' => ['required', 'string', 'max:100'],
            'api_password' => [$requirePassword ? 'required' : 'nullable', 'string', 'max:255'],
            'use_ssl' => ['nullable'],
            'status' => ['nullable', 'string', 'max:30'],
        ];

        $data = $request->validate($rules);

        $payload = [
            'name' => $data['name'],
            'host' => $data['host'],
            'api_port' => $data['api_port'] ?: 8728,
            'username' => $data['username'],
            'api_password' => $data['api_password'] ?? null,
            'use_ssl' => $request->boolean('use_ssl'),
            'status' => $data['status'] ?? 'active',
        ];

        return $this->onlyExistingColumns('mikrotik_routers', $payload);
    }


    public function linkPppoeActiveToCustomer(MikrotikPppoeActiveSession $session)
    {
        $this->ensureAdmin();

        $username = trim((string) $session->name);

        if ($username === '') {
            return back()->with('error', 'Nama PPPoE Active kosong. Tidak bisa ditautkan.');
        }

        $customer = $this->findCustomerForActiveSession($session);

        if (!$customer) {
            $clean = $this->cleanPppoeName($username);

            return back()->with('error', 'Pelanggan tidak ditemukan untuk PPPoE '.$username.'. Nama hasil baca: '.$clean.'. Pastikan pelanggan sudah ada di Billing.');
        }

        $usernameKey = mb_strtolower($username);

        $duplicate = Customer::query()
            ->whereNotNull('pppoe_username')
            ->where('pppoe_username', '!=', '')
            ->whereRaw('LOWER(TRIM(pppoe_username)) = ?', [$usernameKey])
            ->where('id', '!=', $customer->id)
            ->first();

        if ($duplicate) {
            return back()->with('error', 'PPPoE '.$username.' sudah tertaut ke pelanggan lain: '.$duplicate->name.'.');
        }

        $secret = MikrotikPppoeSecret::query()
            ->where('mikrotik_router_id', $session->mikrotik_router_id)
            ->whereRaw('LOWER(TRIM(name)) = ?', [$usernameKey])
            ->first();

        $profile = null;

        if ($secret?->profile) {
            $profile = MikrotikPppoeProfile::query()
                ->where('mikrotik_router_id', $session->mikrotik_router_id)
                ->where('name', $secret->profile)
                ->first();
        }

        $payload = [
            'mikrotik_router_id' => $session->mikrotik_router_id,
            'mikrotik_pppoe_profile_id' => $profile?->id,
            'mikrotik_pppoe_secret_id' => $secret?->id,
            'pppoe_username' => $username,
            'pppoe_password' => $secret ? (string) $secret->password : $customer->pppoe_password,
            'mikrotik_sync_status' => 'Tersinkron',
            'mikrotik_synced_at' => now(),
            'mikrotik_sync_message' => 'Ditautkan dari PPPoE Active.',
            'pppoe_online_status' => 'Online',
            'pppoe_online_at' => now(),
            'pppoe_last_seen_at' => $session->last_seen_at ?: now(),
            'pppoe_remote_address' => $session->address,
            'pppoe_caller_id' => $session->caller_id,
            'pppoe_uptime' => $session->uptime,
        ];

        $customer->forceFill($this->onlyExistingColumns('customers', $payload))->save();

        return back()->with('success', 'PPPoE '.$username.' berhasil ditautkan ke pelanggan '.$customer->name.'. Status otomatis Tersinkron.');
    }

    private function findCustomerForActiveSession(MikrotikPppoeActiveSession $session): ?Customer
    {
        $username = trim((string) $session->name);
        $usernameKey = mb_strtolower($username);

        if ($username === '') {
            return null;
        }

        $exact = Customer::query()
            ->whereNotNull('pppoe_username')
            ->where('pppoe_username', '!=', '')
            ->whereRaw('LOWER(TRIM(pppoe_username)) = ?', [$usernameKey])
            ->first();

        if ($exact) {
            return $exact;
        }

        if (preg_match('/^0*([1-9][0-9]*)/', $username, $m)) {
            $id = (int) $m[1];

            $byId = Customer::query()->find($id);

            if ($byId) {
                return $byId;
            }
        }

        $clean = $this->normalizePppoeName($this->cleanPppoeName($username));

        if ($clean === '' || mb_strlen($clean) < 3) {
            return null;
        }

        $customers = Customer::query()
            ->select(['id', 'name', 'pppoe_username'])
            ->orderBy('id')
            ->get();

        foreach ($customers as $customer) {
            $name = $this->normalizePppoeName((string) $customer->name);

            if ($name === $clean) {
                return $customer;
            }
        }

        foreach ($customers as $customer) {
            $name = $this->normalizePppoeName((string) $customer->name);

            if ($name !== '' && (str_contains($name, $clean) || str_contains($clean, $name))) {
                return $customer;
            }
        }

        return null;
    }

    private function cleanPppoeName(string $username): string
    {
        $name = trim($username);

        $name = preg_replace('/^[0-9]+/u', '', $name);
        $name = preg_replace('/user\s*[-_ ]?\s*client/iu', '', $name);
        $name = str_replace(['_', '-', '.', '/'], ' ', $name);
        $name = preg_replace('/\s+/u', ' ', $name);

        return trim($name);
    }

    private function normalizePppoeName(string $name): string
    {
        $name = mb_strtolower($name);
        $name = preg_replace('/[^a-z0-9]+/u', ' ', $name);
        $name = preg_replace('/\s+/u', ' ', $name);

        return trim($name);
    }



    public function linkPppoeSecretToCustomer(MikrotikPppoeSecret $secret)
    {
        $this->ensureAdmin();

        $username = trim((string) $secret->name);

        if ($username === '') {
            return back()->with('error', 'Nama PPPoE Secret kosong. Tidak bisa ditautkan.');
        }

        $customer = $this->findCustomerForPppoeName($username);

        if (!$customer) {
            $clean = $this->cleanPppoeName($username);

            return back()->with('error', 'Pelanggan tidak ditemukan untuk PPPoE Secret '.$username.'. Nama hasil baca: '.$clean.'.');
        }

        $usernameKey = mb_strtolower($username);

        $duplicate = Customer::query()
            ->whereNotNull('pppoe_username')
            ->where('pppoe_username', '!=', '')
            ->whereRaw('LOWER(TRIM(pppoe_username)) = ?', [$usernameKey])
            ->where('id', '!=', $customer->id)
            ->first();

        if ($duplicate) {
            return back()->with('error', 'PPPoE '.$username.' sudah tertaut ke pelanggan lain: '.$duplicate->name.'.');
        }

        $profile = null;

        if ($secret->profile) {
            $profile = MikrotikPppoeProfile::query()
                ->where('mikrotik_router_id', $secret->mikrotik_router_id)
                ->where('name', $secret->profile)
                ->first();
        }

        $payload = [
            'mikrotik_router_id' => $secret->mikrotik_router_id,
            'mikrotik_pppoe_profile_id' => $profile?->id,
            'mikrotik_pppoe_secret_id' => $secret->id,
            'pppoe_username' => $username,
            'pppoe_password' => (string) ($secret->password ?? ''),
            'mikrotik_sync_status' => 'Tersinkron',
            'mikrotik_synced_at' => now(),
            'mikrotik_sync_message' => 'Ditautkan dari PPPoE Secret.',
            'pppoe_remote_address' => $secret->remote_address,
        ];

        $customer->forceFill($this->onlyExistingColumns('customers', $payload))->saveQuietly();

        $fresh = Customer::query()->find($customer->id);

        if (!$fresh || trim((string) $fresh->pppoe_username) !== $username) {
            return back()->with('error', 'Gagal menyimpan PPPoE Secret ke pelanggan. Cek field pppoe_username di tabel customers.');
        }

        return back()->with('success', 'PPPoE Secret '.$username.' berhasil ditautkan ke pelanggan '.$customer->name.'. Username, password, profile, dan status Tersinkron sudah disimpan.');
    }

    private function findCustomerForPppoeName(string $username): ?Customer
    {
        $username = trim($username);
        $usernameKey = mb_strtolower($username);

        if ($username === '') {
            return null;
        }

        $exact = Customer::query()
            ->whereNotNull('pppoe_username')
            ->where('pppoe_username', '!=', '')
            ->whereRaw('LOWER(TRIM(pppoe_username)) = ?', [$usernameKey])
            ->first();

        if ($exact) {
            return $exact;
        }

        if (preg_match('/^0*([1-9][0-9]*)/', $username, $m)) {
            $id = (int) $m[1];

            $byId = Customer::query()->find($id);

            if ($byId) {
                return $byId;
            }
        }

        $clean = $this->normalizePppoeName($this->cleanPppoeName($username));

        if ($clean === '' || mb_strlen($clean) < 3) {
            return null;
        }

        $customers = Customer::query()
            ->select(['id', 'name', 'pppoe_username'])
            ->orderBy('id')
            ->get();

        foreach ($customers as $customer) {
            $name = $this->normalizePppoeName((string) $customer->name);

            if ($name === $clean) {
                return $customer;
            }
        }

        foreach ($customers as $customer) {
            $name = $this->normalizePppoeName((string) $customer->name);

            if ($name !== '' && (str_contains($name, $clean) || str_contains($clean, $name))) {
                return $customer;
            }
        }

        return null;
    }




    public function autoLinkPppoeSecrets(Request $request)
    {
        $this->ensureAdmin();

        $routerId = $request->input('router_id');

        $secrets = MikrotikPppoeSecret::query()
            ->when($routerId, fn ($q) => $q->where('mikrotik_router_id', $routerId))
            ->orderByRaw('LOWER(name) ASC')
            ->get();

        $linked = 0;
        $already = 0;
        $skipped = 0;
        $duplicate = 0;
        $ambiguous = 0;
        $mismatch = 0;
        $examples = [];

        foreach ($secrets as $secret) {
            $username = trim((string) $secret->name);

            if ($username === '') {
                $skipped++;
                continue;
            }

            $usernameKey = mb_strtolower($username);

            $existingLinked = Customer::query()
                ->where(function ($q) use ($secret, $usernameKey) {
                    $q->where('mikrotik_pppoe_secret_id', $secret->id)
                        ->orWhereRaw('LOWER(TRIM(pppoe_username)) = ?', [$usernameKey]);
                })
                ->first();

            if ($existingLinked) {
                $already++;
                continue;
            }

            [$customer, $score, $secondScore, $reason] = $this->bestAutoCustomerForPppoeSecret($secret);

            if (!$customer) {
                $skipped++;
                $examples[] = $username.' => skip: tidak ada kandidat kuat';
                continue;
            }

            if ($score < 115) {
                $skipped++;
                $examples[] = $username.' => skip: skor tidak cukup aman '.$score;
                continue;
            }

            if ($secondScore !== null && ($score - $secondScore) < 25) {
                $ambiguous++;
                $examples[] = $username.' => skip: ambigu skor '.$score.' vs '.$secondScore;
                continue;
            }

            $usedByOther = Customer::query()
                ->whereNotNull('pppoe_username')
                ->where('pppoe_username', '!=', '')
                ->whereRaw('LOWER(TRIM(pppoe_username)) = ?', [$usernameKey])
                ->where('id', '!=', $customer->id)
                ->first();

            if ($usedByOther) {
                $duplicate++;
                $examples[] = $username.' => skip: sudah dipakai '.$usedByOther->name;
                continue;
            }

            $currentCustomerUsername = trim((string) $customer->pppoe_username);

            if ($currentCustomerUsername !== '' && mb_strtolower($currentCustomerUsername) !== $usernameKey) {
                $mismatch++;
                $examples[] = $username.' => skip: '.$customer->name.' sudah punya PPPoE '.$currentCustomerUsername;
                continue;
            }

            $profile = null;

            if ($secret->profile) {
                $profile = MikrotikPppoeProfile::query()
                    ->where('mikrotik_router_id', $secret->mikrotik_router_id)
                    ->where('name', $secret->profile)
                    ->first();
            }

            $payload = [
                'mikrotik_router_id' => $secret->mikrotik_router_id,
                'mikrotik_pppoe_profile_id' => $profile?->id,
                'mikrotik_pppoe_secret_id' => $secret->id,
                'pppoe_username' => $username,
                'pppoe_password' => (string) ($secret->password ?? ''),
                'mikrotik_sync_status' => 'Tersinkron',
                'mikrotik_synced_at' => now(),
                'mikrotik_sync_message' => 'Auto sync dari PPPoE Secret. Skor nama: '.$score.'. '.$reason,
                'pppoe_remote_address' => $secret->remote_address,
            ];

            $customer->forceFill($this->onlyExistingColumns('customers', $payload))->saveQuietly();

            $linked++;
            $examples[] = $username.' => tertaut ke '.$customer->name.' skor '.$score;
        }

        $message = 'Auto sync selesai. Tertaut: '.$linked.
            '. Sudah tertaut: '.$already.
            '. Skip: '.$skipped.
            '. Ambigu: '.$ambiguous.
            '. Duplikat: '.$duplicate.
            '. Beda username: '.$mismatch.'.';

        if ($examples) {
            $message .= ' Contoh: '.implode(' | ', array_slice($examples, 0, 6));
        }

        return back()->with($linked > 0 ? 'success' : 'error', $message);
    }

    private function bestAutoCustomerForPppoeSecret(MikrotikPppoeSecret $secret): array
    {
        $secretRaw = trim((string) $secret->name);
        $cleanSecret = $this->normalizePppoeName($this->cleanPppoeName($secretRaw));

        if ($cleanSecret === '' || mb_strlen($cleanSecret) < 3) {
            return [null, 0, null, 'Nama PPPoE Secret tidak cukup jelas'];
        }

        $secretTokens = $this->autoLinkNameTokens($cleanSecret);
        $isSingleTokenSecret = count($secretTokens) === 1;

        $customers = Customer::query()
            ->select(['id', 'name', 'pppoe_username', 'mikrotik_sync_status'])
            ->orderBy('name')
            ->get();

        $rows = [];

        foreach ($customers as $customer) {
            $customerName = $this->normalizePppoeName((string) $customer->name);

            if ($customerName === '' || mb_strlen($customerName) < 3) {
                continue;
            }

            $customerTokens = $this->autoLinkNameTokens($customerName);

            if (!$customerTokens) {
                continue;
            }

            if ($this->autoLinkQualifierMismatch($secretTokens, $customerTokens)) {
                continue;
            }

            $score = 0;
            $reason = '';

            if ($customerName === $cleanSecret) {
                $score = $isSingleTokenSecret ? 135 : 140;
                $reason = $isSingleTokenSecret
                    ? 'Nama satu kata sama persis'
                    : 'Nama sama persis';
            } elseif (!$isSingleTokenSecret) {
                $missingTokens = array_values(array_diff($secretTokens, $customerTokens));
                $extraTokens = array_values(array_diff($customerTokens, $secretTokens));

                if (count($missingTokens) === 0) {
                    $score = 125 - min(count($extraTokens) * 5, 20);
                    $reason = 'Semua kata PPPoE ada di nama pelanggan';
                } else {
                    $max = max(strlen($cleanSecret), strlen($customerName));
                    $distance = levenshtein(substr($cleanSecret, 0, 80), substr($customerName, 0, 80));
                    $similarity = max(0, 1 - ($distance / max(1, $max)));

                    if ($similarity >= 0.94) {
                        $score = (int) round($similarity * 120);
                        $reason = 'Kemiripan karakter sangat tinggi';
                    }
                }
            }

            if ($score > 0) {
                $rows[] = [
                    'customer' => $customer,
                    'score' => $score,
                    'reason' => $reason,
                ];
            }
        }

        usort($rows, function ($a, $b) {
            if ($a['score'] === $b['score']) {
                return strcmp((string) $a['customer']->name, (string) $b['customer']->name);
            }

            return $b['score'] <=> $a['score'];
        });

        if (!$rows) {
            return [null, 0, null, 'Tidak ada kandidat yang aman'];
        }

        $first = $rows[0];
        $second = $rows[1] ?? null;

        return [
            $first['customer'],
            (int) $first['score'],
            $second ? (int) $second['score'] : null,
            $first['reason'].' | Nama terbaca: '.$cleanSecret,
        ];
    }

    public function showPppoeSecretLinkCustomers(Request $request, MikrotikPppoeSecret $secret)
    {
        $this->ensureAdmin();

        $baseKeyword = $this->cleanPppoeName((string) $secret->name);
        $keyword = trim((string) $request->input('q', $baseKeyword));

        $candidates = $this->candidateCustomersForPppoeSecret($secret, $keyword);

        return view('admin.mikrotik.pppoe-secret-link', compact('secret', 'keyword', 'baseKeyword', 'candidates'));
    }

    public function linkPppoeSecretToSelectedCustomer(MikrotikPppoeSecret $secret, Customer $customer)
    {
        $this->ensureAdmin();

        $username = trim((string) $secret->name);

        if ($username === '') {
            return back()->with('error', 'Nama PPPoE Secret kosong. Tidak bisa ditautkan.');
        }

        $usernameKey = mb_strtolower($username);

        $duplicate = Customer::query()
            ->whereNotNull('pppoe_username')
            ->where('pppoe_username', '!=', '')
            ->whereRaw('LOWER(TRIM(pppoe_username)) = ?', [$usernameKey])
            ->where('id', '!=', $customer->id)
            ->first();

        if ($duplicate) {
            return back()->with('error', 'PPPoE '.$username.' sudah tertaut ke pelanggan lain: '.$duplicate->name.'.');
        }

        $profile = null;

        if ($secret->profile) {
            $profile = MikrotikPppoeProfile::query()
                ->where('mikrotik_router_id', $secret->mikrotik_router_id)
                ->where('name', $secret->profile)
                ->first();
        }

        $payload = [
            'mikrotik_router_id' => $secret->mikrotik_router_id,
            'mikrotik_pppoe_profile_id' => $profile?->id,
            'mikrotik_pppoe_secret_id' => $secret->id,
            'pppoe_username' => $username,
            'pppoe_password' => (string) ($secret->password ?? ''),
            'mikrotik_sync_status' => 'Tersinkron',
            'mikrotik_synced_at' => now(),
            'mikrotik_sync_message' => 'Ditautkan dari PPPoE Secret melalui pilihan pelanggan.',
            'pppoe_remote_address' => $secret->remote_address,
        ];

        $customer->forceFill($this->onlyExistingColumns('customers', $payload))->saveQuietly();

        return redirect('/admin/mikrotik/pppoe-secret')
            ->with('success', 'PPPoE Secret '.$username.' berhasil ditautkan ke pelanggan '.$customer->name.'. Username, password, profile, dan status Tersinkron sudah disimpan.');
    }

    private function candidateCustomersForPppoeSecret(MikrotikPppoeSecret $secret, string $keyword): array
    {
        $pppoeName = trim((string) $secret->name);
        $cleanSecret = $this->normalizePppoeName($this->cleanPppoeName($pppoeName));
        $cleanKeyword = $this->normalizePppoeName($keyword);
        $idFromSecret = null;

        if (preg_match('/^0*([1-9][0-9]*)/', $pppoeName, $m)) {
            $idFromSecret = (int) $m[1];
        }

        $customers = Customer::query()
            ->select(['id', 'name', 'pppoe_username', 'mikrotik_sync_status'])
            ->orderBy('name')
            ->get();

        $rows = [];

        foreach ($customers as $customer) {
            $customerName = $this->normalizePppoeName((string) $customer->name);
            $customerUsername = $this->normalizePppoeName((string) $customer->pppoe_username);
            $score = 0;

            if ($idFromSecret && (int) $customer->id === $idFromSecret) {
                $score += 120;
            }

            if ($customerUsername !== '' && $customerUsername === $this->normalizePppoeName($pppoeName)) {
                $score += 130;
            }

            foreach ([$cleanSecret, $cleanKeyword] as $needle) {
                if ($needle === '') {
                    continue;
                }

                if ($customerName === $needle) {
                    $score += 100;
                } elseif (str_contains($customerName, $needle)) {
                    $score += 75;
                } elseif (str_contains($needle, $customerName) && mb_strlen($customerName) >= 3) {
                    $score += 65;
                }

                $secretTokens = array_filter(explode(' ', $needle));
                $nameTokens = array_filter(explode(' ', $customerName));
                $intersect = array_intersect($secretTokens, $nameTokens);

                $score += count($intersect) * 18;

                if ($customerName !== '' && mb_strlen($needle) >= 3) {
                    $max = max(mb_strlen($needle), mb_strlen($customerName));
                    $distance = levenshtein(substr($needle, 0, 80), substr($customerName, 0, 80));
                    $similarity = max(0, 1 - ($distance / max(1, $max)));
                    $score += (int) round($similarity * 35);
                }
            }

            if ($cleanKeyword === '' || $score >= 20 || ($idFromSecret && (int) $customer->id === $idFromSecret)) {
                $rows[] = [
                    'customer' => $customer,
                    'score' => $score,
                ];
            }
        }

        usort($rows, function ($a, $b) {
            if ($a['score'] === $b['score']) {
                return strcmp((string) $a['customer']->name, (string) $b['customer']->name);
            }

            return $b['score'] <=> $a['score'];
        });

        return array_slice($rows, 0, 50);
    }



    private function syncPppoeProfileToPackage(MikrotikPppoeProfile $profile): string
    {
        if (!Schema::hasTable('packages')) {
            return 'skipped';
        }

        $name = trim((string) $profile->name);
        $nameKey = mb_strtolower($name);
        $rateLimit = trim((string) ($profile->rate_limit ?? ''));

        if ($name === '') {
            return 'skipped';
        }

        $skipNames = [
            'default',
            'default-encryption',
            'nunggak',
            'isolir',
            'suspend',
            'disabled',
        ];

        if (in_array($nameKey, $skipNames, true)) {
            return 'skipped';
        }

        if ($rateLimit === '') {
            return 'skipped';
        }

        $existing = \Illuminate\Support\Facades\DB::table('packages')
            ->whereRaw('LOWER(TRIM(name)) = ?', [$nameKey])
            ->first();

        if ($existing) {
            $update = [];

            if (Schema::hasColumn('packages', 'speed')) {
                $update['speed'] = $rateLimit;
            }

            if (Schema::hasColumn('packages', 'updated_at')) {
                $update['updated_at'] = now();
            }

            if ($update) {
                \Illuminate\Support\Facades\DB::table('packages')
                    ->where('id', $existing->id)
                    ->update($update);
            }

            return 'updated';
        }

        $insert = [
            'name' => $name,
        ];

        if (Schema::hasColumn('packages', 'speed')) {
            $insert['speed'] = $rateLimit;
        }

        if (Schema::hasColumn('packages', 'status')) {
            $insert['status'] = 'active';
        }

        if (Schema::hasColumn('packages', 'created_at')) {
            $insert['created_at'] = now();
        }

        if (Schema::hasColumn('packages', 'updated_at')) {
            $insert['updated_at'] = now();
        }

        \Illuminate\Support\Facades\DB::table('packages')->insert($insert);

        return 'created';
    }



    private function autoLinkNameTokens(string $name): array
    {
        $name = $this->normalizePppoeName($name);
        $tokens = preg_split('/\s+/', $name) ?: [];

        $noise = [
            'user',
            'client',
            'server',
            'pelanggan',
            'customer',
            'pak',
            'bpk',
            'bu',
            'ibu',
            'mr',
            'mrs',
        ];

        $tokens = array_values(array_filter($tokens, function ($token) use ($noise) {
            if ($token === '') {
                return false;
            }

            if (in_array($token, $noise, true)) {
                return false;
            }

            return true;
        }));

        return array_values(array_unique($tokens));
    }

    private function autoLinkQualifierMismatch(array $secretTokens, array $customerTokens): bool
    {
        $qualifiers = [
            'lor',
            'kidul',
            'kulon',
            'wetan',
            'barat',
            'timur',
            'utara',
            'selatan',
            'tengah',
            'atas',
            'bawah',
            'depan',
            'belakang',
            'kiri',
            'kanan',
            'baru',
            'lama',
            'satu',
            'dua',
            'tiga',
            '1',
            '2',
            '3',
            'i',
            'ii',
            'iii',
        ];

        $secretQualifiers = array_values(array_intersect($secretTokens, $qualifiers));
        $customerQualifiers = array_values(array_intersect($customerTokens, $qualifiers));

        sort($secretQualifiers);
        sort($customerQualifiers);

        if ($secretQualifiers || $customerQualifiers) {
            return $secretQualifiers !== $customerQualifiers;
        }

        return false;
    }


    private function selectedRouters(Request $request)
    {
        $routerId = $request->input('router_id');

        return MikrotikRouter::query()
            ->when($routerId, fn ($q) => $q->where('id', $routerId))
            ->orderBy('name')
            ->get();
    }

    private function safeUpdateRouter(MikrotikRouter $router, array $data): void
    {
        $router->forceFill($this->onlyExistingColumns('mikrotik_routers', $data))->save();
    }

    private function onlyExistingColumns(string $table, array $data): array
    {
        return collect($data)
            ->filter(fn ($value, $column) => Schema::hasColumn($table, $column))
            ->all();
    }


    public function pppoeOffline(\Illuminate\Http\Request $request)
    {
        $this->ensureAdmin();

        $routers = \App\Models\MikrotikRouter::query()->orderBy('name')->get();
        $routerId = $request->input('router_id');

        $rows = \App\Models\Customer::query()
            ->with(['mikrotikRouter', 'mikrotikPppoeProfile'])
            ->when($routerId, fn ($q) => $q->where('mikrotik_router_id', $routerId))
            ->whereNotNull('pppoe_username')
            ->where('pppoe_username', '!=', '')
            ->where(function ($q) {
                $q->whereNull('pppoe_online_status')
                    ->orWhere('pppoe_online_status', 'Offline')
                    ->orWhere('pppoe_online_status', 'Unknown');
            })
            ->orderByRaw('LOWER(name) ASC')
            ->get();

        return view('admin.mikrotik.pppoe-offline', compact('routers', 'routerId', 'rows'));
    }

}
