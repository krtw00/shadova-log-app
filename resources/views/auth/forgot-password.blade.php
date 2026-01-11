<x-layouts.auth>
    <x-slot:title>パスワードリセット - Shadova Log</x-slot:title>

    <div class="glass-card rounded-2xl p-8">
        <h1 class="text-2xl font-bold text-white text-center mb-2">パスワードリセット</h1>
        <p class="text-gray-400 text-center text-sm mb-6">
            登録したメールアドレスを入力してください。<br>
            パスワードリセット用のリンクをお送りします。
        </p>

        @if (session('status'))
            <div class="mb-4 p-4 rounded-xl bg-green-600/20 border border-green-500/30 text-green-400 text-sm">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
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

            <!-- 送信ボタン -->
            <button
                type="submit"
                class="w-full py-3 px-4 rounded-xl bg-purple-600 text-white font-semibold hover:bg-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:ring-offset-gray-800 transition-all"
            >
                リセットリンクを送信
            </button>
        </form>

        <!-- 戻るリンク -->
        <div class="mt-6 text-center">
            <a href="{{ route('login') }}" class="text-purple-400 hover:text-purple-300 font-medium text-sm">
                ログイン画面に戻る
            </a>
        </div>
    </div>
</x-layouts.auth>
