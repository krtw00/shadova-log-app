<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>配信オーバーレイ - Shadova Log</title>
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900" rel="stylesheet" />
    @php
        $fontSizeBase = match($setting->overlay_font_size) {
            'small' => 12,
            'medium' => 14,
            'large' => 18,
            'xlarge' => 24,
            default => 14,
        };
        // 勝率リングのサイズをフォントサイズに連動
        $ringSize = match($setting->overlay_font_size) {
            'small' => 72,
            'medium' => 80,
            'large' => 100,
            'xlarge' => 130,
            default => 80,
        };
        $ringInnerSize = $ringSize - 16;
        $isLight = $setting->overlay_color_theme === 'light';
        $isTransparent = $setting->overlay_bg_transparent;
    @endphp
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        :root {
            --font-size-base: {{ $fontSizeBase }}px;
            --font-size-lg: {{ $fontSizeBase * 1.25 }}px;
            --font-size-xl: {{ $fontSizeBase * 1.5 }}px;
            --font-size-2xl: {{ $fontSizeBase * 2 }}px;
            --font-size-3xl: {{ $fontSizeBase * 2.5 }}px;
            --font-size-sm: {{ $fontSizeBase * 0.875 }}px;
            --font-size-xs: {{ $fontSizeBase * 0.75 }}px;
            --ring-size: {{ $ringSize }}px;
            --ring-inner-size: {{ $ringInnerSize }}px;
        }

        body {
            font-size: var(--font-size-base);
            @if($isTransparent)
            background: transparent !important;
            @endif
        }

        @if($isTransparent)
        html, body {
            background: transparent !important;
        }
        @endif

        .overlay-card {
            @if($isTransparent)
            background: {{ $isLight ? 'rgba(255,255,255,0.9)' : 'rgba(17,24,39,0.9)' }};
            backdrop-filter: blur(12px);
            @else
            background: {{ $isLight ? '#ffffff' : '#111827' }};
            @endif
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.3);
        }

        .win-rate-ring {
            background: conic-gradient(
                #22c55e calc(var(--win-rate) * 1%),
                {{ $isLight ? '#e5e7eb' : '#374151' }} calc(var(--win-rate) * 1%)
            );
        }

        .stat-badge {
            background: {{ $isLight ? 'rgba(0,0,0,0.05)' : 'rgba(255,255,255,0.1)' }};
            border-radius: 8px;
            padding: 8px 12px;
        }

        .result-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }

        .result-win { background: #22c55e; }
        .result-lose { background: #ef4444; }

        .streak-badge {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 700;
        }

        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.4); }
            50% { box-shadow: 0 0 0 8px rgba(34, 197, 94, 0); }
        }

        .streak-active {
            animation: pulse-glow 2s infinite;
        }
    </style>
</head>
<body class="{{ $isTransparent ? '' : ($isLight ? 'bg-gray-200' : 'bg-gray-950') }} min-h-screen p-4">
    @php
        $minWidth = match($setting->overlay_font_size) {
            'small' => 260,
            'medium' => 280,
            'large' => 340,
            'xlarge' => 420,
            default => 280,
        };
    @endphp
    <div class="overlay-card {{ $isLight ? 'text-gray-900' : 'text-white' }} p-5 inline-block"
         style="min-width: {{ $minWidth }}px;"
         x-data="overlayData()"
         x-init="init()">

        <!-- Header: Session Name -->
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></div>
                <span class="font-semibold opacity-70" style="font-size: var(--font-size-sm)" x-text="sessionName">{{ $sessionName }}</span>
            </div>
            @if($setting->overlay_show_streak)
            <div x-show="stats.streak > 0" class="streak-badge" :class="stats.streak >= 3 ? 'streak-active' : ''">
                <span x-text="stats.streak + '連勝'">{{ $stats['streak'] }}連勝</span>
            </div>
            @endif
        </div>

        <!-- Main Stats -->
        <div class="flex items-center gap-5">
            @if($setting->overlay_show_winrate)
            <!-- Win Rate Circle -->
            <div class="relative flex-shrink-0">
                <div class="win-rate-ring rounded-full flex items-center justify-center"
                     :style="{ width: '{{ $ringSize }}px', height: '{{ $ringSize }}px', '--win-rate': stats.win_rate }">
                    <div class="rounded-full {{ $isLight ? 'bg-white' : 'bg-gray-900' }} flex items-center justify-center"
                         style="width: {{ $ringInnerSize }}px; height: {{ $ringInnerSize }}px;">
                        <div class="text-center">
                            <div class="font-black text-green-500" style="font-size: var(--font-size-2xl)" x-text="stats.win_rate">{{ $stats['win_rate'] }}</div>
                            <div class="font-medium opacity-60" style="font-size: var(--font-size-xs)">%</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Win/Loss Stats -->
            <div class="flex-1">
                @if($setting->overlay_show_record)
                <div class="flex gap-3 mb-3">
                    <div class="stat-badge flex-1 text-center">
                        <div class="font-black text-green-500" style="font-size: var(--font-size-xl)" x-text="stats.wins">{{ $stats['wins'] }}</div>
                        <div class="font-medium opacity-60" style="font-size: var(--font-size-xs)">WIN</div>
                    </div>
                    <div class="stat-badge flex-1 text-center">
                        <div class="font-black text-red-500" style="font-size: var(--font-size-xl)" x-text="stats.losses">{{ $stats['losses'] }}</div>
                        <div class="font-medium opacity-60" style="font-size: var(--font-size-xs)">LOSE</div>
                    </div>
                </div>
                @endif

                <!-- Recent Results (dots) -->
                @if($setting->overlay_show_log)
                <div class="flex items-center gap-1.5">
                    <span class="opacity-50 mr-1" style="font-size: var(--font-size-xs)">直近:</span>
                    <template x-for="(battle, index) in log.slice(0, 10)" :key="index">
                        <div class="result-dot" :class="battle.result ? 'result-win' : 'result-lose'"></div>
                    </template>
                </div>
                @endif
            </div>
        </div>

        <!-- Deck Info -->
        @if($setting->overlay_show_deck)
        <div x-show="deck" class="mt-4 pt-4 border-t {{ $isLight ? 'border-gray-200' : 'border-gray-700' }}">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <span class="font-semibold" style="font-size: var(--font-size-sm)" x-text="deck?.name">{{ $currentDeck?->name ?? '' }}</span>
                </div>
                <div class="flex items-center gap-2" style="font-size: var(--font-size-sm)">
                    <span class="text-green-500 font-bold" x-text="deck?.wins + 'W'"></span>
                    <span class="text-red-500 font-bold" x-text="deck?.losses + 'L'"></span>
                    <span class="opacity-60" x-text="'(' + deck?.win_rate + '%)'"></span>
                </div>
            </div>
        </div>
        @endif

        <!-- Battle Log (detailed) -->
        @if($setting->overlay_show_log)
        <div x-show="log.length > 0" class="mt-4 pt-4 border-t {{ $isLight ? 'border-gray-200' : 'border-gray-700' }}">
            <div class="space-y-2">
                <template x-for="(battle, index) in log.slice(0, {{ $setting->overlay_log_count }})" :key="index">
                    <div class="flex items-center gap-3" style="font-size: var(--font-size-sm)">
                        <div class="w-12 text-center py-1 rounded font-bold"
                             :class="battle.result ? 'bg-green-500/20 text-green-500' : 'bg-red-500/20 text-red-500'"
                             x-text="battle.result ? 'WIN' : 'LOSE'"></div>
                        <div class="flex-1">
                            <span class="opacity-60">vs</span>
                            <span class="font-medium ml-1" x-text="battle.opponent"></span>
                        </div>
                        <div class="opacity-40" style="font-size: var(--font-size-xs)" x-text="battle.is_first ? '先攻' : '後攻'"></div>
                    </div>
                </template>
            </div>
        </div>
        @endif
    </div>

    @php
        $logData = $battleLog->map(function ($battle) {
            return [
                'result' => $battle->result,
                'deck' => $battle->deck?->name ?? $battle->myClass?->name,
                'opponent' => $battle->opponentClass->name,
                'is_first' => $battle->is_first,
            ];
        });
    @endphp
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        function overlayData() {
            return {
                sessionName: '{{ $sessionName }}',
                stats: {
                    total: {{ $stats['total'] }},
                    wins: {{ $stats['wins'] }},
                    losses: {{ $stats['losses'] }},
                    win_rate: {{ $stats['win_rate'] }},
                    streak: {{ $stats['streak'] }}
                },
                deck: @json($deckStats),
                log: @json($logData),
                init() {
                    this.refresh();
                    setInterval(() => this.refresh(), 5000);
                },
                async refresh() {
                    try {
                        const response = await fetch('{{ route('streamer.overlay.data') }}');
                        const data = await response.json();
                        this.sessionName = data.session_name;
                        this.stats = data.stats;
                        this.deck = data.deck;
                        this.log = data.log;
                    } catch (error) {
                        console.error('Failed to refresh overlay data:', error);
                    }
                }
            };
        }
    </script>
</body>
</html>
