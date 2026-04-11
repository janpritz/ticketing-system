@extends('layouts.staff')

@section('title', 'Staff Dashboard')

@section('staff-content')
    <div class="min-h-full">
        <div class="sm:px-2">
            <!-- Main content -->
            <section class="space-y-6 flex flex-col">
                <!-- KPI cards -->
                {{-- <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 order-2 md:order-1 mt-4 md:mt-0">
                    <!-- Open -->
                    @include('dashboards.staff.index.components.open-kpi')

                    <!-- Forwarded -->
                    @include('dashboards.staff.index.components.forwarded-kpi')

                    <!-- Resolved -->
                    @include('dashboards.staff.index.components.resolved-kpi')

                    <!-- Total -->
                    @include('dashboards.staff.index.components.total-kpi')
                </div> --}}

                <!-- Tickets Table -->
                @include('dashboards.staff.index.components.tickets-table')

                <!-- Ticket Details Modal - Modern Design from Admin Dashboard -->
                @include('dashboards.staff.index.components.ticket-details-modal')

                <!-- Image Lightbox - Full screen viewer for attachment images -->
                @include('dashboards.staff.index.components.image-lightbox')
            </section>
        </div>
    </div>
@endsection

@include('dashboards.staff.index.utils.toast-container')

@push('scripts')
    @include('dashboards.staff.index.scripts')
@endpush
