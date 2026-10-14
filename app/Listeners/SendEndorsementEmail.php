<?php

namespace App\Listeners;

use App\Events\PolicyEndorsed;
use App\Mail\EndorsementMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEndorsementEmail implements ShouldQueue
{
    public function handle(PolicyEndorsed $event): void
    {
        $client = $event->endorsement->policy->client;

        if (! $client?->email) {
            Log::warning('Endorsement created but client has no email', [
                'endorsement_id' => $event->endorsement->id,
                'client_id'      => $client?->id,
            ]);
            return;
        }

        Mail::to($client->email)->queue(new EndorsementMail($event->endorsement));
    }
}