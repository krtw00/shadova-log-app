<?php

namespace App\Http\Controllers;

use App\Services\GitHubService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    public function __construct(
        private GitHubService $github
    ) {}

    public function index()
    {
        return view('feedback.index');
    }

    /**
     * バグ報告を送信
     */
    public function storeBug(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'steps' => 'nullable|string|max:3000',
            'expected' => 'nullable|string|max:1000',
            'actual' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();
        $body = $this->buildBugReportBody($validated, $request, $user);

        $result = $this->github->createIssue(
            title: "[Bug] {$validated['title']}",
            body: $body,
            labels: ['bug', 'user-reported']
        );

        if ($result) {
            return redirect()->route('feedback.index')
                ->with('success', 'バグ報告を送信しました。ご報告ありがとうございます！');
        }

        return redirect()->route('feedback.index')
            ->with('error', '送信に失敗しました。しばらく経ってから再度お試しください。');
    }

    /**
     * 機能要望を送信
     */
    public function storeEnhancement(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'use_case' => 'nullable|string|max:2000',
        ]);

        $user = Auth::user();
        $body = $this->buildEnhancementBody($validated, $user);

        $result = $this->github->createIssue(
            title: "[Feature Request] {$validated['title']}",
            body: $body,
            labels: ['enhancement', 'user-reported']
        );

        if ($result) {
            return redirect()->route('feedback.index')
                ->with('success', '機能リクエストを送信しました。ご要望ありがとうございます！');
        }

        return redirect()->route('feedback.index')
            ->with('error', '送信に失敗しました。しばらく経ってから再度お試しください。');
    }

    /**
     * お問い合わせを送信
     */
    public function storeContact(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        $user = Auth::user();
        $body = $this->buildContactBody($validated, $user);

        $result = $this->github->createIssue(
            title: "[Question] {$validated['subject']}",
            body: $body,
            labels: ['question', 'user-reported']
        );

        if ($result) {
            return redirect()->route('feedback.index')
                ->with('success', 'お問い合わせを送信しました。確認次第ご連絡いたします。');
        }

        return redirect()->route('feedback.index')
            ->with('error', '送信に失敗しました。しばらく経ってから再度お試しください。');
    }

    private function buildBugReportBody(array $data, Request $request, $user): string
    {
        $userAgent = $request->userAgent() ?? 'Unknown';
        $now = now()->toIso8601String();
        $steps = $data['steps'] ?? '未記入';
        $expected = $data['expected'] ?? '未記入';
        $actual = $data['actual'] ?? '未記入';

        return <<<MD
## 説明
{$data['description']}

## 再現手順
{$steps}

## 期待する動作
{$expected}

## 実際の動作
{$actual}

---
**報告者:** User ID {$user->id}
**ブラウザ:** {$userAgent}
**送信日時:** {$now}
MD;
    }

    private function buildEnhancementBody(array $data, $user): string
    {
        $now = now()->toIso8601String();
        $useCase = $data['use_case'] ?? '未記入';

        return <<<MD
## 説明
{$data['description']}

## ユースケース
{$useCase}

---
**要望者:** User ID {$user->id}
**送信日時:** {$now}
MD;
    }

    private function buildContactBody(array $data, $user): string
    {
        $now = now()->toIso8601String();

        return <<<MD
## メッセージ
{$data['message']}

---
**送信者:** {$user->name} (ID: {$user->id}, Email: {$user->email})
**送信日時:** {$now}
MD;
    }
}
