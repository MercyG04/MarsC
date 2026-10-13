@php
    $isActive = request()->routeIs($match ?? $route);
@endphp

<a href="{{ route($route) }}" class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium transition
          {{ $isActive
    ? 'bg-green-50 text-green-800'
    : 'text-gray-600 hover:text-green-800 hover:bg-gray-50' }}">
    {{ $label }}
</a>