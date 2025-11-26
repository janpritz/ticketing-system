@extends('layouts.app')

@section('title', 'Check Ticket Status')

@section('content')
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="md:flex md:items-center md:justify-between">
            <div class="flex-1 text-center min-w-0 pt-5">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                    Check Ticket Status
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Enter your email address to verify and view your tickets
                </p>
            </div>
        </div>

        <div class="mt-8">
            <!-- Email Verification Section -->
            <div id="email-verification-section" class="bg-white shadow overflow-hidden sm:rounded-lg" style="display: none;">
                <div class="px-4 py-5 sm:p-6">
                    
                    <!-- Information Notice -->
                    <div class="mb-6">
                        <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-blue-800">How to check your ticket status</h3>
                                    <div class="mt-2 text-sm text-blue-700">
                                        <p>Enter your email address that you used when creating the ticket. We'll send you a verification code to confirm your identity before showing your tickets.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Email Input Form -->
                    <div id="email-form" class="space-y-4">
                        <div>
                            <label for="verification_email" class="block text-sm font-medium text-gray-700">Email Address</label>
                            <div class="mt-1">
                                <input type="email" id="verification_email" name="verification_email"
                                    class="py-2 px-3 block w-full border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    placeholder="Enter your email address">
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="button" id="send-otp-btn"
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                disabled>
                                Send Verification Code
                            </button>
                        </div>
                    </div>

                    <!-- OTP Verification Form -->
                    <div id="otp-form" class="space-y-4" style="display: none;">
                        <div>
                            <label for="otp_code" class="block text-sm font-medium text-gray-700">Verification Code</label>
                            <div class="mt-1">
                                <input type="text" id="otp_code" name="otp_code" maxlength="6"
                                    class="py-2 px-3 block w-full border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    placeholder="Enter 6-digit code" onkeypress="return isNumber(event)">
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-500">
                                <span id="otp-timer" class="hidden">Time remaining: <span id="countdown">--</span>s</span>
                            </div>
                            <div class="flex space-x-2">
                                <button type="button" id="resend-otp-btn" class="text-indigo-600 hover:text-indigo-500 text-sm font-medium hidden">
                                    Resend Code
                                </button>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-2">
                            <button type="button" id="reset-email-btn" class="py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Reset Email
                            </button>
                            <button type="button" id="verify-otp-btn"
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Verify & View Tickets
                            </button>
                        </div>
                    </div>
                </div>
            </div>

                    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            let otpTimer = null;
                            let countdown = 0;

                            // Always show email verification section
                            const emailSection = document.getElementById('email-verification-section');
                            emailSection.style.display = 'block';

                            function showOtpForm() {
                                document.getElementById('email-form').style.display = 'none';
                                document.getElementById('otp-form').style.display = 'block';
                                startTimer(900); // 15 minutes
                            }

                            function showEmailForm() {
                                document.getElementById('email-form').style.display = 'block';
                                document.getElementById('otp-form').style.display = 'none';
                            }

                            function isNumber(evt) {
                                evt = (evt) ? evt : window.event;
                                var charCode = (evt.which) ? evt.which : evt.keyCode;
                                if (charCode > 31 && (charCode < 48 || charCode > 57)) {
                                    return false;
                                }
                                return true;
                            }

                            function startTimer(seconds) {
                                countdown = seconds;
                                const timerElement = document.getElementById('otp-timer');
                                const resendButton = document.getElementById('resend-otp-btn');
                                const sendButton = document.getElementById('send-otp-btn');

                                timerElement.classList.remove('hidden');
                                resendButton.classList.add('hidden');
                                sendButton.disabled = true;

                                otpTimer = setInterval(function() {
                                    const minutes = Math.floor(countdown / 60);
                                    const secs = countdown % 60;
                                    document.getElementById('countdown').textContent = 
                                        String(minutes).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
                                    
                                    countdown--;
                                    
                                    if (countdown < 0) {
                                        clearInterval(otpTimer);
                                        timerElement.classList.add('hidden');
                                        resendButton.classList.remove('hidden');
                                        sendButton.disabled = false;
                                    }
                                }, 1000);
                            }

                            // Enable/disable Send OTP button based on form validation
                            document.getElementById('verification_email').addEventListener('input', function() {
                                const email = this.value;
                                const sendButton = document.getElementById('send-otp-btn');
                                
                                sendButton.disabled = !email || !email.includes('@');
                            });

                            // Send OTP
                            document.getElementById('send-otp-btn').addEventListener('click', function() {
                                const email = document.getElementById('verification_email').value;
                                const sendButton = this;
                                
                                if (!email) {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: 'Please enter a valid email address.'
                                    });
                                    return;
                                }

                                // Show loading animation
                                const originalText = sendButton.innerHTML;
                                sendButton.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Sending...';
                                sendButton.disabled = true;

                                fetch('{{ route("tickets.send-otp-status") }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                    },
                                    body: JSON.stringify({
                                        email: email
                                    })
                                })
                                .then(response => response.json())
                                .then(data => {
                                    // Reset button
                                    sendButton.innerHTML = originalText;
                                    sendButton.disabled = false;

                                    if (data.success) {
                                        showOtpForm();
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Code Sent',
                                            text: 'Please check your email for the verification code.'
                                        });
                                    } else {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error',
                                            text: data.message || 'Failed to send verification code.'
                                        });
                                    }
                                })
                                .catch(error => {
                                    // Reset button
                                    sendButton.innerHTML = originalText;
                                    sendButton.disabled = false;
                                    
                                    console.error('Error:', error);
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: 'Failed to send verification code. Please try again.'
                                    });
                                });
                            });

                            // Verify OTP
                            document.getElementById('verify-otp-btn').addEventListener('click', function() {
                                const email = document.getElementById('verification_email').value;
                                const otpCode = document.getElementById('otp_code').value;
                                const verifyButton = this;
                                
                                if (!otpCode || otpCode.length !== 6) {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: 'Please enter a valid 6-digit verification code.'
                                    });
                                    return;
                                }

                                // Show loading animation
                                const originalText = verifyButton.innerHTML;
                                verifyButton.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Verifying...';
                                verifyButton.disabled = true;

                                fetch('{{ route("tickets.verify-otp-status") }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                    },
                                    body: JSON.stringify({
                                        email: email,
                                        otp_code: otpCode
                                    })
                                })
                                .then(response => response.json())
                                .then(data => {
                                    // Reset button
                                    verifyButton.innerHTML = originalText;
                                    verifyButton.disabled = false;
                                    
                                    if (data.success) {
                                        clearInterval(otpTimer);
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Success',
                                            text: 'Email verified successfully! Redirecting to your tickets...',
                                            timer: 2000,
                                            timerProgressBar: true,
                                            showConfirmButton: false
                                        }).then(() => {
                                            // Redirect to tickets page with email
                                            window.location.href = data.redirect_url || `/tickets/${encodeURIComponent(email)}`;
                                        });
                                    } else {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error',
                                            text: data.message || 'Invalid verification code.'
                                        });
                                    }
                                })
                                .catch(error => {
                                    // Reset button
                                    verifyButton.innerHTML = originalText;
                                    verifyButton.disabled = false;
                                    
                                    console.error('Error:', error);
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: 'Failed to verify code. Please try again.'
                                    });
                                });
                            });

                            // Resend OTP
                            document.getElementById('resend-otp-btn').addEventListener('click', function() {
                                const email = document.getElementById('verification_email').value;
                                const sendButton = document.getElementById('send-otp-btn');
                                
                                // Simulate clicking the send button and show loading
                                const originalText = sendButton.innerHTML;
                                sendButton.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Sending...';
                                sendButton.disabled = true;

                                fetch('{{ route("tickets.send-otp-status") }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                    },
                                    body: JSON.stringify({
                                        email: email
                                    })
                                })
                                .then(response => response.json())
                                .then(data => {
                                    // Reset button
                                    sendButton.innerHTML = originalText;
                                    sendButton.disabled = false;
                                    
                                    if (data.success) {
                                        startTimer(900); // 15 minutes timer for resend
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Code Resent',
                                            text: 'Please check your email for the new verification code.'
                                        });
                                    } else {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error',
                                            text: data.message || 'Failed to resend code.'
                                        });
                                    }
                                })
                                .catch(error => {
                                    // Reset button
                                    sendButton.innerHTML = originalText;
                                    sendButton.disabled = false;
                                    
                                    console.error('Error:', error);
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: 'Failed to resend code. Please try again.'
                                    });
                                });
                            });

                            // Reset Email button
                            document.getElementById('reset-email-btn').addEventListener('click', function() {
                                clearInterval(otpTimer);
                                document.getElementById('verification_email').value = '';
                                document.getElementById('send-otp-btn').disabled = true;
                                showEmailForm();
                            });

                            // Restrict OTP input to numbers only
                            document.getElementById('otp_code').addEventListener('input', function() {
                                this.value = this.value.replace(/[^0-9]/g, '');
                            });
                        });
                    </script>
                </div>
            </div>
        </div>
    </div>
   
@endsection