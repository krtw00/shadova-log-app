<?php

namespace App\Http\Controllers;

use App\Models\Battle;
use App\Models\Deck;
use App\Models\LeaderClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $period = $request->get('period', 'total');

        // 期間に応じたクエリのベースを作成
        $baseQuery = Battle::where('user_id', $user->id);

        switch ($period) {
            case 'today':
                $baseQuery->whereDate('played_at', today());
                break;
            case 'week':
                $baseQuery->whereBetween('played_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $baseQuery->whereMonth('played_at', now()->month)
                          ->whereYear('played_at', now()->year);
                break;
            // 'total' は全期間なのでフィルターなし
        }

        // 総合統計
        $totalBattles = (clone $baseQuery)->count();
        $totalWins = (clone $baseQuery)->where('result', true)->count();
        $totalLosses = $totalBattles - $totalWins;
        $winRate = $totalBattles > 0 ? round(($totalWins / $totalBattles) * 100, 1) : 0;

        // 最高連勝（全期間）
        $maxStreak = $this->calculateMaxStreak($user->id);

        // 登録デッキ数
        $deckCount = Deck::where('user_id', $user->id)->count();
        $activeDeckCount = Deck::where('user_id', $user->id)
            ->whereHas('battles', function ($q) {
                $q->where('played_at', '>=', now()->subDays(30));
            })
            ->count();

        // 先攻/後攻勝率
        $firstBattles = (clone $baseQuery)->where('is_first', true);
        $firstTotal = (clone $firstBattles)->count();
        $firstWins = (clone $firstBattles)->where('result', true)->count();
        $firstWinRate = $firstTotal > 0 ? round(($firstWins / $firstTotal) * 100, 1) : 0;

        $secondBattles = (clone $baseQuery)->where('is_first', false);
        $secondTotal = (clone $secondBattles)->count();
        $secondWins = (clone $secondBattles)->where('result', true)->count();
        $secondWinRate = $secondTotal > 0 ? round(($secondWins / $secondTotal) * 100, 1) : 0;

        // クラス別対戦成績
        $classStats = (clone $baseQuery)
            ->select(
                'opponent_class_id',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN result = 1 THEN 1 ELSE 0 END) as wins')
            )
            ->groupBy('opponent_class_id')
            ->get()
            ->map(function ($stat) {
                $stat->losses = $stat->total - $stat->wins;
                $stat->win_rate = $stat->total > 0 ? round(($stat->wins / $stat->total) * 100, 1) : 0;
                $stat->class = LeaderClass::find($stat->opponent_class_id);
                return $stat;
            });

        // デッキ別統計
        $deckStats = Deck::where('user_id', $user->id)
            ->withCount(['battles as total_battles' => function ($q) use ($period) {
                $this->applyPeriodFilter($q, $period);
            }])
            ->withCount(['battles as wins' => function ($q) use ($period) {
                $this->applyPeriodFilter($q, $period);
                $q->where('result', true);
            }])
            ->with('leaderClass')
            ->get()
            ->map(function ($deck) {
                $deck->losses = $deck->total_battles - $deck->wins;
                $deck->win_rate = $deck->total_battles > 0
                    ? round(($deck->wins / $deck->total_battles) * 100, 1)
                    : 0;
                return $deck;
            })
            ->sortByDesc('total_battles');

        // 相性表データ（自分のクラス vs 相手クラス）
        $matchupData = $this->getMatchupData($user->id, $period);

        // 最近のアクティビティ（直近5件）
        $recentBattles = Battle::where('user_id', $user->id)
            ->with(['deck.leaderClass', 'myClass', 'opponentClass', 'gameMode'])
            ->orderBy('played_at', 'desc')
            ->limit(5)
            ->get();

        $leaderClasses = LeaderClass::all();

        return view('statistics.index', compact(
            'period',
            'totalBattles',
            'totalWins',
            'totalLosses',
            'winRate',
            'maxStreak',
            'deckCount',
            'activeDeckCount',
            'firstTotal',
            'firstWins',
            'firstWinRate',
            'secondTotal',
            'secondWins',
            'secondWinRate',
            'classStats',
            'deckStats',
            'matchupData',
            'recentBattles',
            'leaderClasses'
        ));
    }

    private function applyPeriodFilter($query, $period)
    {
        switch ($period) {
            case 'today':
                $query->whereDate('played_at', today());
                break;
            case 'week':
                $query->whereBetween('played_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('played_at', now()->month)
                      ->whereYear('played_at', now()->year);
                break;
        }
        return $query;
    }

    private function calculateMaxStreak($userId)
    {
        $battles = Battle::where('user_id', $userId)
            ->orderBy('played_at', 'asc')
            ->pluck('result');

        $maxStreak = 0;
        $currentStreak = 0;

        foreach ($battles as $result) {
            if ($result) {
                $currentStreak++;
                $maxStreak = max($maxStreak, $currentStreak);
            } else {
                $currentStreak = 0;
            }
        }

        return $maxStreak;
    }

    private function getMatchupData($userId, $period)
    {
        $query = Battle::where('battles.user_id', $userId);
        $this->applyPeriodFilter($query, $period);

        // デッキのクラスまたはmy_class_idと相手クラスでグループ化
        $results = $query
            ->select(
                DB::raw('COALESCE(decks.leader_class_id, battles.my_class_id) as my_class_id'),
                'battles.opponent_class_id',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN battles.result = 1 THEN 1 ELSE 0 END) as wins')
            )
            ->leftJoin('decks', 'battles.deck_id', '=', 'decks.id')
            ->groupBy('my_class_id', 'battles.opponent_class_id')
            ->get();

        // マトリックス形式に変換
        $matchup = [];
        foreach ($results as $row) {
            if (!$row->my_class_id) continue;

            if (!isset($matchup[$row->my_class_id])) {
                $matchup[$row->my_class_id] = [];
            }
            $matchup[$row->my_class_id][$row->opponent_class_id] = [
                'total' => $row->total,
                'wins' => $row->wins,
                'losses' => $row->total - $row->wins,
                'win_rate' => $row->total > 0 ? round(($row->wins / $row->total) * 100, 1) : 0,
            ];
        }

        return $matchup;
    }
}
