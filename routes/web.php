<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BattleController;
use App\Http\Controllers\DeckController;
use App\Http\Controllers\PublicProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ShareController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\StreamerController;
use Illuminate\Support\Facades\Route;

// 認証ルート（ゲスト用）
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // パスワードリセット
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

// ログアウト（認証済み用）
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// トップページは対戦記録にリダイレクト
Route::get('/', function () {
    return redirect()->route('battles.index');
});

// 認証が必要なルート
Route::middleware(['auth'])->group(function () {
    // 対戦記録
    Route::get('/battles', [BattleController::class, 'index'])->name('battles.index');
    Route::post('/battles', [BattleController::class, 'store'])->name('battles.store');
    Route::put('/battles/{battle}', [BattleController::class, 'update'])->name('battles.update');
    Route::delete('/battles/{battle}', [BattleController::class, 'destroy'])->name('battles.destroy');

    // デッキ管理（対戦記録画面に統合、APIルートのみ残す）
    Route::get('/decks', function () {
        return redirect()->route('battles.index');
    })->name('decks.index');
    Route::get('/decks/{deck}', function () {
        return redirect()->route('battles.index');
    });
    Route::post('/decks', [DeckController::class, 'store'])->name('decks.store');
    Route::put('/decks/{deck}', [DeckController::class, 'update'])->name('decks.update');
    Route::delete('/decks/{deck}', [DeckController::class, 'destroy'])->name('decks.destroy');

    // 統計・分析
    Route::get('/statistics', [StatisticsController::class, 'index'])->name('statistics.index');

    // 共有リンク管理
    Route::post('/shares', [ShareController::class, 'store'])->name('shares.store');
    Route::put('/shares/{shareLink}', [ShareController::class, 'update'])->name('shares.update');
    Route::delete('/shares/{shareLink}', [ShareController::class, 'destroy'])->name('shares.destroy');
    Route::post('/shares/{shareLink}/toggle', [ShareController::class, 'toggle'])->name('shares.toggle');
    Route::post('/profile/username', [ShareController::class, 'updateUsername'])->name('profile.username.update');

    // 設定
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile');
    Route::put('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');
    Route::put('/settings/preferences', [SettingsController::class, 'updatePreferences'])->name('settings.preferences');
    Route::put('/settings/per-page', [SettingsController::class, 'updatePerPage'])->name('settings.per-page');
    Route::put('/settings/streamer', [SettingsController::class, 'updateStreamerMode'])->name('settings.streamer');
    Route::get('/settings/export', [SettingsController::class, 'exportData'])->name('settings.export');
    Route::delete('/settings/data', [SettingsController::class, 'deleteAllData'])->name('settings.data.delete');
    Route::delete('/settings/account', [SettingsController::class, 'deleteAccount'])->name('settings.account.delete');

    // 配信者モード
    Route::get('/streamer', [StreamerController::class, 'index'])->name('streamer.index');
    Route::get('/streamer/overlay', [StreamerController::class, 'overlay'])->name('streamer.overlay');
    Route::get('/streamer/overlay/data', [StreamerController::class, 'overlayData'])->name('streamer.overlay.data');
    Route::post('/streamer/session/start', [StreamerController::class, 'startSession'])->name('streamer.session.start');
    Route::post('/streamer/session/end', [StreamerController::class, 'endSession'])->name('streamer.session.end');
    Route::post('/streamer/streak/reset', [StreamerController::class, 'resetStreak'])->name('streamer.streak.reset');
    Route::put('/streamer/overlay/settings', [StreamerController::class, 'updateOverlaySettings'])->name('streamer.overlay.settings');
});

// 公開プロフィール（認証不要）
Route::get('/u/{username}/{slug}', [PublicProfileController::class, 'show'])->name('profile.share');

// UIモック（開発用）
Route::get('/ui-mocks', function () {
    return redirect('/ui-mocks/index.html');
});