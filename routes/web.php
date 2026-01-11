<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BattleController;
use App\Http\Controllers\DeckController;
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

// 認証が必要なルート（開発中は認証なしで動作）
// TODO: 本番環境では auth ミドルウェアを追加
// Route::middleware(['auth'])->group(function () {

    // 対戦記録
    Route::get('/battles', [BattleController::class, 'index'])->name('battles.index');
    Route::post('/battles', [BattleController::class, 'store'])->name('battles.store');
    Route::put('/battles/{battle}', [BattleController::class, 'update'])->name('battles.update');
    Route::delete('/battles/{battle}', [BattleController::class, 'destroy'])->name('battles.destroy');

    // デッキ管理
    Route::get('/decks', [DeckController::class, 'index'])->name('decks.index');
    Route::post('/decks', [DeckController::class, 'store'])->name('decks.store');
    Route::put('/decks/{deck}', [DeckController::class, 'update'])->name('decks.update');
    Route::delete('/decks/{deck}', [DeckController::class, 'destroy'])->name('decks.destroy');
    Route::post('/decks/{deck}/toggle-active', [DeckController::class, 'toggleActive'])->name('decks.toggle-active');

    // 統計・分析
    Route::get('/statistics', function () {
        return view('statistics.index');
    })->name('statistics.index');

// });

// UIモック（開発用）
Route::get('/ui-mocks', function () {
    return redirect('/ui-mocks/index.html');
});