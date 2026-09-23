<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VerificationService
{
    /**
     * Base URL of the mock verification server.
     * Configured via config/services.php → VERIFICATION_URL env var.
     */
    protected string $baseUrl;

    /**
     * Request timeout in seconds.
     */
    protected int $timeout = 8;

    public function __construct()
    {
        $this->baseUrl = rtrim(
            config('services.verification.url', 'http://localhost:4500'),
            '/'
        );
    }

    /**
     * ─────────────────────────────────────────────────────────
     * PUBLIC METHODS — one per verification type
     * ─────────────────────────────────────────────────────────
     */

    /**
     * Verify a person's identity via IPRS (mock).
     *
     * @return array|null  Person data on success, null on failure.
     */
    public function verifyIprs(string $nationalId): ?array
    {
        return $this->call('iprs', [
            'national_id' => trim($nationalId),
        ]);
    }

    /**
     * Verify a driving licence via NTSA (mock).
     *
     * @return array|null  DL data on success, null on failure.
     */
    public function verifyDriver(string $dlNumber): ?array
    {
        return $this->call('ntsa', [
            'dl_number' => strtoupper(trim($dlNumber)),
        ]);
    }

    /**
     * Verify a vehicle via NTSA TIMS (mock).
     *
     * @return array|null  Vehicle data on success, null on failure.
     */
    public function verifyVehicle(string $registrationNumber): ?array
    {
        return $this->call('ntsa_vehicle', [
            'registration_number' => strtoupper(trim($registrationNumber)),
        ]);
    }

    /**
     * ─────────────────────────────────────────────────────────
     * INTERNAL — the unified call to the mock server
     * ─────────────────────────────────────────────────────────
     */

    /**
     * POST to the mock server's unified /verify endpoint.
     *
     * Returns the `data` payload on success, or null on any failure.
     * Never throws — callers can always treat null as "verification failed".
     */
    protected function call(string $type, array $payload): ?array
    {
        $payload['type'] = $type;

        try {
            $response = Http::timeout($this->timeout)
                ->acceptJson()
                ->asJson()
                ->post("{$this->baseUrl}/verify", $payload);

            // Non-2xx response — log and return null
            if (! $response->successful()) {
                Log::warning('Verification failed', [
                    'type'   => $type,
                    'status' => $response->status(),
                    'body'   => $response->body(),
                    'input'  => $this->redact($payload),
                ]);

                return null;
            }

            $json = $response->json();

            // Server returned 200 but with ok=false
            if (! ($json['ok'] ?? false)) {
                Log::info('Verification returned not-ok', [
                    'type'  => $type,
                    'error' => $json['error'] ?? 'unknown',
                    'input' => $this->redact($payload),
                ]);

                return null;
            }

            // Success
            Log::info('Verification succeeded', [
                'type'   => $type,
                'source' => $json['source'] ?? null,
            ]);

            return $json['data'] ?? null;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Network-level failure — server down, DNS, timeout
            Log::error('Verification connection failed', [
                'type'  => $type,
                'url'   => "{$this->baseUrl}/verify",
                'error' => $e->getMessage(),
            ]);

            return null;

        } catch (\Throwable $e) {
            // Anything else — bad JSON, unexpected shape, etc.
            Log::error('Verification exception', [
                'type'  => $type,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * ─────────────────────────────────────────────────────────
     * HELPERS
     * ─────────────────────────────────────────────────────────
     */

    /**
     * Redact sensitive identifiers before logging.
     * We log *that* a verification happened, but not the full PII.
     */
    protected function redact(array $payload): array
    {
        return collect($payload)
            ->map(function ($value, $key) {
                if (in_array($key, ['national_id', 'dl_number', 'registration_number'])) {
                    // Show last 3 chars only: "12345678" → "*****678"
                    $str = (string) $value;
                    return strlen($str) > 3
                        ? str_repeat('*', strlen($str) - 3) . substr($str, -3)
                        : '***';
                }
                return $value;
            })
            ->toArray();
    }
}