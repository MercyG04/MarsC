@extends('layouts.app')

@section('content')
    <div class="max-w-2xl mx-auto py-8 px-4">

        <div class="mb-6">
            <a href="{{ route('policies.show', $policy) }}" class="text-sm text-green-700 hover:text-green-900 font-medium">
                ← Back to policy
            </a>
            <h1 class="text-2xl font-bold text-green-800 mt-2">Cancel Policy</h1>
            <p class="text-sm text-gray-500">
                Policy <span class="font-mono font-medium">{{ $policy->policy_number }}</span>
                for {{ $policy->vehicle->registration_number }}
            </p>
        </div>

        {{-- Legal warning --}}
        <div class="mb-6 rounded-lg bg-yellow-50 border border-yellow-200 p-4 text-sm text-yellow-900">
            <strong class="block mb-1">This action stops cover immediately.</strong>
            The refund is calculated on a short-period basis, which includes an administrative deduction.
            You will have <strong>7 days</strong> to surrender the original and duplicate certificates
            to the underwriter. Failure to do so is a statutory offence under the Insurance
            (Motor Vehicle Third Party Risks) Act.
        </div>

        @if ($errors->any())
            <div class="mb-4 p-3 rounded-lg bg-yellow-50 border border-yellow-200 text-yellow-900 text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Refund preview --}}
        <div class="mb-6 bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h2 class="text-xs font-semibold text-green-800 uppercase tracking-wide mb-3">
                Estimated Refund
            </h2>
            <dl class="text-sm space-y-2">
                <div class="flex justify-between">
                    <dt class="text-gray-600">Gross Premium Paid</dt>
                    <dd class="text-gray-800">KES {{ number_format($policy->gross_premium, 2) }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-600">Remaining Days</dt>
                    <dd class="text-gray-800">
                        {{ now()->diffInDays($policy->end_date) }} of
                        {{ $policy->start_date->diffInDays($policy->end_date) }}
                    </dd>
                </div>
                <div class="flex justify-between border-t border-gray-100 pt-2 mt-2">
                    <dt class="font-semibold text-green-800">Refund on Short-Period Basis</dt>
                    <dd class="font-bold text-green-800 text-lg">
                        KES {{ number_format($policy->premium_balance > 0 ? $policy->premium_balance : 0, 2) }}
                    </dd>
                </div>
            </dl>
            <p class="text-xs text-gray-500 mt-3">
                The final refund is calculated by the system when you submit this form.
            </p>
        </div>

        <form method="POST" action="{{ route('policies.cancel.store', $policy) }}"
            class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Reason for cancellation *
                </label>
                <textarea name="reason" rows="4" required minlength="10" maxlength="500"
                    placeholder="e.g. Vehicle sold to a third party on 30/09/2026"
                    class="w-full rounded-lg border border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">{{ old('reason') }}</textarea>
                <p class="text-xs text-gray-500 mt-1">
                    This reason is recorded on the policy and forms part of the audit trail.
                </p>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('policies.show', $policy) }}"
                    class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium">
                    Keep Policy
                </a>
                <button type="submit" onclick="return confirm('Cancel this policy? Cover will stop immediately.')"
                    class="px-5 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm font-medium shadow-sm">
                    Cancel Policy
                </button>
            </div>
        </form>
    </div>
@endsection