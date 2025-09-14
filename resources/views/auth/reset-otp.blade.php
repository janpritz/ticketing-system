@extends('layouts.app')

@section('title', 'Reset Password')

@section('content')
<div class="bg-gray-50">
  <div class="min-h-screen flex flex-col items-center justify-center py-6 px-4">
    <div class="max-w-[480px] w-full">
      <div class="p-6 sm:p-8 rounded-2xl bg-white border border-gray-200 shadow-sm">
        <h1 class="text-slate-900 text-center text-2xl font-semibold">Reset Password</h1>

        @if(session('status'))
          <div class="mt-4 rounded-md border border-emerald-200 bg-emerald-50 text-emerald-800 px-3 py-2 text-sm">
            {{ session('status') }}
          </div>
        @endif

        <form method="POST" action="{{ route('password.reset.apply') }}" class="mt-6 space-y-5">
          @csrf

          <div>
            <label for="email" class="text-slate-900 text-sm font-medium mb-2 block">Email</label>
            <input id="email"
                   name="email"
                   type="email"
                   required
                   class="w-full text-slate-900 text-sm border border-slate-300 px-4 py-3 rounded-md outline-blue-600"
                   placeholder="Enter your account email"
                   value="{{ old('email', $email ?? '') }}" />
            @error('email')
              <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
            @enderror
          </div>

          <div>
            <label for="otp" class="text-slate-900 text-sm font-medium mb-2 block">6‑digit OTP</label>
            <input id="otp"
                   name="otp"
                   type="text"
                   inputmode="numeric"
                   pattern="[0-9]{6}"
                   maxlength="6"
                   required
                   class="w-full text-slate-900 text-sm border border-slate-300 px-4 py-3 rounded-md outline-blue-600 tracking-widest text-center"
                   placeholder="______"
                   value="{{ old('otp') }}" />
            @error('otp')
              <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
            @enderror
            <div class="text-xs text-slate-600 mt-1">OTP expires in 10 minutes.</div>
          </div>

          <div>
            <label for="password" class="text-slate-900 text-sm font-medium mb-2 block">New Password</label>
            <div class="relative flex items-center">
              <input id="password"
                     name="password"
                     type="password"
                     required
                     class="w-full text-slate-900 text-sm border border-slate-300 px-4 py-3 pr-8 rounded-md outline-blue-600"
                     placeholder="Enter new password" />
              <svg id="toggleNewPassword" xmlns="http://www.w3.org/2000/svg" fill="#bbb" stroke="#bbb" class="w-4 h-4 absolute right-4 cursor-pointer" viewBox="0 0 128 128" role="button" tabindex="0" aria-label="Toggle new password visibility">
                <path d="M64 104C22.127 104 1.367 67.496.504 65.943a4 4 0 0 1 0-3.887C1.367 60.504 22.127 24 64 24s62.633 36.504 63.496 38.057a4 4 0 0 1 0 3.887C126.633 67.496 105.873 104 64 104zM8.707 63.994C13.465 71.205 32.146 96 64 96c31.955 0 50.553-24.775 55.293-31.994C114.535 56.795 95.854 32 64 32 32.045 32 13.447 56.775 8.707 63.994zM64 88c-13.234 0-24-10.766-24-24s10.766-24 24-24 24 10.766 24 24-10.766 24-24 24zm0-40c-8.822 0-16 7.178-16 16s7.178 16 16 16 16-7.178 16-16-7.178-16-16-16z"></path>
              </svg>
            </div>
            @error('password')
              <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
            @enderror
          </div>

          <div>
            <label for="password_confirmation" class="text-slate-900 text-sm font-medium mb-2 block">Confirm New Password</label>
            <div class="relative flex items-center">
              <input id="password_confirmation"
                     name="password_confirmation"
                     type="password"
                     required
                     class="w-full text-slate-900 text-sm border border-slate-300 px-4 py-3 pr-8 rounded-md outline-blue-600"
                     placeholder="Confirm new password" />
              <svg id="toggleConfirmPassword" xmlns="http://www.w3.org/2000/svg" fill="#bbb" stroke="#bbb" class="w-4 h-4 absolute right-4 cursor-pointer" viewBox="0 0 128 128" role="button" tabindex="0" aria-label="Toggle confirm password visibility">
                <path d="M64 104C22.127 104 1.367 67.496.504 65.943a4 4 0 0 1 0-3.887C1.367 60.504 22.127 24 64 24s62.633 36.504 63.496 38.057a4 4 0 0 1 0 3.887C126.633 67.496 105.873 104 64 104zM8.707 63.994C13.465 71.205 32.146 96 64 96c31.955 0 50.553-24.775 55.293-31.994C114.535 56.795 95.854 32 64 32 32.045 32 13.447 56.775 8.707 63.994zM64 88c-13.234 0-24-10.766-24-24s10.766-24 24-24 24 10.766 24 24-10.766 24-24 24zm0-40c-8.822 0-16 7.178-16 16s7.178 16 16 16 16-7.178 16-16-7.178-16-16-16z"></path>
              </svg>
            </div>
          </div>

          <div class="!mt-6">
            <button type="submit" class="w-full py-2 px-4 text-[15px] font-medium tracking-wide rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none cursor-pointer">
              Update Password
            </button>
          </div>

          <div class="text-center text-sm mt-2">
            <a href="{{ route('password.forgot') }}" class="text-blue-600 hover:underline">Resend OTP</a>
            <span class="text-slate-400 mx-1">•</span>
            <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Back to Sign in</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
(function() {
  function bindToggle(id, inputId) {
    const t = document.getElementById(id);
    const inp = document.getElementById(inputId);
    if (!t || !inp) return;
    function go() { inp.type = (inp.type === 'text') ? 'password' : 'text'; }
    t.addEventListener('click', go);
    t.addEventListener('keydown', (e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); go(); }});
  }
  bindToggle('toggleNewPassword', 'password');
  bindToggle('toggleConfirmPassword', 'password_confirmation');
})();
</script>
@endsection