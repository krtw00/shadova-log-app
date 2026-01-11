<?php

namespace App\Http\Controllers;

use App\Models\ShareLink;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShareController extends Controller
{
    private function getUser()
    {
        // 開発用: 認証がない場合はテストユーザーを使用
        return Auth::user() ?? User::first();
    }

    public function store(Request $request)
    {
        $user = $this->getUser();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:100|regex:/^[a-z0-9-]+$/',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        // ユーザー名が未設定の場合はエラー
        if (!$user->username) {
            return redirect()->back()
                ->withErrors(['username' => 'ユーザー名を先に設定してください'])
                ->withInput();
        }

        // スラッグの重複チェック
        $exists = ShareLink::where('user_id', $user->id)
            ->where('slug', $validated['slug'])
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->withErrors(['slug' => 'このスラッグは既に使用されています'])
                ->withInput();
        }

        ShareLink::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'is_active' => true,
        ]);

        return redirect()->route('battles.index')
            ->with('success', '共有リンクを作成しました');
    }

    public function update(Request $request, ShareLink $shareLink)
    {
        $user = $this->getUser();

        if ($shareLink->user_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
        ]);

        $shareLink->update($validated);

        return redirect()->back()->with('success', '共有リンクを更新しました');
    }

    public function destroy(ShareLink $shareLink)
    {
        $user = $this->getUser();

        if ($shareLink->user_id !== $user->id) {
            abort(403);
        }

        $shareLink->delete();

        return redirect()->route('battles.index')
            ->with('success', '共有リンクを削除しました');
    }

    public function toggle(ShareLink $shareLink)
    {
        $user = $this->getUser();

        if ($shareLink->user_id !== $user->id) {
            abort(403);
        }

        $shareLink->update(['is_active' => !$shareLink->is_active]);

        $status = $shareLink->is_active ? '公開' : '非公開';
        return redirect()->back()->with('success', "共有リンクを{$status}にしました");
    }

    public function updateUsername(Request $request)
    {
        $user = $this->getUser();

        $validated = $request->validate([
            'username' => 'required|string|max:50|regex:/^[a-z0-9-]+$/|unique:users,username',
        ]);

        $user->update(['username' => $validated['username']]);

        return redirect()->back()->with('success', 'ユーザー名を設定しました');
    }
}
