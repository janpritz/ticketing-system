@if (!empty($isDeletedView))
    <div class="hidden sm:flex items-center gap-2">
        <a href="{{ route('admin.knowledgebase.index') }}"
            class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-sm font-medium px-3 py-2">
            ← Back to Knowledgebase Management
        </a>
    </div>
@else
    <div class="hidden sm:flex items-center gap-2">
        <!-- Trash Icon Button (View Deleted Documents) -->
        <a href="{{ route('admin.knowledgebase.index', ['include_deleted' => '1']) }}"
            class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-sm font-medium px-3 py-2"
            aria-label="View Deleted Documents">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            <span class="hidden lg:inline">Deleted</span>
            <span class="lg:hidden">Trash</span>
        </a>

        <!-- Upload File Button -->
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

        <!-- History Button (global Document Management History modal) -->
        {{-- <button id="uploadLogsBtn" type="button"
            class="inline-flex items-center gap-2 rounded-lg bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium px-3 py-2"
            aria-label="History">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" width="16" height="16" fill="currentColor"
                viewBox="0 0 16 16">
                <path
                    d="M8.515 1.019A7 7 0 0 0 8 1V0a8 8 0 0 1 .589.022zm2.004.45a7 7 0 0 0-.985-.299l.219-.976q.576.129 1.126.342zm1.37.71a7 7 0 0 0-.439-.27l.493-.87a8 8 0 0 1 .979.654l-.615.789a7 7 0 0 0-.418-.302zm1.834 1.79a7 7 0 0 0-.653-.796l.724-.69q.406.429.747.91zm.744 1.352a7 7 0 0 0-.214-.468l.893-.45a8 8 0 0 1 .45 1.088l-.95.313a7 7 0 0 0-.179-.483m.53 2.507a7 7 0 0 0-.1-1.025l.985-.17q.1.58.116 1.17zm-.131 1.538q.05-.254.081-.51l.993.123a8 8 0 0 1-.23 1.155l-.964-.267q.069-.247.12-.501m-.952 2.379q.276-.436.486-.908l.914.405q-.24.54-.555 1.038zm-.964 1.205q.183-.183.35-.378l.758.653a8 8 0 0 1-.401.432z" />
                <path d="M8 1a7 7 0 1 0 4.95 11.95l.707.707A8.001 8.001 0 1 1 8 0z" />
                <path
                    d="M7.5 3a.5.5 0 0 1 .5.5v5.21l3.248 1.856a.5.5 0 0 1-.496.868l-3.5-2A.5.5 0 0 1 7 9V3.5a.5.5 0 0 1 .5-.5" />
            </svg>
            <span class="hidden lg:inline">History</span>
            <span class="lg:hidden">History</span>
        </button> --}}

        <!-- Refresh Documents Button -->
        {{-- <button id="refreshDocsBtn" type="button"
            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-3 py-2"
            aria-label="Refresh Documents">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span>Refresh</span>
        </button> --}}
    </div>
@endif
