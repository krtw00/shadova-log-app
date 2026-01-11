<x-layouts.app>
    <x-slot:title>配信者モード - Shadova Log</x-slot:title>

    <div class="flex-1 flex flex-col min-w-0" x-data="{
        showStartSessionModal: false,
        showResetStreakModal: false,
        sessionName: '',
        streakStart: 0
    }">
        <!-- Top Bar -->
        <header class="flex-shrink-0 border-b dark:border-gray-700 border-gray-200 dark:bg-gray-800/50 bg-white/50">
            <div class="flex items-center h-14 px-4">
                <h1 class="text-lg font-semibold dark:text-white text-gray-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    配信者モード
                </h1>
            </div>
        </header>

        <!-- Scrollable Content -->
        <main class="flex-1 overflow-y-auto scrollbar-thin p-4 md:p-6">
            <div class="max-w-4xl mx-auto space-y-6">

                <!-- Flash Messages -->
                @if(session('success'))
                <div class="p-4 rounded-lg bg-green-600/20 border border-green-500/50 text-green-600 dark:text-green-400">
                    {{ session('success') }}
                </div>
                @endif

                @if(session('error'))
                <div class="p-4 rounded-lg bg-red-600/20 border border-red-500/50 text-red-600 dark:text-red-400">
                    {{ session('error') }}
                </div>
                @endif

                <!-- Stats Count Control -->
                <section class="glass-card rounded-xl p-6">
                    <h2 class="text-lg font-semibold dark:text-white text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        戦績カウント
                    </h2>

                    @if($activeSession)
                    <div class="mb-4 p-4 rounded-lg dark:bg-green-900/30 bg-green-100 border dark:border-green-700 border-green-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="inline-flex items-center gap-2 dark:text-green-400 text-green-700 font-medium">
                                    <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                                    カウント中: {{ $activeSession->name ?? '配信' }}
                                </span>
                                <p class="text-sm dark:text-green-300 text-green-600 mt-1">
                                    開始: {{ $activeSession->started_at->format('H:i') }}
                                    ({{ $activeSession->started_at->diffForHumans() }})
                                </p>
                            </div>
                            <form action="{{ route('streamer.session.end') }}" method="POST">
                                @csrf
                                <button type="submit" class="px-4 py-2 rounded-lg bg-red-600 hover:bg-red-500 text-white font-medium transition-colors">
                                    カウント終了
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Reset Streak Button -->
                    <button type="button" @click="showResetStreakModal = true"
                            class="w-full px-4 py-2 rounded-lg dark:bg-gray-700 bg-gray-200 dark:hover:bg-gray-600 hover:bg-gray-300 dark:text-white text-gray-900 font-medium transition-colors">
                        連勝カウンターをリセット
                    </button>
                    @else
                    <p class="dark:text-gray-400 text-gray-600 mb-4">
                        戦績カウントを開始すると、その時点からの勝敗を記録します。配信ごとに戦績を分けて管理できます。
                    </p>
                    <button type="button" @click="showStartSessionModal = true"
                            class="w-full px-4 py-2 rounded-lg bg-purple-600 hover:bg-purple-500 text-white font-medium transition-colors">
                        戦績カウントを開始
                    </button>
                    @endif
                </section>

                <!-- Overlay -->
                <section class="glass-card rounded-xl p-6">
                    <h2 class="text-lg font-semibold dark:text-white text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        オーバーレイ
                    </h2>

                    <p class="dark:text-gray-400 text-gray-600 mb-4">
                        OBSなどの配信ソフトでブラウザソースとして使用できるオーバーレイです。
                    </p>

                    @php
                        // フォントサイズに応じた幅
                        $overlayWidth = match($setting->overlay_font_size) {
                            'small' => 280,
                            'medium' => 300,
                            'large' => 360,
                            'xlarge' => 450,
                            default => 300,
                        };

                        // 高さの計算（表示項目に応じて）
                        $baseHeight = 80; // ヘッダー + パディング
                        $rowHeight = match($setting->overlay_font_size) {
                            'small' => 20,
                            'medium' => 24,
                            'large' => 30,
                            'xlarge' => 38,
                            default => 24,
                        };

                        // 勝率リング + 勝敗数の高さ
                        if ($setting->overlay_show_winrate || $setting->overlay_show_record) {
                            $baseHeight += match($setting->overlay_font_size) {
                                'small' => 80,
                                'medium' => 90,
                                'large' => 110,
                                'xlarge' => 150,
                                default => 90,
                            };
                        }

                        // デッキ情報
                        if ($setting->overlay_show_deck) {
                            $baseHeight += $rowHeight + 20;
                        }

                        // 対戦ログ
                        if ($setting->overlay_show_log) {
                            $baseHeight += ($rowHeight * min($setting->overlay_log_count, 5)) + 30;
                        }

                        $overlayHeight = $baseHeight;
                    @endphp
                    <div class="space-y-3">
                        <button type="button"
                                onclick="window.open('{{ route('streamer.overlay') }}', 'overlay', 'width={{ $overlayWidth }},height={{ $overlayHeight }},resizable=yes')"
                                class="w-full px-4 py-2 rounded-lg bg-purple-600 hover:bg-purple-500 text-white font-medium transition-colors flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                            オーバーレイを開く
                        </button>

                        <div class="p-3 rounded-lg dark:bg-gray-900 bg-gray-100">
                            <label class="block text-sm dark:text-gray-400 text-gray-600 mb-2">OBS用URL（ブラウザソースに貼り付け）</label>
                            <div class="flex gap-2">
                                <input type="text" readonly value="{{ route('streamer.overlay') }}"
                                       id="overlay-url"
                                       class="flex-1 px-3 py-2 rounded-lg dark:bg-gray-800 bg-white border dark:border-gray-600 border-gray-300 dark:text-white text-gray-900 text-sm">
                                <button type="button"
                                        onclick="navigator.clipboard.writeText(document.getElementById('overlay-url').value); this.textContent = 'コピー済み'; setTimeout(() => this.textContent = 'コピー', 2000)"
                                        class="px-4 py-2 rounded-lg dark:bg-gray-700 bg-gray-200 dark:hover:bg-gray-600 hover:bg-gray-300 dark:text-white text-gray-900 font-medium transition-colors">
                                    コピー
                                </button>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Overlay Settings -->
                <section class="glass-card rounded-xl p-6">
                    <h2 class="text-lg font-semibold dark:text-white text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                        </svg>
                        オーバーレイ設定
                    </h2>

                    <form action="{{ route('streamer.overlay.settings') }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <!-- Display Options -->
                        <div>
                            <label class="block text-sm dark:text-gray-400 text-gray-600 mb-2">表示項目</label>
                            <div class="space-y-2">
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="hidden" name="overlay_show_winrate" value="0">
                                    <input type="checkbox" name="overlay_show_winrate" value="1"
                                           {{ $setting->overlay_show_winrate ? 'checked' : '' }}
                                           class="rounded dark:border-gray-600 border-gray-300 dark:bg-gray-900 bg-white text-purple-600 focus:ring-purple-500">
                                    <span class="dark:text-white text-gray-900">勝率</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="hidden" name="overlay_show_record" value="0">
                                    <input type="checkbox" name="overlay_show_record" value="1"
                                           {{ $setting->overlay_show_record ? 'checked' : '' }}
                                           class="rounded dark:border-gray-600 border-gray-300 dark:bg-gray-900 bg-white text-purple-600 focus:ring-purple-500">
                                    <span class="dark:text-white text-gray-900">勝敗数 (○勝○敗)</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="hidden" name="overlay_show_streak" value="0">
                                    <input type="checkbox" name="overlay_show_streak" value="1"
                                           {{ $setting->overlay_show_streak ? 'checked' : '' }}
                                           class="rounded dark:border-gray-600 border-gray-300 dark:bg-gray-900 bg-white text-purple-600 focus:ring-purple-500">
                                    <span class="dark:text-white text-gray-900">連勝数</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="hidden" name="overlay_show_deck" value="0">
                                    <input type="checkbox" name="overlay_show_deck" value="1"
                                           {{ $setting->overlay_show_deck ? 'checked' : '' }}
                                           class="rounded dark:border-gray-600 border-gray-300 dark:bg-gray-900 bg-white text-purple-600 focus:ring-purple-500">
                                    <span class="dark:text-white text-gray-900">使用デッキ</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="hidden" name="overlay_show_log" value="0">
                                    <input type="checkbox" name="overlay_show_log" value="1"
                                           {{ $setting->overlay_show_log ? 'checked' : '' }}
                                           class="rounded dark:border-gray-600 border-gray-300 dark:bg-gray-900 bg-white text-purple-600 focus:ring-purple-500">
                                    <span class="dark:text-white text-gray-900">対戦ログ</span>
                                </label>
                            </div>
                        </div>

                        <!-- Log Count -->
                        <div>
                            <label class="block text-sm dark:text-gray-400 text-gray-600 mb-2">対戦ログ表示件数</label>
                            <select name="overlay_log_count"
                                    class="w-full px-4 py-2 rounded-lg dark:bg-gray-900 bg-white border dark:border-gray-600 border-gray-300 dark:text-white text-gray-900 focus:border-purple-500 focus:ring-1 focus:ring-purple-500">
                                @foreach([3, 5, 10, 15, 20] as $count)
                                <option value="{{ $count }}" {{ $setting->overlay_log_count == $count ? 'selected' : '' }}>
                                    {{ $count }}件
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Background -->
                        <div>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="hidden" name="overlay_bg_transparent" value="0">
                                <input type="checkbox" name="overlay_bg_transparent" value="1"
                                       {{ $setting->overlay_bg_transparent ? 'checked' : '' }}
                                       class="rounded dark:border-gray-600 border-gray-300 dark:bg-gray-900 bg-white text-purple-600 focus:ring-purple-500">
                                <span class="dark:text-white text-gray-900">背景を透過する（OBS用）</span>
                            </label>
                        </div>

                        <!-- Font Size -->
                        <div>
                            <label class="block text-sm dark:text-gray-400 text-gray-600 mb-2">フォントサイズ</label>
                            <select name="overlay_font_size"
                                    class="w-full px-4 py-2 rounded-lg dark:bg-gray-900 bg-white border dark:border-gray-600 border-gray-300 dark:text-white text-gray-900 focus:border-purple-500 focus:ring-1 focus:ring-purple-500">
                                <option value="small" {{ $setting->overlay_font_size === 'small' ? 'selected' : '' }}>小</option>
                                <option value="medium" {{ $setting->overlay_font_size === 'medium' ? 'selected' : '' }}>中</option>
                                <option value="large" {{ $setting->overlay_font_size === 'large' ? 'selected' : '' }}>大</option>
                                <option value="xlarge" {{ $setting->overlay_font_size === 'xlarge' ? 'selected' : '' }}>特大</option>
                            </select>
                        </div>

                        <!-- Color Theme -->
                        <div>
                            <label class="block text-sm dark:text-gray-400 text-gray-600 mb-2">カラーテーマ</label>
                            <div class="flex gap-3">
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" name="overlay_color_theme" value="dark"
                                           {{ $setting->overlay_color_theme === 'dark' ? 'checked' : '' }}
                                           class="hidden peer">
                                    <div class="p-3 rounded-lg border-2 dark:border-gray-600 border-gray-300 peer-checked:border-purple-500 bg-gray-900 text-center transition-colors">
                                        <span class="text-white font-medium text-sm">ダーク</span>
                                    </div>
                                </label>
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" name="overlay_color_theme" value="light"
                                           {{ $setting->overlay_color_theme === 'light' ? 'checked' : '' }}
                                           class="hidden peer">
                                    <div class="p-3 rounded-lg border-2 dark:border-gray-600 border-gray-300 peer-checked:border-purple-500 bg-white text-center transition-colors">
                                        <span class="text-gray-900 font-medium text-sm">ライト</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="w-full px-4 py-2 rounded-lg bg-purple-600 hover:bg-purple-500 text-white font-medium transition-colors">
                            設定を保存
                        </button>
                    </form>
                </section>

                <!-- Stats History -->
                @if($sessions->count() > 0)
                <section class="glass-card rounded-xl p-6">
                    <h2 class="text-lg font-semibold dark:text-white text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        配信履歴
                    </h2>

                    <div class="space-y-2">
                        @foreach($sessions as $session)
                        @php
                            $stats = $session->getStats();
                        @endphp
                        <div class="p-3 rounded-lg dark:bg-gray-900 bg-gray-100 flex items-center justify-between">
                            <div>
                                <span class="dark:text-white text-gray-900 font-medium">
                                    {{ $session->name ?? '配信' }}
                                </span>
                                <p class="text-sm dark:text-gray-400 text-gray-600">
                                    {{ $session->started_at->format('m/d H:i') }}
                                    @if($session->ended_at)
                                    〜 {{ $session->ended_at->format('H:i') }}
                                    @elseif($session->is_active)
                                    〜 カウント中
                                    @endif
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="dark:text-white text-gray-900 font-medium">
                                    {{ $stats['wins'] }}勝{{ $stats['losses'] }}敗
                                </span>
                                <p class="text-sm dark:text-gray-400 text-gray-600">
                                    勝率 {{ $stats['win_rate'] }}%
                                </p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </section>
                @endif

            </div>
        </main>

        <!-- Start Stats Count Modal -->
        <div x-show="showStartSessionModal" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
             @click.self="showStartSessionModal = false">
            <div class="glass-card rounded-xl p-6 w-full max-w-md" @click.stop>
                <h3 class="text-lg font-semibold dark:text-white text-gray-900 mb-4">戦績カウントを開始</h3>
                <form action="{{ route('streamer.session.start') }}" method="POST">
                    @csrf
                    <div class="space-y-4 mb-4">
                        <div>
                            <label class="block text-sm dark:text-gray-400 text-gray-600 mb-2">配信名（任意）</label>
                            <input type="text" name="name" x-model="sessionName"
                                   placeholder="例: 朝配信、ランクマッチ回"
                                   class="w-full px-4 py-2 rounded-lg dark:bg-gray-900 bg-white border dark:border-gray-600 border-gray-300 dark:text-white text-gray-900 dark:placeholder-gray-500 placeholder-gray-400 focus:border-purple-500 focus:ring-1 focus:ring-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm dark:text-gray-400 text-gray-600 mb-2">連勝スタート値</label>
                            <input type="number" name="streak_start" x-model="streakStart" min="0"
                                   placeholder="0"
                                   class="w-full px-4 py-2 rounded-lg dark:bg-gray-900 bg-white border dark:border-gray-600 border-gray-300 dark:text-white text-gray-900 dark:placeholder-gray-500 placeholder-gray-400 focus:border-purple-500 focus:ring-1 focus:ring-purple-500">
                            <p class="mt-1 text-xs dark:text-gray-500 text-gray-500">
                                前回の配信から連勝を引き継ぐ場合に設定
                            </p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" @click="showStartSessionModal = false"
                                class="flex-1 px-4 py-2 rounded-lg dark:bg-gray-700 bg-gray-200 dark:hover:bg-gray-600 hover:bg-gray-300 dark:text-white text-gray-900 font-medium transition-colors">
                            キャンセル
                        </button>
                        <button type="submit"
                                class="flex-1 px-4 py-2 rounded-lg bg-purple-600 hover:bg-purple-500 text-white font-medium transition-colors">
                            開始
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Reset Streak Modal -->
        <div x-show="showResetStreakModal" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
             @click.self="showResetStreakModal = false">
            <div class="glass-card rounded-xl p-6 w-full max-w-md" @click.stop>
                <h3 class="text-lg font-semibold dark:text-white text-gray-900 mb-4">連勝カウンターをリセット</h3>
                <form action="{{ route('streamer.streak.reset') }}" method="POST">
                    @csrf
                    <div class="space-y-4 mb-4">
                        <p class="dark:text-gray-400 text-gray-600">
                            連勝カウンターをリセットします。新しい開始値を設定できます。
                        </p>
                        <div>
                            <label class="block text-sm dark:text-gray-400 text-gray-600 mb-2">新しい連勝スタート値</label>
                            <input type="number" name="streak_start" x-model="streakStart" min="0"
                                   placeholder="0"
                                   class="w-full px-4 py-2 rounded-lg dark:bg-gray-900 bg-white border dark:border-gray-600 border-gray-300 dark:text-white text-gray-900 dark:placeholder-gray-500 placeholder-gray-400 focus:border-purple-500 focus:ring-1 focus:ring-purple-500">
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" @click="showResetStreakModal = false"
                                class="flex-1 px-4 py-2 rounded-lg dark:bg-gray-700 bg-gray-200 dark:hover:bg-gray-600 hover:bg-gray-300 dark:text-white text-gray-900 font-medium transition-colors">
                            キャンセル
                        </button>
                        <button type="submit"
                                class="flex-1 px-4 py-2 rounded-lg bg-purple-600 hover:bg-purple-500 text-white font-medium transition-colors">
                            リセット
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
