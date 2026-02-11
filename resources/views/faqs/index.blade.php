@extends('layouts.app')

@section('title', 'Frequently Asked Questions')

@section('content')
    <div class="bg-white flex flex-col h-screen">
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
                            <a href="{{ route('faqs.index') }}" class="text-white text-sm font-medium hover:text-gray-100">Home</a>
                            <a href="#" class="text-white text-sm font-medium hover:text-gray-100">About Us</a>
                            <a href="#" class="text-white text-sm font-medium hover:text-gray-100">Contact Us</a>
                        </div>
                    </div>


                    <!-- Right: Profile -->
                    <div class="flex items-center gap-4">
                        <span class="text-white text-sm">HELLO, USERNAME</span>
                        <button id="profileBtn" class="p-2 rounded-full hover:bg-white/20 transition-colors"
                            title="Profile">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                        <!-- Profile Dropdown -->
                        <div id="profileDropdown"
                            class="hidden absolute right-4 top-16 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                            <div class="p-4 border-b border-gray-200">
                                <p class="text-sm text-gray-600">Email Status:</p>
                                <p id="emailStatus" class="text-sm font-semibold text-gray-900">Not Verified</p>
                            </div>
                            <button id="verifyEmailBtn"
                                class="w-full text-left px-4 py-2 hover:bg-gray-50 text-sm text-gray-700 transition-colors">
                                Verify Email
                            </button>
                            <button id="viewHistoryBtn"
                                class="w-full text-left px-4 py-2 hover:bg-gray-50 text-sm text-gray-700 transition-colors border-t border-gray-200"
                                style="display: none;">
                                View Ticket History
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="flex-1 overflow-hidden mt-16 lg:mt-24">
            <div class="max-w-7xl mx-auto h-full px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-0 h-full">
                    <!-- Left Column: Title and FAQs -->
                    <div class="lg:col-span-1 flex flex-col overflow-hidden mt-20">
                        <div class="flex-shrink-0 pt-8 px-8 lg:pt-50 lg:px-12">
                            <h1 class="text-4xl font-bold text-gray-900 mb-2">Frequently Asked</h1>
                            <h2 class="text-4xl font-bold text-gray-900 mb-8">Questions</h2>

                            <!-- Search Bar -->
                            <div class="mb-8">
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
                        <div class="flex-1 overflow-y-auto px-8 pb-8">
                            <div class="space-y-3">
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
                    <div class="lg:col-span-1 flex items-center justify-center h-full lg:mt-20">
                        <img src="{{ asset('faq_img.png') }}" alt="FAQ Illustration" class="w-full h-full object-cover">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Email Verification Modal -->
    <div id="emailVerificationModal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>

        <!-- Modal Content -->
        <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Verify Your Email</h2>
                <p class="text-sm text-gray-600 mt-1">Enter your email to access your ticket history</p>
            </div>

            <!-- Body -->
            <div class="px-6 py-4 space-y-4">
                <div id="emailInputStep">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                    <input type="email" id="emailInput" placeholder="your@email.com"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                    <p id="emailError" class="text-sm text-red-600 mt-2 hidden"></p>
                </div>

                <div id="otpInputStep" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Verification Code</label>
                    <p class="text-sm text-gray-600 mb-3">We've sent a code to <span id="displayEmail"
                            class="font-semibold"></span></p>
                    <input type="text" id="otpInput" placeholder="000000" maxlength="6"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 text-center text-2xl tracking-widest">
                    <p id="otpError" class="text-sm text-red-600 mt-2 hidden"></p>
                    <button id="resendOtpBtn" class="text-sm text-orange-600 hover:text-orange-700 mt-3">Resend
                        Code</button>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 border-t border-gray-200 flex gap-3">
                <button id="closeModalBtn"
                    class="flex-1 px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                    Cancel
                </button>
                <button id="sendOtpBtn"
                    class="flex-1 px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors font-medium">
                    Send Code
                </button>
                <button id="verifyOtpBtn"
                    class="flex-1 px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors font-medium hidden">
                    Verify
                </button>
            </div>
        </div>
    </div>

    <script>
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

        // Profile Menu Toggle
        const profileBtn = document.getElementById('profileBtn');
        const profileDropdown = document.getElementById('profileDropdown');

        profileBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            profileDropdown.classList.toggle('hidden');
        });

        document.addEventListener('click', (e) => {
            if (!profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
                profileDropdown.classList.add('hidden');
            }
        });

        // Email Verification Modal
        const emailVerificationModal = document.getElementById('emailVerificationModal');
        const verifyEmailBtn = document.getElementById('verifyEmailBtn');
        const closeModalBtn = document.getElementById('closeModalBtn');
        const emailInput = document.getElementById('emailInput');
        const otpInput = document.getElementById('otpInput');
        const sendOtpBtn = document.getElementById('sendOtpBtn');
        const verifyOtpBtn = document.getElementById('verifyOtpBtn');
        const resendOtpBtn = document.getElementById('resendOtpBtn');
        const emailInputStep = document.getElementById('emailInputStep');
        const otpInputStep = document.getElementById('otpInputStep');
        const emailError = document.getElementById('emailError');
        const otpError = document.getElementById('otpError');
        const displayEmail = document.getElementById('displayEmail');
        const emailStatus = document.getElementById('emailStatus');
        const viewHistoryBtn = document.getElementById('viewHistoryBtn');

        // Check if email is already verified in session
        function checkEmailVerification() {
            const verifiedEmail = sessionStorage.getItem('verified_email');
            if (verifiedEmail) {
                emailStatus.textContent = verifiedEmail;
                emailStatus.classList.remove('text-gray-900');
                emailStatus.classList.add('text-green-600');
                verifyEmailBtn.textContent = 'Change Email';
                viewHistoryBtn.style.display = 'block';
            }
        }

        checkEmailVerification();

        verifyEmailBtn.addEventListener('click', () => {
            emailVerificationModal.classList.remove('hidden');
            emailInputStep.classList.remove('hidden');
            otpInputStep.classList.add('hidden');
            sendOtpBtn.classList.remove('hidden');
            verifyOtpBtn.classList.add('hidden');
            emailInput.value = '';
            otpInput.value = '';
            emailError.classList.add('hidden');
            otpError.classList.add('hidden');
        });

        closeModalBtn.addEventListener('click', () => {
            emailVerificationModal.classList.add('hidden');
        });

        emailVerificationModal.addEventListener('click', (e) => {
            if (e.target === emailVerificationModal) {
                emailVerificationModal.classList.add('hidden');
            }
        });

        // Send OTP
        sendOtpBtn.addEventListener('click', async () => {
            const email = emailInput.value.trim();
            emailError.classList.add('hidden');

            if (!email) {
                emailError.textContent = 'Please enter your email address';
                emailError.classList.remove('hidden');
                return;
            }

            if (!email.includes('@')) {
                emailError.textContent = 'Please enter a valid email address';
                emailError.classList.remove('hidden');
                return;
            }

            try {
                sendOtpBtn.disabled = true;
                sendOtpBtn.textContent = 'Sending...';

                const response = await fetch('{{ route('tickets.send-otp') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        email
                    })
                });

                const data = await response.json();

                if (data.success) {
                    displayEmail.textContent = email;
                    emailInputStep.classList.add('hidden');
                    otpInputStep.classList.remove('hidden');
                    sendOtpBtn.classList.add('hidden');
                    verifyOtpBtn.classList.remove('hidden');
                    otpInput.focus();
                } else {
                    emailError.textContent = data.message || 'Failed to send OTP';
                    emailError.classList.remove('hidden');
                }
            } catch (error) {
                emailError.textContent = 'An error occurred. Please try again.';
                emailError.classList.remove('hidden');
            } finally {
                sendOtpBtn.disabled = false;
                sendOtpBtn.textContent = 'Send Code';
            }
        });

        // Verify OTP
        verifyOtpBtn.addEventListener('click', async () => {
            const email = emailInput.value.trim();
            const otp = otpInput.value.trim();
            otpError.classList.add('hidden');

            if (!otp || otp.length !== 6) {
                otpError.textContent = 'Please enter a valid 6-digit code';
                otpError.classList.remove('hidden');
                return;
            }

            try {
                verifyOtpBtn.disabled = true;
                verifyOtpBtn.textContent = 'Verifying...';

                const response = await fetch('{{ route('tickets.verify-otp-submit') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        email,
                        otp
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Store verified email in session storage
                    sessionStorage.setItem('verified_email', email);
                    emailStatus.textContent = email;
                    emailStatus.classList.remove('text-gray-900');
                    emailStatus.classList.add('text-green-600');
                    verifyEmailBtn.textContent = 'Change Email';
                    viewHistoryBtn.style.display = 'block';
                    emailVerificationModal.classList.add('hidden');
                } else {
                    otpError.textContent = data.message || 'Invalid verification code';
                    otpError.classList.remove('hidden');
                }
            } catch (error) {
                otpError.textContent = 'An error occurred. Please try again.';
                otpError.classList.remove('hidden');
            } finally {
                verifyOtpBtn.disabled = false;
                verifyOtpBtn.textContent = 'Verify';
            }
        });

        // Resend OTP
        resendOtpBtn.addEventListener('click', async () => {
            const email = emailInput.value.trim();
            otpError.classList.add('hidden');

            try {
                resendOtpBtn.disabled = true;
                resendOtpBtn.textContent = 'Resending...';

                const response = await fetch('{{ route('tickets.resend-otp') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        email
                    })
                });

                const data = await response.json();

                if (data.success) {
                    otpError.classList.add('hidden');
                    otpInput.value = '';
                    otpInput.focus();
                } else {
                    otpError.textContent = data.message || 'Failed to resend OTP';
                    otpError.classList.remove('hidden');
                }
            } catch (error) {
                otpError.textContent = 'An error occurred. Please try again.';
                otpError.classList.remove('hidden');
            } finally {
                resendOtpBtn.disabled = false;
                resendOtpBtn.textContent = 'Resend Code';
            }
        });

        // View Ticket History
        viewHistoryBtn.addEventListener('click', () => {
            const verifiedEmail = sessionStorage.getItem('verified_email');
            if (verifiedEmail) {
                window.location.href = `{{ route('tickets.index') }}?email=${encodeURIComponent(verifiedEmail)}`;
            }
        });

        // Allow Enter key to send OTP
        emailInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && emailInputStep.classList.contains('hidden') === false) {
                sendOtpBtn.click();
            }
        });

        // Allow Enter key to verify OTP
        otpInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && otpInputStep.classList.contains('hidden') === false) {
                verifyOtpBtn.click();
            }
        });
    </script>
@endsection
