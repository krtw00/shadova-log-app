<x-layouts.auth>
    <x-slot:title>新しいパスワードを設定 - Shadova Log</x-slot:title>

    <div class="glass-card rounded-2xl p-8">
        <h1 class="text-2xl font-bold text-white text-center mb-6">新しいパスワードを設定</h1>

        <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">

            <!-- メールアドレス -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-300 mb-2">メールアドレス</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email', $email) }}"
                    required
                    autofocus
                    class="w-full px-4 py-3 rounded-xl bg-gray-700/50 border border-gray-600 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                    placeholder="example@email.com"
                >
                @error('email')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- 新しいパスワード -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-300 mb-2">新しいパスワード</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    class="w-full px-4 py-3 rounded-xl bg-gray-700/50 border border-gray-600 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                    placeholder="8文字以上"
                >
                @error('password')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- パスワード確認 -->
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-300 mb-2">パスワード（確認）</label>
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    required
                    class="w-full px-4 py-3 rounded-xl bg-gray-700/50 border border-gray-600 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                    placeholder="パスワードを再入力"
                >
            </div>

            <!-- 送信ボタン -->
            <button
                type="submit"
                class="w-full py-3 px-4 rounded-xl bg-purple-600 text-white font-semibold hover:bg-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:ring-offset-gray-800 transition-all"
            >
                パスワードを変更
            </button>
        </form>
    </div>
</x-layouts.auth>
