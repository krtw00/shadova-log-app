<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'Shadova Log') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Instrument Sans', sans-serif; }
        .glass-card {
            background: rgba(31, 41, 55, 0.8);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(128, 128, 128, 0.2);
        }
    </style>
</head>
<body class="min-h-screen bg-gray-900 text-gray-100 flex items-center justify-center">
    <div class="w-full max-w-md px-4">
        <!-- Logo -->
        <div class="flex justify-center mb-8">
            <a href="/" class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-600">
                    <svg class="h-7 w-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <span class="text-2xl font-bold text-purple-400">Shadova Log</span>
            </a>
        </div>

        <!-- Content -->
        {{ $slot }}
    </div>

    @if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
         class="fixed bottom-4 right-4 bg-green-600 text-white px-4 py-2 rounded-lg shadow-lg">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
         class="fixed bottom-4 right-4 bg-red-600 text-white px-4 py-2 rounded-lg shadow-lg">
        {{ session('error') }}
    </div>
    @endif
</body>
</html>
