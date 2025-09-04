@extends('layouts.app')

@section('title', 'Create Ticket')

@section('content')
<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="md:flex md:items-center md:justify-between">
        <div class="flex-1 text-center min-w-0 pt-5">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                Submit a Ticket
            </h2>
        </div>
    </div>

    <div class="mt-8">
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <!-- Success Message -->
                @if(session('success'))
                <div class="rounded-md bg-green-50 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">
                                {{ session('success') }}
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Error Message -->
                @if($errors->any())
                <div class="rounded-md bg-red-50 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
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
                <form action="{{ route('tickets.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div>
                        <label for="recepient_id" class="block text-sm font-medium text-gray-700">Recepient ID</label>
                        <div class="mt-1">
                            <input type="text" name="recepient_id" id="recepient_id" class="py-2 px-3 block w-full border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required value="{{ old('recepient_id', $recepient_id) }}" readonly>
                        </div>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <div class="mt-1">
                            <input type="email" name="email" id="email" class="py-2 px-3 block w-full border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required value="{{ old('email') }}">
                        </div>
                    </div>

                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                        <div class="mt-1">
                            <input list="category-list" name="category" id="category" class="py-2 px-3 block w-full border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required onchange="showDynamicFields()" value="{{ old('category') }}">
                            <datalist id="category-list">
                                <option value="Course Registration">
                                <option value="Add or Drop Classes">
                                <option value="Late Enrollment">
                                <option value="Shifting to a Different Program">
                                <option value="Transferring Between Schools">
                                <option value="Schedule Conflicts">
                                <option value="Tuition Fee Inquiries">
                                <option value="Payment Methods (Online, Bank, etc.)">
                                <option value="Scholarships & Financial Aid">
                                <option value="Refund Issues">
                                <option value="Billing and Invoice Problems">
                                <option value="Merit-Based Scholarships">
                                <option value="Need-Based Scholarships">
                                <option value="Scholarship Application Status">
                                <option value="Eligibility and Deadlines for Scholarships">
                                <option value="Grades and Transcript Requests">
                                <option value="Class Schedules">
                                <option value="Academic Probation or Warnings">
                                <option value="Course Prerequisites">
                                <option value="Graduation Requirements">
                                <option value="Thesis/Dissertation Submission">
                                <option value="Exam Schedules">
                                <option value="Exam Results">
                                <option value="Re-scheduling Exams">
                                <option value="Special Exam Accommodations">
                                <option value="Career Counseling">
                                <option value="Student Organizations & Activities">
                                <option value="Mental Health Support">
                                <option value="Peer Mentoring">
                                <option value="Internship Assistance">
                                <option value="Book Borrowing">
                                <option value="Access to Digital Resources">
                                <option value="Study Room Reservations">
                                <option value="Library Fees and Fines">
                                <option value="Research Assistance">
                                <option value="Wi-Fi Issues">
                                <option value="Software Installation">
                                <option value="Email Issues">
                                <option value="Computer Lab Problems">
                                <option value="Learning Management System (LMS) Issues">
                                <option value="Student Life Events">
                                <option value="Student Rights and Responsibilities">
                                <option value="Code of Conduct Violations">
                                <option value="Disciplinary Actions">
                                <option value="Visa Assistance">
                                <option value="Scholarships for International Students">
                                <option value="Cultural Integration Support">
                                <option value="Study Abroad Programs">
                                <option value="Graduation Requirements">
                                <option value="Commencement Exercises">
                                <option value="Diploma Requests">
                                <option value="Alumni Services">
                                <option value="Sports Club Registration">
                                <option value="Physical Education Classes">
                                <option value="Sports Event Tickets">
                                <option value="Sports Scholarships">
                            </datalist>
                        </div>
                    </div>

                    <div>
                        <label for="question" class="block text-sm font-medium text-gray-700">Question</label>
                        <div class="mt-1">
                            <textarea name="question" id="question" rows="4" class="py-2 px-3 block w-full border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>{{ old('question') }}</textarea>
                        </div>
                    </div>

                    <div class="flex items-center justify-end">
                        <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Create Ticket
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection