<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class OAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_discord_redirect_requests_email_scope(): void
    {
        $provider = Mockery::mock();
        $provider->shouldReceive('scopes')
            ->once()
            ->with(['identify', 'email'])
            ->andReturnSelf();
        $provider->shouldReceive('with')
            ->once()
            ->with(['prompt' => 'consent'])
            ->andReturnSelf();
        $provider->shouldReceive('redirect')
            ->once()
            ->andReturn(redirect('https://discord.com/oauth2/authorize'));

        Socialite::shouldReceive('driver')
            ->once()
            ->with('discord')
            ->andReturn($provider);

        $response = $this->get(route('oauth.redirect', 'discord'));

        $response->assertRedirect('https://discord.com/oauth2/authorize');
    }

    public function test_google_callback_creates_a_new_user(): void
    {
        $provider = Mockery::mock();
        $provider->shouldReceive('with')
            ->once()
            ->with(['prompt' => 'select_account'])
            ->andReturnSelf();
        $provider->shouldReceive('user')
            ->once()
            ->andReturn($this->makeSocialiteUser(
                id: 'google-user-1',
                email: 'oauth@example.com',
                name: 'OAuth User',
                avatar: 'https://example.com/avatar.png',
            ));

        Socialite::shouldReceive('driver')
            ->once()
            ->with('google')
            ->andReturn($provider);

        $response = $this->get(route('oauth.callback', 'google'));

        $response->assertRedirect(route('battles.index'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'oauth@example.com',
            'google_id' => 'google-user-1',
        ]);
    }

    public function test_google_callback_links_existing_user_by_email(): void
    {
        $user = User::factory()->create([
            'email' => 'existing@example.com',
            'google_id' => null,
        ]);

        $provider = Mockery::mock();
        $provider->shouldReceive('with')
            ->once()
            ->with(['prompt' => 'select_account'])
            ->andReturnSelf();
        $provider->shouldReceive('user')
            ->once()
            ->andReturn($this->makeSocialiteUser(
                id: 'google-user-2',
                email: 'existing@example.com',
                name: 'Existing User',
                avatar: 'https://example.com/avatar.png',
            ));

        Socialite::shouldReceive('driver')
            ->once()
            ->with('google')
            ->andReturn($provider);

        $response = $this->get(route('oauth.callback', 'google'));

        $response->assertRedirect(route('battles.index'));
        $this->assertAuthenticatedAs($user->fresh());
        $this->assertSame('google-user-2', $user->fresh()->google_id);
    }

    public function test_google_redirect_prompts_for_account_selection(): void
    {
        $provider = Mockery::mock();
        $provider->shouldReceive('with')
            ->once()
            ->with(['prompt' => 'select_account'])
            ->andReturnSelf();
        $provider->shouldReceive('redirect')
            ->once()
            ->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));

        Socialite::shouldReceive('driver')
            ->once()
            ->with('google')
            ->andReturn($provider);

        $response = $this->get(route('oauth.redirect', 'google'));

        $response->assertRedirect('https://accounts.google.com/o/oauth2/auth');
    }

    public function test_discord_callback_requires_email_for_new_user_creation(): void
    {
        $provider = Mockery::mock();
        $provider->shouldReceive('scopes')
            ->once()
            ->with(['identify', 'email'])
            ->andReturnSelf();
        $provider->shouldReceive('with')
            ->once()
            ->with(['prompt' => 'consent'])
            ->andReturnSelf();
        $provider->shouldReceive('user')
            ->once()
            ->andReturn($this->makeSocialiteUser(
                id: 'discord-user-1',
                email: null,
                name: 'Discord User',
                avatar: 'https://example.com/avatar.png',
            ));

        Socialite::shouldReceive('driver')
            ->once()
            ->with('discord')
            ->andReturn($provider);

        $response = $this->from(route('login'))->get(route('oauth.callback', 'discord'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('oauth');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', [
            'discord_id' => 'discord-user-1',
        ]);
    }

    private function makeSocialiteUser(string $id, ?string $email, string $name, ?string $avatar): SocialiteUser
    {
        $user = new SocialiteUser;
        $user->map([
            'id' => $id,
            'nickname' => null,
            'name' => $name,
            'email' => $email,
            'avatar' => $avatar,
        ]);

        return $user;
    }
}
