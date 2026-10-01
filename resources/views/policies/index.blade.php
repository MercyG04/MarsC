@extends('layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto py-8 px-4">

        {{-- ─── Header ─── --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-green-800">Policies</h1>
                <p class="text-sm text-gray-500">
                    {{ $policies->total() }} {{ Str::plural('policy', $policies->total()) }} on record.
                </p>
            </div>
            <a href="{{ route('clients.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white font-medium shadow-sm transition">
                + Issue Policy
            </a>
        </div>

        {{-- ─── Flash message ─── --}}
        @if (session('success'))
            <div class="mb-4 p-3 rounded-lg bg-lime-50 border border-lime-200 text-lime-800 text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- ─── Search + Filter ─── --}}
        <form method="GET" action="{{ route('policies.index') }}" class="mb-6">
            <div class="flex flex-col md:flex-row gap-2">

                {{-- Search input --}}
                <input type="text" name="q" value="{{ $search }}"
                    placeholder="Search: policy number, client name, ID, or vehicle reg…"
                    class="flex-1 rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">

                {{-- Status filter --}}
                <select name="status"
                    class="rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm md:w-48">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $s)
                        <option value="{{ $s->value }}" @selected($status === $s->value)>
                            {{ $s->label() }}
                        </option>
                    @endforeach
                </select>

                {{-- Submit --}}
                <button type="submit"
                    class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-medium shadow-sm">
                    Search
                </button>

                {{-- Clear --}}
                @if ($search || $status)
                    <a href="{{ route('policies.index') }}"
                        class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium text-center">
                        Clear
                    </a>
                @endif
            </div>
        </form>

        {{-- ─── Results table ─── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-green-50 text-green-900 text-left text-xs uppercase tracking-wide">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Policy No.</th>
                        <th class="px-5 py-3 font-semibold">Client</th>
                        <th class="px-5 py-3 font-semibold">Vehicle</th>
                        <th class="px-5 py-3 font-semibold">Cover</th>
                        <th class="px-5 py-3 font-semibold text-right">Premium</th>
                        <th class="px-5 py-3 font-semibold">Status</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($policies as $policy)
                        <tr class="hover:bg-lilac-50/40 transition">
                            <td class="px-5 py-3">
                                <div class="font-mono text-xs font-semibold text-gray-800">
                                    {{ $policy->policy_number }}
                                </div>
                                <div class="text-xs text-gray-500 mt-0.5">
                                    {{ $policy->start_date->format('d M Y') }} – {{ $policy->end_date->format('d M Y') }}
                                </div>
                            </td>
                            <td class="px-5 py-3">
                                <div class="font-medium text-gray-800">
                                    {{ $policy->client->first_name }} {{ $policy->client->last_name }}
                                </div>
                                <div class="text-xs text-gray-500">{{ $policy->client->phone_number }}</div>
                            </td>
                            <td class="px-5 py-3">
                                <div class="font-medium text-gray-800">
                                    {{ $policy->vehicle->make }} {{ $policy->vehicle->model }}
                                </div>
                                <div class="text-xs font-mono text-gray-500">
                                    {{ $policy->vehicle->registration_number }}
                                </div>
                            </td>
                            <td class="px-5 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                        @if($policy->policy_type === \App\Enums\PolicyType::Comprehensive) bg-lime-100 text-lime-800
                                        @elseif($policy->policy_type === \App\Enums\PolicyType::ThirdParty) bg-yellow-100 text-yellow-800
                                        @else bg-purple-100 text-purple-800
                                        @endif">
                                    {{ $policy->policy_type->label() }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <div class="font-medium text-gray-800">
                                    KES {{ number_format($policy->gross_premium, 2) }}
                                </div>
                                @if ($policy->premium_balance > 0)
                                    <div class="text-xs text-yellow-700">
                                        Balance: {{ number_format($policy->premium_balance, 2) }}
                                    </div>
                                @elseif ($policy->premium_balance <= 0)
                                    <div class="text-xs text-green-700">Fully paid</div>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                        @if($policy->status === \App\Enums\PolicyStatus::Active) bg-lime-100 text-lime-800
                                        @elseif($policy->status === \App\Enums\PolicyStatus::Pending) bg-yellow-100 text-yellow-800
                                        @elseif($policy->status === \App\Enums\PolicyStatus::Cancelled) bg-gray-100 text-gray-600
                                        @else bg-purple-100 text-purple-800
                                        @endif">
                                    {{ $policy->status->label() }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('policies.show', $policy) }}"
                                    class="text-green-700 hover:text-green-900 font-medium">View →</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-gray-400">
                                @if ($search || $status)
                                    No policies found matching your search.
                                    <a href="{{ route('policies.index') }}"
                                        class="text-green-700 font-medium hover:text-green-900 ml-1">
                                        Clear filters
                                    </a>
                                @else
                                    No policies on record yet.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ─── Pagination ─── --}}
        <div class="mt-4">
            {{ $policies->links() }}
        </div>

    </div>
@endsection