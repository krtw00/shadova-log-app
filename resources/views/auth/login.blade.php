<x-layouts.auth>
    <x-slot:title>ログイン - Shadova Log</x-slot:title>

    <div class="glass-card rounded-2xl p-8">
        <h1 class="text-2xl font-bold text-white text-center mb-6">ログイン</h1>

        @if (session('status'))
            <div class="mb-4 p-4 rounded-xl bg-green-600/20 border border-green-500/30 text-green-400 text-sm">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <!-- メールアドレス -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-300 mb-2">メールアドレス</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    class="w-full px-4 py-3 rounded-xl bg-gray-700/50 border border-gray-600 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                    placeholder="example@email.com"
                >
                @error('email')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- パスワード -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-300 mb-2">パスワード</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    class="w-full px-4 py-3 rounded-xl bg-gray-700/50 border border-gray-600 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                    placeholder="パスワードを入力"
                >
                @error('password')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- ログイン保持とパスワードリセット -->
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input
                        type="checkbox"
                        id="remember"
                        name="remember"
                        class="w-4 h-4 rounded bg-gray-700 border-gray-600 text-purple-600 focus:ring-purple-500 focus:ring-offset-gray-800"
                    >
                    <label for="remember" class="ml-2 text-sm text-gray-300">ログイン状態を保持</label>
                </div>
                <a href="{{ route('password.request') }}" class="text-sm text-purple-400 hover:text-purple-300">
                    パスワードを忘れた方
                </a>
            </div>

            <!-- ログインボタン -->
            <button
                type="submit"
                class="w-full py-3 px-4 rounded-xl bg-purple-600 text-white font-semibold hover:bg-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:ring-offset-gray-800 transition-all"
            >
                ログイン
            </button>
        </form>

        <!-- 登録へのリンク -->
        <div class="mt-6 text-center">
            <p class="text-gray-400">
                アカウントをお持ちでない方は
                <a href="{{ route('register') }}" class="text-purple-400 hover:text-purple-300 font-medium">新規登録</a>
            </p>
        </div>
    </div>
</x-layouts.auth>
