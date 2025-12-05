<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class LicenseService
{
    // Path to store the license file locally
    protected string $licensePath = 'license.key';

    // Your SaaS API Endpoint
    protected string $remoteUrl = 'https://raystate.com/api/v1/license';

    /**
     * The Main Check: Call this in Middleware.
     */
    public function check(): bool
    {
        // 1. Basic Local Validation (Fast)
        if (! $this->validateLocal()) {
            return false;
        }

        // 2. Optional: Background Remote Check (if internet is available)
        // We cache the "online check" for 24h to avoid slowing down the app on every request
        if ($this->hasInternetConnection() && ! Cache::has('license_verified_recently')) {
            return $this->validateRemote();
        }

        return true;
    }

    /**
     * Step 1: Activate the App (Needs Internet)
     */
    public function activate(string $licenseKey): bool
    {
        try {
            $machineId = $this->getMachineId();

            $response = Http::post("$this->remoteUrl/activate", [
                'key' => $licenseKey,
                'machine_id' => $machineId,
                'app_version' => config('app.version', '1.0.0'),
            ]);

            if ($response->successful()) {
                // The server returns a signed payload (JWT or encrypted JSON)
                // containing: { key, machine_id, expiry, signature }
                $licenseData = $response->json('license_data');

                // Store it securely
                Storage::disk('local')->put($this->licensePath, json_encode($licenseData));

                // Mark as verified recently
                Cache::put('license_verified_recently', true, now()->addDay());

                return true;
            }
        } catch (\Exception $e) {
            // Log error
        }

        return false;
    }

    /**
     * Check if the local file exists and belongs to THIS machine.
     */
    protected function validateLocal(): bool
    {
        if (! Storage::disk('local')->exists($this->licensePath)) {
            return false;
        }

        $data = json_decode(Storage::disk('local')->get($this->licensePath), true);

        // 1. Check if Machine ID matches (Prevents copying file to another PC)
        if (($data['machine_id'] ?? '') !== $this->getMachineId()) {
            return false;
        }

        // 2. (Optional) Verify Cryptographic Signature here if using Public Key
        // if (! $this->verifySignature($data)) return false;

        return true;
    }

    /**
     * Re-validate with server to check for revocations/bans.
     */
    protected function validateRemote(): bool
    {
        try {
            $data = json_decode(Storage::disk('local')->get($this->licensePath), true);

            $response = Http::post("$this->remoteUrl/validate", [
                'key' => $data['key'] ?? '',
                'machine_id' => $this->getMachineId(),
            ]);

            if ($response->status() === 403) {
                // License Revoked/Banned! Delete local file.
                Storage::disk('local')->delete($this->licensePath);

                return false;
            }

            // Still valid, update cache
            Cache::put('license_verified_recently', true, now()->addDay());

            return true;

        } catch (\Exception $e) {
            // If internet fails mid-check, fail open (allow access) or closed?
            // Usually better to "Fail Open" (return true) so user isn't locked out due to bad wifi.
            return true;
        }
    }

    /**
     * Generate a unique fingerprint for the device.
     * In NativePHP, you might use shell_exec or specific OS commands.
     */
    protected function getMachineId(): string
    {
        // Simple example: Hash of the machine name + OS
        // For production, use `wmic csproduct get uuid` on Windows
        return hash('sha256', php_uname('n').php_uname('r'));
    }

    protected function hasInternetConnection(): bool
    {
        return @fsockopen('www.google.com', 80) !== false;
    }
}
