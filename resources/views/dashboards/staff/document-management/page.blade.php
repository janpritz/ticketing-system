@extends('layouts.staff')

@section('title', 'Document Management')

@section('staff-content')
    <div class="sm:px-2">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-semibold text-slate-900">Document Management</h1>
            </div>
            {{-- Helpers for deleted pages and desktop actions --}}
            @include('dashboards.staff.document-management.utils.helpers')
        </div>
        <div class="mt-4 relative">
            <p class="text-sm text-gray-600 mb-4">Documents are used for chatbot training.</p>
        </div>
        {{-- Documents Table --}}
        @include('dashboards.staff.document-management.components.documents-table')
    </div>

    {{-- Mobile Drawer --}}
    @include('dashboards.staff.document-management.components.mobile-drawer')

    {{-- Modals --}}
    @include('dashboards.staff.document-management.modals.file-upload')

    @include('dashboards.staff.document-management.modals.view')

    @include('dashboards.staff.document-management.modals.edit')
@endsection

@push('scripts')
    @include('dashboards.staff.document-management.scripts')
@endpush
