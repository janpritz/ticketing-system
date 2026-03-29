<div id="passwordModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-hidden="true">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-md transition-opacity" data-modal-backdrop></div>
    <div class="relative mx-auto my-0 sm:my-8 w-full h-full sm:h-auto sm:w-[95%] max-w-lg flex items-center">
        <div class="bg-white shadow-2xl w-full h-full sm:h-auto sm:max-h-[95vh] sm:max-w-lg overflow-hidden sm:rounded-2xl flex flex-col">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex items-center justify-between shrink-0">
                <h3 class="text-lg font-semibold text-gray-900">Change Password</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg" aria-label="Close" data-modal-hide="passwordModal">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('staff.profile.password.update') }}">
                @csrf
                <div class="p-6 space-y-4">
                    <!-- Current password removed by request: only new password + confirmation required -->

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2" for="password">New Password</label>
                        <div class="relative">
                            <input id="password" name="password" type="password" required class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 pr-12">
                            <button type="button" id="toggleNewPassword" style="right:0; left:auto;" class="absolute inset-y-0 flex items-center text-gray-500 px-2" aria-label="Show password">
                                <svg id="toggleNewPasswordIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                        </div>
                        @if($errors->has('password'))
                            <p class="mt-2 text-xs text-red-600">{{ $errors->first('password') }}</p>
                        @endif
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2" for="password_confirmation">Confirm Password</label>
                        <div class="relative">
                            <input id="password_confirmation" name="password_confirmation" type="password" required class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 pr-12">
                            <button type="button" id="toggleConfirmPassword" style="right:0; left:auto;" class="absolute inset-y-0 flex items-center text-gray-500 px-2" aria-label="Show confirm password">
                                <svg id="toggleConfirmPasswordIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex items-center justify-end gap-3">
                    <button type="button" data-modal-hide="passwordModal" class="inline-flex items-center justify-center rounded-lg bg-white px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-5 py-2 text-sm font-medium text-white hover:bg-indigo-700">Change Password</button>
                </div>
            </form>
        </div>
    </div>
</div>