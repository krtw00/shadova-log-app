<x-layouts.app>
    <x-slot name="title">統計・分析 - Shadova Log</x-slot>

    @php
        // TODO: コントローラーから渡されるデータを使用
        // 現在はモックデータ
        $stats = [
            'total' => ['wins' => 0, 'losses' => 0, 'total' => 0, 'win_rate' => 0],
            'today' => ['wins' => 0, 'losses' => 0, 'total' => 0, 'win_rate' => 0],
            'week' => ['wins' => 0, 'losses' => 0, 'total' => 0, 'win_rate' => 0],
            'month' => ['wins' => 0, 'losses' => 0, 'total' => 0, 'win_rate' => 0],
        ];
    @endphp

    <!-- Main Content Area (Center) -->
    <div class="flex-1 flex flex-col min-w-0"
         x-data="{
             period: 'total',
             viewMode: 'overview'
         }">
        <!-- Top Bar -->
        <header class="flex-shrink-0 border-b border-gray-700 bg-gray-800/50">
            <div class="flex items-center justify-between h-14 px-4">
                <div class="flex items-center gap-4">
                    <h1 class="text-lg font-semibold text-white">統計・分析</h1>
                </div>
                <!-- Period Filter -->
                <div class="flex items-center gap-1 p-1 rounded-lg bg-gray-700/50">
                    <button
                        @click="period = 'today'"
                        class="px-3 py-1.5 rounded text-sm font-medium transition-colors"
                        :class="period === 'today' ? 'bg-purple-600 text-white' : 'text-gray-400 hover:text-white'"
                    >今日</button>
                    <button
                        @click="period = 'week'"
                        class="px-3 py-1.5 rounded text-sm font-medium transition-colors"
                        :class="period === 'week' ? 'bg-purple-600 text-white' : 'text-gray-400 hover:text-white'"
                    >今週</button>
                    <button
                        @click="period = 'month'"
                        class="px-3 py-1.5 rounded text-sm font-medium transition-colors"
                        :class="period === 'month' ? 'bg-purple-600 text-white' : 'text-gray-400 hover:text-white'"
                    >今月</button>
                    <button
                        @click="period = 'total'"
                        class="px-3 py-1.5 rounded text-sm font-medium transition-colors"
                        :class="period === 'total' ? 'bg-purple-600 text-white' : 'text-gray-400 hover:text-white'"
                    >全期間</button>
                </div>
            </div>
            <!-- View Mode Tabs -->
            <div class="flex items-center gap-1 px-4 pb-2">
                <button
                    @click="viewMode = 'overview'"
                    class="px-4 py-1.5 rounded-full text-sm font-medium transition-all"
                    :class="viewMode === 'overview' ? 'bg-purple-600 text-white' : 'bg-gray-700/50 text-gray-400 hover:text-white'"
                >
                    概要
                </button>
                <button
                    @click="viewMode = 'deck'"
                    class="px-4 py-1.5 rounded-full text-sm font-medium transition-all"
                    :class="viewMode === 'deck' ? 'bg-purple-600 text-white' : 'bg-gray-700/50 text-gray-400 hover:text-white'"
                >
                    デッキ別
                </button>
                <button
                    @click="viewMode = 'matchup'"
                    class="px-4 py-1.5 rounded-full text-sm font-medium transition-all"
                    :class="viewMode === 'matchup' ? 'bg-purple-600 text-white' : 'bg-gray-700/50 text-gray-400 hover:text-white'"
                >
                    相性表
                </button>
                <button
                    @click="viewMode = 'trend'"
                    class="px-4 py-1.5 rounded-full text-sm font-medium transition-all"
                    :class="viewMode === 'trend' ? 'bg-purple-600 text-white' : 'bg-gray-700/50 text-gray-400 hover:text-white'"
                >
                    トレンド
                </button>
            </div>
        </header>

        <!-- Scrollable Content -->
        <main class="flex-1 overflow-y-auto scrollbar-thin p-4">
            <!-- Overview View -->
            <div x-show="viewMode === 'overview'">
                <!-- Summary Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="rounded-xl bg-gray-800 border border-gray-700 p-4">
                        <div class="text-sm text-gray-400 mb-1">総戦績</div>
                        <div class="text-2xl font-bold text-white">0戦</div>
                        <div class="text-xs text-gray-500 mt-1">0勝 / 0敗</div>
                    </div>
                    <div class="rounded-xl bg-gray-800 border border-gray-700 p-4">
                        <div class="text-sm text-gray-400 mb-1">勝率</div>
                        <div class="text-2xl font-bold text-gray-400">--%</div>
                        <div class="text-xs text-gray-500 mt-1">データなし</div>
                    </div>
                    <div class="rounded-xl bg-gray-800 border border-gray-700 p-4">
                        <div class="text-sm text-gray-400 mb-1">最高連勝</div>
                        <div class="text-2xl font-bold text-white">0</div>
                        <div class="text-xs text-gray-500 mt-1">連勝</div>
                    </div>
                    <div class="rounded-xl bg-gray-800 border border-gray-700 p-4">
                        <div class="text-sm text-gray-400 mb-1">登録デッキ数</div>
                        <div class="text-2xl font-bold text-purple-400">0</div>
                        <div class="text-xs text-gray-500 mt-1">アクティブ: 0</div>
                    </div>
                </div>

                <!-- First/Second Stats -->
                <div class="mb-6">
                    <h3 class="text-sm font-medium text-gray-400 mb-3">先攻/後攻 勝率</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="rounded-xl bg-gray-800 border border-gray-700 p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="h-3 w-3 rounded-full bg-blue-400"></div>
                                <span class="text-sm font-medium text-gray-300">先攻</span>
                            </div>
                            <div class="text-4xl font-bold text-white mb-2">--%</div>
                            <div class="h-2 bg-gray-700 rounded-full overflow-hidden mb-2">
                                <div class="h-full bg-blue-500 rounded-full" style="width: 0%"></div>
                            </div>
                            <div class="text-xs text-gray-500">0勝 0敗 (0戦)</div>
                        </div>
                        <div class="rounded-xl bg-gray-800 border border-gray-700 p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="h-3 w-3 rounded-full bg-orange-400"></div>
                                <span class="text-sm font-medium text-gray-300">後攻</span>
                            </div>
                            <div class="text-4xl font-bold text-white mb-2">--%</div>
                            <div class="h-2 bg-gray-700 rounded-full overflow-hidden mb-2">
                                <div class="h-full bg-orange-500 rounded-full" style="width: 0%"></div>
                            </div>
                            <div class="text-xs text-gray-500">0勝 0敗 (0戦)</div>
                        </div>
                    </div>
                </div>

                <!-- Class Matchup Overview -->
                <div>
                    <h3 class="text-sm font-medium text-gray-400 mb-3">クラス別対戦成績</h3>
                    <div class="rounded-xl bg-gray-800 border border-gray-700 overflow-hidden">
                        <div class="text-center py-12 text-gray-500">
                            対戦記録がありません。対戦記録を追加すると統計が表示されます。
                        </div>
                    </div>
                </div>
            </div>

            <!-- Deck Stats View -->
            <div x-show="viewMode === 'deck'" style="display: none;">
                <div class="text-center py-12 text-gray-500">
                    デッキを作成して対戦を記録すると、デッキ別の統計が表示されます。
                </div>
            </div>

            <!-- Matchup View -->
            <div x-show="viewMode === 'matchup'" style="display: none;">
                <div class="text-center py-12 text-gray-500">
                    対戦記録を追加すると、相性表が表示されます。
                </div>
            </div>

            <!-- Trend View -->
            <div x-show="viewMode === 'trend'" style="display: none;">
                <div class="text-center py-12 text-gray-500">
                    対戦記録を追加すると、勝率の推移グラフが表示されます。
                </div>
            </div>
        </main>
    </div>

    <!-- Right Info Panel -->
    <aside class="w-80 flex-shrink-0 border-l border-gray-700 bg-gray-800/50 hidden lg:flex flex-col overflow-hidden">
        <div class="flex-shrink-0 flex items-center h-14 px-4 border-b border-gray-700">
            <h2 class="text-sm font-medium text-gray-400">クイック情報</h2>
        </div>
        <div class="flex-1 overflow-y-auto p-4 scrollbar-thin">
            <!-- Recent Activity -->
            <div class="mb-6">
                <h3 class="text-sm font-medium text-gray-400 mb-3">最近のアクティビティ</h3>
                <div class="text-center py-8 text-gray-500 text-sm">
                    最近のアクティビティはありません
                </div>
            </div>

            <!-- Tips -->
            <div>
                <h3 class="text-sm font-medium text-gray-400 mb-3">ヒント</h3>
                <div class="space-y-3">
                    <div class="rounded-lg bg-gray-700/50 p-3">
                        <p class="text-xs text-gray-400">
                            対戦記録ページから対戦を記録すると、自動的に統計が更新されます。
                        </p>
                    </div>
                    <div class="rounded-lg bg-gray-700/50 p-3">
                        <p class="text-xs text-gray-400">
                            デッキを作成してから対戦を記録すると、デッキ別の勝率を確認できます。
                        </p>
                    </div>
                    <div class="rounded-lg bg-gray-700/50 p-3">
                        <p class="text-xs text-gray-400">
                            期間フィルターを使って、特定の期間の統計を確認できます。
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </aside>
</x-layouts.app>
