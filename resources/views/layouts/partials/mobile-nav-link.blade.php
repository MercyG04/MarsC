@php
    $isActive = request()->routeIs($match ?? $route);
@endphp

<a href="{{ route($route) }}" class="block px-3 py-2 rounded-lg text-base font-medium transition
          {{ $isActive
    ? 'bg-green-50 text-green-800'
    : 'text-gray-700 hover:bg-gray-50 hover:text-green-800' }}">
    {{ $label }}
</a>