<!-- FAQ Management -->
@extends('layouts.admin')

@section('title', 'FAQ Management')

@section('admin-content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">FAQ Management</h1>
            <p class="text-gray-600 mt-1">Review and approve pending FAQs from tickets</p>
        </div>
        <div class="flex items-center gap-2">
            <button 
                onclick="openAnalyzeModal()"
                class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition-colors"
            >
                Analyze Tickets
            </button>
        </div>
    </div>

    <!-- FAQ List -->
    @if($faqs->isEmpty())
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <div class="text-gray-500 text-lg">
                No pending FAQs to review
            </div>
        </div>
    @else
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 uppercase tracking-wider">
                            Semantic Key
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 uppercase tracking-wider">
                            Question
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 uppercase tracking-wider">
                            Answer
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 uppercase tracking-wider">
                            Ticket Count
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($faqs as $faq)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ Str::limit($faq->semantic_key, 50) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ Str::limit($faq->question, 100) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ Str::limit($faq->answer, 150) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                            {{ $faq->ticket_count }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <button 
                                onclick="approveFAQ('{{ $faq->semantic_key }}')"
                                class="px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700 transition-colors"
                                data-semantic-key="{{ $faq->semantic_key }}"
                            >
                                Approve
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<!-- Success Notification -->
<div id="success-notification" class="fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-[9999] hidden">
    FAQ approved successfully!
</div>

<!-- Error Notification -->
<div id="error-notification" class="fixed top-4 right-4 bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg z-[9999] hidden">
    Failed to approve FAQ. Please try again.
</div>

<!-- Analyze Tickets Modal - Modern Design -->
<div id="analyze-modal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-md transition-opacity" data-modal-backdrop></div>
    <!-- Centered panel with modern minimal design -->
    <div class="relative mx-auto my-0 sm:my-8 w-full h-full sm:h-auto sm:w-[95%] max-w-2xl flex items-center">
        <div class="bg-white shadow-2xl w-full h-full sm:h-auto sm:max-h-[95vh] sm:max-w-2xl overflow-hidden sm:rounded-2xl flex flex-col">

            <!-- Header - Minimal & Clean -->
            <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex items-center justify-between shrink-0">
                <div class="flex-1 min-w-0">
                    <h3 class="text-lg font-semibold text-gray-900">Analyze Tickets for FAQ Generation</h3>
                    <p class="text-sm text-gray-500 mt-1">Process closed tickets using OpenAI to generate FAQ clusters</p>
                </div>
                <button type="button"
                    class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-lg hover:bg-gray-100"
                    aria-label="Close" onclick="closeAnalyzeModal()">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Content - Scrollable -->
            <div class="flex-1 overflow-y-auto px-6 py-5 space-y-5">

                <!-- Unprocessed Tickets Info -->
                <div class="bg-blue-50 rounded-xl p-4 border border-blue-100">
                    <div class="flex items-center gap-2 mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-xs font-medium text-blue-700 uppercase tracking-wide">Unprocessed Tickets</span>
                    </div>
                    <div class="text-2xl font-bold text-blue-900" id="unprocessed-count">0</div>
                    <p class="text-blue-700 text-xs mt-2">
                        These closed tickets will be analyzed by OpenAI to generate FAQ clusters.
                    </p>
                </div>

                <!-- Progress Section (Hidden by default) -->
                <div id="progress-section" class="hidden space-y-3">
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div id="progress-bar" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                    <p id="progress-text" class="text-sm text-gray-600">Initializing analysis...</p>
                </div>

                <!-- Results Section (Hidden by default) -->
                <div id="results-section" class="hidden space-y-3">
                    <div class="bg-emerald-50 rounded-xl p-4 border border-emerald-200">
                        <div class="flex items-center gap-2 mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-sm font-semibold text-emerald-700">Analysis Completed!</span>
                        </div>
                        <ul class="text-emerald-700 text-sm space-y-2">
                            <li class="flex justify-between">
                                <span>Tickets Processed:</span>
                                <span class="font-semibold" id="tickets-processed">0</span>
                            </li>
                            <li class="flex justify-between">
                                <span>FAQs Generated:</span>
                                <span class="font-semibold" id="faqs-generated">0</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Footer - Actions -->
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 shrink-0">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                    <div></div>
                    <div class="flex items-center gap-2">
                        <button type="button"
                            class="flex-1 sm:flex-none inline-flex items-center justify-center rounded-lg bg-white px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 transition-colors"
                            onclick="closeAnalyzeModal()">Cancel</button>
                        <button type="button"
                            class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 rounded-lg bg-green-600 px-5 py-2 text-sm font-medium text-white hover:bg-green-700 transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                            id="analyze-btn"
                            onclick="startAnalysis()">
                            <!-- icon shown when idle -->
                            <svg id="analyzeIcon" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/>
                            </svg>
                            <!-- spinner shown while requesting -->
                            <svg id="analyzeSpinner" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 animate-spin hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" />
                            </svg>
                            <span id="analyzeText">Start Analysis</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('admin-scripts')
@parent
<script>
// Initialize unprocessed count on page load
document.addEventListener('DOMContentLoaded', function() {
    const unprocessedCount = {{ $unprocessedTickets ?? 0 }};
    document.getElementById('unprocessed-count').textContent = unprocessedCount;
});

function approveFAQ(semanticKey) {
    if (!confirm('Are you sure you want to approve this FAQ?')) {
        return;
    }

    fetch('{{ route('admin.faqs.approve') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            semantic_key: semanticKey
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('success-notification').classList.remove('hidden');
            setTimeout(() => {
                document.getElementById('success-notification').classList.add('hidden');
                location.reload();
            }, 2000);
        } else {
            document.getElementById('error-notification').classList.remove('hidden');
            setTimeout(() => {
                document.getElementById('error-notification').classList.add('hidden');
            }, 3000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('error-notification').classList.remove('hidden');
        setTimeout(() => {
            document.getElementById('error-notification').classList.add('hidden');
        }, 3000);
    });
}

function openAnalyzeModal() {
    document.getElementById('analyze-modal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
    
    // Reset modal state
    document.getElementById('progress-section').classList.add('hidden');
    document.getElementById('results-section').classList.add('hidden');
    document.getElementById('analyze-btn').disabled = false;
    document.getElementById('analyzeIcon').classList.remove('hidden');
    document.getElementById('analyzeSpinner').classList.add('hidden');
    document.getElementById('analyzeText').textContent = 'Start Analysis';
}

function closeAnalyzeModal() {
    document.getElementById('analyze-modal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

function startAnalysis() {
    const btn = document.getElementById('analyze-btn');
    const icon = document.getElementById('analyzeIcon');
    const spinner = document.getElementById('analyzeSpinner');
    const text = document.getElementById('analyzeText');
    
    btn.disabled = true;
    icon.classList.add('hidden');
    spinner.classList.remove('hidden');
    text.textContent = 'Analyzing...';
    
    document.getElementById('progress-section').classList.remove('hidden');
    document.getElementById('results-section').classList.add('hidden');
    
    // Call the backend API to process analysis
    fetch('{{ route("admin.faqs.process-analysis") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            completeAnalysis(data);
        } else {
            showError(data.message || 'Analysis failed');
            btn.disabled = false;
            spinner.classList.add('hidden');
            icon.classList.remove('hidden');
            text.textContent = 'Start Analysis';
            document.getElementById('progress-section').classList.add('hidden');
        }
    })
    .catch(error => {
        showError('Error: ' + error.message);
        btn.disabled = false;
        spinner.classList.add('hidden');
        icon.classList.remove('hidden');
        text.textContent = 'Start Analysis';
        document.getElementById('progress-section').classList.add('hidden');
    });
    
    // Simulate progress updates
    let progress = 0;
    const interval = setInterval(() => {
        progress += Math.random() * 25;
        if (progress > 90) progress = 90;
        
        document.getElementById('progress-bar').style.width = progress + '%';
        document.getElementById('progress-text').textContent = 'Processing tickets... ' + Math.round(progress) + '%';
        
        if (progress >= 90) {
            clearInterval(interval);
        }
    }, 800);
}

function completeAnalysis(data) {
    document.getElementById('progress-bar').style.width = '100%';
    document.getElementById('progress-text').textContent = 'Analysis complete!';
    
    setTimeout(() => {
        document.getElementById('progress-section').classList.add('hidden');
        document.getElementById('results-section').classList.remove('hidden');
        
        document.getElementById('tickets-processed').textContent = data.tickets_processed || 0;
        document.getElementById('faqs-generated').textContent = data.faqs_generated || 0;
        
        showSuccess('Analysis completed successfully!');
        
        // Reload page after 3 seconds to show new FAQs
        setTimeout(() => {
            location.reload();
        }, 3000);
    }, 1000);
}

function showSuccess(message) {
    const notification = document.getElementById('success-notification');
    notification.textContent = message;
    notification.classList.remove('hidden');
    setTimeout(() => {
        notification.classList.add('hidden');
    }, 4000);
}

function showError(message) {
    const notification = document.getElementById('error-notification');
    notification.textContent = message;
    notification.classList.remove('hidden');
    setTimeout(() => {
        notification.classList.add('hidden');
    }, 4000);
}
</script>
@endsection