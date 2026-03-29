@extends('layouts.app')

@section('title', 'Change Password')

@section('content')
<div class="mx-auto max-w-xl px-4 py-8">
    {{-- Helpers --}}
    @include('dashboards.staff.password.utils.helpers')

    @include('dashboards.staff.password.utils.back-button')

    <div class="bg-white rounded-xl shadow-sm ring-1 ring-slate-900/5 p-5">
        <h1 class="text-base font-semibold text-gray-800 mb-4">Change Password</h1>

        @include('dashboards.staff.password.utils.change-password-form')
    </div>
</div>
@endsection