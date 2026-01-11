<?php

namespace App\Http\Controllers;

use App\Models\Deck;
use App\Models\LeaderClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class DeckController extends Controller
{
    private function getUser()
    {
        // 開発用: 認証がない場合はテストユーザーを使用
        return Auth::user() ?? User::first();
    }

    public function index()
    {
        $user = $this->getUser();
        $decks = $user->decks()
            ->with('leaderClass')
            ->withCount(['battles', 'battles as wins_count' => fn($q) => $q->where('result', true)])
            ->orderBy('active', 'desc')
            ->orderBy('updated_at', 'desc')
            ->get();

        $leaderClasses = LeaderClass::all();

        return view('decks.index', compact('decks', 'leaderClasses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'leader_class_id' => 'required|exists:leader_classes,id',
        ]);

        $user = $this->getUser();

        Deck::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'leader_class_id' => $validated['leader_class_id'],
            'active' => true,
        ]);

        return redirect()->route('decks.index')
            ->with('success', 'デッキを作成しました');
    }

    public function update(Request $request, Deck $deck)
    {
        $this->authorize('update', $deck);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'leader_class_id' => 'sometimes|exists:leader_classes,id',
            'active' => 'sometimes|boolean',
        ]);

        $deck->update($validated);

        return redirect()->back()->with('success', 'デッキを更新しました');
    }

    public function destroy(Deck $deck)
    {
        $this->authorize('delete', $deck);

        // 関連する対戦記録があるかチェック
        if ($deck->battles()->exists()) {
            return redirect()->back()
                ->with('error', 'このデッキには対戦記録があるため削除できません。非アクティブにしてください。');
        }

        $deck->delete();

        return redirect()->route('decks.index')
            ->with('success', 'デッキを削除しました');
    }

    public function toggleActive(Deck $deck)
    {
        $this->authorize('update', $deck);

        $deck->update(['active' => !$deck->active]);

        $status = $deck->active ? 'アクティブ' : '非アクティブ';
        return redirect()->back()
            ->with('success', "デッキを{$status}にしました");
    }
}
