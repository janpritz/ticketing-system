@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div>
      <div class="min-h-screen flex flex-col items-center justify-center py-6 px-4">
        <div class="max-w-[480px] w-full">
          <div class="flex items-center justify-center mb-5">
            <img src="{{ asset('logo.png') }}" alt="Sangkay ITS logo" class="w-24 h-auto" loading="lazy" decoding="async" />
            <h1 class="text-slate-900 text-center text-3xl font-semibold">Sangkay Integrated Ticketing System</h1>
          </div>
          <div class="p-6 sm:p-8 rounded-2xl bg-white border border-gray-200 shadow-sm">
            <h1 class="text-slate-900 text-center text-3xl font-semibold">Sign in</h1>

            @if ($errors->any())
              <div class="mt-4 rounded-md border border-red-200 bg-red-50 text-red-800 px-3 py-2 text-sm">
                <div class="font-semibold">Sign in error</div>
                <ul class="mt-1 list-disc pl-5 space-y-1">
                  @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="mt-12 space-y-6">
              @csrf
              <div>
                <label class="text-slate-900 text-sm font-medium mb-2 block">Email</label>
                <div class="relative flex items-center">
                  <input name="email" type="email" required class="w-full text-slate-900 text-sm border border-slate-300 px-4 py-3 pr-8 rounded-md outline-blue-600" placeholder="Enter email" value="{{ old('email') }}" />
                  <svg xmlns="http://www.w3.org/2000/svg" fill="#bbb" stroke="#bbb" class="w-4 h-4 absolute right-4" viewBox="0 0 24 24">
                    <circle cx="10" cy="7" r="6" data-original="#000000"></circle>
                    <path d="M14 15H6a5 5 0 0 0-5 5 3 3 0 0 0 3 3h12a3 3 0 0 0 3-3 5 5 0 0 0-5-5zm8-4h-2.59l.3-.29a1 1 0 0 0-1.42-1.42l-2 2a1 1 0 0 0 0 1.42l2 2a1 1 0 0 0 1.42 0 1 1 0 0 0 0-1.42l-.3-.29H22a1 1 0 0 0 0-2z" data-original="#000000"></path>
                  </svg>
                </div>
              </div>
              <div>
                <label class="text-slate-900 text-sm font-medium mb-2 block">Password</label>
                <div class="relative flex items-center">
                  <input name="password" type="password" required class="w-full text-slate-900 text-sm border border-slate-300 px-4 py-3 pr-8 rounded-md outline-blue-600" placeholder="Enter password" />
                  <!-- Open Eye Icon -->
                  <svg id="eyeOpen" xmlns="http://www.w3.org/2000/svg" fill="#bbb" stroke="#bbb" class="w-4 h-4 absolute right-4 cursor-pointer" viewBox="0 0 128 128" aria-label="Show password" role="button" tabindex="0">
                    <path d="M64 104C22.127 104 1.367 67.496.504 65.943a4 4 0 0 1 0-3.887C1.367 60.504 22.127 24 64 24s62.633 36.504 63.496 38.057a4 4 0 0 1 0 3.887C126.633 67.496 105.873 104 64 104zM8.707 63.994C13.465 71.205 32.146 96 64 96c31.955 0 50.553-24.775 55.293-31.994C114.535 56.795 95.854 32 64 32 32.045 32 13.447 56.775 8.707 63.994zM64 88c-13.234 0-24-10.766-24-24s10.766-24 24-24 24 10.766 24 24-10.766 24-24 24zm0-40c-8.822 0-16 7.178-16 16s7.178 16 16 16 16-7.178 16-16-7.178-16-16-16z" data-original="#000000"></path>
                  </svg>
                  <!-- Closed Eye Icon -->
                  <svg id="eyeClosed" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="#bbb" stroke-width="2" class="w-4 h-4 absolute right-4 cursor-pointer hidden" viewBox="0 0 24 24" aria-label="Hide password" role="button" tabindex="0">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                  </svg>
                </div>
              </div>
              <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center">
                  <input id="remember" name="remember" value="1" type="checkbox" class="h-4 w-4 shrink-0 text-blue-600 focus:ring-blue-500 border-slate-300 rounded" {{ old('remember') ? 'checked' : '' }} />
                  <label for="remember" class="ml-3 block text-sm text-slate-900">
                    Remember me
                  </label>
                </div>
                <div class="text-sm">
                  <a href="{{ route('password.forgot') }}" class="text-blue-600 hover:underline font-semibold">
                    Forgot your password?
                  </a>
                </div>
              </div>

              <div class="!mt-12">
                <button type="submit" class="w-full py-2 px-4 text-[15px] font-medium tracking-wide rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none cursor-pointer">
                  Sign in
                </button>
              </div>
              
            </form>
          </div>
        </div>
      </div>
    </div>
@endsection

@push('scripts')
<!-- SweetAlert2 for success messages -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Show success SweetAlert for account verification
@if(session('success'))
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'success',
            title: 'Account Setup Complete!',
            text: '{{ session('success') }}',
            timer: 4000,
            timerProgressBar: true,
            showConfirmButton: false,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
                setTimeout(() => {
                    Swal.hideLoading();
                }, 500);
            }
        });
    });
@endif

// Also handle status messages (for other redirect scenarios)
@if(session('status'))
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: '{{ session('status') }}',
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false
        });
    });
@endif
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const pwd = document.querySelector('input[name="password"]');
  const eyeOpen = document.getElementById('eyeOpen');
  const eyeClosed = document.getElementById('eyeClosed');
  if (!pwd || !eyeOpen || !eyeClosed) return;

  function toggleVisibility() {
    const isPassword = pwd.getAttribute('type') === 'password';
    pwd.setAttribute('type', isPassword ? 'text' : 'password');
    if (isPassword) {
      eyeOpen.classList.add('hidden');
      eyeClosed.classList.remove('hidden');
    } else {
      eyeOpen.classList.remove('hidden');
      eyeClosed.classList.add('hidden');
    }
  }

  const toggleElements = [eyeOpen, eyeClosed];
  toggleElements.forEach(el => {
    el.addEventListener('click', toggleVisibility);
    el.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        toggleVisibility();
      }
    });
  });
});
</script>
@endpush