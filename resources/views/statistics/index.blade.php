<x-layouts.app>
    <x-slot name="title">統計・分析 - Shadova Log</x-slot>

    <!-- Main Content Area (Center) -->
    <div class="flex-1 flex flex-col min-w-0"
         x-data="{
             period: '{{ $period }}',
             viewMode: 'overview'
         }">
        <!-- Top Bar -->
        <header class="flex-shrink-0 border-b dark:border-gray-700 border-gray-200 dark:bg-gray-800/50 bg-white/50">
            <div class="flex items-center justify-between h-14 px-4">
                <div class="flex items-center gap-4">
                    <h1 class="text-lg font-semibold dark:text-white text-gray-900">統計・分析</h1>
                </div>
                <!-- Period Filter -->
                <div class="flex items-center gap-1 p-1 rounded-lg dark:bg-gray-700/50 bg-gray-200">
                    <a href="{{ route('statistics.index', ['period' => 'today']) }}"
                       class="px-3 py-1.5 rounded text-sm font-medium transition-colors {{ $period === 'today' ? 'bg-purple-600 text-white' : 'dark:text-gray-400 text-gray-600 dark:hover:text-white hover:text-gray-900' }}">
                        今日
                    </a>
                    <a href="{{ route('statistics.index', ['period' => 'week']) }}"
                       class="px-3 py-1.5 rounded text-sm font-medium transition-colors {{ $period === 'week' ? 'bg-purple-600 text-white' : 'dark:text-gray-400 text-gray-600 dark:hover:text-white hover:text-gray-900' }}">
                        今週
                    </a>
                    <a href="{{ route('statistics.index', ['period' => 'month']) }}"
                       class="px-3 py-1.5 rounded text-sm font-medium transition-colors {{ $period === 'month' ? 'bg-purple-600 text-white' : 'dark:text-gray-400 text-gray-600 dark:hover:text-white hover:text-gray-900' }}">
                        今月
                    </a>
                    <a href="{{ route('statistics.index', ['period' => 'total']) }}"
                       class="px-3 py-1.5 rounded text-sm font-medium transition-colors {{ $period === 'total' ? 'bg-purple-600 text-white' : 'dark:text-gray-400 text-gray-600 dark:hover:text-white hover:text-gray-900' }}">
                        全期間
                    </a>
                </div>
            </div>
            <!-- View Mode Tabs -->
            <div class="flex items-center gap-1 px-4 pb-2">
                <button
                    @click="viewMode = 'overview'"
                    class="px-4 py-1.5 rounded-full text-sm font-medium transition-all"
                    :class="viewMode === 'overview' ? 'bg-purple-600 text-white' : 'dark:bg-gray-700/50 bg-gray-200 dark:text-gray-400 text-gray-600 dark:hover:text-white hover:text-gray-900'"
                >
                    概要
                </button>
                <button
                    @click="viewMode = 'deck'"
                    class="px-4 py-1.5 rounded-full text-sm font-medium transition-all"
                    :class="viewMode === 'deck' ? 'bg-purple-600 text-white' : 'dark:bg-gray-700/50 bg-gray-200 dark:text-gray-400 text-gray-600 dark:hover:text-white hover:text-gray-900'"
                >
                    デッキ別
                </button>
                <button
                    @click="viewMode = 'matchup'"
                    class="px-4 py-1.5 rounded-full text-sm font-medium transition-all"
                    :class="viewMode === 'matchup' ? 'bg-purple-600 text-white' : 'dark:bg-gray-700/50 bg-gray-200 dark:text-gray-400 text-gray-600 dark:hover:text-white hover:text-gray-900'"
                >
                    相性表
                </button>
            </div>
        </header>

        <!-- Scrollable Content -->
        <main class="flex-1 overflow-y-auto scrollbar-thin p-4">
            <!-- Overview View -->
            <div x-show="viewMode === 'overview'">
                <!-- Summary Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="rounded-xl dark:bg-gray-800 bg-white border dark:border-gray-700 border-gray-200 p-4">
                        <div class="text-sm dark:text-gray-400 text-gray-600 mb-1">総戦績</div>
                        <div class="text-2xl font-bold dark:text-white text-gray-900">{{ $totalBattles }}戦</div>
                        <div class="text-xs dark:text-gray-500 text-gray-500 mt-1">{{ $totalWins }}勝 / {{ $totalLosses }}敗</div>
                    </div>
                    <div class="rounded-xl dark:bg-gray-800 bg-white border dark:border-gray-700 border-gray-200 p-4">
                        <div class="text-sm dark:text-gray-400 text-gray-600 mb-1">勝率</div>
                        <div class="text-2xl font-bold {{ $winRate >= 50 ? 'text-green-500' : ($totalBattles > 0 ? 'text-red-500' : 'dark:text-gray-400 text-gray-500') }}">
                            {{ $totalBattles > 0 ? $winRate . '%' : '--%' }}
                        </div>
                        <div class="text-xs dark:text-gray-500 text-gray-500 mt-1">
                            {{ $totalBattles > 0 ? ($winRate >= 50 ? '勝ち越し' : '負け越し') : 'データなし' }}
                        </div>
                    </div>
                    <div class="rounded-xl dark:bg-gray-800 bg-white border dark:border-gray-700 border-gray-200 p-4">
                        <div class="text-sm dark:text-gray-400 text-gray-600 mb-1">最高連勝</div>
                        <div class="text-2xl font-bold dark:text-white text-gray-900">{{ $maxStreak }}</div>
                        <div class="text-xs dark:text-gray-500 text-gray-500 mt-1">連勝</div>
                    </div>
                    <div class="rounded-xl dark:bg-gray-800 bg-white border dark:border-gray-700 border-gray-200 p-4">
                        <div class="text-sm dark:text-gray-400 text-gray-600 mb-1">登録デッキ数</div>
                        <div class="text-2xl font-bold text-purple-500">{{ $deckCount }}</div>
                        <div class="text-xs dark:text-gray-500 text-gray-500 mt-1">アクティブ: {{ $activeDeckCount }}</div>
                    </div>
                </div>

                <!-- First/Second Stats -->
                <div class="mb-6">
                    <h3 class="text-sm font-medium dark:text-gray-400 text-gray-600 mb-3">先攻/後攻 勝率</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="rounded-xl dark:bg-gray-800 bg-white border dark:border-gray-700 border-gray-200 p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="h-3 w-3 rounded-full bg-blue-400"></div>
                                <span class="text-sm font-medium dark:text-gray-300 text-gray-700">先攻</span>
                            </div>
                            <div class="text-4xl font-bold dark:text-white text-gray-900 mb-2">
                                {{ $firstTotal > 0 ? $firstWinRate . '%' : '--%' }}
                            </div>
                            <div class="h-2 dark:bg-gray-700 bg-gray-200 rounded-full overflow-hidden mb-2">
                                <div class="h-full bg-blue-500 rounded-full" style="width: {{ $firstWinRate }}%"></div>
                            </div>
                            <div class="text-xs dark:text-gray-500 text-gray-500">{{ $firstWins }}勝 {{ $firstTotal - $firstWins }}敗 ({{ $firstTotal }}戦)</div>
                        </div>
                        <div class="rounded-xl dark:bg-gray-800 bg-white border dark:border-gray-700 border-gray-200 p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="h-3 w-3 rounded-full bg-orange-400"></div>
                                <span class="text-sm font-medium dark:text-gray-300 text-gray-700">後攻</span>
                            </div>
                            <div class="text-4xl font-bold dark:text-white text-gray-900 mb-2">
                                {{ $secondTotal > 0 ? $secondWinRate . '%' : '--%' }}
                            </div>
                            <div class="h-2 dark:bg-gray-700 bg-gray-200 rounded-full overflow-hidden mb-2">
                                <div class="h-full bg-orange-500 rounded-full" style="width: {{ $secondWinRate }}%"></div>
                            </div>
                            <div class="text-xs dark:text-gray-500 text-gray-500">{{ $secondWins }}勝 {{ $secondTotal - $secondWins }}敗 ({{ $secondTotal }}戦)</div>
                        </div>
                    </div>
                </div>

                <!-- Class Matchup Overview -->
                <div>
                    <h3 class="text-sm font-medium dark:text-gray-400 text-gray-600 mb-3">クラス別対戦成績</h3>
                    @if($classStats->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                        @foreach($classStats->sortByDesc('total') as $stat)
                        <div class="rounded-lg dark:bg-gray-800 bg-white border dark:border-gray-700 border-gray-200 p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-medium dark:text-white text-gray-900">vs {{ $stat->class->name }}</span>
                                <span class="text-sm {{ $stat->win_rate >= 50 ? 'text-green-500' : 'text-red-500' }}">
                                    {{ $stat->win_rate }}%
                                </span>
                            </div>
                            <div class="h-2 dark:bg-gray-700 bg-gray-200 rounded-full overflow-hidden mb-2">
                                <div class="h-full {{ $stat->win_rate >= 50 ? 'bg-green-500' : 'bg-red-500' }} rounded-full" style="width: {{ $stat->win_rate }}%"></div>
                            </div>
                            <div class="text-xs dark:text-gray-500 text-gray-500">
                                {{ $stat->wins }}勝 {{ $stat->losses }}敗 ({{ $stat->total }}戦)
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="rounded-xl dark:bg-gray-800 bg-white border dark:border-gray-700 border-gray-200 overflow-hidden">
                        <div class="text-center py-12 dark:text-gray-500 text-gray-500">
                            対戦記録がありません。対戦記録を追加すると統計が表示されます。
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Deck Stats View -->
            <div x-show="viewMode === 'deck'" style="display: none;">
                @if($deckStats->count() > 0)
                <div class="space-y-4">
                    @foreach($deckStats as $deck)
                    <div class="rounded-xl dark:bg-gray-800 bg-white border dark:border-gray-700 border-gray-200 p-4">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-lg bg-purple-600/20 flex items-center justify-center">
                                    <span class="text-purple-500 font-bold">{{ mb_substr($deck->leaderClass->name, 0, 1) }}</span>
                                </div>
                                <div>
                                    <h4 class="font-medium dark:text-white text-gray-900">{{ $deck->name }}</h4>
                                    <p class="text-xs dark:text-gray-500 text-gray-500">{{ $deck->leaderClass->name }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-bold {{ $deck->win_rate >= 50 ? 'text-green-500' : ($deck->total_battles > 0 ? 'text-red-500' : 'dark:text-gray-400 text-gray-500') }}">
                                    {{ $deck->total_battles > 0 ? $deck->win_rate . '%' : '--%' }}
                                </div>
                                <div class="text-xs dark:text-gray-500 text-gray-500">
                                    {{ $deck->wins }}勝 {{ $deck->losses }}敗 ({{ $deck->total_battles }}戦)
                                </div>
                            </div>
                        </div>
                        @if($deck->total_battles > 0)
                        <div class="h-2 dark:bg-gray-700 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full {{ $deck->win_rate >= 50 ? 'bg-green-500' : 'bg-red-500' }} rounded-full" style="width: {{ $deck->win_rate }}%"></div>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-12 dark:text-gray-500 text-gray-500">
                    デッキを作成して対戦を記録すると、デッキ別の統計が表示されます。
                </div>
                @endif
            </div>

            <!-- Matchup View -->
            <div x-show="viewMode === 'matchup'" style="display: none;">
                @if(count($matchupData) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="dark:bg-gray-800 bg-gray-100">
                                <th class="p-3 text-left dark:text-gray-400 text-gray-600 font-medium">自分 ＼ 相手</th>
                                @foreach($leaderClasses as $class)
                                <th class="p-3 text-center dark:text-gray-400 text-gray-600 font-medium">{{ $class->name }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($leaderClasses as $myClass)
                                @if(isset($matchupData[$myClass->id]))
                                <tr class="border-t dark:border-gray-700 border-gray-200">
                                    <td class="p-3 font-medium dark:text-white text-gray-900">{{ $myClass->name }}</td>
                                    @foreach($leaderClasses as $oppClass)
                                    <td class="p-3 text-center">
                                        @if(isset($matchupData[$myClass->id][$oppClass->id]))
                                            @php $m = $matchupData[$myClass->id][$oppClass->id]; @endphp
                                            <div class="text-sm font-bold {{ $m['win_rate'] >= 50 ? 'text-green-500' : 'text-red-500' }}">
                                                {{ $m['win_rate'] }}%
                                            </div>
                                            <div class="text-xs dark:text-gray-500 text-gray-500">
                                                {{ $m['wins'] }}-{{ $m['losses'] }}
                                            </div>
                                        @else
                                            <span class="dark:text-gray-600 text-gray-400">-</span>
                                        @endif
                                    </td>
                                    @endforeach
                                </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-12 dark:text-gray-500 text-gray-500">
                    対戦記録を追加すると、相性表が表示されます。
                </div>
                @endif
            </div>
        </main>
    </div>

    <!-- Right Info Panel -->
    <aside class="w-80 flex-shrink-0 border-l dark:border-gray-700 border-gray-200 dark:bg-gray-800/50 bg-white/50 hidden lg:flex flex-col overflow-hidden">
        <div class="flex-shrink-0 flex items-center h-14 px-4 border-b dark:border-gray-700 border-gray-200">
            <h2 class="text-sm font-medium dark:text-gray-400 text-gray-600">最近のアクティビティ</h2>
        </div>
        <div class="flex-1 overflow-y-auto p-4 scrollbar-thin">
            @if($recentBattles->count() > 0)
            <div class="space-y-3">
                @foreach($recentBattles as $battle)
                <div class="rounded-lg dark:bg-gray-700/50 bg-gray-100 p-3">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-medium {{ $battle->result ? 'text-green-500' : 'text-red-500' }}">
                            {{ $battle->result ? 'WIN' : 'LOSE' }}
                        </span>
                        <span class="text-xs dark:text-gray-500 text-gray-500">{{ $battle->played_at->diffForHumans() }}</span>
                    </div>
                    <div class="text-xs dark:text-gray-400 text-gray-600">
                        @if($battle->deck)
                            {{ $battle->deck->name }}
                        @elseif($battle->myClass)
                            {{ $battle->myClass->name }}
                        @endif
                        vs {{ $battle->opponentClass->name }}
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-8 dark:text-gray-500 text-gray-500 text-sm">
                最近のアクティビティはありません
            </div>
            @endif

            <!-- Tips -->
            <div class="mt-6">
                <h3 class="text-sm font-medium dark:text-gray-400 text-gray-600 mb-3">ヒント</h3>
                <div class="space-y-3">
                    <div class="rounded-lg dark:bg-gray-700/50 bg-gray-200 p-3">
                        <p class="text-xs dark:text-gray-400 text-gray-600">
                            期間フィルターを使って、特定の期間の統計を確認できます。
                        </p>
                    </div>
                    <div class="rounded-lg dark:bg-gray-700/50 bg-gray-200 p-3">
                        <p class="text-xs dark:text-gray-400 text-gray-600">
                            相性表で苦手なクラスを把握し、対策を考えましょう。
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </aside>
</x-layouts.app>
