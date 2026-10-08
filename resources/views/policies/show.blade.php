@extends('layouts.app')

@section('content')
    <div class="max-w-5xl mx-auto py-8 px-4">

        {{-- ─── Header ─── --}}
        <div class="mb-6 flex items-start justify-between">
            <div>
                <a href="{{ route('policies.index') }}" class="text-sm text-green-700 hover:text-green-900 font-medium">
                    ← Back to Policies
                </a>
                <h1 class="text-2xl font-bold text-green-800 mt-2">
                    Policy <span class="font-mono">{{ $policy->policy_number }}</span>
                </h1>
                <p class="text-sm text-gray-500">
                    {{ $policy->policy_type->label() }} cover ·
                    {{ $policy->start_date->format('d M Y') }} – {{ $policy->end_date->format('d M Y') }}
                </p>
            </div>

            <div class="text-right">
                @php
                    $statusClasses = match ($policy->status) {
                        \App\Enums\PolicyStatus::Active => 'bg-lime-100 text-lime-800',
                        \App\Enums\PolicyStatus::Pending => 'bg-yellow-100 text-yellow-800',
                        \App\Enums\PolicyStatus::Cancelled => 'bg-gray-100 text-gray-700',
                        \App\Enums\PolicyStatus::Expired => 'bg-purple-100 text-purple-800',
                    };
                @endphp
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $statusClasses }}">
                    {{ $policy->status->label() }}
                </span>

                @if ($policy->cancellation_status)
                    <div class="mt-2 text-xs text-yellow-700">
                        {{ $policy->cancellation_status->label() }}
                    </div>
                @endif
            </div>
        </div>

        {{-- ─── Flash messages ─── --}}
        @if (session('success'))
            <div class="mb-4 p-3 rounded-lg bg-lime-50 border border-lime-200 text-lime-800 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 p-3 rounded-lg bg-yellow-50 border border-yellow-200 text-yellow-900 text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ─── Cancellation alert (if pending) ─── --}}
        @if ($policy->cancellation_status === \App\Enums\PolicyCancellationStatus::CertificatePending)
            <div class="mb-4 rounded-lg bg-yellow-50 border border-yellow-200 p-4">
                <strong class="block text-yellow-900 mb-1">Cancellation pending certificate surrender</strong>
                <p class="text-sm text-yellow-800 mb-3">
                    Cover has stopped. The client must return the original and duplicate certificates
                    within 7 days. Failure to do so is a statutory offence under the Insurance
                    (Motor Vehicle Third Party Risks) Act.
                </p>
                <form method="POST" action="{{ route('policies.certificate.surrendered', $policy) }}">
                    @csrf
                    <button type="submit" onclick="return confirm('Confirm the certificates have been received?')"
                        class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-medium shadow-sm">
                        Mark Certificate Surrendered
                    </button>
                </form>
            </div>
        @endif

        {{-- ─── Top cards — Vehicle and Client ─── --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-xs font-semibold text-green-800 uppercase tracking-wide">Insured Vehicle</h2>
                    <a href="{{ route('vehicles.show', $policy->vehicle) }}"
                        class="text-xs text-green-700 hover:text-green-900 font-medium">View →</a>
                </div>
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
                        <dt class="text-gray-500">Year</dt>
                        <dd class="text-gray-800">{{ $policy->vehicle->year_of_manufacture }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Chassis</dt>
                        <dd class="font-mono text-xs text-gray-700">{{ $policy->vehicle->chassis_number }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Usage</dt>
                        <dd class="text-gray-800">{{ $policy->vehicle->vehicle_use->label() }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-xs font-semibold text-green-800 uppercase tracking-wide">Policy Holder</h2>
                    <a href="{{ route('clients.show', $policy->client) }}"
                        class="text-xs text-green-700 hover:text-green-900 font-medium">View →</a>
                </div>
                <dl class="text-sm space-y-1.5">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Name</dt>
                        <dd class="font-medium text-gray-800">
                            {{ $policy->client->first_name }} {{ $policy->client->last_name }}
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">National ID</dt>
                        <dd class="font-mono text-gray-800">{{ $policy->client->national_id }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Phone</dt>
                        <dd class="text-gray-800">{{ $policy->client->phone_number }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Email</dt>
                        <dd class="text-gray-800 truncate">{{ $policy->client->email }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">County</dt>
                        <dd class="text-gray-800">{{ $policy->client->county }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- ─── Premium breakdown ─── --}}
        <div class="mt-4 bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h2 class="text-xs font-semibold text-green-800 uppercase tracking-wide mb-4">Premium Breakdown</h2>

            <dl class="text-sm space-y-2">
                <div class="flex justify-between">
                    <dt class="text-gray-600">Basic Premium</dt>
                    <dd class="text-gray-800">KES {{ number_format($policy->basic_premium, 2) }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-600">Training Levy (0.2%)</dt>
                    <dd class="text-gray-800">KES {{ number_format($policy->training_levy, 2) }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-600">PHCF (0.25%)</dt>
                    <dd class="text-gray-800">KES {{ number_format($policy->phcf, 2) }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-600">Stamp Duty</dt>
                    <dd class="text-gray-800">KES {{ number_format($policy->stamp_duty, 2) }}</dd>
                </div>

                @if ($policy->addOns->isNotEmpty())
                    <div class="pt-2 mt-2 border-t border-gray-100">
                        <dt class="text-xs text-gray-500 uppercase tracking-wide mb-2">Optional Covers</dt>
                        @foreach ($policy->addOns as $addOn)
                            <div class="flex justify-between text-xs">
                                <dt class="text-gray-600">{{ $addOn->name }}</dt>
                                <dd class="text-gray-800">
                                    KES {{ number_format($addOn->pivot->charged_amount, 2) }}
                                </dd>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="flex justify-between border-t-2 border-green-200 pt-3 mt-3">
                    <dt class="font-semibold text-green-800">Gross Premium</dt>
                    <dd class="font-bold text-green-800 text-lg">
                        KES {{ number_format($policy->gross_premium, 2) }}
                    </dd>
                </div>

                <div class="flex justify-between pt-2">
                    <dt class="text-gray-600">Payment Frequency</dt>
                    <dd class="text-gray-800">{{ $policy->payment_frequency->label() }}</dd>
                </div>

                <div class="flex justify-between">
                    <dt class="text-gray-600">Premium Balance</dt>
                    <dd class="font-semibold
                                @if($policy->premium_balance > 0) text-yellow-700
                                @else text-green-700
                                @endif">
                        KES {{ number_format($policy->premium_balance, 2) }}
                        @if ($policy->premium_balance <= 0)
                            <span class="text-xs font-normal">(Fully paid)</span>
                        @endif
                    </dd>
                </div>

                <div class="flex justify-between">
                    <dt class="text-gray-600">Deductible / Excess</dt>
                    <dd class="text-gray-800">KES {{ number_format($policy->deductible_amount, 2) }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-600">Sum Insured</dt>
                    <dd class="text-gray-800">KES {{ number_format($policy->sum_insured, 2) }}</dd>
                </div>
            </dl>
        </div>

        {{-- ─── Endorsement history ─── --}}
        @if ($policy->endorsements->isNotEmpty())
            <div class="mt-4 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="text-xs font-semibold text-green-800 uppercase tracking-wide">
                        Endorsement History
                    </h2>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-lime-50 text-green-900 text-left text-xs uppercase tracking-wide">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Number</th>
                            <th class="px-5 py-3 font-semibold">Date</th>
                            <th class="px-5 py-3 font-semibold">Change</th>
                            <th class="px-5 py-3 font-semibold text-right">Additional</th>
                            <th class="px-5 py-3 font-semibold text-right">Refund</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($policy->endorsements as $endorsement)
                            <tr class="hover:bg-lilac-50/40 transition">
                                <td class="px-5 py-3 font-mono text-xs text-gray-700">
                                    <a href="{{ route('policies.endorsements.show', [$policy, $endorsement]) }}"
                                        class="text-purple-700 hover:text-purple-900 font-medium">
                                        {{ $endorsement->endorsement_number }}
                                    </a>
                                </td>
                                <td class="px-5 py-3 text-gray-700">
                                    {{ $endorsement->effective_date->format('d M Y') }}
                                </td>
                                <td class="px-5 py-3 text-gray-700">
                                    {{ str_replace('_', ' ', $endorsement->endorsement_type) }}
                                </td>
                                <td class="px-5 py-3 text-right text-gray-800">
                                    {{ $endorsement->additional_premium > 0 ? number_format($endorsement->additional_premium, 2) : '—' }}
                                </td>
                                <td class="px-5 py-3 text-right text-gray-800">
                                    {{ $endorsement->refund_premium > 0 ? number_format($endorsement->refund_premium, 2) : '—' }}
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        {{-- ─── Cancellation details (if cancelled) ─── --}}
        @if ($policy->cancellation_status)
            <div class="mt-4 bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h2 class="text-xs font-semibold text-green-800 uppercase tracking-wide mb-4">
                    Cancellation Details
                </h2>
                <dl class="text-sm space-y-2">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Status</dt>
                        <dd class="font-medium text-gray-800">{{ $policy->cancellation_status->label() }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Requested</dt>
                        <dd class="text-gray-800">{{ $policy->cancellation_requested_at?->format('d M Y, H:i') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Effective</dt>
                        <dd class="text-gray-800">{{ $policy->cancellation_effective_at?->format('d M Y, H:i') }}</dd>
                    </div>
                    @if ($policy->certificate_surrendered_at)
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Certificate Surrendered</dt>
                            <dd class="text-green-700 font-medium">
                                {{ $policy->certificate_surrendered_at->format('d M Y, H:i') }}
                            </dd>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Refund Due</dt>
                        <dd class="font-semibold text-green-800">
                            KES {{ number_format($policy->refund_amount, 2) }}
                        </dd>
                    </div>
                    @if ($policy->cancellation_reason)
                        <div class="pt-2 mt-2 border-t border-gray-100">
                            <dt class="text-gray-500 mb-1">Reason</dt>
                            <dd class="text-gray-700 whitespace-pre-line">{{ $policy->cancellation_reason }}</dd>
                        </div>
                    @endif
                    @if ($policy->cancelledBy)
                        <div class="flex justify-between text-xs text-gray-500 pt-2">
                            <dt>Processed by</dt>
                            <dd>{{ $policy->cancelledBy->name }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        @endif

        {{-- ─── Action buttons ─── --}}
        <div class="mt-6 flex flex-wrap gap-3">
            @if ($policy->status === \App\Enums\PolicyStatus::Active && $policy->cancellation_status === null)
                <a href="{{ route('policies.endorse', $policy) }}"
                    class="px-5 py-2 rounded-lg border border-green-400 text-green-700 hover:bg-green-50 text-sm font-medium shadow-sm">
                    Endorse Policy
                </a>
                <a href="{{ route('policies.cancel', $policy) }}"
                    class="px-5 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm font-medium shadow-sm">
                    Cancel Policy
                </a>
            @endif
        </div>

    </div>
@endsection