<?php

namespace App\Http\Controllers;

use App\Models\Battle;
use App\Models\Deck;
use App\Models\GameMode;
use App\Models\Group;
use App\Models\LeaderClass;
use App\Models\Rank;
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
        $setting = $user->getOrCreateSetting();
        $gameModeCode = $request->get('mode', 'RANK');
        $gameMode = GameMode::where('code', $gameModeCode)->first();
        $perPage = $request->get('per_page', $setting->per_page);

        $battles = Battle::with(['deck.leaderClass', 'myClass', 'opponentClass', 'gameMode', 'rank', 'group'])
            ->where('user_id', $user->id)
            ->when($gameMode, fn($q) => $q->where('game_mode_id', $gameMode->id))
            ->orderBy('played_at', 'desc')
            ->paginate($perPage);

        $decks = $user->decks()->withCount(['battles', 'battles as wins_count' => function($q) {
            $q->whereRaw('result is true');
        }])->with('leaderClass')->get();
        $leaderClasses = LeaderClass::all();
        $gameModes = GameMode::all();
        $ranks = Rank::orderBy('sort_order')->get();
        $groups = Group::orderBy('sort_order')->get();

        // 共有リンク
        $shareLinks = $user->shareLinks()->orderBy('created_at', 'desc')->get();

        // 前回の対戦記録（ランク/グループ引き継ぎ用）
        $lastBattle = Battle::with('rank')
            ->where('user_id', $user->id)
            ->whereNotNull('rank_id')
            ->orderBy('played_at', 'desc')
            ->first();

        // 統計情報
        $stats = $this->getStats($user, $gameMode);

        return view('battles.index', compact(
            'battles',
            'decks',
            'leaderClasses',
            'gameModes',
            'gameMode',
            'stats',
            'shareLinks',
            'ranks',
            'groups',
            'perPage',
            'lastBattle'
        ));
    }

    public function store(Request $request)
    {
        $gameMode = GameMode::find($request->game_mode_id);
        $is2Pick = $gameMode && $gameMode->code === '2PICK';

        $rules = [
            'opponent_class_id' => 'required|exists:leader_classes,id',
            'game_mode_id' => 'required|exists:game_modes,id',
            'rank_id' => 'nullable|exists:ranks,id',
            'group_id' => 'nullable|exists:groups,id',
            'result' => 'required|boolean',
            'is_first' => 'required|boolean',
            'notes' => 'nullable|string|max:500',
        ];

        if ($is2Pick) {
            $rules['my_class_id'] = 'required|exists:leader_classes,id';
            $rules['deck_id'] = 'nullable';
        } else {
            $rules['deck_id'] = 'required|exists:decks,id';
            $rules['my_class_id'] = 'nullable';
        }

        $validated = $request->validate($rules);

        $user = $this->getUser();

        // デッキが指定されている場合は自分のものか確認
        if (!empty($validated['deck_id'])) {
            $deck = Deck::where('id', $validated['deck_id'])
                ->where('user_id', $user->id)
                ->firstOrFail();
        }

        Battle::create([
            'user_id' => $user->id,
            'deck_id' => $validated['deck_id'] ?? null,
            'my_class_id' => $validated['my_class_id'] ?? null,
            'opponent_class_id' => $validated['opponent_class_id'],
            'game_mode_id' => $validated['game_mode_id'],
            'rank_id' => $validated['rank_id'] ?? null,
            'group_id' => $validated['group_id'] ?? null,
            'result' => $validated['result'],
            'is_first' => $validated['is_first'],
            'played_at' => now(),
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('battles.index', ['mode' => $gameMode->code])
            ->with('success', '対戦を記録しました');
    }

    public function update(Request $request, Battle $battle)
    {
        $user = $this->getUser();

        if ($battle->user_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'deck_id' => 'sometimes|exists:decks,id',
            'opponent_class_id' => 'sometimes|exists:leader_classes,id',
            'rank_id' => 'nullable|exists:ranks,id',
            'group_id' => 'nullable|exists:groups,id',
            'result' => 'sometimes|boolean',
            'is_first' => 'sometimes|boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        $battle->update($validated);

        return redirect()->back()->with('success', '対戦記録を更新しました');
    }

    public function destroy(Battle $battle)
    {
        $user = $this->getUser();

        if ($battle->user_id !== $user->id) {
            abort(403);
        }

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
            ->selectRaw('opponent_class_id, SUM(CASE WHEN result THEN 1 ELSE 0 END) as wins, COUNT(*) as total')
            ->groupBy('opponent_class_id')
            ->with('opponentClass')
            ->get();

        // 先攻後攻勝率
        $firstWins = (clone $query)->today()->whereRaw('is_first is true')->wins()->count();
        $firstTotal = (clone $query)->today()->whereRaw('is_first is true')->count();
        $secondWins = (clone $query)->today()->whereRaw('is_first is false')->wins()->count();
        $secondTotal = (clone $query)->today()->whereRaw('is_first is false')->count();

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
