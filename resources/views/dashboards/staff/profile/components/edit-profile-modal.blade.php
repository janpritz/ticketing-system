<div id="editProfileModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-hidden="true">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-md transition-opacity" data-modal-backdrop></div>
    <div class="relative mx-auto my-0 sm:my-8 w-full h-full sm:h-auto sm:w-[95%] max-w-2xl flex items-center">
        <div
            class="bg-white shadow-2xl w-full h-full sm:h-auto sm:max-h-[95vh] sm:max-w-2xl overflow-hidden sm:rounded-2xl flex flex-col">
            <!-- Header -->
            <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex items-center justify-between shrink-0">
                <h3 class="text-xl font-semibold text-gray-900">Edit Profile</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg" aria-label="Close"
                    data-modal-hide="editProfileModal">
                    <svg class="h-5 w-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form method="POST" action="{{ route('staff.profile.update') }}" enctype="multipart/form-data">
                @csrf
                <div class="p-6 space-y-6">
                    @if ($errors->any())
                        <div class="mb-4">
                            <div class="text-sm text-red-700 font-medium">Please fix the following errors:</div>
                            <ul class="mt-2 text-sm text-red-600 list-disc list-inside">
                                @foreach ($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2" for="name_modal">Full
                                Name</label>
                            <input type="text" id="name_modal" name="name" value="{{ old('name', $user->name) }}"
                                required
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2" for="photo">Update Profile
                                Photo</label>
                            <input type="file" id="photo" name="photo" accept=".jpg,.jpeg,.png"
                                class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 file:cursor-pointer border border-gray-300 rounded-lg cursor-pointer focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div
                            class="md:col-span-2 flex items-start gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-100">
                            <div class="flex-shrink-0 mt-0.5">
                                <svg class="h-5 w-5 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="space-y-1">
                                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Account
                                    Constraints</p>
                                <p class="text-[13px] leading-relaxed text-slate-500">
                                    Only your <span class="text-slate-900 font-medium">full name</span> and <span
                                        class="text-slate-900 font-medium">profile picture</span> can be modified here.
                                    Account details including <span class="text-slate-700">email</span>, <span
                                        class="text-slate-700">department</span>, and <span
                                        class="text-slate-700">assigned roles</span> are managed by administration.
                                </p>
                                <p class="text-[12px] italic text-slate-400 mt-2">
                                    Need to update your email, role or department? Please coordinate with your system
                                    administrator.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Footer -->
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 shrink-0 flex items-center justify-end gap-3">
                    <button type="button" data-modal-hide="editProfileModal"
                        class="flex inline-flex items-center justify-center rounded-lg bg-white px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50">Cancel</button>
                    <button type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-5 py-2 text-sm font-medium text-white hover:bg-indigo-700">Save
                        Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
