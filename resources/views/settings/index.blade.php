<x-layouts.app>
    <x-slot:title>設定 - Shadova Log</x-slot:title>

    <div class="flex-1 flex flex-col min-w-0" x-data="{
        showDeleteDataModal: false,
        showDeleteAccountModal: false,
        confirmText: '',
        confirmAccountText: ''
    }">
        <!-- Top Bar -->
        <header class="flex-shrink-0 border-b dark:border-gray-700 border-gray-200 dark:bg-gray-800/50 bg-white/50">
            <div class="flex items-center h-14 px-4">
                <h1 class="text-lg font-semibold dark:text-white text-gray-900">設定</h1>
            </div>
        </header>

        <!-- Scrollable Content -->
        <main class="flex-1 overflow-y-auto scrollbar-thin p-4 md:p-6">
            <div class="max-w-2xl mx-auto space-y-6">

                <!-- Flash Messages -->
                @if(session('success'))
                <div class="p-4 rounded-lg bg-green-600/20 border border-green-500/50 text-green-600 dark:text-green-400">
                    {{ session('success') }}
                </div>
                @endif

                @if($errors->any())
                <div class="p-4 rounded-lg bg-red-600/20 border border-red-500/50 text-red-600 dark:text-red-400">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <!-- Profile Section -->
                <section class="glass-card rounded-xl p-6">
                    <h2 class="text-lg font-semibold dark:text-white text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        プロフィール
                    </h2>

                    <!-- Username -->
                    <form action="{{ route('settings.profile') }}" method="POST" class="mb-6">
                        @csrf
                        @method('PUT')
                        <label class="block text-sm dark:text-gray-400 text-gray-600 mb-2">ユーザー名（公開URL用）</label>
                        <div class="flex gap-3">
                            <input type="text" name="username" value="{{ old('username', $user->username) }}"
                                   placeholder="例: my-username"
                                   pattern="[a-z0-9-]+"
                                   class="flex-1 px-4 py-2 rounded-lg dark:bg-gray-900 bg-white border dark:border-gray-600 border-gray-300 dark:text-white text-gray-900 dark:placeholder-gray-500 placeholder-gray-400 focus:border-purple-500 focus:ring-1 focus:ring-purple-500">
                            <button type="submit" class="px-4 py-2 rounded-lg bg-purple-600 hover:bg-purple-500 text-white font-medium transition-colors">
                                更新
                            </button>
                        </div>
                        <p class="mt-2 text-xs dark:text-gray-500 text-gray-500">英小文字、数字、ハイフンのみ使用可能</p>
                    </form>

                    <!-- Password -->
                    <form action="{{ route('settings.password') }}" method="POST" x-data="{ showPasswords: false }">
                        @csrf
                        @method('PUT')
                        <label class="block text-sm dark:text-gray-400 text-gray-600 mb-2">パスワード変更</label>
                        <div class="space-y-3">
                            <div class="relative">
                                <input :type="showPasswords ? 'text' : 'password'" name="current_password"
                                       placeholder="現在のパスワード"
                                       class="w-full px-4 py-2 rounded-lg dark:bg-gray-900 bg-white border dark:border-gray-600 border-gray-300 dark:text-white text-gray-900 dark:placeholder-gray-500 placeholder-gray-400 focus:border-purple-500 focus:ring-1 focus:ring-purple-500">
                            </div>
                            <div class="relative">
                                <input :type="showPasswords ? 'text' : 'password'" name="password"
                                       placeholder="新しいパスワード（8文字以上）"
                                       class="w-full px-4 py-2 rounded-lg dark:bg-gray-900 bg-white border dark:border-gray-600 border-gray-300 dark:text-white text-gray-900 dark:placeholder-gray-500 placeholder-gray-400 focus:border-purple-500 focus:ring-1 focus:ring-purple-500">
                            </div>
                            <div class="relative">
                                <input :type="showPasswords ? 'text' : 'password'" name="password_confirmation"
                                       placeholder="新しいパスワード（確認）"
                                       class="w-full px-4 py-2 rounded-lg dark:bg-gray-900 bg-white border dark:border-gray-600 border-gray-300 dark:text-white text-gray-900 dark:placeholder-gray-500 placeholder-gray-400 focus:border-purple-500 focus:ring-1 focus:ring-purple-500">
                            </div>
                            <div class="flex items-center justify-between">
                                <label class="flex items-center gap-2 text-sm dark:text-gray-400 text-gray-600 cursor-pointer">
                                    <input type="checkbox" x-model="showPasswords" class="rounded dark:border-gray-600 border-gray-300 dark:bg-gray-900 bg-white text-purple-600 focus:ring-purple-500">
                                    パスワードを表示
                                </label>
                                <button type="submit" class="px-4 py-2 rounded-lg bg-purple-600 hover:bg-purple-500 text-white font-medium transition-colors">
                                    変更
                                </button>
                            </div>
                        </div>
                    </form>
                </section>

                <!-- Preferences Section -->
                <section class="glass-card rounded-xl p-6">
                    <h2 class="text-lg font-semibold dark:text-white text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        設定
                    </h2>

                    <form action="{{ route('settings.preferences') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Default Game Mode -->
                        <div class="mb-4">
                            <label class="block text-sm dark:text-gray-400 text-gray-600 mb-2">デフォルトゲームモード</label>
                            <select name="default_game_mode_id" class="w-full px-4 py-2 rounded-lg dark:bg-gray-900 bg-white border dark:border-gray-600 border-gray-300 dark:text-white text-gray-900 focus:border-purple-500 focus:ring-1 focus:ring-purple-500">
                                <option value="">なし</option>
                                @foreach($gameModes as $mode)
                                <option value="{{ $mode->id }}" {{ $setting->default_game_mode_id == $mode->id ? 'selected' : '' }}>
                                    {{ $mode->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Theme -->
                        <div class="mb-4">
                            <label class="block text-sm dark:text-gray-400 text-gray-600 mb-2">テーマ</label>
                            <div class="flex gap-3">
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" name="theme" value="dark" {{ $setting->theme === 'dark' ? 'checked' : '' }} class="hidden peer">
                                    <div class="p-4 rounded-lg border-2 dark:border-gray-600 border-gray-300 peer-checked:border-purple-500 dark:bg-gray-900 bg-gray-100 text-center transition-colors">
                                        <svg class="w-8 h-8 mx-auto mb-2 dark:text-gray-400 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                                        </svg>
                                        <span class="dark:text-white text-gray-900 font-medium">ダーク</span>
                                    </div>
                                </label>
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" name="theme" value="light" {{ $setting->theme === 'light' ? 'checked' : '' }} class="hidden peer">
                                    <div class="p-4 rounded-lg border-2 dark:border-gray-600 border-gray-300 peer-checked:border-purple-500 dark:bg-gray-900 bg-gray-100 text-center transition-colors">
                                        <svg class="w-8 h-8 mx-auto mb-2 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                                        </svg>
                                        <span class="dark:text-white text-gray-900 font-medium">ライト</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="w-full px-4 py-2 rounded-lg bg-purple-600 hover:bg-purple-500 text-white font-medium transition-colors">
                            設定を保存
                        </button>
                    </form>
                </section>

                <!-- Data Management Section -->
                <section class="glass-card rounded-xl p-6">
                    <h2 class="text-lg font-semibold dark:text-white text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                        </svg>
                        データ管理
                    </h2>

                    <!-- Export -->
                    <div class="mb-6">
                        <label class="block text-sm dark:text-gray-400 text-gray-600 mb-2">データエクスポート</label>
                        <p class="text-xs dark:text-gray-500 text-gray-500 mb-3">対戦記録をファイルとしてダウンロードできます。</p>
                        <div class="flex gap-3">
                            <a href="{{ route('settings.export', ['format' => 'csv']) }}"
                               class="flex-1 px-4 py-2 rounded-lg dark:bg-gray-700 bg-gray-200 dark:hover:bg-gray-600 hover:bg-gray-300 dark:text-white text-gray-900 font-medium text-center transition-colors">
                                CSV形式
                            </a>
                            <a href="{{ route('settings.export', ['format' => 'json']) }}"
                               class="flex-1 px-4 py-2 rounded-lg dark:bg-gray-700 bg-gray-200 dark:hover:bg-gray-600 hover:bg-gray-300 dark:text-white text-gray-900 font-medium text-center transition-colors">
                                JSON形式
                            </a>
                        </div>
                    </div>

                    <!-- Delete All Data -->
                    <div class="pt-4 border-t dark:border-gray-700 border-gray-200">
                        <label class="block text-sm dark:text-gray-400 text-gray-600 mb-2">対戦記録の全削除</label>
                        <p class="text-xs dark:text-gray-500 text-gray-500 mb-3">全ての対戦記録を削除します。この操作は取り消せません。</p>
                        <button type="button" @click="showDeleteDataModal = true"
                                class="w-full px-4 py-2 rounded-lg bg-red-600/20 hover:bg-red-600/30 border border-red-500/50 text-red-600 dark:text-red-400 font-medium transition-colors">
                            全データを削除
                        </button>
                    </div>
                </section>

                <!-- Account Section -->
                <section class="glass-card rounded-xl p-6">
                    <h2 class="text-lg font-semibold dark:text-white text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        アカウント削除
                    </h2>
                    <p class="text-sm dark:text-gray-400 text-gray-600 mb-4">
                        アカウントを削除すると、全ての対戦記録、デッキ、設定が完全に削除されます。この操作は取り消せません。
                    </p>
                    <button type="button" @click="showDeleteAccountModal = true"
                            class="w-full px-4 py-2 rounded-lg bg-red-600 hover:bg-red-500 text-white font-medium transition-colors">
                        アカウントを削除
                    </button>
                </section>

            </div>
        </main>

        <!-- Delete Data Modal -->
        <div x-show="showDeleteDataModal" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
             @click.self="showDeleteDataModal = false">
            <div class="glass-card rounded-xl p-6 w-full max-w-md" @click.stop>
                <h3 class="text-lg font-semibold dark:text-white text-gray-900 mb-4">対戦記録の全削除</h3>
                <p class="dark:text-gray-400 text-gray-600 mb-4">
                    本当に全ての対戦記録を削除しますか？この操作は取り消せません。
                </p>
                <p class="text-sm dark:text-gray-500 text-gray-500 mb-4">
                    削除するには「DELETE」と入力してください。
                </p>
                <form action="{{ route('settings.data.delete') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <input type="text" name="confirm_delete" x-model="confirmText"
                           placeholder="DELETE"
                           class="w-full px-4 py-2 rounded-lg dark:bg-gray-900 bg-white border dark:border-gray-600 border-gray-300 dark:text-white text-gray-900 dark:placeholder-gray-500 placeholder-gray-400 focus:border-red-500 focus:ring-1 focus:ring-red-500 mb-4">
                    <div class="flex gap-3">
                        <button type="button" @click="showDeleteDataModal = false; confirmText = ''"
                                class="flex-1 px-4 py-2 rounded-lg dark:bg-gray-700 bg-gray-200 dark:hover:bg-gray-600 hover:bg-gray-300 dark:text-white text-gray-900 font-medium transition-colors">
                            キャンセル
                        </button>
                        <button type="submit" :disabled="confirmText !== 'DELETE'"
                                class="flex-1 px-4 py-2 rounded-lg bg-red-600 hover:bg-red-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium transition-colors">
                            削除
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Account Modal -->
        <div x-show="showDeleteAccountModal" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
             @click.self="showDeleteAccountModal = false">
            <div class="glass-card rounded-xl p-6 w-full max-w-md" @click.stop>
                <h3 class="text-lg font-semibold dark:text-white text-gray-900 mb-4">アカウント削除</h3>
                <p class="dark:text-gray-400 text-gray-600 mb-4">
                    本当にアカウントを削除しますか？全てのデータが完全に削除され、復元できません。
                </p>
                <form action="{{ route('settings.account.delete') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="space-y-4 mb-4">
                        <div>
                            <label class="block text-sm dark:text-gray-500 text-gray-500 mb-1">「DELETE」と入力</label>
                            <input type="text" name="confirm_delete_account" x-model="confirmAccountText"
                                   placeholder="DELETE"
                                   class="w-full px-4 py-2 rounded-lg dark:bg-gray-900 bg-white border dark:border-gray-600 border-gray-300 dark:text-white text-gray-900 dark:placeholder-gray-500 placeholder-gray-400 focus:border-red-500 focus:ring-1 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block text-sm dark:text-gray-500 text-gray-500 mb-1">パスワードを入力</label>
                            <input type="password" name="password"
                                   placeholder="パスワード"
                                   class="w-full px-4 py-2 rounded-lg dark:bg-gray-900 bg-white border dark:border-gray-600 border-gray-300 dark:text-white text-gray-900 dark:placeholder-gray-500 placeholder-gray-400 focus:border-red-500 focus:ring-1 focus:ring-red-500">
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" @click="showDeleteAccountModal = false; confirmAccountText = ''"
                                class="flex-1 px-4 py-2 rounded-lg dark:bg-gray-700 bg-gray-200 dark:hover:bg-gray-600 hover:bg-gray-300 dark:text-white text-gray-900 font-medium transition-colors">
                            キャンセル
                        </button>
                        <button type="submit" :disabled="confirmAccountText !== 'DELETE'"
                                class="flex-1 px-4 py-2 rounded-lg bg-red-600 hover:bg-red-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium transition-colors">
                            削除
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
