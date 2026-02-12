@extends('layouts.app')

@section('title', 'Frequently Asked Questions')

@section('content')
    <div class="bg-white flex flex-col min-h-screen lg:h-screen">
        <!-- Navigation Bar -->
        <nav class="fixed top-0 left-0 right-0 z-50 flex-shrink-0" style="background-color: #FF9D00;">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center gap-4">
                        <div class="flex items-center mr-20">
                            <img src="{{ asset('logo-white.png') }}" alt="Sangkay Logo" class="h-8 w-8">
                            <span class="text-white font-bold text-sm tracking-wider ml-2">SANGKAY FAQs</span>
                        </div>

                        <!-- Menu Items -->
                        <div class="hidden md:flex items-center gap-4">
                            <a href="{{ route('faqs.index') }}"
                                class="text-white text-sm font-medium hover:text-gray-100">Home</a>
                            <a href="{{ route('about') }}" class="text-white text-sm font-medium hover:text-gray-100">About Us</a>
                            <a href="{{ route('contact') }}" class="text-white text-sm font-medium hover:text-gray-100">Contact Us</a>
                        </div>
                    </div>


                    <!-- Right: Profile -->
                    <div class="flex items-center gap-4">
                        <span id="greetingText" class="text-white text-sm hidden">HELLO, <span id="userEmail"></span></span>
                        <button id="profileBtn" class="p-2 rounded-full hover:bg-white/20 transition-colors"
                            title="Profile">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="flex-1 overflow-visible lg:overflow-hidden mt-16 lg:mt-24">
            <div class="max-w-7xl mx-auto h-full px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-0 h-full">
                    <!-- Left Column: Title and FAQs -->
                    <div class="lg:col-span-1 flex flex-col lg:overflow-hidden mt-12 lg:mt-20">
                        <div class="flex-shrink-0 pt-8 px-0 lg:pt-50 lg:px-0">
                            <h1 class="text-4xl font-bold text-gray-900 mb-2 px-8 lg:px-12">Frequently Asked</h1>
                            <h2 class="text-4xl font-bold text-gray-900 mb-8 px-8 lg:px-12">Questions</h2>

                            <!-- Search Bar -->
                            <div class="mb-8 px-8 lg:px-12">
                                <div class="relative">
                                    <input type="text" id="searchInput" placeholder="Search question here"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                    <button class="absolute right-3 top-3 text-gray-400 hover:text-gray-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- FAQ List - Scrollable -->
                        <div class="flex-1 lg:overflow-y-auto px-0 lg:px-0 pb-8">
                            <div class="space-y-3 px-8 lg:px-12">
                                @if ($faqs->isEmpty())
                                    <div class="text-center py-12">
                                        <p class="text-gray-600">No FAQs available at the moment.</p>
                                    </div>
                                @else
                                    @foreach ($faqs as $faq)
                                        <div
                                            class="faq-item border border-gray-200 rounded-lg overflow-hidden hover:shadow-md transition-shadow">
                                            <!-- FAQ Header (Question) -->
                                            <button
                                                class="faq-header w-full px-6 py-4 flex items-center justify-between bg-white hover:bg-gray-50 transition-colors text-left">
                                                <div class="flex-1">
                                                    <h3 class="text-gray-900 font-medium">{{ $faq->suggested_q }}</h3>
                                                    @if ($faq->general_topic)
                                                        <p class="text-sm text-gray-500 mt-1">{{ $faq->general_topic }}</p>
                                                    @endif
                                                </div>
                                                <svg class="faq-chevron w-5 h-5 text-gray-400 flex-shrink-0 ml-4 transition-transform duration-300"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                                </svg>
                                            </button>

                                            <!-- FAQ Body (Answer - Hidden by default) -->
                                            <div class="faq-body hidden bg-gray-50 px-6 py-4 border-t border-gray-200">
                                                <p class="text-gray-700 text-sm leading-relaxed">
                                                    {!! nl2br(e($faq->suggested_a)) !!}
                                                </p>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Illustration -->
                    <div
                        class="lg:col-span-1 
                                flex items-center justify-center 
                                mt-8 lg:mt-20 
                                h-auto lg:h-full">

                        <img src="{{ asset('faq_img.png') }}" alt="FAQ Illustration"
                            class="w-full 
                                    max-w-md mx-auto 
                                    h-auto 
                                    lg:w-full lg:h-full 
                                    object-contain lg:object-cover">
                    </div>

                </div>
            </div>
        </div>
    </div>

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

        // Initialize greeting on page load
        document.addEventListener('DOMContentLoaded', function() {
            const verifiedEmail = getCookie('verified_email');
            const greetingText = document.getElementById('greetingText');
            const userEmail = document.getElementById('userEmail');

            if (verifiedEmail) {
                userEmail.textContent = verifiedEmail;
                greetingText.classList.remove('hidden');
            }
        });

        // FAQ Accordion Toggle
        document.querySelectorAll('.faq-header').forEach(header => {
            header.addEventListener('click', function() {
                const item = this.closest('.faq-item');
                const body = item.querySelector('.faq-body');
                const chevron = item.querySelector('.faq-chevron');
                const isOpen = !body.classList.contains('hidden');

                // Close all other FAQs
                document.querySelectorAll('.faq-body').forEach(b => {
                    if (b !== body) {
                        b.classList.add('hidden');
                        b.closest('.faq-item').querySelector('.faq-chevron').style.transform =
                            'rotate(0deg)';
                    }
                });

                // Toggle current FAQ
                body.classList.toggle('hidden');
                chevron.style.transform = body.classList.contains('hidden') ? 'rotate(0deg)' :
                    'rotate(180deg)';
            });
        });

        // Search functionality
        const searchInput = document.getElementById('searchInput');
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            document.querySelectorAll('.faq-item').forEach(item => {
                const question = item.querySelector('.faq-header h3').textContent.toLowerCase();
                const answer = item.querySelector('.faq-body p').textContent.toLowerCase();
                const matches = question.includes(searchTerm) || answer.includes(searchTerm);
                item.style.display = matches ? 'block' : 'none';
            });
        });

        // Profile Icon Click Handler
        const profileBtn = document.getElementById('profileBtn');
        profileBtn.addEventListener('click', (e) => {
            e.stopPropagation();

            // Check if OTP session is active (check cookie)
            const verifiedEmail = getCookie('verified_email');

            if (verifiedEmail) {
                // OTP session is active, directly navigate to ticket history
                window.location.href = `{{ route('tickets.index') }}?email=${encodeURIComponent(verifiedEmail)}`;
            } else {
                // No OTP session, navigate to OTP verification page
                window.location.href = `{{ route('tickets.verify-otp') }}`;
            }
        });
    </script>
@endsection
