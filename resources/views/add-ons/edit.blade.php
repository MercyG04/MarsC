@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto py-8 px-4">

    <div class="mb-6">
        <a href="{{ route('add-ons.index') }}"
           class="text-sm text-green-700 hover:text-green-900 font-medium">
            ← Back to Add-Ons
        </a>
        <h1 class="text-2xl font-bold text-green-800 mt-2">Edit Add-On</h1>
        <p class="text-sm text-gray-500">
            Updating <strong>{{ $addOn->name }}</strong>
            <code class="text-xs bg-gray-100 px-1.5 py-0.5 rounded font-mono">{{ $addOn->code }}</code>
        </p>
    </div>

    {{-- Warning: rate change is forward-only --}}
    <div class="mb-4 p-3 rounded-lg bg-lilac-50 border border-purple-200 text-xs text-gray-700">
        <strong class="text-green-800 block mb-1">Rate changes are forward-only.</strong>
        Existing policies that include this add-on keep their original charged amount.
        Only new policies priced after this change will use the new rate.
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

    <form method="POST" action="{{ route('add-ons.update', $addOn) }}"
          class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-5">
        @csrf
        @method('PUT')

        {{-- Identity --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                <input type="text" name="name" value="{{ old('name', $addOn->name) }}" required
                       class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Code *</label>
                <input type="text" name="code" value="{{ old('code', $addOn->code) }}" required
                       class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm font-mono lowercase">
                <p class="text-xs text-gray-500 mt-1">
                    Changing the code may break external references. Edit with care.
                </p>
            </div>
        </div>

        {{-- Rate --}}
        <div class="rounded-lg bg-lime-50 border border-lime-200 p-4">
            <h2 class="text-sm font-semibold text-green-800 mb-3">Rate Configuration</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Rate Type *</label>
                    <select name="rate_type" id="rate-type" required
                            class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">
                        @foreach ($rateTypes as $type)
                            <option value="{{ $type->value }}"
                                    @selected(old('rate_type', $addOn->rate_type->value) === $type->value)>
                                {{ $type->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Rate Value *
                        <span id="rate-hint" class="text-xs font-normal text-gray-500"></span>
                    </label>
                    <input type="number" step="0.01" min="0" name="rate_value"
                           value="{{ old('rate_value', $addOn->rate_value) }}" required
                           class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">
                </div>
            </div>

            {{-- Show current rate for comparison --}}
            <div class="mt-3 text-xs text-gray-600">
                <strong>Current rate:</strong>
                @if ($addOn->rate_type === \App\Enums\AddOnRateType::Fixed)
                    KES {{ number_format($addOn->rate_value, 2) }}
                @else
                    {{ rtrim(rtrim(number_format($addOn->rate_value, 2), '0'), '.') }}% of sum insured
                @endif
            </div>
        </div>

        {{-- Description --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea name="description" rows="3"
                      class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm">{{ old('description', $addOn->description) }}</textarea>
        </div>

        {{-- Active toggle --}}
        <div class="rounded-lg bg-lilac-50 border border-purple-200 p-4">
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" name="is_active" value="1"
                       @checked(old('is_active', $addOn->is_active))
                       class="mt-1 rounded border-gray-300 text-green-600 focus:ring-green-500">
                <span class="text-sm text-gray-700">
                    <strong class="block text-green-800">Active</strong>
                    Deactivating hides this add-on from new policy pricing. Historical references remain intact.
                </span>
            </label>
        </div>

        {{-- Actions --}}
        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('add-ons.index') }}"
               class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium">
                Cancel
            </a>
            <button type="submit"
                    class="px-5 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-medium shadow-sm">
                Save Changes
            </button>
        </div>
    </form>
</div>

<script>
    (function () {
        const typeSelect = document.getElementById('rate-type');
        const hint = document.getElementById('rate-hint');

        function updateHint() {
            if (typeSelect.value === 'fixed') {
                hint.textContent = '(KES amount)';
            } else if (typeSelect.value === 'percentage') {
                hint.textContent = '(%)';
            } else {
                hint.textContent = '';
            }
        }

        typeSelect.addEventListener('change', updateHint);
        updateHint();
    })();
</script>
@endsection