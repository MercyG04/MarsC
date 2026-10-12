@extends('layouts.app')

@section('content')
    <div class="max-w-5xl mx-auto py-8 px-4">

        {{-- ─── Header ─── --}}
        <div class="mb-6 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
                <a href="{{ route('claims.index') }}" class="text-sm text-green-700 hover:text-green-900 font-medium">
                    ← Back to Claims
                </a>
                <h1 class="text-2xl font-bold text-green-800 mt-2">
                    Claim <span class="font-mono">{{ $claim->claim_number }}</span>
                </h1>
                <p class="text-sm text-gray-500">
                    {{ $claim->claim_type->label() }} ·
                    Reported {{ $claim->reported_at->format('d M Y, H:i') }}
                </p>
            </div>

            <div class="text-left sm:text-right">
                @php
                    $statusClasses = match ($claim->status) {
                        \App\Enums\ClaimStatus::Submitted => 'bg-gray-100 text-gray-700',
                        \App\Enums\ClaimStatus::Acknowledged => 'bg-yellow-100 text-yellow-800',
                        \App\Enums\ClaimStatus::UnderReview => 'bg-yellow-100 text-yellow-800',
                        \App\Enums\ClaimStatus::Assessed => 'bg-purple-100 text-purple-800',
                        \App\Enums\ClaimStatus::Approved => 'bg-lime-100 text-lime-800',
                        \App\Enums\ClaimStatus::Rejected => 'bg-red-100 text-red-800',
                        \App\Enums\ClaimStatus::Paid => 'bg-green-100 text-green-800',
                    };
                @endphp
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $statusClasses }}">
                    {{ $claim->status->label() }}
                </span>
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

        {{-- ─── Policy + Client + Vehicle cards ─── --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            {{-- Policy --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h2 class="text-xs font-semibold text-green-800 uppercase tracking-wide mb-3">Policy</h2>
                <dl class="text-sm space-y-1.5">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Number</dt>
                        <dd class="font-mono text-xs font-semibold text-gray-800">{{ $claim->policy->policy_number }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Cover</dt>
                        <dd class="text-gray-800">{{ $claim->policy->policy_type->label() }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Deductible</dt>
                        <dd class="font-medium text-gray-800">KES {{ number_format($claim->policy->deductible_amount, 2) }}
                        </dd>
                    </div>
                    <div class="pt-2 mt-2 border-t border-gray-100">
                        <a href="{{ route('policies.show', $claim->policy) }}"
                            class="text-xs text-green-700 hover:text-green-900 font-medium">View policy →</a>
                    </div>
                </dl>
            </div>

            {{-- Client --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h2 class="text-xs font-semibold text-green-800 uppercase tracking-wide mb-3">Client</h2>
                <dl class="text-sm space-y-1.5">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Name</dt>
                        <dd class="font-medium text-gray-800">
                            {{ $claim->policy->client->first_name }} {{ $claim->policy->client->last_name }}
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Phone</dt>
                        <dd class="text-gray-800">{{ $claim->policy->client->phone_number }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Email</dt>
                        <dd class="text-gray-800 truncate">{{ $claim->policy->client->email }}</dd>
                    </div>
                    <div class="pt-2 mt-2 border-t border-gray-100">
                        <a href="{{ route('clients.show', $claim->policy->client) }}"
                            class="text-xs text-green-700 hover:text-green-900 font-medium">View client →</a>
                    </div>
                </dl>
            </div>

            {{-- Vehicle --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h2 class="text-xs font-semibold text-green-800 uppercase tracking-wide mb-3">Vehicle</h2>
                <dl class="text-sm space-y-1.5">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Registration</dt>
                        <dd class="font-mono font-semibold text-gray-800">{{ $claim->policy->vehicle->registration_number }}
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Make & Model</dt>
                        <dd class="text-gray-800">{{ $claim->policy->vehicle->make }} {{ $claim->policy->vehicle->model }}
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Year</dt>
                        <dd class="text-gray-800">{{ $claim->policy->vehicle->year_of_manufacture }}</dd>
                    </div>
                    <div class="pt-2 mt-2 border-t border-gray-100">
                        <a href="{{ route('vehicles.show', $claim->policy->vehicle) }}"
                            class="text-xs text-green-700 hover:text-green-900 font-medium">View vehicle →</a>
                    </div>
                </dl>
            </div>
        </div>

        {{-- ─── Incident details ─── --}}
        <div class="mt-4 bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h2 class="text-xs font-semibold text-green-800 uppercase tracking-wide mb-4">Incident Details</h2>
            <dl class="text-sm space-y-2">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Incident Date</dt>
                    <dd class="text-gray-800 font-medium">{{ $claim->incident_date->format('d M Y') }}</dd>
                </div>
                @if ($claim->location)
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Location</dt>
                        <dd class="text-gray-800">{{ $claim->location }}</dd>
                    </div>
                @endif
                @if ($claim->police_abstract_ref)
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Police Abstract</dt>
                        <dd class="font-mono text-gray-800">{{ $claim->police_abstract_ref }}</dd>
                    </div>
                @endif
                @if ($claim->driver)
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Driver at Time</dt>
                        <dd class="text-gray-800">
                            {{ $claim->driver->name }}
                            @if ($claim->driver->is_primary)
                                <span class="text-xs text-lime-700 ml-1">(Primary)</span>
                            @endif
                        </dd>
                    </div>
                @else
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Driver at Time</dt>
                        <dd class="text-yellow-700 text-sm italic">Not in authorized list</dd>
                    </div>
                @endif
                <div class="pt-2 mt-2 border-t border-gray-100">
                    <dt class="text-gray-500 mb-1">Description</dt>
                    <dd class="text-gray-700 whitespace-pre-line">{{ $claim->description }}</dd>
                </div>
            </dl>
        </div>

        {{-- ─── Financial summary ─── --}}
        <div class="mt-4 bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h2 class="text-xs font-semibold text-green-800 uppercase tracking-wide mb-4">Financials</h2>
            <dl class="text-sm space-y-2">
                <div class="flex justify-between">
                    <dt class="text-gray-600">Amount Claimed</dt>
                    <dd class="text-gray-800">KES {{ number_format($claim->claimed_amount, 2) }}</dd>
                </div>
                @if ($claim->assessed_amount)
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Assessed Amount</dt>
                        <dd class="text-gray-800">KES {{ number_format($claim->assessed_amount, 2) }}</dd>
                    </div>
                @endif
                @if ($claim->approved_amount)
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Approved Amount</dt>
                        <dd class="text-gray-800">KES {{ number_format($claim->approved_amount, 2) }}</dd>
                    </div>
                @endif
                @if ($claim->deductible_applied)
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Deductible Applied</dt>
                        <dd class="text-gray-800">− KES {{ number_format($claim->deductible_applied, 2) }}</dd>
                    </div>
                @endif
                @if ($claim->salvage_value)
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Salvage Retained</dt>
                        <dd class="text-gray-800">− KES {{ number_format($claim->salvage_value, 2) }}</dd>
                    </div>
                @endif

                @if ($claim->netPayout() !== null)
                    <div class="flex justify-between border-t-2 border-green-200 pt-3 mt-3">
                        <dt class="font-semibold text-green-800">Net Payout</dt>
                        <dd class="font-bold text-green-800 text-xl">
                            KES {{ number_format($claim->netPayout(), 2) }}
                        </dd>
                    </div>
                @endif
            </dl>
        </div>

        {{-- ─── Rejection details (if rejected) ─── --}}
        @if ($claim->status === \App\Enums\ClaimStatus::Rejected)
            <div class="mt-4 bg-white rounded-xl shadow-sm border border-red-200 p-5">
                <h2 class="text-xs font-semibold text-red-800 uppercase tracking-wide mb-4">Rejection Details</h2>
                <dl class="text-sm space-y-2">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Rejected On</dt>
                        <dd class="text-gray-800">{{ $claim->rejected_at?->format('d M Y, H:i') }}</dd>
                    </div>
                    @if ($claim->rejection_reason)
                        <div>
                            <dt class="text-gray-500 mb-1">Reason</dt>
                            <dd class="text-gray-800 font-medium">
                                {{ $claim->rejection_reason->label() ?? $claim->rejection_reason }}
                            </dd>
                        </div>
                    @endif
                    @if ($claim->rejection_notes)
                        <div class="pt-2 mt-2 border-t border-gray-100">
                            <dt class="text-gray-500 mb-1">Notes</dt>
                            <dd class="text-gray-700 whitespace-pre-line">{{ $claim->rejection_notes }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        @endif

        {{-- ─── Documents (placeholder for now) ─── --}}
        <div class="mt-4 bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-xs font-semibold text-green-800 uppercase tracking-wide">Documents</h2>
                <span class="text-xs text-gray-400">{{ $claim->documents->count() }} attached</span>
            </div>

            @if ($claim->documents->isEmpty())
                <p class="text-sm text-gray-400">
                    No documents attached yet. Document upload will be available in a future update.
                </p>
            @else
                <ul class="divide-y divide-gray-100 text-sm">
                    @foreach ($claim->documents as $doc)
                        <li class="py-2 flex items-center justify-between">
                            <span class="font-medium text-gray-800">{{ $doc->type->label() ?? $doc->type }}</span>
                            <span class="text-xs text-gray-500">{{ $doc->original_filename }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>


        {{-- ─── Transitions ─── --}}


        <div class="mt-6">

            {{-- Acknowledge ── status Submitted --}}
            @if ($claim->status === \App\Enums\ClaimStatus::Submitted)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <h2 class="text-xs font-semibold text-green-800 uppercase tracking-wide mb-3">
                        Next Step
                    </h2>
                    <p class="text-sm text-gray-600 mb-4">
                        Acknowledge this claim to confirm the insurer has received it.
                        AKI guidelines require acknowledgment within 24 hours of filing.
                    </p>
                    <form method="POST" action="{{ route('claims.acknowledge', $claim) }}">
                        @csrf
                        <button type="submit"
                            class="px-5 py-2.5 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-medium shadow-sm">
                            Acknowledge Claim
                        </button>
                    </form>
                </div>
            @endif

            {{-- Assign & Begin Review ── status Acknowledged --}}
            @if ($claim->status === \App\Enums\ClaimStatus::Acknowledged)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <h2 class="text-xs font-semibold text-green-800 uppercase tracking-wide mb-3">
                        Assign to a Claims Officer
                    </h2>
                    <p class="text-sm text-gray-600 mb-4">
                        Assign this claim to the officer who will handle it. All intake documents
                        must be received before the claim moves to review.
                    </p>

                    @php
                        $officers = \App\Models\User::whereIn('role', [
                            \App\Enums\UserRole::ClaimsOfficer->value,
                            \App\Enums\UserRole::Underwriter->value,
                            \App\Enums\UserRole::Admin->value,
                        ])->orderBy('name')->get();
                    @endphp

                    <form method="POST" action="{{ route('claims.begin-review', $claim) }}" class="space-y-4">
                        @csrf

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Assign To *
                            </label>
                            <select name="assigned_to" required
                                class="w-full rounded-lg border-2 border-purple-200 bg-white px-4 py-3 text-base text-gray-800 focus:border-purple-500 focus:ring-4 focus:ring-purple-100 focus:outline-none transition">
                                <option value="">Select a claims officer…</option>
                                @foreach ($officers as $officer)
                                    <option value="{{ $officer->id }}" @selected(old('assigned_to') == $officer->id)>
                                        {{ $officer->name }} ({{ $officer->role->label() }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit"
                            class="px-5 py-2.5 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-medium shadow-sm">
                            Begin Review
                        </button>
                    </form>
                </div>
            @endif

            {{-- Record Assessment ── status UnderReview --}}
            @if ($claim->status === \App\Enums\ClaimStatus::UnderReview)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <h2 class="text-xs font-semibold text-green-800 uppercase tracking-wide mb-3">
                        Record Assessment
                    </h2>
                    <p class="text-sm text-gray-600 mb-4">
                        Enter the assessor's findings. This determines whether the claim is approved for
                        repair, written off, or settled for the total loss value.
                    </p>

                    <form method="POST" action="{{ route('claims.record-assessment', $claim) }}" class="space-y-4">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Assessed Amount (KES) *
                                </label>
                                <input type="number" name="assessed_amount" required min="0" step="0.01"
                                    value="{{ old('assessed_amount') }}"
                                    class="w-full rounded-lg border-2 border-purple-200 bg-white px-4 py-3 text-base text-gray-800 focus:border-purple-500 focus:ring-4 focus:ring-purple-100 focus:outline-none transition">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Adjustment Type *
                                </label>
                                <select name="adjustment_type" required
                                    class="w-full rounded-lg border-2 border-purple-200 bg-white px-4 py-3 text-base text-gray-800 focus:border-purple-500 focus:ring-4 focus:ring-purple-100 focus:outline-none transition">
                                    <option value="">Select…</option>
                                    <option value="repair" @selected(old('adjustment_type') === 'repair')>Repair</option>
                                    <option value="write_off" @selected(old('adjustment_type') === 'write_off')>Write-Off</option>
                                    <option value="cash_in_lieu" @selected(old('adjustment_type') === 'cash_in_lieu')>Cash in Lieu
                                    </option>
                                    <option value="total_loss" @selected(old('adjustment_type') === 'total_loss')>Total Loss
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Salvage Value (KES)
                                </label>
                                <input type="number" name="salvage_value" min="0" step="0.01" value="{{ old('salvage_value') }}"
                                    class="w-full rounded-lg border-2 border-purple-200 bg-white px-4 py-3 text-base text-gray-800 focus:border-purple-500 focus:ring-4 focus:ring-purple-100 focus:outline-none transition">
                                <p class="text-xs text-gray-500 mt-1.5">
                                    Only if the client retains the salvage.
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Assessor
                                </label>
                                <select name="assessor_id"
                                    class="w-full rounded-lg border-2 border-purple-200 bg-white px-4 py-3 text-base text-gray-800 focus:border-purple-500 focus:ring-4 focus:ring-purple-100 focus:outline-none transition">
                                    <option value="">Current user</option>
                                    @foreach ($officers as $officer)
                                        <option value="{{ $officer->id }}" @selected(old('assessor_id') == $officer->id)>
                                            {{ $officer->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Notes
                                </label>
                                <textarea name="notes" rows="3" placeholder="Any additional notes from the assessor's report."
                                    class="w-full rounded-lg border-2 border-purple-200 bg-white px-4 py-3 text-base text-gray-800 placeholder-gray-400 focus:border-purple-500 focus:ring-4 focus:ring-purple-100 focus:outline-none transition">{{ old('notes') }}</textarea>
                            </div>
                        </div>

                        <button type="submit"
                            class="px-5 py-2.5 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-medium shadow-sm">
                            Record Assessment
                        </button>
                    </form>
                </div>
            @endif

            {{-- Next session: Assessed, Approved, Rejected, Paid states --}}

        </div>

    </div>
@endsection