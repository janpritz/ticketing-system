@extends('layouts.app')

@section('title', 'Frequently Asked Questions')

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // FAQ Accordion Toggle
    const faqHeaders = document.querySelectorAll('.faq-header');
    
    faqHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const item = this.closest('.faq-item');
            const body = item.querySelector('.faq-body');
            const chevron = item.querySelector('.faq-chevron');
            
            // Toggle the hidden class
            body.classList.toggle('hidden');
            
            // Rotate the chevron
            if (chevron) {
                chevron.classList.toggle('rotate-180');
            }
        });
    });
    
    // Search Functionality
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const faqItems = document.querySelectorAll('.faq-item');
            
            faqItems.forEach(item => {
                const question = item.querySelector('h3').textContent.toLowerCase();
                const topic = item.querySelector('.text-gray-500')?.textContent.toLowerCase() || '';
                
                if (question.includes(searchTerm) || topic.includes(searchTerm)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }
});
</script>
@endsection

@section('content')
    <div class="bg-white flex flex-col min-h-screen lg:h-screen">
        <!-- Navigation Bar - Using Component -->
        <x-public-nav active="home" :logo-text="'SANGKAY FAQs'" />

        <!-- Main Content -->
        <div class="flex-1 overflow-visible lg:overflow-hidden mt-4 lg:mt-4">
            <div class="max-w-7xl mx-auto h-full px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-0 h-full">
                    <!-- Left Column: Title and FAQs -->
                    <div class="lg:col-span-1 flex flex-col lg:overflow-hidden mt-12 lg:mt-20">
                        <div class="flex-shrink-0 pt-8 px-0 lg:pt-50 lg:px-0">
                            <h1 class="text-4xl font-bold text-gray-900 mb-2 px-8 lg:px-12">Frequently Asked</h1>
                            <h2 class="text-4xl font-bold text-gray-900 mb-8 px-8 lg:px-12">Questions</h2>

                            <!-- Search Bar -->
                            <div class="mb-8 px-8 lg:px-12">
                                <div class="relative">
                                    <input type="text" id="searchInput" placeholder="Search question here"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                    <button class="absolute right-3 top-3 text-gray-400 hover:text-gray-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- FAQ List - Scrollable -->
                        <div class="flex-1 lg:overflow-y-auto px-0 lg:px-0 pb-8">
                            <div class="space-y-3 px-8 lg:px-12">
                                @if ($faqs->isEmpty())
                                    <div class="text-center py-12">
                                        <p class="text-gray-600">No FAQs available at the moment.</p>
                                    </div>
                                @else
                                    @foreach ($faqs as $faq)
                                        <div
                                            class="faq-item border border-gray-200 rounded-lg overflow-hidden hover:shadow-md transition-shadow">
                                            <!-- FAQ Header (Question) -->
                                            <button
                                                class="faq-header w-full px-6 py-4 flex items-center justify-between bg-white hover:bg-gray-50 transition-colors text-left">
                                                <div class="flex-1">
                                                    <h3 class="text-gray-900 font-medium">{{ $faq->suggested_q }}</h3>
                                                    @if ($faq->general_topic)
                                                        <p class="text-sm text-gray-500 mt-1">{{ $faq->general_topic }}</p>
                                                    @endif
                                                </div>
                                                <svg class="faq-chevron w-5 h-5 text-gray-400 flex-shrink-0 ml-4 transition-transform duration-300"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                                </svg>
                                            </button>

                                            <!-- FAQ Body (Answer - Hidden by default) -->
                                            <div class="faq-body hidden bg-gray-50 px-6 py-4 border-t border-gray-200">
                                                <p class="text-gray-700 text-sm leading-relaxed">
                                                    {!! nl2br(e($faq->suggested_a)) !!}
                                                </p>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Illustration -->
                    <div
                        class="lg:col-span-1 
                                flex items-center justify-center 
                                mt-8 lg:mt-20 
                                h-auto lg:h-full">

                        <img src="{{ asset('faq_img.png') }}" alt="FAQ Illustration"
                            class="w-full 
                                    max-w-md mx-auto 
                                    h-auto 
                                    lg:w-full lg:h-full 
                                    object-contain lg:object-cover">
                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection
