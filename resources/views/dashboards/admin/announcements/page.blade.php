@extends('layouts.admin')

@section('title', 'Announcements')

@section('admin-content')
    <div class="sm:px-2">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-semibold text-slate-900">Announcements</h1>
                <p class="text-sm text-gray-600 mt-1">Manage dynamic chatbot responses.</p>
            </div>
            <!-- Buttons -->
            <div class="flex gap-2">
                <!-- Add Announcement Button -->
                @include('dashboards.admin.announcements.components.add-announcement-btn')
            </div>
        </div>

        <!-- Search Bar -->
        @include('dashboards.admin.announcements.components.search-bar')

        <!-- Document Training Alert -->
        {{-- @include('dashboards.admin.announcements.utils.document-training-alert') --}}

        <!-- Announcements List -->
        @include('dashboards.admin.announcements.components.announcement-table')
     </div>

     <!-- Add Announcement Modal -->
     @include('dashboards.admin.announcements.components.add-announcement-modal')

     <!-- View Announcement Modal -->
    @include('dashboards.admin.announcements.components.view-announcement-modal')
@endsection

@push('scripts')
    @include('dashboards.admin.announcements.scripts')
@endpush
