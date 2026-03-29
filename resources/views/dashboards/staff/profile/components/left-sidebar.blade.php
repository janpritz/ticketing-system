<div class="lg:col-span-4 space-y-6">
    <!-- Profile Photo Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="bg-gradient-to-br from-indigo-500 to-purple-600 h-24"></div>
        <div class="px-6 pb-6">
            <div class="flex flex-col items-center -mt-12">
                @php
                    $ver = optional($user->updated_at)->timestamp;
                    $photo = $user->profile_photo ? asset('storage/' . $user->profile_photo) . '?v=' . $ver : null;
                @endphp
                <img id="photoPreview" class="h-24 w-24 rounded-full object-cover ring-4 ring-white shadow-lg"
                    src="{{ $photo ?: 'https://ui-avatars.com/api/?background=E5E7EB&color=111827&name=' . urlencode($user->name) }}"
                    alt="Profile Photo">
                <h2 class="mt-4 text-xl font-semibold text-gray-900">{{ $user->name }}</h2>
                <p class="text-sm text-gray-500">{{ $user->role }}</p>
                <div
                    class="mt-3 inline-flex items-center gap-2 rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    {{ is_object($user->category) ? $user->category->name ?? 'General' : ($user->category ?: 'General') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-sm font-semibold text-gray-900 mb-4 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            Performance Overview
        </h3>
        <div class="space-y-4">
            <div class="flex items-center justify-between p-3 rounded-lg bg-blue-50 border border-blue-100">
                <div>
                    <p class="text-xs font-medium text-blue-900">Assigned</p>
                    <p class="text-2xl font-bold text-blue-700 mt-1">{{ (int) $assignedCount }}</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
            </div>
            <div class="flex items-center justify-between p-3 rounded-lg bg-emerald-50 border border-emerald-100">
                <div>
                    <p class="text-xs font-medium text-emerald-900">Closed</p>
                    <p class="text-2xl font-bold text-emerald-700 mt-1">{{ (int) $resolvedCount }}</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-emerald-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-emerald-600" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="flex items-center justify-between p-3 rounded-lg bg-purple-50 border border-purple-100">
                <div>
                    <p class="text-xs font-medium text-purple-900">Success Rate</p>
                    @php
                        $rate = $assignedCount > 0 ? round(($resolvedCount / max(1, $assignedCount)) * 100) : 0;
                    @endphp
                    <p class="text-2xl font-bold text-purple-700 mt-1">{{ $rate }}%</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-purple-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
            </div>
        </div>
    </div>
</div>
