@extends('layouts.staff')

@section('title', 'Announcements')

@section('staff-content')
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="p-6 border-b border-gray-200">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Announcements</h1>
                <p class="text-gray-600 mt-1">Manage system announcements</p>
            </div>
            <button type="button" onclick="openAddModal()" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:ring-4 focus:ring-green-300">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add Announcement
            </button>
        </div>
    </div>

    <!-- Announcements List -->
    <div class="divide-y divide-gray-200">
        @forelse($announcements as $announcement)
        <div class="p-6 hover:bg-gray-50">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900">{{ $announcement['title'] ?? 'Announcement' }}</h3>
                    <p class="mt-2 text-gray-600 whitespace-pre-wrap">{{ $announcement['content'] }}</p>
                    <p class="mt-3 text-sm text-gray-500">ID: {{ $announcement['id'] }}</p>
                </div>
                <div class="ml-4 flex-shrink-0">
                    <button type="button" onclick="deleteAnnouncement({{ $announcement['id'] }})"
                            class="inline-flex items-center p-2 text-red-600 hover:text-red-900 hover:bg-red-50 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        @empty
        <div class="p-6 text-center text-gray-500">
            <p>No announcements found.</p>
        </div>
        @endforelse
    </div>
</div>

<!-- Add Announcement Modal -->
<div id="addAnnouncementModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Add New Announcement</h3>
                <button type="button" onclick="closeAddModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form id="addAnnouncementForm" class="space-y-4">
                @csrf
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Title *</label>
                    <input type="text" id="title" name="title" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label for="content" class="block text-sm font-medium text-gray-700 mb-2">Content *</label>
                    <textarea id="content" name="content" rows="4" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>

                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeAddModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700">
                        Add Announcement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('staff-scripts')
<script>
function openAddModal() {
    document.getElementById('addAnnouncementModal').classList.remove('hidden');
    document.getElementById('title').focus();
}

function closeAddModal() {
    document.getElementById('addAnnouncementModal').classList.add('hidden');
    document.getElementById('addAnnouncementForm').reset();
}

function deleteAnnouncement(id) {
    if (confirm('Are you sure you want to delete this announcement?')) {
        // Note: Delete functionality would need to be implemented in the controller
        alert('Delete functionality not yet implemented. Announcement ID: ' + id);
    }
}

document.getElementById('addAnnouncementForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('{{ route("staff.knowledgebase.announcements.store") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Announcement added successfully!');
            closeAddModal();
            location.reload();
        } else {
            alert('Error adding announcement: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error adding announcement.');
        console.error('Error:', error);
    });
});

// Close modal when clicking outside
document.getElementById('addAnnouncementModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeAddModal();
    }
});
</script>
@endsection