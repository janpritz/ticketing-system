<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Configuration & State
    let identifier = document.getElementById('identifier').value;
    let otpExpiryTime = null;
    let resendCooldownTime = null;
    let expiryInterval = null;
    let resendInterval = null;

    // Helper: UI Selectors
    const elements = {
        identifierInput: document.getElementById('identifier'),
        otpRequestForm: document.getElementById('otpRequestForm'),
        otpInputForm: document.getElementById('otpInputForm'),
        otpCode: document.getElementById('otpCode'),
        expiryTimer: document.getElementById('expiryTimer'),
        resendBtn: document.getElementById('resendOtpBtn'),
        verifiedEmailField: document.getElementById('verifiedEmailField')
    };

    // Helper: Global Toast Config
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        confirmButtonColor: '#ff9d00'
    });

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    // Update identifier context
    elements.identifierInput.addEventListener('change', (e) => identifier = e.target.value);

    // --- OTP ACTIONS ---

    // Send/Resend Logic Helper
    async function handleOtpRequest(route, btnId, textId, spinnerId) {
        const btn = document.getElementById(btnId);
        const originalText = document.getElementById(textId).textContent;
        
        if (!isValidEmail(identifier)) {
            Swal.fire({ icon: 'warning', title: 'Invalid Email', text: 'Please enter a valid email address.' });
            return;
        }

        // UI Loading State
        btn.disabled = true;
        document.getElementById(textId).textContent = 'Processing...';
        document.getElementById(spinnerId)?.classList.remove('hidden');

        try {
            const response = await fetch(route, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ identifier })
            });

            const data = await response.json();

            if (data.success) {
                // UI Switch
                elements.verifiedEmailField.value = identifier;
                elements.otpRequestForm.classList.add('hidden');
                elements.otpInputForm.classList.remove('hidden');

                // Timer Management
                otpExpiryTime = Date.now() + (15 * 60 * 1000);
                resendCooldownTime = Date.now() + (60 * 1000);
                startExpiryTimer();
                startResendCooldown();

                Toast.fire({ icon: 'success', title: data.message || 'OTP Sent' });
                elements.otpCode.focus();
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            Swal.fire({ icon: 'error', title: 'Error', text: error.message || 'Request failed.' });
            btn.disabled = false;
        } finally {
            document.getElementById(textId).textContent = originalText;
            document.getElementById(spinnerId)?.classList.add('hidden');
        }
    }

    document.getElementById('sendOtpBtn').addEventListener('click', () => 
        handleOtpRequest('{{ route('tickets.send-otp') }}', 'sendOtpBtn', 'sendOtpText', 'sendOtpSpinner')
    );

    elements.resendBtn.addEventListener('click', () => 
        handleOtpRequest('{{ route('tickets.resend-otp') }}', 'resendOtpBtn', 'resendOtpBtn', null)
    );

    // Verify Logic
    document.getElementById('verifyOtpBtn').addEventListener('click', async function() {
        const otpCode = elements.otpCode.value.trim();
        if (otpCode.length !== 6) return Toast.fire({ icon: 'warning', title: 'Enter 6-digit OTP' });

        this.disabled = true;
        
        try {
            const response = await fetch('{{ route('tickets.verify-otp-submit') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ identifier, otp_code: otpCode })
            });

            const data = await response.json();

            if (data.success) {
                if (expiryInterval) clearInterval(expiryInterval);
                if (resendInterval) clearInterval(resendInterval);

                // Note: The cookie is set by the Controller response automatically.
                Swal.fire({ 
                    icon: 'success', 
                    title: 'Verified!', 
                    text: data.message,
                    confirmButtonColor: '#ff9d00'
                }).then(() => window.location.href = data.redirect_url);
            } else {
                Swal.fire({ icon: 'error', title: 'Failed', text: data.message });
                this.disabled = false;
            }
        } catch (error) {
            this.disabled = false;
        }
    });

    // --- UTILS ---

    document.getElementById('backBtn').addEventListener('click', () => {
        elements.otpRequestForm.classList.remove('hidden');
        elements.otpInputForm.classList.add('hidden');
    });

    elements.otpCode.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
    });

    function startExpiryTimer() {
        if (expiryInterval) clearInterval(expiryInterval);
        expiryInterval = setInterval(() => {
            const remaining = otpExpiryTime - Date.now();
            if (remaining <= 0) {
                clearInterval(expiryInterval);
                elements.otpRequestForm.classList.remove('hidden');
                elements.otpInputForm.classList.add('hidden');
                return Swal.fire({ icon: 'warning', title: 'OTP Expired' });
            }
            const mins = Math.floor(remaining / 60000);
            const secs = Math.floor((remaining % 60000) / 1000);
            elements.expiryTimer.textContent = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
        }, 1000);
    }

    function startResendCooldown() {
        if (resendInterval) clearInterval(resendInterval);
        elements.resendBtn.disabled = true;
        resendInterval = setInterval(() => {
            const remaining = Math.ceil((resendCooldownTime - Date.now()) / 1000);
            if (remaining <= 0) {
                clearInterval(resendInterval);
                elements.resendBtn.disabled = false;
                elements.resendBtn.textContent = 'Resend OTP';
            } else {
                elements.resendBtn.textContent = `Resend OTP (${remaining}s)`;
            }
        }, 1000);
    }
</script>