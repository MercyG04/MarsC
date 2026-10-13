@extends('layouts.app')

@section('content')
    <div class="max-w-5xl mx-auto py-6 sm:py-8 px-4">

        {{-- ─── Header ─── --}}
        <div class="mb-6 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div class="min-w-0">
                <a href="{{ route('claims.index') }}" class="text-sm text-green-700 hover:text-green-900 font-medium">
                    ← Back to Claims
                </a>
                <h1 class="text-xl sm:text-2xl font-bold text-green-800 mt-2 break-words">
                    Claim <span class="font-mono">{{ $claim->claim_number }}</span>
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $claim->claim_type->label() }} ·
                    Reported {{ $claim->reported_at->format('d M Y, H:i') }}
                </p>
            </div>

            <div class="text-left sm:text-right shrink-0">
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

        {{-- ─── Officers list — hoisted for use in multiple transition forms ─── --}}
        @php
            $officers = \App\Models\User::whereIn('role', [
                \App\Enums\UserRole::ClaimsOfficer->value,
                \App\Enums\UserRole::Underwriter->value,
                \App\Enums\UserRole::Admin->value,
            ])->orderBy('name')->get();
        @endphp

        {{-- ─── Policy + Client + Vehicle cards ─── --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

            {{-- Policy --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h2 class="text-xs font-semibold text-green-800 uppercase tracking-wide mb-3">Policy</h2>
                <dl class="text-sm space-y-1.5">
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500 shrink-0">Number</dt>
                        <dd class="font-mono text-xs font-semibold text-gray-800 truncate">
                            {{ $claim->policy->policy_number }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500 shrink-0">Cover</dt>
                        <dd class="text-gray-800 text-right">{{ $claim->policy->policy_type->label() }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500 shrink-0">Deductible</dt>
                        <dd class="font-medium text-gray-800 text-right">
                            KES {{ number_format($claim->policy->deductible_amount, 2) }}
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
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500 shrink-0">Name</dt>
                        <dd class="font-medium text-gray-800 text-right">
                            {{ $claim->policy->client->first_name }} {{ $claim->policy->client->last_name }}
                        </dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500 shrink-0">Phone</dt>
                        <dd class="text-gray-800 text-right">{{ $claim->policy->client->phone_number }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500 shrink-0">Email</dt>
                        <dd class="text-gray-800 truncate text-right">{{ $claim->policy->client->email }}</dd>
                    </div>
                    <div class="pt-2 mt-2 border-t border-gray-100">
                        <a href="{{ route('clients.show', $claim->policy->client) }}"
                            class="text-xs text-green-700 hover:text-green-900 font-medium">View client →</a>
                    </div>
                </dl>
            </div>

            {{-- Vehicle --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 sm:col-span-2 lg:col-span-1">
                <h2 class="text-xs font-semibold text-green-800 uppercase tracking-wide mb-3">Vehicle</h2>
                <dl class="text-sm space-y-1.5">
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500 shrink-0">Registration</dt>
                        <dd class="font-mono font-semibold text-gray-800">{{ $claim->policy->vehicle->registration_number }}
                        </dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500 shrink-0">Make & Model</dt>
                        <dd class="text-gray-800 text-right">
                            {{ $claim->policy->vehicle->make }} {{ $claim->policy->vehicle->model }}
                        </dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500 shrink-0">Year</dt>
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
                <div class="flex justify-between gap-3">
                    <dt class="text-gray-500 shrink-0">Incident Date</dt>
                    <dd class="text-gray-800 font-medium">{{ $claim->incident_date->format('d M Y') }}</dd>
                </div>
                @if ($claim->location)
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500 shrink-0">Location</dt>
                        <dd class="text-gray-800 text-right">{{ $claim->location }}</dd>
                    </div>
                @endif
                @if ($claim->police_abstract_ref)
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500 shrink-0">Police Abstract</dt>
                        <dd class="font-mono text-gray-800">{{ $claim->police_abstract_ref }}</dd>
                    </div>
                @endif
                @if ($claim->driver)
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500 shrink-0">Driver at Time</dt>
                        <dd class="text-gray-800 text-right">
                            {{ $claim->driver->name }}
                            @if ($claim->driver->is_primary)
                                <span class="text-xs text-lime-700 ml-1">(Primary)</span>
                            @endif
                        </dd>
                    </div>
                @else
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500 shrink-0">Driver at Time</dt>
                        <dd class="text-yellow-700 text-sm italic text-right">Not in authorized list</dd>
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
                <div class="flex justify-between gap-3">
                    <dt class="text-gray-600">Amount Claimed</dt>
                    <dd class="text-gray-800">KES {{ number_format($claim->claimed_amount, 2) }}</dd>
                </div>
                @if ($claim->assessed_amount)
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-600">Assessed Amount</dt>
                        <dd class="text-gray-800">KES {{ number_format($claim->assessed_amount, 2) }}</dd>
                    </div>
                @endif
                @if ($claim->approved_amount)
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-600">Approved Amount</dt>
                        <dd class="text-gray-800">KES {{ number_format($claim->approved_amount, 2) }}</dd>
                    </div>
                @endif
                @if ($claim->deductible_applied)
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-600">Deductible Applied</dt>
                        <dd class="text-gray-800">− KES {{ number_format($claim->deductible_applied, 2) }}</dd>
                    </div>
                @endif
                @if ($claim->salvage_value)
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-600">Salvage Retained</dt>
                        <dd class="text-gray-800">− KES {{ number_format($claim->salvage_value, 2) }}</dd>
                    </div>
                @endif

                @if ($claim->netPayout() !== null)
                    <div class="flex justify-between items-baseline gap-3 border-t-2 border-green-200 pt-3 mt-3">
                        <dt class="font-semibold text-green-800">Net Payout</dt>
                        <dd class="font-bold text-green-800 text-lg sm:text-xl">
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
                    <div class="flex justify-between gap-3">
                        <dt class="text-gray-500 shrink-0">Rejected On</dt>
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

       
        {{-- ─── Documents ─── --}}
<div class="mt-4 bg-white rounded-xl shadow-sm border border-gray-100 p-5">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xs font-semibold text-green-800 uppercase tracking-wide">Documents</h2>
        @php
            $received = $claim->documents->whereNull('deleted_at')->pluck('type')->map(fn ($t) => $t->value)->toArray();
            $required = \App\Enums\ClaimDocumentType::requiredForIntake();
            $receivedCount = count(array_intersect(array_map(fn ($t) => $t->value, $required), $received));
        @endphp
        <span class="text-xs
            {{ $receivedCount === count($required) ? 'text-green-700 font-medium' : 'text-yellow-700' }}">
            {{ $receivedCount }} of {{ count($required) }} required
        </span>
    </div>

    {{-- Required documents --}}
    <div class="space-y-3">
        @foreach ($required as $type)
            @php
                $doc = $claim->documents
                    ->whereNull('deleted_at')
                    ->where('type', $type)
                    ->first();
            @endphp

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-3 rounded-lg
                {{ $doc ? 'bg-lime-50 border border-lime-200' : 'bg-yellow-50 border border-yellow-200' }}">

                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="font-medium text-gray-800 text-sm">{{ $type->label() }}</span>
                        @if ($doc)
                            <span class="text-xs px-2 py-0.5 rounded-full bg-lime-200 text-lime-900 font-medium">
                                Received
                            </span>
                        @else
                            <span class="text-xs px-2 py-0.5 rounded-full bg-yellow-200 text-yellow-900 font-medium">
                                Pending
                            </span>
                        @endif
                    </div>
                    @if ($doc)
                        <div class="text-xs text-gray-500 mt-1 truncate">
                            {{ $doc->original_filename }}
                            · {{ number_format($doc->size_bytes / 1024, 1) }} KB
                        </div>
                    @endif
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    @if ($doc)
                        <a href="{{ route('claims.documents.download', [$claim, $doc]) }}"
                           class="text-xs px-3 py-1.5 rounded-lg border border-green-300 text-green-700 hover:bg-green-50 font-medium">
                            Download
                        </a>
                        <form method="POST" action="{{ route('claims.documents.destroy', [$claim, $doc]) }}"
                              onsubmit="return confirm('Remove this document?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="text-xs px-3 py-1.5 rounded-lg border border-red-300 text-red-700 hover:bg-red-50 font-medium">
                                Remove
                            </button>
                        </form>
                    @else
                        <button type="button"
                                onclick="document.getElementById('upload-{{ $type->value }}').classList.toggle('hidden')"
                                class="text-xs px-3 py-1.5 rounded-lg bg-green-600 hover:bg-green-700 text-white font-medium">
                            Upload
                        </button>
                    @endif
                </div>
            </div>

            {{-- Upload form (hidden by default) --}}
            @if (! $doc)
                <div id="upload-{{ $type->value }}" class="hidden pl-3 mt-2">
                    <form method="POST"
                          action="{{ route('claims.documents.store', $claim) }}"
                          enctype="multipart/form-data"
                          class="flex flex-col sm:flex-row gap-2 items-stretch sm:items-center">
                        @csrf
                        <input type="hidden" name="type" value="{{ $type->value }}">
                        <input type="file" name="file" required
                               accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                               class="flex-1 rounded-lg border-2 border-purple-200 bg-white px-3 py-2 text-sm text-gray-700 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:bg-purple-600 file:text-white file:text-xs file:font-medium hover:file:bg-purple-700">
                        <button type="submit"
                                class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-medium shrink-0">
                            Upload
                        </button>
                    </form>
                </div>
            @endif
        @endforeach
    </div>

    {{-- Optional documents --}}
    @php
        $optionalTypes = collect(\App\Enums\ClaimDocumentType::cases())
            ->reject(fn ($t) => in_array($t, $required, true));
    @endphp

    @if ($optionalTypes->isNotEmpty())
        <div class="mt-6 pt-4 border-t border-gray-100">
            <h3 class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-3">Additional Documents</h3>
            <div class="flex flex-wrap gap-2">
                @foreach ($optionalTypes as $type)
                    <button type="button"
                            onclick="document.getElementById('upload-opt-{{ $type->value }}').classList.toggle('hidden')"
                            class="text-xs px-3 py-1.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium">
                        + {{ $type->label() }}
                    </button>
                @endforeach
            </div>

            @foreach ($optionalTypes as $type)
                <div id="upload-opt-{{ $type->value }}" class="hidden mt-3">
                    <form method="POST"
                          action="{{ route('claims.documents.store', $claim) }}"
                          enctype="multipart/form-data"
                          class="flex flex-col sm:flex-row gap-2 items-stretch sm:items-center">
                        @csrf
                        <input type="hidden" name="type" value="{{ $type->value }}">
                        <input type="file" name="file" required
                               accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                               class="flex-1 rounded-lg border-2 border-purple-200 bg-white px-3 py-2 text-sm text-gray-700 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:bg-purple-600 file:text-white file:text-xs file:font-medium hover:file:bg-purple-700">
                        <button type="submit"
                                class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-medium shrink-0">
                            Upload
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Uploaded optional documents --}}
    @php
        $optionalDocs = $claim->documents
            ->whereNull('deleted_at')
            ->reject(fn ($d) => in_array($d->type, $required, true));
    @endphp

    @if ($optionalDocs->isNotEmpty())
        <div class="mt-4 pt-4 border-t border-gray-100">
            <h3 class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">Additional Files</h3>
            <ul class="divide-y divide-gray-100 text-sm">
                @foreach ($optionalDocs as $doc)
                    <li class="py-2 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div class="min-w-0">
                            <div class="font-medium text-gray-800">{{ $doc->type->label() }}</div>
                            <div class="text-xs text-gray-500 truncate">
                                {{ $doc->original_filename }} · {{ number_format($doc->size_bytes / 1024, 1) }} KB
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <a href="{{ route('claims.documents.download', [$claim, $doc]) }}"
                               class="text-xs text-green-700 hover:text-green-900 font-medium">Download</a>
                            <form method="POST" action="{{ route('claims.documents.destroy', [$claim, $doc]) }}"
                                  onsubmit="return confirm('Remove this document?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-red-600 hover:text-red-800 font-medium">
                                    Remove
                                </button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
      

        {{-- ─── Transitions ─── --}}
        <div class="mt-6 space-y-4">

            {{-- ═══ Acknowledge ── status Submitted ═══ --}}
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
                            class="w-full sm:w-auto px-5 py-2.5 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-medium shadow-sm transition">
                            Acknowledge Claim
                        </button>
                    </form>
                </div>
            @endif

            {{-- ═══ Assign & Begin Review ── status Acknowledged ═══ --}}
            @if ($claim->status === \App\Enums\ClaimStatus::Acknowledged)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <h2 class="text-xs font-semibold text-green-800 uppercase tracking-wide mb-3">
                        Assign to a Claims Officer
                    </h2>
                    <p class="text-sm text-gray-600 mb-4">
                        Assign this claim to the officer who will handle it. All intake documents
                        must be received before the claim moves to review.
                    </p>

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
                            class="w-full sm:w-auto px-5 py-2.5 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-medium shadow-sm transition">
                            Begin Review
                        </button>
                    </form>
                </div>
            @endif

            {{-- ═══ Record Assessment ── status UnderReview ═══ --}}
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
                                <p class="text-xs text-gray-500 mt-1.5">Only if the client retains the salvage.</p>
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
                            class="w-full sm:w-auto px-5 py-2.5 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-medium shadow-sm transition">
                            Record Assessment
                        </button>
                    </form>
                </div>
            @endif

            {{-- ═══ Approve / Reject ── status Assessed ═══ --}}
            @if ($claim->status === \App\Enums\ClaimStatus::Assessed)

                {{-- Approve — underwriter or admin --}}
                @if (auth()->user()->hasRole(\App\Enums\UserRole::Admin, \App\Enums\UserRole::Underwriter))
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        <h2 class="text-xs font-semibold text-green-800 uppercase tracking-wide mb-3">
                            Approve Claim
                        </h2>
                        <p class="text-sm text-gray-600 mb-4">
                            The assessed amount is <strong>KES {{ number_format($claim->assessed_amount, 2) }}</strong>.
                            Approving commits the insurer to a payout.
                        </p>

                        <form method="POST" action="{{ route('claims.approve', $claim) }}" class="space-y-4">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                        Approved Amount (KES) *
                                    </label>
                                    <input type="number" name="approved_amount" required min="0" step="0.01"
                                        value="{{ old('approved_amount', $claim->assessed_amount) }}"
                                        class="w-full rounded-lg border-2 border-purple-200 bg-white px-4 py-3 text-base text-gray-800 focus:border-purple-500 focus:ring-4 focus:ring-purple-100 focus:outline-none transition">
                                    <p class="text-xs text-gray-500 mt-1.5">
                                        Cannot exceed the assessed amount. Must exceed the policy deductible of KES
                                        {{ number_format($claim->policy->deductible_amount, 2) }}.
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                        Salvage Value (KES)
                                    </label>
                                    <input type="number" name="salvage_value" min="0" step="0.01"
                                        value="{{ old('salvage_value', $claim->salvage_value) }}"
                                        class="w-full rounded-lg border-2 border-purple-200 bg-white px-4 py-3 text-base text-gray-800 focus:border-purple-500 focus:ring-4 focus:ring-purple-100 focus:outline-none transition">
                                </div>
                            </div>
                            <button type="submit"
                                class="w-full sm:w-auto px-5 py-2.5 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-medium shadow-sm transition">
                                Approve Claim
                            </button>
                        </form>
                    </div>
                @endif

                {{-- Reject — underwriter or admin --}}
                @if (auth()->user()->hasRole(\App\Enums\UserRole::Admin, \App\Enums\UserRole::Underwriter))
                    <div class="bg-white rounded-xl shadow-sm border border-red-200 p-5">
                        <h2 class="text-xs font-semibold text-red-800 uppercase tracking-wide mb-3">
                            Reject Claim
                        </h2>
                        <p class="text-sm text-gray-600 mb-4">
                            Rejecting denies liability. Fraud-based rejections require documented evidence.
                        </p>

                        <form method="POST" action="{{ route('claims.reject', $claim) }}" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Rejection Reason *
                                </label>
                                <select name="rejection_reason" required
                                    class="w-full rounded-lg border-2 border-purple-200 bg-white px-4 py-3 text-base text-gray-800 focus:border-purple-500 focus:ring-4 focus:ring-purple-100 focus:outline-none transition">
                                    <option value="">Select a reason…</option>
                                    @foreach (\App\Enums\RejectionReason::cases() as $reason)
                                        <option value="{{ $reason->value }}" @selected(old('rejection_reason') === $reason->value)>
                                            {{ $reason->label() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Notes / Evidence
                                </label>
                                <textarea name="rejection_notes" rows="4"
                                    placeholder="Document the evidence and legal basis for rejection. Required for fraud-based rejections."
                                    class="w-full rounded-lg border-2 border-purple-200 bg-white px-4 py-3 text-base text-gray-800 placeholder-gray-400 focus:border-purple-500 focus:ring-4 focus:ring-purple-100 focus:outline-none transition">{{ old('rejection_notes') }}</textarea>
                            </div>

                            <button type="submit" onclick="return confirm('Reject this claim? This is a final decision.')"
                                class="w-full sm:w-auto px-5 py-2.5 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm font-medium shadow-sm transition">
                                Reject Claim
                            </button>
                        </form>
                    </div>
                @endif
            @endif

            {{-- ═══ Mark Paid ── status Approved ═══ --}}
            @if ($claim->status === \App\Enums\ClaimStatus::Approved)

                {{-- Approved summary --}}
                <div class="bg-lime-50 rounded-xl shadow-sm border border-lime-200 p-5">
                    <h2 class="text-xs font-semibold text-lime-900 uppercase tracking-wide mb-3">
                        Claim Approved
                    </h2>
                    <dl class="text-sm space-y-1.5">
                        <div class="flex justify-between gap-3">
                            <dt class="text-lime-800">Approved Amount</dt>
                            <dd class="font-medium text-lime-900">KES {{ number_format($claim->approved_amount, 2) }}</dd>
                        </div>
                        @if ($claim->deductible_applied)
                            <div class="flex justify-between gap-3">
                                <dt class="text-lime-800">Less Deductible</dt>
                                <dd class="text-lime-900">− KES {{ number_format($claim->deductible_applied, 2) }}</dd>
                            </div>
                        @endif
                        @if ($claim->salvage_value)
                            <div class="flex justify-between gap-3">
                                <dt class="text-lime-800">Less Salvage</dt>
                                <dd class="text-lime-900">− KES {{ number_format($claim->salvage_value, 2) }}</dd>
                            </div>
                        @endif
                        @if ($claim->netPayout() !== null)
                            <div class="flex justify-between items-baseline gap-3 border-t border-lime-300 pt-3 mt-3">
                                <dt class="font-semibold text-lime-900">Net Payout</dt>
                                <dd class="font-bold text-lime-900 text-lg">KES {{ number_format($claim->netPayout(), 2) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                {{-- Mark paid — admin only --}}
                @if (auth()->user()->hasRole(\App\Enums\UserRole::Admin))
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        <h2 class="text-xs font-semibold text-green-800 uppercase tracking-wide mb-3">
                            Mark as Paid
                        </h2>
                        <p class="text-sm text-gray-600 mb-4">
                            Confirm that payment has been issued to the client. This is the final
                            state for the claim and cannot be reversed.
                        </p>
                        <form method="POST" action="{{ route('claims.mark-paid', $claim) }}">
                            @csrf
                            <button type="submit"
                                onclick="return confirm('Confirm that payment has been issued? This closes the claim.')"
                                class="w-full sm:w-auto px-5 py-2.5 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-medium shadow-sm transition">
                                Mark as Paid
                            </button>
                        </form>
                    </div>
                @else
                    <div class="rounded-lg bg-lilac-50 border border-purple-200 p-4 text-xs text-gray-600">
                        <strong class="text-green-800">Awaiting admin payment.</strong>
                        Only an administrator can confirm payment has been issued.
                    </div>
                @endif
            @endif

            {{-- ═══ Final states ── Rejected / Paid ═══ --}}
            @if ($claim->status === \App\Enums\ClaimStatus::Rejected)
                <div class="rounded-lg bg-red-50 border border-red-200 p-4 text-xs text-red-800">
                    <strong>This claim has been rejected.</strong>
                    No further actions are available.
                </div>
            @endif

            @if ($claim->status === \App\Enums\ClaimStatus::Paid)
                <div class="rounded-lg bg-green-50 border border-green-200 p-4 text-xs text-green-800">
                    <strong>This claim has been paid and closed.</strong>
                    Paid on {{ $claim->paid_at?->format('d M Y, H:i') }}.
                </div>
            @endif

        </div>

    </div>
@endsection