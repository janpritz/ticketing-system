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

        <div class="mt-6 text-center">
            <a href="{{ route('tickets.status.form') }}" class="text-sm text-indigo-600 hover:text-indigo-500">
                Back to Check Status
            </a>
        </div>
    </div>
</div>
@include('tickets.verify-email.scripts')
@endsection
