@extends('layouts.admin')

@section('title', 'Reports & Analytics')

@section('admin-content')
    <div class="sm:px-2">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Reports & Analytics</h1>
                <p class="text-sm text-slate-500 mt-1">Monitor ticket trends and system performance</p>
            </div>
            {{-- Date filters --}}
            @include('dashboards.admin.reports.utils.time-range-filter')
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Current Open Tickets -->
            @include('dashboards.admin.reports.components.current-open-tickets')

            <!-- Placeholder for future KPIs -->
            @include('dashboards.admin.reports.components.place-holder-for-future-fpis')

            {{-- Total Tickets --}}
            @include('dashboards.admin.reports.components.total-tickets')

            {{-- Overdue tickets --}}
            @include('dashboards.admin.reports.components.overdue-tickets')
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Closed Tickets Trend Chart -->
            @include('dashboards.admin.reports.components.closed-tickets')

            <!-- Tickets Solved/Closed -->
            @include('dashboards.admin.reports.components.tickets-solved')
        </div>

        <!-- Staff Performance Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Tickets Assigned (Current Workload) -->
            @include('dashboards.admin.reports.components.staff-workload')

            <!-- Workload Distribution -->
            @include('dashboards.admin.reports.components.workload-distribution')
        </div>

        <!-- Trend Identification and Root Cause Analysis Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8 mb-8">
            <!-- Top Ticket Drivers -->
            @include('dashboards.admin.reports.components.top-ticket')

            <!-- Forwarded Tickets (by Forwarder) -->
            @include('dashboards.admin.reports.components.forwarded-tickets')
        </div>
    </div>
    <!-- Forwards modal (admin-style, hidden by default) -->
    @include('dashboards.admin.reports.components.forwarded-modal')
@endsection

@push('scripts')
    @include('dashboards.admin.reports.scripts')
@endpush
