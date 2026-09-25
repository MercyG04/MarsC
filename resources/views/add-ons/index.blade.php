@extends('layouts.app')

@section('content')
    <div class="max-w-5xl mx-auto py-8 px-4">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-green-800">Add-Ons Catalog</h1>
                <p class="text-sm text-gray-500">
                    Optional covers available when pricing a policy.
                    Rate changes apply to <strong>new</strong> policies only — historical policies keep their original
                    pricing.
                </p>
            </div>
            <a href="{{ route('add-ons.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white font-medium shadow-sm transition">
                + New Add-On
            </a>
        </div>

        @if (session('success'))
            <div class="mb-4 p-3 rounded-lg bg-lime-50 border border-lime-200 text-lime-800 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-green-50 text-green-900 text-left text-xs uppercase tracking-wide">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Name</th>
                        <th class="px-5 py-3 font-semibold">Code</th>
                        <th class="px-5 py-3 font-semibold">Rate</th>
                        <th class="px-5 py-3 font-semibold">Status</th>
                        <th class="px-5 py-3 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($addOns as $addOn)
                        <tr class="hover:bg-lilac-50/40 transition">
                            <td class="px-5 py-3">
                                <div class="font-medium text-gray-800">{{ $addOn->name }}</div>
                                @if ($addOn->description)
                                    <div class="text-xs text-gray-500 mt-0.5">{{ Str::limit($addOn->description, 70) }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <code
                                    class="text-xs bg-gray-100 px-1.5 py-0.5 rounded font-mono text-gray-700">{{ $addOn->code }}</code>
                            </td>
                            <td class="px-5 py-3">
                                @if ($addOn->rate_type === \App\Enums\AddOnRateType::Fixed)
                                    <span class="font-medium text-gray-800">
                                        KES {{ number_format($addOn->rate_value, 2) }}
                                    </span>
                                    <span class="text-xs text-gray-500">fixed</span>
                                @else
                                    <span class="font-medium text-gray-800">
                                        {{ rtrim(rtrim(number_format($addOn->rate_value, 2), '0'), '.') }}%
                                    </span>
                                    <span class="text-xs text-gray-500">of sum insured</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                @if ($addOn->is_active)
                                    <span
                                        class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-lime-100 text-lime-800">
                                        Active
                                    </span>
                                @else
                                    <span
                                        class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('add-ons.edit', $addOn) }}"
                                    class="text-green-700 hover:text-green-900 font-medium">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-gray-400">
                                No add-ons in the catalog yet.
                                <a href="{{ route('add-ons.create') }}"
                                    class="text-green-700 font-medium hover:text-green-900 ml-1">
                                    Create the first one →
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $addOns->links() }}
        </div>

        {{-- Immutability note --}}
        <div class="mt-6 rounded-lg bg-lilac-50 border border-purple-200 p-4 text-xs text-gray-600">
            <strong class="text-green-800">Rate changes are forward-only.</strong>
            Editing an add-on's rate updates the catalog — but every policy snapshots the charged
            amount at the time of sale, so historical policies are never affected. Deactivated add-ons
            stay in the catalog for historical reference but no longer appear when pricing new policies.
        </div>

    </div>
@endsection