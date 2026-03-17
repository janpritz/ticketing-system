<div class="hidden sm:flex items-center gap-3">
    <form method="GET" action="{{ route('admin.users.index', $isDeletedView ? ['include_deleted' => '1'] : []) }}"
        class="flex items-center gap-2">
        @if ($isDeletedView)
            <input type="hidden" name="include_deleted" value="1">
        @endif
        <label class="relative block">
            <span class="sr-only">Search</span>
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                    <path
                        d="M15.5 14h-.79l-.28-.27a6.5 6.5 0 10-.71.71l.27.28v.79L20 21.5 21.5 20l-6-6zM10 15a5 5 0 110-10 5 5 0 010 10z" />
                </svg>
            </span>
            <input type="text" name="q" value="{{ $q ?? '' }}"
                placeholder="Search name, email, role, category"
                class="w-72 pl-9 pr-3 py-2 text-sm rounded-md border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
        </label>
        <button type="submit"
            class="rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-sm px-3 py-2">Search</button>
        @if (($q ?? '') !== '')
            <a href="{{ route('admin.users.index', $isDeletedView ? ['include_deleted' => '1'] : []) }}"
                class="text-sm text-slate-600 hover:text-slate-800">Clear</a>
        @endif
    </form>
</div>
