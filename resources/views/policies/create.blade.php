@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto py-8 px-4">

        {{-- ─── Header ─── --}}
        <div class="mb-6">
            <a href="{{ route('vehicles.show', $vehicle) }}"
                class="text-sm text-green-700 hover:text-green-900 font-medium">
                ← Back to vehicle
            </a>
            <h1 class="text-2xl font-bold text-green-800 mt-2">Issue Policy</h1>
            <p class="text-sm text-gray-500">
                Create a motor insurance policy for
                <strong>{{ $vehicle->year_of_manufacture }} {{ $vehicle->make }} {{ $vehicle->model }}</strong>
                (<span class="font-mono">{{ $vehicle->registration_number }}</span>).
            </p>
        </div>

        {{-- ─── Auto-loaded vehicle summary ─── --}}
        <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h2 class="text-xs font-semibold text-green-800 uppercase tracking-wide mb-3">
                    Insured Vehicle
                </h2>
                <dl class="text-sm space-y-1.5">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Registration</dt>
                        <dd class="font-mono font-semibold text-gray-800">{{ $vehicle->registration_number }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Make & Model</dt>
                        <dd class="text-gray-800">{{ $vehicle->make }} {{ $vehicle->model }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Year</dt>
                        <dd class="text-gray-800">{{ $vehicle->year_of_manufacture }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Usage</dt>
                        <dd class="text-gray-800">{{ $vehicle->vehicle_use->label() }}</dd>
                    </div>
                    <div class="flex justify-between border-t border-gray-100 pt-1.5 mt-1.5">
                        <dt class="text-gray-500 font-medium">Current Value (ACV)</dt>
                        <dd class="font-semibold text-green-800">
                            KES {{ number_format($vehicle->currentValue(), 2) }}
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h2 class="text-xs font-semibold text-green-800 uppercase tracking-wide mb-3">
                    Policy Holder
                </h2>
                <dl class="text-sm space-y-1.5">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Name</dt>
                        <dd class="font-medium text-gray-800">
                            {{ $vehicle->client->first_name }} {{ $vehicle->client->last_name }}
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">National ID</dt>
                        <dd class="font-mono text-gray-800">{{ $vehicle->client->national_id }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Phone</dt>
                        <dd class="text-gray-800">{{ $vehicle->client->phone_number }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Email</dt>
                        <dd class="text-gray-800 truncate">{{ $vehicle->client->email }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">County</dt>
                        <dd class="text-gray-800">{{ $vehicle->client->county }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- ─── Validation errors ─── --}}
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

        <form method="POST" action="{{ route('policies.store', $vehicle) }}"
            class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">
            @csrf

            {{-- ─── Step 1 — Cover Type ─── --}}
            <div>
                <h2 class="text-sm font-semibold text-green-800 mb-3">1. Choose Cover Type</h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    @foreach ($types as $type)
                        <label class="block cursor-pointer">
                            <input type="radio" name="policy_type" value="{{ $type->value }}" @checked(old('policy_type', \App\Enums\PolicyType::Comprehensive->value) === $type->value) class="peer sr-only">

                            <div class="p-4 rounded-lg border-2 border-gray-200
                                            peer-checked:border-green-500 peer-checked:bg-lime-50
                                            hover:border-green-300 transition">

                                <p class="font-semibold text-gray-800">{{ $type->label() }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ $type->description() }}</p>

                                <p class="text-xs text-green-700 font-medium mt-3">
                                    @if ($type === \App\Enums\PolicyType::ThirdParty)
                                        From KES {{ number_format($type->minimumPremium(), 0) }}
                                    @elseif ($type === \App\Enums\PolicyType::ThirdPartyFireTheft)
                                        From KES {{ number_format($type->minimumPremium(), 0) }} + 1% of ACV
                                    @else
                                        {{ $type->baseRate() }}% of ACV
                                    @endif
                                </p>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- ─── Step 2 — Optional Covers ─── --}}
            <div>
                <h2 class="text-sm font-semibold text-green-800 mb-3">2. Optional Covers</h2>

                @if ($addOns->isEmpty())
                    <p class="text-sm text-gray-400">No optional covers available.</p>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach ($addOns as $addOn)
                            <label class="flex items-start gap-3 p-3 rounded-lg border-2 border-gray-200
                                                  hover:border-green-300 cursor-pointer transition">
                                <input type="checkbox" name="add_on_ids[]" value="{{ $addOn->id }}" @checked(in_array($addOn->id, old('add_on_ids', [])))
                                    class="mt-1 rounded border-gray-300 text-green-600 focus:ring-green-500">

                                <div class="flex-1">
                                    <p class="font-medium text-gray-800">{{ $addOn->name }}</p>
                                    @if ($addOn->description)
                                        <p class="text-xs text-gray-500">{{ $addOn->description }}</p>
                                    @endif
                                    <p class="text-xs font-mono text-green-700 mt-1">
                                        @if ($addOn->rate_type === \App\Enums\AddOnRateType::Fixed)
                                            KES {{ number_format($addOn->rate_value, 2) }}
                                        @else
                                            {{ rtrim(rtrim(number_format($addOn->rate_value, 2), '0'), '.') }}% of sum insured
                                        @endif
                                    </p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- ─── Step 3 — Payment & Dates ─── --}}
            <div>
                <h2 class="text-sm font-semibold text-green-800 mb-3">3. Payment & Dates</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Payment Frequency *
                        </label>
                        <select name="payment_frequency" required
                            class="w-full rounded-lg border border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">
                            @foreach (\App\Enums\PaymentFrequency::cases() as $freq)
                                <option value="{{ $freq->value }}" @selected(old('payment_frequency', \App\Enums\PaymentFrequency::Annual->value) === $freq->value)>
                                    {{ $freq->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Start Date *
                        </label>
                        <input type="date" name="start_date" value="{{ old('start_date', now()->toDateString()) }}"
                            min="{{ now()->toDateString() }}" required
                            class="w-full rounded-lg border border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">
                        <p class="text-xs text-gray-500 mt-1">
                            The policy runs for one year from this date.
                        </p>
                    </div>
                </div>
            </div>

            {{-- ─── Actions ─── --}}
            <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
                <a href="{{ route('vehicles.show', $vehicle) }}"
                    class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium">
                    Cancel
                </a>
                <button type="submit"
                    class="px-5 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-medium shadow-sm">
                    Issue Policy
                </button>
            </div>
        </form>
    </div>
@endsection