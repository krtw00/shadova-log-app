<x-layouts.app>
    <x-slot:title>対戦記録 - Shadova Log</x-slot:title>

    <!-- Main Content Area (Center) -->
    <div class="flex-1 flex flex-col min-w-0"
         x-data="{
             selectedResult: null,
             selectedOpponent: null,
             selectedTurn: 'first',
             selectedDeck: {{ $decks->first()?->id ?? 'null' }},
             defaultTurn: 'first',
             resultFilter: 'all'
         }">
        <!-- Top Bar with Game Mode Tabs -->
        <header class="flex-shrink-0 border-b border-gray-700 bg-gray-800/50">
            <div class="flex items-center justify-between h-14 px-4">
                <div class="flex items-center gap-4">
                    <h1 class="text-lg font-semibold text-white">対戦記録</h1>
                    <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-lg bg-gray-700/50 text-sm">
                        <span class="text-gray-400">今日:</span>
                        <span class="text-green-400 font-medium">{{ $stats['today']['wins'] }}勝</span>
                        <span class="text-gray-500">/</span>
                        <span class="text-red-400 font-medium">{{ $stats['today']['losses'] }}敗</span>
                        <span class="text-gray-500">|</span>
                        <span class="text-purple-400 font-medium">{{ $stats['today']['winRate'] }}%</span>
                    </div>
                </div>
                @if($decks->isNotEmpty())
                <!-- 使用中デッキ表示 -->
                <div class="flex items-center gap-3">
                    <select x-model="selectedDeck" class="rounded-lg bg-purple-600/20 border border-purple-500/30 text-sm text-purple-300 px-3 py-1.5">
                        @foreach($decks as $deck)
                        <option value="{{ $deck->id }}">{{ $deck->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>
            <!-- Game Mode Tabs -->
            <div class="flex items-center gap-1 px-4 pb-2">
                @foreach($gameModes as $mode)
                <a href="{{ route('battles.index', ['mode' => $mode->code]) }}"
                   class="px-4 py-1.5 rounded-full text-sm font-medium transition-all {{ $gameMode && $gameMode->code === $mode->code ? 'bg-purple-600 text-white' : 'bg-gray-700/50 text-gray-400 hover:text-white' }}">
                    {{ $mode->name }}
                </a>
                @endforeach
            </div>
        </header>

        <!-- Scrollable Content -->
        <main class="flex-1 overflow-y-auto scrollbar-thin">
            <!-- インライン対戦入力セクション -->
            @if($decks->isNotEmpty())
            <div class="p-4 border-b border-gray-700 bg-gray-800/30">
                <form action="{{ route('battles.store') }}" method="POST" class="glass-card rounded-xl p-4">
                    @csrf
                    <input type="hidden" name="game_mode_id" value="{{ $gameMode?->id ?? 1 }}">
                    <input type="hidden" name="deck_id" x-bind:value="selectedDeck">
                    <input type="hidden" name="result" x-bind:value="selectedResult === 'win' ? 1 : 0">
                    <input type="hidden" name="is_first" x-bind:value="selectedTurn === 'first' ? 1 : 0">
                    <input type="hidden" name="opponent_class_id" x-bind:value="selectedOpponent">

                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-sm font-medium text-gray-400 flex items-center gap-2">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            クイック入力
                        </h2>
                        <div class="flex items-center gap-2 text-xs">
                            <span class="text-gray-500">初期値:</span>
                            <button type="button" @click="defaultTurn = 'first'; selectedTurn = 'first'"
                                class="px-2 py-1 rounded transition-colors"
                                :class="defaultTurn === 'first' ? 'bg-blue-600/30 text-blue-400' : 'bg-gray-700 text-gray-400 hover:text-white'">先攻</button>
                            <button type="button" @click="defaultTurn = 'second'; selectedTurn = 'second'"
                                class="px-2 py-1 rounded transition-colors"
                                :class="defaultTurn === 'second' ? 'bg-orange-600/30 text-orange-400' : 'bg-gray-700 text-gray-400 hover:text-white'">後攻</button>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <!-- 勝敗ボタン -->
                        <div class="flex gap-2">
                            <button type="button" @click="selectedResult = 'win'"
                                class="flex items-center gap-2 px-4 py-2.5 rounded-xl font-medium transition-all"
                                :class="selectedResult === 'win'
                                    ? 'bg-green-600 text-white ring-2 ring-green-400 ring-offset-2 ring-offset-gray-800'
                                    : 'bg-green-600/20 text-green-400 hover:bg-green-600/30 border border-green-500/30'">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                WIN
                            </button>
                            <button type="button" @click="selectedResult = 'lose'"
                                class="flex items-center gap-2 px-4 py-2.5 rounded-xl font-medium transition-all"
                                :class="selectedResult === 'lose'
                                    ? 'bg-red-600 text-white ring-2 ring-red-400 ring-offset-2 ring-offset-gray-800'
                                    : 'bg-red-600/20 text-red-400 hover:bg-red-600/30 border border-red-500/30'">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                LOSE
                            </button>
                        </div>

                        <div class="h-10 w-px bg-gray-600"></div>

                        <!-- 相手クラス選択 -->
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-400">vs</span>
                            <div class="flex flex-wrap gap-1">
                                @foreach($leaderClasses as $class)
                                <button type="button" @click="selectedOpponent = {{ $class->id }}"
                                    class="px-2.5 py-1.5 rounded-lg text-xs font-medium transition-all"
                                    :class="selectedOpponent === {{ $class->id }}
                                        ? 'bg-purple-600 text-white'
                                        : 'bg-gray-700 text-gray-300 hover:bg-gray-600'">
                                    {{ $class->name }}
                                </button>
                                @endforeach
                            </div>
                        </div>

                        <div class="h-10 w-px bg-gray-600"></div>

                        <!-- 先攻/後攻 -->
                        <div class="flex gap-1">
                            <button type="button" @click="selectedTurn = 'first'"
                                class="px-3 py-1.5 rounded-lg text-sm font-medium transition-all"
                                :class="selectedTurn === 'first' ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600'">
                                <span class="flex items-center gap-1">
                                    <span class="h-2 w-2 rounded-full bg-blue-400"></span>
                                    先攻
                                </span>
                            </button>
                            <button type="button" @click="selectedTurn = 'second'"
                                class="px-3 py-1.5 rounded-lg text-sm font-medium transition-all"
                                :class="selectedTurn === 'second' ? 'bg-orange-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600'">
                                <span class="flex items-center gap-1">
                                    <span class="h-2 w-2 rounded-full bg-orange-400"></span>
                                    後攻
                                </span>
                            </button>
                        </div>

                        <!-- 記録ボタン -->
                        <button type="submit"
                            class="ml-auto flex items-center gap-2 px-5 py-2.5 rounded-xl font-medium transition-all"
                            :class="selectedResult && selectedOpponent
                                ? 'bg-purple-600 text-white hover:bg-purple-500'
                                : 'bg-gray-700 text-gray-500 cursor-not-allowed'"
                            :disabled="!selectedResult || !selectedOpponent">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            記録
                        </button>
                    </div>
                </form>
            </div>
            @else
            <div class="p-4 border-b border-gray-700 bg-gray-800/30">
                <div class="glass-card rounded-xl p-4 text-center">
                    <p class="text-gray-400 mb-3">対戦を記録するにはデッキを作成してください</p>
                    <a href="{{ route('decks.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-purple-600 text-white hover:bg-purple-500">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        デッキを作成
                    </a>
                </div>
            </div>
            @endif

            <!-- Battle List -->
            <div class="p-4">
                <!-- Quick Filters -->
                <div class="flex items-center gap-2 mb-4 flex-wrap">
                    <button @click="resultFilter = 'all'" class="px-3 py-1.5 rounded-full text-sm font-medium transition-all"
                        :class="resultFilter === 'all' ? 'bg-purple-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600'">すべて</button>
                    <button @click="resultFilter = 'win'" class="px-3 py-1.5 rounded-full text-sm font-medium transition-all"
                        :class="resultFilter === 'win' ? 'bg-purple-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600'">勝ち</button>
                    <button @click="resultFilter = 'lose'" class="px-3 py-1.5 rounded-full text-sm font-medium transition-all"
                        :class="resultFilter === 'lose' ? 'bg-purple-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600'">負け</button>
                </div>

                <!-- Battle Cards -->
                <div class="space-y-2">
                    @forelse($battles as $battle)
                    <div class="group rounded-xl bg-gray-800 border border-gray-700 p-4 hover:border-purple-500/50 transition-all"
                         x-show="resultFilter === 'all' || (resultFilter === 'win' && {{ $battle->result ? 'true' : 'false' }}) || (resultFilter === 'lose' && {{ !$battle->result ? 'true' : 'false' }})">
                        <div class="flex items-center gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl {{ $battle->result ? 'bg-green-500/10 border border-green-500/20' : 'bg-red-500/10 border border-red-500/20' }}">
                                @if($battle->result)
                                <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                @else
                                <svg class="h-6 w-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-semibold text-white">{{ $battle->deck->name }}</span>
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
                                    <span>{{ $battle->played_at->diffForHumans() }}</span>
                                </div>
                            </div>
                            <div class="hidden group-hover:flex items-center gap-2">
                                <form action="{{ route('battles.destroy', $battle) }}" method="POST" onsubmit="return confirm('削除しますか？')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 rounded-lg text-gray-400 hover:bg-gray-700 hover:text-red-400">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                        @if($battle->notes)
                        <div class="mt-3 pt-3 border-t border-gray-700">
                            <p class="text-sm text-gray-400 italic">"{{ $battle->notes }}"</p>
                        </div>
                        @endif
                    </div>
                    @empty
                    <div class="flex flex-col items-center justify-center rounded-lg border border-dashed border-gray-700 bg-gray-800/50 py-20 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                            <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                        <h3 class="mt-4 text-lg font-medium text-white">対戦記録がありません</h3>
                        <p class="mt-1 text-sm text-gray-400">最初の対戦を記録して、分析を始めましょう。</p>
                    </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                @if($battles->hasPages())
                <div class="mt-4">
                    {{ $battles->links() }}
                </div>
                @endif
            </div>
        </main>
    </div>

    <!-- Right Stats Panel -->
    <aside class="hidden lg:flex w-80 flex-shrink-0 border-l border-gray-700 bg-gray-800/50 flex-col overflow-hidden"
           x-data="{ rightPanel: 'stats' }">
        <!-- Panel Tabs -->
        <div class="flex-shrink-0 flex border-b border-gray-700">
            <button @click="rightPanel = 'stats'" class="flex-1 px-4 py-3 text-sm font-medium transition-colors"
                :class="rightPanel === 'stats' ? 'text-purple-400 border-b-2 border-purple-400 bg-gray-800' : 'text-gray-400 hover:text-white'">
                統計
            </button>
            <button @click="rightPanel = 'deck'" class="flex-1 px-4 py-3 text-sm font-medium transition-colors"
                :class="rightPanel === 'deck' ? 'text-purple-400 border-b-2 border-purple-400 bg-gray-800' : 'text-gray-400 hover:text-white'">
                デッキ
            </button>
        </div>

        <!-- Stats Panel Content -->
        <div class="flex-1 overflow-y-auto p-4 scrollbar-thin" x-show="rightPanel === 'stats'">
            <!-- Streak Card -->
            <div class="rounded-xl bg-gradient-to-br from-purple-900/40 to-gray-800 border border-purple-500/30 p-4 mb-4">
                <div class="text-sm text-purple-300 mb-1">{{ $stats['streak'] > 0 ? '連勝中' : '直近' }}</div>
                <div class="text-4xl font-bold text-white">{{ $stats['streak'] }}</div>
            </div>

            <!-- First/Second Stats -->
            <div class="mb-6">
                <h3 class="text-sm font-medium text-gray-400 mb-3">先攻/後攻勝率（今日）</h3>
                <div class="grid grid-cols-2 gap-2">
                    <div class="rounded-lg bg-gray-700/50 p-3 text-center">
                        <div class="text-xs text-blue-400 mb-1">先攻</div>
                        <div class="text-2xl font-bold text-white">{{ $stats['byTurn']['first']['winRate'] }}%</div>
                        <div class="text-xs text-gray-500">{{ $stats['byTurn']['first']['wins'] }}-{{ $stats['byTurn']['first']['total'] - $stats['byTurn']['first']['wins'] }}</div>
                    </div>
                    <div class="rounded-lg bg-gray-700/50 p-3 text-center">
                        <div class="text-xs text-orange-400 mb-1">後攻</div>
                        <div class="text-2xl font-bold text-white">{{ $stats['byTurn']['second']['winRate'] }}%</div>
                        <div class="text-xs text-gray-500">{{ $stats['byTurn']['second']['wins'] }}-{{ $stats['byTurn']['second']['total'] - $stats['byTurn']['second']['wins'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Deck Panel Content -->
        <div class="flex-1 overflow-y-auto p-4 scrollbar-thin" x-show="rightPanel === 'deck'" style="display: none;">
            <div>
                <h3 class="text-sm font-medium text-gray-400 mb-3">登録デッキ</h3>
                <div class="space-y-2">
                    @foreach($decks as $deck)
                    <div class="rounded-lg bg-gray-700/50 p-3">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-lg bg-purple-600/20 flex items-center justify-center">
                                <span class="text-purple-400 font-bold">{{ mb_substr($deck->leaderClass->name, 0, 1) }}</span>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-medium text-white text-sm">{{ $deck->name }}</h4>
                                <p class="text-xs text-gray-400">{{ $deck->winRate() }}% ({{ $deck->battles_count }}戦)</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    <a href="{{ route('decks.index') }}" class="block w-full rounded-lg border-2 border-dashed border-gray-600 p-3 text-center text-gray-400 hover:border-purple-500/50 hover:text-purple-400 transition-colors">
                        <svg class="h-5 w-5 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span class="text-sm">デッキ管理へ</span>
                    </a>
                </div>
            </div>
        </div>
    </aside>
</x-layouts.app>
