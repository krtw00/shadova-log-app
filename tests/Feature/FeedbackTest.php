<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_feedback_page(): void
    {
        $response = $this->get('/feedback');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_feedback_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/feedback');

        $response->assertStatus(200);
        $response->assertSee('フィードバック');
        $response->assertSee('バグ報告');
        $response->assertSee('機能要望');
        $response->assertSee('お問い合わせ');
    }

    public function test_bug_report_validation(): void
    {
        $user = User::factory()->create();
        $token = 'test-token';

        $response = $this->withSession(['_token' => $token])
            ->actingAs($user)
            ->postJson('/feedback/bug', [
                '_token' => $token,
                'title' => '',
                'description' => '',
            ], [
                'X-CSRF-TOKEN' => $token,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title', 'description']);
    }

    public function test_enhancement_request_validation(): void
    {
        $user = User::factory()->create();
        $token = 'test-token';

        $response = $this->withSession(['_token' => $token])
            ->actingAs($user)
            ->postJson('/feedback/enhancement', [
                '_token' => $token,
                'title' => '',
                'description' => '',
            ], [
                'X-CSRF-TOKEN' => $token,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title', 'description']);
    }

    public function test_contact_form_validation(): void
    {
        $user = User::factory()->create();
        $token = 'test-token';

        $response = $this->withSession(['_token' => $token])
            ->actingAs($user)
            ->postJson('/feedback/contact', [
                '_token' => $token,
                'subject' => '',
                'message' => '',
            ], [
                'X-CSRF-TOKEN' => $token,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['subject', 'message']);
    }
}
