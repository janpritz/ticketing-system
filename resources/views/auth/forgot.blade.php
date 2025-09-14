@extends('layouts.app')

@section('title', 'Forgot Password')

@section('content')
<div class="bg-gray-50">
  <div class="min-h-screen flex flex-col items-center justify-center py-6 px-4">
    <div class="max-w-[480px] w-full">
      <div class="p-6 sm:p-8 rounded-2xl bg-white border border-gray-200 shadow-sm">
        <h1 class="text-slate-900 text-center text-2xl font-semibold">Forgot Password</h1>

        @if(session('status'))
          <div class="mt-4 rounded-md border border-emerald-200 bg-emerald-50 text-emerald-800 px-3 py-2 text-sm">
            {{ session('status') }}
          </div>
        @endif

        <form method="POST" action="{{ route('password.otp') }}" class="mt-6 space-y-5">
          @csrf
          <div>
            <label for="email" class="text-slate-900 text-sm font-medium mb-2 block">Email</label>
            <input id="email"
                   name="email"
                   type="email"
                   required
                   class="w-full text-slate-900 text-sm border border-slate-300 px-4 py-3 rounded-md outline-blue-600"
                   placeholder="Enter your account email"
                   value="{{ old('email') }}" />
            @error('email')
              <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
            @enderror
          </div>

        <div class="text-xs text-slate-600">
            You will receive a 6-digit One-Time Password (OTP) to verify your request.
          </div>

          <div class="!mt-6">
            <button type="submit" class="w-full py-2 px-4 text-[15px] font-medium tracking-wide rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none cursor-pointer">
              Send OTP
            </button>
          </div>

          <div class="text-center text-sm mt-2">
            <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Back to Sign in</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection