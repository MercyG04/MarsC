@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto py-8 px-4">

        <div class="mb-6">
            <a href="{{ route('clients.show', $client) }}" class="text-sm text-green-700 hover:text-green-900 font-medium">
                ← Back Clients page
            </a>
            <h1 class="text-2xl font-bold text-green-800 mt-2">Add a Vehicle</h1>
            <p class="text-sm text-gray-500">
                For <strong>{{ $client->first_name }} {{ $client->last_name }}</strong>
                — the vehicle will be verified against NTSA TIMS.
            </p>
        </div>

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

            {{-- ─── Step 1 — NTSA Verification ─── --}}
            <div class="rounded-lg bg-lime-50 border border-lime-200 p-4">
                <h2 class="text-sm font-semibold text-green-800 mb-3">Step 1 — NTSA TIMS Verification</h2>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Registration Number *
                    </label>
                    <div class="flex gap-2">
                        <input type="text" name="registration_number" id="registration_number"
                            value="{{ old('registration_number') }}" required placeholder="e.g. KDA777Z"
                            class="flex-1 rounded-lg border border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm uppercase">
                        <button type="button" onclick="verifyVehicle()" id="verify-vehicle-btn"
                            class="px-4 py-2 rounded-lg bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium shrink-0">
                            Verify
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        Try <code class="bg-lime-100 px-1 rounded">KDA777Z</code> or
                        <code class="bg-lime-100 px-1 rounded">KDB345</code>.
                    </p>
                    <p id="vehicle-status" class="text-xs mt-1"></p>
                </div>
            </div>

            {{-- ─── Step 2 — Vehicle details ─── --}}
            <h2 class="text-sm font-semibold text-green-800">Step 2 — Vehicle Details</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Chassis Number *</label>
                    <input type="text" name="chassis_number" id="chassis_number" value="{{ old('chassis_number') }}"
                        required
                        class="w-full rounded-lg border border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm uppercase">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Logbook Number *</label>
                    <input type="text" name="logbook_number" id="logbook_number" value="{{ old('logbook_number') }}"
                        required
                        class="w-full rounded-lg border border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm uppercase">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Make *</label>
                    <input type="text" name="make" id="make" value="{{ old('make') }}" required
                        class="w-full rounded-lg border border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Model *</label>
                    <input type="text" name="model" id="model" value="{{ old('model') }}" required
                        class="w-full rounded-lg border border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Year of Manufacture *</label>
                    <input type="number" name="year_of_manufacture" id="year_of_manufacture"
                        value="{{ old('year_of_manufacture') }}" required min="1950" max="{{ date('Y') + 1 }}"
                        class="w-full rounded-lg border border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Declared Value (KES) *</label>
                    <input type="number" name="initial_estimated_value" value="{{ old('initial_estimated_value') }}"
                        required min="10000" step="0.01"
                        class="w-full rounded-lg border border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">
                    <p class="text-xs text-gray-500 mt-1">Preliminary — the final value comes from the valuer.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Vehicle Use *</label>
                    <select name="vehicle_use" required
                        class="w-full rounded-lg border border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">
                        <option value="">Select…</option>
                        <option value="personal" @selected(old('vehicle_use') === 'personal')>Private</option>
                        <option value="commercial" @selected(old('vehicle_use') === 'commercial')>Commercial</option>
                        <option value="psv" @selected(old('vehicle_use') === 'psv')>PSV (Public Service Vehicle)</option>
                    </select>
                </div>
            </div>

            <div class="rounded-lg bg-lilac-50 border border-purple-200 p-4 text-sm text-gray-700">
                <strong class="block text-green-800 mb-1">What happens next:</strong>
                <ol class="list-decimal list-inside space-y-1">
                    <li>The vehicle is saved with status <em>Pending Valuation</em>.</li>
                    <li>An underwriter records the assessor's valuation.</li>
                    <li>Once valued, a policy can be quoted and issued.</li>
                </ol>
            </div>

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

    <script>
        async function verifyVehicle() {
            const regNumber = document.getElementById('registration_number').value.trim();
            const status = document.getElementById('vehicle-status');
            const btn = document.getElementById('verify-vehicle-btn');

            if (!regNumber) {
                status.textContent = 'Enter a registration number first.';
                status.className = 'text-xs mt-1 text-yellow-700';
                return;
            }

            btn.disabled = true;
            btn.textContent = 'Verifying…';
            status.textContent = '';

            try {
                const response = await fetch('{{ route('verify.vehicle') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ registration_number: regNumber }),
                });

                const result = await response.json();

                if (!result.ok) {
                    status.textContent = result.error || 'Vehicle not found in NTSA TIMS.';
                    status.className = 'text-xs mt-1 text-red-700';
                    return;
                }

                // Auto-fill the vehicle fields
                fillField('chassis_number', result.data.chassis_number);
                fillField('logbook_number', result.data.logbook_number);
                fillField('make', result.data.make);
                fillField('model', result.data.model);
                fillField('year_of_manufacture', result.data.year_of_manufacture);

                status.textContent = '✓ Verified with NTSA TIMS.';
                status.className = 'text-xs mt-1 text-green-700 font-medium';
            } catch (error) {
                status.textContent = 'Verification failed. Try again.';
                status.className = 'text-xs mt-1 text-red-700';
            } finally {
                btn.disabled = false;
                btn.textContent = 'Verify';
            }
        }

        function fillField(name, value) {
            const field = document.querySelector(`[name="${name}"]`);
            if (field && value !== undefined && value !== null) {
                field.value = value;
            }
        }
    </script>
@endsection