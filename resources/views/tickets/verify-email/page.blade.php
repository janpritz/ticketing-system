@extends('layouts.app')

@section('title', 'Email Verification')

@section('content')
<div class="max-w-md mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <div class="bg-white shadow-lg rounded-lg p-8">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100">
                <svg class="h-6 w-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
            <h2 class="mt-4 text-2xl font-bold text-gray-900">Email Verification Required</h2>
            <p class="mt-2 text-sm text-gray-600">Please enter your email address to create a support ticket.</p>
        </div>

        <!-- Email Input Form -->
        <div id="email-form" class="mt-8">
            <div class="mb-6">
                <label for="verification-email" class="block text-sm font-medium text-gray-700">Email Address</label>
                <input type="email" id="verification-email" name="email" required 
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-3 px-4 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                       placeholder="Enter your email address">
            </div>
            <button type="button" id="send-otp-btn" class="w-full inline-flex justify-center py-3 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Send Verification Code
            </button>
        </div>

        <!-- OTP Verification Form -->
        <div id="otp-form" class="hidden mt-8">
            <div class="mb-6">
                <label for="verification-otp" class="block text-sm font-medium text-gray-700">Verification Code</label>
                <input type="text" id="verification-otp" name="otp_code" maxlength="6" required 
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-3 px-4 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-center text-2xl tracking-widest font-mono" 
                       placeholder="123456">
                <p class="mt-2 text-sm text-gray-500 text-center" id="otp-timer">Code expires in 15:00</p>
            </div>
            <div class="flex space-x-3">
                <button type="button" id="verify-otp-btn" class="flex-1 inline-flex justify-center py-3 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Verify
                </button>
                <button type="button" id="resend-otp-btn" class="flex-1 inline-flex justify-center py-3 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Resend
                </button>
            </div>
        </div>

        <!-- <div class="mt-6 text-center">
            <a href="{{ route('tickets.status.form') }}" class="text-sm text-indigo-600 hover:text-indigo-500">
                Back to Check Status
            </a>
        </div> -->
    </div>
</div>
<div id="cookie-modal" class="fixed bottom-4 right-4 z-50 hidden">
    <div class="bg-white rounded-lg shadow-2xl p-6 max-w-sm w-full border border-gray-200">
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-semibold text-gray-900">Cookie Preferences</h3>
                <p class="mt-1 text-xs text-gray-600">We use cookies to improve your experience. By continuing, you accept our cookie policy.</p>
<div class="mt-4 flex gap-2">
                    <button type="button" id="accept-cookies" class="flex-1 inline-flex justify-center py-2 px-3 border border-transparent shadow-sm text-xs font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Accept
                    </button>
                    <button type="button" id="decline-cookies" class="flex-1 inline-flex justify-center py-2 px-3 border border-gray-300 shadow-sm text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Decline
                    </button>
                </div>
            </div>
            <button type="button" id="close-cookie-modal" class="text-gray-400 hover:text-gray-600">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
</div>

@include('tickets.verify-email.scripts')
@endsection
