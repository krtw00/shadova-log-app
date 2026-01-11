<x-layouts.auth>
    <x-slot:title>ユーザー登録 - Shadova Log</x-slot:title>

    <div class="glass-card rounded-2xl p-8">
        <h1 class="text-2xl font-bold text-white text-center mb-6">ユーザー登録</h1>

        <form method="POST" action="{{ route('register') }}" class="space-y-5">
            @csrf

            <!-- 名前 -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-300 mb-2">ユーザー名</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    autofocus
                    class="w-full px-4 py-3 rounded-xl bg-gray-700/50 border border-gray-600 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                    placeholder="表示名を入力"
                >
                @error('name')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- メールアドレス -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-300 mb-2">メールアドレス</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
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

            <!-- 登録ボタン -->
            <button
                type="submit"
                class="w-full py-3 px-4 rounded-xl bg-purple-600 text-white font-semibold hover:bg-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:ring-offset-gray-800 transition-all"
            >
                登録する
            </button>
        </form>

        <!-- ログインへのリンク -->
        <div class="mt-6 text-center">
            <p class="text-gray-400">
                すでにアカウントをお持ちの方は
                <a href="{{ route('login') }}" class="text-purple-400 hover:text-purple-300 font-medium">ログイン</a>
            </p>
        </div>
    </div>
</x-layouts.auth>
