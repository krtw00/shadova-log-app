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
        .scrollbar-thin::-webkit-scrollbar { width: 6px; }
        .scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
        .scrollbar-thin::-webkit-scrollbar-thumb { background: #374151; border-radius: 3px; }
        .glass-card {
            background: rgba(31, 41, 55, 0.8);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(128, 128, 128, 0.2);
        }
    </style>
</head>
<body class="h-screen overflow-hidden bg-gray-900 text-gray-100"
      x-data="{
          sidebarExpanded: false,
          showSettings: false
      }">
    <div class="flex h-screen">
        <!-- Left Collapsible Sidebar -->
        <aside
            class="flex-shrink-0 flex flex-col bg-gray-800 border-r border-gray-700 transition-all duration-300"
            :class="sidebarExpanded ? 'w-52' : 'w-16'"
        >
            <!-- Logo & Toggle -->
            <div class="flex h-14 items-center border-b border-gray-700 px-3" :class="sidebarExpanded ? 'justify-between' : 'justify-center'">
                <a href="/" class="flex items-center gap-2 overflow-hidden">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-purple-600">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <span x-show="sidebarExpanded" x-transition class="text-lg font-bold text-purple-400 whitespace-nowrap">Shadova</span>
                </a>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 mt-4 px-2 space-y-1">
                <!-- 対戦記録 -->
                <a href="{{ route('battles.index') }}"
                    class="w-full flex items-center gap-3 rounded-lg px-3 py-3 transition-colors {{ request()->routeIs('battles.*') ? 'bg-purple-600/20 text-purple-400' : 'text-gray-400 hover:bg-gray-700 hover:text-white' }}"
                    :class="sidebarExpanded ? 'justify-start' : 'justify-center'"
                >
                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.5 2L20 7.5M20 7.5L8 19.5L2 22L4.5 16L16.5 4M20 7.5L16.5 4M8 19.5L4.5 16"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2 2L7 7"/>
                    </svg>
                    <span x-show="sidebarExpanded" x-transition class="whitespace-nowrap text-sm font-medium">対戦記録</span>
                </a>

                <!-- デッキ管理 -->
                <a href="{{ route('decks.index') }}"
                    class="w-full flex items-center gap-3 rounded-lg px-3 py-3 transition-colors {{ request()->routeIs('decks.*') ? 'bg-purple-600/20 text-purple-400' : 'text-gray-400 hover:bg-gray-700 hover:text-white' }}"
                    :class="sidebarExpanded ? 'justify-start' : 'justify-center'"
                >
                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="4" y="4" width="12" height="16" rx="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 2h10a2 2 0 012 2v14"/>
                    </svg>
                    <span x-show="sidebarExpanded" x-transition class="whitespace-nowrap text-sm font-medium">デッキ管理</span>
                </a>

                <!-- 統計・分析 -->
                <a href="{{ route('statistics.index') }}"
                    class="w-full flex items-center gap-3 rounded-lg px-3 py-3 transition-colors {{ request()->routeIs('statistics.*') ? 'bg-purple-600/20 text-purple-400' : 'text-gray-400 hover:bg-gray-700 hover:text-white' }}"
                    :class="sidebarExpanded ? 'justify-start' : 'justify-center'"
                >
                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="12" width="4" height="9" rx="1" stroke-linecap="round" stroke-linejoin="round"/>
                        <rect x="10" y="8" width="4" height="13" rx="1" stroke-linecap="round" stroke-linejoin="round"/>
                        <rect x="17" y="3" width="4" height="18" rx="1" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span x-show="sidebarExpanded" x-transition class="whitespace-nowrap text-sm font-medium">統計・分析</span>
                </a>
            </nav>

            <!-- Bottom Section -->
            <div class="border-t border-gray-700 p-2 space-y-1">
                <!-- 設定 -->
                <button
                    @click="showSettings = !showSettings"
                    class="w-full flex items-center gap-3 rounded-lg px-3 py-2 text-gray-400 hover:bg-gray-700 hover:text-white transition-colors"
                    :class="sidebarExpanded ? 'justify-start' : 'justify-center'"
                >
                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="3"/>
                        <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
                    </svg>
                    <span x-show="sidebarExpanded" x-transition class="whitespace-nowrap text-sm">設定</span>
                </button>

                <!-- 折りたたみトグル -->
                <button
                    @click="sidebarExpanded = !sidebarExpanded"
                    class="w-full flex items-center gap-3 rounded-lg px-3 py-2 text-gray-400 hover:bg-gray-700 hover:text-white transition-colors"
                    :class="sidebarExpanded ? 'justify-start' : 'justify-center'"
                >
                    <svg
                        class="h-5 w-5 shrink-0 transition-transform duration-300"
                        :class="sidebarExpanded ? '' : 'rotate-180'"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                    </svg>
                    <span x-show="sidebarExpanded" x-transition class="whitespace-nowrap text-sm">折りたたむ</span>
                </button>

                <!-- ユーザーアイコン -->
                @auth
                <div class="relative" x-data="{ showUserMenu: false }">
                    <button
                        @click="showUserMenu = !showUserMenu"
                        class="w-full flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-gray-700 transition-colors"
                        :class="sidebarExpanded ? 'justify-start' : 'justify-center'"
                    >
                        <div class="h-8 w-8 shrink-0 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center">
                            <span class="text-xs text-white font-medium">{{ mb_substr(Auth::user()->name, 0, 1) }}</span>
                        </div>
                        <span x-show="sidebarExpanded" x-transition class="whitespace-nowrap text-sm text-gray-300">{{ Auth::user()->name }}</span>
                    </button>
                    <!-- ユーザーメニュー -->
                    <div x-show="showUserMenu" @click.away="showUserMenu = false"
                         x-transition
                         class="absolute bottom-full left-0 mb-2 w-48 rounded-lg bg-gray-700 border border-gray-600 shadow-lg py-1"
                         style="display: none;">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2 text-left text-sm text-gray-300 hover:bg-gray-600 hover:text-white">
                                ログアウト
                            </button>
                        </form>
                    </div>
                </div>
                @else
                <a href="{{ route('login') }}"
                    class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-gray-700 transition-colors"
                    :class="sidebarExpanded ? 'justify-start' : 'justify-center'"
                >
                    <div class="h-8 w-8 shrink-0 rounded-full bg-gray-600 flex items-center justify-center">
                        <span class="text-xs text-gray-400 font-medium">G</span>
                    </div>
                    <span x-show="sidebarExpanded" x-transition class="whitespace-nowrap text-sm text-gray-400">ログイン</span>
                </a>
                @endauth
            </div>
        </aside>

        <!-- Main Content Area -->
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
