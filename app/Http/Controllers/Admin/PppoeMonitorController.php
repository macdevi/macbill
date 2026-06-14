<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\MikrotikRouter;
use App\Models\MikrotikPppoeSecret;
use App\Models\MikrotikPppoeActiveSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use App\Services\SettingService;
use App\Models\MikrotikPppoeProfile;

class PppoeMonitorController extends Controller
{
    private function ensureAdmin(): void
    {
        abort_unless(auth()->check() && auth()->user()->role === 'admin', 403);
    }

    public function index(Request $request)
    {
        $this->ensureAdmin();

        $search = trim((string) $request->query('search', ''));
        $status = trim((string) $request->query('status', ''));
        $routerId = trim((string) $request->query('router_id', ''));

        $base = Customer::query()
            ->with(['mikrotikRouter', 'mikrotikPppoeProfile'])
            ->whereNotNull('pppoe_username')
            ->where('pppoe_username', '!=', '');

        $totalPppoe = (clone $base)->count();
        $online = (clone $base)->where('pppoe_online_status', 'Online')->count();
        $offline = (clone $base)->where('pppoe_online_status', 'Offline')->count();
        $unknown = (clone $base)->where(function ($q) {
            $q->whereNull('pppoe_online_status')
                ->orWhere('pppoe_online_status', '')
                ->orWhere('pppoe_online_status', 'Unknown');
        })->count();

        $customers = $base
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%")
                        ->orWhere('pppoe_username', 'like', "%{$search}%")
                        ->orWhere('pppoe_remote_address', 'like', "%{$search}%")
                        ->orWhere('pppoe_caller_id', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', function ($query) use ($status) {
                if ($status === 'Unknown') {
                    $query->where(function ($q) {
                        $q->whereNull('pppoe_online_status')
                            ->orWhere('pppoe_online_status', '')
                            ->orWhere('pppoe_online_status', 'Unknown');
                    });
                } else {
                    $query->where('pppoe_online_status', $status);
                }
            })
            ->when($routerId !== '', function ($query) use ($routerId) {
                $query->where('mikrotik_router_id', $routerId);
            })
            ->orderByRaw("CASE pppoe_online_status WHEN 'Online' THEN 1 WHEN 'Offline' THEN 2 ELSE 3 END")
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        $routers = MikrotikRouter::query()
            ->orderByDesc('status')
            ->orderBy('name')
            ->get();

        return view('admin.monitoring.pppoe', compact(
            'customers',
            'routers',
            'search',
            'status',
            'routerId',
            'totalPppoe',
            'online',
            'offline',
            'unknown'
        ));
    }

    public function smartLinkPppoe(Request $request)
    {
        $this->ensureAdmin();

        try {
            if (! array_key_exists('macbilling:pppoe-smart-link', \Illuminate\Support\Facades\Artisan::all())) {
                return back()->with('error', 'Command macbilling:pppoe-smart-link belum terdaftar.');
            }

            \Illuminate\Support\Facades\Artisan::call('macbilling:pppoe-smart-link', [
                '--apply' => true,
            ]);

            $output = trim(\Illuminate\Support\Facades\Artisan::output());

            return back()
                ->with('success', 'Smart Match PPPoE selesai.')
                ->with('command_output', $output ?: 'Selesai tanpa output.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Smart Match gagal: '.$e->getMessage());
        }
    }

    public function reconcile(Request $request)
    {
        $this->ensureAdmin();

        $search = trim((string) $request->query('search', ''));

        $customers = Customer::query()
            ->with(['mikrotikRouter'])
            ->whereNotNull('pppoe_username')
            ->where('pppoe_username', '!=', '')
            ->get();

        $customerKeys = $customers
            ->mapWithKeys(function ($customer) {
                $routerId = $customer->mikrotik_router_id ?: 0;
                $username = trim((string) $customer->pppoe_username);

                return [$routerId.'|'.$username => $customer];
            });

        $activeSessions = MikrotikPppoeActiveSession::query()
            ->with('router')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%")
                        ->orWhere('caller_id', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->get();

        $secrets = MikrotikPppoeSecret::query()
            ->with('router')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('profile', 'like', "%{$search}%")
                        ->orWhere('comment', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->get();

        $activeMatched = collect();
        $activeUnmatched = collect();

        foreach ($activeSessions as $session) {
            $key = ($session->mikrotik_router_id ?: 0).'|'.trim((string) $session->name);

            if ($customerKeys->has($key)) {
                $session->matched_customer = $customerKeys->get($key);
                $activeMatched->push($session);
            } else {
                $activeUnmatched->push($session);
            }
        }

        $secretMatched = collect();
        $secretUnmatched = collect();

        foreach ($secrets as $secret) {
            $key = ($secret->mikrotik_router_id ?: 0).'|'.trim((string) $secret->name);

            if ($customerKeys->has($key)) {
                $secret->matched_customer = $customerKeys->get($key);
                $secretMatched->push($secret);
            } else {
                $secretUnmatched->push($secret);
            }
        }

        $summary = [
            'customers' => $customers->count(),
            'active_total' => $activeSessions->count(),
            'active_matched' => $activeMatched->count(),
            'active_unmatched' => $activeUnmatched->count(),
            'secret_total' => $secrets->count(),
            'secret_matched' => $secretMatched->count(),
            'secret_unmatched' => $secretUnmatched->count(),
        ];

        return view('admin.monitoring.pppoe-reconcile', compact(
            'search',
            'summary',
            'activeMatched',
            'activeUnmatched',
            'secretMatched',
            'secretUnmatched'
        ));
    }



    public function importSecretToCustomer(Request $request, MikrotikPppoeSecret $secret)
    {
        $this->ensureAdmin();

        $existing = Customer::query()
            ->where('mikrotik_router_id', $secret->mikrotik_router_id)
            ->where('pppoe_username', $secret->name)
            ->first();

        if ($existing) {
            return redirect('/admin/customers/'.$existing->id.'/detail')
                ->with('success', 'Secret ini sudah terhubung ke pelanggan: '.$existing->name);
        }

        $profile = null;

        if ($secret->profile) {
            $profile = MikrotikPppoeProfile::query()
                ->where('mikrotik_router_id', $secret->mikrotik_router_id)
                ->where('name', $secret->profile)
                ->first();
        }

        $active = MikrotikPppoeActiveSession::query()
            ->where('mikrotik_router_id', $secret->mikrotik_router_id)
            ->where('name', $secret->name)
            ->first();

        $package = $this->resolvePackageFromSecret($secret);

        $customer = new Customer();

        $data = [
            'name' => $this->guessCustomerNameFromSecret($secret),
            'phone' => '',
            'address' => $secret->comment ?: 'Import dari PPPoE Secret Mikrotik',
            'package_id' => $package?->id,
            'billing_day' => (int) SettingService::get('default_billing_day', 1),
            'monthly_price' => 0,
            'status' => 'active',

            'mikrotik_router_id' => $secret->mikrotik_router_id,
            'mikrotik_pppoe_profile_id' => $profile?->id,
            'mikrotik_pppoe_secret_id' => $secret->id,
            'pppoe_username' => $secret->name,
            'pppoe_password' => (string) $secret->password,
            'mikrotik_sync_status' => 'Tersinkron',
            'mikrotik_synced_at' => now(),
            'mikrotik_sync_message' => 'Diimport dari PPPoE Secret Mikrotik.',

            'pppoe_online_status' => $active ? 'Online' : 'Offline',
            'pppoe_online_at' => $active ? now() : null,
            'pppoe_last_seen_at' => $active?->last_seen_at,
            'pppoe_remote_address' => $active?->address,
            'pppoe_caller_id' => $active?->caller_id,
            'pppoe_uptime' => $active?->uptime,
        ];

        foreach ($data as $key => $value) {
            if (Schema::hasColumn('customers', $key)) {
                $customer->{$key} = $value;
            }
        }

        $customer->save();

        return redirect('/admin/customers/'.$customer->id.'/detail')
            ->with('success', 'PPPoE Secret berhasil diimport menjadi pelanggan. Lengkapi ODP, port, paket, alamat, dan harga bulanan jika diperlukan.');
    }

    private function resolvePackageFromSecret(MikrotikPppoeSecret $secret): ?object
    {
        if (!class_exists(\App\Models\Package::class) || !Schema::hasTable('packages')) {
            return null;
        }

        $profileName = trim((string) $secret->profile);

        if ($profileName !== '') {
            $existing = \App\Models\Package::query()
                ->where('name', $profileName)
                ->first();

            if ($existing) {
                return $existing;
            }

            try {
                $package = new \App\Models\Package();

                if (Schema::hasColumn('packages', 'name')) {
                    $package->name = $profileName;
                }

                if (Schema::hasColumn('packages', 'speed')) {
                    $package->speed = $profileName;
                }

                if (Schema::hasColumn('packages', 'status')) {
                    $package->status = 'active';
                }

                if (Schema::hasColumn('packages', 'price')) {
                    $package->price = 0;
                }

                if (Schema::hasColumn('packages', 'monthly_price')) {
                    $package->monthly_price = 0;
                }

                $package->save();

                return $package;
            } catch (\Throwable $e) {
                return \App\Models\Package::query()->first();
            }
        }

        return \App\Models\Package::query()->first();
    }

    private function guessCustomerNameFromSecret(MikrotikPppoeSecret $secret): string
    {
        $name = trim((string) $secret->comment);

        if ($name !== '' && !str_contains($name, 'MAC-SERVICE')) {
            return mb_substr($name, 0, 150);
        }

        $name = trim((string) $secret->name);

        $name = preg_replace('/^\d+/', '', $name);
        $name = str_replace(['User-Client_', 'user-client_', 'USER-CLIENT_', 'Client_', 'client_'], '', $name);
        $name = str_replace(['_', '-'], ' ', $name);
        $name = trim(preg_replace('/\s+/', ' ', $name));

        if ($name === '') {
            $name = $secret->name;
        }

        return mb_substr(ucwords(strtolower($name)), 0, 150);
    }


}
