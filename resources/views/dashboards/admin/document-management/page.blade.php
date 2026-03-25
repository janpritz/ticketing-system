@extends('layouts.admin')

@section('title', 'Document Management')
@section('admin-content')
    <div class="sm:px-2">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-semibold text-slate-900">Document Management</h1>
            </div>
            {{-- Checks if viewing deleted --}}
            @include('dashboards.admin.knowledgebase.utils.check-if-deleted')

            <!-- Desktop actions -->
            @include('dashboards.admin.knowledgebase.utils.desktop-actions')

        </div>

        <div class="mt-4 relative">
            <p class="text-sm text-gray-600 mb-4">Download submitted documents to import on rasa server.</p>
            <!-- Mobile hamburger menu aligned with text -->
            @include('dashboards.admin.knowledgebase.components.mobile-hamburger-menu')
        </div>

        <!-- Training Required Alert -->
        @include('dashboards.admin.knowledgebase.utils.training-required-alert')

        {{-- Display the documents table here --}}
        @include('dashboards.admin.document-management.components.document-table')
        
    </div>

    <!-- View Document Modal -->
    @include('dashboards.admin.document-management.components.view-document-modal')

    <!-- Edit Document Modal -->
    @include('dashboards.admin.document-management.components.edit-document-modal')
@endsection

@push('scripts')
    @include('dashboards.admin.document-management.script')
@endpush
