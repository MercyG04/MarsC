@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto py-8 px-4">

        {{-- Header --}}
        <div class="mb-6">
            <a href="{{ route('clients.show', $client) }}" class="text-sm text-green-700 hover:text-green-900 font-medium">
                ← Back to client
            </a>
            <h1 class="text-2xl font-bold text-green-800 mt-2">Onboard a Vehicle</h1>
            <p class="text-sm text-gray-500">
                For client
                <strong>{{ $client->first_name }} {{ $client->last_name }}</strong>
                — the vehicle will be verified against NTSA TIMS and queued for valuation.
            </p>
        </div>

        {{-- Validation errors --}}
        @if ($errors->any())
            <div class="mb-4 p-3 rounded-lg bg-yellow-50 border border-yellow-200 text-yellow-900 text-sm">
                <strong>Please fix the following:</strong>
                <ul class="list-disc list-inside mt-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('vehicles.store', $client) }}"
            class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-5">
            @csrf

            {{-- Step 1 — NTSA verification hint --}}
            <div class="rounded-lg bg-lime-50 border border-lime-200 p-4">
                <h2 class="text-sm font-semibold text-green-800 mb-3">
                    Step 1 — NTSA TIMS Verification
                </h2>
                <p class="text-xs text-gray-600 mb-3">
                    Enter the registration number exactly as it appears on the logbook.
                    The system will call NTSA TIMS to verify the vehicle, confirm the chassis,
                    and flag any stolen or duty-unpaid records.
                </p>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Registration Number *
                    </label>
                    <input type="text" name="registration_number" value="{{ old('registration_number') }}" required
                        placeholder="e.g. KDA777Z"
                        class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm uppercase tracking-wider">
                    <p class="text-xs text-gray-500 mt-1">
                        Try <code class="bg-lime-100 px-1 rounded">KDA777Z</code>
                        or <code class="bg-lime-100 px-1 rounded">KDB345</code> to see a successful verification.
                    </p>
                </div>
            </div>

            {{-- Step 2 — Vehicle details --}}
            <h2 class="text-sm font-semibold text-green-800">Step 2 — Vehicle Details</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Chassis Number *
                    </label>
                    <input type="text" name="chassis_number" value="{{ old('chassis_number') }}" required
                        placeholder="e.g. NCP1234567890"
                        class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm uppercase">
                    <p class="text-xs text-gray-500 mt-1">
                        Must match what NTSA has on file — otherwise the vehicle is flagged.
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Logbook Number *
                    </label>
                    <input type="text" name="logbook_number" value="{{ old('logbook_number') }}" required
                        class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm uppercase">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Make *</label>
                    <input type="text" name="make" value="{{ old('make') }}" required placeholder="Toyota"
                        class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Model *</label>
                    <input type="text" name="model" value="{{ old('model') }}" required placeholder="Hilux"
                        class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Year of Manufacture *
                    </label>
                    <input type="number" name="year_of_manufacture" min="1950" max="{{ date('Y') + 1 }}"
                        value="{{ old('year_of_manufacture') }}" required
                        class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Declared Value (KES) *
                    </label>
                    <input type="number" step="0.01" name="initial_estimated_value"
                        value="{{ old('initial_estimated_value') }}" required min="10000" placeholder="2000000"
                        class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">
                    <p class="text-xs text-gray-500 mt-1">
                        Preliminary — the final value comes from the valuer.
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Vehicle Use *
                    </label>
                    <select name="vehicle_use" required
                        class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">
                        <option value="">Select…</option>
                        <option value="private" @selected(old('vehicle_use') === 'private')>Private</option>
                        <option value="commercial" @selected(old('vehicle_use') === 'commercial')>Commercial</option>
                        <option value="psv" @selected(old('vehicle_use') === 'psv')>PSV (Public Service Vehicle)</option>
                    </select>
                </div>
            </div>

            {{-- What happens next --}}
            <div class="rounded-lg bg-lilac-50 border border-purple-200 p-4 text-sm text-gray-700">
                <strong class="block text-green-800 mb-2">What happens next:</strong>
                <ol class="list-decimal list-inside space-y-1">
                    <li>The vehicle is saved with status <em>Pending Valuation</em>.</li>
                    <li>NTSA TIMS is queried to verify the chassis and flag stolen/duty-unpaid records.</li>
                    <li>An authorization letter is prepared for the valuer.</li>
                    <li>Once the valuation is submitted, the vehicle flips to <em>Valued</em>.</li>
                    <li>A policy can then be quoted and issued.</li>
                </ol>
            </div>

            {{-- Actions --}}
            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('clients.show', $client) }}"
                    class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium">
                    Cancel
                </a>
                <button type="submit"
                    class="px-5 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-medium shadow-sm">
                    Onboard Vehicle
                </button>
            </div>
        </form>
    </div>
@endsection