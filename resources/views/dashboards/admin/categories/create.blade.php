@extends('layouts.admin')

@section('title', 'Create Category')

@section('admin-content')
<div class="sm:px-2">
  <div class="max-w-3xl mx-auto bg-white rounded-lg shadow p-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-slate-900">Create Category</h1>
        <p class="text-sm text-slate-500">Assign a category to an existing role.</p>
      </div>
      <div>
        <a href="{{ route('admin.categories.index') }}" class="text-sm text-blue-600 hover:underline">Back to categories</a>
      </div>
    </div>

    <form method="POST" action="{{ route('admin.categories.store') }}" class="mt-6 space-y-4">
      @csrf

      <div>
        <label class="block text-sm font-medium text-slate-700">Role</label>
        <div class="mt-1">
          <select name="role_id" required class="py-2 px-3 block w-full border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            <option value="">Select role</option>
            @foreach($roles as $role)
              <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700">Name</label>
        <div class="mt-1">
          <input type="text" name="name" required value="{{ old('name') }}" class="py-2 px-3 block w-full border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700">Description (optional)</label>
        <div class="mt-1">
          <textarea name="description" rows="3" class="py-2 px-3 block w-full border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">{{ old('description') }}</textarea>
        </div>
      </div>

      <div class="pt-2 flex items-center justify-end gap-3">
        <a href="{{ route('admin.categories.index') }}" class="rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-sm px-4 py-2">Cancel</a>
        <button type="submit" class="rounded-md bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2">Create Category</button>
      </div>
    </form>
  </div>
</div>
@endsection