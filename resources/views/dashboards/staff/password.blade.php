@extends('layouts.app')

@section('title', 'Change Password')

@section('content')
<div class="mx-auto max-w-xl px-4 py-8">
    @if (session('status'))
        <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <div class="font-semibold mb-1">Please fix the following:</div>
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mb-4">
        <a href="{{ route('staff.dashboard') }}" class="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-600" viewBox="0 0 24 24" fill="currentColor">
                <path d="M10.828 11H20a1 1 0 1 1 0 2h-9.172l3.536 3.536a1 1 0 1 1-1.414 1.414l-5.243-5.243a1 1 0 0 1 0-1.414l5.243-5.243a1 1 0 1 1 1.414 1.414L10.828 11Z"/>
            </svg>
            <span>Back to Dashboard</span>
        </a>
    </div>
    <div class="bg-white rounded-xl shadow-sm ring-1 ring-slate-900/5 p-5">
        <h1 class="text-base font-semibold text-gray-800 mb-4">Change Password</h1>

        <form method="POST" action="{{ route('staff.profile.password.update') }}" class="space-y-4">
            @csrf
            <div>
                <label for="current_password" class="block text-xs text-gray-600 mb-1">Current Password</label>
                <input type="password" id="current_password" name="current_password" required
                       class="w-full rounded-md border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                       autocomplete="current-password">
            </div>
            <div>
                <label for="password" class="block text-xs text-gray-600 mb-1">New Password</label>
                <input type="password" id="password" name="password" required minlength="8"
                       class="w-full rounded-md border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                       autocomplete="new-password">
                <p class="mt-1 text-xs text-gray-500">Minimum 8 characters.</p>
            </div>
            <div>
                <label for="password_confirmation" class="block text-xs text-gray-600 mb-1">Confirm New Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8"
                       class="w-full rounded-md border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                       autocomplete="new-password">
            </div>

            <div class="flex items-center justify-between pt-2">
                <a href="{{ route('staff.profile') }}" class="text-sm text-gray-600 hover:text-gray-800">Back to Profile</a>
                <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    Update Password
                </button>
            </div>
        </form>
    </div>
</div>
@endsection