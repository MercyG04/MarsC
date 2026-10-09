@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto py-8 px-4">

    {{-- ─── Header ─── --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div>
            <a href="{{ route('quotations.index') }}"
               class="text-sm text-green-700 hover:text-green-900 font-medium">
                ← Back to Quotations
            </a>
            <h1 class="text-2xl font-bold text-green-800 mt-2">
                Quotation <span class="font-mono">{{ $quotation->quote_number }}</span>
            </h1>
            <p class="text-sm text-gray-500">
                Created {{ $quotation->created_at->format('d M Y, H:i') }}
                by {{ $quotation->createdBy?->name ?? '—' }}
            </p>
        </div>

        <div class="text-left sm:text-right">
            @php
                $statusClasses = match($quotation->status) {
                    \App\Enums\QuotationStatus::Draft    => 'bg-gray-100 text-gray-700',
                    \App\Enums\QuotationStatus::Sent     => 'bg-yellow-100 text-yellow-800',
                    \App\Enums\QuotationStatus::Accepted => 'bg-lime-100 text-lime-800',
                    \App\Enums\QuotationStatus::Declined => 'bg-red-100 text-red-800',
                    \App\Enums\QuotationStatus::Expired  => 'bg-purple-100 text-purple-800',
                };
            @endphp
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $statusClasses }}">
                {{ $quotation->status->label() }}
            </span>
            <p class="text-xs text-gray-500 mt-2">
                Valid until {{ $quotation->valid_until->format('d M Y') }}
            </p>
        </div>
    </div>

    {{-- ─── Flash + errors ─── --}}
    @if (session('success'))
        <div class="mb-4 p-3 rounded-lg bg-lime-50 border border-lime-200 text-lime-800 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ─── Accepted notice ─── --}}
    @if ($quotation->status === \App\Enums\QuotationStatus::Accepted && $quotation->policy)
        <div class="mb-4 p-4 rounded-lg bg-lime-50 border border-lime-200">
            <strong class="text-lime-900 text-sm">This quotation has been converted.</strong>
            <p class="text-sm text-lime-800 mt-1">
                Policy
                <a href="{{ route('policies.show', $quotation->policy) }}"
                   class="font-mono font-medium underline">
                    {{ $quotation->policy->policy_number }}
                </a>
                was issued for this quote.
            </p>
        </div>
    @endif

    {{-- ─── Prospect + Vehicle cards ─── --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        {{-- Prospect --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h2 class="text-xs font-semibold text-green-800 uppercase tracking-wide mb-3">Prospect</h2>
            <dl class="text-sm space-y-1.5">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Name</dt>
                    <dd class="font-medium text-gray-800">{{ $quotation->prospect_name }}</dd>
                </div>
                @if ($quotation->prospect_phone)
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Phone</dt>
                        <dd class="text-gray-800">{{ $quotation->prospect_phone }}</dd>
                    </div>
                @endif
                @if ($quotation->prospect_email)
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Email</dt>
                        <dd class="text-gray-800 truncate">{{ $quotation->prospect_email }}</dd>
                    </div>
                @endif
                @if ($quotation->client)
                    <div class="flex justify-between pt-2 border-t border-gray-100 mt-2">
                        <dt class="text-gray-500">Linked Client</dt>
                        <dd>
                            <a href="{{ route('clients.show', $quotation->client) }}"
                               class="text-green-700 hover:text-green-900 font-medium">
                                {{ $quotation->client->first_name }} {{ $quotation->client->last_name }}
                            </a>
                        </dd>
                    </div>
                @endif
            </dl>
        </div>

        {{-- Vehicle --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h2 class="text-xs font-semibold text-green-800 uppercase tracking-wide mb-3">Vehicle</h2>
            <dl class="text-sm space-y-1.5">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Make & Model</dt>
                    <dd class="text-gray-800">
                        {{ $quotation->vehicle_details['make'] ?? '—' }}
                        {{ $quotation->vehicle_details['model'] ?? '' }}
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Year</dt>
                    <dd class="text-gray-800">{{ $quotation->vehicle_details['year_of_manufacture'] ?? '—' }}</dd>
                </div>
                @if (! empty($quotation->vehicle_details['registration_number']))
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Registration</dt>
                        <dd class="font-mono text-gray-800">
                            {{ $quotation->vehicle_details['registration_number'] }}
                        </dd>
                    </div>
                @endif
                <div class="flex justify-between">
                    <dt class="text-gray-500">Usage</dt>
                    <dd class="text-gray-800">
                        {{ \App\Enums\VehicleUse::from($quotation->vehicle_details['vehicle_use'] ?? 'personal')->label() }}
                    </dd>
                </div>
                <div class="flex justify-between border-t border-gray-100 pt-1.5 mt-1.5">
                    <dt class="text-gray-500">Estimated Value</dt>
                    <dd class="font-semibold text-green-800">
                        KES {{ number_format($quotation->vehicle_details['estimated_value'] ?? 0, 2) }}
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    {{-- ─── Premium breakdown ─── --}}
    @php $breakdown = $quotation->premium_breakdown; @endphp
    <div class="mt-4 bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xs font-semibold text-green-800 uppercase tracking-wide">Premium Breakdown</h2>
            <span class="text-xs text-gray-500">{{ $quotation->policy_type->label() }}</span>
        </div>

        <dl class="text-sm space-y-2">
            <div class="flex justify-between">
                <dt class="text-gray-600">Basic Premium</dt>
                <dd class="text-gray-800">KES {{ number_format($breakdown['basic_premium'], 2) }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-gray-600">Training Levy (0.2%)</dt>
                <dd class="text-gray-800">KES {{ number_format($breakdown['training_levy'], 2) }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-gray-600">PHCF (0.25%)</dt>
                <dd class="text-gray-800">KES {{ number_format($breakdown['phcf'], 2) }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-gray-600">Stamp Duty</dt>
                <dd class="text-gray-800">KES {{ number_format($breakdown['stamp_duty'], 2) }}</dd>
            </div>

            @if (! empty($breakdown['add_ons']))
                <div class="pt-2 mt-2 border-t border-gray-100">
                    <dt class="text-xs text-gray-500 uppercase tracking-wide mb-2">Optional Covers</dt>
                    @foreach ($breakdown['add_ons'] as $addOn)
                        <div class="flex justify-between text-xs">
                            <dt class="text-gray-600">{{ $addOn['name'] }}</dt>
                            <dd class="text-gray-800">
                                KES {{ number_format($addOn['charged_amount'], 2) }}
                            </dd>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="flex justify-between border-t-2 border-green-200 pt-3 mt-3">
                <dt class="font-semibold text-green-800">Gross Premium</dt>
                <dd class="font-bold text-green-800 text-xl">
                    KES {{ number_format($breakdown['gross_premium'], 2) }}
                </dd>
            </div>

            <div class="flex justify-between pt-2 text-xs text-gray-500">
                <dt>Sum Insured</dt>
                <dd>KES {{ number_format($breakdown['sum_insured'], 2) }}</dd>
            </div>
            <div class="flex justify-between text-xs text-gray-500">
                <dt>Deductible</dt>
                <dd>KES {{ number_format($breakdown['deductible_amount'], 2) }}</dd>
            </div>
        </dl>
    </div>

    {{-- ─── Action buttons ─── --}}
    @php
        $canSend    = $quotation->status === \App\Enums\QuotationStatus::Draft;
        $canDecline = in_array($quotation->status, [
            \App\Enums\QuotationStatus::Draft,
            \App\Enums\QuotationStatus::Sent,
        ], true);
        $canConvert = $quotation->status === \App\Enums\QuotationStatus::Sent;
    @endphp

    <div class="mt-6 flex flex-col sm:flex-row flex-wrap gap-3">

        @if ($canSend)
            <form method="POST" action="{{ route('quotations.mark-sent', $quotation) }}">
                @csrf
                <button type="submit"
                        class="w-full sm:w-auto px-5 py-2.5 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-medium shadow-sm">
                    Mark as Sent & Email Prospect
                </button>
            </form>
        @endif

        @if ($canConvert)
            <a href="{{ route('clients.create', ['quote_id' => $quotation->id]) }}"
               class="w-full sm:w-auto px-5 py