@php
    $hasTrainingRequired = \App\Models\DocumentChange::where('training_required', true)
        ->where('training_completed', false)
        ->exists();

    // Define the pages where we want to hide the "Review Changes" button
    $hideButtonOn = ['admin/logs', 'admin/rasa-server'];
@endphp

@if ($hasTrainingRequired)
    <div id="globalTrainingAlert" class="bg-amber-50 border-l-4 border-amber-400 p-4 mb-2 mt-2">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-amber-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-amber-700">
                        {{-- Check if current request matches either excluded page --}}
                        @if (request()->is($hideButtonOn))
                            <strong>Data Out of Sync:</strong> To reduce training costs, system updates are queued for
                            automatic retraining every night at 9:00 PM. If changes needs to take effect immediately, a manual
                            trigger is available in the Rasa Server Controller (estimated duration: 10–20 minutes).
                        @else
                            <strong>Training Required:</strong> Documents have been modified and need chatbot
                            retraining.
                        @endif
                    </p>
                </div>
            </div>

            {{-- Only display the button if NOT on the excluded pages --}}
            @if (!request()->is($hideButtonOn))
                <div class="ml-4 flex-shrink-0">
                    <a href="{{ url('/admin/logs') }}"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-semibold rounded-md shadow-sm text-white bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-colors duration-200">
                        <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Review Changes
                    </a>
                </div>
            @endif
        </div>
    </div>
@endif
