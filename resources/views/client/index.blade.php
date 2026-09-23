@extends('layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto py-8 px-4">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-green-800">Clients</h1>
                <p class="text-sm text-gray-500">All registered clients</p>
            </div>
            <a href="{{ route('clients.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white font-medium shadow-sm transition">
                + Onboard Client
            </a>
        </div>

        @if (session('success'))
            <div class="mb-4 p-3 rounded-lg bg-lime-50 border border-lime-200 text-lime-800 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-green-50 text-green-900 text-left">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Name</th>
                        <th class="px-4 py-3 font-semibold">National ID</th>
                        <th class="px-4 py-3 font-semibold">Phone</th>
                        <th class="px-4 py-3 font-semibold">County</th>
                        <th class="px-4 py-3 font-semibold">Vehicles</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($clients as $client)
                        <tr class="hover:bg-lilac-50/40 transition">
                            <td class="px-4 py-3 font-medium text-gray-800">
                                {{ $client->first_name }} {{ $client->last_name }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $client->national_id }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $client->phone_number }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $client->county }}</td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-800 text-xs font-semibold">
                                    {{ $client->vehicles_count ?? 0 }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('clients.show', $client) }}"
                                    class="text-green-700 hover:text-green-900 font-medium">View →</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-gray-400">
                                No clients yet. Click <strong>Onboard Client</strong> to add one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $clients->links() }}
        </div>
    </div>
@endsection