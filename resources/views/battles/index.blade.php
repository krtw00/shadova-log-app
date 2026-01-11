<x-layouts.app>
    <x-slot:title>対戦記録 - Shadova Log</x-slot:title>

    {{-- Page Header --}}
    <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-white">対戦記録</h1>
            <p class="mt-1 text-gray-400">最近の対戦履歴と新しい記録の追加</p>
        </div>
        <a href="#" class="inline-flex items-center gap-2 rounded-md bg-purple-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-purple-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-purple-600">
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
            </svg>
            <span>新しい対戦を記録</span>
        </a>
    </div>

    {{-- Recent Battles Table --}}
    <div class="mt-8 flow-root">
        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                @if(empty($recentBattles))
                    <div class="flex flex-col items-center justify-center rounded-lg border border-dashed border-gray-700 bg-gray-800/50 py-20 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                            <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                        <h3 class="mt-4 text-lg font-medium text-white">対戦記録がありません</h3>
                        <p class="mt-1 text-sm text-gray-400">最初の対戦を記録して、分析を始めましょう。</p>
                    </div>
                @else
                    <table class="min-w-full divide-y divide-gray-700">
                        <thead>
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-white sm:pl-0">結果</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-white">使用デッキ</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-white">相手クラス</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-white">モード</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-white">日時</th>
                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-0">
                                    <span class="sr-only">編集</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                            @foreach($recentBattles as $battle)
                                <tr>
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-0">
                                        @if($battle['result'])
                                            <span class="inline-flex items-center rounded-md bg-green-500/10 px-2 py-1 text-xs font-medium text-green-400 ring-1 ring-inset ring-green-500/20">WIN</span>
                                        @else
                                             <span class="inline-flex items-center rounded-md bg-red-500/10 px-2 py-1 text-xs font-medium text-red-400 ring-1 ring-inset ring-red-500/20">LOSE</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-300">
                                        <div class="font-medium text-white">{{ $battle['deck_name'] }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-300">{{ $battle['opponent_class'] }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-300">{{ $battle['game_mode'] }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-400">{{ $battle['played_at'] }}</td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-0">
                                        <a href="#" class="text-purple-400 hover:text-purple-300">編集</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>

</x-layouts.app>
