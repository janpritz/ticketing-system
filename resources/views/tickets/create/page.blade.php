@extends('layouts.app')

@section('title', 'Create Ticket')

@section('content')
    <!-- Public Navigation Bar -->
    <x-public-nav :logo-margin="'mr-4'" :logo-text="'SANGKAY'" />

    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="md:flex md:items-center md:justify-between">
            <div class="flex-1 text-center min-w-0 pt-5">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                    Create a Ticket
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Please fill out the form below to create your support ticket.
                </p>
            </div>
        </div>

        <div class="mt-8">
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">

                    <!-- Success Message -->
                    @if (session('success'))
                        <div class="rounded-md bg-green-50 p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-green-800">
                                        {{ session('success') }}
                                    </h3>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Error Message -->
                    @if ($errors->any())
                        <div class="rounded-md bg-red-50 p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">
                                        There were {{ $errors->count() }} error(s) with your submission
                                    </h3>
                                    <div class="mt-2 text-sm text-red-700">
                                        <ul class="list-disc pl-5 space-y-1">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Ticket Creation Form -->
                    <form action="{{ route('tickets.store') }}" method="POST" class="space-y-6" novalidate
                        enctype="multipart/form-data">
                        @csrf

                        <!-- Hidden fields -->
                        <input type="hidden" name="recepient_id" value="{{ $recepient_id }}">

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                            <div class="mt-1">
                                <input readonly type="email" name="email" id="email"
                                    class="py-2 px-3 block w-full border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm bg-gray-100"
                                    required value="{{ old('email', $email) }}">
                            </div>
                        </div>

                        <div>
                            <div
                                style="display: flex; align-items: center; gap: 8px; position: relative; margin-bottom: 20px; font-family: sans-serif;">

                                <label for="category" style="font-size: 14px; font-weight: 500; color: #374151;">
                                    Category
                                </label>

                                <span
                                    onmouseenter="document.getElementById('tooltip').style.opacity = '1'; document.getElementById('tooltip').style.visibility = 'visible';"
                                    onmouseleave="document.getElementById('tooltip').style.opacity = '0'; document.getElementById('tooltip').style.visibility = 'hidden';"
                                    style="cursor: pointer; color: #9ca3af; display: flex; align-items: center;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </span>

                                <span id="tooltip"
                                    style="
                                        position: absolute; 
                                        top: 25px; 
                                        left: 0; 
                                        width: 250px; 
                                        background-color: #1f2937; 
                                        color: white; 
                                        font-size: 12px; 
                                        padding: 8px; 
                                        border-radius: 4px; 
                                        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); 
                                        z-index: 50; 
                                        opacity: 0; 
                                        visibility: hidden; 
                                        transition: opacity 0.2s ease-in-out;
                                        pointer-events: none;
                                    ">
                                    Select the specific office or department that your ticket corresponds to. This helps
                                    your ticket to be assigned to proper staff.
                                </span>

                            </div>
                            <div class="mt-1">
                                <input list="category-list" name="role_name" id="category"
                                    class="py-2 px-3 block w-full border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    required value="{{ old('role_name') }}">
                                <input type="hidden" name="role_id" id="role_id_input" value="{{ old('role_id') }}">
                                <datalist id="category-list">
                                    @if (isset($roles) && count($roles))
                                        @foreach ($roles as $id => $name)
                                            <option value="{{ $name }}">
                                        @endforeach
                                    @else
                                        <!-- Fallback to the hard-coded list if controller did not provide categories -->
                                        <option value="Course Registration">

                                    @endif
                                </datalist>
                            </div>
                        </div>

                        <div>
                            <label for="question" class="block text-sm font-medium text-gray-700">Question</label>
                            <div class="mt-1">
                                <textarea name="question" id="question" rows="4"
                                    class="py-2 px-3 block w-full border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    required placeholder="Please describe your question or issue in detail...">{{ old('question') }}</textarea>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Attachments (Screenshots - Max 5MB per
                                image)</label>
                            <div class="mt-1">
                                <button type="button" id="add-photo-btn-submit"
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Add Photo
                                </button>
                                <input type="file" name="attachments[]" id="attachments" multiple accept="image/*"
                                    class="hidden">
                                <div id="selected-thumbnails-submit"
                                    class="mt-2 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2"></div>
                                <p class="mt-1 text-sm text-gray-500">You can upload 5 files only. (Only jpeg,jpg,png files
                                    are allowed.)</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Verification</label>
                            <div class="mt-1">
                                {!! app('captcha')->display() !!}
                                @error('g-recaptcha-response')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="privacy-consent" name="privacy_consent" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="privacy-consent" class="font-medium text-gray-700">I consent to the collection of my email address, questions, and attachments for processing this support ticket.</label>
                            </div>
                        </div>

                        <div class="flex items-center justify-end">
                            <button id="submitTicketBtn" type="submit" disabled
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                Create Ticket
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@include('tickets.create.scripts')
