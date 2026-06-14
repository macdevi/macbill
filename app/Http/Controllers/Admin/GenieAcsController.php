<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class GenieAcsController extends Controller
{
    private function ensureAdmin(): void
    {
        abort_unless(auth()->check() && (auth()->user()->role ?? null) === 'admin', 403);
    }

    private function setting(string $key, ?string $default = null): ?string
    {
        if (!Schema::hasTable('settings')) {
            return $default;
        }

        $value = DB::table('settings')->where('key', $key)->value('value');

        if ($value === null || $value === '') {
            return $default;
        }

        return (string) $value;
    }

    private function saveSetting(string $key, ?string $value, string $type = 'text'): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        $now = now();

        $exists = DB::table('settings')->where('key', $key)->exists();

        if ($exists) {
            DB::table('settings')->where('key', $key)->update([
                'value' => $value,
                'group' => 'genieacs',
                'type' => $type,
                'updated_at' => $now,
            ]);
            return;
        }

        DB::table('settings')->insert([
            'key' => $key,
            'value' => $value,
            'group' => 'genieacs',
            'type' => $type,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function config(): array
    {
        return [
            'url' => rtrim((string) $this->setting('genieacs_url', ''), '/'),
            'username' => (string) $this->setting('genieacs_username', ''),
            'password' => (string) $this->setting('genieacs_password', ''),
            'timeout' => (int) $this->setting('genieacs_timeout', '8'),
        ];
    }

    private function client(array $config)
    {
        $timeout = max(3, min((int) ($config['timeout'] ?? 8), 60));

        $client = Http::timeout($timeout)->acceptJson();

        if (($config['username'] ?? '') !== '') {
            $client = $client->withBasicAuth($config['username'], $config['password'] ?? '');
        }

        return $client;
    }

    private function getPath($data, string $path, $default = '-')
    {
        $current = $data;

        foreach (explode('.', $path) as $part) {
            if (is_array($current) && array_key_exists($part, $current)) {
                $current = $current[$part];
                continue;
            }

            if (is_object($current) && isset($current->{$part})) {
                $current = $current->{$part};
                continue;
            }

            return $default;
        }

        if (is_array($current) || is_object($current)) {
            return $default;
        }

        return $current === null || $current === '' ? $default : $current;
    }

    private function fetchDevices(array $config): array
    {
        if (($config['url'] ?? '') === '') {
            return [
                'ok' => false,
                'message' => 'URL GenieACS belum diisi.',
                'devices' => [],
            ];
        }

        try {
            $url = rtrim($config['url'], '/') . '/devices/';
            $response = $this->client($config)->get($url, [
                'query' => '{}',
            ]);

            if (!$response->successful()) {
                return [
                    'ok' => false,
                    'message' => 'Gagal mengambil device. HTTP '.$response->status().' - '.$response->body(),
                    'devices' => [],
                ];
            }

            $json = $response->json();

            if (!is_array($json)) {
                return [
                    'ok' => false,
                    'message' => 'Respons GenieACS tidak berupa JSON array.',
                    'devices' => [],
                ];
            }

            return [
                'ok' => true,
                'message' => count($json).' device terbaca dari GenieACS.',
                'devices' => array_slice($json, 0, 100),
            ];
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'message' => $e->getMessage(),
                'devices' => [],
            ];
        }
    }

    public function index(Request $request)
    {
        $this->ensureAdmin();

        $config = $this->config();
        $devices = [];
        $result = null;

        if ($request->query('load') === 'devices') {
            $result = $this->fetchDevices($config);
            $devices = $result['devices'] ?? [];
        }

        $stat = [
            'url_filled' => $config['url'] !== '',
            'auth_filled' => $config['username'] !== '',
            'device_count' => count($devices),
        ];

        return view('admin.genieacs.index', [
            'config' => $config,
            'devices' => $devices,
            'result' => $result,
            'stat' => $stat,
        ]);
    }

    public function save(Request $request)
    {
        $this->ensureAdmin();

        $data = $request->validate([
            'genieacs_url' => ['nullable', 'string', 'max:255'],
            'genieacs_username' => ['nullable', 'string', 'max:255'],
            'genieacs_password' => ['nullable', 'string', 'max:255'],
            'genieacs_timeout' => ['nullable', 'integer', 'min:3', 'max:60'],
        ]);

        $url = trim((string) ($data['genieacs_url'] ?? ''));
        $url = rtrim($url, '/');

        $this->saveSetting('genieacs_url', $url);
        $this->saveSetting('genieacs_username', trim((string) ($data['genieacs_username'] ?? '')));
        $this->saveSetting('genieacs_password', (string) ($data['genieacs_password'] ?? ''), 'password');
        $this->saveSetting('genieacs_timeout', (string) ($data['genieacs_timeout'] ?? 8), 'number');

        return redirect('/admin/genieacs')->with('success', 'Setting GenieACS berhasil disimpan.');
    }

    public function test()
    {
        $this->ensureAdmin();

        $config = $this->config();

        if (($config['url'] ?? '') === '') {
            return redirect('/admin/genieacs')->with('error', 'URL GenieACS belum diisi.');
        }

        try {
            $url = rtrim($config['url'], '/') . '/devices/';
            $response = $this->client($config)->get($url, [
                'query' => '{}',
            ]);

            if ($response->successful()) {
                $json = $response->json();
                $count = is_array($json) ? count($json) : 0;

                return redirect('/admin/genieacs')->with('success', 'Koneksi GenieACS berhasil. Device terbaca: '.$count.'.');
            }

            return redirect('/admin/genieacs')->with('error', 'Koneksi gagal. HTTP '.$response->status().' - '.$response->body());
        } catch (\Throwable $e) {
            return redirect('/admin/genieacs')->with('error', 'Koneksi gagal: '.$e->getMessage());
        }
    }
}
