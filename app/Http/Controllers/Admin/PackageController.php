<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function index()
    {
        $packages = Package::latest()->paginate(20);

        return view('admin.packages.index', compact('packages'));
    }

    public function create()
    {
        $package = new Package();

        return view('admin.packages.form', compact('package'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'speed' => ['nullable', 'string', 'max:80'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        Package::create($data);

        return redirect('/admin/packages')->with('success', 'Paket berhasil ditambahkan.');
    }

    public function edit(Package $package)
    {
        return view('admin.packages.form', compact('package'));
    }

    public function update(Request $request, Package $package)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'speed' => ['nullable', 'string', 'max:80'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $package->update($data);

        return redirect('/admin/packages')->with('success', 'Paket berhasil diperbarui.');
    }

    public function destroy(Package $package)
    {
        $package->delete();

        return redirect('/admin/packages')->with('success', 'Paket berhasil dihapus.');
    }
}
