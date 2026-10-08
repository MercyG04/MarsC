<?php

namespace App\Services;

use App\Models\Client;
use Illuminate\Support\Facades\DB;

class ClientKycService
{
    public function __construct(
        protected VerificationService $verification,
    ) {}

    /**
     * Enrich the incoming form data with IPRS + NTSA data,
     * then create the client record.
     */
    public function onboard(array $formData): Client
    {
        $enriched = $this->enrich($formData);

        return DB::transaction(function () use ($enriched) {
            return Client::create($enriched);
        });
    }

    /**
     * Merge external verification data into the form data.
     */
    protected function enrich(array $data): array
    {
        // 1. IPRS lookup using national ID
        $iprs = $this->verification->verifyIprs($data['national_id']);

        if ($iprs) {
            $data['first_name']  = $iprs['first_name']  ?? $data['first_name'];
            $data['last_name']   = $iprs['last_name']   ?? $data['last_name'];
            $data['other_names'] = $iprs['other_names'] ?? ($data['other_names'] ?? null);
            $data['kra_pin']     = $iprs['kra_pin']     ?? $data['kra_pin'];
            $data['date_of_birth'] = $iprs['date_of_birth'] ?? $data['date_of_birth'];
            $data['gender']      = $iprs['gender']      ?? $data['gender'];
        }

        // 2. NTSA lookup using driving licence number (if provided)
        if (! empty($data['dl_number'])) {
            $ntsa = $this->verification->verifyDriver($data['dl_number']);

            if ($ntsa && isset($ntsa['driving_experience'])) {
                $data['driving_experience'] = (int) $ntsa['driving_experience'];
            }
        }

        // Remove dl_number — not a clients column
        unset($data['dl_number']);

        // Ensure consent timestamp is set if consent given
        if (! empty($data['consent_given'])) {
            $data['consent_given'] = true;
            $data['consent_given_at'] = now();
        }

        return $data;
    }
}