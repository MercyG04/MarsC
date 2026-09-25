@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto py-8 px-4">

        <div class="mb-6">
            <a href="{{ route('vehicles.show', $vehicle) }}"
                class="text-sm text-green-700 hover:text-green-900 font-medium">
                ← Back to vehicle
            </a>
            <h1 class="text-2xl font-bold text-green-800 mt-2">Record a Valuation</h1>
            <p class="text-sm text-gray-500">
                For <strong>{{ $vehicle->year_of_manufacture }} {{ $vehicle->make }} {{ $vehicle->model }}</strong>
                (<span class="font-mono">{{ $vehicle->registration_number }}</span>)
                owned by {{ $vehicle->client->first_name }} {{ $vehicle->client->last_name }}.
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

        <form method="POST" action="{{ route('valuations.store', $vehicle) }}" enctype="multipart/form-data"
            class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-5">
            @csrf

            {{-- Step 1 — Source --}}
            <div class="rounded-lg bg-lime-50 border border-lime-200 p-4">
                <h2 class="text-sm font-semibold text-green-800 mb-3">Step 1 — Assessor Details</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Valuation Firm *</label>
                        <input type="text" name="valuation_firm" value="{{ old('valuation_firm') }}" required
                            class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Valuer Name</label>
                        <input type="text" name="valuer_name" value="{{ old('valuer_name') }}"
                            class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">
                    </div>
                </div>
            </div>

            {{-- Step 2 — Numbers --}}
            <h2 class="text-sm font-semibold text-green-800">Step 2 — Valuation Numbers</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Valuation Date *</label>
                    <input type="date" name="valuation_date" value="{{ old('valuation_date', now()->toDateString()) }}"
                        max="{{ now()->toDateString() }}" required
                        class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Actual Cash Value (KES) *</label>
                    <input type="number" step="0.01" min="1" name="actual_cash_value" value="{{ old('actual_cash_value') }}"
                        required
                        class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Forced Sale Value (KES)</label>
                    <input type="number" step="0.01" min="0" name="forced_sale_value" value="{{ old('forced_sale_value') }}"
                        class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">
                    <p class="text-xs text-gray-500 mt-1">Cannot exceed ACV.</p>
                </div>
            </div>

            {{-- Step 3 — Type --}}
            <h2 class="text-sm font-semibold text-green-800">Step 3 — Valuation Type</h2>

            <div>
                <select name="valuation_type" required
                    class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">
                    @foreach ($types as $type)
                        @if ($type !== \App\Enums\ValuationType::Disputed || $canOverride)
                            <option value="{{ $type->value }}" @selected(old('valuation_type', $suggestedType->value) === $type->value)>
                                {{ $type->label() }}
                            </option>
                        @endif
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1">
                    Suggested: <strong>{{ $suggestedType->label() }}</strong>
                    @if ($canOverride)
                        · You can override this as an admin.
                    @else
                        · Only admins can record disputed valuations.
                    @endif
                </p>
            </div>

            {{-- Step 4 — Report upload --}}
            <h2 class="text-sm font-semibold text-green-800">Step 4 — Valuation Report (optional)</h2>

            <div>
                <input type="file" name="report" accept="application/pdf"
                    class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-green-600 file:text-white file:text-sm file:font-medium hover:file:bg-green-700">
                <p class="text-xs text-gray-500 mt-1">
                    PDF only, max 10 MB.
                </p>
            </div>

            {{-- Notes --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" rows="4"
                    class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">{{ old('notes') }}</textarea>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('vehicles.show', $vehicle) }}"
                    class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium">
                    Cancel
                </a>
                <button type="submit"
                    class="px-5 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-medium shadow-sm">
                    Record Valuation
                </button>
            </div>
        </form>
    </div>
@endsection