@if ($logs->hasPages())
    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
                Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{ $logs->total() }} results
            </div>
            <div class="flex space-x-1">
                @if ($logs->onFirstPage())
                    <span
                        class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-300 bg-white border border-gray-300 cursor-not-allowed">
                        Previous
                    </span>
                @else
                    <a href="{{ $logs->previousPageUrl() }}"
                        class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50">
                        Previous
                    </a>
                @endif

                @foreach ($logs->getUrlRange(1, $logs->lastPage()) as $page => $url)
                    @if ($page == $logs->currentPage())
                        <span
                            class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 border border-blue-500">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}"
                            class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 hover:bg-gray-50">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach

                @if ($logs->hasMorePages())
                    <a href="{{ $logs->nextPageUrl() }}"
                        class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-r-md hover:bg-gray-50">
                        Next
                    </a>
                @else
                    <span
                        class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-300 bg-white border border-gray-300 cursor-not-allowed rounded-r-md">
                        Next
                    </span>
                @endif
            </div>
        </div>
    </div>
@endif
