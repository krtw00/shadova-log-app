<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GitHubService
{
    private string $token;
    private string $owner;
    private string $repo;
    private string $baseUrl = 'https://api.github.com';

    public function __construct()
    {
        $this->token = config('services.github.token') ?? '';
        $this->owner = config('services.github.owner') ?? 'krtw00';
        $this->repo = config('services.github.repo') ?? 'shadova-log-app';
    }

    /**
     * GitHub Issueを作成する
     *
     * @param string $title Issueタイトル
     * @param string $body Issue本文（Markdown）
     * @param array $labels 適用するラベル
     * @return array|null 成功時はレスポンスデータ、失敗時はnull
     */
    public function createIssue(string $title, string $body, array $labels = []): ?array
    {
        if (empty($this->token)) {
            Log::error('GitHubService: GITHUB_TOKEN is not configured');
            return null;
        }

        try {
            $response = Http::withToken($this->token)
                ->withHeaders([
                    'Accept' => 'application/vnd.github.v3+json',
                    'X-GitHub-Api-Version' => '2022-11-28',
                ])
                ->post("{$this->baseUrl}/repos/{$this->owner}/{$this->repo}/issues", [
                    'title' => $title,
                    'body' => $body,
                    'labels' => $labels,
                ]);

            if ($response->successful()) {
                Log::info('GitHubService: Issue created successfully', [
                    'issue_number' => $response->json('number'),
                    'url' => $response->json('html_url'),
                ]);
                return $response->json();
            }

            Log::error('GitHubService: Failed to create issue', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('GitHubService: Exception occurred', [
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
