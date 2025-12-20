@extends('layouts.staff')

@section('title', 'Knowledgebase - FAQs')

@section('staff-content')
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="p-6 border-b border-gray-200">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">FAQs</h1>
                <p class="text-gray-600 mt-1">Manage frequently asked questions</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-2">
                <input type="text" id="searchFaqInput" placeholder="Search FAQs..."
                    class="rounded-md border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 px-3 py-2">
                <button type="button" onclick="processClosedTickets()" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    Process Closed Tickets
                </button>
                <a href="{{ route('staff.knowledgebase.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:ring-4 focus:ring-green-300">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Create FAQ
                </a>
            </div>
        </div>
    </div>

    <!-- File Upload Section -->
    <div class="p-6 border-b border-gray-200 bg-gray-50">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Upload Document</h2>
        <form id="uploadForm" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label for="file" class="block text-sm font-medium text-gray-700 mb-2">Select File</label>
                <input type="file" id="file" name="file" accept=".txt,.md,.pdf,.doc,.docx" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                <p class="mt-1 text-sm text-gray-500">Supported formats: TXT, MD, PDF, DOC, DOCX (max 10MB)</p>
            </div>
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>
                Upload File
            </button>
        </form>
    </div>

    <!-- FAQs Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Intent</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200" id="faqsTableBody">
                @forelse($faqs as $faq)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $faq->intent }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">{{ $faq->description }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            <a href="{{ route('staff.knowledgebase.show', $faq) }}" class="text-blue-600 hover:text-blue-900">View</a>
                            <a href="{{ route('staff.knowledgebase.edit', $faq) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                            <form method="POST" action="{{ route('staff.knowledgebase.destroy', $faq) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this FAQ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">No FAQs found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('staff-scripts')
<script>
function processClosedTickets() {
    if (confirm('Are you sure you want to process closed tickets for FAQ generation?')) {
        fetch('{{ route("staff.knowledgebase.process-closed-tickets") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            alert('Closed tickets processed successfully!');
            location.reload();
        })
        .catch(error => {
            alert('Error processing closed tickets.');
            console.error('Error:', error);
        });
    }
}

document.getElementById('uploadForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('{{ route("staff.knowledgebase.upload") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('File uploaded successfully!');
        } else {
            alert('Error uploading file: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error uploading file.');
        console.error('Error:', error);
    });
});

// Search functionality
const searchFaqInput = document.getElementById('searchFaqInput');
const faqsTableBody = document.getElementById('faqsTableBody');

function renderFaqs(faqs) {
    if (!faqs || faqs.length === 0) {
        faqsTableBody.innerHTML = '<tr><td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">No FAQs found.</td></tr>';
        return;
    }
    const rows = faqs.map(faq => `
        <tr class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${faq.intent}</td>
            <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">${faq.description || ''}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                    <a href="/staff/knowledgebase/${faq.id}" class="text-blue-600 hover:text-blue-900">View</a>
                    <a href="/staff/knowledgebase/${faq.id}/edit" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                    <form method="POST" action="/staff/knowledgebase/${faq.id}" class="inline" onsubmit="return confirm('Are you sure you want to delete this FAQ?')">
                        <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                    </form>
                </div>
            </td>
        </tr>
    `).join('');
    faqsTableBody.innerHTML = rows;
}

function fetchFaqs(search = '') {
    const url = '{{ route("staff.knowledgebase.index") }}' + (search ? '?search=' + encodeURIComponent(search) : '');
    fetch(url, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    })
    .then(response => response.json())
    .then(data => {
        renderFaqs(data.faqs);
    })
    .catch(error => {
        console.error('Error fetching FAQs:', error);
    });
}

let searchTimeout;
if (searchFaqInput) {
    searchFaqInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            const searchTerm = searchFaqInput.value.trim();
            fetchFaqs(searchTerm);
        }, 300);
    });
}
</script>
@endsection