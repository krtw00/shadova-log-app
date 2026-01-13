<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class OAuthController extends Controller
{
    protected array $providers = ['google', 'discord'];

    public function redirectToProvider(string $provider): RedirectResponse
    {
        if (! in_array($provider, $this->providers)) {
            return redirect()->route('login')->withErrors(['oauth' => '無効な認証プロバイダーです。']);
        }

        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback(string $provider): RedirectResponse
    {
        if (! in_array($provider, $this->providers)) {
            return redirect()->route('login')->withErrors(['oauth' => '無効な認証プロバイダーです。']);
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors(['oauth' => '認証に失敗しました。もう一度お試しください。']);
        }

        $providerIdColumn = $provider.'_id';

        // プロバイダーIDで既存ユーザーを検索
        $user = User::where($providerIdColumn, $socialUser->getId())->first();

        if ($user) {
            Auth::login($user, true);

            return redirect()->route('battles.index');
        }

        // メールアドレスで既存ユーザーを検索
        if ($socialUser->getEmail()) {
            $user = User::where('email', $socialUser->getEmail())->first();

            if ($user) {
                // 既存ユーザーにプロバイダーIDを紐付け
                $user->update([
                    $providerIdColumn => $socialUser->getId(),
                    'avatar' => $user->avatar ?? $socialUser->getAvatar(),
                ]);

                Auth::login($user, true);

                return redirect()->route('battles.index')->with('success', $this->getProviderName($provider).'アカウントを連携しました。');
            }
        }

        // 新規ユーザー作成
        $user = User::create([
            'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'ユーザー',
            'email' => $socialUser->getEmail(),
            $providerIdColumn => $socialUser->getId(),
            'avatar' => $socialUser->getAvatar(),
            'email_verified_at' => now(),
        ]);

        Auth::login($user, true);

        return redirect()->route('battles.index')->with('success', 'ユーザー登録が完了しました。');
    }

    protected function getProviderName(string $provider): string
    {
        return match ($provider) {
            'google' => 'Google',
            'discord' => 'Discord',
            default => $provider,
        };
    }
}
