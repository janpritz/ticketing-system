@extends('layouts.staff')

@section('title', 'FAQ Details')

@section('staff-content')
<div class="max-w-4xl mx-auto bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="p-6 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">FAQ Details</h1>
                <p class="text-gray-600 mt-1">View FAQ information</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('staff.knowledgebase.edit', $faq) }}"
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-300">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit FAQ
                </a>
                <a href="{{ route('staff.knowledgebase.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:ring-4 focus:ring-gray-300">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to FAQs
                </a>
            </div>
        </div>
    </div>

    <div class="p-6 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Intent</h3>
                <p class="mt-2 text-lg font-semibold text-gray-900">{{ $faq->intent }}</p>
            </div>

            <div>
                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Created At</h3>
                <p class="mt-2 text-lg font-semibold text-gray-900">{{ $faq->created_at->format('M d, Y H:i') }}</p>
            </div>
        </div>

        @if($faq->description)
        <div>
            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Description</h3>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-gray-900 whitespace-pre-wrap">{{ $faq->description }}</p>
            </div>
        </div>
        @endif

        <div>
            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Response</h3>
            <div class="bg-blue-50 rounded-lg p-4 border-l-4 border-blue-400">
                <p class="text-gray-900 whitespace-pre-wrap">{{ $faq->response }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Updated At</h3>
                <p class="mt-2 text-lg font-semibold text-gray-900">{{ $faq->updated_at->format('M d, Y H:i') }}</p>
            </div>

            <div>
                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Status</h3>
                <p class="mt-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        Active
                    </span>
                </p>
            </div>
        </div>
    </div>

    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
        <div class="flex justify-end space-x-3">
            <form method="POST" action="{{ route('staff.knowledgebase.destroy', $faq) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this FAQ? This action cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 focus:ring-4 focus:ring-red-300">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Delete FAQ
                </button>
            </form>
        </div>
    </div>
</div>
@endsection