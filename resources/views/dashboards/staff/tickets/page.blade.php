@extends('layouts.staff')

@section('title', 'Staff Tickets')

@section('staff-content')
    <div class="sm-pt:2">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Tickets Management</h1>
            <p class="mt-1 text-sm text-gray-600">Manage and track all assigned tickets</p>
        </div>

        <!-- Filter Tabs -->
        @include('dashboards.staff.tickets.components.filters')

        <!-- Search and Controls -->
        @include('dashboards.staff.tickets.components.search-controls')

        <!-- Tickets Table -->
        @include('dashboards.staff.tickets.components.tickets-table')

        <!-- Image Lightbox -->
        @include('dashboards.staff.tickets.components.image-lightbox')

        <!-- Ticket Details Modal - Modern Design from Admin Dashboard -->
        @include('dashboards.staff.tickets.components.ticket-modal')
    </div>
@endsection
@push('scripts')
    @include('dashboards.staff.tickets.scripts')
@endpush
