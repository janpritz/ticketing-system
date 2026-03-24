@extends('layouts.admin')

@section('title', 'Ticket Management')

@section('admin-content')
    <div class="sm:px-2">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Ticket Management</h1>
                <p class="text-sm text-slate-500 mt-1">Manage all tickets: respond, forward, edit, delete.</p>
            </div>
            <div class="flex items-center gap-3">
                <!-- Desktop search + per-page -->
                @include('dashboards.admin.tickets.components.desktop-search')

                <!-- Mobile search -->
                @include('dashboards.admin.tickets.components.mobile-search')
                {{-- Refresh Button --}}
                @include('dashboards.admin.tickets.components.refreshbutton')

                {{-- Open Filters button --}}
                <button id="openFiltersBtn"
                    class="ml-2 rounded-md border border-gray-200 bg-white px-3 py-2 text-sm">Filters</button>
            </div>
        </div>

        {{-- Tickets Table --}}
        @include('dashboards.admin.tickets.components.ticket-table')
    </div>

    <!-- Image Lightbox -->
    @include('dashboards.admin.tickets.components.image-lightbox')

    <div id="ticketsDrawerOverlay" class="hidden fixed inset-0 bg-black/30 z-40"></div>
    <!-- Bottom drawer: Filters & Sort -->
    @include('dashboards.admin.tickets.components.bottom-drawer')

    <!-- Ticket Details Modal - Modern Design from Admin Dashboard -->
    @include('dashboards.admin.tickets.components.ticket-details-modal')

@endsection
@push('scripts')
    @include('dashboards.admin.tickets.scripts')
@endpush
