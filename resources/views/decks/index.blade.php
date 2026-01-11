<x-layouts.app>
    <x-slot name="title">デッキ管理 - Shadova Log</x-slot>

    <!-- Main Content Area (Center) -->
    <div class="flex-1 flex flex-col min-w-0"
         x-data="{
             showCreateModal: false,
             showEditModal: false,
             editDeck: null,
             newDeckName: '',
             newDeckClass: '',
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
        <!-- Top Bar -->
        <header class="flex-shrink-0 border-b border-gray-700 bg-gray-800/50">
            <div class="flex items-center justify-between h-14 px-4">
                <div class="flex items-center gap-4">
                    <h1 class="text-lg font-semibold text-white">デッキ管理</h1>
                    <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-lg bg-gray-700/50 text-sm">
                        <span class="text-gray-400">登録数:</span>
                        <span class="text-purple-400 font-medium">{{ $decks->count() }} デッキ</span>
                    </div>
                </div>
                <button
                    @click="showCreateModal = true"
                    class="flex items-center gap-2 px-4 py-2 rounded-lg bg-purple-600 text-white text-sm font-medium hover:bg-purple-500 transition-colors"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    新規デッキ
                </button>
            </div>
        </header>

        <!-- Scrollable Content -->
        <main class="flex-1 overflow-y-auto scrollbar-thin p-4">
            @if($decks->isEmpty())
                <!-- Empty State -->
                <div class="flex flex-col items-center justify-center h-full text-center">
                    <div class="h-24 w-24 rounded-full bg-gray-800 flex items-center justify-center mb-4">
                        <svg class="h-12 w-12 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="4" y="4" width="12" height="16" rx="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 2h10a2 2 0 012 2v14"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-semibold text-white mb-2">デッキがありません</h2>
                    <p class="text-gray-400 mb-6">対戦記録を始めるには、まずデッキを作成してください</p>
                    <button
                        @click="showCreateModal = true"
                        class="flex items-center gap-2 px-6 py-3 rounded-xl bg-purple-600 text-white font-medium hover:bg-purple-500 transition-colors"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        最初のデッキを作成
                    </button>
                </div>
            @else
                <!-- Active Decks Section -->
                @php
                    $activeDecks = $decks->where('active', true);
                    $inactiveDecks = $decks->where('active', false);
                @endphp

                @if($activeDecks->isNotEmpty())
                    <div class="mb-8">
                        <h2 class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-4">アクティブなデッキ</h2>
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($activeDecks as $deck)
                                @php
                                    $winRate = $deck->battles_count > 0
                                        ? round(($deck->wins_count / $deck->battles_count) * 100, 1)
                                        : 0;
                                    $classId = $deck->leader_class_id;
                                @endphp
                                <div class="group rounded-xl bg-gray-800 border border-gray-700 p-4 hover:border-purple-500/50 transition-all">
                                    <div class="flex items-start gap-4">
                                        <div class="h-14 w-14 rounded-xl flex items-center justify-center"
                                             :class="classColors[{{ $classId }}]?.bg || 'bg-gray-600/20'">
                                            <span class="text-xl font-bold"
                                                  :class="classColors[{{ $classId }}]?.text || 'text-gray-400'"
                                                  x-text="classColors[{{ $classId }}]?.label || '?'"></span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h3 class="font-semibold text-white truncate">{{ $deck->name }}</h3>
                                            <p class="text-sm text-gray-400">{{ $deck->leaderClass->name }}</p>
                                        </div>
                                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button
                                                @click="editDeck = {{ $deck->toJson() }}; showEditModal = true"
                                                class="p-2 rounded-lg text-gray-400 hover:bg-gray-700 hover:text-white"
                                                title="編集"
                                            >
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                </svg>
                                            </button>
                                            <form action="{{ route('decks.toggle-active', $deck) }}" method="POST" class="inline">
                                                @csrf
                                                <button
                                                    type="submit"
                                                    class="p-2 rounded-lg text-gray-400 hover:bg-gray-700 hover:text-yellow-400"
                                                    title="非アクティブにする"
                                                >
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="mt-4 grid grid-cols-3 gap-2 text-center">
                                        <div class="rounded-lg bg-gray-700/50 p-2">
                                            <div class="text-xs text-gray-400">勝率</div>
                                            <div class="text-lg font-bold {{ $winRate >= 50 ? 'text-green-400' : ($winRate > 0 ? 'text-red-400' : 'text-gray-400') }}">
                                                {{ $winRate }}%
                                            </div>
                                        </div>
                                        <div class="rounded-lg bg-gray-700/50 p-2">
                                            <div class="text-xs text-gray-400">勝ち</div>
                                            <div class="text-lg font-bold text-green-400">{{ $deck->wins_count }}</div>
                                        </div>
                                        <div class="rounded-lg bg-gray-700/50 p-2">
                                            <div class="text-xs text-gray-400">総戦績</div>
                                            <div class="text-lg font-bold text-white">{{ $deck->battles_count }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Inactive Decks Section -->
                @if($inactiveDecks->isNotEmpty())
                    <div>
                        <h2 class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-4">非アクティブなデッキ</h2>
                        <div class="space-y-2">
                            @foreach($inactiveDecks as $deck)
                                @php
                                    $winRate = $deck->battles_count > 0
                                        ? round(($deck->wins_count / $deck->battles_count) * 100, 1)
                                        : 0;
                                    $classId = $deck->leader_class_id;
                                @endphp
                                <div class="group rounded-lg bg-gray-800/50 border border-gray-700/50 p-3 hover:border-gray-600 transition-all">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-lg flex items-center justify-center opacity-60"
                                             :class="classColors[{{ $classId }}]?.bg || 'bg-gray-600/20'">
                                            <span class="text-sm font-bold"
                                                  :class="classColors[{{ $classId }}]?.text || 'text-gray-400'"
                                                  x-text="classColors[{{ $classId }}]?.label || '?'"></span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h3 class="font-medium text-gray-400 truncate">{{ $deck->name }}</h3>
                                            <p class="text-xs text-gray-500">{{ $deck->leaderClass->name }} / {{ $winRate }}% ({{ $deck->battles_count }}戦)</p>
                                        </div>
                                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <form action="{{ route('decks.toggle-active', $deck) }}" method="POST" class="inline">
                                                @csrf
                                                <button
                                                    type="submit"
                                                    class="p-2 rounded-lg text-gray-400 hover:bg-gray-700 hover:text-green-400"
                                                    title="アクティブにする"
                                                >
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                </button>
                                            </form>
                                            @if($deck->battles_count == 0)
                                                <form action="{{ route('decks.destroy', $deck) }}" method="POST" class="inline"
                                                      onsubmit="return confirm('このデッキを削除しますか？')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button
                                                        type="submit"
                                                        class="p-2 rounded-lg text-gray-400 hover:bg-gray-700 hover:text-red-400"
                                                        title="削除"
                                                    >
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif
        </main>

        <!-- Create Deck Modal -->
        <div
            x-show="showCreateModal"
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
                @click.away="showCreateModal = false"
                class="w-full max-w-md rounded-2xl bg-gray-800 border border-gray-700 p-6 shadow-xl mx-4"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
            >
                <h2 class="text-lg font-semibold text-white mb-4">新規デッキ作成</h2>
                <form action="{{ route('decks.store') }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-2">デッキ名</label>
                            <input
                                type="text"
                                name="name"
                                required
                                class="w-full rounded-lg bg-gray-700 border-gray-600 text-white px-4 py-2.5 focus:border-purple-500 focus:ring-purple-500"
                                placeholder="例: 秘術ウィッチ"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-2">クラス</label>
                            <div class="grid grid-cols-4 gap-2">
                                @foreach($leaderClasses as $class)
                                    <label class="relative cursor-pointer">
                                        <input type="radio" name="leader_class_id" value="{{ $class->id }}" class="peer sr-only" required>
                                        <div class="flex flex-col items-center p-3 rounded-lg bg-gray-700 border-2 border-transparent peer-checked:border-purple-500 hover:bg-gray-600 transition-colors">
                                            <span class="text-sm text-gray-300">{{ $class->name }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button
                            type="button"
                            @click="showCreateModal = false"
                            class="px-4 py-2 rounded-lg bg-gray-700 text-gray-300 text-sm font-medium hover:bg-gray-600"
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
            x-show="showEditModal"
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
                @click.away="showEditModal = false; editDeck = null"
                class="w-full max-w-md rounded-2xl bg-gray-800 border border-gray-700 p-6 shadow-xl mx-4"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
            >
                <h2 class="text-lg font-semibold text-white mb-4">デッキ編集</h2>
                <form :action="editDeck ? '{{ url('decks') }}/' + editDeck.id : '#'" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-2">デッキ名</label>
                            <input
                                type="text"
                                name="name"
                                x-model="editDeck?.name"
                                required
                                class="w-full rounded-lg bg-gray-700 border-gray-600 text-white px-4 py-2.5 focus:border-purple-500 focus:ring-purple-500"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-2">クラス</label>
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
                                        <div class="flex flex-col items-center p-3 rounded-lg bg-gray-700 border-2 border-transparent peer-checked:border-purple-500 hover:bg-gray-600 transition-colors">
                                            <span class="text-sm text-gray-300">{{ $class->name }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button
                            type="button"
                            @click="showEditModal = false; editDeck = null"
                            class="px-4 py-2 rounded-lg bg-gray-700 text-gray-300 text-sm font-medium hover:bg-gray-600"
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

    <!-- Right Stats Panel -->
    <aside class="w-80 flex-shrink-0 border-l border-gray-700 bg-gray-800/50 hidden lg:flex flex-col overflow-hidden">
        <div class="flex-shrink-0 flex items-center h-14 px-4 border-b border-gray-700">
            <h2 class="text-sm font-medium text-gray-400">デッキ統計</h2>
        </div>
        <div class="flex-1 overflow-y-auto p-4 scrollbar-thin">
            @if($decks->isNotEmpty())
                <!-- Overall Stats -->
                <div class="mb-6">
                    <h3 class="text-sm font-medium text-gray-400 mb-3">全体統計</h3>
                    @php
                        $totalBattles = $decks->sum('battles_count');
                        $totalWins = $decks->sum('wins_count');
                        $overallWinRate = $totalBattles > 0 ? round(($totalWins / $totalBattles) * 100, 1) : 0;
                    @endphp
                    <div class="rounded-lg bg-gray-700/50 p-4">
                        <div class="flex justify-between items-baseline mb-2">
                            <span class="text-sm text-gray-400">総合勝率</span>
                            <span class="text-xl font-bold {{ $overallWinRate >= 50 ? 'text-green-400' : 'text-red-400' }}">{{ $overallWinRate }}%</span>
                        </div>
                        <div class="h-2 bg-gray-600 rounded-full overflow-hidden mb-2">
                            <div class="h-full bg-green-500 rounded-full" style="width: {{ $overallWinRate }}%"></div>
                        </div>
                        <div class="flex justify-between text-xs text-gray-500">
                            <span>{{ $totalWins }}勝</span>
                            <span>{{ $totalBattles }}戦</span>
                            <span>{{ $totalBattles - $totalWins }}敗</span>
                        </div>
                    </div>
                </div>

                <!-- Top Decks -->
                <div class="mb-6">
                    <h3 class="text-sm font-medium text-gray-400 mb-3">勝率ランキング</h3>
                    <div class="space-y-2">
                        @foreach($decks->filter(fn($d) => $d->battles_count >= 5)->sortByDesc(fn($d) => $d->battles_count > 0 ? $d->wins_count / $d->battles_count : 0)->take(5) as $deck)
                            @php
                                $deckWinRate = $deck->battles_count > 0
                                    ? round(($deck->wins_count / $deck->battles_count) * 100, 1)
                                    : 0;
                            @endphp
                            <div class="flex items-center justify-between p-2 rounded-lg bg-gray-700/50">
                                <span class="text-sm text-gray-300 truncate flex-1">{{ $deck->name }}</span>
                                <span class="text-sm font-medium {{ $deckWinRate >= 50 ? 'text-green-400' : 'text-red-400' }} ml-2">
                                    {{ $deckWinRate }}%
                                </span>
                            </div>
                        @endforeach
                        @if($decks->filter(fn($d) => $d->battles_count >= 5)->isEmpty())
                            <p class="text-sm text-gray-500 text-center py-4">5戦以上のデッキがありません</p>
                        @endif
                    </div>
                </div>

                <!-- Class Distribution -->
                <div>
                    <h3 class="text-sm font-medium text-gray-400 mb-3">クラス分布</h3>
                    <div class="space-y-2">
                        @foreach($leaderClasses as $class)
                            @php
                                $classDecks = $decks->where('leader_class_id', $class->id);
                                $classBattles = $classDecks->sum('battles_count');
                            @endphp
                            @if($classBattles > 0)
                                <div class="flex items-center justify-between p-2 rounded-lg bg-gray-700/50">
                                    <span class="text-sm text-gray-300">{{ $class->name }}</span>
                                    <span class="text-sm text-gray-400">{{ $classBattles }}戦</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @else
                <div class="text-center py-8">
                    <p class="text-gray-500 text-sm">デッキを作成すると統計が表示されます</p>
                </div>
            @endif
        </div>
    </aside>
</x-layouts.app>
