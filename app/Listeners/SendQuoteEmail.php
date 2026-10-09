<?php

namespace App\Listeners;

use App\Events\QuoteSent;
use App\Mail\QuoteMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\InteractsWithQueue;

class SendQuoteEmail
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
    public function handle(QuoteSent $event): void
    {
        $quote = $event->quote;
        if (! $quote->prospect_email) {
            Log::warning('Quote sent but no email on file', [
                'quote_id'     => $quote->id,
                'quote_number' => $quote->quote_number,
            ]);
    }
    Mail::to($quote->prospect_email)->queue(new QuoteMail($quote));
    }
}