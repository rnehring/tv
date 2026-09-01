<!doctype html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-gray-50 dark:bg-gray-900">
<div class="flex min-h-full items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
        <div class="mb-6 text-center">
            <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-blue-600 text-2xl">📺</div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ config('app.name') }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Slideshow Admin</p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-8 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            @if ($errors->any())
                <div class="mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-700 dark:bg-gray-700 dark:text-red-400">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf
                <div>
                    <label for="username" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Username</label>
                    <input type="text" name="username" id="username" value="{{ old('username') }}" required autofocus
                           class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                           placeholder="rnehring">
                </div>
                <div>
                    <label for="password" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Password</label>
                    <input type="password" name="password" id="password" required
                           class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                           placeholder="••••••••">
                </div>
                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox"
                           class="h-4 w-4 rounded border-gray-300 bg-gray-50 text-blue-600 focus:ring-blue-500">
                    <label for="remember" class="ms-2 text-sm text-gray-600 dark:text-gray-400">Keep me signed in</label>
                </div>
                <button type="submit"
                        class="w-full rounded-lg bg-blue-600 px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-blue-700 focus:ring-4 focus:ring-blue-300">
                    Sign In
                </button>
            </form>
        </div>
        <p class="mt-6 text-center text-xs text-gray-400">
            <a href="{{ route('slideshow') }}" class="hover:underline">View the Live Slideshow →</a>
        </p>
    </div>
</div>
</body>
</html>
