@extends('layouts.admin')

@section('title', 'Document Change Logs')

@section('admin-content')
    <div class="sm:px-2">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-semibold text-slate-900">Document Change Logs</h1>
                <p class="text-sm text-gray-600 mt-1">Track changes to FAQs, announcements, and documents</p>
            </div>
        </div>

        <!-- Search and Filters -->
        @include('dashboards.admin.document-logs.utils.search-filters')

        <!-- Logs Table -->
        <div class="mt-4 bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                @include('dashboards.admin.document-logs.components.logs-table')
            </div>

            <!-- Pagination -->
            @include('dashboards.admin.document-logs.utils.pagination')
        </div>
    </div>
@endsection
