<?php

namespace App\Http\Controllers;

use App\Models\Battle;
use App\Models\LeaderClass;
use App\Models\ShareLink;
use App\Models\User;
use Illuminate\Http\Request;

class PublicProfileController extends Controller
{
    public function show(string $username, string $slug)
    {
        $user = User::where('username', $username)->firstOrFail();

        $shareLink = ShareLink::where('user_id', $user->id)
            ->where('slug', $slug)
            ->whereRaw('is_active is true')
            ->firstOrFail();

        // 期間内の対戦記録を取得
        $battles = Battle::with(['deck.leaderClass', 'opponentClass', 'gameMode'])
            ->where('user_id', $user->id)
            ->whereBetween('played_at', [$shareLink->start_date, $shareLink->end_date->endOfDay()])
            ->orderBy('played_at', 'desc')
            ->get();

        // 統計情報を計算
        $stats = $this->calculateStats($battles);

        // デッキ別統計
        $deckStats = $this->calculateDeckStats($battles);

        // リーダークラス一覧（相手クラス表示用）
        $leaderClasses = LeaderClass::all()->keyBy('id');

        return view('shares.public', compact(
            'user',
            'shareLink',
            'battles',
            'stats',
            'deckStats',
            'leaderClasses'
        ));
    }

    private function calculateStats($battles)
    {
        $total = $battles->count();
        $wins = $battles->where('result', true)->count();
        $losses = $total - $wins;
        $winRate = $total > 0 ? round(($wins / $total) * 100, 1) : 0;

        // 先攻/後攻別
        $firstBattles = $battles->where('is_first', true);
        $firstWins = $firstBattles->where('result', true)->count();
        $firstTotal = $firstBattles->count();
        $firstWinRate = $firstTotal > 0 ? round(($firstWins / $firstTotal) * 100, 1) : 0;

        $secondBattles = $battles->where('is_first', false);
        $secondWins = $secondBattles->where('result', true)->count();
        $secondTotal = $secondBattles->count();
        $secondWinRate = $secondTotal > 0 ? round(($secondWins / $secondTotal) * 100, 1) : 0;

        // クラス別統計
        $byClass = $battles->groupBy('opponent_class_id')->map(function ($classBattles) {
            $total = $classBattles->count();
            $wins = $classBattles->where('result', true)->count();
            return [
                'total' => $total,
                'wins' => $wins,
                'winRate' => $total > 0 ? round(($wins / $total) * 100, 1) : 0,
            ];
        });

        // 連勝計算
        $streak = 0;
        foreach ($battles->sortByDesc('played_at') as $battle) {
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
            'winRate' => $winRate,
            'streak' => $streak,
            'byTurn' => [
                'first' => [
                    'total' => $firstTotal,
                    'wins' => $firstWins,
                    'winRate' => $firstWinRate,
                ],
                'second' => [
                    'total' => $secondTotal,
                    'wins' => $secondWins,
                    'winRate' => $secondWinRate,
                ],
            ],
            'byClass' => $byClass,
        ];
    }

    private function calculateDeckStats($battles)
    {
        return $battles->groupBy('deck_id')->map(function ($deckBattles) {
            $deck = $deckBattles->first()->deck;
            $total = $deckBattles->count();
            $wins = $deckBattles->where('result', true)->count();

            return [
                'deck' => $deck,
                'total' => $total,
                'wins' => $wins,
                'winRate' => $total > 0 ? round(($wins / $total) * 100, 1) : 0,
            ];
        })->sortByDesc('total');
    }
}
