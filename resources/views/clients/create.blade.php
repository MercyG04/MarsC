@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto py-8 px-4">

        <a href="{{ route('clients.index') }}" class="text-sm text-green-700 hover:text-green-900 font-medium">
            ← Back to Clients
        </a>

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-green-800">Onboard a New Client</h1>
            <p class="text-sm text-gray-500">
                Enter the national ID and DL number — the system will pull the rest from IPRS and NTSA.
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

        <form method="POST" action="{{ route('clients.store') }}"
            class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-5">
            @csrf

            {{-- Verification section --}}
            <div class="rounded-lg bg-lime-50 border border-lime-200 p-4">
                <h2 class="text-sm font-semibold text-green-800 mb-3">Step 1 — Identity Verification</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">National ID *</label>
                        <div class="flex gap-2">
                            <input type="text" name="national_id" id="national_id" value="{{ old('national_id') }}" required
                                class="flex-1 rounded-lg border border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">
                            <button type="button" onclick="verifyIprs()" id="verify-iprs-btn"
                                class="px-4 py-2 rounded-lg bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium shrink-0">
                                Verify
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Try 12345678 or 87654321</p>
                        <p id="iprs-status" class="text-xs mt-1"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Driving Licence No.</label>
                        <div class="flex gap-2">
                            <input type="text" name="dl_number" id="dl_number" value="{{ old('dl_number') }}"
                                class="flex-1 rounded-lg border border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">
                            <button type="button" onclick="verifyDriver()" id="verify-driver-btn"
                                class="px-4 py-2 rounded-lg bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium shrink-0">
                                Verify
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Try DL-001, DL-002, or DL-003</p>
                        <p id="driver-status" class="text-xs mt-1"></p>
                    </div>
                </div>
            </div>

            {{-- Client details --}}
            <h2 class="text-sm font-semibold text-green-800">Step 2 — Client Details</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">First Name *</label>
                    <input type="text" name="first_name" value="{{ old('first_name') }}" required
                        class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Last Name *</label>
                    <input type="text" name="last_name" value="{{ old('last_name') }}" required
                        class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Other Names</label>
                    <input type="text" name="other_names" value="{{ old('other_names') }}"
                        class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">KRA PIN *</label>
                    <input type="text" name="kra_pin" value="{{ old('kra_pin') }}" required
                        class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone *</label>
                    <input type="text" name="phone_number" value="{{ old('phone_number') }}" required
                        class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth *</label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" required
                        class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Gender *</label>
                    <select name="gender" required
                        class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">
                        <option value="">Select…</option>
                        <option value="male" @selected(old('gender') === 'male')>Male</option>
                        <option value="female" @selected(old('gender') === 'female')>Female</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">County *</label>
                    <input type="text" name="county" value="{{ old('county') }}" required
                        class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Physical Location *</label>
                    <input type="text" name="physical_location" value="{{ old('physical_location') }}" required
                        class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Occupation</label>
                    <input type="text" name="occupation" value="{{ old('occupation') }}"
                        class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Driving Experience (years) *</label>
                    <input type="number" name="driving_experience" min="0" max="80"
                        value="{{ old('driving_experience', 0) }}" required
                        class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">
                </div>
            </div>

            {{-- Consent --}}
            <div class="rounded-lg bg-lilac-50 border border-purple-200 p-4">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="consent_given" value="1" @checked(old('consent_given'))
                        class="mt-1 rounded border-gray-300 text-green-600 focus:ring-green-500">
                    <span class="text-sm text-gray-700">
                        The client consents to the collection and processing of their personal data in accordance
                        with the Data Protection Act, 2019.
                    </span>
                </label>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('clients.index') }}"
                    class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium">
                    Cancel
                </a>
                <button type="submit"
                    class="px-5 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-medium shadow-sm">
                    Onboard Client
                </button>
            </div>
        </form>
    </div>

    <script>
        async function verifyIprs() {
            const nationalId = document.getElementById('national_id').value.trim();
            const status = document.getElementById('iprs-status');
            const btn = document.getElementById('verify-iprs-btn');

            if (!nationalId) {
                status.textContent = 'Enter a national ID first.';
                status.className = 'text-xs mt-1 text-yellow-700';
                return;
            }

            btn.disabled = true;
            btn.textContent = 'Verifying…';
            status.textContent = '';

            try {
                const response = await fetch('{{ route('verify.iprs') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ national_id: nationalId }),
                });

                const result = await response.json();

                if (!result.ok) {
                    status.textContent = result.error || 'Not found.';
                    status.className = 'text-xs mt-1 text-red-700';
                    return;
                }

                // Fill in the form fields
                fillField('first_name', result.data.first_name);
                fillField('last_name', result.data.last_name);
                fillField('other_names', result.data.other_names);
                fillField('kra_pin', result.data.kra_pin);
                fillField('date_of_birth', result.data.date_of_birth);
                fillField('gender', result.data.gender);

                status.textContent = '✓ Verified with IPRS.';
                status.className = 'text-xs mt-1 text-green-700 font-medium';
            } catch (error) {
                status.textContent = 'Verification failed. Try again.';
                status.className = 'text-xs mt-1 text-red-700';
            } finally {
                btn.disabled = false;
                btn.textContent = 'Verify';
            }
        }

        async function verifyDriver() {
            const dlNumber = document.getElementById('dl_number').value.trim();
            const status = document.getElementById('driver-status');
            const btn = document.getElementById('verify-driver-btn');

            if (!dlNumber) {
                status.textContent = 'Enter a DL number first.';
                status.className = 'text-xs mt-1 text-yellow-700';
                return;
            }

            btn.disabled = true;
            btn.textContent = 'Verifying…';
            status.textContent = '';

            try {
                const response = await fetch('{{ route('verify.driver') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ dl_number: dlNumber }),
                });

                const result = await response.json();

                if (!result.ok) {
                    status.textContent = result.error || 'Not found.';
                    status.className = 'text-xs mt-1 text-red-700';
                    return;
                }

                // Fill in driving experience
                fillField('driving_experience', result.data.driving_experience);

                status.textContent = `✓ Verified with NTSA. Class ${result.data.dl_class}.`;
                status.className = 'text-xs mt-1 text-green-700 font-medium';
            } catch (error) {
                status.textContent = 'Verification failed. Try again.';
                status.className = 'text-xs mt-1 text-red-700';
            } finally {
                btn.disabled = false;
                btn.textContent = 'Verify';
            }
        }

        /**
         * Helper — set a form field's value by name.
         */
        function fillField(name, value) {
            const field = document.querySelector(`[name="${name}"]`);
            if (field && value !== undefined && value !== null) {
                field.value = value;
            }
        }
    </script>
@endsection