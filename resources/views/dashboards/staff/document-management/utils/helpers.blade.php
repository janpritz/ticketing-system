@if (!empty($isDeletedView))
    <div class="flex sm:hidden items-center gap-2">
        <a href="{{ route('staff.document_management.index') }}"
            class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-sm font-medium px-3 py-2 text-slate-700"
            aria-label="Back to Document Management">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-700" viewBox="0 0 24 24" fill="currentColor">
                <path d="M15 6l-6 6 6 6" />
            </svg>
            <span>Back to Document Management</span>
        </a>
    </div>
    <div class="hidden sm:flex items-center gap-2">
        <a href="{{ route('staff.document_management.index') }}"
            class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-sm font-medium px-3 py-2">
            ← Back to Document Management
        </a>
    </div>
@else
    <div class="flex sm:hidden items-center gap-2">
        <button id="mobileActionsToggle" type="button"
            class="p-2 rounded-lg bg-white border border-gray-200 text-slate-700 hover:bg-gray-50"
            aria-label="Open helper actions">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-700" viewBox="0 0 24 24" fill="none"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </div>
    <div class="hidden sm:flex items-center gap-2">
        <a href="{{ route('staff.document_management.index', ['include_deleted' => '1']) }}"
            class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-sm font-medium px-3 py-2"
            aria-label="View Deleted Documents">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            <span class="hidden lg:inline">Deleted</span>
            <span class="lg:hidden">Trash</span>
        </a>

        <button id="uploadFileBtn" type="button"
            class="inline-flex items-center gap-2 rounded-lg bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium px-3 py-2"
            aria-label="Upload File">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
            </svg>
            <span class="hidden lg:inline">Upload Files</span>
            <span class="lg:hidden">Upload</span>
        </button>

    </div>
@endif
