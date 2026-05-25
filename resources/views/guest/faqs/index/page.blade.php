@extends('layouts.app')

@section('title', 'Frequently Asked Questions')

@section('content')
<div class="bg-gray-50 flex flex-col min-h-screen">
    <x-public-nav active="home" :logo-text="'SANGKAY TICKETS'" />

    <div class="flex-1 flex flex-col items-center justify-center px-6 pt-16 pb-24">
        <div class="max-w-2xl w-full space-y-12">
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
            </div>

            <div class="relative max-w-2xl mx-auto w-full">
                {{-- Message Input Area --}}
                <div class="relative max-w-2xl mx-auto w-full">
                    <div id="input-shell"
                        class="flex items-center bg-white rounded-full shadow-md px-5 pr-2 h-14 gap-3
                transition-shadow duration-200 focus-within:shadow-blue-200 focus-within:shadow-lg">

                        {{-- Text input --}}
                        <input
                            type="text"
                            id="messageInput"
                            class="flex-1 bg-transparent text-gray-800 placeholder-gray-400 text-[15px]
                   focus:outline-none caret-blue-500"
                            placeholder="Ask Sangkay anything…"
                            oninput="toggleSendBtn(this)"
                            onkeydown="handleEnter(event)"
                            autocomplete="off" />

                        {{-- Send button --}}
                        <button
                            id="sendBtn"
                            type="button"
                            onclick="sendMessage()"
                            disabled
                            class="w-10 h-10 flex items-center justify-center rounded-full flex-shrink-0
                   bg-slate-200 text-slate-400 cursor-default
                   transition-all duration-150 active:scale-90"
                            aria-label="Send message">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M12 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>

                    <p class="mt-3 text-center text-xs text-gray-400">
                        Press Enter to send &nbsp;·&nbsp; Or pick a question below
                    </p>
                </div>
            </div>

            @if ($faqs->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach ($faqs->take(3) as $faq)
                <button
                    class="group bg-white border border-gray-200 hover:border-orange-300 rounded-xl px-5 py-4 text-left transition-all shadow-sm hover:shadow-md faq-suggest-btn"
                    data-question="{{ addslashes($faq->suggested_q) }}"
                    data-answer="{{ addslashes($faq->answer ?? 'Our team will get back to you shortly.') }}">
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

<div id="faq-modal"
    x-data="{ show: false, question: '', answer: '' }"
    x-show="show"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4"
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
               bg-white
               shadow-[0_25px_80px_rgba(0,0,0,0.28)]
               border border-gray-200">

        <!-- Header -->
        <div class="px-8 pt-8 pb-6 flex items-start gap-4">
            <div class="w-11 h-11 rounded-2xl bg-orange-100 flex items-center justify-center flex-shrink-0">
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
                       text-gray-400 hover:text-gray-700 hover:bg-gray-100
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
    </div>
</div>

@endsection
@push('scripts')
@include('guest.faqs.index.scripts')
@endpush