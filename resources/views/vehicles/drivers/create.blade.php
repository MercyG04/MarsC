@extends('layouts.app')

@section('content')
    <div class="max-w-2xl mx-auto py-8 px-4">

        <div class="mb-6">
            <a href="{{ route('vehicles.show', $vehicle) }}"
                class="text-sm text-green-700 hover:text-green-900 font-medium">
                ← Back to vehicle
            </a>
            <h1 class="text-2xl font-bold text-green-800 mt-2">Add Driver</h1>
            <p class="text-sm text-gray-500">
                For {{ $vehicle->make }} {{ $vehicle->model }}
                (<span class="font-mono">{{ $vehicle->registration_number }}</span>)
            </p>
        </div>

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

        <form method="POST" action="{{ route('vehicles.drivers.store', $vehicle) }}"
            class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-5">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required maxlength="150"
                        class="w-full rounded-lg border border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">National ID *</label>
                    <input type="text" name="national_id" value="{{ old('national_id') }}" required maxlength="20"
                        class="w-full rounded-lg border border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm uppercase">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">DL Number *</label>
                    <input type="text" name="dl_number" value="{{ old('dl_number') }}" required maxlength="30"
                        class="w-full rounded-lg border border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm uppercase">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">DL Class *</label>
                    <input type="text" name="dl_class" value="{{ old('dl_class') }}" required maxlength="10"
                        placeholder="B, BCE, A"
                        class="w-full rounded-lg border border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm uppercase">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth *</label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" required
                        max="{{ now()->subYears(18)->toDateString() }}"
                        class="w-full rounded-lg border border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Driving Experience (years) *</label>
                    <input type="number" name="driving_experience" value="{{ old('driving_experience') }}" required min="0"
                        max="80"
                        class="w-full rounded-lg border border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">
                </div>
            </div>

            {{-- Primary checkbox — only if no driver exists yet, or no primary set --}}
            @if ($vehicle->drivers->where('is_primary', true)->isEmpty())
                <div class="rounded-lg bg-lime-50 border border-lime-200 p-4">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="is_primary" value="1" @checked(old('is_primary'))
                            class="mt-1 rounded border-gray-300 text-green-600 focus:ring-green-500">
                        <span class="text-sm text-gray-700">
                            <strong class="block text-green-800">Mark as primary driver</strong>
                            The primary driver is the main person authorized to drive this vehicle.
                            The first driver added is automatically marked primary.
                        </span>
                    </label>
                </div>
            @endif

            <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
                <a href="{{ route('vehicles.show', $vehicle) }}"
                    class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium">
                    Cancel
                </a>
                <button type="submit"
                    class="px-5 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-medium shadow-sm">
                    Add Driver
                </button>
            </div>
        </form>
    </div>
@endsection