<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DatabaseBackupController extends Controller
{
    private function ensureAdmin(): void
    {
        abort_unless(auth()->check() && auth()->user()->role === 'admin', 403);
    }

    public function index()
    {
        $this->ensureAdmin();

        $backupDir = storage_path('app/backups');

        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0775, true);
        }

        $files = collect(File::files($backupDir))
            ->filter(function ($file) {
                return str_ends_with($file->getFilename(), '.sqlite');
            })
            ->map(function ($file) {
                return [
                    'name' => $file->getFilename(),
                    'size' => $this->humanSize($file->getSize()),
                    'bytes' => $file->getSize(),
                    'modified_at' => date('d/m/Y H:i:s', $file->getMTime()),
                ];
            })
            ->sortByDesc('name')
            ->values();

        $dbPath = database_path('database.sqlite');

        $dbInfo = [
            'exists' => File::exists($dbPath),
            'path' => $dbPath,
            'size' => File::exists($dbPath) ? $this->humanSize(File::size($dbPath)) : '-',
            'modified_at' => File::exists($dbPath) ? date('d/m/Y H:i:s', File::lastModified($dbPath)) : '-',
        ];

        return view('admin.system.backups', compact('files', 'dbInfo'));
    }

    public function create()
    {
        $this->ensureAdmin();

        $dbPath = database_path('database.sqlite');

        if (!File::exists($dbPath)) {
            return back()->with('error', 'Database SQLite tidak ditemukan: '.$dbPath);
        }

        $backupDir = storage_path('app/backups');

        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0775, true);
        }

        $name = 'macbilling-db-'.now()->format('Ymd-His').'.sqlite';
        $target = $backupDir.'/'.$name;

        File::copy($dbPath, $target);

        return back()->with('success', 'Backup database berhasil dibuat: '.$name);
    }

    public function download(string $file)
    {
        $this->ensureAdmin();

        $file = basename($file);

        abort_if(!Str::startsWith($file, 'macbilling-db-'), 404);
        abort_if(!str_ends_with($file, '.sqlite'), 404);

        $path = storage_path('app/backups/'.$file);

        abort_unless(File::exists($path), 404);

        return response()->download($path, $file, [
            'Content-Type' => 'application/octet-stream',
        ]);
    }

    public function destroy(string $file)
    {
        $this->ensureAdmin();

        $file = basename($file);

        abort_if(!Str::startsWith($file, 'macbilling-db-'), 404);
        abort_if(!str_ends_with($file, '.sqlite'), 404);

        $path = storage_path('app/backups/'.$file);

        if (File::exists($path)) {
            File::delete($path);
        }

        return back()->with('success', 'Backup berhasil dihapus.');
    }

    private function humanSize(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2, ',', '.').' GB';
        }

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2, ',', '.').' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2, ',', '.').' KB';
        }

        return $bytes.' B';
    }
}
