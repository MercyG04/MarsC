<?php

namespace App\Listeners;

use App\Events\PolicyIssued;
use App\Mail\PolicyMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPolicyEmail
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PolicyIssued $event): void
    {
        $policy = $event->policy;
        $client = $policy->client;
        if (! $client?->email) {
            Log::warning('Policy issued but client has no email', [
                'policy_id'     => $policy->id,
                'policy_number' => $policy->policy_number,
                'client_id'     => $client?->id,
            ]);
            return;
        }
        Mail::to($client->email)->queue(new PolicyMail($policy));
    }
}
