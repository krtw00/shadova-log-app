<?php

namespace App\Http\Controllers;

use App\Models\Battle;
use App\Models\Deck;
use App\Models\GameMode;
use App\Models\LeaderClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class BattleController extends Controller
{
    private function getUser()
    {
        // 開発用: 認証がない場合はテストユーザーを使用
        return Auth::user() ?? User::first();
    }

    public function index(Request $request)
    {
        $user = $this->getUser();
        $gameModeCode = $request->get('mode', 'RANK');
        $gameMode = GameMode::where('code', $gameModeCode)->first();

        $battles = Battle::with(['deck.leaderClass', 'opponentClass', 'gameMode'])
            ->where('user_id', $user->id)
            ->when($gameMode, fn($q) => $q->where('game_mode_id', $gameMode->id))
            ->orderBy('played_at', 'desc')
            ->paginate(20);

        $decks = $user->activeDecks()->with('leaderClass')->get();
        $leaderClasses = LeaderClass::all();
        $gameModes = GameMode::all();

        // 統計情報
        $stats = $this->getStats($user, $gameMode);

        return view('battles.index', compact(
            'battles',
            'decks',
            'leaderClasses',
            'gameModes',
            'gameMode',
            'stats'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'deck_id' => 'required|exists:decks,id',
            'opponent_class_id' => 'required|exists:leader_classes,id',
            'game_mode_id' => 'required|exists:game_modes,id',
            'result' => 'required|boolean',
            'is_first' => 'required|boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = $this->getUser();

        // デッキが自分のものか確認
        $deck = Deck::where('id', $validated['deck_id'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        Battle::create([
            'user_id' => $user->id,
            'deck_id' => $validated['deck_id'],
            'opponent_class_id' => $validated['opponent_class_id'],
            'game_mode_id' => $validated['game_mode_id'],
            'result' => $validated['result'],
            'is_first' => $validated['is_first'],
            'played_at' => now(),
            'notes' => $validated['notes'] ?? null,
        ]);

        $gameMode = GameMode::find($validated['game_mode_id']);

        return redirect()->route('battles.index', ['mode' => $gameMode->code])
            ->with('success', '対戦を記録しました');
    }

    public function update(Request $request, Battle $battle)
    {
        $this->authorize('update', $battle);

        $validated = $request->validate([
            'deck_id' => 'sometimes|exists:decks,id',
            'opponent_class_id' => 'sometimes|exists:leader_classes,id',
            'result' => 'sometimes|boolean',
            'is_first' => 'sometimes|boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        $battle->update($validated);

        return redirect()->back()->with('success', '対戦記録を更新しました');
    }

    public function destroy(Battle $battle)
    {
        $this->authorize('delete', $battle);

        $battle->delete();

        return redirect()->back()->with('success', '対戦記録を削除しました');
    }

    private function getStats($user, $gameMode = null)
    {
        $query = Battle::where('user_id', $user->id);
        if ($gameMode) {
            $query->where('game_mode_id', $gameMode->id);
        }

        $todayQuery = (clone $query)->today();
        $todayWins = (clone $todayQuery)->wins()->count();
        $todayLosses = (clone $todayQuery)->losses()->count();
        $todayTotal = $todayWins + $todayLosses;
        $todayWinRate = $todayTotal > 0 ? round(($todayWins / $todayTotal) * 100, 1) : 0;

        // 連勝計算
        $recentBattles = (clone $query)->orderBy('played_at', 'desc')->limit(50)->get();
        $streak = 0;
        foreach ($recentBattles as $battle) {
            if ($battle->result) {
                $streak++;
            } else {
                break;
            }
        }

        // クラス別勝率
        $classBattles = (clone $query)->today()
            ->selectRaw('opponent_class_id, SUM(result) as wins, COUNT(*) as total')
            ->groupBy('opponent_class_id')
            ->with('opponentClass')
            ->get();

        // 先攻後攻勝率
        $firstWins = (clone $query)->today()->where('is_first', true)->wins()->count();
        $firstTotal = (clone $query)->today()->where('is_first', true)->count();
        $secondWins = (clone $query)->today()->where('is_first', false)->wins()->count();
        $secondTotal = (clone $query)->today()->where('is_first', false)->count();

        return [
            'today' => [
                'wins' => $todayWins,
                'losses' => $todayLosses,
                'winRate' => $todayWinRate,
            ],
            'streak' => $streak,
            'byClass' => $classBattles,
            'byTurn' => [
                'first' => [
                    'wins' => $firstWins,
                    'total' => $firstTotal,
                    'winRate' => $firstTotal > 0 ? round(($firstWins / $firstTotal) * 100, 1) : 0,
                ],
                'second' => [
                    'wins' => $secondWins,
                    'total' => $secondTotal,
                    'winRate' => $secondTotal > 0 ? round(($secondWins / $secondTotal) * 100, 1) : 0,
                ],
            ],
        ];
    }
}