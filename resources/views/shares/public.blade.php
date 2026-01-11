<!DOCTYPE html>
<html lang="ja" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $shareLink->name }} - {{ $user->name }} | Shadova Log</title>
    <meta name="description" content="{{ $user->name }}の戦績: {{ $stats['wins'] }}勝{{ $stats['losses'] }}敗 ({{ $stats['winRate'] }}%)">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-900 text-white">
    <!-- Header -->
    <header class="border-b border-gray-700 bg-gray-800/50">
        <div class="max-w-4xl mx-auto px-4 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <div class="h-12 w-12 rounded-full bg-purple-600/30 flex items-center justify-center">
                            <span class="text-xl font-bold text-purple-400">{{ mb_substr($user->name, 0, 1) }}</span>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-white">{{ $shareLink->name }}</h1>
                            <p class="text-sm text-gray-400">by {{ $user->name }} (@{{ $user->username }})</p>
                        </div>
                    </div>
                    <p class="text-sm text-gray-500">
                        {{ $shareLink->start_date->format('Y/m/d') }} 〜 {{ $shareLink->end_date->format('Y/m/d') }}
                    </p>
                </div>
                <a href="{{ url('/') }}" class="text-sm text-purple-400 hover:text-purple-300">
                    Shadova Log
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 py-8">
        <!-- Summary Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="rounded-xl bg-gray-800 border border-gray-700 p-4 text-center">
                <div class="text-3xl font-bold text-white">{{ $stats['total'] }}</div>
                <div class="text-sm text-gray-400">総対戦数</div>
            </div>
            <div class="rounded-xl bg-gray-800 border border-gray-700 p-4 text-center">
                <div class="text-3xl font-bold text-green-400">{{ $stats['wins'] }}</div>
                <div class="text-sm text-gray-400">勝利</div>
            </div>
            <div class="rounded-xl bg-gray-800 border border-gray-700 p-4 text-center">
                <div class="text-3xl font-bold text-red-400">{{ $stats['losses'] }}</div>
                <div class="text-sm text-gray-400">敗北</div>
            </div>
            <div class="rounded-xl bg-gradient-to-br from-purple-900/40 to-gray-800 border border-purple-500/30 p-4 text-center">
                <div class="text-3xl font-bold text-purple-400">{{ $stats['winRate'] }}%</div>
                <div class="text-sm text-gray-400">勝率</div>
            </div>
        </div>

        <!-- Turn Stats -->
        <div class="grid grid-cols-2 gap-4 mb-8">
            <div class="rounded-xl bg-gray-800 border border-gray-700 p-4">
                <div class="flex items-center gap-2 mb-2">
                    <span class="h-3 w-3 rounded-full bg-blue-400"></span>
                    <span class="text-sm font-medium text-gray-400">先攻</span>
                </div>
                <div class="text-2xl font-bold text-white">{{ $stats['byTurn']['first']['winRate'] }}%</div>
                <div class="text-sm text-gray-500">{{ $stats['byTurn']['first']['wins'] }}勝 / {{ $stats['byTurn']['first']['total'] }}戦</div>
            </div>
            <div class="rounded-xl bg-gray-800 border border-gray-700 p-4">
                <div class="flex items-center gap-2 mb-2">
                    <span class="h-3 w-3 rounded-full bg-orange-400"></span>
                    <span class="text-sm font-medium text-gray-400">後攻</span>
                </div>
                <div class="text-2xl font-bold text-white">{{ $stats['byTurn']['second']['winRate'] }}%</div>
                <div class="text-sm text-gray-500">{{ $stats['byTurn']['second']['wins'] }}勝 / {{ $stats['byTurn']['second']['total'] }}戦</div>
            </div>
        </div>

        <!-- Class Stats -->
        @if($stats['byClass']->isNotEmpty())
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-white mb-4">クラス別成績</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                @foreach($stats['byClass'] as $classId => $classStat)
                @php $class = $leaderClasses[$classId] ?? null; @endphp
                @if($class)
                <div class="rounded-lg bg-gray-800 border border-gray-700 p-3">
                    <div class="text-sm font-medium text-gray-300 mb-1">vs {{ $class->name }}</div>
                    <div class="text-xl font-bold text-white">{{ $classStat['winRate'] }}%</div>
                    <div class="text-xs text-gray-500">{{ $classStat['wins'] }}勝 / {{ $classStat['total'] }}戦</div>
                </div>
                @endif
                @endforeach
            </div>
        </div>
        @endif

        <!-- Deck Stats -->
        @if($deckStats->isNotEmpty())
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-white mb-4">使用デッキ</h2>
            <div class="space-y-2">
                @foreach($deckStats as $deckStat)
                <div class="rounded-lg bg-gray-800 border border-gray-700 p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-lg bg-purple-600/20 flex items-center justify-center">
                                <span class="text-purple-400 font-bold text-sm">{{ mb_substr($deckStat['deck']->leaderClass->name ?? '?', 0, 1) }}</span>
                            </div>
                            <div>
                                <h3 class="font-medium text-white">{{ $deckStat['deck']->name }}</h3>
                                <p class="text-xs text-gray-400">{{ $deckStat['deck']->leaderClass->name ?? 'Unknown' }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-bold text-purple-400">{{ $deckStat['winRate'] }}%</div>
                            <div class="text-xs text-gray-500">{{ $deckStat['wins'] }}勝 / {{ $deckStat['total'] }}戦</div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Battle History -->
        <div>
            <h2 class="text-lg font-semibold text-white mb-4">対戦履歴</h2>
            <div class="space-y-2">
                @forelse($battles as $battle)
                <div class="rounded-lg bg-gray-800 border border-gray-700 p-4">
                    <div class="flex items-center gap-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg {{ $battle->result ? 'bg-green-500/10 border border-green-500/20' : 'bg-red-500/10 border border-red-500/20' }}">
                            @if($battle->result)
                            <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            @else
                            <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-medium text-white">{{ $battle->deck->name }}</span>
                                <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                                <span class="text-gray-300">{{ $battle->opponentClass->name }}</span>
                            </div>
                            <div class="flex items-center gap-3 mt-1 text-sm text-gray-400">
                                <span class="inline-flex items-center gap-1">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $battle->is_first ? 'bg-blue-400' : 'bg-orange-400' }}"></span>
                                    {{ $battle->is_first ? '先攻' : '後攻' }}
                                </span>
                                <span class="px-1.5 py-0.5 rounded bg-purple-600/30 text-purple-400 text-xs">{{ $battle->gameMode->name }}</span>
                                <span>{{ $battle->played_at->format('m/d H:i') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-12 text-gray-500">
                    対戦記録がありません
                </div>
                @endforelse
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="border-t border-gray-700 bg-gray-800/50 mt-12">
        <div class="max-w-4xl mx-auto px-4 py-6 text-center text-sm text-gray-500">
            <p>Powered by <a href="{{ url('/') }}" class="text-purple-400 hover:text-purple-300">Shadova Log</a></p>
        </div>
    </footer>
</body>
</html>
