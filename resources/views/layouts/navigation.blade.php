@auth

    @php
        $user = auth()->user();
    @endphp

    <nav class="bg-white border-b border-gray-100 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">

                {{-- ─── Left: Brand + Desktop Links ─── --}}
                <div class="flex items-center">

                    {{-- Brand --}}
                    <a href="{{ route('clients.index') }}" class="shrink-0 flex items-center gap-2">
                        <span class="text-xl font-bold text-green-800 tracking-tight">
                            MarsC
                        </span>
                    </a>

                    {{-- Desktop Nav Links --}}
                    <div class="hidden md:flex md:items-center md:ml-10 md:space-x-1">

                        {{-- Clients — everyone --}}
                        @include('layouts.partials.nav-link', [
                            'route' => 'clients.index',
                            'label' => 'Clients',
                        ])



                        {{-- Policies — everyone --}}
                        @include('layouts.partials.nav-link', [
                            'route' => 'policies.index',
                            'label' => 'Policies',
                            'match' => 'policies.*',
                        ])

                        {{-- Quotations — agents, admins --}}
                        @if ($user->hasRole(\App\Enums\UserRole::Agent, \App\Enums\UserRole::Admin))
                            @include('layouts.partials.nav-link', [
                                'route' => 'quotations.index',
                                'label' => 'Quotations',
                                'match' => 'quotations.*',
                            ])
                        @endif

                        {{-- Claims — claims officers, underwriters, admins --}}
                        @if (
                                $user->hasRole(
                                    \App\Enums\UserRole::ClaimsOfficer,
                                    \App\Enums\UserRole::Underwriter,
                                    \App\Enums\UserRole::Admin,
                                )
                            )
                            @include('layouts.partials.nav-link', [
                                'route' => 'claims.index',
                                'label' => 'Claims',
                                'match' => 'claims.*',
                            ])
                        @endif

                        {{-- Add-Ons — admin only --}}
                        @if ($user->hasRole(\App\Enums\UserRole::Admin))
                            @include('layouts.partials.nav-link', [
                                'route' => 'add-ons.index',
                                'label' => 'Add-Ons',
                                'match' => 'add-ons.*',
                            ])
                        @endif
                    </div>
                </div>

                {{-- ─── Right: User Info + Logout ─── --}}
                <div class="hidden md:flex md:items-center md:gap-3">

                    {{-- User info --}}
                    <div class="text-right">
                        <div class="text-sm font-medium text-gray-800 leading-tight">
                            {{ $user->name }}
                        </div>
                        <div class="text-xs text-gray-500 leading-tight">
                            {{ $user->role->label() }}
                        </div>
                    </div>

                    {{-- Logout --}}
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="px-3 py-1.5 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition">
                            Log Out
                        </button>
                    </form>
                </div>

                {{-- ─── Mobile Hamburger ─── --}}
                <div class="flex items-center md:hidden">
                    <button type="button" onclick="document.getElementById('mobile-menu').classList.toggle('hidden')"
                        class="inline-flex items-center justify-center p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- ─── Mobile Menu ─── --}}
        <div id="mobile-menu" class="hidden md:hidden border-t border-gray-100">
            <div class="px-4 py-3 space-y-1">

                @include('layouts.partials.mobile-nav-link', [
                    'route' => 'clients.index',
                    'label' => 'Clients',
                ])



                @include('layouts.partials.mobile-nav-link', [
                    'route' => 'policies.index',
                    'label' => 'Policies',
                    'match' => 'policies.*',
                ])

                @if ($user->hasRole(\App\Enums\UserRole::Agent, \App\Enums\UserRole::Admin))
                    @include('layouts.partials.mobile-nav-link', [
                        'route' => 'quotations.index',
                        'label' => 'Quotations',
                        'match' => 'quotations.*',
                    ])
                @endif

                @if (
                        $user->hasRole(
                            \App\Enums\UserRole::ClaimsOfficer,
                            \App\Enums\UserRole::Underwriter,
                            \App\Enums\UserRole::Admin,
                        )
                    )
                    @include('layouts.partials.mobile-nav-link', [
                        'route' => 'claims.index',
                        'label' => 'Claims',
                        'match' => 'claims.*',
                    ])
                @endif

                @if ($user->hasRole(\App\Enums\UserRole::Admin))
                    @include('layouts.partials.mobile-nav-link', [
                        'route' => 'add-ons.index',
                        'label' => 'Add-Ons',
                        'match' => 'add-ons.*',
                    ])
                @endif
            </div>

            {{-- Mobile user info + logout --}}
            <div class="border-t border-gray-100 px-4 py-3">
                <div class="text-sm font-medium text-gray-800">{{ $user->name }}</div>
                <div class="text-xs text-gray-500">{{ $user->role->label() }}</div>

                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit"
                        class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition">
                        Log Out
                    </button>
                </form>
            </div>
        </div>
    </nav>
@endauth