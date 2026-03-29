@extends('layouts.staff')

@section('title', 'Staff Dashboard')

@section('staff-content')
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 sm:px-2 lg:px-2">
        <div class="mx-auto max-w-5xl">
            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                <!-- Left Sidebar - Profile Photo & Quick Stats -->
                @include('dashboards.staff.profile.components.left-sidebar')

                <!-- Right Content - Profile Form & Activity -->
                <div class="lg:col-span-8 space-y-6">

                    <!-- Profile Information Form -->
                    @include('dashboards.staff.profile.components.profile-form')

                    <!-- Recent Activity -->
                    @include('dashboards.staff.profile.components.recent-activity')

                    <!-- Push Notifications -->
                    @include('dashboards.staff.profile.components.push-notifications')

                    <!-- Email Notifications -->
                    @include('dashboards.staff.profile.components.email-notifications')
                    {{-- Change Password Card --}}
                    @include('dashboards.staff.profile.components.change-password')
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal - modern admin-style modal (matches admin layout) -->
    @include('dashboards.staff.profile.components.edit-profile-modal')

    <!-- Password Change Modal (admin-style) -->
    @include('dashboards.staff.profile.components.password-modal')
@endsection

@push('scripts')
    @include('dashboards.staff.profile.scripts')
@endpush
