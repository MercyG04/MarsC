@extends('layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto py-8 px-4">

        {{-- ─── Header ─── --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-green-800">Claims</h1>
                <p class="text-sm text-gray-500">
                    {{ $claims->total() }} {{ Str::plural('claim', $claims->total()) }} on record.
                </p>
            </div>
        </div>

        {{-- ─── Flash ─── --}}
        @if (session('success'))
            <div class="mb-4 p-3 rounded-lg bg-lime-50 border border-lime-200 text-lime-800 text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- ─── Search + Filter ─── --}}
        <form method="GET" action="{{ route('claims.index') }}" class="mb-6">
            <div class="flex flex-col md:flex-row gap-3">

                <input type="text" name="q" value="{{ $search }}"
                    placeholder="Search: claim number, policy, client, vehicle, or OB ref…"
                    class="flex-1 rounded-lg border-2 border-purple-200 bg-white px-4 py-3 text-base text-gray-800 placeholder-gray-400 focus:border-purple-500 focus:ring-4 focus:ring-purple-100 focus:outline-none transition">

                <select name="status"
                    class="rounded-lg border-2 border-purple-200 bg-white px-4 py-3 text-base text-gray-800 focus:border-purple-500 focus:ring-4 focus:ring-purple-100 focus:outline-none transition md:w-56">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $s)
                        <option value="{{ $s->value }}" @selected($status === $s->value)>
                            {{ $s->label() }}
                        </option>
                    @endforeach
                </select>

                <button type="submit"
                    class="px-5 py-3 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-medium shadow-sm">
                    Search
                </button>

                @if ($search || $status)
                    <a href="{{ route('claims.index') }}"
                        class="px-5 py-3 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium text-center">
                        Clear
                    </a>
                @endif
            </div>
        </form>

        {{-- ─── Table ─── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[900px]">
                    <thead class="bg-green-50 text-green-900 text-left text-xs uppercase tracking-wide">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Claim No.</th>
                            <th class="px-5 py-3 font-semibold">Policy</th>
                            <th class="px-5 py-3 font-semibold">Client</th>
                            <th class="px-5 py-3 font-semibold">Incident</th>
                            <th class="px-5 py-3 font-semibold text-right">Claimed</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($claims as $claim)
                            <tr class="hover:bg-purple-50/40 transition">
                                <td class="px-5 py-3">
                                    <div class="font-mono text-xs font-semibold text-gray-800">
                                        {{ $claim->claim_number }}
                                    </div>
                                    <div class="text-xs text-gray-500 mt-0.5">
                                        Reported {{ $claim->reported_at->format('d M Y') }}
                                    </div>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="font-mono text-xs text-gray-700">
                                        {{ $claim->policy->policy_number }}
                                    </div>
                                    <div class="text-xs font-mono text-gray-500">
                                        {{ $claim->policy->vehicle->registration_number }}
                                    </div>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="font-medium text-gray-800">
                                        {{ $claim->policy->client->first_name }}
                                        {{ $claim->policy->client->last_name }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $claim->policy->client->phone_number }}
                                    </div>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="text-gray-700">
                                        {{ $claim->incident_date->format('d M Y') }}
                                    </div>
                                    <div class="text-xs text-gray-500">{{ $claim->claim_type->label() }}</div>
                                </td>
                                <td class="px-5 py-3 text-right font-medium text-gray-800">
                                    KES {{ number_format($claim->claimed_amount, 2) }}
                                </td>
                                <td class="px-5 py-3">
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
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $statusClasses }}">
                                        {{ $claim->status->label() }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <a href="{{ route('claims.show', $claim) }}"
                                        class="text-green-700 hover:text-green-900 font-medium">View →</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center text-gray-400">
                                    @if ($search || $status)
                                        No claims matched your search.
                                        <a href="{{ route('claims.index') }}"
                                            class="text-green-700 font-medium hover:text-green-900 ml-1">
                                            Clear filters
                                        </a>
                                    @else
                                        No claims on record.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            {{ $claims->links() }}
        </div>

    </div>
@endsection