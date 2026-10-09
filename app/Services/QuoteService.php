<?php

namespace App\Services;

use App\Enums\QuotationStatus;
use App\Events\QuoteSent;
use App\Models\Quote;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuoteService
{
    public function __construct(
        protected PremiumCalculator $calculator,
    ) {}

    /**
     * Create a new quote.
     */
    public function create(array $data): Quote
    {
        return DB::transaction(function () use ($data) {

            // 1. Compute the premium breakdown for the raw vehicle details
            $breakdown = $this->calculator->calculateForQuote(
                $data['vehicle_details'],
                $data['policy_type'],
                $data['selected_add_on_ids'] ?? [],
            );

            // 2. Create the quote
            $quote = Quote::create([
                'quote_number'        => $this->nextNumber(),
                'client_id'           => $data['client_id'] ?? null,
                'prospect_name'       => $data['prospect_name'],
                'prospect_phone'      => $data['prospect_phone'] ?? null,
                'prospect_email'      => $data['prospect_email'] ?? null,
                'vehicle_details'     => $data['vehicle_details'],
                'policy_type'         => $data['policy_type'],
                'selected_add_on_ids' => $data['selected_add_on_ids'] ?? [],
                'premium_breakdown'   => $breakdown,
                'gross_premium'       => $breakdown['gross_premium'],
                'status'              => QuotationStatus::Draft,
                'valid_until'         => now()->addDays(14),
                'created_by'          => Auth::id(),
            ]);

            return $quote;
        });
    }

    /**
     * Mark a quote as sent. Fires the event that triggers email delivery.
     */
    public function markAsSent(Quote $quote): Quote
    {
        if ($quote->status->isFinal()) {
            throw new \RuntimeException('Cannot send a quote in a final state.');
        }

        $quote->update([
            'status'  => QuotationStatus::Sent,
            'sent_at' => now(),
        ]);

        // Fire the event — the listener sends the email
        event(new QuoteSent($quote->fresh()));

        return $quote->fresh();
    }

    /**
     * Mark a quote as accepted. Called when the linked policy is issued.
     */
    public function markAsAccepted(Quote $quote): Quote
    {
        if ($quote->status !== QuotationStatus::Sent) {
            throw new \RuntimeException('Only sent quotes can be accepted.');
        }

        $quote->update([
            'status'      => QuotationStatus::Accepted,
            'accepted_at' => now(),
        ]);

        return $quote->fresh();
    }

    /**
     * Mark a quote as declined.
     */
    public function markAsDeclined(Quote $quote): Quote
    {
        if ($quote->status->isFinal()) {
            throw new \RuntimeException('Cannot decline a quote in a final state.');
        }

        $quote->update([
            'status' => QuotationStatus::Declined,
        ]);

        return $quote->fresh();
    }

    /**
     * Expire stale quotes. Called by a scheduled command.
     */
    public function expireStaleQuotes(): int
    {
        return Quote::where('status', QuotationStatus::Sent->value)
            ->where('valid_until', '<', now())
            ->update(['status' => QuotationStatus::Expired->value]);
    }

    /**
     * Generate the next quote number for the current year.
     * Format: QUO-YYYY-NNNNN
     */
    protected function nextNumber(): string
    {
        $prefix = 'QUO-' . now()->year . '-';

        $latest = Quote::withTrashed()
            ->where('quote_number', 'like', "{$prefix}%")
            ->orderByDesc('quote_number')
            ->lockForUpdate()
            ->first();

        $next = $latest
            ? ((int) substr($latest->quote_number, -5)) + 1
            : 1;

        return $prefix . str_pad($next, 5, '0', STR_PAD_LEFT);
    }
}