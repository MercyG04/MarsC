@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto py-8 px-4">

        {{-- ─── Header ─── --}}
        <div class="mb-6">
            <a href="{{ route('policies.show', $policy) }}" class="text-sm text-green-700 hover:text-green-900 font-medium">
                ← Back to policy
            </a>
            <h1 class="text-2xl font-bold text-green-800 mt-2">File a Claim</h1>
            <p class="text-sm text-gray-500">
                For policy <span class="font-mono">{{ $policy->policy_number }}</span>
                — {{ $policy->client->first_name }} {{ $policy->client->last_name }}
            </p>
        </div>

        {{-- ─── Vehicle context card ─── --}}
        <div class="mb-6 bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h2 class="text-xs font-semibold text-green-800 uppercase tracking-wide mb-3">Insured Vehicle</h2>
            <dl class="text-sm space-y-1.5">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Registration</dt>
                    <dd class="font-mono font-semibold text-gray-800">{{ $policy->vehicle->registration_number }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Make & Model</dt>
                    <dd class="text-gray-800">{{ $policy->vehicle->make }} {{ $policy->vehicle->model }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Cover Type</dt>
                    <dd class="text-gray-800">{{ $policy->policy_type->label() }}</dd>
                </div>
                <div class="flex justify-between border-t border-gray-100 pt-1.5 mt-1.5">
                    <dt class="text-gray-500">Deductible</dt>
                    <dd class="font-semibold text-gray-800">
                        KES {{ number_format($policy->deductible_amount, 2) }}
                    </dd>
                </div>
            </dl>
        </div>

        @if ($errors->any())
            <div class="mb-4 p-4 rounded-lg bg-yellow-50 border border-yellow-200 text-yellow-900 text-sm">
                <strong>Please fix the following:</strong>
                <ul class="list-disc list-inside mt-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('claims.store', $policy) }}" class="space-y-6">
            @csrf

            {{-- ─── Section 1 — Incident ─── --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center gap-2 mb-4">
                    <span
                        class="w-6 h-6 flex items-center justify-center rounded-full bg-green-100 text-green-800 text-xs font-bold">1</span>
                    <h2 class="text-sm font-semibold text-green-800 uppercase tracking-wide">Incident Details</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Incident Date *
                        </label>
                        <input type="date" name="incident_date" value="{{ old('incident_date') }}"
                            max="{{ now()->toDateString() }}" required
                            class="w-full rounded-lg border-2 border-purple-200 bg-white px-4 py-3 text-base text-gray-800 focus:border-purple-500 focus:ring-4 focus:ring-purple-100 focus:outline-none transition">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Claim Type *
                        </label>
                        <select name="claim_type" required
                            class="w-full rounded-lg border-2 border-purple-200 bg-white px-4 py-3 text-base text-gray-800 focus:border-purple-500 focus:ring-4 focus:ring-purple-100 focus:outline-none transition">
                            <option value="">Select…</option>
                            @foreach ($claimTypes as $type)
                                <option value="{{ $type->value }}" @selected(old('claim_type') === $type->value)>
                                    {{ $type->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Location
                        </label>
                        <input type="text" name="location" value="{{ old('location') }}" maxlength="200"
                            placeholder="Where did the incident occur?"
                            class="w-full rounded-lg border-2 border-purple-200 bg-white px-4 py-3 text-base text-gray-800 placeholder-gray-400 focus:border-purple-500 focus:ring-4 focus:ring-purple-100 focus:outline-none transition">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Police Abstract Reference
                        </label>
                        <input type="text" name="police_abstract_ref" value="{{ old('police_abstract_ref') }}"
                            maxlength="100" placeholder="OB/XXXX/YYYY"
                            class="w-full rounded-lg border-2 border-purple-200 bg-white px-4 py-3 text-base text-gray-800 placeholder-gray-400 uppercase focus:border-purple-500 focus:ring-4 focus:ring-purple-100 focus:outline-none transition">
                        <p class="text-xs text-gray-500 mt-1.5">
                            If you don't have the abstract yet, leave blank and add it later.
                        </p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Description of the Incident *
                        </label>
                        <textarea name="description" rows="5" required minlength="20" maxlength="2000"
                            placeholder="Describe what happened in detail — the circumstances, sequence of events, and any witnesses."
                            class="w-full rounded-lg border-2 border-purple-200 bg-white px-4 py-3 text-base text-gray-800 placeholder-gray-400 focus:border-purple-500 focus:ring-4 focus:ring-purple-100 focus:outline-none transition">{{ old('description') }}</textarea>
                        <p class="text-xs text-gray-500 mt-1.5">
                            Minimum 20 characters. This is the official record of what happened.
                        </p>
                    </div>
                </div>
            </div>

            {{-- ─── Section 2 — Financial ─── --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center gap-2 mb-4">
                    <span
                        class="w-6 h-6 flex items-center justify-center rounded-full bg-green-100 text-green-800 text-xs font-bold">2</span>
                    <h2 class="text-sm font-semibold text-green-800 uppercase tracking-wide">Claim Amount</h2>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Amount Being Claimed (KES) *
                    </label>
                    <input type="number" name="claimed_amount" value="{{ old('claimed_amount') }}" required min="1000"
                        step="0.01" placeholder="450000"
                        class="w-full rounded-lg border-2 border-purple-200 bg-white px-4 py-3 text-base text-gray-800 placeholder-gray-400 focus:border-purple-500 focus:ring-4 focus:ring-purple-100 focus:outline-none transition">
                    <p class="text-xs text-gray-500 mt-1.5">
                        This is your initial estimate. The final amount is determined by the assessor's report.
                        Your deductible of KES {{ number_format($policy->deductible_amount, 2) }} will be applied to any
                        payout.
                    </p>
                </div>
            </div>

            {{-- ─── Section 3 — Driver ─── --}}
            @if ($policy->vehicle->drivers->isNotEmpty())
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <span
                            class="w-6 h-6 flex items-center justify-center rounded-full bg-green-100 text-green-800 text-xs font-bold">3</span>
                        <h2 class="text-sm font-semibold text-green-800 uppercase tracking-wide">Driver at Time of Incident</h2>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Who was driving?
                        </label>
                        <select name="driver_id"
                            class="w-full rounded-lg border-2 border-purple-200 bg-white px-4 py-3 text-base text-gray-800 focus:border-purple-500 focus:ring-4 focus:ring-purple-100 focus:outline-none transition">
                            <option value="">Not in the authorized list (or unknown)</option>
                            @foreach ($policy->vehicle->drivers as $driver)
                                <option value="{{ $driver->id }}" @selected(old('driver_id') == $driver->id)>
                                    {{ $driver->name }}
                                    @if ($driver->is_primary) — Primary @endif
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1.5">
                            If the driver at the time of the incident is not in this list, leave blank —
                            the claims officer will review the case.
                        </p>
                    </div>
                </div>
            @endif

            {{-- ─── Actions ─── --}}
            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-2">
                <a href="{{ route('policies.show', $policy) }}"
                    class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium text-center">
                    Cancel
                </a>
                <button type="submit"
                    class="px-6 py-2.5 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-medium shadow-sm">
                    File Claim
                </button>
            </div>
        </form>
    </div>
@endsection