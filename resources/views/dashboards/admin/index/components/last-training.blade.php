<div class="block bg-white rounded-xl border border-gray-200 p-4 hover:bg-gray-100 transition-colors">
    <div class="flex items-start justify-between">
        <div>
            <div class="text-xs font-medium text-slate-500">Last Rasa Training</div>
            <div class="mt-2"><span id="lastTrainingValue"
                    class="text-2xl sm:text-2xl font-bold text-slate-900">{{ $lastTraining ?? 'Never' }}</span>
            </div>
        </div>
        <div class="rounded-md bg-purple-50 p-2 text-purple-600 border border-purple-100">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                <path
                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
            </svg>
        </div>
    </div>
</div>
