<!-- Public Navigation Bar Component -->
<nav class="flex-shrink-0" style="background-color: #FF9D00;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center gap-4">
                <div class="flex items-center {{ $logoMargin ?? 'mr-20' }}">
                    <a href="{{ route('faqs.index') }}" class="flex items-center">
                        <img src="{{ asset('logo-white.png') }}" alt="Sangkay Logo" class="h-8 w-8">
                        <span class="text-white font-bold text-sm tracking-wider ml-2">{{ 'SANGKAY FAQs' }}</span>
                    </a>
                </div>

                <!-- Menu Items -->
                <div class="hidden md:flex items-center gap-4">
                    <a href="{{ route('faqs.index') }}"
                        class="text-white text-sm font-medium hover:text-gray-100 {{ isset($active) && $active === 'home' ? 'border-b-2 border-white' : '' }}">Home</a>
                    <a href="{{ route('about') }}"
                        class="text-white text-sm font-medium hover:text-gray-100 {{ isset($active) && $active === 'about' ? 'border-b-2 border-white' : '' }}">About
                        Us</a>
                    <a href="{{ route('tickets.verify-otp', ['identifier' => rand(100000, 999999)]) }}"
                        class="text-white text-sm font-medium hover:text-gray-100 {{ isset($active) && $active === 'tickets.index' ? 'border-b-2 border-white' : '' }}">
                        My Tickets
                    </a>
                </div>
            </div>

            <!-- Right: Profile -->
            <div class="flex items-center gap-4">
                <span id="greetingText" class="text-white text-sm hidden md:inline"></span>
                <button id="profileBtn" class="p-2 rounded-full hover:bg-white/20 transition-colors" title="Profile">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</nav>

<script>
    // Helper function to get cookie value
    function getCookie(name) {
        const nameEQ = name + "=";
        const cookies = document.cookie.split(';');
        for (let i = 0; i < cookies.length; i++) {
            let cookie = cookies[i].trim();
            if (cookie.indexOf(nameEQ) === 0) {
                return decodeURIComponent(cookie.substring(nameEQ.length));
            }
        }
        return null;
    }
    // Profile Icon Click Handler
    const profileBtn = document.getElementById('profileBtn');
    if (profileBtn) {
        profileBtn.addEventListener('click', (e) => {
            e.stopPropagation();

            // Check if OTP session is active (check cookie)
            const verifiedEmail = getCookie('verified_email');

            if (verifiedEmail) {
                // OTP session is active, directly navigate to ticket history
                window.location.href =
                    `{{ route('tickets.index') }}?email=${encodeURIComponent(verifiedEmail)}`;
            } else {
                // No OTP session, navigate to OTP verification page
                window.location.href = `{{ route('tickets.verify-otp') }}`;
            }
        });
    }
</script>
