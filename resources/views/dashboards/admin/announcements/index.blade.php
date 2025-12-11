@extends('layouts.admin')

@section('title', 'Announcements')

@section('admin-content')
    <div class="sm:px-2">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Announcements</h1>
            </div>
        </div>

        <div class="mt-4">
            <p class="text-sm text-gray-600 mb-4">Manage system announcements and notifications.</p>
        </div>

        <div class="mt-4 bg-white rounded-xl border border-gray-200 p-8">
            <div class="text-center text-gray-500">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No announcements yet</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating your first announcement.</p>
            </div>
        </div>
    </div>
@endsection