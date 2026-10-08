@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto py-8 px-4">

        {{-- ─── Header ─── --}}
        <div class="mb-6">
            <a href="{{ route('policies.show', $policy) }}" class="text-sm text-green-700 hover:text-green-900 font-medium">
                ← Back to policy
            </a>
            <h1 class="text-2xl font-bold text-green-800 mt-2">Endorse Policy</h1>
            <p class="text-sm text-gray-500">
                Policy <span class="font-mono font-medium">{{ $policy->policy_number }}</span> ·
                Current: {{ $policy->policy_type->label() }} cover
            </p>
        </div>

        {{-- ─── Explainer ─── */
        <div class="mb-6 rounded-lg bg-lilac-50 border border-purple-200 p-4 text-sm text-gray-700">
            <strong class="block text-green-800 mb-1">Mid-term endorsement</strong>
            An endorsement is a formal addendum to an active policy. Only the changes you make here
            are applied. The pro-rata additional premium is calculated for the remaining days
            of the policy term.
        </div>--}}

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
        @if ($errors->has('endorsement'))
            <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
                {{ $errors->first('endorsement') }}
            </div>
        @endif

        <form method="POST" action="{{ route('policies.endorse.store', $policy) }}"
            class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">
            @csrf

            {{-- ─── Change 1 — Policy Type ─── --}}
            <div>
                <h2 class="text-sm font-semibold text-green-800 mb-3">1. Change Cover Type</h2>
                <p class="text-xs text-gray-500 mb-3">
                    Leave as-is to keep the current cover. Select a different type to upgrade or downgrade.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    @foreach ($types as $type)
                        <label class="block cursor-pointer">
                            <input type="radio" name="policy_type" value="{{ $type->value }}" @checked(old('policy_type', $policy->policy_type->value) === $type->value) class="peer sr-only">

                            <div class="p-4 rounded-lg border-2 border-gray-200
                                                                    peer-checked:border-green-500 peer-checked:bg-lime-50
                                                                    hover:border-green-300 transition">

                                <p class="font-semibold text-gray-800">{{ $type->label() }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ $type->description() }}</p>

                                @if ($type === $policy->policy_type)
                                    <p class="text-xs text-green-700 font-medium mt-2">
                                        Currently active
                                    </p>
                                @endif
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- ─── Change 2 — Add-ons ─── --}}
            <div>
                <h2 class="text-sm font-semibold text-green-800 mb-3">2. Adjust Optional Covers</h2>
                <p class="text-xs text-gray-500 mb-3">
                    Check new covers, uncheck ones you no longer want. Leave as-is to keep the current selection.
                </p>

                @php
                    $currentAddOnIds = $policy->addOns->pluck('id')->toArray();
                @endphp

                @if ($addOns->isEmpty())
                    <p class="text-sm text-gray-400">No optional covers available.</p>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach ($addOns as $addOn)
                            <label
                                class="flex items-start gap-3 p-3 rounded-lg border-2 border-gray-200
                                                                                      hover:border-green-300 cursor-pointer transition">
                                <input type="checkbox" name="add_on_ids[]" value="{{ $addOn->id }}" @checked(in_array($addOn->id, old('add_on_ids', $currentAddOnIds)))
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
                                    @if (in_array($addOn->id, $currentAddOnIds))
                                        <p class="text-xs text-green-700 mt-1">Currently on this policy</p>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>
            {{-- ─── Change 3 — Named Drivers ─── --}}
            <div>
                <h2 class="text-sm font-semibold text-green-800 mb-3">3. Adjust Named Drivers</h2>
                <p class="text-xs text-gray-500 mb-3">
                    Check new drivers, uncheck ones you no longer authorize. The primary driver cannot be removed.
                </p>

                @php
                    $currentDriverIds = $policy->vehicle->drivers->pluck('id')->toArray();
                @endphp

                @if ($policy->vehicle->drivers->isEmpty())
                    <p class="text-sm text-gray-400">
                        No drivers on record.
                        <a href="#" class="text-green-700 font-medium">Add drivers to the vehicle →</a>
                    </p>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach ($policy->vehicle->drivers as $driver)
                            <label class="flex items-start gap-3 p-3 rounded-lg border-2 border-gray-200
                                                      hover:border-green-300 cursor-pointer transition">
                                <input type="checkbox" name="driver_ids[]" value="{{ $driver->id }}" @checked(in_array($driver->id, old('driver_ids', $currentDriverIds)))
                                    class="mt-1 rounded border-gray-300 text-green-600 focus:ring-green-500">

                                <div class="flex-1">
                                    <p class="font-medium text-gray-800">
                                        {{ $driver->name }}
                                        @if ($driver->is_primary)
                                            <span class="text-xs px-1.5 py-0.5 rounded bg-lime-100 text-lime-800 ml-1">
                                                Primary
                                            </span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        DL {{ $driver->dl_number }} · Class {{ $driver->dl_class }} ·
                                        {{ $driver->age }} yrs old
                                    </p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- ─── Reason ─── --}}
            <div>
                <h2 class="text-sm font-semibold text-green-800 mb-3">3. Reason for Endorsement</h2>
                <textarea name="reason" rows="3" required minlength="10" maxlength="500"
                    placeholder="e.g. Client requested upgrade to comprehensive cover after purchasing a new vehicle"
                    class="w-full rounded-lg border border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">{{ old('reason') }}</textarea>
                <p class="text-xs text-gray-500 mt-1">
                    This reason is recorded on the endorsement note and forms part of the policy history.
                </p>
            </div>

            {{-- ─── Actions ─── --}}
            <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
                <a href="{{ route('policies.show', $policy) }}"
                    class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium">
                    Cancel
                </a>
                <button type="submit"
                    class="px-5 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-medium shadow-sm">
                    Apply Endorsement
                </button>
            </div>
        </form>
    </div>
@endsection