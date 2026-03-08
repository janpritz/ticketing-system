@extends('layouts.app')

@section('title', 'Verify OTP - Tickets')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-indigo-50 to-blue-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Verify Your Identity</h1>
                <p class="text-gray-600">
                    For data privacy and security, please verify your identity with an OTP sent to your email.
                </p>
            </div>

            <!-- OTP Request Form -->
            <div id="otpRequestForm" class="space-y-6">
                <div>
                    <label for="identifier" class="block text-sm font-medium text-gray-700 mb-2">
                        Email
                    </label>
                    <input
                        type="text"
                        id="identifier"
                        name="identifier"
                        placeholder="Please enter your email"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    >
                    <!-- Hidden field to store email for OTP verification -->
                    <input type="hidden" id="verifiedEmailField" name="verified_email" value="">
                </div>

                <button
                    type="button"
                    id="sendOtpBtn"
                    class="w-full bg-indigo-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-indigo-700 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 flex items-center justify-center gap-2"
                >
                    <span id="sendOtpText">Send OTP</span>
                    <svg id="sendOtpSpinner" class="hidden w-4 h-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>

                <p class="text-xs text-gray-500 text-center">
                    We'll send a 6-digit code to your registered email address.
                </p>
            </div>

            <!-- OTP Input Form (Hidden initially) -->
            <div id="otpInputForm" class="space-y-6 hidden">
                <div>
                    <label for="otpCode" class="block text-sm font-medium text-gray-700 mb-2">
                        Enter OTP Code
                    </label>
                    <input 
                        type="text" 
                        id="otpCode" 
                        name="otp_code"
                        placeholder="000000"
                        maxlength="6"
                        inputmode="numeric"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg text-center text-2xl tracking-widest font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    >
                    <p class="text-xs text-gray-500 mt-2">
                        OTP expires in <span id="expiryTimer">15:00</span>
                    </p>
                </div>

                <button
                    type="button"
                    id="verifyOtpBtn"
                    class="w-full bg-indigo-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-indigo-700 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 flex items-center justify-center gap-2"
                >
                    <span id="verifyOtpText">Verify OTP</span>
                    <svg id="verifyOtpSpinner" class="hidden w-4 h-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>

                <div class="flex items-center justify-between">
                    <button 
                        type="button" 
                        id="resendOtpBtn"
                        disabled
                        class="text-sm text-indigo-600 hover:text-indigo-700 disabled:text-gray-400 disabled:cursor-not-allowed transition-colors"
                    >
                        Resend OTP (<span id="resendTimer">60</span>s)
                    </button>
                    <button 
                        type="button" 
                        id="backBtn"
                        class="text-sm text-gray-600 hover:text-gray-700 transition-colors"
                    >
                        Back
                    </button>
                </div>
            </div>
        </div>

        <!-- Info Box -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        <strong>Privacy Notice:</strong> Your ticket information is protected. OTP verification ensures only authorized users can access ticket details.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@include('tickets.verify-otp.scripts')
@endsection
