@extends('layouts.staff')

@section('title', 'Staff Reports')

@section('staff-content')
    <div class="sm:px-2">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Reports</h1>
                <p class="text-sm text-slate-500 mt-1">Performance metrics and analysis for your tickets</p>
            </div>
            @include('dashboards.staff.reports.components.filters')
        </div>
    </div>

    <!-- Performance Dashboard -->
    @include('dashboards.staff.reports.components.performance-metrics')

    <!-- Charts -->
    @include('dashboards.staff.reports.components.charts')

    <!-- Weekly Tickets Chart -->
    @include('dashboards.staff.reports.components.weekly-tickets')

    <!-- Overdue Tickets -->
    @include('dashboards.staff.reports.components.overdue-tickets')

    <!-- Total Tickets Modal -->
    @include('dashboards.staff.reports.modals.total-tickets')

    <!-- Resolved Tickets Modal -->
    @include('dashboards.staff.reports.modals.resolved-tickets')

    <!-- Average Resolution Time Modal -->
    @include('dashboards.staff.reports.modals.avg-resolution-time')

    <!-- Resolution Rate Modal -->
    @include('dashboards.staff.reports.modals.resolution-rate')

@endsection

@push('scripts')
    @include('dashboards.staff.reports.scripts')
@endpush
