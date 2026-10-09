@extends('layouts.app')

@section('content')
    <div class="max-w-5xl mx-auto py-8 px-4">

        {{-- ─── Header ─── --}}
        <div class="mb-6 flex items-start justify-between">
            <div>
                <a href="{{ route('clients.show', $vehicle->client) }}"
                    class="text-sm text-green-700 hover:text-green-900 font-medium">
                    ← Back to {{ $vehicle->client->first_name }} {{ $vehicle->client->last_name }}
                </a>
                <h1 class="text-2xl font-bold text-green-800 mt-2">
                    {{ $vehicle->make }} {{ $vehicle->model }}
                    <span class="text-gray-400 font-mono text-lg">{{ $vehicle->registration_number }}</span>
                </h1>
                <p class="text-sm text-gray-500">
                    {{ $vehicle->year_of_manufacture }} · {{ $vehicle->vehicle_use->label() }}
                </p>
            </div>

            {{-- Status badge --}}
            <div class="text-right">
                @php
                    $statusClasses = match ($vehicle->status) {
                        \App\Enums\VehicleStatus::PendingValuation => 'bg-yellow-100 text-yellow-800',
                        \App\Enums\VehicleStatus::Valued => 'bg-lime-100 text-lime-800',
                        \App\Enums\VehicleStatus::Insured => 'bg-green-100 text-green-800',
                        \App\Enums\VehicleStatus::Retired => 'bg-gray-100 text-gray-700',
                    };
                @endphp
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $statusClasses }}">
                    {{ $vehicle->status->label() }}
                </span>

                @if ($vehicle->ntsa_verified)
                    <div class="mt-2 text-xs text-green-700">
                        ✓ NTSA verified {{ $vehicle->ntsa_verified_at?->diffForHumans() }}
                    </div>
                @else
                    <div class="mt-2 text-xs text-yellow-700">
                        ⚠ Not verified with NTSA
                    </div>
                @endif
            </div>
        </div>

        @if (session('success'))
            <div class="mb-4 p-3 rounded-lg bg-lime-50 border border-lime-200 text-lime-800 text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- ─── Two-column detail cards ─── --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            {{-- Identity (immutable fields) --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-semibold text-green-800">Identity</h2>
                    <span class="text-xs text-gray-400">Read-only</span>
                </div>
                <dl class="text-sm space-y-2">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Registration No.</dt>
                        <dd class="font-mono font-semibold text-gray-800">{{ $vehicle->registration_number }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Chassis No.</dt>
                        <dd class="font-mono text-gray-800">{{ $vehicle->chassis_number }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Logbook No.</dt>
                        <dd class="font-mono text-gray-800">{{ $vehicle->logbook_number }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Initial Declared Value</dt>
                        <dd class="font-semibold text-gray-800">
                            KES {{ number_format($vehicle->initial_estimated_value, 2) }}
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- Vehicle specs --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h2 class="text-sm font-semibold text-green-800 mb-3">Specifications</h2>
                <dl class="text-sm space-y-2">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Make</dt>
                        <dd class="font-medium text-gray-800">{{ $vehicle->make }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Model</dt>
                        <dd class="font-medium text-gray-800">{{ $vehicle->model }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Year</dt>
                        <dd class="font-medium text-gray-800">{{ $vehicle->year_of_manufacture }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Vehicle Use</dt>
                        <dd class="font-medium text-gray-800">{{ $vehicle->vehicle_use->label() }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- ─── Owner ─── --}}
        <div class="mt-4 bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h2 class="text-sm font-semibold text-green-800 mb-3">Owner</h2>
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-medium text-gray-800">
                        {{ $vehicle->client->first_name }}
                        {{ $vehicle->client->other_names }}
                        {{ $vehicle->client->last_name }}
                    </p>
                    <p class="text-xs text-gray-500">
                        ID {{ $vehicle->client->national_id }} ·
                        {{ $vehicle->client->phone_number }} ·
                        {{ $vehicle->client->county }}
                    </p>
                </div>
                <a href="{{ route('clients.show', $vehicle->client) }}"
                    class="text-sm text-green-700 hover:text-green-900 font-medium">
                    View client →
                </a>
            </div>
        </div>

        {{-- ─── Valuation History ─── --}}
        <div class="mt-4 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-green-800">Valuation History</h2>
                @if ($vehicle->status === \App\Enums\VehicleStatus::PendingValuation)
                    <a href="{{ route('valuations.index', $vehicle) }}"
                        class="text-xs px-3 py-1.5 rounded-lg bg-green-600 hover:bg-green-700 text-white font-medium">
                        + Record Valuation
                    </a>
                @endif
            </div>

            @if ($vehicle->valuations->isEmpty())
                <div class="px-5 py-8 text-center text-sm text-gray-400">
                    No valuations on record.
                    @if ($vehicle->status === \App\Enums\VehicleStatus::PendingValuation)
                        <p class="mt-1">Send the vehicle for valuation to unlock policy issuance.</p>
                    @endif
                </div>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-lime-50 text-green-900 text-left text-xs uppercase tracking-wide">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Date</th>
                            <th class="px-5 py-3 font-semibold">Firm</th>
                            <th class="px-5 py-3 font-semibold">Valuer</th>
                            <th class="px-5 py-3 font-semibold text-right">ACV (KES)</th>
                            <th class="px-5 py-3 font-semibold text-right">FSV (KES)</th>
                            <th class="px-5 py-3 font-semibold">Type</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($vehicle->valuations->sortByDesc('valuation_date') as $valuation)
                            <tr class="hover:bg-lilac-50/40 transition">
                                <td class="px-5 py-3 text-gray-700">
                                    {{ $valuation->valuation_date->format('d M Y') }}
                                </td>
                                <td class="px-5 py-3 text-gray-700">{{ $valuation->valuation_firm }}</td>
                                <td class="px-5 py-3 text-gray-500">{{ $valuation->valuer_name ?? '—' }}</td>
                                <td class="px-5 py-3 text-right font-medium text-gray-800">
                                    {{ number_format($valuation->actual_cash_value, 2) }}
                                </td>
                                <td class="px-5 py-3 text-right text-gray-600">
                                    {{ $valuation->forced_sale_value ? number_format($valuation->forced_sale_value, 2) : '—' }}
                                </td>
                                <td class="px-5 py-3">
                                    <span
                                        class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        {{ $valuation->valuation_type->label() }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        {{-- ─── Action Buttons ─── --}}
        <div class="mt-6 flex flex-wrap gap-3">
            @if (
                    $vehicle->status === \App\Enums\VehicleStatus::PendingValuation
                    && auth()->user()->hasRole(\App\Enums\UserRole::Underwriter, \App\Enums\UserRole::Admin)
                )
                <a href="{{ route('valuations.create', $vehicle) }}"
                    class="px-5 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-medium shadow-sm">
                    Record Valuation
                </a>
            @endif


            @if ($vehicle->status === \App\Enums\VehicleStatus::Valued)
                <a href=" {{ route('policies.create', $vehicle) }}"
                    class="px-5 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-medium shadow-sm">
                    Issue Policy
                </a>
            @endif

            @if ($vehicle->status === \App\Enums\VehicleStatus::Insured)
                <a href="{{ route('policies.show', $vehicle->activePolicy) }}"
                    class="px-5 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-medium shadow-sm">
                    View Active Policy
                </a>
            @endif

            <a href="{{ route('vehicles.create', $vehicle->client) }}"
                class="px-5 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium">
                Add Another Vehicle
            </a>
        </div>
        {{-- ─── Authorized Drivers ─── --}}
        <div class="mt-4 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-green-800">
                    Authorized Drivers
                    <span class="ml-2 text-xs px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-800 font-semibold">
                        {{ $vehicle->drivers->count() }}
                    </span>
                </h2>
                <a href="{{ route('vehicles.drivers.create', $vehicle) }}"
                    class="text-xs px-3 py-1.5 rounded-lg bg-green-600 hover:bg-green-700 text-white font-medium">
                    + Add Driver
                </a>
            </div>

            @if ($vehicle->drivers->isEmpty())
                <div class="px-5 py-8 text-center text-sm text-gray-400">
                    No drivers on record.
                    @if ($vehicle->vehicle_use === \App\Enums\VehicleUse::Personal)
                        The vehicle owner will be added as the primary driver on onboarding.
                    @else
                        Add the first driver to authorize them on this vehicle.
                    @endif
                </div>
            @else
                <ul class="divide-y divide-gray-100">
                    @foreach ($vehicle->drivers as $driver)
                        <li class="px-5 py-3 flex items-center justify-between hover:bg-lilac-50/40 transition">
                            <div>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('vehicles.drivers.show', [$vehicle, $driver]) }}"
                                        class="font-medium text-green-700 hover:text-green-900">
                                        {{ $driver->name }}
                                    </a>
                                    @if ($driver->is_primary)
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-lime-100 text-lime-800 font-semibold">
                                            Primary
                                        </span>
                                    @endif
                                    @if ($driver->isYoungDriver())
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-800">
                                            Under 25
                                        </span>
                                    @endif
                                    @if ($driver->hasPsvLicence())
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-purple-100 text-purple-800">
                                            PSV
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    DL {{ $driver->dl_number }} · Class {{ $driver->dl_class }} ·
                                    {{ $driver->driving_experience }} yrs experience
                                </p>
                            </div>
                            <a href="{{ route('vehicles.drivers.show', [$vehicle, $driver]) }}"
                                class="text-xs text-green-700 hover:text-green-900 font-medium">
                                View →
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

    </div>
@endsection