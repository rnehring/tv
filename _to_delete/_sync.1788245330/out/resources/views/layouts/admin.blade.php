<!doctype html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-gray-50 text-gray-900 dark:bg-gray-900 dark:text-gray-100">
@php
    $nav = [
        ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => '🏠', 'active' => request()->routeIs('admin.dashboard')],
        ['route' => 'admin.slides.index', 'label' => 'Static slides', 'icon' => '🖼️', 'active' => request()->routeIs('admin.slides.*')],
        ['route' => 'admin.monthly.index', 'label' => 'Monthly slides', 'icon' => '🎂', 'active' => request()->routeIs('admin.monthly.*')],
        ['route' => 'admin.gas.index', 'label' => 'Gas cards', 'icon' => '⛽', 'active' => request()->routeIs('admin.gas.*')],
        // OTIF backlog hidden from the nav for now (route still works directly).
        // ['route' => 'admin.otif.index', 'label' => 'OTIF backlog', 'icon' => '📦', 'active' => request()->routeIs('admin.otif.*')],
        ['route' => 'admin.locations.index', 'label' => 'Locations', 'icon' => '📍', 'active' => request()->routeIs('admin.locations.*')],
    ];
@endphp

<div class="flex h-full">
    <!-- Sidebar -->
    <aside class="hidden w-64 shrink-0 border-r border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800 md:flex md:flex-col">
        <div class="flex items-center gap-2 px-6 py-5">
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-600 text-lg">📺</span>
            <span class="text-lg font-bold">{{ config('app.name') }}</span>
        </div>
        <nav class="flex-1 space-y-1 px-3">
            @foreach ($nav as $item)
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium
                          {{ $item['active']
                              ? 'bg-blue-50 text-blue-700 dark:bg-gray-700 dark:text-white'
                              : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                    <span>{{ $item['icon'] }}</span>{{ $item['label'] }}
                </a>
            @endforeach
        </nav>
        <div class="border-t border-gray-200 p-3 dark:border-gray-700">
            <a href="{{ route('slideshow') }}" target="_blank"
               class="mb-2 flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
                <span>▶️</span>Open slideshow
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
                    <span>🚪</span>Sign out ({{ auth()->user()->username }})
                </button>
            </form>
        </div>
    </aside>

    <!-- Main -->
    <div class="flex min-w-0 flex-1 flex-col">
        <header class="flex items-center justify-between border-b border-gray-200 bg-white px-6 py-4 dark:border-gray-700 dark:bg-gray-800">
            <h1 class="text-xl font-semibold">@yield('title', 'Admin')</h1>
            <div class="text-sm text-gray-500 dark:text-gray-400">{{ now()->format('l, F j, Y') }}</div>
        </header>

        <main class="flex-1 overflow-y-auto p-6">
            @if (session('status'))
                <div class="mb-5 flex items-center gap-2 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800 dark:border-green-800 dark:bg-gray-800 dark:text-green-400">
                    <span>✅</span>{{ session('status') }}
                </div>
            @endif
            @if (session('import_errors'))
                <div class="mb-5 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-800 dark:bg-gray-800 dark:text-amber-400">
                    <p class="mb-1 font-semibold">Some rows were skipped:</p>
                    <ul class="list-inside list-disc">
                        @foreach (session('import_errors') as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-5 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-800 dark:bg-gray-800 dark:text-red-400">
                    <ul class="list-inside list-disc">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
