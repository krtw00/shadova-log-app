<x-layouts.app>
    <x-slot:title>フィードバック - Shadova Log</x-slot:title>

    <div class="flex-1 flex flex-col min-w-0" x-data="{ activeTab: 'bug' }">
        <!-- Top Bar -->
        <header class="flex-shrink-0 border-b dark:border-gray-700 border-gray-200 dark:bg-gray-800/50 bg-white/50">
            <div class="flex items-center h-14 px-4">
                <h1 class="text-lg font-semibold dark:text-white text-gray-900">フィードバック</h1>
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

                @if(session('error'))
                <div class="p-4 rounded-lg bg-red-600/20 border border-red-500/50 text-red-600 dark:text-red-400">
                    {{ session('error') }}
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

                <!-- Intro -->
                <div class="glass-card rounded-xl p-6">
                    <p class="dark:text-gray-300 text-gray-700">
                        Shadova Logをご利用いただきありがとうございます。<br>
                        バグの報告、機能のご要望、その他お問い合わせがございましたら、以下のフォームからお送りください。
                    </p>
                </div>

                <!-- Tabs -->
                <div class="glass-card rounded-xl overflow-hidden">
                    <!-- Tab Headers -->
                    <div class="flex border-b dark:border-gray-700 border-gray-200">
                        <button type="button"
                                @click="activeTab = 'bug'"
                                :class="activeTab === 'bug' ? 'dark:bg-gray-700/50 bg-gray-100 dark:text-purple-400 text-purple-600 border-b-2 border-purple-500' : 'dark:text-gray-400 text-gray-600 dark:hover:bg-gray-700/30 hover:bg-gray-50'"
                                class="flex-1 px-4 py-3 font-medium text-sm transition-colors flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            バグ報告
                        </button>
                        <button type="button"
                                @click="activeTab = 'enhancement'"
                                :class="activeTab === 'enhancement' ? 'dark:bg-gray-700/50 bg-gray-100 dark:text-purple-400 text-purple-600 border-b-2 border-purple-500' : 'dark:text-gray-400 text-gray-600 dark:hover:bg-gray-700/30 hover:bg-gray-50'"
                                class="flex-1 px-4 py-3 font-medium text-sm transition-colors flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                            機能要望
                        </button>
                        <button type="button"
                                @click="activeTab = 'contact'"
                                :class="activeTab === 'contact' ? 'dark:bg-gray-700/50 bg-gray-100 dark:text-purple-400 text-purple-600 border-b-2 border-purple-500' : 'dark:text-gray-400 text-gray-600 dark:hover:bg-gray-700/30 hover:bg-gray-50'"
                                class="flex-1 px-4 py-3 font-medium text-sm transition-colors flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            お問い合わせ
                        </button>
                    </div>

                    <!-- Tab Content -->
                    <div class="p-6">
                        <!-- Bug Report Form -->
                        <form x-show="activeTab === 'bug'" x-transition action="{{ route('feedback.bug') }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">
                                    タイトル <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="title" value="{{ old('title') }}" required maxlength="255"
                                       placeholder="例: 対戦記録が保存されない"
                                       class="w-full px-4 py-2 rounded-lg dark:bg-gray-900 bg-white border dark:border-gray-600 border-gray-300 dark:text-white text-gray-900 dark:placeholder-gray-500 placeholder-gray-400 focus:border-purple-500 focus:ring-1 focus:ring-purple-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">
                                    説明 <span class="text-red-500">*</span>
                                </label>
                                <textarea name="description" required maxlength="5000" rows="4"
                                          placeholder="どのような問題が発生しましたか？"
                                          class="w-full px-4 py-2 rounded-lg dark:bg-gray-900 bg-white border dark:border-gray-600 border-gray-300 dark:text-white text-gray-900 dark:placeholder-gray-500 placeholder-gray-400 focus:border-purple-500 focus:ring-1 focus:ring-purple-500">{{ old('description') }}</textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">再現手順</label>
                                <textarea name="steps" maxlength="3000" rows="3"
                                          placeholder="1. ○○をクリック&#10;2. △△を入力&#10;3. エラーが発生"
                                          class="w-full px-4 py-2 rounded-lg dark:bg-gray-900 bg-white border dark:border-gray-600 border-gray-300 dark:text-white text-gray-900 dark:placeholder-gray-500 placeholder-gray-400 focus:border-purple-500 focus:ring-1 focus:ring-purple-500">{{ old('steps') }}</textarea>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">期待する動作</label>
                                    <textarea name="expected" maxlength="1000" rows="2"
                                              placeholder="本来どうなるべきか"
                                              class="w-full px-4 py-2 rounded-lg dark:bg-gray-900 bg-white border dark:border-gray-600 border-gray-300 dark:text-white text-gray-900 dark:placeholder-gray-500 placeholder-gray-400 focus:border-purple-500 focus:ring-1 focus:ring-purple-500">{{ old('expected') }}</textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">実際の動作</label>
                                    <textarea name="actual" maxlength="1000" rows="2"
                                              placeholder="実際に何が起こったか"
                                              class="w-full px-4 py-2 rounded-lg dark:bg-gray-900 bg-white border dark:border-gray-600 border-gray-300 dark:text-white text-gray-900 dark:placeholder-gray-500 placeholder-gray-400 focus:border-purple-500 focus:ring-1 focus:ring-purple-500">{{ old('actual') }}</textarea>
                                </div>
                            </div>
                            <div class="flex justify-end pt-2">
                                <button type="submit" class="px-6 py-2.5 rounded-lg bg-purple-600 hover:bg-purple-500 text-white font-medium transition-colors">
                                    バグを報告する
                                </button>
                            </div>
                        </form>

                        <!-- Enhancement Form -->
                        <form x-show="activeTab === 'enhancement'" x-transition action="{{ route('feedback.enhancement') }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">
                                    タイトル <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="title" value="{{ old('title') }}" required maxlength="255"
                                       placeholder="例: デッキのインポート機能"
                                       class="w-full px-4 py-2 rounded-lg dark:bg-gray-900 bg-white border dark:border-gray-600 border-gray-300 dark:text-white text-gray-900 dark:placeholder-gray-500 placeholder-gray-400 focus:border-purple-500 focus:ring-1 focus:ring-purple-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">
                                    説明 <span class="text-red-500">*</span>
                                </label>
                                <textarea name="description" required maxlength="5000" rows="4"
                                          placeholder="どのような機能がほしいですか？"
                                          class="w-full px-4 py-2 rounded-lg dark:bg-gray-900 bg-white border dark:border-gray-600 border-gray-300 dark:text-white text-gray-900 dark:placeholder-gray-500 placeholder-gray-400 focus:border-purple-500 focus:ring-1 focus:ring-purple-500">{{ old('description') }}</textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">ユースケース</label>
                                <textarea name="use_case" maxlength="2000" rows="3"
                                          placeholder="この機能があるとどんな時に便利ですか？"
                                          class="w-full px-4 py-2 rounded-lg dark:bg-gray-900 bg-white border dark:border-gray-600 border-gray-300 dark:text-white text-gray-900 dark:placeholder-gray-500 placeholder-gray-400 focus:border-purple-500 focus:ring-1 focus:ring-purple-500">{{ old('use_case') }}</textarea>
                            </div>
                            <div class="flex justify-end pt-2">
                                <button type="submit" class="px-6 py-2.5 rounded-lg bg-purple-600 hover:bg-purple-500 text-white font-medium transition-colors">
                                    要望を送信する
                                </button>
                            </div>
                        </form>

                        <!-- Contact Form -->
                        <form x-show="activeTab === 'contact'" x-transition action="{{ route('feedback.contact') }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">
                                    件名 <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="subject" value="{{ old('subject') }}" required maxlength="255"
                                       placeholder="お問い合わせの件名"
                                       class="w-full px-4 py-2 rounded-lg dark:bg-gray-900 bg-white border dark:border-gray-600 border-gray-300 dark:text-white text-gray-900 dark:placeholder-gray-500 placeholder-gray-400 focus:border-purple-500 focus:ring-1 focus:ring-purple-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-2">
                                    メッセージ <span class="text-red-500">*</span>
                                </label>
                                <textarea name="message" required maxlength="5000" rows="6"
                                          placeholder="お問い合わせ内容をご記入ください"
                                          class="w-full px-4 py-2 rounded-lg dark:bg-gray-900 bg-white border dark:border-gray-600 border-gray-300 dark:text-white text-gray-900 dark:placeholder-gray-500 placeholder-gray-400 focus:border-purple-500 focus:ring-1 focus:ring-purple-500">{{ old('message') }}</textarea>
                            </div>
                            <p class="text-sm dark:text-gray-500 text-gray-500">
                                ご登録のメールアドレス（{{ Auth::user()->email }}）宛に返信いたします。
                            </p>
                            <div class="flex justify-end pt-2">
                                <button type="submit" class="px-6 py-2.5 rounded-lg bg-purple-600 hover:bg-purple-500 text-white font-medium transition-colors">
                                    送信する
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </main>
    </div>
</x-layouts.app>
