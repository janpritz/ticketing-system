@extends('layouts.admin')

@section('title', 'Role Management')

@section('admin-content')
    <div class="sm:px-2">
        <div class="flex items-center justify-between gap-4">
            {{-- Header and add new role button --}}
            @include('dashboards.admin.roles.components.header')
        </div>
        <div class="mt-5 bg-white rounded-xl border border-gray-200 overflow-hidden">
            {{-- Role Table --}}
            @include('dashboards.admin.roles.components.role-table')
        </div>
    </div>

    <!-- Create Role Modal -->
    @include('dashboards.admin.roles.components.create-role-modal')

    <!-- Edit Role Modal -->
    @include('dashboards.admin.roles.components.edit-role-modal')
@endsection

@push('scripts')
    @include('dashboards.admin.roles.scripts')
@endpush
