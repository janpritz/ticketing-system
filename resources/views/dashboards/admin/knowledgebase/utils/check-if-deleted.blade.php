@if (!empty($isDeletedView))
    <div class="flex sm:hidden items-center gap-2">
        <a href="{{ route('admin.knowledgebase.index') }}"
            class="p-2 rounded-lg bg-white border border-gray-200 text-slate-700 hover:bg-gray-50"
            aria-label="Back to Knowledgebase Management (mobile)">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-700" viewBox="0 0 24 24" fill="currentColor">
                <path d="M15 6l-6 6 6 6" />
            </svg>
        </a>
    </div>
@endif
