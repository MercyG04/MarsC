@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto py-8 px-4">

        {{-- ─── Header ─── --}}
        <div class="mb-6">
            <a href="{{ route('policies.show', $policy) }}" class="text-sm text-green-700 hover:text-green-900 font-medium">
                ← Back to Policy {{ $policy->policy_number }}
            </a>
            <h1 class="text-2xl font-bold text-green-800 mt-2">
                Endorsement <span class="font-mono">{{ $endorsement->endorsement_number }}</span>
            </h1>
            <p class="text-sm text-gray-500">
                {{ $endorsement->endorsement_type->label() }} ·
                Effective {{ $endorsement->effective_date->format('d M Y') }}
            </p>
        </div>

        {{-- ─── Summary cards ─── --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h2 class="text-xs font-semibold text-green-800 uppercase tracking-wide mb-3">
                    Financial Impact
                </h2>
                @if ($endorsement->additional_premium > 0)
                    <p class="text-2xl font-bold text-green-800">
                        + KES {{ number_format($endorsement->additional_premium, 2) }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">Additional premium charged</p>
                @elseif ($endorsement->refund_premium > 0)
                    <p class="text-2xl font-bold text-yellow-700">
                        − KES {{ number_format($endorsement->refund_premium, 2) }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">Refund issued to client</p>
                @else
                    <p class="text-2xl font-bold text-gray-400">KES 0.00</p>
                    <p class="text-xs text-gray-500 mt-1">No financial impact</p>
                @endif
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h2 class="text-xs font-semibold text-green-800 uppercase tracking-wide mb-3">
                    Recorded
                </h2>
                <p class="text-sm text-gray-800">
                    {{ $endorsement->created_at->format('d M Y, H:i') }}
                </p>
                <p class="text-xs text-gray-500 mt-1">
                    by {{ $endorsement->createdBy?->name ?? '—' }}
                </p>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h2 class="text-xs font-semibold text-green-800 uppercase tracking-wide mb-3">
                    Policy
                </h2>
                <p class="font-mono text-sm font-semibold text-gray-800">
                    {{ $policy->policy_number }}
                </p>
                <p class="text-xs text-gray-500 mt-1">
                    {{ $policy->client->first_name }} {{ $policy->client->last_name }}
                </p>
            </div>
        </div>

        {{-- ─── Changes diff ─── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-4">
            <h2 class="text-xs font-semibold text-green-800 uppercase tracking-wide mb-4">
                What Changed
            </h2>

            @php $changes = $endorsement->changes ?? []; @endphp

            @if (empty($changes))
                <p class="text-sm text-gray-400">No structured changes recorded.</p>
            @else
                <dl class="text-sm space-y-4">
                    @if (isset($changes['policy_type']))
                        <div>
                            <dt class="text-gray-500 mb-1">Policy Type</dt>
                            <dd class="flex items-center gap-2">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                    {{ $changes['policy_type']['from'] }}
                                </span>
                                <span class="text-gray-400">→</span>
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-lime-100 text-lime-800">
                                    {{ $changes['policy_type']['to'] }}
                                </span>
                            </dd>
                        </div>
                    @endif

                    @if (isset($changes['add_ons']))
                        <div>
                            <dt class="text-gray-500 mb-1">Add-Ons</dt>
                            <dd class="text-gray-800">
                                <span class="text-xs text-gray-500">From:</span>
                                {{ implode(', ', $changes['add_ons']['from'] ?: ['none']) }}
                                <br>
                                <span class="text-xs text-gray-500">To:</span>
                                {{ implode(', ', $changes['add_ons']['to'] ?: ['none']) }}
                            </dd>
                        </div>
                    @endif

                    @if (isset($changes['drivers']))
                        <div>
                            <dt class="text-gray-500 mb-1">Drivers</dt>
                            <dd class="text-gray-800">
                                <span class="text-xs text-gray-500">From:</span>
                                {{ implode(', ', $changes['drivers']['from'] ?: ['none']) }}
                                <br>
                                <span class="text-xs text-gray-500">To:</span>
                                {{ implode(', ', $changes['drivers']['to'] ?: ['none']) }}
                            </dd>
                        </div>
                    @endif
                </dl>
            @endif
        </div>

        {{-- ─── Reason ─── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h2 class="text-xs font-semibold text-green-800 uppercase tracking-wide mb-3">
                Reason for Endorsement
            </h2>
            <p class="text-sm text-gray-700 whitespace-pre-line">{{ $endorsement->reason }}</p>
        </div>

        {{-- ─── Immutability notice ─── --}}
        <div class="mt-6 rounded-lg bg-lilac-50 border border-purple-200 p-4 text-xs text-gray-600">
            <strong class="text-green-800">This record is immutable.</strong>
            Endorsements are append-only. To make a further change, record a new endorsement.

        </div>

    </div>
@endsection