@extends('layouts.admin')

@section('title', 'Departments')

@section('admin-content')
<div class="sm:px-2">
  <div class="flex items-center justify-between gap-4">
    {{-- Header and add department button --}}
    @include('dashboards.admin.departments.components.header')
  </div>
  <div class="mt-5 bg-white rounded-xl border border-gray-200 overflow-hidden">
    {{-- Display departments table --}}
    @include('dashboards.admin.departments.components.departments-table')
  </div>
</div>

<!-- Create Department Modal -->
@include('dashboards.admin.departments.components.create-department-modal')

<!-- Edit Department Modal -->
@include('dashboards.admin.departments.components.edit-department-modal')
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    @include('dashboards.admin.departments.scripts.role-manager')
    @include('dashboards.admin.departments.scripts.create-handler')
    @include('dashboards.admin.departments.scripts.edit-handler')
    @include('dashboards.admin.departments.scripts.delete-handler')
</script>
@endpush
