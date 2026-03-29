@extends('layouts.staff')

@section('title', 'Deleted Announcements')

@section('staff-content')
    <div class="sm:px-2">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-semibold text-slate-900">Deleted Announcements</h1>
                <p class="text-sm text-gray-600 mt-1">Manage deleted announcements.</p>
            </div>
            <!-- Back Button -->
            <a href="{{ route('staff.announcements.index') }}"
                class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-sm font-medium px-3 py-2">
                ← Back to Announcements
            </a>
        </div>

        <!-- Deleted Announcements List -->
        <div id="deletedAnnouncementsList" class="mt-6 space-y-4">
            <!-- Loaded via AJAX -->
        </div>
    </div>

    <!-- View Deleted Announcement Modal -->
    @include('dashboards.staff.announcements.components.view-announcement-modal')

@endsection
@push('scripts')
    @include('dashboards.staff.announcements.scripts.deleted-page-scripts')
@endpush
