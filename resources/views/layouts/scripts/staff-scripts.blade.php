<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggles = document.querySelectorAll('[data-collapse-toggle]');
        toggles.forEach(toggle => {
            toggle.addEventListener('click', function() {
                const targetId = this.getAttribute('data-collapse-toggle');
                const target = document.getElementById(targetId);
                if (target) {
                    target.classList.toggle('hidden');
                }
            });
        });
    });
</script>

<!-- Sidebar collapse/expand for mobile + desktop -->
<script>
    (function() {
        const toggleBtn = document.getElementById('sidebar-toggle');
        const sidebar = document.getElementById('default-sidebar');
        const content = document.getElementById('content-wrapper');
        const backdrop = document.getElementById('sidebar-backdrop');
        const STORAGE_KEY = 'staff.sidebar.open';

        if (!toggleBtn || !sidebar || !content) return;

        const mq = window.matchMedia('(max-width: 639.98px)');

        function isMobile() {
            return mq.matches;
        }

        function openDesktop() {
            sidebar.classList.remove('sm:-translate-x-full');
            sidebar.classList.add('sm:translate-x-0');
            content.classList.add('sm:ml-64');
            content.classList.remove('ml-0');
            if (backdrop) backdrop.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        function closeDesktop() {
            sidebar.classList.add('sm:-translate-x-full');
            sidebar.classList.remove('sm:translate-x-0');
            content.classList.remove('sm:ml-64');
            content.classList.add('ml-0');
            if (backdrop) backdrop.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        function openMobile() {
            sidebar.classList.remove('-translate-x-full');
            sidebar.classList.add('translate-x-0');
            if (backdrop) backdrop.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeMobile() {
            sidebar.classList.add('-translate-x-full');
            sidebar.classList.remove('translate-x-0');
            if (backdrop) backdrop.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        function applyState(open) {
            if (open) {
                if (isMobile()) openMobile();
                else openDesktop();
            } else {
                if (isMobile()) closeMobile();
                else closeDesktop();
            }
        }

        function readState() {
            try {
                const v = localStorage.getItem(STORAGE_KEY);
                return v === 'true';
            } catch (e) {
                return false;
            }
        }

        function writeState(open) {
            try {
                localStorage.setItem(STORAGE_KEY, open ? 'true' : 'false');
            } catch (e) {
                /* ignore */ }
        }

        function toggleSidebar() {
            const currentlyOpen = readState();
            const next = !currentlyOpen;
            applyState(next);
            writeState(next);
        }

        (function init() {
            sidebar.classList.add('sm:-translate-x-full');
            sidebar.classList.remove('sm:translate-x-0');
            sidebar.classList.add('-translate-x-full');
            sidebar.classList.remove('translate-x-0');
            content.classList.remove('sm:ml-64');
            content.classList.add('ml-0');
            if (backdrop) backdrop.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');

            const wasOpen = readState();
            applyState(wasOpen);
        })();

        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            toggleSidebar();
        });

        if (backdrop) {
            backdrop.addEventListener('click', function() {
                if (isMobile()) {
                    closeMobile();
                    writeState(false);
                }
            });
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && isMobile()) {
                closeMobile();
                writeState(false);
            }
        });

        mq.addEventListener('change', () => {
            const wasOpen = readState();
            applyState(wasOpen);
        });
    })();
</script>
