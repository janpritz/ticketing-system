@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('admin-content')
    @include('dashboards.admin.index.components.document-training-alert')

    {{-- System Status & Metrics --}}
    @include('dashboards.admin.rasa-server.components.system-status-card')
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @include('dashboards.admin.index.components.total-open-tickets')
        @include('dashboards.admin.index.components.active-staff')
        @include('dashboards.admin.index.components.last-training')
    </div>

    {{-- Analytics Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        @include('dashboards.admin.index.components.weekly-tickets')
        @include('dashboards.admin.index.components.tickets-by-category')
    </div>

    {{-- Unassigned Tickets Table --}}
    <div class="grid grid-cols-1 gap-4 mb-10">
        @php
            // Move logic like this to a Helper or View Composer if used in multiple pages
            $badge = fn($status) => match ($status) {
                'Open'      => 'text-blue-700 bg-blue-50 ring-blue-600/20',
                'Forwarded' => 'text-amber-700 bg-amber-50 ring-amber-600/20',
                'Closed'    => 'text-emerald-700 bg-emerald-50 ring-emerald-600/20',
                default     => 'text-slate-700 bg-slate-50 ring-slate-600/20',
            };
        @endphp
        @include('dashboards.admin.index.components.unassigned-tickets')
    </div>

    {{-- Modals & Drawers --}}
    @include('dashboards.admin.index.components.ticket-details-modal')
    @include('dashboards.admin.index.components.image-lightbox')
    @include('dashboards.admin.index.components.righ-side-drawer-for-active-staff')
    @include('dashboards.admin.index.components.secondary-right-side-contacts-nav')

    {{-- Data Serialization --}}
    @include('dashboards.admin.index.components.serialized-analytics-data-for-charts')
@endsection

{{-- 
    CRITICAL: Change this to @push('scripts') 
    if your layouts.app uses @stack('scripts')
--}}
@push('scripts')
    @include('dashboards.admin.index.components.rasa-status-scripts')
    @include('dashboards.admin.index.scripts')
@endpush