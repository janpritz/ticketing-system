@extends('layouts.admin')

@section('title', 'Staff Management')

@section('admin-content')
    <div class="sm:px-2">
        {{-- Header & Toolbar --}}
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">
                    {{ $isDeletedView ? 'Deleted Users' : 'Staff Management' }}
                </h1>
                <p class="text-sm text-slate-500">
                    {{ $isDeletedView ? 'View and restore deleted staff accounts' : 'Manage staff accounts' }}
                </p>
            </div>
            @include('dashboards.admin.users.components.desktop-actions')
            @include('dashboards.admin.users.components.mobile-toolbar')
        </div>

        {{-- Search Section --}}
        <div class="mt-5">
            @include('dashboards.admin.users.components.desktop-search')
            @include('dashboards.admin.users.components.mobile-search')
        </div>

        {{-- Table Card --}}
        <div class="mt-4 bg-white rounded-xl border border-gray-200 overflow-hidden">
            @include('dashboards.admin.users.components.table-content')
            @include('dashboards.admin.users.components.pagination')
        </div>
    </div>

    {{-- Modals --}}
    @include('dashboards.admin.users.components.create-staff-modal')
    @include('dashboards.admin.users.components.edit-staff-modal')

    {{-- State Data (Moved inside section to stay within the DOM flow) --}}
    <div id="admin-users-state" class="hidden" 
        data-has-errors="{{ $errors->any() ? '1' : '0' }}"
        data-old-edit-id="{{ old('editing_user_id') }}" 
        data-old-form-context="{{ old('form_context') }}">
    </div>
@endsection

{{-- Push scripts to the stack at the bottom of the body --}}
@push('scripts')
    @include('dashboards.admin.users.scripts')
@endpush