@extends('layouts.app')

@section('title', 'Frequently Asked Questions')

@section('content')
<div class="bg-gray-50 flex flex-col min-h-screen">
    <!-- Navigation Bar - Using Component -->
    <x-public-nav active="home" :logo-text="'SANGKAY FAQs'" />

    <!-- ChatGPT/Claude/Gemini Style Landing -->
    <div class="flex-1 flex flex-col items-center justify-center px-6 pt-16 pb-24">
        <div class="max-w-2xl w-full space-y-12">
            <!-- Welcome Greeting -->
            @php
            $greetings = [
            'Where should we begin?',
            'Ask anything to Sangkay',
            'Welcome back ACCian!',
            'How can I help you today?'
            ];
            $greeting = $greetings[array_rand($greetings)];
            @endphp

                <div class="text-center">
                    <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ $greeting }}</h1>
                    <!-- <p class="text-xl text-gray-600 mb-2">How can I help you today?</p>
                    <p class="text-base text-gray-500">Ask me anything about SANGKAY or browse popular questions below.</p> -->
                </div>

            <!-- Message Input Area -->
            <div class="relative">
                <label for="messageInput" class="sr-only">Message input</label>
                <div class="flex items-center bg-white border border-gray-200 rounded-xl shadow-sm focus-within:border-orange-400 focus-within:ring-2 focus-within:ring-orange-400 transition-all duration-200">
                    <input type="text" id="messageInput"
                        class="flex-1 bg-transparent px-6 py-4 text-gray-900 placeholder-gray-400 focus:outline-none text-base resize-none"
                        placeholder="Type your message here...">
                    <button onclick="sendMessage()"
                        class="ml-3 flex-shrink-0 px-5 py-2 bg-orange-600 hover:bg-orange-700 text-white font-medium rounded-xl transition-all duration-200 flex items-center justify-center">
                        Send
                    </button>
                </div>
                <p class="mt-2 text-xs text-gray-400 text-center">
                    Press Enter to send • Or pick a question below
                </p>
            </div>

            <!-- Top 3 Suggestion Cards -->
            @if ($faqs->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach ($faqs->take(3) as $faq)
                <button onclick="showFaqModal({{ Js::encode($faq->suggested_q) }}, {{ Js::encode($faq->answer ?? 'Our team will get back to you shortly.') }})"
                    class="group bg-white border border-gray-200 hover:border-orange-300 rounded-xl px-5 py-4 text-left transition-all shadow-sm hover:shadow-md">
                    <p class="text-gray-900 font-medium group-hover:text-orange-600 transition-colors">
                        {{ $faq->suggested_q }}
                    </p>
                </button>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modern Glass FAQ Modal -->
<div id="faq-modal"
    x-data="{ show: false, question: '', answer: '' }"
    x-show="show"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xl px-4"
    @keydown.escape.window="show = false">

    <div
        @click.away="show = false"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="w-full max-w-2xl overflow-hidden rounded-[28px]
               bg-white/95 backdrop-blur-2xl shadow-[0_20px_70px_rgba(0,0,0,0.25)]">

        <!-- Header -->
        <div class="px-8 pt-8 pb-6 flex items-start gap-4">
            <div class="w-11 h-11 rounded-2xl bg-orange-100/80 flex items-center justify-center flex-shrink-0">
                <span class="text-2xl">💡</span>
            </div>

            <div class="flex-1 min-w-0">
                <p class="text-[11px] uppercase tracking-[0.2em] font-semibold text-orange-500 mb-2">
                    Question
                </p>

                <h3 class="text-2xl font-semibold leading-tight text-gray-900"
                    x-text="question">
                </h3>
            </div>

            <button
                @click="show = false"
                class="w-10 h-10 rounded-2xl flex items-center justify-center
                       text-gray-400 hover:text-gray-700 hover:bg-gray-100/70
                       transition-all duration-200">
                <span class="text-3xl leading-none -mt-1">&times;</span>
            </button>
        </div>

        <!-- Body -->
        <div class="px-8 pb-8 max-h-[60vh] overflow-y-auto custom-scroll">
            <div class="space-y-4">
                <div class="text-[11px] uppercase tracking-[0.2em] font-semibold text-gray-400">
                    Answer
                </div>

                <p class="text-[15.5px] leading-8 text-gray-700 whitespace-pre-line"
                    x-text="answer">
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="px-8 py-5 flex items-center justify-between bg-gray-50/70">
            <button
                @click="copyAnswer()"
                class="flex items-center gap-2 text-sm text-gray-500
                       hover:text-gray-800 transition-all duration-200">

                <svg xmlns="http://www.w3.org/2000/svg"
                    class="w-4 h-4"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor">

                    <path stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M8 16h8M8 12h8m-8-4h8M4 6h16v12H4z" />
                </svg>

                <span>Copy Answer</span>
            </button>

            <button
                @click="show = false"
                class="px-5 py-2.5 rounded-2xl bg-orange-600 text-white
                       hover:bg-orange-700 active:scale-95
                       transition-all duration-200 text-sm font-medium shadow-md">
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