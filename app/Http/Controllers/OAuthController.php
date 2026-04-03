<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class OAuthController extends Controller
{
    protected array $providers = ['google', 'discord'];

    public function redirectToProvider(string $provider): RedirectResponse
    {
        if (! in_array($provider, $this->providers)) {
            return redirect()->route('login')->withErrors(['oauth' => '無効な認証プロバイダーです。']);
        }

        return $this->socialiteDriver($provider)->redirect();
    }

    public function handleProviderCallback(string $provider): RedirectResponse
    {
        if (! in_array($provider, $this->providers)) {
            return redirect()->route('login')->withErrors(['oauth' => '無効な認証プロバイダーです。']);
        }

        try {
            $socialUser = $this->socialiteDriver($provider)->user();
        } catch (\Throwable $e) {
            Log::warning('OAuth callback failed', [
                'provider' => $provider,
                'message' => $e->getMessage(),
            ]);

            return redirect()->route('login')->withErrors(['oauth' => '認証に失敗しました。もう一度お試しください。']);
        }

        $providerIdColumn = $provider.'_id';

        try {
            // プロバイダーIDで既存ユーザーを検索
            $user = User::where($providerIdColumn, $socialUser->getId())->first();

            if ($user) {
                Auth::login($user, true);

                return redirect()->route('battles.index');
            }

            $email = $socialUser->getEmail();

            if (blank($email)) {
                return redirect()->route('login')->withErrors([
                    'oauth' => $this->getProviderName($provider).'アカウントのメールアドレスを取得できませんでした。メールアドレス公開設定または認証状態を確認して、もう一度お試しください。',
                ]);
            }

            // メールアドレスで既存ユーザーを検索
            $user = User::where('email', $email)->first();

            if ($user) {
                // 既存ユーザーにプロバイダーIDを紐付け
                $user->update([
                    $providerIdColumn => $socialUser->getId(),
                    'avatar' => $user->avatar ?? $socialUser->getAvatar(),
                ]);

                Auth::login($user, true);

                return redirect()->route('battles.index')->with('success', $this->getProviderName($provider).'アカウントを連携しました。');
            }

            // 新規ユーザー作成
            $user = User::create([
                'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'ユーザー',
                'email' => $email,
                $providerIdColumn => $socialUser->getId(),
                'avatar' => $socialUser->getAvatar(),
                'email_verified_at' => now(),
            ]);

            Auth::login($user, true);

            return redirect()->route('battles.index')->with('success', 'ユーザー登録が完了しました。');
        } catch (\Throwable $e) {
            Log::error('OAuth user sync failed', [
                'provider' => $provider,
                'provider_user_id' => $socialUser->getId(),
                'message' => $e->getMessage(),
            ]);

            return redirect()->route('login')->withErrors(['oauth' => '認証後のアカウント処理に失敗しました。しばらくしてから再度お試しください。']);
        }
    }

    protected function getProviderName(string $provider): string
    {
        return match ($provider) {
            'google' => 'Google',
            'discord' => 'Discord',
            default => $provider,
        };
    }

    protected function socialiteDriver(string $provider)
    {
        $driver = Socialite::driver($provider);

        if ($provider === 'discord') {
            return $driver
                ->scopes(['identify', 'email'])
                ->with(['prompt' => 'consent']);
        }

        if ($provider === 'google') {
            return $driver->with(['prompt' => 'select_account']);
        }

        return $driver;
    }
}
