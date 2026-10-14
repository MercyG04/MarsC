<?php

use App\Enums\PolicyType;
use App\Enums\QuotationStatus;
use App\Enums\UserRole;
use App\Mail\QuoteMail;
use App\Models\Quote;
use App\Models\User;
use App\Services\QuoteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

it('creates a quotation, sends it, and dispatches the email', function () {
    // ─── Arrange ───
    Mail::fake();

    $agent = User::create([
        'name'              => 'Test Agent',
        'email'             => 'agent-test@marsc.test',
        'phone_number'      => '0700000001',
        'password'          => bcrypt('Demo1234!'),
        'role'              => UserRole::Agent,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($agent);

    $service = app(QuoteService::class);

    // ─── Act 1: Create the quote ───
    $quote = $service->create([
        'prospect_name'   => 'John Kamau',
        'prospect_phone'  => '0722334455',
        'prospect_email'  => 'john.kamau@example.com',
        'vehicle_details' => [
            'make'                 => 'Toyota',
            'model'                => 'Hilux',
            'year_of_manufacture'  => 2022,
            'registration_number'  => 'KDA777Z',
            'estimated_value'      => 3500000,
            'vehicle_use'          => 'personal',
        ],
        'policy_type'          => 'comprehensive',
        'selected_add_on_ids'  => [],
    ]);

    // ─── Assert 1: The quote exists with the right fields ───
    expect($quote)->toBeInstanceOf(Quote::class)
        ->and($quote->status)->toBe(QuotationStatus::Draft)
        ->and($quote->prospect_name)->toBe('John Kamau')
        ->and($quote->prospect_email)->toBe('john.kamau@example.com')
        ->and($quote->policy_type)->toBe(PolicyType::Comprehensive)
        ->and((float) $quote->gross_premium)->toBeGreaterThan(0);

    expect($quote->premium_breakdown)->toBeArray()
        ->and($quote->premium_breakdown)->toHaveKeys([
            'basic_premium',
            'training_levy',
            'phcf',
            'stamp_duty',
            'gross_premium',
            'sum_insured',
            'deductible_amount',
        ]);

    expect($quote->vehicle_details)->toBeArray()
        ->and($quote->vehicle_details['make'])->toBe('Toyota');

    $this->assertDatabaseHas('quotes', [
        'quote_number'    => $quote->quote_number,
        'prospect_name'   => 'John Kamau',
        'prospect_email'  => 'john.kamau@example.com',
    ]);

    expect($quote->quote_number)->toMatch('/^QUO-\d{4}-\d{5}$/');

    // ─── Act 2: Mark the quote as sent ───
    $service->markAsSent($quote);

    // ─── Assert 2: Status flipped and mail dispatched ───
    $quote->refresh();

    expect($quote->status)->toBe(QuotationStatus::Sent)
        ->and($quote->sent_at)->not->toBeNull();

    Mail::assertQueued(QuoteMail::class, function ($mail) use ($quote) {
        return $mail->quote->id === $quote->id
            && $mail->hasTo('john.kamau@example.com');
    });
});