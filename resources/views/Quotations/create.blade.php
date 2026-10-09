@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto py-8 px-4">

        {{-- ─── Header ─── --}}
        <div class="mb-6">
            <a href="{{ route('quotations.index') }}" class="text-sm text-green-700 hover:text-green-900 font-medium">
                ← Back to Quotations
            </a>
            <h1 class="text-2xl font-bold text-green-800 mt-2">New Quotation</h1>
            <p class="text-sm text-gray-500">
                Capture the prospect and vehicle details, select the cover, and get an instant premium.
            </p>
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

        <form method="POST" action="{{ route('quotations.store') }}" class="space-y-6">
            @csrf

            {{-- ─── Section 1 — Prospect ─── --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center gap-2 mb-4">
                    <span
                        class="w-6 h-6 flex items-center justify-center rounded-full bg-green-100 text-green-800 text-xs font-bold">1</span>
                    <h2 class="text-sm font-semibold text-green-800 uppercase tracking-wide">Prospect Details</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Prospect Name *
                        </label>
                        <input type="text" name="prospect_name" value="{{ old('prospect_name') }}" required maxlength="150"
                            placeholder="Full name as provided"
                            class="w-full rounded-lg border-2 border-purple-200 bg-white px-4 py-3 text-base text-gray-800 placeholder-gray-400 focus:border-purple-500 focus:ring-4 focus:ring-purple-100 focus:outline-none transition">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Phone Number
                        </label>
                        <input type="text" name="prospect_phone" value="{{ old('prospect_phone') }}" maxlength="20"
                            placeholder="07XXXXXXXX"
                            class="w-full rounded-lg border-2 border-purple-200 bg-white px-4 py-3 text-base text-gray-800 placeholder-gray-400 focus:border-purple-500 focus:ring-4 focus:ring-purple-100 focus:outline-none transition">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Email Address
                        </label>
                        <input type="email" name="prospect_email" value="{{ old('prospect_email') }}" maxlength="150"
                            placeholder="name@example.com"
                            class="w-full rounded-lg border-2 border-purple-200 bg-white px-4 py-3 text-base text-gray-800 placeholder-gray-400 focus:border-purple-500 focus:ring-4 focus:ring-purple-100 focus:outline-none transition">
                    </div>
                </div>
            </div>

            {{-- ─── Section 2 — Vehicle ─── --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center gap-2 mb-4">
                    <span
                        class="w-6 h-6 flex items-center justify-center rounded-full bg-green-100 text-green-800 text-xs font-bold">2</span>
                    <h2 class="text-sm font-semibold text-green-800 uppercase tracking-wide">Vehicle Details</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Make *</label>
                        <input type="text" name="vehicle_details[make]" value="{{ old('vehicle_details.make') }}" required
                            maxlength="80" placeholder="Toyota"
                            class="w-full rounded-lg border-2 border-purple-200 bg-white px-4 py-3 text-base text-gray-800 placeholder-gray-400 focus:border-purple-500 focus:ring-4 focus:ring-purple-100 focus:outline-none transition">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Model *</label>
                        <input type="text" name="vehicle_details[model]" value="{{ old('vehicle_details.model') }}" required
                            maxlength="80" placeholder="Hilux"
                            class="w-full rounded-lg border-2 border-purple-200 bg-white px-4 py-3 text-base text-gray-800 placeholder-gray-400 focus:border-purple-500 focus:ring-4 focus:ring-purple-100 focus:outline-none transition">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Year of Manufacture *
                        </label>
                        <input type="number" name="vehicle_details[year_of_manufacture]"
                            value="{{ old('vehicle_details.year_of_manufacture') }}" required min="1950"
                            max="{{ date('Y') + 1 }}" placeholder="{{ date('Y') - 3 }}"
                            class="w-full rounded-lg border-2 border-purple-200 bg-white px-4 py-3 text-base text-gray-800 placeholder-gray-400 focus:border-purple-500 focus:ring-4 focus:ring-purple-100 focus:outline-none transition">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Estimated Value (KES) *
                        </label>
                        <input type="number" name="vehicle_details[estimated_value]"
                            value="{{ old('vehicle_details.estimated_value') }}" required min="10000" step="0.01"
                            placeholder="3500000"
                            class="w-full rounded-lg border-2 border-purple-200 bg-white px-4 py-3 text-base text-gray-800 placeholder-gray-400 focus:border-purple-500 focus:ring-4 focus:ring-purple-100 focus:outline-none transition">
                        <p class="text-xs text-gray-500 mt-1.5">
                            Preliminary estimate. The final valuation comes from the assessor.
                        </p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Vehicle Use *
                        </label>
                        <select name="vehicle_details[vehicle_use]" required
                            class="w-full rounded-lg border-2 border-purple-200 bg-white px-4 py-3 text-base text-gray-800 focus:border-purple-500 focus:ring-4 focus:ring-purple-100 focus:outline-none transition">
                            <option value="">Select…</option>
                            @foreach (\App\Enums\VehicleUse::cases() as $use)
                                <option value="{{ $use->value }}" @selected(old('vehicle_details.vehicle_use') === $use->value)>
                                    {{ $use->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Registration Number
                        </label>
                        <input type="text" name="vehicle_details[registration_number]"
                            value="{{ old('vehicle_details.registration_number') }}" maxlength="20" placeholder="KDA777Z"
                            class="w-full rounded-lg border-2 border-purple-200 bg-white px-4 py-3 text-base text-gray-800 placeholder-gray-400 uppercase focus:border-purple-500 focus:ring-4 focus:ring-purple-100 focus:outline-none transition">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Chassis Number
                        </label>
                        <input type="text" name="vehicle_details[chassis_number]"
                            value="{{ old('vehicle_details.chassis_number') }}" maxlength="50" placeholder="NCP1234567890"
                            class="w-full rounded-lg border-2 border-purple-200 bg-white px-4 py-3 text-base text-gray-800 placeholder-gray-400 uppercase focus:border-purple-500 focus:ring-4 focus:ring-purple-100 focus:outline-none transition">
                    </div>
                </div>
            </div>

            {{-- ─── Section 3 — Cover Type ─── --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center gap-2 mb-4">
                    <span
                        class="w-6 h-6 flex items-center justify-center rounded-full bg-green-100 text-green-800 text-xs font-bold">3</span>
                    <h2 class="text-sm font-semibold text-green-800 uppercase tracking-wide">Choose Cover Type</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    @foreach ($types as $type)
                        <label class="block cursor-pointer">
                            <input type="radio" name="policy_type" value="{{ $type->value }}" @checked(old('policy_type', \App\Enums\PolicyType::Comprehensive->value) === $type->value) class="peer sr-only">

                            <div
                                class="p-4 rounded-lg border-2 border-gray-200 peer-checked:border-green-500 peer-checked:bg-lime-50 hover:border-green-300 transition">
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

            {{-- ─── Section 4 — Add-Ons ─── --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center gap-2 mb-4">
                    <span
                        class="w-6 h-6 flex items-center justify-center rounded-full bg-green-100 text-green-800 text-xs font-bold">4</span>
                    <h2 class="text-sm font-semibold text-green-800 uppercase tracking-wide">Optional Covers</h2>
                </div>

                @if ($addOns->isEmpty())
                    <p class="text-sm text-gray-400">No optional covers available.</p>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach ($addOns as $addOn)
                            <label
                                class="flex items-start gap-3 p-4 rounded-lg border-2 border-gray-200 hover:border-green-300 cursor-pointer transition">
                                <input type="checkbox" name="selected_add_on_ids[]" value="{{ $addOn->id }}"
                                    @checked(in_array($addOn->id, old('selected_add_on_ids', [])))
                                    class="mt-1 rounded border-gray-300 text-green-600 focus:ring-green-500">

                                <div class="flex-1">
                                    <p class="font-medium text-gray-800">{{ $addOn->name }}</p>
                                    @if ($addOn->description)
                                        <p class="text-xs text-gray-500">{{ $addOn->description }}</p>
                                    @endif
                                    <p class="text-xs font-mono text-green-700 mt-1.5">
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

            {{-- ─── Actions ─── --}}
            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-2">
                <a href="{{ route('quotations.index') }}"
                    class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium text-center">
                    Cancel
                </a>
                <button type="submit"
                    class="px-6 py-2.5 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-medium shadow-sm">
                    Create Quotation
                </button>
            </div>
        </form>
    </div>
@endsection