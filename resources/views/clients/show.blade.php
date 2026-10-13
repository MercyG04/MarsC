@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto py-8 px-4">

        {{-- ─── Header ─── --}}
        <div class="mb-6 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
                <a href="{{ route('clients.index') }}" class="text-sm text-green-700 hover:text-green-900 font-medium">
                    ← Back
                </a>
                <h1 class="text-2xl font-bold text-green-800 mt-2">
                    {{ $client->first_name }} {{ $client->other_names }} {{ $client->last_name }}
                </h1>
                <p class="text-sm text-gray-500">Client #{{ $client->id }}</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('vehicles.create', $client) }}"
                    class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-medium shadow-sm">
                    + Add Vehicle
                </a>
            </div>
        </div>

        {{-- ─── Flash ─── --}}
        @if (session('success'))
            <div class="mb-4 p-3 rounded-lg bg-lime-50 border border-lime-200 text-lime-800 text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- ─── Personal + Contact cards ─── --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h2 class="text-xs font-semibold text-green-800 uppercase tracking-wide mb-3">Personal</h2>
                <dl class="text-sm space-y-2">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">National ID</dt>
                        <dd class="font-mono font-medium text-gray-800">{{ $client->national_id }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">KRA PIN</dt>
                        <dd class="font-mono font-medium text-gray-800">{{ $client->kra_pin }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Date of Birth</dt>
                        <dd class="font-medium text-gray-800">{{ $client->date_of_birth?->format('d M Y') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Gender</dt>
                        <dd class="font-medium text-gray-800 capitalize">{{ $client->gender }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Driving Experience</dt>
                        <dd class="font-medium text-gray-800">{{ $client->driving_experience }} yrs</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h2 class="text-xs font-semibold text-green-800 uppercase tracking-wide mb-3">Contact</h2>
                <dl class="text-sm space-y-2">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Phone</dt>
                        <dd class="font-medium text-gray-800">{{ $client->phone_number }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Email</dt>
                        <dd class="font-medium text-gray-800 truncate">{{ $client->email }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">County</dt>
                        <dd class="font-medium text-gray-800">{{ $client->county }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Location</dt>
                        <dd class="font-medium text-gray-800">{{ $client->physical_location }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- ─── Vehicles ─── --}}
        <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h2 class="text-xs font-semibold text-green-800 uppercase tracking-wide">
                    Vehicles
                    <span class="ml-2 text-xs px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-800 font-semibold">
                        {{ $client->vehicles->count() }}
                    </span>
                </h2>
                <a href="{{ route('vehicles.create', $client) }}"
                    class="text-xs px-3 py-1.5 rounded-lg bg-green-600 hover:bg-green-700 text-white font-medium">
                    + Add Vehicle
                </a>
            </div>

            @if ($client->vehicles->isEmpty())
                <div class="px-5 py-8 text-center text-sm text-gray-400">
                    No vehicles on record.
                    <a href="{{ route('vehicles.create', $client) }}"
                        class="text-green-700 hover:text-green-900 font-medium ml-1">
                        Add the first vehicle →
                    </a>
                </div>
            @else
                <ul class="divide-y divide-gray-100 text-sm">
                    @foreach ($client->vehicles as $vehicle)
                        <li class="px-5 py-3 hover:bg-lilac-50/40 transition">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                <div>
                                    <div class="font-medium text-gray-800">
                                        {{ $vehicle->year_of_manufacture }} {{ $vehicle->make }} {{ $vehicle->model }}
                                    </div>
                                    <div class="text-xs text-gray-500 mt-0.5">
                                        <span class="font-mono">{{ $vehicle->registration_number }}</span>
                                        · {{ $vehicle->vehicle_use->label() }}
                                        @if ($vehicle->ntsa_verified)
                                            <span class="ml-1 text-green-700">✓ NTSA</span>
                                        @else
                                            <span class="ml-1 text-yellow-700">⚠ Unverified</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-xs px-2 py-0.5 rounded-full
                                                            @if ($vehicle->status === \App\Enums\VehicleStatus::PendingValuation) bg-yellow-100 text-yellow-800
                                                            @elseif ($vehicle->status === \App\Enums\VehicleStatus::Valued) bg-lime-100 text-lime-800
                                                            @elseif ($vehicle->status === \App\Enums\VehicleStatus::Insured) bg-green-100 text-green-800
                                                            @else bg-gray-100 text-gray-700
                                                            @endif">
                                        {{ $vehicle->status->label() }}
                                    </span>
                                    <a href="{{ route('vehicles.show', $vehicle) }}"
                                        class="text-green-700 hover:text-green-900 font-medium">
                                        View →
                                    </a>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- ─── Consent ─── --}}
        @if ($client->consent_given)
            <div class="mt-4 p-3 rounded-lg bg-lime-50 border border-lime-200 text-xs text-lime-800">
                Consent given on {{ $client->consent_given_at?->format('d M Y, H:i') }}.
            </div>
        @endif
    </div>
@endsection