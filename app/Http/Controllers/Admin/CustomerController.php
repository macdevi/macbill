<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Odp;
use App\Models\MikrotikRouter;
use App\Models\MikrotikPppoeProfile;
use App\Models\MikrotikPppoeSecret;
use App\Services\MikrotikApiClient;
use App\Models\Package;
use App\Models\Payment;
use Illuminate\Http\Request;
use Throwable;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->input('q'));

        $customers = Customer::with(['package', 'odpMaster'])
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%")
                        ->orWhere('address', 'like', "%{$q}%")
                        ->orWhere('odp', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(1000)
            ->withQueryString();

        return view('admin.customers.index', compact('customers', 'q'));
    }

    public function create()
    {
        $customer = new Customer([
            'billing_day' => 1,
            'monthly_price' => 0,
            'status' => 'active',
        ]);

        $packages = Package::where('status', 'active')->orderBy('name')->get();
        $odps = Odp::where('status', 'active')->orderBy('name')->get();

                $mikrotikRouters = MikrotikRouter::query()->where('status', 'active')->orderBy('name')->get();
        $pppoeProfiles = MikrotikPppoeProfile::query()->with('router')->orderBy('name')->get();

return view('admin.customers.form', compact('customer', 'packages', 'odps', 'mikrotikRouters', 'pppoeProfiles'));
    }

    public function store(Request $request)
    {
        Customer::create($this->payload($request));

        return redirect('/admin/customers')->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    public function detail(Customer $customer)
    {
        $customer->load(['package', 'odpMaster']);

        $invoices = Invoice::where('customer_id', $customer->id)
            ->latest('due_date')
            ->limit(20)
            ->get();

        $payments = Payment::where('customer_id', $customer->id)
            ->latest('paid_at')
            ->limit(20)
            ->get();

        return view('admin.customers.detail', compact('customer', 'invoices', 'payments'));
    }

    public function edit(Customer $customer)
    {
        $packages = Package::where('status', 'active')->orderBy('name')->get();
        $odps = Odp::where('status', 'active')->orderBy('name')->get();

                $mikrotikRouters = MikrotikRouter::query()->where('status', 'active')->orderBy('name')->get();
        $pppoeProfiles = MikrotikPppoeProfile::query()->with('router')->orderBy('name')->get();

return view('admin.customers.form', compact('customer', 'packages', 'odps', 'mikrotikRouters', 'pppoeProfiles'));
    }

    public function update(Request $request, Customer $customer)
    {
        $customer->update($this->payload($request, $customer));

        return redirect('/admin/customers')->with('success', 'Pelanggan berhasil diperbarui.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect('/admin/customers')->with('success', 'Pelanggan berhasil dihapus.');
    }

    public function importForm()
    {
        return view('admin.customers.import');
    }

    public function template()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Template Pelanggan');

        $headers = [
            'name',
            'phone',
            'address',
            'odp',
            'port_number',
            'package_name',
            'package_speed',
            'billing_day',
            'monthly_price',
            'status',
        ];

        $example = [
            'Contoh Pelanggan',
            '08123456789',
            'Alamat pelanggan',
            'ODP-01',
            1,
            '10 Mbps',
            'Up to 10 Mbps',
            15,
            100000,
            'active',
        ];

        $sheet->fromArray($headers, null, 'A1');
        $sheet->fromArray($example, null, 'A2');

        $sheet->getStyle('A1:J1')->getFont()->setBold(true);
        $sheet->getStyle('B:B')->getNumberFormat()->setFormatCode('@');
        $sheet->getStyle('I:I')->getNumberFormat()->setFormatCode('#,##0');

        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $this->downloadSpreadsheet($spreadsheet, 'template_import_pelanggan.xlsx');
    }

    public function export()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Data Pelanggan');

        $headers = [
            'id',
            'name',
            'phone',
            'address',
            'odp',
            'port_number',
            'package_name',
            'package_speed',
            'billing_day',
            'monthly_price',
            'status',
        ];

        $sheet->fromArray($headers, null, 'A1');

        $row = 2;

        Customer::with(['package', 'odpMaster'])->orderBy('id')->chunk(200, function ($customers) use ($sheet, &$row) {
            foreach ($customers as $customer) {
                $sheet->fromArray([
                    $customer->id,
                    $customer->name,
                    $customer->phone,
                    $customer->address,
                    $customer->odpMaster?->name ?: $customer->odp,
                    $customer->port_number,
                    $customer->package?->name,
                    $customer->package?->speed,
                    $customer->billing_day,
                    $customer->monthly_price,
                    $customer->status,
                ], null, 'A' . $row);

                $row++;
            }
        });

        $sheet->getStyle('A1:K1')->getFont()->setBold(true);
        $sheet->getStyle('C:C')->getNumberFormat()->setFormatCode('@');
        $sheet->getStyle('J:J')->getNumberFormat()->setFormatCode('#,##0');

        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $this->downloadSpreadsheet($spreadsheet, 'export_pelanggan_' . now()->format('Ymd_His') . '.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls'],
        ]);

        $spreadsheet = IOFactory::load($request->file('file')->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();

        $highestRow = $sheet->getHighestDataRow();
        $highestColumn = Coordinate::columnIndexFromString($sheet->getHighestDataColumn());

        if ($highestRow < 2) {
            return back()->with('error', 'File XLSX tidak memiliki data pelanggan.');
        }

        $headers = [];

        for ($col = 1; $col <= $highestColumn; $col++) {
            $headers[$col] = $this->normalizeHeader($sheet->getCell(Coordinate::stringFromColumnIndex($col) . '1')->getValue());
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        for ($row = 2; $row <= $highestRow; $row++) {
            try {
                $data = [];

                for ($col = 1; $col <= $highestColumn; $col++) {
                    $key = $headers[$col] ?? null;

                    if ($key) {
                        $data[$key] = $this->cellValue($sheet->getCell(Coordinate::stringFromColumnIndex($col) . $row)->getValue());
                    }
                }

                $name = trim((string) ($data['name'] ?? $data['nama'] ?? $data['nama_pelanggan'] ?? ''));
                $phone = trim((string) ($data['phone'] ?? $data['no_hp'] ?? $data['whatsapp'] ?? ''));
                $address = trim((string) ($data['address'] ?? $data['alamat'] ?? ''));
                $odpName = trim((string) ($data['odp'] ?? $data['kode_odp'] ?? $data['nama_odp'] ?? ''));
                $portNumber = (int) ($data['port_number'] ?? $data['port'] ?? $data['nomor_port'] ?? 0);
                $packageName = trim((string) ($data['package_name'] ?? $data['paket'] ?? $data['nama_paket'] ?? ''));
                $packageSpeed = trim((string) ($data['package_speed'] ?? $data['speed'] ?? ''));
                $billingDay = (int) ($data['billing_day'] ?? $data['tanggal_tagihan'] ?? 1);
                $monthlyPrice = $this->moneyToInt($data['monthly_price'] ?? $data['harga_bulanan'] ?? $data['nominal'] ?? 0);
                $status = $this->normalizeStatus($data['status'] ?? 'active');

                if ($name === '') {
                    $skipped++;
                    continue;
                }

                $billingDay = max(1, min(31, $billingDay ?: 1));

                $packageId = null;

                if ($packageName !== '') {
                    $package = Package::firstOrCreate(
                        ['name' => $packageName],
                        ['speed' => $packageSpeed, 'status' => 'active']
                    );

                    if ($packageSpeed !== '' && $package->speed !== $packageSpeed) {
                        $package->update(['speed' => $packageSpeed]);
                    }

                    $packageId = $package->id;
                }

                $odpId = null;

                if ($odpName !== '') {
                    $odp = Odp::firstOrCreate(
                        ['name' => $odpName],
                        [
                            'location' => null,
                            'port_count' => max(8, $portNumber ?: 8),
                            'status' => 'active',
                        ]
                    );

                    if ($portNumber > 0 && (int) $odp->port_count < $portNumber) {
                        $odp->update(['port_count' => $portNumber]);
                    }

                    $odpId = $odp->id;
                }

                $payload = [
                    'name' => $name,
                    'phone' => $phone ?: null,
                    'address' => $address ?: null,
                    'odp_id' => $odpId,
                    'odp' => $odpName ?: null,
                    'port_number' => $portNumber > 0 ? $portNumber : null,
                    'package_id' => $packageId,
                    'billing_day' => $billingDay,
                    'monthly_price' => $monthlyPrice,
                    'status' => $status,
                ];

                $customer = null;

                if ($phone !== '') {
                    $customer = Customer::where('phone', $phone)->first();
                }

                if (!$customer) {
                    $customer = Customer::where('name', $name)->first();
                }

                if ($odpId && $portNumber > 0) {
                    $usedPort = Customer::query()
                        ->where('odp_id', $odpId)
                        ->where('port_number', $portNumber);

                    if ($customer) {
                        $usedPort->where('id', '!=', $customer->id);
                    }

                    if ($usedPort->exists()) {
                        $skipped++;
                        $errors[] = 'Baris ' . $row . ': Port ' . $portNumber . ' pada ODP ' . $odpName . ' sudah dipakai pelanggan lain.';
                        continue;
                    }
                }

                if ($customer) {
                    $customer->update($payload);
                    $updated++;
                } else {
                    Customer::create($payload);
                    $created++;
                }
            } catch (\Throwable $e) {
                $skipped++;
                $errors[] = 'Baris ' . $row . ': ' . $e->getMessage();
            }
        }

        $message = "Import XLSX selesai. Baru: {$created}, update: {$updated}, skip: {$skipped}.";

        if ($errors) {
            $message .= ' Catatan: ' . implode(' | ', array_slice($errors, 0, 5));
        }

        return redirect('/admin/customers')->with($created || $updated ? 'success' : 'error', $message);
    }

    private function payload(Request $request, ?Customer $customer = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:140'],
            'phone' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'string'],
            'odp_id' => ['nullable', 'exists:odps,id'],
            'port_number' => ['nullable', 'integer', 'min:1', 'max:128'],
            'package_id' => ['nullable', 'exists:packages,id'],
            'billing_day' => ['required', 'integer', 'min:1', 'max:31'],
            'monthly_price' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
            'mikrotik_router_id' => ['nullable', 'exists:mikrotik_routers,id'],
            'mikrotik_pppoe_profile_id' => ['nullable', 'exists:mikrotik_pppoe_profiles,id'],
            'mikrotik_pppoe_secret_id' => ['nullable', 'exists:mikrotik_pppoe_secrets,id'],
            'pppoe_username' => ['nullable', 'string', 'max:150'],
            'pppoe_password' => ['nullable', 'string', 'max:255'],
            'mikrotik_sync_status' => ['nullable', 'string', 'max:50'],
            'mikrotik_sync_message' => ['nullable', 'string', 'max:1000'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'cable_path_json' => ['nullable', 'string'],
            'cable_distance_m' => ['nullable', 'integer', 'min:0', 'max:99999999'],
        ]);

        foreach (['name', 'phone', 'pppoe_username'] as $key) {
            if (array_key_exists($key, $data) && is_string($data[$key])) {
                $data[$key] = trim($data[$key]);
            }
        }

        foreach (['latitude', 'longitude', 'cable_path_json', 'cable_distance_m'] as $key) {
            if (array_key_exists($key, $data) && $data[$key] === '') {
                $data[$key] = null;
            }
        }

        if (empty($data['latitude']) || empty($data['longitude'])) {
            $data['latitude'] = null;
            $data['longitude'] = null;
            $data['cable_path_json'] = null;
            $data['cable_distance_m'] = null;
        }

        foreach (['mikrotik_router_id', 'mikrotik_pppoe_profile_id', 'mikrotik_pppoe_secret_id', 'pppoe_username', 'pppoe_password', 'mikrotik_sync_status', 'mikrotik_sync_message'] as $key) {
            if (array_key_exists($key, $data) && $data[$key] === '') {
                $data[$key] = null;
            }
        }

        if (empty($data['mikrotik_sync_status'])) {
            $data['mikrotik_sync_status'] = 'Belum Sync';
        }

        if (($customer ?? null) && empty($data['pppoe_password'])) {
            unset($data['pppoe_password']);
        }

        if (empty($data['mikrotik_router_id'])) {
            $data['mikrotik_pppoe_profile_id'] = null;
            $data['mikrotik_pppoe_secret_id'] = null;
            $data['pppoe_username'] = null;
            $data['pppoe_password'] = null;
            $data['mikrotik_sync_status'] = 'Belum Sync';
            $data['mikrotik_sync_message'] = null;
        }

        if (!empty($data['mikrotik_router_id']) && !empty($data['pppoe_username'])) {
            $usernameKey = strtolower(trim((string) $data['pppoe_username']));
            $routerId = (int) $data['mikrotik_router_id'];

            $usedUsername = Customer::query()
                ->whereRaw('LOWER(TRIM(pppoe_username)) = ?', [$usernameKey])
                ->where(function ($query) use ($routerId) {
                    $query->where('mikrotik_router_id', $routerId)
                        ->orWhereNull('mikrotik_router_id');
                });

            if ($customer && $customer->exists) {
                $usedUsername->where('id', '!=', $customer->id);
            }

            if ($usedUsername->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'pppoe_username' => 'Username PPPoE ini sudah dipakai pelanggan lain pada router yang sama.',
                ]);
            }
        }

        $odp = !empty($data['odp_id']) ? Odp::find($data['odp_id']) : null;

        if (!$odp) {
            $data['odp'] = null;
            $data['port_number'] = null;

            return $data;
        }

        $data['odp'] = $odp->name;

        if (empty($data['port_number'])) {
            $data['port_number'] = null;

            return $data;
        }

        $port = (int) $data['port_number'];

        if ($port > (int) $odp->port_count) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'port_number' => 'Nomor port melebihi jumlah port ODP.',
            ]);
        }

        $used = Customer::where('odp_id', $odp->id)
            ->where('port_number', $port);

        if ($customer && $customer->exists) {
            $used->where('id', '!=', $customer->id);
        }

        if ($used->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'port_number' => 'Port ini sudah dipakai pelanggan lain.',
            ]);
        }

        return $data;
    }

    private function normalizeHeader($value): string
    {
        $value = strtolower(trim((string) $value));
        $value = str_replace([' ', '-', '.', '/'], '_', $value);

        return $value;
    }

    private function cellValue($value): string
    {
        if (is_float($value) || is_int($value)) {
            return number_format((float) $value, 0, '', '');
        }

        return trim((string) $value);
    }

    private function moneyToInt($value): int
    {
        $value = preg_replace('/[^0-9]/', '', (string) $value);

        return (int) ($value ?: 0);
    }

    private function normalizeStatus($value): string
    {
        $value = strtolower(trim((string) $value));

        return in_array($value, ['inactive', 'nonaktif', 'non_aktif', 'non-aktif', '0', 'false'], true)
            ? 'inactive'
            : 'active';
    }

    private function downloadSpreadsheet(Spreadsheet $spreadsheet, string $filename)
    {
        $writer = new Xlsx($spreadsheet);
        $path = storage_path('app/' . $filename);

        $writer->save($path);

        return response()->download($path, $filename)->deleteFileAfterSend(true);
    }

    public function pppoeSecretSearch(Request $request)
    {
        abort_unless(auth()->check() && auth()->user()->role === 'admin', 403);

        $term = trim((string) $request->query('q', ''));
        $routerId = $request->query('router_id');

        $secrets = MikrotikPppoeSecret::query()
            ->with('router')
            ->when($routerId, function ($query) use ($routerId) {
                $query->where('mikrotik_router_id', $routerId);
            })
            ->when($term !== '', function ($query) use ($term) {
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'like', "%{$term}%")
                        ->orWhere('profile', 'like', "%{$term}%")
                        ->orWhere('comment', 'like', "%{$term}%")
                        ->orWhere('remote_address', 'like', "%{$term}%");
                });
            })
            ->orderBy('name')
            ->limit(20)
            ->get()
            ->map(function ($secret) {
                $profileId = null;

                if ($secret->profile) {
                    $profile = MikrotikPppoeProfile::query()
                        ->where('mikrotik_router_id', $secret->mikrotik_router_id)
                        ->where('name', $secret->profile)
                        ->first();

                    $profileId = $profile?->id;
                }

                return [
                    'id' => $secret->id,
                    'router_id' => $secret->mikrotik_router_id,
                    'router_name' => $secret->router?->name,
                    'name' => $secret->name,
                    'password' => $secret->password,
                    'profile' => $secret->profile,
                    'profile_id' => $profileId,
                    'remote_address' => $secret->remote_address,
                    'disabled' => $secret->disabled,
                    'comment' => $secret->comment,
                    'label' => trim($secret->name.' · '.($secret->profile ?: '-').' · '.($secret->router?->name ?: '-')),
                ];
            });

        return response()->json([
            'data' => $secrets,
        ]);
    }



    public function odpPorts(\App\Models\Odp $odp, Request $request)
    {
        abort_unless(auth()->check() && auth()->user()->role === 'admin', 403);

        $customerId = $request->query('customer_id');

        $portCount = (int) ($odp->port_count ?: 8);

        if ($portCount < 1) {
            $portCount = 8;
        }

        $usedPorts = Customer::query()
            ->where('odp_id', $odp->id)
            ->whereNotNull('port_number')
            ->when($customerId, function ($query) use ($customerId) {
                $query->where('id', '!=', $customerId);
            })
            ->pluck('port_number')
            ->map(fn ($port) => (string) $port)
            ->toArray();

        $ports = [];

        for ($i = 1; $i <= $portCount; $i++) {
            $port = (string) $i;

            $ports[] = [
                'value' => $port,
                'label' => 'Port '.$port,
                'used' => in_array($port, $usedPorts, true),
            ];
        }

        return response()->json([
            'odp' => [
                'id' => $odp->id,
                'name' => $odp->name,
                'port_count' => $portCount,
            ],
            'ports' => $ports,
        ]);
    }



    public function syncPppoeSecret(Customer $customer)
    {
        abort_unless(auth()->check() && auth()->user()->role === 'admin', 403);

        $router = $customer->mikrotikRouter;
        $profileName = $customer->mikrotikPppoeProfile?->name ?: $customer->mikrotikPppoeSecret?->profile;
        $username = trim((string) $customer->pppoe_username);
        $password = (string) $customer->pppoe_password;

        if (!$router) {
            return back()->with('error', 'Router Mikrotik belum dipilih pada data pelanggan.');
        }

        if ($username === '') {
            return back()->with('error', 'PPPoE Username / Secret Name belum diisi.');
        }

        if ($password === '') {
            return back()->with('error', 'PPPoE Password belum diisi.');
        }

        if (!$profileName) {
            return back()->with('error', 'PPPoE Profile belum dipilih.');
        }

        try {
            $comment = 'MAC-SERVICE Customer #'.$customer->id.' - '.$customer->name;

            $result = (new MikrotikApiClient($router))->syncPppoeSecret(
                $username,
                $password,
                $profileName,
                $comment
            );

            $secret = $result['secret'] ?? [];

            $localSecret = MikrotikPppoeSecret::query()->updateOrCreate(
                [
                    'mikrotik_router_id' => $router->id,
                    'name' => $username,
                ],
                [
                    'mikrotik_id' => $secret['.id'] ?? null,
                    'password' => $password,
                    'service' => $secret['service'] ?? 'pppoe',
                    'profile' => $secret['profile'] ?? $profileName,
                    'local_address' => $secret['local-address'] ?? null,
                    'remote_address' => $secret['remote-address'] ?? null,
                    'disabled' => $secret['disabled'] ?? 'false',
                    'comment' => $secret['comment'] ?? $comment,
                    'raw_json' => json_encode($secret, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'last_synced_at' => now(),
                ]
            );

            $customer->update([
                'mikrotik_pppoe_secret_id' => $localSecret->id,
                'mikrotik_sync_status' => 'Tersinkron',
                'mikrotik_synced_at' => now(),
                'mikrotik_sync_message' => ($result['action'] === 'created' ? 'Secret dibuat di Mikrotik.' : 'Secret diperbarui di Mikrotik.'),
            ]);

            return back()->with('success', 'PPPoE Secret berhasil disinkron ke Mikrotik.');
        } catch (Throwable $e) {
            $customer->update([
                'mikrotik_sync_status' => 'Gagal Sync',
                'mikrotik_synced_at' => now(),
                'mikrotik_sync_message' => $e->getMessage(),
            ]);

            return back()->with('error', 'Gagal sync PPPoE Secret: '.$e->getMessage());
        }
    }


}
