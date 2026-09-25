@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto py-8 px-4">

        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-green-800">
                    {{ $client->first_name }} {{ $client->other_names }} {{ $client->last_name }}
                </h1>
                <p class="text-sm text-gray-500">Client #{{ $client->id }}</p>
            </div>
            <a href="{{ route('clients.index') }}" class="text-sm text-green-700 hover:text-green-900 font-medium">
                ← Back to Clients
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h2 class="text-sm font-semibold text-green-800 mb-3">Personal</h2>
                <dl class="text-sm space-y-2">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">National ID</dt>
                        <dd class="font-medium text-gray-800">{{ $client->national_id }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">KRA PIN</dt>
                        <dd class="font-medium text-gray-800">{{ $client->kra_pin }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Date of Birth</dt>
                        <dd class="font-medium text-gray-800">{{ $client->date_of_birth?->format('d M Y') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Gender</dt>
                        <dd class="font-medium text-gray-800 capitalize">{{ $client->gender }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Driving Experience</dt>
                        <dd class="font-medium text-gray-800">{{ $client->driving_experience }} yrs</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h2 class="text-sm font-semibold text-green-800 mb-3">Contact</h2>
                <dl class="text-sm space-y-2">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Phone</dt>
                        <dd class="font-medium text-gray-800">{{ $client->phone_number }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Email</dt>
                        <dd class="font-medium text-gray-800">{{ $client->email }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">County</dt>
                        <dd class="font-medium text-gray-800">{{ $client->county }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Location</dt>
                        <dd class="font-medium text-gray-800">{{ $client->physical_location }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold text-green-800">Vehicles</h2>
                <span class="text-xs px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-800 font-semibold">
                    {{ $client->vehicles->count() }}
                </span>
            </div>
            @if ($client->vehicles->isEmpty())
                <p class="text-sm text-gray-400">No vehicles yet.</p>
            @else
                <ul class="divide-y divide-gray-100 text-sm">
                    @foreach ($client->vehicles as $vehicle)
                        <li class="py-2 flex justify-between">
                            <span class="font-medium text-gray-800">
                                {{ $vehicle->year_of_manufacture }} {{ $vehicle->make }} {{ $vehicle->model }}
                            </span>
                            <span class="text-gray-500">{{ $vehicle->registration_number }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        @if ($client->consent_given)
            <div class="mt-4 p-3 rounded-lg bg-lime-50 border border-lime-200 text-xs text-lime-800">
                Consent given on {{ $client->consent_given_at?->format('d M Y, H:i') }}.
            </div>
        @endif
    </div>
@endsection