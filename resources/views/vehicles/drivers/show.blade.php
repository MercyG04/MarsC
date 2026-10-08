@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto py-8 px-4">

        <div class="mb-6">
            <a href="{{ route('vehicles.show', $vehicle) }}"
                class="text-sm text-green-700 hover:text-green-900 font-medium">
                ← Back to {{ $vehicle->make }} {{ $vehicle->model }}
            </a>
            <h1 class="text-2xl font-bold text-green-800 mt-2">{{ $driver->name }}</h1>
            <p class="text-sm text-gray-500">
                Authorized driver of
                <span class="font-mono">{{ $vehicle->registration_number }}</span>
                @if ($driver->is_primary)
                    · <span class="text-lime-700 font-medium">Primary Driver</span>
                @endif
            </p>
        </div>

        @if (session('success'))
            <div class="mb-4 p-3 rounded-lg bg-lime-50 border border-lime-200 text-lime-800 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 p-3 rounded-lg bg-yellow-50 border border-yellow-200 text-yellow-900 text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Identity card --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-xs font-semibold text-green-800 uppercase tracking-wide">Identity</h2>
                <span class="text-xs text-gray-400">Read-only</span>
            </div>
            <dl class="text-sm space-y-2">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Full Name</dt>
                    <dd class="font-medium text-gray-800">{{ $driver->name }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">National ID</dt>
                    <dd class="font-mono text-gray-800">{{ $driver->national_id }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Date of Birth</dt>
                    <dd class="text-gray-800">
                        {{ $driver->date_of_birth->format('d M Y') }}
                        <span class="text-xs text-gray-500">({{ $driver->age }} yrs)</span>
                    </dd>
                </div>
            </dl>
        </div>

        {{-- Licence card --}}
        <div class="mt-4 bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h2 class="text-xs font-semibold text-green-800 uppercase tracking-wide mb-3">
                Driving Licence
            </h2>
            <dl class="text-sm space-y-2">
                <div class="flex justify-between">
                    <dt class="text-gray-500">DL Number</dt>
                    <dd class="font-mono text-gray-800">{{ $driver->dl_number }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Class</dt>
                    <dd class="text-gray-800">
                        {{ $driver->dl_class }}
                        @if ($driver->hasPsvLicence())
                            <span class="ml-2 text-xs px-2 py-0.5 rounded-full bg-purple-100 text-purple-800">
                                PSV Licensed
                            </span>
                        @endif
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Experience</dt>
                    <dd class="text-gray-800">{{ $driver->driving_experience }} years</dd>
                </div>
            </dl>
        </div>

        {{-- Primary driver toggle --}}
        @php
            $hasOtherPrimary = $vehicle->drivers()->where('is_primary', true)->where('id', '!=', $driver->id)->exists();
        @endphp

        <div class="mt-4 bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h2 class="text-xs font-semibold text-green-800 uppercase tracking-wide mb-3">
                Primary Driver Status
            </h2>
            @if ($driver->is_primary)
                <p class="text-sm text-gray-700 mb-3">
                    This driver is currently marked as the <strong>primary driver</strong>.
                </p>
                @if ($vehicle->drivers->where('is_primary', true)->count() > 1)
                    <p class="text-xs text-yellow-700 mb-3">
                        ⚠ Multiple primary drivers detected — this shouldn't happen.
                    </p>
                @endif
            @else
                <p class="text-sm text-gray-700 mb-3">
                    This driver is not the primary driver.
                </p>
                <form method="POST" action="{{ route('vehicles.drivers.update', [$vehicle, $driver]) }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="is_primary" value="1">
                    <button type="submit"
                        class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-medium shadow-sm">
                        Make Primary Driver
                    </button>
                </form>
            @endif
        </div>

        {{-- Immutability notice --}}
        <div class="mt-6 rounded-lg bg-lilac-50 border border-purple-200 p-4 text-xs text-gray-600">
            <strong class="text-green-800">Driver details are immutable.</strong>
            Name, ID, DL number, and licence class are verified against IPRS and NTSA and cannot be edited.
            To change these, remove driver via endorsement.
        </div>

    </div>
@endsection