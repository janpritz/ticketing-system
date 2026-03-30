<script>
    (function() {
        // ========== Sidebar Toggle Logic ==========
        const toggleBtn = document.getElementById('sidebar-toggle');
        const sidebar = document.getElementById('default-sidebar');
        const content = document.getElementById('content-wrapper');
        const backdrop = document.getElementById('sidebar-backdrop');
        const STORAGE_KEY = 'admin.sidebar.open';

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
            } catch (e) {}
        }

        function toggleSidebar() {
            const currentlyOpen = readState();
            const next = !currentlyOpen;
            applyState(next);
            writeState(next);
        }

        // Initialize sidebar
        (function initSidebar() {
            // Normalize classes so we can reliably toggle later
            // Ensure desktop hidden baseline
            sidebar.classList.add('sm:-translate-x-full');
            sidebar.classList.remove('sm:translate-x-0');
            // Ensure mobile hidden baseline
            sidebar.classList.add('-translate-x-full');
            sidebar.classList.remove('translate-x-0');
            // Ensure content baseline
            content.classList.remove('sm:ml-64');
            content.classList.add('ml-0');
            if (backdrop) backdrop.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');

            // Apply persisted state
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

        // ========== Dropdown Logic ==========
        const dropdownConfigs = [{
                buttonId: 'userManagementDropdown',
                menuId: 'userManagementMenu',
                chevronId: 'userManagementChevron',
                storageKey: 'admin.dropdown.userManagement'
            },
            {
                buttonId: 'faqManagementDropdown',
                menuId: 'faqManagementMenu',
                chevronId: 'faqManagementChevron',
                storageKey: 'admin.dropdown.faqManagement'
            }
        ];

        function setDropdownState(config, isOpen) {
            const btn = document.getElementById(config.buttonId);
            const menu = document.getElementById(config.menuId);
            const chevron = document.getElementById(config.chevronId);

            if (!btn || !menu) return;

            if (isOpen) {
                menu.classList.remove('hidden');
                btn.setAttribute('aria-expanded', 'true');
                if (chevron) chevron.style.transform = 'rotate(180deg)';
            } else {
                menu.classList.add('hidden');
                btn.setAttribute('aria-expanded', 'false');
                if (chevron) chevron.style.transform = 'rotate(0deg)';
            }

            try {
                localStorage.setItem(config.storageKey, isOpen ? 'true' : 'false');
            } catch (e) {}
        }

        // Initialize Dropdowns
        dropdownConfigs.forEach(config => {
            const btn = document.getElementById(config.buttonId);
            const menu = document.getElementById(config.menuId);
            if (!btn || !menu) return;

            // Check for active links
            const currentPath = window.location.pathname;
            const hasActiveLink = Array.from(menu.querySelectorAll('a'))
                .some(link => link.getAttribute('href') && currentPath.startsWith(link.getAttribute(
                    'href')));

            const persisted = localStorage.getItem(config.storageKey) === 'true';

            if (persisted || hasActiveLink) {
                setDropdownState(config, true);
            }

            // Click Toggle
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const isCurrentlyOpen = !menu.classList.contains('hidden');

                // Close others
                dropdownConfigs.forEach(other => {
                    if (other.buttonId !== config.buttonId) setDropdownState(other, false);
                });

                // Toggle this one
                setDropdownState(config, !isCurrentlyOpen);
            });
        });

        // Global Click to close and Accessibility
        document.addEventListener('click', function(e) {
            dropdownConfigs.forEach(config => {
                const btn = document.getElementById(config.buttonId);
                const menu = document.getElementById(config.menuId);
                if (btn && menu && !btn.contains(e.target) && !menu.contains(e.target)) {
                    setDropdownState(config, false);
                }
            });
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                if (isMobile()) {
                    closeMobile();
                    writeState(false);
                }
                dropdownConfigs.forEach(config => setDropdownState(config, false));
            }
        });

        mq.addEventListener('change', () => {
            const wasOpen = readState();
            applyState(wasOpen);
        });
    })();
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>