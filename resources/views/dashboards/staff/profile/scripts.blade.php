<script>
    function askForPermission() {
        Notification.requestPermission().then((permission) => {
            if (permission === 'granted') {
                // get service worker
                navigator.serviceWorker.ready.then((sw) => {
                    // subscribe
                    sw.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: '{{ env('PUBLIC_KEY') }}'
                    }).then((subscription) => {
                        try {
                            console.log(JSON.stringify(subscription));
                        } catch (_) {}
                        saveSub(JSON.stringify(subscription));
                    }).catch(function(err) {
                        console.error('Push subscription failed', err);
                        alert('Push subscription failed: ' + (err && err.message ? err.message :
                            'unknown'));
                    });
                }).catch(function(err) {
                    console.error('Service worker ready failed', err);
                    alert('Service worker not ready: ' + (err && err.message ? err.message :
                        'unknown'));
                });
            } else {
                // Permission was denied or dismissed - no action required
                console.info('Notification permission result:', permission);
            }
        }).catch(err => {
            console.error('Permission request failed', err);
            alert('Permission request failed: ' + (err && err.message ? err.message : 'unknown'));
        });
    }

    // Save subscription to DB
    function saveSub(sub) {
        // sub may be a JSON-stringified subscription or an object
        let payload;
        try {
            payload = (typeof sub === 'string') ? JSON.parse(sub) : sub;
        } catch (e) {
            console.error('Invalid subscription payload', e);
            return;
        }

        const body = {
            subscription: payload
        };

        if (window.axios && typeof window.axios.post === 'function') {
            window.axios.post("{{ route('staff.push.subscribe') }}", body)
                .then(function(response) {
                    console.log('Subscription saved', response.data);
                    // Optionally show a small success hint
                    try {
                        alert('Push subscription saved');
                    } catch (_) {}
                })
                .catch(function(error) {
                    console.error('Failed to save subscription via axios:', error);
                    alert('Failed to save subscription');
                });
        } else {
            // Fallback to fetch (include CSRF token)
            fetch("{{ route('staff.push.subscribe') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(body)
                })
                .then(r => r.json())
                .then(data => {
                    console.log('Subscription saved (fetch)', data);
                    try {
                        alert('Push subscription saved');
                    } catch (_) {}
                })
                .catch(err => {
                    console.error('Failed to save subscription via fetch:', err);
                    alert('Failed to save subscription');
                });
        }
    }

    function sendNotification() {
        if (window.axios && typeof window.axios.post === 'function') {
            window.axios.post("{{ route('staff.push.send') }}", {
                title: document.getElementById('title').value,
                body: document.getElementById('body').value,
                idOfProduct: document.getElementById('idOfProduct').value
            }).then(function(response) {
                alert('Send successful');
                console.log(response.data);
            }).catch(function(error) {
                console.error('Send failed:', error);
                alert('Send failed');
            });
        } else {
            // Fallback to fetch if axios isn't available
            fetch("{{ route('staff.push.send') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    title: document.getElementById('title').value,
                    body: document.getElementById('body').value,
                    idOfProduct: document.getElementById('idOfProduct').value
                })
            }).then(r => r.json()).then(data => {
                alert('Send successful');
                console.log(data);
            }).catch(err => {
                console.error(err);
                alert('Send failed');
            });
        }
    }
</script>
<script>
    // Email notification toggle (UI only - will be wired to backend later)
    document.addEventListener('DOMContentLoaded', function() {
        const btn = document.getElementById('emailNotifToggle');
        if (!btn) return;
        const label = document.getElementById('emailNotifLabel');
        const dot = document.getElementById('emailNotifDot');
        // Initialize state from server-side user preference if available
        let enabled = {{ json_encode($user->email_notifications ?? false) }};

        function applyState(on) {
            btn.setAttribute('aria-checked', on ? 'true' : 'false');
            if (on) {
                btn.classList.remove('bg-gray-200', 'text-gray-700');
                btn.classList.add('bg-emerald-600', 'text-white');
                label.textContent = 'On';
                dot.classList.remove('bg-white');
                dot.classList.add('bg-emerald-200');
            } else {
                btn.classList.remove('bg-emerald-600', 'text-white');
                btn.classList.add('bg-gray-200', 'text-gray-700');
                label.textContent = 'Off';
                dot.classList.remove('bg-emerald-200');
                dot.classList.add('bg-white');
            }
        }

        applyState(enabled);

        btn.addEventListener('click', function() {
            const newState = !enabled;
            // Optimistically apply state in UI
            applyState(newState);

            // Persist to server
            fetch("{{ route('staff.profile.email_notifications') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    enabled: newState
                })
            }).then(r => r.json()).then(json => {
                if (json && json.saved) {
                    enabled = !!json.enabled;
                    applyState(enabled);
                } else {
                    // Revert on failure
                    applyState(enabled);
                    alert('Failed to save email notification preference');
                }
            }).catch(err => {
                console.error('Failed to save email notification preference', err);
                applyState(enabled);
                alert('Failed to save email notification preference');
            });
        });
    });
</script>
<script>
    // Ticket details modal for Recent Activity (simple copy of admin details)
    (function() {
        const staffTicketModal = document.createElement('div');
        staffTicketModal.id = 'staffTicketModal';
        staffTicketModal.className = 'fixed inset-0 z-50 hidden overflow-y-auto';
        staffTicketModal.innerHTML = `
                <div class="absolute inset-0 bg-black/60 backdrop-blur-md" data-modal-backdrop></div>
                <div class="relative mx-auto my-0 sm:my-8 w-full h-full sm:h-auto sm:w-[95%] max-w-2xl flex items-center">
                    <div class="bg-white shadow-2xl w-full h-full sm:h-auto sm:max-h-[95vh] sm:max-w-2xl overflow-hidden sm:rounded-2xl flex flex-col">
                        <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                            <h3 id="stTicketNo" class="text-lg font-semibold text-gray-900">Ticket #</h3>
                            <button type="button" id="stCloseTicketModal" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg" aria-label="Close">&times;</button>
                        </div>
                        <div class="p-6 overflow-y-auto">
                            <div class="mb-4"><strong>Status:</strong> <span id="stStatus" class="text-sm text-gray-700"></span></div>
                            <div class="mb-4"><strong>Question:</strong><div id="stQuestion" class="mt-2 text-sm text-gray-900 whitespace-pre-wrap"></div></div>
                            <div class="mt-4"><strong>Category:</strong> <span id="stCategory" class="text-sm text-gray-700"></span></div>
                            <div class="mt-2"><strong>Updated:</strong> <span id="stDates" class="text-sm text-gray-700"></span></div>
                            <div class="mt-2"><strong>Email:</strong> <span id="stEmail" class="text-sm text-gray-700"></span></div>
                        </div>
                    </div>
                </div>
            `;
        document.body.appendChild(staffTicketModal);

        async function loadAndShowTicket(id) {
            if (!id) return;
            const url = `{{ url('/staff/tickets') }}/${encodeURIComponent(id)}`;
            try {
                const res = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });
                if (!res.ok) return;
                const t = await res.json();
                document.getElementById('stTicketNo').textContent = 'Ticket #' + (t.id || id);
                document.getElementById('stQuestion').textContent = t.question || '';
                document.getElementById('stStatus').textContent = t.status || '';
                document.getElementById('stCategory').textContent = t.category || '';
                document.getElementById('stDates').textContent = (t.updated_at || t.date_created) || '';
                document.getElementById('stEmail').textContent = t.email || '';

                staffTicketModal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            } catch (err) {
                console.error('Failed to load ticket', err);
            }
        }

        document.addEventListener('click', function(e) {
            const btn = e.target && e.target.closest ? e.target.closest('.btn-view') : null;
            if (!btn) return;
            const id = btn.getAttribute('data-id') || btn.dataset.id;
            if (!id) return;
            loadAndShowTicket(id);
        });

        document.addEventListener('click', function(e) {
            if (e.target && e.target.id === 'stCloseTicketModal') {
                staffTicketModal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
            if (e.target && e.target.closest && e.target.closest('[data-modal-backdrop]')) {
                if (!staffTicketModal.classList.contains('hidden')) {
                    staffTicketModal.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                }
            }
        });
    })();
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if ($errors->any())
            try {
                @if (old('password') || $errors->has('password'))
                    var modalEl = document.getElementById('passwordModal');
                @else
                    var modalEl = document.getElementById('editProfileModal');
                @endif
                if (modalEl) {
                    modalEl.classList.remove('hidden');
                    modalEl.setAttribute('aria-hidden', 'false');
                    document.body.classList.add('overflow-hidden');
                }
            } catch (e) {
                console.error('Failed to show modal on validation errors', e);
            }
        @endif
    });
</script>
<script>
    (function() {
        // Photo preview (kept separate and intact)
        const input = document.getElementById('photo');
        const preview = document.getElementById('photoPreview');

        if (input && preview) {
            input.addEventListener('change', function() {
                const file = this.files && this.files[0];
                if (!file) return;

                // Validate file type
                if (!/^image\/(png|jpeg|jpg)$/.test(file.type)) {
                    alert('Invalid file type. Please select a JPG or PNG image.');
                    this.value = '';
                    return;
                }

                // Validate file size (max 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('File is too large. Maximum size is 5MB.');
                    this.value = '';
                    return;
                }

                // Preview image
                const reader = new FileReader();
                reader.onload = e => {
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            });
        }

        // Push notifications registration
        try {
            navigator.serviceWorker.register("{{ url('sw.js') }}", {
                scope: './'
            });
        } catch (e) {
            console.warn('Service worker registration (profile) failed', e);
        }
    })();
</script>

@if (session('status'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            try {
                const msg = @json(session('status'));
                if (typeof Swal === 'function') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Profile updated',
                        text: msg || 'Your profile was updated.',
                        showConfirmButton: false,
                        timer: 1600
                    });
                } else {
                    // Fallback to global toast if SweetAlert isn't available
                    (window.showToast || function(t, m) {
                        alert(m);
                    })('success', msg || 'Your profile was updated.');
                }
            } catch (e) {
                console.error('Profile update notification failed', e);
            }
        });
    </script>
@endif
<script>
    // Simple modal toggle for Edit Profile modal (robust, admin-style)
    document.addEventListener('DOMContentLoaded', function() {
        try {
            const modal = document.getElementById('editProfileModal');
            if (!modal) return;

            const toggleBtns = document.querySelectorAll('[data-modal-toggle="editProfileModal"]');
            const closeBtns = modal.querySelectorAll('[data-modal-hide="editProfileModal"]');
            const backdrop = modal.querySelector('[data-modal-backdrop]');

            function showModal() {
                modal.classList.remove('hidden');
                modal.setAttribute('aria-hidden', 'false');
                document.body.classList.add('overflow-hidden');
            }

            function hideModal() {
                modal.classList.add('hidden');
                modal.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('overflow-hidden');
            }

            toggleBtns.forEach(btn => btn.addEventListener('click', (e) => {
                e.preventDefault();
                showModal();
            }));

            closeBtns.forEach(btn => btn.addEventListener('click', (e) => {
                e.preventDefault();
                hideModal();
            }));

            // Close when backdrop is clicked (admin style)
            if (backdrop) {
                backdrop.addEventListener('click', function() {
                    hideModal();
                });
            } else {
                // Fallback: clicking outside the panel (modal element) should close
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) hideModal();
                });
            }
        } catch (err) {
            console.error('Edit profile modal init failed', err);
        }
    });
</script>
<script>
    // Simple modal toggle for Password modal
    document.addEventListener('DOMContentLoaded', function() {
        try {
            const modal = document.getElementById('passwordModal');
            if (!modal) return;

            const toggleBtns = document.querySelectorAll('[data-modal-toggle="passwordModal"]');
            const closeBtns = modal.querySelectorAll('[data-modal-hide="passwordModal"]');
            const backdrop = modal.querySelector('[data-modal-backdrop]');

            function showModal() {
                modal.classList.remove('hidden');
                modal.setAttribute('aria-hidden', 'false');
                document.body.classList.add('overflow-hidden');
            }

            function hideModal() {
                modal.classList.add('hidden');
                modal.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('overflow-hidden');
            }

            toggleBtns.forEach(btn => btn.addEventListener('click', (e) => {
                e.preventDefault();
                showModal();
            }));
            closeBtns.forEach(btn => btn.addEventListener('click', (e) => {
                e.preventDefault();
                hideModal();
            }));

            if (backdrop) {
                backdrop.addEventListener('click', hideModal);
            } else {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) hideModal();
                });
            }

            // Show/hide password toggles
            try {
                const toggleNew = document.getElementById('toggleNewPassword');
                const toggleConfirm = document.getElementById('toggleConfirmPassword');
                const newInput = document.getElementById('password');
                const confirmInput = document.getElementById('password_confirmation');
                const eyeSvg =
                    '<path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
                const eyeOffSvg = '<path d="M3 3l18 18"/><path d="M10.58 10.58a3 3 0 104.83 4.83"/>';

                if (toggleNew && newInput) {
                    toggleNew.addEventListener('click', function() {
                        if (newInput.type === 'password') {
                            newInput.type = 'text';
                            try {
                                document.getElementById('toggleNewPasswordIcon').innerHTML = eyeOffSvg;
                            } catch (_) {}
                        } else {
                            newInput.type = 'password';
                            try {
                                document.getElementById('toggleNewPasswordIcon').innerHTML = eyeSvg;
                            } catch (_) {}
                        }
                    });
                }
                if (toggleConfirm && confirmInput) {
                    toggleConfirm.addEventListener('click', function() {
                        if (confirmInput.type === 'password') {
                            confirmInput.type = 'text';
                            try {
                                document.getElementById('toggleConfirmPasswordIcon').innerHTML =
                                    eyeOffSvg;
                            } catch (_) {}
                        } else {
                            confirmInput.type = 'password';
                            try {
                                document.getElementById('toggleConfirmPasswordIcon').innerHTML = eyeSvg;
                            } catch (_) {}
                        }
                    });
                }
            } catch (e) {
                /* non-fatal */
            }
        } catch (err) {
            console.error('Password modal init failed', err);
        }
    });
</script>
