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
        ]);

        return redirect()->route('battles.index')
            ->with('success', 'デッキを作成しました');
    }

    public function update(Request $request, Deck $deck)
    {
        $user = $this->getUser();

        // デッキが自分のものか確認
        if ($deck->user_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'leader_class_id' => 'sometimes|exists:leader_classes,id',
        ]);

        $deck->update($validated);

        return redirect()->route('battles.index')->with('success', 'デッキを更新しました');
    }

    public function destroy(Deck $deck)
    {
        $user = $this->getUser();

        // デッキが自分のものか確認
        if ($deck->user_id !== $user->id) {
            abort(403);
        }

        $deck->delete();

        return redirect()->route('battles.index')
            ->with('success', 'デッキを削除しました');
    }
}
