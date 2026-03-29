<form method="POST" action="{{ route('staff.profile.password.update') }}" class="space-y-4">
    @csrf
    <div>
        <label for="current_password" class="block text-xs text-gray-600 mb-1">Current Password</label>
        <input type="password" id="current_password" name="current_password" required
            class="w-full rounded-md border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            autocomplete="current-password">
    </div>
    <div>
        <label for="password" class="block text-xs text-gray-600 mb-1">New Password</label>
        <input type="password" id="password" name="password" required minlength="8"
            class="w-full rounded-md border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            autocomplete="new-password">
        <p class="mt-1 text-xs text-gray-500">Minimum 8 characters.</p>
    </div>
    <div>
        <label for="password_confirmation" class="block text-xs text-gray-600 mb-1">Confirm New Password</label>
        <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8"
            class="w-full rounded-md border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            autocomplete="new-password">
    </div>

    <div class="flex items-center justify-between pt-2">
        <a href="{{ route('staff.profile') }}" class="text-sm text-gray-600 hover:text-gray-800">Back to Profile</a>
        <button type="submit"
            class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
            Update Password
        </button>
    </div>
</form>
