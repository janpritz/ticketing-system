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
            <p class="text-sm text-gray-600 mb-4">Documents stored in the Rasa server for Knowledgebase training.</p>
            <!-- Mobile hamburger menu aligned with text -->
            @include('dashboards.admin.knowledgebase.components.mobile-hamburger-menu')
        </div>

        <!-- Training Required Alert -->
        @include('dashboards.admin.knowledgebase.utils.training-required-alert')

        {{-- Loading Documents on first load --}}
        @include('dashboards.admin.knowledgebase.utils.loading-docs')
    </div>

    <!-- Mobile Bottom Drawer -->
    @include('dashboards.admin.knowledgebase.components.mobile-bottom-drawer')

    <!-- Mobile Drawer Overlay -->
    <div id="mobileDrawerOverlay" class="fixed inset-0 bg-black/50 z-30 hidden sm:hidden"></div>

    <!-- Create Knowledgebase Modal -->
    @include('dashboards.admin.knowledgebase.components.create-knowledgebase-modal')

    <!-- Upload File Modal -->
    @include('dashboards.admin.knowledgebase.components.upload-file-modal')

    <!-- View/Edit Knowledgebase Modal -->
    @include('dashboards.admin.knowledgebase.components.view-edit-knowledgebase-modal')

    <!-- Edit Document Modal -->
    @include('dashboards.admin.knowledgebase.components.edit-document-modal')

    <!-- Document Management History Modal -->
    @include('dashboards.admin.knowledgebase.components.document-management-history-modal')

    <!-- Hidden state with URLs -->
    @include('dashboards.admin.knowledgebase.utils.hidden-state-urls')

@endsection

@push('scripts')
    @include('dashboards.admin.knowledgebase.scripts')
@endpush
