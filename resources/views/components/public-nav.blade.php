<nav class="flex-shrink-0 sticky top-0 z-40 shadow-md" style="background-color: #FF9D00;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            <!-- LEFT GROUP (logo + menu) -->
            <div class="flex items-center gap-5">

                <a href="{{ route('home') }}" class="flex items-center">
                    <img src="{{ asset('logo-white.png') }}" alt="Sangkay Logo" class="h-8 w-8">
                    <span class="text-white font-bold text-sm tracking-wider ml-2">
                        {{ 'SANGKAY TICKETS' }}
                    </span>
                </a>

                <!-- Desktop Menu -->
                @php
                    $linkBaseClasses = 'text-white text-sm font-medium transition-all px-1 pb-1 border-b-2';
                    $linkInactiveClasses = 'border-transparent hover:text-gray-100';
                    $linkActiveClasses = 'border-white text-white';
                @endphp

                <div class="hidden md:flex items-center gap-3">
                    {{-- 
                    <a href="{{ route('faqs.index') }}"
                        class="{{ $linkBaseClasses }} {{ isset($active) && $active === 'home' ? $linkActiveClasses : $linkInactiveClasses }}">
                        Home
                    </a>
                    --}}

                    {{-- 
                    <a href="{{ route('about') }}"
                        class="{{ $linkBaseClasses }} {{ isset($active) && $active === 'about' ? $linkActiveClasses : $linkInactiveClasses }}">
                        About Us
                    </a> 
                    --}}

                    <a href="{{ route('tickets.index') }}"
                        class="{{ $linkBaseClasses }} {{ isset($active) && $active === 'tickets.index' ? $linkActiveClasses : $linkInactiveClasses }}">
                        My Tickets
                    </a>
                </div>

            </div>

            <!-- Hamburger -->
            <div class="flex items-center md:hidden">
                <button @click="open = true" type="button"
                    class="p-2 rounded-md text-white hover:bg-white/20 transition-colors focus:outline-none focus:ring-2 focus:ring-white/50"
                    aria-label="Open Menu">

                    <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor"
                        class="bi bi-list" viewBox="0 0 16 16">
                        <path fill-rule="evenodd"
                            d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5" />
                    </svg>

                </button>
            </div>

        </div>
    </div>
</nav>
