<div id="mobileDrawer"
    class="fixed inset-x-0 bottom-0 z-40 transform translate-y-full transition-transform duration-300 ease-in-out sm:hidden">
    <div class="bg-white border-t border-gray-200 shadow-lg">
        <div class="p-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-medium text-gray-900">Actions</h3>
                <button id="mobileDrawerClose" type="button" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="space-y-3">
                @if (!empty($isDeletedView))
                    <a href="{{ route('staff.document_management.index') }}"
                        class="w-full inline-flex items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-sm font-medium px-4 py-2 text-slate-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M15 6l-6 6 6 6" />
                        </svg>
                        Back to Document Management
                    </a>
                @else
                    <a href="{{ route('staff.document_management.index', ['include_deleted' => '1']) }}"
                        class="w-full inline-flex items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-sm font-medium px-4 py-2 text-slate-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Deleted Documents
                    </a>
                    <button id="mobileUploadFileBtn" type="button"
                        class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium px-4 py-2">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        Upload Files
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
<div id="mobileDrawerOverlay" class="fixed inset-0 bg-black/50 z-30 hidden sm:hidden"></div>
