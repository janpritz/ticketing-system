<button id="refreshBtn"
    class="ml-2 rounded-md border border-gray-200 bg-white px-3 py-2 text-sm inline-flex items-center gap-2"
    title="Refresh" aria-label="Refresh tickets">
    <svg id="refreshIcon" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12 6V3L8 7l4 4V8a4 4 0 110 8 4 4 0 01-4-4H6a6 6 0 106-6z" />
    </svg>
    <svg id="refreshSpinner" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 animate-spin hidden" viewBox="0 0 24 24"
        fill="none" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 4v5h.582M20 20v-5h-.581M5.636 5.636A9 9 0 0018.364 18.364" />
    </svg>
    <!-- check icon intentionally removed; spinner indicates both loading and brief success state -->
    <span class="hidden md:inline">Refresh</span>
</button>
