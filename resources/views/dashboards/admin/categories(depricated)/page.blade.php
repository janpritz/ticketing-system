@extends('layouts.admin')

@section('title', 'Category Management')

@section('admin-content')
    <div class="sm:px-2">
        <div class="flex items-center justify-between gap-4">
            {{-- Header and add button --}}
            @include('dashboards.admin.categories.components.header')
        </div>

        {{-- Display category table here --}}
        @include('dashboards.admin.categories.components.category-table')
    </div>

    <!-- Add Category Modal -->
    @include('dashboards.admin.categories.components.add-category-modal')

    <!-- Edit Category Modal -->
    @include('dashboards.admin.categories.components.edit-category-modal')
@endsection

@push('scripts')
    @include('dashboards.admin.categories.scripts.create-modal')
    @include('dashboards.admin.categories.scripts.delete-handler')
    @include('dashboards.admin.categories.scripts.edit-modal')
@endpush
