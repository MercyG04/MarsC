@extends('layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto py-8 px-4">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-green-800">Valuations</h1>
                <p class="text-sm text-gray-500">Search by registration, chassis, or logbook number.</p>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-4 p-3 rounded-lg bg-lime-50 border border-lime-200 text-lime-800 text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- Search bar --}}
        <form method="GET" action="{{ route('valuations.index') }}" class="mb-6">
            <div class="flex gap-2">
                <input type="text" name="q" value="{{ $search }}" placeholder="Search: KDA777Z, chassis, or logbook…"
                    class="flex-1 rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">
                <button type="submit"
                    class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-medium shadow-sm">
                    Search
                </button>
                @if ($search)
                    <a href="{{ route('valuations.index') }}"
                        class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium">
                        Clear
                    </a>
                @endif
            </div>
        </form>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-green-50 text-green-900 text-left text-xs uppercase tracking-wide">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Date</th>
                        <th class="px-5 py-3 font-semibold">Vehicle</th>
                        <th class="px-5 py-3 font-semibold">Owner</th>
                        <th class="px-5 py-3 font-semibold">Firm</th>
                        <th class="px-5 py-3 font-semibold text-right">ACV</th>
                        <th class="px-5 py-3 font-semibold">Type</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($valuations as $valuation)
                        <tr class="hover:bg-lilac-50/40 transition">
                            <td class="px-5 py-3 text-gray-700">
                                {{ $valuation->valuation_date->format('d M Y') }}
                            </td>
                            <td class="px-5 py-3">
                                <div class="font-medium text-gray-800">{{ $valuation->vehicle->make }}
                                    {{ $valuation->vehicle->model }}</div>
                                <div class="text-xs font-mono text-gray-500">{{ $valuation->vehicle->registration_number }}
                                </div>
                            </td>
                            <td class="px-5 py-3 text-gray-700">
                                {{ $valuation->vehicle->client->first_name }}
                                {{ $valuation->vehicle->client->last_name }}
                            </td>
                            <td class="px-5 py-3 text-gray-600">{{ $valuation->valuation_firm }}</td>
                            <td class="px-5 py-3 text-right font-medium text-gray-800">
                                {{ number_format($valuation->actual_cash_value, 2) }}
                            </td>
                            <td class="px-5 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                        @if($valuation->valuation_type === \App\Enums\ValuationType::Initial) bg-lime-100 text-lime-800
                                        @elseif($valuation->valuation_type === \App\Enums\ValuationType::Disputed) bg-purple-100 text-purple-800
                                        @else bg-yellow-100 text-yellow-800
                                        @endif">
                                    {{ $valuation->valuation_type->label() }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('valuations.show', $valuation) }}"
                                    class="text-green-700 hover:text-green-900 font-medium">View →</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-gray-400">
                                @if ($search)
                                    No valuations found for "<strong>{{ $search }}</strong>".
                                @else
                                    No valuations on record yet.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $valuations->links() }}
        </div>
    </div>
@endsection