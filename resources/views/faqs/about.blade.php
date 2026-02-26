@extends('layouts.app')

@section('title', 'About Us - Sangkay')

@section('content')
    <div class="bg-white min-h-screen">
        <!-- Navigation Bar - Using Component -->
        <x-public-nav active="about" :logo-margin="'mr-4'" :logo-text="'SANGKAY'" />

        <!-- Main Content -->
        <div class="pt-8 pb-16 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <!-- Page Header -->
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold text-gray-900 mb-4">About Sangkay</h1>
                <p class="text-lg text-gray-600 max-w-3xl mx-auto">An intelligent student support platform designed to enhance communication and service delivery at Abuyog Community College.</p>
            </div>

            <!-- About Sangkay Section -->
            <section class="mb-16">
                <div class="bg-gray-50 rounded-xl p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                        <span class="mr-3">ℹ️</span>
                        About Sangkay
                    </h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Sangkay is an intelligent student support platform developed to enhance communication and service delivery at Abuyog Community College. The system integrates Artificial Intelligence, Natural Language Processing (NLP), and a structured ticketing mechanism to provide students with fast, reliable, and accessible assistance anytime.
                    </p>
                    <p class="text-gray-700 leading-relaxed">
                        The name <strong>Sangkay</strong> represents connection and guidance — reflecting the system's mission to bridge students with accurate information and responsive support services.
                    </p>
                </div>
            </section>

            <!-- Sangkay Chatbot Section -->
            <section class="mb-16">
                <div class="bg-gradient-to-r from-orange-50 to-amber-50 rounded-xl p-8 border border-orange-100">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                        <span class="mr-3">🤖</span>
                        Sangkay Chatbot
                    </h2>
                    <p class="text-gray-700 leading-relaxed mb-6">
                        The Sangkay Chatbot is an AI-powered virtual assistant built using the Rasa Framework and integrated with Laravel. It is designed to understand student inquiries using Natural Language Processing (NLP) and provide instant responses through a web-based platform.
                    </p>
                    
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Key Features:</h3>
                    <ul class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-6">
                        <li class="flex items-start">
                            <span class="text-green-500 mr-2">✓</span>
                            <span class="text-gray-700">Real-time automated responses</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-500 mr-2">✓</span>
                            <span class="text-gray-700">NLP-powered intent recognition</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-500 mr-2">✓</span>
                            <span class="text-gray-700">Context-aware conversation handling</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-500 mr-2">✓</span>
                            <span class="text-gray-700">Seamless integration with ticketing system</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-500 mr-2">✓</span>
                            <span class="text-gray-700">24/7 accessibility for student inquiries</span>
                        </li>
                    </ul>
                    <p class="text-gray-700 leading-relaxed bg-white/50 p-4 rounded-lg">
                        <strong>Smart Escalation:</strong> If the chatbot is unable to fully resolve a concern, it automatically guides students to submit a support ticket for further assistance — ensuring that every inquiry is properly addressed.
                    </p>
                </div>
            </section>

            <!-- Sangkay FAQs Section -->
            <section class="mb-16">
                <div class="bg-blue-50 rounded-xl p-8 border border-blue-100">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                        <span class="mr-3">❓</span>
                        Sangkay FAQs
                    </h2>
                    <p class="text-gray-700 leading-relaxed mb-6">
                        The Sangkay FAQs Module serves as a centralized knowledge base containing commonly asked questions about academic services, library concerns, policies, and general student information.
                    </p>
                    
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Purpose:</h3>
                    <ul class="space-y-2 mb-6">
                        <li class="flex items-start">
                            <span class="text-blue-500 mr-2">•</span>
                            <span class="text-gray-700">Provide immediate answers to frequently asked questions</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-blue-500 mr-2">•</span>
                            <span class="text-gray-700">Reduce repetitive inquiries</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-blue-500 mr-2">•</span>
                            <span class="text-gray-700">Improve information accessibility</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-blue-500 mr-2">•</span>
                            <span class="text-gray-700">Support students anytime</span>
                        </li>
                    </ul>
                    <p class="text-gray-700 leading-relaxed">
                        The FAQ system is continuously maintained and updated to ensure accuracy, clarity, and relevance for all students.
                    </p>
                </div>
            </section>

            <!-- Sangkay Ticketing System Section -->
            <section class="mb-16">
                <div class="bg-green-50 rounded-xl p-8 border border-green-100">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                        <span class="mr-3">🎫</span>
                        Sangkay Ticketing System
                    </h2>
                    <p class="text-gray-700 leading-relaxed mb-6">
                        The Sangkay Ticketing System is an integrated support management tool that allows students to formally submit concerns requiring human assistance. Instead of traditional account registration, Sangkay uses a secure Email OTP verification process, making it accessible, secure, and easy to use.
                    </p>
                    
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Features:</h3>
                    <ul class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-6">
                        <li class="flex items-start">
                            <span class="text-green-500 mr-2">✓</span>
                            <span class="text-gray-700">Secure OTP-based email verification</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-500 mr-2">✓</span>
                            <span class="text-gray-700">Ticket submission and tracking</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-500 mr-2">✓</span>
                            <span class="text-gray-700">Real-time ticket status updates</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-500 mr-2">✓</span>
                            <span class="text-gray-700">Organized concern categorization</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-500 mr-2">✓</span>
                            <span class="text-gray-700">Staff response management</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-green-500 mr-2">✓</span>
                            <span class="text-gray-700">Transparent communication between students and support staff</span>
                        </li>
                    </ul>
                    <p class="text-gray-700 leading-relaxed bg-white/50 p-4 rounded-lg">
                        This ensures that concerns such as lost books, fines, damaged materials, lost borrower's cards, and other academic-related issues are properly documented and efficiently resolved.
                    </p>
                </div>
            </section>

            <!-- Mission and Vision Section -->
            <section class="mb-16">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Mission -->
                    <div class="bg-orange-100 rounded-xl p-8 border border-orange-200">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                            <span class="mr-3">🎯</span>
                            Our Mission
                        </h2>
                        <p class="text-gray-700 leading-relaxed">
                            To provide a smart, efficient, and student-centered digital support system that enhances communication, improves service response time, and promotes technological innovation within Abuyog Community College.
                        </p>
                    </div>

                    <!-- Vision -->
                    <div class="bg-purple-100 rounded-xl p-8 border border-purple-200">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                            <span class="mr-3">🔭</span>
                            Our Vision
                        </h2>
                        <p class="text-gray-700 leading-relaxed">
                            To become a model AI-powered academic support system that empowers students through accessible, reliable, and intelligent digital assistance.
                        </p>
                    </div>
                </div>
            </section>

            <!-- Contact Section -->
            <section class="text-center py-8 border-t border-gray-200">
                <p class="text-gray-600 mb-4">
                    Have questions or feedback about Sangkay? Our team is here to help.
                </p>
                <a href="{{ route('contact') }}" class="inline-flex items-center px-6 py-3 rounded-lg text-white font-medium transition-colors" style="background-color: #FF9D00;">
                    <span class="mr-2">📧</span>
                    Contact Us
                </a>
            </section>
        </div>
    </div>
@endsection
