<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Overview') · Dormwise</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar">
            <a class="brand" href="{{ route('dormitory.dashboard') }}">
                <span class="brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-5h6v5M9 9h.01M15 9h.01M9 12h.01M15 12h.01"/>
                    </svg>
                </span>
                <span>
                    <strong>Dormwise</strong>
                    <small>BED SPACE MANAGEMENT</small>
                </span>
            </a>

            <p class="nav-label">WORKSPACE</p>
            <nav class="side-nav" aria-label="Main navigation">
                <a class="nav-link {{ request()->routeIs('dormitory.dashboard') ? 'is-active' : '' }}" href="{{ route('dormitory.dashboard') }}">
                    <span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="8" height="8" rx="1.5"/><rect x="13" y="3" width="8" height="5" rx="1.5"/><rect x="13" y="10" width="8" height="11" rx="1.5"/><rect x="3" y="13" width="8" height="8" rx="1.5"/></svg></span> Overview
                </a>
                <a class="nav-link {{ request()->routeIs('dormitory.students.*') ? 'is-active' : '' }}" href="{{ route('dormitory.students.index') }}">
                    <span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="4"/><path d="M3 21v-2a6 6 0 0 1 12 0v2M16 4.5a4 4 0 0 1 0 7.5M18 15a5 5 0 0 1 3 4.5V21"/></svg></span> Students
                </a>
                <a class="nav-link {{ request()->routeIs('dormitory.buildings.*') ? 'is-active' : '' }}" href="{{ route('dormitory.buildings.index') }}">
                    <span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-5h6v5M9 9h.01M15 9h.01M9 12h.01M15 12h.01"/></svg></span> Buildings
                </a>
                <a class="nav-link {{ request()->routeIs('dormitory.rooms.*') ? 'is-active' : '' }}" href="{{ route('dormitory.rooms.index') }}">
                    <span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 3v18"/></svg></span> Rooms
                </a>
                <a class="nav-link {{ request()->routeIs('dormitory.bed-slots.*') ? 'is-active' : '' }}" href="{{ route('dormitory.bed-slots.index') }}">
                    <span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v6M3 16h18M5 10V7a2 2 0 0 1 2-2h3v5M3 20v-2M21 20v-2"/></svg></span> Bed slots
                </a>
                <a class="nav-link {{ request()->routeIs('dormitory.contracts.*') ? 'is-active' : '' }}" href="{{ route('dormitory.contracts.index') }}">
                    <span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M8 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-3M8 3v5h5M8 3l5 5M12 13h8M12 17h8M16 9l2 2 3-3"/></svg></span> Contracts
                </a>
            </nav>

            <div class="sidebar-note">
                <span class="note-dot"></span>
                <div>
                    <strong>Housing office</strong>
                    <small>Manage your dormitory</small>
                </div>
            </div>
        </aside>

        <main class="main-panel">
            <header class="topbar">
                <div>
                    <span class="eyebrow">UNIVERSITY HOUSING</span>
                    <h1>@yield('heading', 'Overview')</h1>
                </div>
                <div class="topbar-actions">
                    <div class="topbar-date">{{ now()->format('l, F j, Y') }}</div>
                    <span class="user-name">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="button button-quiet" type="submit">Log out</button>
                    </form>
                </div>
            </header>

            <section class="page-content">
                @if (session('success'))
                    <div class="alert alert-success" role="status">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-error" role="alert">{{ session('error') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-error" role="alert">
                        <strong>Please check the form:</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </section>
        </main>
    </div>
</body>
</html>
