<?php

namespace App\Http\Controllers;

use App\Models\Battle;
use App\Models\Deck;
use App\Models\StreamerSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StreamerController extends Controller
{
    /**
     * 配信者モードダッシュボード
     */
    public function index()
    {
        $user = Auth::user();
        $setting = $user->getOrCreateSetting();

        if (!$setting->streamer_mode_enabled) {
            return redirect()->route('settings.index')
                ->with('error', '配信者モードを有効にしてください');
        }

        $activeSession = StreamerSession::getActiveSession($user->id);
        $sessions = StreamerSession::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('streamer.index', compact('setting', 'activeSession', 'sessions'));
    }

    /**
     * オーバーレイ表示（ポップアップウィンドウ用）
     */
    public function overlay()
    {
        $user = Auth::user();
        $setting = $user->getOrCreateSetting();

        $activeSession = StreamerSession::getActiveSession($user->id);

        // セッションがあればセッションの統計、なければ今日の統計
        if ($activeSession) {
            $stats = $activeSession->getStats();
            $sessionName = $activeSession->name ?? '配信中';
        } else {
            $stats = $this->getTodayStats($user->id);
            $sessionName = '今日';
        }

        // 現在使用中のデッキ（直近の対戦から）
        $lastBattle = Battle::where('user_id', $user->id)
            ->with('deck.leaderClass')
            ->orderBy('played_at', 'desc')
            ->first();
        $currentDeck = $lastBattle?->deck;

        // デッキの統計
        $deckStats = null;
        if ($currentDeck) {
            $deckQuery = Battle::where('user_id', $user->id)
                ->where('deck_id', $currentDeck->id);

            if ($activeSession) {
                $deckQuery->where('played_at', '>=', $activeSession->started_at);
            } else {
                $deckQuery->whereDate('played_at', today());
            }

            $deckTotal = $deckQuery->count();
            $deckWins = (clone $deckQuery)->where('result', true)->count();
            $deckStats = [
                'total' => $deckTotal,
                'wins' => $deckWins,
                'losses' => $deckTotal - $deckWins,
                'win_rate' => $deckTotal > 0 ? round(($deckWins / $deckTotal) * 100, 1) : 0,
            ];
        }

        // 対戦ログ
        $logQuery = Battle::where('user_id', $user->id)
            ->with(['deck.leaderClass', 'myClass', 'opponentClass'])
            ->orderBy('played_at', 'desc');

        if ($activeSession) {
            $logQuery->where('played_at', '>=', $activeSession->started_at);
        }

        $battleLog = $logQuery->limit($setting->overlay_log_count)->get();

        return view('streamer.overlay', compact(
            'setting',
            'stats',
            'sessionName',
            'currentDeck',
            'deckStats',
            'battleLog',
            'activeSession'
        ));
    }

    /**
     * オーバーレイデータ（JSON API）
     */
    public function overlayData()
    {
        $user = Auth::user();
        $setting = $user->getOrCreateSetting();

        $activeSession = StreamerSession::getActiveSession($user->id);

        if ($activeSession) {
            $stats = $activeSession->getStats();
            $sessionName = $activeSession->name ?? '配信中';
        } else {
            $stats = $this->getTodayStats($user->id);
            $sessionName = '今日';
        }

        $lastBattle = Battle::where('user_id', $user->id)
            ->with('deck.leaderClass')
            ->orderBy('played_at', 'desc')
            ->first();

        $currentDeck = $lastBattle?->deck;
        $deckStats = null;

        if ($currentDeck) {
            $deckQuery = Battle::where('user_id', $user->id)
                ->where('deck_id', $currentDeck->id);

            if ($activeSession) {
                $deckQuery->where('played_at', '>=', $activeSession->started_at);
            } else {
                $deckQuery->whereDate('played_at', today());
            }

            $deckTotal = $deckQuery->count();
            $deckWins = (clone $deckQuery)->where('result', true)->count();
            $deckStats = [
                'name' => $currentDeck->name,
                'class' => $currentDeck->leaderClass->name,
                'total' => $deckTotal,
                'wins' => $deckWins,
                'losses' => $deckTotal - $deckWins,
                'win_rate' => $deckTotal > 0 ? round(($deckWins / $deckTotal) * 100, 1) : 0,
            ];
        }

        $logQuery = Battle::where('user_id', $user->id)
            ->with(['deck.leaderClass', 'myClass', 'opponentClass'])
            ->orderBy('played_at', 'desc');

        if ($activeSession) {
            $logQuery->where('played_at', '>=', $activeSession->started_at);
        }

        $battleLog = $logQuery->limit($setting->overlay_log_count)->get()->map(function ($battle) {
            return [
                'result' => $battle->result,
                'deck' => $battle->deck?->name ?? $battle->myClass?->name,
                'opponent' => $battle->opponentClass->name,
                'is_first' => $battle->is_first,
                'played_at' => $battle->played_at->diffForHumans(),
            ];
        });

        return response()->json([
            'session_name' => $sessionName,
            'stats' => $stats,
            'deck' => $deckStats,
            'log' => $battleLog,
        ]);
    }

    /**
     * セッション開始
     */
    public function startSession(Request $request)
    {
        $user = Auth::user();

        // 既存のアクティブセッションを終了
        StreamerSession::where('user_id', $user->id)
            ->where('is_active', true)
            ->update(['is_active' => false, 'ended_at' => now()]);

        // 新しいセッションを作成
        $session = StreamerSession::create([
            'user_id' => $user->id,
            'name' => $request->input('name'),
            'started_at' => now(),
            'is_active' => true,
            'streak_start' => $request->input('streak_start', 0),
        ]);

        return redirect()->back()->with('success', '戦績カウントを開始しました');
    }

    /**
     * セッション終了
     */
    public function endSession()
    {
        $user = Auth::user();

        StreamerSession::where('user_id', $user->id)
            ->where('is_active', true)
            ->update(['is_active' => false, 'ended_at' => now()]);

        return redirect()->back()->with('success', '戦績カウントを終了しました');
    }

    /**
     * 連勝カウンターリセット
     */
    public function resetStreak(Request $request)
    {
        $user = Auth::user();
        $activeSession = StreamerSession::getActiveSession($user->id);

        if ($activeSession) {
            $activeSession->update([
                'streak_start' => $request->input('streak_start', 0),
                'started_at' => now(), // 連勝計算の起点をリセット
            ]);
        }

        return redirect()->back()->with('success', '連勝カウンターをリセットしました');
    }

    /**
     * オーバーレイ設定更新
     */
    public function updateOverlaySettings(Request $request)
    {
        $user = Auth::user();
        $setting = $user->getOrCreateSetting();

        $validated = $request->validate([
            'overlay_bg_transparent' => 'boolean',
            'overlay_font_size' => 'in:small,medium,large,xlarge',
            'overlay_color_theme' => 'in:dark,light,custom',
            'overlay_custom_bg_color' => 'nullable|string|max:20',
            'overlay_custom_text_color' => 'nullable|string|max:20',
            'overlay_show_winrate' => 'boolean',
            'overlay_show_record' => 'boolean',
            'overlay_show_streak' => 'boolean',
            'overlay_show_deck' => 'boolean',
            'overlay_show_log' => 'boolean',
            'overlay_log_count' => 'integer|min:1|max:20',
        ]);

        $setting->update($validated);

        return redirect()->back()->with('success', 'オーバーレイ設定を更新しました');
    }

    private function getTodayStats($userId): array
    {
        $query = Battle::where('user_id', $userId)->whereDate('played_at', today());
        $total = $query->count();
        $wins = (clone $query)->where('result', true)->count();
        $losses = $total - $wins;
        $winRate = $total > 0 ? round(($wins / $total) * 100, 1) : 0;

        // 連勝計算
        $recentBattles = Battle::where('user_id', $userId)
            ->orderBy('played_at', 'desc')
            ->limit(50)
            ->get();

        $streak = 0;
        foreach ($recentBattles as $battle) {
            if ($battle->result) {
                $streak++;
            } else {
                break;
            }
        }

        return [
            'total' => $total,
            'wins' => $wins,
            'losses' => $losses,
            'win_rate' => $winRate,
            'streak' => $streak,
        ];
    }
}
