@extends('layouts.app')

@section('title', 'Frequently Asked Questions')

@section('content')
    <div class="bg-white flex flex-col min-h-screen">
        <!-- Navigation Bar - Using Component -->
        <x-public-nav active="home" :logo-text="'SANGKAY FAQs'" />

        <!-- ChatGPT/Claude/Gemini Style Landing -->
        <div class="flex-1 flex flex-col items-center justify-center px-4 pt-8 pb-24">
            <div class="max-w-3xl w-full text-center">
                <!-- Welcome Greeting -->
                <div class="mb-8">
                    <h1 class="text-5xl font-semibold text-gray-900 mb-3">Hi there! 👋</h1>
                    <p class="text-2xl text-gray-700">How can I help you today?</p>
                    <p class="mt-2 text-gray-500">Ask me anything about SANGKAY or browse popular questions below.</p>
                </div>

                <!-- Top 3 Floating Question Cards -->
                @if ($faqs->isNotEmpty())
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-10">
                        @foreach ($faqs->take(3) as $index => $faq)
                            <button class="faq-card group p-5 bg-white border border-gray-200 rounded-2xl shadow-sm hover:shadow-lg hover:border-orange-300 transition-all text-left">
                                <div class="flex items-start gap-3">
                                    <div class="mt-1 w-8 h-8 flex-shrink-0 rounded-full bg-orange-100 flex items-center justify-center">
                                        <span class="text-orange-600 text-sm font-medium">{{ $index + 1 }}</span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-gray-900 font-medium leading-snug group-hover:text-orange-600 transition-colors">
                                            {{ $faq->suggested_q }}
                                        </p>
                                    </div>
                                </div>
                            </button>
                        @endforeach
                    </div>
                @endif

                <!-- Message Input Field -->
                <div class="max-w-2xl mx-auto w-full">
                    <div class="relative">
                        <div class="flex items-center bg-white border border-gray-300 rounded-3xl shadow-sm focus-within:border-orange-400 focus-within:ring-1 focus-within:ring-orange-400 transition-all">
                            <input type="text" id="messageInput" 
                                   class="flex-1 bg-transparent px-6 py-4 text-gray-900 placeholder-gray-400 focus:outline-none text-base"
                                   placeholder="Send a message...">
                            <button onclick="sendMessage()"
                                    class="mr-2 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-full transition-colors flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                            </button>
                        </div>
                        <p class="text-xs text-gray-400 mt-2 text-center">Press enter to send • Click cards above to ask a question</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FAQ Modal -->
    <div id="faqModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl max-w-lg w-full mx-4 overflow-hidden">
            <div class="px-6 py-5 border-b flex justify-between items-center">
                <h3 id="modalQuestion" class="font-semibold text-gray-900 pr-4"></h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <div class="px-6 py-6">
                <p id="modalAnswer" class="text-gray-700 leading-relaxed"></p>
            </div>
            <div class="px-6 py-4 bg-gray-50 flex justify-end">
                <button onclick="closeModal()" 
                        class="px-5 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>

    {{-- @include('components.chatbot-widget') --}}
@endsection
@push('scripts')
    @include('guest.faqs.index.scripts')
@endpush
