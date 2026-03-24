<div id="ticketsBottomDrawer"
    class="fixed left-0 right-0 bottom-0 z-50 bg-white border-t border-gray-200 shadow-lg transform translate-y-full transition-transform duration-200">
    <div class="px-4 py-3 flex items-center justify-between border-b">
        <div class="text-sm font-semibold text-slate-800">Filters & Sort</div>
        <button id="closeFiltersBtn" type="button"
            class="p-2 rounded-md text-slate-600 hover:text-slate-800 hover:bg-gray-50" aria-label="Close filters">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                <path d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    <div class="px-4 py-3 grid grid-cols-1 sm:grid-cols-4 gap-3">
        <div>
            <label for="filterStatus" class="block text-xs text-slate-600 mb-1">Status</label>
            <select id="filterStatus" class="w-full rounded-md border border-gray-300 bg-white text-sm px-3 py-2">
                <option value="all">All</option>
                <option value="Open">Open</option>
                <option value="Forwarded">Forwarded</option>
                <option value="Closed">Closed</option>
            </select>
        </div>
        <div>
            <label for="filterSort" class="block text-xs text-slate-600 mb-1">Sort by</label>
            <select id="filterSort" class="w-full rounded-md border border-gray-300 bg-white text-sm px-3 py-2">
                <option value="created_desc" selected>Created (newest)</option>
                <option value="created_asc">Created (oldest)</option>
                <option value="status_asc">Status (A-Z)</option>
                <option value="status_desc">Status (Z-A)</option>
                <option value="assignee_asc">Assignee (A-Z)</option>
                <option value="assignee_desc">Assignee (Z-A)</option>
            </select>
        </div>
        <div>
            <label for="filterRole" class="block text-xs text-slate-600 mb-1">Role</label>
            <select id="filterRole" class="w-full rounded-md border border-gray-300 bg-white text-sm px-3 py-2">
                <option value="">All</option>
                @php
                    $roles = \App\Models\Role::orderBy('name')->pluck('name')->toArray();
                @endphp
                @foreach ($roles as $r)
                    <option value="{{ $r }}">{{ $r }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="filterAssigneeId" class="block text-xs text-slate-600 mb-1">Assignee</label>
            <select id="filterAssigneeId" class="w-full rounded-md border border-gray-300 bg-white text-sm px-3 py-2">
                <option value="">All</option>
                @isset($users)
                    @foreach ($users as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}@if (!empty($u->role))
                                ({{ $u->role }})
                            @endif
                        </option>
                    @endforeach
                @endisset
            </select>
        </div>
        <div>
            <label for="filterPerPage" class="block text-xs text-slate-600 mb-1">Per page</label>
            <select id="filterPerPage" class="w-full rounded-md border border-gray-300 bg-white text-sm px-3 py-2">
                <option value="10">10</option>
                <option value="25" selected>25</option>
                <option value="50">50</option>
            </select>
        </div>
    </div>
    <div class="px-4 py-3 border-t flex items-center justify-end gap-2">
        <button id="resetFiltersBtn" type="button"
            class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm">Reset</button>
        <button id="applyFiltersBtn" type="button"
            class="rounded-md bg-blue-600 text-white px-4 py-2 text-sm">Apply</button>
    </div>
</div>
