@extends('layouts.admin')

@section('title', 'Rasa Server Manager')

@section('admin-content')
    <div class="sm:px-2">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-semibold text-slate-900">Rasa Server Manager</h1>
                <p class="text-sm text-gray-600 mt-1">Monitor and manage Rasa chatbot server system status, training, and
                    backups.</p>
            </div>
        </div>

        <!-- System Status Card -->
        @include('dashboards.admin.rasa-server.components.system-status-card')

        <!-- Training History -->
        @include('dashboards.admin.rasa-server.components.training-history')

        <!-- Models List -->
        {{-- @include('dashboards.admin.rasa-server.components.model-lists') --}}

        <!-- Status Data (hidden) -->
        <div id="statusData" class="hidden" data-csrf="{{ csrf_token() }}"></div>
    </div>
@endsection
@push('scripts')
    @include('dashboards.admin.rasa-server.scripts')
@endpush
