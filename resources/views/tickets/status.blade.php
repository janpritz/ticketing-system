@extends('layouts.app')

@section('title', 'Check Ticket Status')

@section('content')
    <!-- Public Navigation Bar -->
    <x-public-nav :logo-margin="'mr-4'" :logo-text="'SANGKAY'" />
    
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="mt-8">
            <!-- Tickets Section -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <!-- Email Input Form -->
                    <div class="space-y-4">
                        <div>
                            <label for="verification_email" class="block text-sm font-medium text-gray-700">Email Address</label>
                            <div class="mt-1">
                                <input type="email" id="verification_email" name="verification_email"
                                    class="py-2 px-3 block w-full border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    placeholder="Enter your email address">
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="button" id="view-tickets-btn"
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                View My Tickets
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // View Tickets button click
            document.getElementById('view-tickets-btn').addEventListener('click', function() {
                const email = document.getElementById('verification_email').value;
                
                if (!email || !email.includes('@')) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Please enter a valid email address.'
                    });
                    return;
                }

                // Redirect to tickets page with email
                window.location.href = `/tickets/${encodeURIComponent(email)}`;
            });

            // Allow Enter key submission
            document.getElementById('verification_email').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    document.getElementById('view-tickets-btn').click();
                }
            });
        });
    </script>
@endsection