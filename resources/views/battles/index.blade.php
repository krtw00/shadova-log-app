<x-layouts.app>
    <x-slot:title>対戦記録 - Shadova Log</x-slot:title>

    <!-- Wrapper with shared Alpine state -->
    <div class="flex-1 flex min-w-0"
         x-data="{
             selectedResult: null,
             selectedOpponent: null,
             selectedTurn: 'first',
             selectedDeck: {{ $decks->first()?->id ?? 'null' }},
             selectedMyClass: null,
             selectedRank: null,
             selectedTier: '{{ $lastBattle?->rank?->tier ?? '' }}',
             selectedLevel: '{{ $lastBattle?->rank?->level ?? '0' }}',
             selectedGroup: '{{ $lastBattle?->group_id ?? '' }}',
             is2Pick: {{ $gameMode && $gameMode->code === '2PICK' ? 'true' : 'false' }},
             isRankMatch: {{ $gameMode && $gameMode->code === 'RANK' ? 'true' : 'false' }},
             hasRank: {{ $gameMode && in_array($gameMode->code, ['RANK', '2PICK']) ? 'true' : 'false' }},
             rankMap: {
                 @foreach($ranks as $rank)
                 '{{ $rank->tier }}_{{ $rank->level }}': {{ $rank->id }},
                 @endforeach
             },
             getRankId() {
                 if (!this.selectedTier) return null;
                 if (this.selectedTier === 'Master' || this.selectedTier === 'GrandMaster') {
                     return this.rankMap[this.selectedTier + '_0'] || null;
                 }
                 // Beginner, D, C, B, A, AA はレベル選択あり
                 return this.rankMap[this.selectedTier + '_' + this.selectedLevel] || null;
             },
             defaultTurn: 'first',
             resultFilter: 'all',
             showCreateDeckModal: false,
             showEditDeckModal: false,
             showCreateShareModal: false,
             showUsernameModal: false,
             editDeck: null,
             showEditBattleModal: false,
             editBattle: null,
             rightPanel: 'stats',
             classColors: {
                 1: { bg: 'bg-green-600/20', text: 'text-green-400', label: 'E' },
                 2: { bg: 'bg-yellow-600/20', text: 'text-yellow-400', label: 'R' },
                 3: { bg: 'bg-purple-600/20', text: 'text-purple-400', label: 'W' },
                 4: { bg: 'bg-red-600/20', text: 'text-red-400', label: 'D' },
                 5: { bg: 'bg-gray-600/20', text: 'text-gray-400', label: 'N' },
                 6: { bg: 'bg-blue-600/20', text: 'text-blue-400', label: 'B' },
                 7: { bg: 'bg-cyan-600/20', text: 'text-cyan-400', label: 'Ne' }
             }
         }">

        <!-- Main Content Area (Center) -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Top Bar with Game Mode Tabs -->
            <header class="flex-shrink-0 border-b dark:border-gray-700 border-gray-200 dark:bg-gray-800/50 bg-white/50">
                <div class="flex items-center justify-between h-14 px-4">
                    <div class="flex items-center gap-4">
                        <h1 class="text-lg font-semibold dark:text-white text-gray-900">対戦記録</h1>
                        <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-lg dark:bg-gray-700/50 bg-gray-100 text-sm">
                            <span class="dark:text-gray-400 text-gray-500">今日:</span>
                            <span class="text-green-500 font-medium">{{ $stats['today']['wins'] }}勝</span>
                            <span class="dark:text-gray-500 text-gray-400">/</span>
                            <span class="text-red-500 font-medium">{{ $stats['today']['losses'] }}敗</span>
                            <span class="dark:text-gray-500 text-gray-400">|</span>
                            <span class="text-purple-500 font-medium">{{ $stats['today']['winRate'] }}%</span>
                        </div>
                    </div>
                    <!-- 2pick: クラス選択 / その他: デッキ選択 -->
                    <template x-if="is2Pick">
                        <select x-model="selectedMyClass" class="rounded-lg dark:bg-gray-900 bg-white border border-purple-500/50 text-sm dark:text-white text-gray-900 px-3 py-1.5">
                            <option value="">クラス選択</option>
                            @foreach($leaderClasses as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </template>
                    <template x-if="!is2Pick && {{ $decks->isNotEmpty() ? 'true' : 'false' }}">
                        <select x-model="selectedDeck" class="rounded-lg dark:bg-gray-900 bg-white border border-purple-500/50 text-sm dark:text-white text-gray-900 px-3 py-1.5">
                            @foreach($decks as $deck)
                            <option value="{{ $deck->id }}">{{ $deck->name }}</option>
                            @endforeach
                        </select>
                    </template>
                </div>
                <!-- Game Mode Tabs -->
                <div class="flex items-center gap-1 px-4 pb-2">
                    @foreach($gameModes as $mode)
                    <a href="{{ route('battles.index', ['mode' => $mode->code]) }}"
                       class="px-4 py-1.5 rounded-full text-sm font-medium transition-all {{ $gameMode && $gameMode->code === $mode->code ? 'bg-purple-600 text-white' : 'dark:bg-gray-700/50 bg-gray-200 dark:text-gray-400 text-gray-600 dark:hover:text-white hover:text-gray-900' }}">
                        {{ $mode->name }}
                    </a>
                    @endforeach
                </div>
            </header>

            <!-- Scrollable Content -->
            <main class="flex-1 overflow-y-auto scrollbar-thin">
                <!-- インライン対戦入力セクション -->
                @if($decks->isNotEmpty() || ($gameMode && $gameMode->code === '2PICK'))
                <div class="p-4 border-b dark:border-gray-700 border-gray-200 dark:bg-gray-800/30 bg-gray-50">
                    <form action="{{ route('battles.store') }}" method="POST" class="glass-card rounded-xl p-4">
                        @csrf
                        <input type="hidden" name="game_mode_id" value="{{ $gameMode?->id ?? 1 }}">
                        <input type="hidden" name="deck_id" x-bind:value="is2Pick ? '' : selectedDeck">
                        <input type="hidden" name="my_class_id" x-bind:value="is2Pick ? selectedMyClass : ''">
                        <input type="hidden" name="result" x-bind:value="selectedResult === 'win' ? 1 : 0">
                        <input type="hidden" name="is_first" x-bind:value="selectedTurn === 'first' ? 1 : 0">
                        <input type="hidden" name="opponent_class_id" x-bind:value="selectedOpponent">
                        <input type="hidden" name="rank_id" x-bind:value="hasRank ? getRankId() : ''">
                        <input type="hidden" name="group_id" x-bind:value="isRankMatch ? (selectedGroup || '') : ''">

                        <div class="flex items-center justify-between mb-3">
                            <h2 class="text-sm font-medium dark:text-gray-400 text-gray-600 flex items-center gap-2">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                対戦記録
                            </h2>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <!-- 勝敗ボタン -->
                            <div class="flex gap-2">
                                <button type="button" @click="selectedResult = 'win'"
                                    class="flex items-center gap-2 px-4 py-2.5 rounded-xl font-medium transition-all"
                                    :class="selectedResult === 'win'
                                        ? 'bg-green-600 text-white ring-2 ring-green-400 ring-offset-2 dark:ring-offset-gray-800 ring-offset-white'
                                        : 'bg-green-600/20 text-green-500 hover:bg-green-600/30 border border-green-500/30'">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    WIN
                                </button>
                                <button type="button" @click="selectedResult = 'lose'"
                                    class="flex items-center gap-2 px-4 py-2.5 rounded-xl font-medium transition-all"
                                    :class="selectedResult === 'lose'
                                        ? 'bg-red-600 text-white ring-2 ring-red-400 ring-offset-2 dark:ring-offset-gray-800 ring-offset-white'
                                        : 'bg-red-600/20 text-red-500 hover:bg-red-600/30 border border-red-500/30'">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    LOSE
                                </button>
                            </div>

                            <div class="h-10 w-px dark:bg-gray-600 bg-gray-300"></div>

                            <!-- 相手クラス選択 -->
                            <div class="flex items-center gap-2">
                                <span class="text-sm dark:text-gray-400 text-gray-500">vs</span>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($leaderClasses as $class)
                                    <button type="button" @click="selectedOpponent = {{ $class->id }}"
                                        class="px-2.5 py-1.5 rounded-lg text-xs font-medium transition-all"
                                        :class="selectedOpponent === {{ $class->id }}
                                            ? 'bg-purple-600 text-white'
                                            : 'dark:bg-gray-700 bg-gray-200 dark:text-gray-300 text-gray-700 dark:hover:bg-gray-600 hover:bg-gray-300'">
                                        {{ $class->name }}
                                    </button>
                                    @endforeach
                                </div>
                            </div>

                            <div class="h-10 w-px dark:bg-gray-600 bg-gray-300"></div>

                            <!-- 先攻/後攻 -->
                            <div class="flex gap-1">
                                <button type="button" @click="selectedTurn = 'first'"
                                    class="px-3 py-1.5 rounded-lg text-sm font-medium transition-all"
                                    :class="selectedTurn === 'first' ? 'bg-blue-600 text-white' : 'dark:bg-gray-700 bg-gray-200 dark:text-gray-300 text-gray-700 dark:hover:bg-gray-600 hover:bg-gray-300'">
                                    <span class="flex items-center gap-1">
                                        <span class="h-2 w-2 rounded-full bg-blue-400"></span>
                                        先攻
                                    </span>
                                </button>
                                <button type="button" @click="selectedTurn = 'second'"
                                    class="px-3 py-1.5 rounded-lg text-sm font-medium transition-all"
                                    :class="selectedTurn === 'second' ? 'bg-orange-600 text-white' : 'dark:bg-gray-700 bg-gray-200 dark:text-gray-300 text-gray-700 dark:hover:bg-gray-600 hover:bg-gray-300'">
                                    <span class="flex items-center gap-1">
                                        <span class="h-2 w-2 rounded-full bg-orange-400"></span>
                                        後攻
                                    </span>
                                </button>
                            </div>

                            <!-- ランク選択（ランクマッチ・2pickのみ） -->
                            <template x-if="hasRank">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-px dark:bg-gray-600 bg-gray-300"></div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm dark:text-gray-400 text-gray-500">ランク</span>
                                        <select x-model="selectedTier"
                                            class="rounded-lg dark:bg-gray-900 bg-white border dark:border-gray-600 border-gray-300 text-sm dark:text-white text-gray-900 px-2 py-1.5 focus:border-purple-500 focus:ring-purple-500">
                                            <option value="">未選択</option>
                                            <option value="Beginner">ビギナー</option>
                                            <option value="D">D</option>
                                            <option value="C">C</option>
                                            <option value="B">B</option>
                                            <option value="A">A</option>
                                            <option value="AA">AA</option>
                                            <option value="Master">マスター</option>
                                            <option value="GrandMaster">グラマス</option>
                                        </select>
                                        <select x-model="selectedLevel"
                                            x-show="selectedTier && selectedTier !== 'Master' && selectedTier !== 'GrandMaster'"
                                            class="rounded-lg dark:bg-gray-900 bg-white border dark:border-gray-600 border-gray-300 text-sm dark:text-white text-gray-900 px-2 py-1.5 focus:border-purple-500 focus:ring-purple-500">
                                            <option value="0">0</option>
                                            <option value="1">1</option>
                                            <option value="2">2</option>
                                            <option value="3">3</option>
                                        </select>
                                    </div>
                                </div>
                            </template>

                            <!-- グループ選択（ランクマッチのみ） -->
                            <div x-show="isRankMatch" class="flex items-center gap-2">
                                <span class="text-sm dark:text-gray-400 text-gray-500">グループ</span>
                                <select x-model="selectedGroup"
                                    class="rounded-lg dark:bg-gray-900 bg-white border dark:border-gray-600 border-gray-300 text-sm dark:text-white text-gray-900 px-2 py-1.5 focus:border-purple-500 focus:ring-purple-500">
                                    <option value="">未選択</option>
                                    @foreach($groups as $group)
                                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- 記録ボタン -->
                            <button type="submit"
                                class="ml-auto flex items-center gap-2 px-5 py-2.5 rounded-xl font-medium transition-all"
                                :class="selectedResult && selectedOpponent && (is2Pick ? selectedMyClass : selectedDeck)
                                    ? 'bg-purple-600 text-white hover:bg-purple-500'
                                    : 'dark:bg-gray-700 bg-gray-200 dark:text-gray-500 text-gray-400 cursor-not-allowed'"
                                :disabled="!selectedResult || !selectedOpponent || (is2Pick ? !selectedMyClass : !selectedDeck)">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                記録
                            </button>
                        </div>
                    </form>
                </div>
                @else
                <div class="p-4 border-b dark:border-gray-700 border-gray-200 dark:bg-gray-800/30 bg-gray-50">
                    <div class="glass-card rounded-xl p-6 text-center">
                        <div class="h-16 w-16 rounded-full dark:bg-gray-800 bg-gray-100 flex items-center justify-center mx-auto mb-4">
                            <svg class="h-8 w-8 dark:text-gray-600 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="4" y="4" width="12" height="16" rx="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 2h10a2 2 0 012 2v14"/>
                            </svg>
                        </div>
                        <h2 class="text-lg font-semibold dark:text-white text-gray-900 mb-2">デッキを作成して始めましょう</h2>
                        <p class="dark:text-gray-400 text-gray-500 mb-4 text-sm">対戦を記録するにはデッキが必要です</p>
                        <button
                            @click="showCreateDeckModal = true"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-purple-600 text-white font-medium hover:bg-purple-500 transition-colors"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            最初のデッキを作成
                        </button>
                    </div>
                </div>
                @endif

                <!-- Battle List -->
                <div class="p-4">
                    <!-- Quick Filters -->
                    <div class="flex items-center gap-2 mb-4 flex-wrap">
                        <button @click="resultFilter = 'all'" class="px-3 py-1.5 rounded-full text-sm font-medium transition-all"
                            :class="resultFilter === 'all' ? 'bg-purple-600 text-white' : 'dark:bg-gray-700 bg-gray-200 dark:text-gray-300 text-gray-700 dark:hover:bg-gray-600 hover:bg-gray-300'">すべて</button>
                        <button @click="resultFilter = 'win'" class="px-3 py-1.5 rounded-full text-sm font-medium transition-all"
                            :class="resultFilter === 'win' ? 'bg-purple-600 text-white' : 'dark:bg-gray-700 bg-gray-200 dark:text-gray-300 text-gray-700 dark:hover:bg-gray-600 hover:bg-gray-300'">勝ち</button>
                        <button @click="resultFilter = 'lose'" class="px-3 py-1.5 rounded-full text-sm font-medium transition-all"
                            :class="resultFilter === 'lose' ? 'bg-purple-600 text-white' : 'dark:bg-gray-700 bg-gray-200 dark:text-gray-300 text-gray-700 dark:hover:bg-gray-600 hover:bg-gray-300'">負け</button>
                    </div>

                    <!-- Battle Cards -->
                    <div class="space-y-2">
                        @forelse($battles as $battle)
                        <div class="group rounded-xl dark:bg-gray-800 bg-white border dark:border-gray-700 border-gray-200 p-4 dark:hover:border-purple-500/50 hover:border-purple-400 transition-all"
                             x-show="resultFilter === 'all' || (resultFilter === 'win' && {{ $battle->result ? 'true' : 'false' }}) || (resultFilter === 'lose' && {{ !$battle->result ? 'true' : 'false' }})">
                            <div class="flex items-center gap-4">
                                <div class="flex h-12 w-16 items-center justify-center gap-1 rounded-xl {{ $battle->result ? 'bg-green-500/10 border border-green-500/20' : 'bg-red-500/10 border border-red-500/20' }}">
                                    @if($battle->result)
                                    <svg class="h-4 w-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span class="text-sm font-bold text-green-500">WIN</span>
                                    @else
                                    <svg class="h-4 w-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    <span class="text-sm font-bold text-red-500">LOSE</span>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-semibold dark:text-white text-gray-900">
                                            @if($battle->deck)
                                                {{ $battle->deck->name }}
                                            @elseif($battle->myClass)
                                                {{ $battle->myClass->name }}
                                            @else
                                                -
                                            @endif
                                        </span>
                                        <svg class="h-4 w-4 dark:text-gray-500 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                        </svg>
                                        <span class="dark:text-gray-300 text-gray-700">{{ $battle->opponentClass->name }}</span>
                                    </div>
                                    <div class="flex items-center gap-3 mt-1 text-sm dark:text-gray-400 text-gray-500">
                                        <span class="inline-flex items-center gap-1">
                                            <span class="h-1.5 w-1.5 rounded-full {{ $battle->is_first ? 'bg-blue-400' : 'bg-orange-400' }}"></span>
                                            {{ $battle->is_first ? '先攻' : '後攻' }}
                                        </span>
                                        <span class="px-1.5 py-0.5 rounded dark:bg-purple-600/30 bg-purple-100 dark:text-purple-400 text-purple-700 text-xs">{{ $battle->gameMode->name }}</span>
                                        @if($battle->rank)
                                        <span class="px-1.5 py-0.5 rounded dark:bg-amber-600/30 bg-amber-100 dark:text-amber-400 text-amber-700 text-xs">{{ $battle->rank->name }}</span>
                                        @endif
                                        @if($battle->group)
                                        <span class="px-1.5 py-0.5 rounded dark:bg-emerald-600/30 bg-emerald-100 dark:text-emerald-400 text-emerald-700 text-xs">{{ $battle->group->name }}</span>
                                        @endif
                                        <span>{{ $battle->played_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                                <div class="hidden group-hover:flex items-center gap-2">
                                    <button
                                        type="button"
                                        @click="editBattle = {{ $battle->toJson() }}; showEditBattleModal = true"
                                        class="p-2 rounded-lg dark:text-gray-400 text-gray-500 dark:hover:bg-gray-700 hover:bg-gray-100 dark:hover:text-white hover:text-gray-900"
                                        title="編集"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                        </svg>
                                    </button>
                                    <form action="{{ route('battles.destroy', $battle) }}" method="POST" onsubmit="return confirm('削除しますか？')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 rounded-lg dark:text-gray-400 text-gray-500 dark:hover:bg-gray-700 hover:bg-gray-100 hover:text-red-400" title="削除">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            @if($battle->notes)
                            <div class="mt-3 pt-3 border-t dark:border-gray-700 border-gray-200">
                                <p class="text-sm dark:text-gray-400 text-gray-500">{{ $battle->notes }}</p>
                            </div>
                            @endif
                        </div>
                        @empty
                        <div class="flex flex-col items-center justify-center rounded-lg border border-dashed dark:border-gray-700 border-gray-300 dark:bg-gray-800/50 bg-gray-50 py-20 text-center">
                            <svg class="mx-auto h-12 w-12 dark:text-gray-500 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                            <h3 class="mt-4 text-lg font-medium dark:text-white text-gray-900">対戦記録がありません</h3>
                            <p class="mt-1 text-sm dark:text-gray-400 text-gray-500">最初の対戦を記録して、分析を始めましょう。</p>
                        </div>
                        @endforelse
                    </div>

                    <!-- Pagination & Per Page -->
                    <div class="mt-4 flex items-center justify-between flex-wrap gap-4">
                        <div class="flex-1">
                            @if($battles->hasPages())
                            {{ $battles->appends(['mode' => $gameMode?->code, 'per_page' => $perPage])->links() }}
                            @endif
                        </div>
                        <form action="{{ route('settings.per-page') }}" method="POST" class="flex items-center gap-2">
                            @csrf
                            @method('PUT')
                            <label class="text-sm dark:text-gray-400 text-gray-500">表示件数</label>
                            <select name="per_page" onchange="this.form.submit()"
                                    class="rounded-lg dark:bg-gray-900 bg-white border dark:border-gray-600 border-gray-300 text-sm dark:text-white text-gray-900 px-2 py-1.5 focus:border-purple-500 focus:ring-purple-500">
                                @foreach([10, 20, 50, 100] as $option)
                                <option value="{{ $option }}" {{ $perPage == $option ? 'selected' : '' }}>{{ $option }}件</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>
            </main>
        </div>

        <!-- Right Stats Panel -->
        <aside class="hidden lg:flex w-80 flex-shrink-0 border-l dark:border-gray-700 border-gray-200 dark:bg-gray-800/50 bg-white/50 flex-col overflow-hidden">
            <!-- Panel Tabs -->
            <div class="flex-shrink-0 flex border-b dark:border-gray-700 border-gray-200">
                <button @click="rightPanel = 'stats'" class="flex-1 px-4 py-3 text-sm font-medium transition-colors"
                    :class="rightPanel === 'stats' ? 'text-purple-500 border-b-2 border-purple-500 dark:bg-gray-800 bg-white' : 'dark:text-gray-400 text-gray-500 dark:hover:text-white hover:text-gray-900'">
                    統計
                </button>
                <button @click="rightPanel = 'deck'" class="flex-1 px-4 py-3 text-sm font-medium transition-colors"
                    :class="rightPanel === 'deck' ? 'text-purple-500 border-b-2 border-purple-500 dark:bg-gray-800 bg-white' : 'dark:text-gray-400 text-gray-500 dark:hover:text-white hover:text-gray-900'">
                    デッキ
                </button>
                <button @click="rightPanel = 'share'" class="flex-1 px-4 py-3 text-sm font-medium transition-colors"
                    :class="rightPanel === 'share' ? 'text-purple-500 border-b-2 border-purple-500 dark:bg-gray-800 bg-white' : 'dark:text-gray-400 text-gray-500 dark:hover:text-white hover:text-gray-900'">
                    共有
                </button>
            </div>

            <!-- Stats Panel Content -->
            <div class="flex-1 overflow-y-auto p-4 scrollbar-thin" x-show="rightPanel === 'stats'">
                <!-- Streak Card -->
                <div class="rounded-xl dark:bg-gradient-to-br dark:from-purple-900/40 dark:to-gray-800 bg-white border dark:border-purple-500/30 border-gray-200 p-4 mb-4">
                    <div class="text-sm text-purple-500 mb-1">{{ $stats['streak'] > 0 ? '連勝中' : '直近' }}</div>
                    <div class="text-4xl font-bold dark:text-white text-gray-900">{{ $stats['streak'] }}</div>
                </div>

                <!-- First/Second Stats -->
                <div class="mb-6">
                    <h3 class="text-sm font-medium dark:text-gray-400 text-gray-600 mb-3">先攻/後攻勝率（今日）</h3>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="rounded-lg dark:bg-gray-700/50 bg-gray-200 p-3 text-center">
                            <div class="text-xs text-blue-500 mb-1">先攻</div>
                            <div class="text-2xl font-bold dark:text-white text-gray-900">{{ $stats['byTurn']['first']['winRate'] }}%</div>
                            <div class="text-xs dark:text-gray-500 text-gray-600">{{ $stats['byTurn']['first']['wins'] }}-{{ $stats['byTurn']['first']['total'] - $stats['byTurn']['first']['wins'] }}</div>
                        </div>
                        <div class="rounded-lg dark:bg-gray-700/50 bg-gray-200 p-3 text-center">
                            <div class="text-xs text-orange-500 mb-1">後攻</div>
                            <div class="text-2xl font-bold dark:text-white text-gray-900">{{ $stats['byTurn']['second']['winRate'] }}%</div>
                            <div class="text-xs dark:text-gray-500 text-gray-600">{{ $stats['byTurn']['second']['wins'] }}-{{ $stats['byTurn']['second']['total'] - $stats['byTurn']['second']['wins'] }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Deck Panel Content -->
            <div class="flex-1 overflow-y-auto scrollbar-thin flex flex-col" x-show="rightPanel === 'deck'" style="display: none;">
                <!-- Header -->
                <div class="flex-shrink-0 flex items-center justify-between px-4 py-3 border-b dark:border-gray-700 border-gray-200">
                    <span class="text-sm font-medium dark:text-gray-400 text-gray-500">デッキ管理</span>
                    <button
                        @click="showCreateDeckModal = true"
                        class="flex items-center gap-1 px-2 py-1 rounded-lg bg-purple-600 text-white text-xs font-medium hover:bg-purple-500 transition-colors"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        新規
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-4 space-y-2">
                    @forelse($decks as $deck)
                    @php
                        $winRate = $deck->battles_count > 0
                            ? round(($deck->wins_count / $deck->battles_count) * 100, 1)
                            : 0;
                    @endphp
                    <div class="group rounded-lg dark:bg-gray-700/50 bg-gray-100 p-3 dark:hover:bg-gray-700 hover:bg-gray-200 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-lg bg-purple-600/20 flex items-center justify-center flex-shrink-0">
                                <span class="text-purple-500 font-bold text-sm">{{ mb_substr($deck->leaderClass->name, 0, 1) }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-medium dark:text-white text-gray-900 text-sm truncate">{{ $deck->name }}</h4>
                                <p class="text-xs dark:text-gray-400 text-gray-500">{{ $winRate }}% ({{ $deck->battles_count }}戦)</p>
                            </div>
                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button
                                    @click="editDeck = {{ $deck->toJson() }}; showEditDeckModal = true"
                                    class="p-1.5 rounded dark:text-gray-400 text-gray-500 dark:hover:bg-gray-600 hover:bg-gray-300 dark:hover:text-white hover:text-gray-900"
                                    title="編集"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                    </svg>
                                </button>
                                <form action="{{ route('decks.destroy', $deck) }}" method="POST" class="inline"
                                      onsubmit="return confirm('このデッキを削除しますか？')">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="p-1.5 rounded dark:text-gray-400 text-gray-500 dark:hover:bg-gray-600 hover:bg-gray-300 hover:text-red-400"
                                        title="削除"
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8">
                        <p class="dark:text-gray-500 text-gray-400 text-sm mb-3">デッキがありません</p>
                        <button
                            @click="showCreateDeckModal = true"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-purple-600 text-white text-sm font-medium hover:bg-purple-500 transition-colors"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            デッキを作成
                        </button>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Share Panel Content -->
            <div class="flex-1 overflow-y-auto scrollbar-thin flex flex-col" x-show="rightPanel === 'share'" style="display: none;">
                <!-- Username Setup -->
                @if(!Auth::user()->username)
                <div class="p-4 border-b dark:border-gray-700 border-gray-200 dark:bg-yellow-900/20 bg-yellow-50">
                    <div class="flex items-center gap-2 text-yellow-600 text-sm mb-2">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        ユーザー名が未設定です
                    </div>
                    <p class="text-xs dark:text-gray-400 text-gray-500 mb-3">共有リンクを作成するにはユーザー名の設定が必要です</p>
                    <button
                        @click="showUsernameModal = true"
                        class="w-full px-3 py-2 rounded-lg bg-yellow-600 text-white text-sm font-medium hover:bg-yellow-500 transition-colors"
                    >
                        ユーザー名を設定
                    </button>
                </div>
                @else
                <!-- Header -->
                <div class="flex-shrink-0 flex items-center justify-between px-4 py-3 border-b dark:border-gray-700 border-gray-200">
                    <div>
                        <span class="text-sm font-medium dark:text-gray-400 text-gray-500">共有リンク</span>
                        <div class="text-xs dark:text-gray-500 text-gray-400">{{ Auth::user()->username }}</div>
                    </div>
                    <button
                        @click="showCreateShareModal = true"
                        class="flex items-center gap-1 px-2 py-1 rounded-lg bg-purple-600 text-white text-xs font-medium hover:bg-purple-500 transition-colors"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        新規
                    </button>
                </div>
                @endif

                <div class="flex-1 overflow-y-auto p-4 space-y-2">
                    @forelse($shareLinks as $link)
                    <div class="rounded-lg dark:bg-gray-700/50 bg-gray-100 p-3 dark:hover:bg-gray-700 hover:bg-gray-200 transition-colors">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-medium dark:text-white text-gray-900 text-sm">{{ $link->name }}</h4>
                            <span class="px-2 py-0.5 rounded text-xs {{ $link->is_active ? 'bg-green-600/30 text-green-500' : 'dark:bg-gray-600/30 bg-gray-200 dark:text-gray-400 text-gray-500' }}">
                                {{ $link->is_active ? '公開中' : '非公開' }}
                            </span>
                        </div>
                        <div class="text-xs dark:text-gray-400 text-gray-500 mb-3">
                            {{ $link->start_date->format('Y/m/d') }} 〜 {{ $link->end_date->format('Y/m/d') }}
                        </div>
                        <div class="flex items-center gap-2">
                            @if($link->is_active && Auth::user()->username)
                            <button
                                onclick="navigator.clipboard.writeText('{{ url('/u/' . Auth::user()->username . '/' . $link->slug) }}').then(() => alert('URLをコピーしました'))"
                                class="flex-1 flex items-center justify-center gap-1 px-2 py-1.5 rounded dark:bg-gray-600 bg-gray-200 dark:text-gray-300 text-gray-700 text-xs dark:hover:bg-gray-500 hover:bg-gray-300 transition-colors"
                            >
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                                </svg>
                                URL
                            </button>
                            @endif
                            <form action="{{ route('shares.toggle', $link) }}" method="POST" class="inline">
                                @csrf
                                <button
                                    type="submit"
                                    class="px-2 py-1.5 rounded text-xs transition-colors {{ $link->is_active ? 'bg-orange-600/30 text-orange-500 hover:bg-orange-600/50' : 'bg-green-600/30 text-green-500 hover:bg-green-600/50' }}"
                                >
                                    {{ $link->is_active ? '非公開' : '公開' }}
                                </button>
                            </form>
                            <form action="{{ route('shares.destroy', $link) }}" method="POST" class="inline" onsubmit="return confirm('削除しますか？')">
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    class="p-1.5 rounded dark:text-gray-400 text-gray-500 dark:hover:bg-gray-600 hover:bg-gray-300 hover:text-red-400"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 dark:text-gray-600 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                        </svg>
                        <p class="dark:text-gray-500 text-gray-400 text-sm mb-3">共有リンクがありません</p>
                        @if(Auth::user()->username)
                        <button
                            @click="showCreateShareModal = true"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-purple-600 text-white text-sm font-medium hover:bg-purple-500 transition-colors"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            共有リンクを作成
                        </button>
                        @endif
                    </div>
                    @endforelse
                </div>
            </div>
        </aside>

        <!-- Create Deck Modal -->
        <div
            x-show="showCreateDeckModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/70"
            style="display: none;"
        >
            <div
                @click.away="showCreateDeckModal = false"
                class="w-full max-w-lg rounded-2xl dark:bg-gray-800 bg-white border dark:border-gray-700 border-gray-200 p-6 shadow-xl mx-4"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
            >
                <h2 class="text-lg font-semibold dark:text-white text-gray-900 mb-4">新規デッキ作成</h2>
                <form action="{{ route('decks.store') }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium dark:text-gray-400 text-gray-500 mb-2">デッキ名</label>
                            <input
                                type="text"
                                name="name"
                                required
                                class="w-full rounded-lg dark:bg-gray-700 bg-gray-100 dark:border-gray-600 border-gray-300 dark:text-white text-gray-900 px-4 py-2.5 focus:border-purple-500 focus:ring-purple-500"
                                placeholder="例: 秘術ウィッチ"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium dark:text-gray-400 text-gray-500 mb-2">クラス</label>
                            <div class="grid grid-cols-4 gap-2">
                                @foreach($leaderClasses as $class)
                                    <label class="relative cursor-pointer">
                                        <input type="radio" name="leader_class_id" value="{{ $class->id }}" class="peer sr-only" required>
                                        <div class="flex flex-col items-center p-3 rounded-lg dark:bg-gray-700 bg-gray-100 border-2 border-transparent peer-checked:border-purple-500 dark:hover:bg-gray-600 hover:bg-gray-200 transition-colors">
                                            <span class="text-sm dark:text-gray-300 text-gray-700">{{ $class->name }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button
                            type="button"
                            @click="showCreateDeckModal = false"
                            class="px-4 py-2 rounded-lg dark:bg-gray-700 bg-gray-200 dark:text-gray-300 text-gray-700 text-sm font-medium dark:hover:bg-gray-600 hover:bg-gray-300"
                        >
                            キャンセル
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 rounded-lg bg-purple-600 text-white text-sm font-medium hover:bg-purple-500"
                        >
                            作成
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Deck Modal -->
        <div
            x-show="showEditDeckModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/70"
            style="display: none;"
        >
            <div
                @click.away="showEditDeckModal = false; editDeck = null"
                class="w-full max-w-lg rounded-2xl dark:bg-gray-800 bg-white border dark:border-gray-700 border-gray-200 p-6 shadow-xl mx-4"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
            >
                <h2 class="text-lg font-semibold dark:text-white text-gray-900 mb-4">デッキ編集</h2>
                <form :action="editDeck ? '{{ url('decks') }}/' + editDeck.id : '#'" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium dark:text-gray-400 text-gray-500 mb-2">デッキ名</label>
                            <input
                                type="text"
                                name="name"
                                x-model="editDeck?.name"
                                required
                                class="w-full rounded-lg dark:bg-gray-700 bg-gray-100 dark:border-gray-600 border-gray-300 dark:text-white text-gray-900 px-4 py-2.5 focus:border-purple-500 focus:ring-purple-500"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium dark:text-gray-400 text-gray-500 mb-2">クラス</label>
                            <div class="grid grid-cols-4 gap-2">
                                @foreach($leaderClasses as $class)
                                    <label class="relative cursor-pointer">
                                        <input
                                            type="radio"
                                            name="leader_class_id"
                                            value="{{ $class->id }}"
                                            class="peer sr-only"
                                            required
                                            :checked="editDeck?.leader_class_id == {{ $class->id }}"
                                        >
                                        <div class="flex flex-col items-center p-3 rounded-lg dark:bg-gray-700 bg-gray-100 border-2 border-transparent peer-checked:border-purple-500 dark:hover:bg-gray-600 hover:bg-gray-200 transition-colors">
                                            <span class="text-sm dark:text-gray-300 text-gray-700">{{ $class->name }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button
                            type="button"
                            @click="showEditDeckModal = false; editDeck = null"
                            class="px-4 py-2 rounded-lg dark:bg-gray-700 bg-gray-200 dark:text-gray-300 text-gray-700 text-sm font-medium dark:hover:bg-gray-600 hover:bg-gray-300"
                        >
                            キャンセル
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 rounded-lg bg-purple-600 text-white text-sm font-medium hover:bg-purple-500"
                        >
                            保存
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Create Share Link Modal -->
        <div
            x-show="showCreateShareModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/70"
            style="display: none;"
        >
            <div
                @click.away="showCreateShareModal = false"
                class="w-full max-w-lg rounded-2xl dark:bg-gray-800 bg-white border dark:border-gray-700 border-gray-200 p-6 shadow-xl mx-4"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
            >
                <h2 class="text-lg font-semibold dark:text-white text-gray-900 mb-4">共有リンク作成</h2>
                <form action="{{ route('shares.store') }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium dark:text-gray-400 text-gray-500 mb-2">リンク名</label>
                            <input
                                type="text"
                                name="name"
                                required
                                class="w-full rounded-lg dark:bg-gray-700 bg-gray-100 dark:border-gray-600 border-gray-300 dark:text-white text-gray-900 px-4 py-2.5 focus:border-purple-500 focus:ring-purple-500"
                                placeholder="例: グランドマスター挑戦"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium dark:text-gray-400 text-gray-500 mb-2">スラッグ（URL用）</label>
                            <div class="flex items-center gap-2">
                                <span class="dark:text-gray-500 text-gray-400 text-sm">/u/{{ Auth::user()->username ?? 'username' }}/</span>
                                <input
                                    type="text"
                                    name="slug"
                                    required
                                    pattern="[a-z0-9-]+"
                                    class="flex-1 rounded-lg dark:bg-gray-700 bg-gray-100 dark:border-gray-600 border-gray-300 dark:text-white text-gray-900 px-4 py-2.5 focus:border-purple-500 focus:ring-purple-500"
                                    placeholder="grandmaster-run"
                                >
                            </div>
                            <p class="text-xs dark:text-gray-500 text-gray-400 mt-1">半角英小文字・数字・ハイフンのみ</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium dark:text-gray-400 text-gray-500 mb-2">開始日</label>
                                <input
                                    type="date"
                                    name="start_date"
                                    required
                                    class="w-full rounded-lg dark:bg-gray-700 bg-gray-100 dark:border-gray-600 border-gray-300 dark:text-white text-gray-900 px-4 py-2.5 focus:border-purple-500 focus:ring-purple-500"
                                >
                            </div>
                            <div>
                                <label class="block text-sm font-medium dark:text-gray-400 text-gray-500 mb-2">終了日</label>
                                <input
                                    type="date"
                                    name="end_date"
                                    required
                                    class="w-full rounded-lg dark:bg-gray-700 bg-gray-100 dark:border-gray-600 border-gray-300 dark:text-white text-gray-900 px-4 py-2.5 focus:border-purple-500 focus:ring-purple-500"
                                >
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button
                            type="button"
                            @click="showCreateShareModal = false"
                            class="px-4 py-2 rounded-lg dark:bg-gray-700 bg-gray-200 dark:text-gray-300 text-gray-700 text-sm font-medium dark:hover:bg-gray-600 hover:bg-gray-300"
                        >
                            キャンセル
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 rounded-lg bg-purple-600 text-white text-sm font-medium hover:bg-purple-500"
                        >
                            作成
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Username Setup Modal -->
        <div
            x-show="showUsernameModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/70"
            style="display: none;"
        >
            <div
                @click.away="showUsernameModal = false"
                class="w-full max-w-md rounded-2xl dark:bg-gray-800 bg-white border dark:border-gray-700 border-gray-200 p-6 shadow-xl mx-4"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
            >
                <h2 class="text-lg font-semibold dark:text-white text-gray-900 mb-4">ユーザー名を設定</h2>
                <p class="text-sm dark:text-gray-400 text-gray-500 mb-4">共有URLに使用されます。一度設定すると変更できません。</p>
                <form action="{{ route('profile.username.update') }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium dark:text-gray-400 text-gray-500 mb-2">ユーザー名</label>
                            <div class="flex items-center gap-2">
                                <span class="dark:text-gray-500 text-gray-400 text-sm">/u/</span>
                                <input
                                    type="text"
                                    name="username"
                                    required
                                    pattern="[a-z0-9-]+"
                                    minlength="3"
                                    maxlength="50"
                                    class="flex-1 rounded-lg dark:bg-gray-700 bg-gray-100 dark:border-gray-600 border-gray-300 dark:text-white text-gray-900 px-4 py-2.5 focus:border-purple-500 focus:ring-purple-500"
                                    placeholder="your-username"
                                >
                            </div>
                            <p class="text-xs dark:text-gray-500 text-gray-400 mt-1">半角英小文字・数字・ハイフンのみ（3〜50文字）</p>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button
                            type="button"
                            @click="showUsernameModal = false"
                            class="px-4 py-2 rounded-lg dark:bg-gray-700 bg-gray-200 dark:text-gray-300 text-gray-700 text-sm font-medium dark:hover:bg-gray-600 hover:bg-gray-300"
                        >
                            キャンセル
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 rounded-lg bg-purple-600 text-white text-sm font-medium hover:bg-purple-500"
                        >
                            設定
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Battle Modal -->
        <div
            x-show="showEditBattleModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/70"
            style="display: none;"
        >
            <div
                @click.away="showEditBattleModal = false; editBattle = null"
                class="w-full max-w-lg rounded-2xl dark:bg-gray-800 bg-white border dark:border-gray-700 border-gray-200 p-6 shadow-xl mx-4 max-h-[90vh] overflow-y-auto"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
            >
                <h2 class="text-lg font-semibold dark:text-white text-gray-900 mb-4">対戦記録を編集</h2>
                <form :action="editBattle ? '{{ url('battles') }}/' + editBattle.id : '#'" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="space-y-4">
                        <!-- 勝敗 -->
                        <div>
                            <label class="block text-sm font-medium dark:text-gray-400 text-gray-600 mb-2">結果</label>
                            <div class="flex gap-3">
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" name="result" value="1" class="peer sr-only" :checked="editBattle?.result == true || editBattle?.result == 1">
                                    <div class="p-3 rounded-lg border-2 dark:border-gray-600 border-gray-300 peer-checked:border-green-500 peer-checked:bg-green-500/10 text-center transition-colors">
                                        <span class="font-medium dark:text-white text-gray-900">WIN</span>
                                    </div>
                                </label>
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" name="result" value="0" class="peer sr-only" :checked="editBattle?.result == false || editBattle?.result == 0">
                                    <div class="p-3 rounded-lg border-2 dark:border-gray-600 border-gray-300 peer-checked:border-red-500 peer-checked:bg-red-500/10 text-center transition-colors">
                                        <span class="font-medium dark:text-white text-gray-900">LOSE</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- 先攻/後攻 -->
                        <div>
                            <label class="block text-sm font-medium dark:text-gray-400 text-gray-600 mb-2">先攻/後攻</label>
                            <div class="flex gap-3">
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" name="is_first" value="1" class="peer sr-only" :checked="editBattle?.is_first == true || editBattle?.is_first == 1">
                                    <div class="p-3 rounded-lg border-2 dark:border-gray-600 border-gray-300 peer-checked:border-blue-500 peer-checked:bg-blue-500/10 text-center transition-colors">
                                        <span class="font-medium dark:text-white text-gray-900">先攻</span>
                                    </div>
                                </label>
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" name="is_first" value="0" class="peer sr-only" :checked="editBattle?.is_first == false || editBattle?.is_first == 0">
                                    <div class="p-3 rounded-lg border-2 dark:border-gray-600 border-gray-300 peer-checked:border-orange-500 peer-checked:bg-orange-500/10 text-center transition-colors">
                                        <span class="font-medium dark:text-white text-gray-900">後攻</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- 相手クラス -->
                        <div>
                            <label class="block text-sm font-medium dark:text-gray-400 text-gray-600 mb-2">相手クラス</label>
                            <div class="grid grid-cols-4 gap-2">
                                @foreach($leaderClasses as $class)
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="opponent_class_id" value="{{ $class->id }}" class="peer sr-only" :checked="editBattle?.opponent_class_id == {{ $class->id }}">
                                    <div class="flex flex-col items-center p-2 rounded-lg dark:bg-gray-700 bg-gray-100 border-2 border-transparent peer-checked:border-purple-500 dark:hover:bg-gray-600 hover:bg-gray-200 transition-colors">
                                        <span class="text-xs dark:text-gray-300 text-gray-700">{{ $class->name }}</span>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- デッキ（2Pick以外） -->
                        <div x-show="editBattle?.deck_id">
                            <label class="block text-sm font-medium dark:text-gray-400 text-gray-600 mb-2">デッキ</label>
                            <select name="deck_id" class="w-full rounded-lg dark:bg-gray-700 bg-gray-100 dark:border-gray-600 border-gray-300 dark:text-white text-gray-900 px-4 py-2.5 focus:border-purple-500 focus:ring-purple-500">
                                @foreach($decks as $deck)
                                <option value="{{ $deck->id }}" :selected="editBattle?.deck_id == {{ $deck->id }}">{{ $deck->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- 自クラス（2Pick用） -->
                        <div x-show="editBattle?.my_class_id && !editBattle?.deck_id">
                            <label class="block text-sm font-medium dark:text-gray-400 text-gray-600 mb-2">自分のクラス</label>
                            <div class="grid grid-cols-4 gap-2">
                                @foreach($leaderClasses as $class)
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="my_class_id" value="{{ $class->id }}" class="peer sr-only" :checked="editBattle?.my_class_id == {{ $class->id }}">
                                    <div class="flex flex-col items-center p-2 rounded-lg dark:bg-gray-700 bg-gray-100 border-2 border-transparent peer-checked:border-purple-500 dark:hover:bg-gray-600 hover:bg-gray-200 transition-colors">
                                        <span class="text-xs dark:text-gray-300 text-gray-700">{{ $class->name }}</span>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- ランク -->
                        <div x-show="editBattle?.rank_id">
                            <label class="block text-sm font-medium dark:text-gray-400 text-gray-600 mb-2">ランク</label>
                            <select name="rank_id" class="w-full rounded-lg dark:bg-gray-700 bg-gray-100 dark:border-gray-600 border-gray-300 dark:text-white text-gray-900 px-4 py-2.5 focus:border-purple-500 focus:ring-purple-500">
                                <option value="">未選択</option>
                                @foreach($ranks as $rank)
                                <option value="{{ $rank->id }}" :selected="editBattle?.rank_id == {{ $rank->id }}">{{ $rank->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- グループ -->
                        <div x-show="editBattle?.group_id">
                            <label class="block text-sm font-medium dark:text-gray-400 text-gray-600 mb-2">グループ</label>
                            <select name="group_id" class="w-full rounded-lg dark:bg-gray-700 bg-gray-100 dark:border-gray-600 border-gray-300 dark:text-white text-gray-900 px-4 py-2.5 focus:border-purple-500 focus:ring-purple-500">
                                <option value="">未選択</option>
                                @foreach($groups as $group)
                                <option value="{{ $group->id }}" :selected="editBattle?.group_id == {{ $group->id }}">{{ $group->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- 備考 -->
                        <div>
                            <label class="block text-sm font-medium dark:text-gray-400 text-gray-600 mb-2">備考</label>
                            <textarea
                                name="notes"
                                rows="2"
                                class="w-full rounded-lg dark:bg-gray-700 bg-gray-100 dark:border-gray-600 border-gray-300 dark:text-white text-gray-900 px-4 py-2.5 focus:border-purple-500 focus:ring-purple-500"
                                placeholder="メモを残す（任意）"
                                x-model="editBattle ? editBattle.notes : ''"
                            ></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button
                            type="button"
                            @click="showEditBattleModal = false; editBattle = null"
                            class="px-4 py-2 rounded-lg dark:bg-gray-700 bg-gray-200 dark:text-gray-300 text-gray-700 text-sm font-medium dark:hover:bg-gray-600 hover:bg-gray-300"
                        >
                            キャンセル
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 rounded-lg bg-purple-600 text-white text-sm font-medium hover:bg-purple-500"
                        >
                            保存
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
