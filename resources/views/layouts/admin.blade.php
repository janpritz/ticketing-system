@extends('layouts.app')

@section('title', trim($__env->yieldContent('title', 'Admin')))

@section('content')
    <!-- Admin Shell: Sidebar + Header -->
    <aside id="default-sidebar"
        class="fixed top-0 left-0 z-40 w-70 h-screen transition-transform -translate-x-full sm:translate-x-0"
        aria-label="Sidebar">
        <div class="h-full px-3 py-4 overflow-y-auto bg-gray-50">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 p-2 rounded-md hover:bg-gray-100">
                <img src="{{ asset('logo.png') }}" alt="Logo" class="w-8 h-8">
                <span class="text-sm font-semibold text-gray-900">Sangkay Ticketing System</span>
            </a>
            <div class="h-px bg-gray-200 my-3"></div>
            <ul class="space-y-2 font-medium">
                <li>
                    <a href="{{ route('admin.dashboard') }}"
                        class="flex items-center p-2 rounded-lg hover:bg-gray-100 group {{ request()->routeIs('admin.dashboard') ? 'bg-gray-100 text-gray-900' : 'text-gray-900' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs('admin.dashboard') ? 'text-gray-900' : 'text-gray-500 group-hover:text-gray-900' }}"
                            aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 22 21">
                            <path
                                d="M16.975 11H10V4.025a1 1 0 0 0-1.066-.998 8.5 8.5 0 1 0 9.039 9.039.999.999 0 0 0-1-1.066h.002Z" />
                            <path
                                d="M12.5 0c-.157 0-.311.01-.565.027A1 1 0 0 0 11 1.02V10h8.975a1 1 0 0 0 1-.935c.013-.188.028-.374.028-.565A8.51 8.51 0 0 0 12.5 0Z" />
                        </svg>
                        <span class="ms-3">Dashboard</span>
                    </a>
                </li>
                <li>
                    <button type="button" id="userManagementDropdown"
                        class="w-full flex items-center justify-between p-2 rounded-lg hover:bg-gray-100 group {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.roles.*') || request()->routeIs('admin.categories.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-900' }}"
                        aria-expanded="false">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.roles.*') || request()->routeIs('admin.categories.*') ? 'text-gray-900' : 'text-gray-500 group-hover:text-gray-900' }}"
                                aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                viewBox="0 0 18 18">
                                <path
                                    d="M6.143 0H1.857A1.857 1.857 0 0 0 0 1.857v4.286C0 7.169.831 8 1.857 8h4.286A1.857 1.857 0 0 0 8 6.143V1.857A1.857 1.857 0 0 0 6.143 0Zm10 0h-4.286A1.857 1.857 0 0 0 10 1.857v4.286C10 7.169 10.831 8 11.857 8h4.286A1.857 1.857 0 0 0 18 6.143V1.857A1.857 1.857 0 0 0 16.143 0Zm-10 10H1.857A1.857 1.857 0 0 0 0 11.857v4.286C0 17.169.831 18 1.857 18h4.286A1.857 1.857 0 0 0 8 16.143v-4.286A1.857 1.857 0 0 0 6.143 10Zm10 0h-4.286A1.857 1.857 0 0 0 10 11.857v4.286c0 1.026.831 1.857 1.857 1.857h4.286A1.857 1.857 0 0 0 18 16.143v-4.286A1.857 1.857 0 0 0 16.143 10Z" />
                            </svg>
                            <span class="ms-3">User Management</span>
                        </div>
                        <svg id="userManagementChevron"
                            class="w-4 h-4 transition-transform duration-200 {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.roles.*') || request()->routeIs('admin.categories.*') ? 'text-gray-900' : 'text-gray-500 group-hover:text-gray-900' }}"
                            aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M7 10l5 5 5-5z" />
                        </svg>
                    </button>
                    <div id="userManagementMenu" class="hidden pl-4 mt-1 space-y-1">
                        <a href="{{ route('admin.users.index') }}"
                            class="flex items-center p-2 text-sm rounded-lg hover:bg-gray-100 group {{ request()->routeIs('admin.users.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-700' }}">
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.users.*') ? 'text-gray-900' : 'text-gray-500 group-hover:text-gray-900' }}"
                                aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                viewBox="0 0 18 18">
                                <path
                                    d="M6.143 0H1.857A1.857 1.857 0 0 0 0 1.857v4.286C0 7.169.831 8 1.857 8h4.286A1.857 1.857 0 0 0 8 6.143V1.857A1.857 1.857 0 0 0 6.143 0Zm10 0h-4.286A1.857 1.857 0 0 0 10 1.857v4.286C10 7.169 10.831 8 11.857 8h4.286A1.857 1.857 0 0 0 18 6.143V1.857A1.857 1.857 0 0 0 16.143 0Zm-10 10H1.857A1.857 1.857 0 0 0 0 11.857v4.286C0 17.169.831 18 1.857 18h4.286A1.857 1.857 0 0 0 8 16.143v-4.286A1.857 1.857 0 0 0 6.143 10Zm10 0h-4.286A1.857 1.857 0 0 0 10 11.857v4.286c0 1.026.831 1.857 1.857 1.857h4.286A1.857 1.857 0 0 0 18 16.143v-4.286A1.857 1.857 0 0 0 16.143 10Z" />
                            </svg>
                            <span class="ms-3">Users</span>
                        </a>
                        <a href="{{ route('admin.departments.index') }}"
                            class="flex items-center p-2 text-sm rounded-lg hover:bg-gray-100 group {{ request()->routeIs('admin.departments.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-700' }}">
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.departments.*') ? 'text-gray-900' : 'text-gray-500 group-hover:text-gray-900' }}"
                                aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="ms-3">Departments</span>
                        </a>
                        <a href="{{ route('admin.roles.index') }}"
                            class="flex items-center p-2 text-sm rounded-lg hover:bg-gray-100 group {{ request()->routeIs('admin.roles.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-700' }}">
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.roles.*') ? 'text-gray-900' : 'text-gray-500 group-hover:text-gray-900' }}"
                                aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path
                                    d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                            <span class="ms-3">Roles</span>
                        </a>
                    </div>
                </li>
                <li>
                    <a href="{{ route('admin.tickets.index') }}"
                        class="flex items-center p-2 rounded-lg hover:bg-gray-100 group {{ request()->routeIs('admin.tickets.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-900' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs('admin.tickets.*') ? 'text-gray-900' : 'text-gray-500 group-hover:text-gray-900' }}"
                            aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 5h16v2H2V5zm0 4h16v2H2V9zm0 4h12v2H2v-2z" />
                        </svg>
                        <span class="ms-3">Ticket Management</span>
                    </a>
                </li>
                <li>
                    <button type="button" id="faqManagementDropdown"
                        class="w-full flex items-center justify-between p-2 rounded-lg hover:bg-gray-100 group {{ request()->routeIs('admin.knowledgebase.*') || request()->routeIs('admin.announcements.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-900' }}"
                        aria-expanded="false">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 {{ request()->routeIs('admin.knowledgebase.*') || request()->routeIs('admin.announcements.*') ? 'text-gray-900' : 'text-gray-500 group-hover:text-gray-900' }}"
                                aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path
                                    d="m17.418 3.623-.018-.008a6.713 6.713 0 0 0-2.4-.569V2h1a1 1 0 1 0 0-2h-2a1 1 0 0 0-1 1v2H9.89A6.977 6.977 0 0 1 12 8v5h-2V8A5 5 0 1 0 0 8v6a1 1 0 0 0 1 1h8v4a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1v-4h6a1 1 0 0 0 1-1V8a5 5 0 0 0-2.582-4.377Z" />
                            </svg>
                            <span class="ms-3">Knowledgebase</span>
                        </div>
                        <svg id="faqManagementChevron"
                            class="w-4 h-4 transition-transform duration-200 {{ request()->routeIs('admin.knowledgebase.*') || request()->routeIs('admin.announcements.*') ? 'text-gray-900' : 'text-gray-500 group-hover:text-gray-900' }}"
                            aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M7 10l5 5 5-5z" />
                        </svg>
                    </button>
                    <div id="faqManagementMenu" class="hidden pl-4 mt-1 space-y-1">
                        <a href="{{ route('admin.knowledgebase.index') }}"
                            class="flex items-center p-2 text-sm rounded-lg hover:bg-gray-100 group {{ request()->routeIs('admin.knowledgebase.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-700' }}">
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.knowledgebase.*') ? 'text-gray-900' : 'text-gray-500 group-hover:text-gray-900' }}"
                                aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path
                                    d="m17.418 3.623-.018-.008a6.713 6.713 0 0 0-2.4-.569V2h1a1 1 0 1 0 0-2h-2a1 1 0 0 0-1 1v2H9.89A6.977 6.977 0 0 1 12 8v5h-2V8A5 5 0 1 0 0 8v6a1 1 0 0 0 1 1h8v4a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1v-4h6a1 1 0 0 0 1-1V8a5 5 0 0 0-2.582-4.377Z" />
                            </svg>
                            <span class="ms-3">Document Management</span>
                        </a>
                        <a href="{{ route('admin.announcements.index') }}"
                            class="flex items-center p-2 text-sm rounded-lg hover:bg-gray-100 group {{ request()->routeIs('admin.announcements.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-700' }}">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="w-4 h-4 {{ request()->routeIs('admin.announcements.*') ? 'text-gray-900' : 'text-gray-500 group-hover:text-gray-900' }}"
                                fill="currentColor" viewBox="0 0 16 16">
                                <path
                                    d="M13 2.5a1.5 1.5 0 0 1 3 0v11a1.5 1.5 0 0 1-3 0zm-1 .724c-2.067.95-4.539 1.481-7 1.656v6.237a25 25 0 0 1 1.088.085c2.053.204 4.038.668 5.912 1.56zm-8 7.841V4.934c-.68.027-1.399.043-2.008.053A2.02 2.02 0 0 0 0 7v2c0 1.106.896 1.996 1.994 2.009l.496.008a64 64 0 0 1 1.51.048m1.39 1.081q.428.032.85.078l.253 1.69a1 1 0 0 1-.983 1.187h-.548a1 1 0 0 1-.916-.599l-1.314-2.48a66 66 0 0 1 1.692.064q.491.026.966.06" />
                            </svg>
                            <span class="ms-3">Announcements</span>
                        </a>
                        <a href="{{ route('admin.logs') }}"
                            class="flex items-center p-2 text-sm rounded-lg hover:bg-gray-100 group {{ request()->routeIs('admin.logs') ? 'bg-gray-100 text-gray-900' : 'text-gray-700' }}">
                            <svg class="w-4 h-4 {{ request()->routeIs('admin.logs') ? 'text-gray-900' : 'text-gray-500 group-hover:text-gray-900' }}"
                                aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span class="ms-3">Logs</span>
                        </a>
                    </div>
                </li>
                <li>
                    <a href="{{ route('admin.faqs.index') }}"
                        class="flex items-center p-2 rounded-lg hover:bg-gray-100 group {{ request()->routeIs('admin.faqs.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-900' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs('admin.faqs.*') ? 'text-gray-900' : 'text-gray-500 group-hover:text-gray-900' }}"
                            aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path
                                d="M9 2a1 1 0 00-1 1v8a1 1 0 001 1h6a1 1 0 001-1V6.414A2 2 0 0016.414 5L11 5 9.707 3.707A1 1 0 009 2zM2 6a1 1 0 00-1 1v8a1 1 0 001 1h6a1 1 0 001-1V7a1 1 0 00-1-1H2z" />
                        </svg>
                        <span class="ms-3">FAQ Management</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.rasa-server.index') }}"
                        class="flex items-center p-2 rounded-lg hover:bg-gray-100 group {{ request()->routeIs('admin.rasa-server.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-900' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs('admin.rasa-server.*') ? 'text-gray-900' : 'text-gray-500 group-hover:text-gray-900' }}"
                            aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z"
                                clip-rule="evenodd" />
                        </svg>
                        <span class="ms-3">Rasa Server Manager</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.reports.index') }}"
                        class="flex items-center p-2 rounded-lg hover:bg-gray-100 group {{ request()->routeIs('admin.reports.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-900' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs('admin.reports.*') ? 'text-gray-900' : 'text-gray-500 group-hover:text-gray-900' }}"
                            aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path
                                d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" />
                        </svg>
                        <span class="ms-3">Reports</span>
                    </a>
                </li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="w-full flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group">
                            <svg class="w-5 h-5 text-gray-500 transition duration-75 group-hover:text-gray-900"
                                aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 18 16">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M1 8h11m0 0L8 4m4 4-4 4m4-11h3a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-3" />
                            </svg>
                            <span class="ms-3">Logout</span>
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </aside>
    <div id="sidebar-backdrop" class="fixed inset-0 z-30 bg-black/40 hidden"></div>
    <div id="content-wrapper" class="px-5 sm:ml-64 transition-all duration-300">
        <!-- Top Bar -->
        <header class="bg-white border border-gray-200 rounded-md">
            <div class="px-4 sm:px-6 lg:px-8 h-12 flex items-center justify-between gap-4">
                <div class="flex-1 max-w-xl flex items-center gap-2">
                    <button id="sidebar-toggle" aria-controls="default-sidebar" type="button"
                        class="inline-flex items-center p-2 text-sm text-gray-500 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200"
                        title="Toggle sidebar">
                        <span class="sr-only">Toggle sidebar</span>
                        <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20"
                            xmlns="http://www.w3.org/2000/svg">
                            <path clip-rule="evenodd" fill-rule="evenodd"
                                d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z">
                            </path>
                        </svg>
                    </button>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-right">
                        <div class="text-xs text-slate-500">Welcome back,</div>
                        <div class="text-sm font-medium text-slate-900">{{ auth()->user()->name ?? 'Admin User' }}</div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page-specific content -->
        <main class="mt-4 space-y-4 mb-10">
            @yield('admin-content')
        </main>
    </div>
    <!-- Sidebar & Dropdowns Script -->
    @push('scripts')
        @include('layouts.scripts.scripts')
    @endpush
@endsection
