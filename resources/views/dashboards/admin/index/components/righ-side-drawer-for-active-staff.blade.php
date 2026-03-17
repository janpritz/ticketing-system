<div id="activeStaffDrawer" class="fixed inset-0 z-50 hidden" aria-hidden="true">
    <div id="asdOverlay" class="absolute inset-0 bg-black/40"></div>
    <div class="absolute right-0 top-0 h-full w-80 bg-white shadow-xl border-l border-gray-200 flex flex-col">
        <div class="h-12 flex items-center justify-between px-4 border-b">
            <div class="text-sm font-semibold text-slate-800">Active Staff</div>
            <button id="asdClose" type="button" class="text-slate-500 hover:text-slate-700" aria-label="Close">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div id="asdList" class="flex-1 overflow-y-auto p-3 text-sm">
            <!-- Filled dynamically -->
        </div>
    </div>
</div>