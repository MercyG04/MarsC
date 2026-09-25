@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto py-8 px-4">

        {{-- Header --}}
        <div class="mb-6 flex items-start justify-between">
            <div>
                <a href="{{ route('valuations.index') }}" class="text-sm text-green-700 hover:text-green-900 font-medium">
                    ← Back to Valuations
                </a>
                <h1 class="text-2xl font-bold text-green-800 mt-2">Valuation Record</h1>
                <p class="text-sm text-gray-500">
                    {{ $valuation->valuation_date->format('d F Y') }} ·
                    {{ $valuation->valuation_firm }}
                </p>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                @if($valuation->valuation_type === \App\Enums\ValuationType::Initial) bg-lime-100 text-lime-800
                @elseif($valuation->valuation_type === \App\Enums\ValuationType::Disputed) bg-purple-100 text-purple-800
                @else bg-yellow-100 text-yellow-800
                @endif">
                {{ $valuation->valuation_type->label() }}
            </span>
        </div>

        {{-- Numbers --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h2 class="text-sm font-semibold text-green-800 mb-3">Valuation Numbers</h2>
                <dl class="text-sm space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Actual Cash Value</dt>
                        <dd class="font-bold text-green-800 text-lg">
                            KES {{ number_format($valuation->actual_cash_value, 2) }}
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Forced Sale Value</dt>
                        <dd class="font-semibold text-gray-800">
                            {{ $valuation->forced_sale_value ? 'KES ' . number_format($valuation->forced_sale_value, 2) : '—' }}
                        </dd>
                    </div>
                    @if ($valuation->forced_sale_value && $valuation->actual_cash_value)
                        @php
                            $discount = (($valuation->actual_cash_value - $valuation->forced_sale_value) / $valuation->actual_cash_value) * 100;
                        @endphp
                        <div class="flex justify-between">
                            <dt class="text-gray-500">FSV discount</dt>
                            <dd class="text-gray-600">{{ number_format($discount, 1) }}% below ACV</dd>
                        </div>
                    @endif
                </dl>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h2 class="text-sm font-semibold text-green-800 mb-3">Assessor</h2>
                <dl class="text-sm space-y-2">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Firm</dt>
                        <dd class="font-medium text-gray-800">{{ $valuation->valuation_firm }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Valuer</dt>
                        <dd class="font-medium text-gray-800">{{ $valuation->valuer_name ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Date</dt>
                        <dd class="font-medium text-gray-800">{{ $valuation->valuation_date->format('d M Y') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Recorded by</dt>
                        <dd class="font-medium text-gray-800">{{ $valuation->createdBy?->name ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Recorded at</dt>
                        <dd class="text-gray-600">{{ $valuation->created_at->format('d M Y, H:i') }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- Vehicle --}}
        <div class="mt-4 bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h2 class="text-sm font-semibold text-green-800 mb-3">Vehicle</h2>
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-medium text-gray-800">
                        {{ $valuation->vehicle->year_of_manufacture }}
                        {{ $valuation->vehicle->make }}
                        {{ $valuation->vehicle->model }}
                        <span class="font-mono text-gray-500">({{ $valuation->vehicle->registration_number }})</span>
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        Chassis {{ $valuation->vehicle->chassis_number }} ·
                        Logbook {{ $valuation->vehicle->logbook_number }}
                    </p>
                </div>
                <a href="{{ route('vehicles.show', $valuation->vehicle) }}"
                    class="text-sm text-green-700 hover:text-green-900 font-medium">View vehicle →</a>
            </div>
        </div>

        {{-- Owner --}}
        <div class="mt-4 bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h2 class="text-sm font-semibold text-green-800 mb-3">Owner</h2>
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-medium text-gray-800">
                        {{ $valuation->vehicle->client->first_name }}
                        {{ $valuation->vehicle->client->other_names }}
                        {{ $valuation->vehicle->client->last_name }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        ID {{ $valuation->vehicle->client->national_id }} ·
                        {{ $valuation->vehicle->client->phone_number }} ·
                        {{ $valuation->vehicle->client->county }}
                    </p>
                </div>
                <a href="{{ route('clients.show', $valuation->vehicle->client) }}"
                    class="text-sm text-green-700 hover:text-green-900 font-medium">View client →</a>
            </div>
        </div>

        {{-- Notes --}}
        @if ($valuation->notes)
            <div class="mt-4 bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h2 class="text-sm font-semibold text-green-800 mb-3">Valuer's Notes</h2>
                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $valuation->notes }}</p>
            </div>
        @endif

        {{-- Report --}}
        <div class="mt-4 bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h2 class="text-sm font-semibold text-green-800 mb-3">Report</h2>
            @if ($valuation->report_path)
                <a href="#" class="inline-flex items-center gap-2 text-sm text-green-700 hover:text-green-900 font-medium">
                    📄 Download Report (signed URL, expires in 15 min)
                </a>
                {{-- When S3 is wired up: --}}
                {{-- <a href="{{ Storage::disk('s3')->temporaryUrl($valuation->report_path, now()->addMinutes(15)) }}" ...> --}}
            @else
                    <p class="text-sm text-gray-400">No report attached.</p>
                @endif
        </div>

        {{-- Immutability notice --}}
        <div class="mt-6 rounded-lg bg-lilac-50 border border-purple-200 p-4 text-xs text-gray-600">
            <strong class="text-green-800">This record is immutable.</strong>
            Valuations are append-only. To correct or dispute this record, record a new valuation —
            the original stays as a historical reference. Only admins can record disputed valuations.
        </div>

    </div>
@endsection