<?php

namespace App\Http\Controllers;

use App\Models\Battle;
use App\Models\GameMode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    private function getUser()
    {
        return Auth::user() ?? User::first();
    }

    public function index()
    {
        $user = $this->getUser();
        $setting = $user->getOrCreateSetting();
        $gameModes = GameMode::all();

        return view('settings.index', compact('user', 'setting', 'gameModes'));
    }

    public function updateProfile(Request $request)
    {
        $user = $this->getUser();

        $validated = $request->validate([
            'username' => ['nullable', 'string', 'min:3', 'max:50', 'regex:/^[a-z0-9-]+$/', 'unique:users,username,' . $user->id],
        ]);

        $user->update($validated);

        return redirect()->route('settings.index')->with('success', 'ユーザー名を更新しました');
    }

    public function updatePassword(Request $request)
    {
        $user = $this->getUser();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('settings.index')->with('success', 'パスワードを更新しました');
    }

    public function updatePreferences(Request $request)
    {
        $user = $this->getUser();
        $setting = $user->getOrCreateSetting();

        $validated = $request->validate([
            'default_game_mode_id' => ['nullable', 'exists:game_modes,id'],
            'theme' => ['required', 'in:dark,light'],
        ]);

        $setting->update($validated);

        return redirect()->route('settings.index')->with('success', '設定を更新しました');
    }

    public function updatePerPage(Request $request)
    {
        $user = $this->getUser();
        $setting = $user->getOrCreateSetting();

        $validated = $request->validate([
            'per_page' => ['required', 'integer', 'in:10,20,50,100'],
        ]);

        $setting->update($validated);

        return redirect()->back()->with('success', '表示件数を更新しました');
    }

    public function exportData(Request $request)
    {
        $user = $this->getUser();
        $format = $request->get('format', 'csv');

        $battles = Battle::with(['deck.leaderClass', 'myClass', 'opponentClass', 'gameMode', 'rank', 'group'])
            ->where('user_id', $user->id)
            ->orderBy('played_at', 'desc')
            ->get();

        if ($format === 'json') {
            $data = $battles->map(function ($battle) {
                return [
                    'played_at' => $battle->played_at->toIso8601String(),
                    'game_mode' => $battle->gameMode->name,
                    'deck' => $battle->deck?->name,
                    'my_class' => $battle->myClass?->name ?? $battle->deck?->leaderClass?->name,
                    'opponent_class' => $battle->opponentClass->name,
                    'result' => $battle->result ? 'win' : 'lose',
                    'is_first' => $battle->is_first,
                    'rank' => $battle->rank?->name,
                    'group' => $battle->group?->name,
                    'notes' => $battle->notes,
                ];
            });

            return response()->json($data)
                ->header('Content-Disposition', 'attachment; filename="battles_' . date('Ymd_His') . '.json"');
        }

        // CSV format
        $csvData = [];
        $csvData[] = ['日時', 'モード', 'デッキ', '自クラス', '相手クラス', '結果', '先攻/後攻', 'ランク', 'グループ', 'メモ'];

        foreach ($battles as $battle) {
            $csvData[] = [
                $battle->played_at->format('Y-m-d H:i:s'),
                $battle->gameMode->name,
                $battle->deck?->name ?? '',
                $battle->myClass?->name ?? $battle->deck?->leaderClass?->name ?? '',
                $battle->opponentClass->name,
                $battle->result ? '勝ち' : '負け',
                $battle->is_first ? '先攻' : '後攻',
                $battle->rank?->name ?? '',
                $battle->group?->name ?? '',
                $battle->notes ?? '',
            ];
        }

        $output = fopen('php://temp', 'r+');
        foreach ($csvData as $row) {
            fputcsv($output, $row);
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        // Add BOM for Excel compatibility
        $csv = "\xEF\xBB\xBF" . $csv;

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="battles_' . date('Ymd_His') . '.csv"');
    }

    public function deleteAllData(Request $request)
    {
        $user = $this->getUser();

        $request->validate([
            'confirm_delete' => ['required', 'in:DELETE'],
        ]);

        // Delete all battles
        Battle::where('user_id', $user->id)->delete();

        return redirect()->route('settings.index')->with('success', '全ての対戦記録を削除しました');
    }

    public function deleteAccount(Request $request)
    {
        $user = $this->getUser();

        $request->validate([
            'confirm_delete_account' => ['required', 'in:DELETE'],
            'password' => ['required', 'current_password'],
        ]);

        // Delete user (cascades to related data)
        $user->delete();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'アカウントを削除しました');
    }
}
