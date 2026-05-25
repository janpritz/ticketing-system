<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const emailForm = document.getElementById('email-form');
        const otpForm = document.getElementById('otp-form');
        const emailInput = document.getElementById('verification-email');
        const otpInput = document.getElementById('verification-otp');
        const sendOtpBtn = document.getElementById('send-otp-btn');
        const verifyOtpBtn = document.getElementById('verify-otp-btn');
        const resendOtpBtn = document.getElementById('resend-otp-btn');
        const otpTimer = document.getElementById('otp-timer');
        
        let currentEmail = '';
        let otpTimerInterval = null;
        let countdown = 15 * 60; // 15 minutes in seconds

        // Get email from URL if present
        const urlParams = new URLSearchParams(window.location.search);
        const urlEmail = urlParams.get('email');
        
        // Use email from URL if available
        if (urlEmail) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (emailRegex.test(urlEmail)) {
                emailInput.value = urlEmail;
                currentEmail = urlEmail;
            }
        }

        // OTP Timer functions
        function startTimer() {
            updateTimer();
            otpTimerInterval = setInterval(updateTimer, 1000);
        }

        function updateTimer() {
            const minutes = Math.floor(countdown / 60);
            const seconds = countdown % 60;
            otpTimer.textContent = `Code expires in ${minutes}:${seconds.toString().padStart(2, '0')}`;
            
            if (countdown <= 0) {
                clearInterval(otpTimerInterval);
                otpTimer.textContent = 'Code has expired. Please request a new one.';
                verifyOtpBtn.disabled = true;
            }
            countdown--;
        }

        function resetTimer() {
            if (otpTimerInterval) {
                clearInterval(otpTimerInterval);
            }
            countdown = 15 * 60;
            verifyOtpBtn.disabled = false;
        }

        // Send OTP function
        async function sendOTP(email, isResend = false) {
            try {
                sendOtpBtn.disabled = true;
                sendOtpBtn.innerHTML = '<svg class="animate-spin h-4 w-4 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Sending...';

                const response = await fetch('{{ route("tickets.send-otp") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        identifier: email,
                        is_resend: isResend
                    })
                });

                const data = await response.json();

                if (data.success) {
                    emailForm.classList.add('hidden');
                    otpForm.classList.remove('hidden');
                    resetTimer();
                    startTimer();
                    
                    // Show success message
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Verification code sent to your email!',
                            position: 'top-end',
                            timer: 3000,
                            timerProgressBar: true,
                            showConfirmButton: false,
                            toast: true
                        });
                    }
                } else {
                    throw new Error(data.message || 'Failed to send verification code');
                }
            } catch (error) {
                console.error('Send OTP error:', error);
                if (window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'Failed to send verification code',
                    });
                } else {
                    alert('Error: ' + (error.message || 'Failed to send verification code'));
                }
            } finally {
                sendOtpBtn.disabled = false;
                sendOtpBtn.textContent = 'Send Verification Code';
            }
        }

        // Verify OTP function
        async function verifyOTP(email, otpCode) {
            try {
                verifyOtpBtn.disabled = true;
                verifyOtpBtn.innerHTML = '<svg class="animate-spin h-4 w-4 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Verifying...';

                const response = await fetch('{{ route("tickets.verify-otp") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        identifier: email,
                        otp_code: otpCode
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Verification successful - redirect to tickets page
                    localStorage.setItem('verified_email_' + email, 'true');
                    
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Email Verified!',
                            text: 'Redirecting to create ticket page...',
                            position: 'top-end',
                            timer: 2000,
                            timerProgressBar: true,
                            showConfirmButton: false,
                            toast: true,
                            willOpen: () => {
                                setTimeout(() => {
                                    window.location.href = `/tickets/create`;
                                }, 2000);
                            }
                        });
                    } else {
                        // Fallback - redirect immediately
                        setTimeout(() => {
                            window.location.href = `/tickets/create/${encodeURIComponent(email)}`;
                        }, 1000);
                    }
                } else {
                    throw new Error(data.message || 'Invalid verification code');
                }
            } catch (error) {
                console.error('Verify OTP error:', error);
                if (window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'Invalid verification code',
                    });
                } else {
                    alert('Error: ' + (error.message || 'Invalid verification code'));
                }
            } finally {
                verifyOtpBtn.disabled = false;
                verifyOtpBtn.textContent = 'Verify';
            }
        }

        // Event listeners
        sendOtpBtn.addEventListener('click', function() {
            const email = emailInput.value.trim();
            if (!email) {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Email Required',
                        text: 'Please enter your email address',
                    });
                } else {
                    alert('Please enter your email address');
                }
                return;
            }

            // Basic email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Invalid Email',
                        text: 'Please enter a valid email address',
                    });
                } else {
                    alert('Please enter a valid email address');
                }
                return;
            }

            currentEmail = email;
            sendOTP(email);
        });

        verifyOtpBtn.addEventListener('click', function() {
            const otpCode = otpInput.value.trim();
            if (!otpCode || otpCode.length !== 6) {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Invalid Code',
                        text: 'Please enter the 6-digit verification code',
                    });
                } else {
                    alert('Please enter the 6-digit verification code');
                }
                return;
            }

            verifyOTP(currentEmail, otpCode);
        });

        resendOtpBtn.addEventListener('click', function() {
            if (currentEmail) {
                sendOTP(currentEmail, true);
            }
        });

        // Allow Enter key submission
        emailInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendOtpBtn.click();
            }
        });

        otpInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                verifyOtpBtn.click();
            }
        });

        // Allow only numbers for OTP input
        otpInput.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
        });
    });
</script>