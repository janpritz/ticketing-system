@extends('layouts.staff')

@section('title', 'Announcements')

@section('staff-content')
    <div class="sm:px-2">
        {{-- Header and buttons --}}
        @include('dashboards.staff.announcements.components.header')

        <!-- Announcements List -->
        @include('dashboards.staff.announcements.components.announcement-list')
    </div>

    <!-- Add Announcement Modal -->
    @include('dashboards.staff.announcements.components.add-announcement-modal')

    <!-- View Announcement Modal -->
    @include('dashboards.staff.announcements.components.view-announcement-modal')

@endsection
@push('scripts')
    @include('dashboards.staff.announcements.scripts')
@endpush
