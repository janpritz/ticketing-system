@if (!empty($isDeletedView))
    <div class="hidden sm:flex items-center gap-2">
        <a href="{{ route('admin.announcements.index') }}"
            class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-sm font-medium px-3 py-2">
            ← Back to Announcements
        </a>
    </div>
    <!-- Mobile Menu Button -->
    <div class="sm:hidden">
        <button id="mobileMenuBtnDeleted" type="button"
            class="inline-flex items-center justify-center w-10 h-10 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 transition-colors"
            aria-label="Menu">
            <svg class="h-6 w-6 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path id="hamburgerIconDeleted" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                <path id="closeIconDeleted" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" class="hidden" />
            </svg>
        </button>
    </div>
@else
    <div class="hidden sm:flex items-center gap-2">
        <!-- Trash Icon Button (View Deleted Announcements) -->
        <a href="{{ route('admin.announcements.deleted') }}"
            class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-sm font-medium px-3 py-2"
            aria-label="View Deleted Announcements">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            <span class="hidden lg:inline">Deleted</span>
            <span class="lg:hidden">Deleted</span>
        </a>

        <!-- Add Announcement Button -->
        <button id="addAnnouncementBtn" type="button"
            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-3 py-2"
            aria-label="Add Announcement">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span class="hidden lg:inline">New Announcement</span>
            <span class="lg:hidden">New</span>
        </button>
    </div>
    <!-- Mobile Menu Button -->
    <div class="sm:hidden">
        <button id="mobileMenuBtn" type="button"
            class="inline-flex items-center justify-center w-10 h-10 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 transition-colors"
            aria-label="Menu">
            <svg class="h-6 w-6 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path id="hamburgerIcon" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                <path id="closeIcon" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" class="hidden" />
            </svg>
        </button>
    </div>
@endif

<!-- Mobile Bottom Drawer -->
<div id="mobileDrawer" class="fixed inset-x-0 bottom-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40" id="drawerBackdrop"></div>
    <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-lg shadow-lg border-t border-gray-200 transform transition-transform duration-300 ease-out">
        <div class="p-4">
            <div class="flex justify-center mb-4">
                <div class="w-12 h-1 bg-gray-300 rounded-full"></div>
            </div>
            <div class="space-y-3">
                @if (!empty($isDeletedView))
                    <a href="{{ route('admin.announcements.index') }}"
                        class="flex items-center gap-3 w-full rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-sm font-medium px-4 py-3">
                        <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to Announcements
                    </a>
                @else
                    <!-- Add Announcement Button -->
                    <button id="mobileAddAnnouncementBtn" type="button"
                        class="flex items-center gap-3 w-full rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-3"
                        aria-label="Add Announcement">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        New Announcement
                    </button>

                    <!-- Trash Icon Button (View Deleted Announcements) -->
                    <a href="{{ route('admin.announcements.deleted') }}"
                        class="flex items-center gap-3 w-full rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-sm font-medium px-4 py-3"
                        aria-label="View Deleted Announcements">
                        <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Deleted Announcements
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mobileDrawer = document.getElementById('mobileDrawer');
    const drawerBackdrop = document.getElementById('drawerBackdrop');
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileMenuBtnDeleted = document.getElementById('mobileMenuBtnDeleted');
    const mobileAddAnnouncementBtn = document.getElementById('mobileAddAnnouncementBtn');

    // Icon elements
    const hamburgerIcon = document.getElementById('hamburgerIcon');
    const closeIcon = document.getElementById('closeIcon');
    const hamburgerIconDeleted = document.getElementById('hamburgerIconDeleted');
    const closeIconDeleted = document.getElementById('closeIconDeleted');

    let isDrawerOpen = false;

    function toggleIcons(showClose) {
        if (hamburgerIcon && closeIcon) {
            if (showClose) {
                hamburgerIcon.classList.add('hidden');
                closeIcon.classList.remove('hidden');
            } else {
                hamburgerIcon.classList.remove('hidden');
                closeIcon.classList.add('hidden');
            }
        }

        if (hamburgerIconDeleted && closeIconDeleted) {
            if (showClose) {
                hamburgerIconDeleted.classList.add('hidden');
                closeIconDeleted.classList.remove('hidden');
            } else {
                hamburgerIconDeleted.classList.remove('hidden');
                closeIconDeleted.classList.add('hidden');
            }
        }
    }

    function openDrawer() {
        mobileDrawer.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        isDrawerOpen = true;
        toggleIcons(true);
        // Trigger animation
        setTimeout(() => {
            mobileDrawer.querySelector('.transform').classList.remove('translate-y-full');
        }, 10);
    }

    function closeDrawer() {
        mobileDrawer.querySelector('.transform').classList.add('translate-y-full');
        isDrawerOpen = false;
        toggleIcons(false);
        setTimeout(() => {
            mobileDrawer.classList.add('hidden');
            document.body.style.overflow = '';
        }, 300);
    }

    // Add translate-y-full initially to hide drawer
    if (mobileDrawer) {
        mobileDrawer.querySelector('.transform').classList.add('translate-y-full');
    }

    // Menu button handlers
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function() {
            if (isDrawerOpen) {
                closeDrawer();
            } else {
                openDrawer();
            }
        });
    }

    if (mobileMenuBtnDeleted) {
        mobileMenuBtnDeleted.addEventListener('click', function() {
            if (isDrawerOpen) {
                closeDrawer();
            } else {
                openDrawer();
            }
        });
    }

    // Backdrop click to close
    if (drawerBackdrop) {
        drawerBackdrop.addEventListener('click', closeDrawer);
    }

    // Mobile add announcement button
    if (mobileAddAnnouncementBtn) {
        mobileAddAnnouncementBtn.addEventListener('click', function() {
            closeDrawer();
            // Trigger the same action as the desktop button
            setTimeout(() => {
                document.getElementById('addAnnouncementBtn').click();
            }, 300);
        });
    }
});
</script>
