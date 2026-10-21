<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_and_receives_token(): void
    {
        User::factory()->create(['email' => 'a@example.com', 'password' => 'secret123']);

        $this->postJson('/api/login', ['email' => 'a@example.com', 'password' => 'secret123'])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['token', 'user' => ['id', 'name', 'email']]]);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create(['email' => 'a@example.com', 'password' => 'secret123']);

        $this->postJson('/api/login', ['email' => 'a@example.com', 'password' => 'wrong'])
            ->assertStatus(401)
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrorsFor('email', 'errors');
    }

    public function test_login_requires_fields(): void
    {
        $this->postJson('/api/login', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password'], 'errors');
    }

    public function test_product_endpoints_require_token(): void
    {
        $this->getJson('/api/products')->assertStatus(401)->assertJsonPath('success', false);
        $this->getJson('/api/products/stats')->assertStatus(401);
        $this->postJson('/api/products', [])->assertStatus(401);
    }

    public function test_logout_revokes_token(): void
    {
        $user = User::factory()->create(['password' => 'secret123']);
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/logout')->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
