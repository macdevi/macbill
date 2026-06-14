<?php

namespace App\Services;

use App\Models\MikrotikRouter;
use RuntimeException;

class MikrotikApiClient
{
    private mixed $stream = null;

    public function __construct(
        private MikrotikRouter $router,
        private int $timeout = 6
    ) {}

    public function test(): array
    {
        $this->connect();

        try {
            $this->login();

            $resource = $this->command('/system/resource/print');
            $identity = $this->command('/system/identity/print');

            $resourceData = $this->firstResponseData($resource);
            $identityData = $this->firstResponseData($identity);

            $message = 'Terhubung';

            if (!empty($identityData['name'])) {
                $message .= ' ke '.$identityData['name'];
            }

            if (!empty($resourceData['version'])) {
                $message .= ' · RouterOS '.$resourceData['version'];
            }

            if (!empty($resourceData['uptime'])) {
                $message .= ' · uptime '.$resourceData['uptime'];
            }

            return [
                'message' => $message,
                'resource' => $resourceData,
                'identity' => $identityData,
            ];
        } finally {
            if (is_resource($this->stream)) {
                fclose($this->stream);
            }
        }
    }


    public function pppoeProfiles(): array
    {
        $this->connect();

        try {
            $this->login();

            $responses = $this->command('/ppp/profile/print');

            $profiles = [];

            foreach ($responses as $sentence) {
                if (!in_array('!re', $sentence, true)) {
                    continue;
                }

                $data = $this->parseWords($sentence);

                if (!empty($data['name'])) {
                    $profiles[] = $data;
                }
            }

            return $profiles;
        } finally {
            if (is_resource($this->stream)) {
                fclose($this->stream);
            }
        }
    }


    public function pppoeSecrets(): array
    {
        $this->connect();

        try {
            $this->login();

            $responses = $this->command('/ppp/secret/print');

            $secrets = [];

            foreach ($responses as $sentence) {
                if (!in_array('!re', $sentence, true)) {
                    continue;
                }

                $data = $this->parseWords($sentence);

                if (!empty($data['name'])) {
                    $secrets[] = $data;
                }
            }

            return $secrets;
        } finally {
            if (is_resource($this->stream)) {
                fclose($this->stream);
            }
        }
    }


    public function syncPppoeSecret(string $name, string $password, string $profile, ?string $comment = null): array
    {
        $this->connect();

        try {
            $this->login();

            $existing = $this->findPppoeSecretByName($name);

            $words = [
                '=name='.$name,
                '=password='.$password,
                '=service=pppoe',
                '=profile='.$profile,
                '=disabled=no',
            ];

            if ($comment !== null && trim($comment) !== '') {
                $words[] = '=comment='.$comment;
            }

            if (!empty($existing['.id'])) {
                $this->command('/ppp/secret/set', array_merge([
                    '=.id='.$existing['.id'],
                ], $words));

                $action = 'updated';
            } else {
                $this->command('/ppp/secret/add', $words);

                $action = 'created';
            }

            $fresh = $this->findPppoeSecretByName($name);

            return [
                'action' => $action,
                'secret' => $fresh ?: [
                    'name' => $name,
                    'password' => $password,
                    'service' => 'pppoe',
                    'profile' => $profile,
                    'disabled' => 'false',
                    'comment' => $comment,
                ],
            ];
        } finally {
            if (is_resource($this->stream)) {
                fclose($this->stream);
            }
        }
    }

    private function findPppoeSecretByName(string $name): ?array
    {
        $responses = $this->command('/ppp/secret/print', [
            '?name='.$name,
        ]);

        foreach ($responses as $sentence) {
            if (!in_array('!re', $sentence, true)) {
                continue;
            }

            $data = $this->parseWords($sentence);

            if (($data['name'] ?? null) === $name) {
                return $data;
            }
        }

        return null;
    }


    public function pppoeActiveSessions(): array
    {
        $this->connect();

        try {
            $this->login();

            $responses = $this->command('/ppp/active/print');

            $sessions = [];

            foreach ($responses as $sentence) {
                if (!in_array('!re', $sentence, true)) {
                    continue;
                }

                $data = $this->parseWords($sentence);

                if (!empty($data['name'])) {
                    $sessions[] = $data;
                }
            }

            return $sessions;
        } finally {
            if (is_resource($this->stream)) {
                fclose($this->stream);
            }
        }
    }

    private function connect(): void
    {
        $host = trim((string) $this->router->host);
        $port = (int) $this->router->api_port;
        $scheme = $this->router->use_ssl ? 'ssl' : 'tcp';
        $address = "{$scheme}://{$host}:{$port}";

        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ],
        ]);

        $errno = 0;
        $errstr = '';

        $this->stream = @stream_socket_client(
            $address,
            $errno,
            $errstr,
            $this->timeout,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if (!$this->stream) {
            throw new RuntimeException("Tidak bisa konek ke {$host}:{$port}. {$errstr}");
        }

        stream_set_timeout($this->stream, $this->timeout);
    }

    private function login(): void
    {
        $responses = $this->command('/login', [
            '=name='.$this->router->username,
            '=password='.(string) $this->router->api_password,
        ]);

        if ($this->hasTrap($responses)) {
            throw new RuntimeException('Login gagal: '.$this->trapMessage($responses));
        }
    }

    private function command(string $command, array $words = []): array
    {
        $this->writeSentence(array_merge([$command], $words));

        return $this->readUntilDone();
    }

    private function writeSentence(array $words): void
    {
        foreach ($words as $word) {
            $this->writeWord((string) $word);
        }

        $this->writeWord('');
    }

    private function writeWord(string $word): void
    {
        $len = strlen($word);
        fwrite($this->stream, $this->encodeLength($len).$word);
    }

    private function encodeLength(int $len): string
    {
        if ($len < 0x80) {
            return chr($len);
        }

        if ($len < 0x4000) {
            return chr(($len >> 8) | 0x80).chr($len & 0xFF);
        }

        if ($len < 0x200000) {
            return chr(($len >> 16) | 0xC0).chr(($len >> 8) & 0xFF).chr($len & 0xFF);
        }

        if ($len < 0x10000000) {
            return chr(($len >> 24) | 0xE0).chr(($len >> 16) & 0xFF).chr(($len >> 8) & 0xFF).chr($len & 0xFF);
        }

        return chr(0xF0).chr(($len >> 24) & 0xFF).chr(($len >> 16) & 0xFF).chr(($len >> 8) & 0xFF).chr($len & 0xFF);
    }

    private function readUntilDone(): array
    {
        $responses = [];
        $start = time();

        while (true) {
            if ((time() - $start) > $this->timeout + 2) {
                throw new RuntimeException('Timeout membaca respon Mikrotik.');
            }

            $sentence = $this->readSentence();

            if ($sentence === []) {
                continue;
            }

            $responses[] = $sentence;

            if (in_array('!done', $sentence, true)) {
                break;
            }

            if (in_array('!fatal', $sentence, true)) {
                throw new RuntimeException('Fatal error dari Mikrotik.');
            }
        }

        if ($this->hasTrap($responses)) {
            throw new RuntimeException($this->trapMessage($responses));
        }

        return $responses;
    }

    private function readSentence(): array
    {
        $words = [];

        while (true) {
            $word = $this->readWord();

            if ($word === '') {
                break;
            }

            $words[] = $word;
        }

        return $words;
    }

    private function readWord(): string
    {
        $len = $this->readLength();

        if ($len === 0) {
            return '';
        }

        return $this->readExact($len);
    }

    private function readLength(): int
    {
        $c = ord($this->readExact(1));

        if (($c & 0x80) === 0x00) {
            return $c;
        }

        if (($c & 0xC0) === 0x80) {
            return (($c & ~0xC0) << 8) + ord($this->readExact(1));
        }

        if (($c & 0xE0) === 0xC0) {
            return (($c & ~0xE0) << 16) + (ord($this->readExact(1)) << 8) + ord($this->readExact(1));
        }

        if (($c & 0xF0) === 0xE0) {
            return (($c & ~0xF0) << 24)
                + (ord($this->readExact(1)) << 16)
                + (ord($this->readExact(1)) << 8)
                + ord($this->readExact(1));
        }

        return (ord($this->readExact(1)) << 24)
            + (ord($this->readExact(1)) << 16)
            + (ord($this->readExact(1)) << 8)
            + ord($this->readExact(1));
    }

    private function readExact(int $length): string
    {
        $data = '';

        while (strlen($data) < $length) {
            $chunk = fread($this->stream, $length - strlen($data));

            if ($chunk === false || $chunk === '') {
                $meta = stream_get_meta_data($this->stream);

                if (!empty($meta['timed_out'])) {
                    throw new RuntimeException('Timeout koneksi Mikrotik.');
                }

                throw new RuntimeException('Koneksi Mikrotik terputus.');
            }

            $data .= $chunk;
        }

        return $data;
    }

    private function hasTrap(array $responses): bool
    {
        foreach ($responses as $sentence) {
            if (in_array('!trap', $sentence, true)) {
                return true;
            }
        }

        return false;
    }

    private function trapMessage(array $responses): string
    {
        foreach ($responses as $sentence) {
            if (!in_array('!trap', $sentence, true)) {
                continue;
            }

            $data = $this->parseWords($sentence);

            return $data['message'] ?? 'akses ditolak atau parameter salah';
        }

        return 'akses ditolak atau parameter salah';
    }

    private function firstResponseData(array $responses): array
    {
        foreach ($responses as $sentence) {
            if (in_array('!re', $sentence, true)) {
                return $this->parseWords($sentence);
            }
        }

        return [];
    }

    private function parseWords(array $sentence): array
    {
        $data = [];

        foreach ($sentence as $word) {
            if (!str_starts_with($word, '=')) {
                continue;
            }

            $word = substr($word, 1);
            $parts = explode('=', $word, 2);

            if (count($parts) === 2) {
                $data[$parts[0]] = $parts[1];
            }
        }

        return $data;
    }
}
