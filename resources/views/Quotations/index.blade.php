@extends('layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto py-8 px-4">

        {{-- ─── Header ─── --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-green-800">Quotations</h1>
                <p class="text-sm text-gray-500">
                    {{ $quotations->total() }}
                    {{ Str::plural('quotation', $quotations->total()) }}
                    {{ auth()->user()->isAdmin() ? 'on record' : 'you have created' }}.
                </p>
            </div>
            <a href="{{ route('quotations.create') }}"
                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg bg-green-600 hover:bg-green-700 text-white font-medium shadow-sm transition">
                + New Quotation
            </a>
        </div>

        {{-- ─── Flash ─── --}}
        @if (session('success'))
            <div class="mb-4 p-3 rounded-lg bg-lime-50 border border-lime-200 text-lime-800 text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- ─── Search + Filter ─── --}}
        <form method="GET" action="{{ route('quotations.index') }}" class="mb-6">
            <div class="flex flex-col md:flex-row gap-3">

                <input type="text" name="q" value="{{ $search }}"
                    placeholder="Search: quote number, prospect name, phone, or email…"
                    class="flex-1 rounded-lg border border-purple-200 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none transition">

                <select name="status"
                    class="rounded-lg border border-purple-200 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none transition md:w-52">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $s)
                        <option value="{{ $s->value }}" @selected($status === $s->value)>
                            {{ $s->label() }}
                        </option>
                    @endforeach
                </select>

                <button type="submit"
                    class="px-5 py-2.5 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-medium shadow-sm">
                    Search
                </button>

                @if ($search || $status)
                    <a href="{{ route('quotations.index') }}"
                        class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium text-center">
                        Clear
                    </a>
                @endif
            </div>
        </form>

        {{-- ─── Table ─── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[800px]">
                    <thead class="bg-green-50 text-green-900 text-left text-xs uppercase tracking-wide">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Quote No.</th>
                            <th class="px-5 py-3 font-semibold">Prospect</th>
                            <th class="px-5 py-3 font-semibold">Vehicle</th>
                            <th class="px-5 py-3 font-semibold">Cover</th>
                            <th class="px-5 py-3 font-semibold text-right">Premium</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($quotations as $quote)
                            <tr class="hover:bg-purple-50/40 transition">
                                <td class="px-5 py-3">
                                    <div class="font-mono text-xs font-semibold text-gray-800">
                                        {{ $quote->quote_number }}
                                    </div>
                                    <div class="text-xs text-gray-500 mt-0.5">
                                        Valid until {{ $quote->valid_until->format('d M Y') }}
                                    </div>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="font-medium text-gray-800">{{ $quote->prospect_name }}</div>
                                    @if ($quote->prospect_phone)
                                        <div class="text-xs text-gray-500">{{ $quote->prospect_phone }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-3">
                                    <div class="font-medium text-gray-800">
                                        {{ $quote->vehicle_details['make'] ?? '—' }}
                                        {{ $quote->vehicle_details['model'] ?? '' }}
                                    </div>
                                    @if (!empty($quote->vehicle_details['registration_number']))
                                        <div class="text-xs font-mono text-gray-500">
                                            {{ $quote->vehicle_details['registration_number'] }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                            @if($quote->policy_type === \App\Enums\PolicyType::Comprehensive) bg-lime-100 text-lime-800
                                            @elseif($quote->policy_type === \App\Enums\PolicyType::ThirdParty) bg-yellow-100 text-yellow-800
                                            @else bg-purple-100 text-purple-800
                                            @endif">
                                        {{ $quote->policy_type->label() }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right font-medium text-gray-800">
                                    KES {{ number_format($quote->gross_premium, 2) }}
                                </td>
                                <td class="px-5 py-3">
                                    @php
                                        $statusClasses = match ($quote->status) {
                                            \App\Enums\QuotationStatus::Draft => 'bg-gray-100 text-gray-700',
                                            \App\Enums\QuotationStatus::Sent => 'bg-yellow-100 text-yellow-800',
                                            \App\Enums\QuotationStatus::Accepted => 'bg-lime-100 text-lime-800',
                                            \App\Enums\QuotationStatus::Declined => 'bg-red-100 text-red-800',
                                            \App\Enums\QuotationStatus::Expired => 'bg-purple-100 text-purple-800',
                                        };
                                    @endphp
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $statusClasses }}">
                                        {{ $quote->status->label() }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <a href="{{ route('quotations.show', $quote) }}"
                                        class="text-green-700 hover:text-green-900 font-medium">View →</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center text-gray-400">
                                    @if ($search || $status)
                                        No quotations matched your search.
                                        <a href="{{ route('quotations.index') }}"
                                            class="text-green-700 font-medium hover:text-green-900 ml-1">
                                            Clear filters
                                        </a>
                                    @else
                                        No quotations yet.
                                        <a href="{{ route('quotations.create') }}"
                                            class="text-green-700 font-medium hover:text-green-900 ml-1">
                                            Create the first one →
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ─── Pagination ─── --}}
        <div class="mt-4">
            {{ $quotations->links() }}
        </div>

    </div>
@endsection