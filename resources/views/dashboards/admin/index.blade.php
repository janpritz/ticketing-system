@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="md:flex md:items-center md:justify-between">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                Admin Dashboard
            </h2>
        </div>
    </div>

    <div class="mt-8">
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    Welcome, Administrator!
                </h3>
                <div class="mt-2 max-w-xl text-sm text-gray-500">
                    <p>
                        This is the administrator dashboard. Here you can manage the system.
                    </p>
                </div>
                <div class="mt-4">
                    <ul class="list-disc pl-5 space-y-1 text-gray-600">
                        <li>Manage staff members</li>
                        <li>View all tickets</li>
                        <li>Generate reports</li>
                        <li>System settings</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection