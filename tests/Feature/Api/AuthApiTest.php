<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_a_token_and_the_authenticated_user(): void
    {
        $user = User::factory()->create(['email' => 'jane@example.com']);

        $response = $this->postJson('/api/login', [
            'email'    => 'jane@example.com',
            'password' => 'password',
        ]);

        $this->assertNotEmpty($response->json('token'));

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id'   => $user->id,
            'tokenable_type' => User::class,
        ]);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/login', [
            'email'    => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('email');
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_login_requires_email_and_password(): void
    {
        $this->postJson('/api/login', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_protected_endpoint_rejects_unauthenticated_requests(): void
    {
        $this->getJson('/api/projects')->assertStatus(401);
    }

    public function test_protected_endpoint_accepts_a_valid_token(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/projects')->assertOk();
    }

    public function test_logout_revokes_the_current_token(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $token = $user->createToken('test-token')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/logout')
            ->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
