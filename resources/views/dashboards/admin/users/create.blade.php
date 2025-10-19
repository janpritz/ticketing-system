@extends('layouts.admin')

@section('title', 'Add Staff')

@section('admin-content')
<div class="px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Add Staff</h1>
            <p class="text-sm text-slate-500">Create a new staff account</p>
        </div>
    </div>

    @if (session('status'))
        <div class="mt-4 rounded-md border border-emerald-200 bg-emerald-50 text-emerald-800 px-4 py-2 text-sm">
            {{ session('status') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="mt-4 rounded-md border border-red-200 bg-red-50 text-red-800 px-4 py-2 text-sm">
            Please fix the errors below.
        </div>
    @endif

    <div class="mt-5 bg-white rounded-xl border border-gray-200 p-5 max-w-2xl">
        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700">Full Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Role</label>
                    @php
                        // Exclude Primary Administrator from the selectable roles
                        $roles = \App\Models\Role::orderBy('name')->where('name', '!=', 'Primary Administrator')->pluck('name')->toArray();
                    @endphp
                    <select name="role" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="" disabled selected>Select role</option>
                        @foreach($roles as $r)
                            <option value="{{ $r }}" {{ old('role') === $r ? 'selected' : '' }}>{{ $r }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-[11px] text-slate-500">Note: "Primary Administrator" cannot be created here.</p>
                    @error('role')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Category/Department (optional)</label>
                    @php
                        $categories = \App\Models\Category::orderBy('name')->pluck('name')->unique()->toArray();
                    @endphp
                    <select name="category" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">— None —</option>
                        @foreach($categories as $c)
                            <option value="{{ $c }}" {{ old('category') === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                    @error('category')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Password</label>
                    <input type="password" name="password" required
                           class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                    @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Confirm Password</label>
                    <input type="password" name="password_confirmation" required
                           class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                </div>
            </div>

            <div class="pt-3 flex items-center gap-3">
                <a href="{{ route('admin.users.index') }}" class="rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-sm px-4 py-2">Cancel</a>
                <button type="submit" class="rounded-md bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2">Create Staff</button>
            </div>
        </form>
    </div>
</div>
@endsection
@section('admin-scripts')
  @parent
  <script>
    (function () {
      try {
        if (typeof Swal !== 'undefined') {
          @if (session('status'))
          Swal.fire({
            icon: 'success',
            title: {!! json_encode(session('status')) !!},
            toast: true,
            position: 'top-end',
            timer: 3000,
            showConfirmButton: false
          });
          @endif

          @if ($errors->any())
          Swal.fire({
            icon: 'error',
            title: 'Validation error',
            text: {!! json_encode($errors->first()) !!}
          });
          @endif
        }
      } catch (e) { /* ignore */ }
    })();
  </script>
@endsection