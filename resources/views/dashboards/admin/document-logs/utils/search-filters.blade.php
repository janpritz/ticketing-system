<div class="mt-4 bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="p-6">
        <form method="GET" class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <label for="q" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" name="q" id="q" value="{{ $q }}"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Search by file name, action, or staff name...">
            </div>
            <div class="sm:w-48">
                <label for="per_page" class="block text-sm font-medium text-gray-700 mb-1">Per Page</label>
                <select name="per_page" id="per_page"
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="25" {{ $per_page == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ $per_page == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $per_page == 100 ? 'selected' : '' }}>100</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit"
                    class="rounded-md bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2">
                    Search
                </button>
                @if ($q || $per_page != 25)
                    <a href="{{ route('admin.logs') }}"
                        class="rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-sm px-4 py-2">
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>
