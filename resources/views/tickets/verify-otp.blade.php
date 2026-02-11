@extends('layouts.app')

@section('title', 'Verify OTP - Tickets')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-indigo-50 to-blue-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Verify Your Identity</h1>
                <p class="text-gray-600">
                    For data privacy and security, please verify your identity with an OTP sent to your email.
                </p>
            </div>

            <!-- OTP Request Form -->
            <div id="otpRequestForm" class="space-y-6">
                <div>
                    <label for="identifier" class="block text-sm font-medium text-gray-700 mb-2">
                        Email or Ticket ID
                    </label>
                    <input 
                        type="text" 
                        id="identifier" 
                        name="identifier"
                        value="{{ $identifier }}"
                        readonly
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-700 cursor-not-allowed focus:outline-none"
                    >
                </div>

                <button 
                    type="button" 
                    id="sendOtpBtn"
                    class="w-full bg-indigo-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-indigo-700 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    Send OTP
                </button>

                <p class="text-xs text-gray-500 text-center">
                    We'll send a 6-digit code to your registered email address.
                </p>
            </div>

            <!-- OTP Input Form (Hidden initially) -->
            <div id="otpInputForm" class="space-y-6 hidden">
                <div>
                    <label for="otpCode" class="block text-sm font-medium text-gray-700 mb-2">
                        Enter OTP Code
                    </label>
                    <input 
                        type="text" 
                        id="otpCode" 
                        name="otp_code"
                        placeholder="000000"
                        maxlength="6"
                        inputmode="numeric"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg text-center text-2xl tracking-widest font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    >
                    <p class="text-xs text-gray-500 mt-2">
                        OTP expires in <span id="expiryTimer">15:00</span>
                    </p>
                </div>

                <button 
                    type="button" 
                    id="verifyOtpBtn"
                    class="w-full bg-indigo-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-indigo-700 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    Verify OTP
                </button>

                <div class="flex items-center justify-between">
                    <button 
                        type="button" 
                        id="resendOtpBtn"
                        disabled
                        class="text-sm text-indigo-600 hover:text-indigo-700 disabled:text-gray-400 disabled:cursor-not-allowed transition-colors"
                    >
                        Resend OTP (<span id="resendTimer">60</span>s)
                    </button>
                    <button 
                        type="button" 
                        id="backBtn"
                        class="text-sm text-gray-600 hover:text-gray-700 transition-colors"
                    >
                        Back
                    </button>
                </div>
            </div>
        </div>

        <!-- Info Box -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        <strong>Privacy Notice:</strong> Your ticket information is protected. OTP verification ensures only authorized users can access ticket details.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const identifier = document.getElementById('identifier').value;
    let otpExpiryTime = null;
    let resendCooldownTime = null;
    let expiryInterval = null;
    let resendInterval = null;

    // Send OTP
    document.getElementById('sendOtpBtn').addEventListener('click', async function() {
        const btn = this;
        btn.disabled = true;
        btn.textContent = 'Sending...';

        try {
            const response = await fetch('{{ route("tickets.send-otp") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    identifier: identifier
                })
            });

            const data = await response.json();

            if (data.success) {
                // Show OTP input form
                document.getElementById('otpRequestForm').classList.add('hidden');
                document.getElementById('otpInputForm').classList.remove('hidden');

                // Start expiry timer (15 minutes)
                otpExpiryTime = Date.now() + (15 * 60 * 1000);
                startExpiryTimer();

                // Start resend cooldown (60 seconds)
                resendCooldownTime = Date.now() + (60 * 1000);
                startResendCooldown();

                Swal.fire({
                    icon: 'success',
                    title: 'OTP Sent',
                    text: data.message,
                    position: 'top-end',
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    toast: true
                });

                // Focus on OTP input
                document.getElementById('otpCode').focus();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message,
                    confirmButtonColor: '#3085d6'
                });
                btn.disabled = false;
                btn.textContent = 'Send OTP';
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred. Please try again.',
                confirmButtonColor: '#3085d6'
            });
            btn.disabled = false;
            btn.textContent = 'Send OTP';
        }
    });

    // Verify OTP
    document.getElementById('verifyOtpBtn').addEventListener('click', async function() {
        const otpCode = document.getElementById('otpCode').value.trim();

        if (!otpCode || otpCode.length !== 6) {
            Swal.fire({
                icon: 'warning',
                title: 'Invalid OTP',
                text: 'Please enter a valid 6-digit OTP code.',
                confirmButtonColor: '#3085d6'
            });
            return;
        }

        const btn = this;
        btn.disabled = true;
        btn.textContent = 'Verifying...';

        try {
            const response = await fetch('{{ route("tickets.verify-otp-submit") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    identifier: identifier,
                    otp_code: otpCode
                })
            });

            const data = await response.json();

            if (data.success) {
                // Clear timers
                if (expiryInterval) clearInterval(expiryInterval);
                if (resendInterval) clearInterval(resendInterval);

                Swal.fire({
                    icon: 'success',
                    title: 'Verified!',
                    text: data.message,
                    confirmButtonColor: '#3085d6'
                }).then(() => {
                    // Redirect to tickets page
                    window.location.href = data.redirect_url;
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Verification Failed',
                    text: data.message,
                    confirmButtonColor: '#3085d6'
                });
                btn.disabled = false;
                btn.textContent = 'Verify OTP';
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred. Please try again.',
                confirmButtonColor: '#3085d6'
            });
            btn.disabled = false;
            btn.textContent = 'Verify OTP';
        }
    });

    // Resend OTP
    document.getElementById('resendOtpBtn').addEventListener('click', async function() {
        const btn = this;
        btn.disabled = true;
        btn.textContent = 'Resending...';

        try {
            const response = await fetch('{{ route("tickets.resend-otp") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    identifier: identifier
                })
            });

            const data = await response.json();

            if (data.success) {
                // Reset OTP input
                document.getElementById('otpCode').value = '';

                // Restart expiry timer
                otpExpiryTime = Date.now() + (15 * 60 * 1000);
                startExpiryTimer();

                // Restart resend cooldown
                resendCooldownTime = Date.now() + (60 * 1000);
                startResendCooldown();

                Swal.fire({
                    icon: 'success',
                    title: 'OTP Resent',
                    text: data.message,
                    position: 'top-end',
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    toast: true
                });

                document.getElementById('otpCode').focus();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Failed to resend OTP.',
                    confirmButtonColor: '#3085d6'
                });
                btn.disabled = false;
                btn.textContent = `Resend OTP (${Math.ceil((resendCooldownTime - Date.now()) / 1000)}s)`;
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred. Please try again.',
                confirmButtonColor: '#3085d6'
            });
            btn.disabled = false;
            btn.textContent = `Resend OTP (${Math.ceil((resendCooldownTime - Date.now()) / 1000)}s)`;
        }
    });

    // Back button
    document.getElementById('backBtn').addEventListener('click', function() {
        if (expiryInterval) clearInterval(expiryInterval);
        if (resendInterval) clearInterval(resendInterval);
        document.getElementById('otpRequestForm').classList.remove('hidden');
        document.getElementById('otpInputForm').classList.add('hidden');
        document.getElementById('otpCode').value = '';
    });

    // Auto-format OTP input (numbers only)
    document.getElementById('otpCode').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
    });

    // Start expiry timer
    function startExpiryTimer() {
        if (expiryInterval) clearInterval(expiryInterval);
        
        expiryInterval = setInterval(function() {
            const remaining = otpExpiryTime - Date.now();
            
            if (remaining <= 0) {
                clearInterval(expiryInterval);
                document.getElementById('expiryTimer').textContent = '00:00';
                
                Swal.fire({
                    icon: 'warning',
                    title: 'OTP Expired',
                    text: 'Your OTP has expired. Please request a new one.',
                    confirmButtonColor: '#3085d6'
                }).then(() => {
                    document.getElementById('otpRequestForm').classList.remove('hidden');
                    document.getElementById('otpInputForm').classList.add('hidden');
                    document.getElementById('otpCode').value = '';
                });
                return;
            }

            const minutes = Math.floor(remaining / 60000);
            const seconds = Math.floor((remaining % 60000) / 1000);
            document.getElementById('expiryTimer').textContent = 
                `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        }, 1000);
    }

    // Start resend cooldown
    function startResendCooldown() {
        if (resendInterval) clearInterval(resendInterval);
        
        const resendBtn = document.getElementById('resendOtpBtn');
        resendBtn.disabled = true;
        
        resendInterval = setInterval(function() {
            const remaining = resendCooldownTime - Date.now();
            
            if (remaining <= 0) {
                clearInterval(resendInterval);
                resendBtn.disabled = false;
                resendBtn.textContent = 'Resend OTP';
                return;
            }

            const seconds = Math.ceil(remaining / 1000);
            resendBtn.textContent = `Resend OTP (${seconds}s)`;
        }, 1000);
    }
</script>
@endsection
