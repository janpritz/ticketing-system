@extends('layouts.staff')

@section('title', 'Create FAQ')

@section('staff-content')
<div class="max-w-2xl mx-auto bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="p-6 border-b border-gray-200">
        <h1 class="text-2xl font-bold text-gray-900">Create New FAQ</h1>
        <p class="text-gray-600 mt-1">Add a new frequently asked question to the knowledgebase</p>
    </div>

    <form method="POST" action="{{ route('staff.knowledgebase.store') }}" class="p-6 space-y-6">
        @csrf

        <div>
            <label for="intent" class="block text-sm font-medium text-gray-700 mb-2">Intent *</label>
            <input type="text" id="intent" name="intent" value="{{ old('intent') }}" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('intent') border-red-500 @enderror">
            @error('intent')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-sm text-gray-500">The intent or question that users might ask</p>
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
            <textarea id="description" name="description" rows="3"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
            @error('description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-sm text-gray-500">Brief description of what this FAQ covers</p>
        </div>

        <div>
            <label for="response" class="block text-sm font-medium text-gray-700 mb-2">Response *</label>
            <textarea id="response" name="response" rows="6" required
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('response') border-red-500 @enderror">{{ old('response') }}</textarea>
            @error('response')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-sm text-gray-500">The answer or response to provide to users</p>
        </div>

        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
            <a href="{{ route('staff.knowledgebase.index') }}"
               class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:ring-4 focus:ring-gray-300">
                Cancel
            </a>
            <button type="submit"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300">
                Create FAQ
            </button>
        </div>
    </form>
</div>
@endsection