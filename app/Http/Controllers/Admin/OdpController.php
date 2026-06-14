<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Odp;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OdpController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->input('q'));

        $odps = Odp::query()
            ->withCount('customers')
            ->when($q, function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('location', 'like', "%{$q}%");
            })
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('admin.odps.index', compact('odps', 'q'));
    }

    public function map()
    {
        $odps = Odp::query()
            ->with(['customers' => function ($query) {
                $query->with('package')->orderBy('name');
            }])
            ->withCount('customers')
            ->orderBy('name')
            ->get();

        $customers = Customer::query()
            ->with(['package', 'odpMaster'])
            ->orderBy('name')
            ->get();

        return view('admin.odps.map', compact('odps', 'customers'));
    }

    public function create()
    {
        $odp = new Odp([
            'port_count' => 8,
            'latitude' => -8.209567,
            'longitude' => 112.658531,
            'status' => 'active',
        ]);

        return view('admin.odps.form', compact('odp'));
    }

    public function store(Request $request)
    {
        Odp::create($this->validated($request));

        return redirect('/admin/odps')->with('success', 'ODP berhasil ditambahkan.');
    }

    public function edit(Odp $odp)
    {
        return view('admin.odps.form', compact('odp'));
    }

    public function update(Request $request, Odp $odp)
    {
        $odp->update($this->validated($request, $odp));

        return redirect('/admin/odps')->with('success', 'ODP berhasil diperbarui.');
    }

    public function destroy(Odp $odp)
    {
        if ($odp->customers()->count() > 0) {
            return redirect('/admin/odps')->with('error', 'ODP tidak bisa dihapus karena masih dipakai pelanggan.');
        }

        $odp->delete();

        return redirect('/admin/odps')->with('success', 'ODP berhasil dihapus.');
    }

    public function ports(Odp $odp)
    {
        $odp->load(['customers' => fn ($q) => $q->orderBy('port_number')->orderBy('name')]);

        $customers = Customer::query()
            ->where(function ($query) use ($odp) {
                $query->whereNull('odp_id')
                    ->orWhere('odp_id', $odp->id);
            })
            ->orderBy('name')
            ->get();

        $assigned = $odp->customers()
            ->whereNotNull('port_number')
            ->pluck('id', 'port_number')
            ->toArray();

        return view('admin.odps.ports', compact('odp', 'customers', 'assigned'));
    }

    public function updatePorts(Request $request, Odp $odp)
    {
        $ports = (array) $request->input('ports', []);
        $usedCustomers = [];
        $updated = 0;

        foreach ($ports as $port => $customerId) {
            $port = (int) $port;
            $customerId = (int) $customerId;

            if ($port < 1 || $port > (int) $odp->port_count) {
                continue;
            }

            if ($customerId <= 0) {
                Customer::where('odp_id', $odp->id)
                    ->where('port_number', $port)
                    ->update([
                        'port_number' => null,
                    ]);

                continue;
            }

            if (in_array($customerId, $usedCustomers, true)) {
                continue;
            }

            $customer = Customer::find($customerId);

            if (!$customer) {
                continue;
            }

            Customer::where('odp_id', $odp->id)
                ->where('port_number', $port)
                ->where('id', '!=', $customer->id)
                ->update([
                    'port_number' => null,
                ]);

            Customer::where('id', '!=', $customer->id)
                ->where('odp_id', $odp->id)
                ->where('port_number', $port)
                ->update([
                    'port_number' => null,
                ]);

            $customer->update([
                'odp_id' => $odp->id,
                'odp' => $odp->name,
                'port_number' => $port,
            ]);

            $usedCustomers[] = $customerId;
            $updated++;
        }

        return redirect('/admin/odps/' . $odp->id . '/ports')
            ->with('success', 'Port ODP berhasil diperbarui. Update: ' . $updated . ' pelanggan.');
    }

    private function validated(Request $request, ?Odp $odp = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('odps', 'name')->ignore($odp?->id),
            ],
            'location' => ['nullable', 'string', 'max:180'],
            'port_count' => ['required', 'integer', 'min:1', 'max:128'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'status' => ['required', 'in:active,inactive'],
        ]);
    }
}
