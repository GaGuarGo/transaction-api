<?php

namespace Tests\Integration;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    use RefreshDatabase;

    private function signup(array $overrides = []): array
    {
        $data = array_merge([
            'username' => 'joao',
            'email' => 'joao@example.com',
            'password' => 'Password1',
            'birthdate' => '2000-01-01',
        ], $overrides);

        $this->postJson('/api/users/signup', $data)->assertStatus(201);

        return $data;
    }

    private function signin(string $username = 'joao', string $password = 'Password1'): string
    {
        $response = $this->postJson('/api/users/signin', [
            'username' => $username,
            'password' => $password,
        ]);

        $response->assertStatus(200);

        return $response->json('token');
    }

    public function test_signup_creates_user_and_default_wallet(): void
    {
        $response = $this->postJson('/api/users/signup', [
            'username' => 'joao',
            'email' => 'joao@example.com',
            'password' => 'Password1',
            'birthdate' => '2000-01-01',
        ]);

        $response->assertStatus(201)->assertJsonStructure(['id']);
        $this->assertDatabaseHas('users', ['username' => 'joao']);
        $this->assertDatabaseHas('wallets', ['name' => 'default']);
    }

    public function test_signup_fails_with_duplicate_username(): void
    {
        $this->signup();

        $this->postJson('/api/users/signup', [
            'username' => 'joao',
            'email' => 'outro@example.com',
            'password' => 'Password1',
            'birthdate' => '2000-01-01',
        ])->assertStatus(422);
    }

    public function test_signup_fails_with_weak_password(): void
    {
        $this->postJson('/api/users/signup', [
            'username' => 'joao',
            'email' => 'joao@example.com',
            'password' => '123456',
            'birthdate' => '2000-01-01',
        ])->assertStatus(422);
    }

    public function test_signin_returns_token(): void
    {
        $this->signup();

        $this->postJson('/api/users/signin', [
            'username' => 'joao',
            'password' => 'Password1',
        ])->assertStatus(200)->assertJsonStructure(['token', 'expiresIn']);
    }

    public function test_signin_fails_with_wrong_password(): void
    {
        $this->signup();

        $this->postJson('/api/users/signin', [
            'username' => 'joao',
            'password' => 'wrong',
        ])->assertStatus(401);
    }

    public function test_list_users_requires_auth(): void
    {
        $this->getJson('/api/users')->assertStatus(401);
    }

    public function test_list_users_returns_all(): void
    {
        $this->signup();
        $token = $this->signin();

        $this->withToken($token)->getJson('/api/users')
            ->assertStatus(200)
            ->assertJsonStructure([['id', 'username', 'birthdate']]);
    }

    public function test_update_user(): void
    {
        $this->signup();
        $token = $this->signin();

        $this->withToken($token)->putJson('/api/users/me', [
            'username' => 'joaonovo',
            'email' => 'joaonovo@example.com',
        ])->assertStatus(200)->assertJsonFragment(['username' => 'joaonovo']);

        $this->assertDatabaseHas('users', ['username' => 'joaonovo']);
    }

    public function test_delete_user(): void
    {
        $this->signup();
        $token = $this->signin();

        $this->withToken($token)->deleteJson('/api/users/me')->assertStatus(204);
        $this->assertSoftDeleted('users', ['username' => 'joao']);
    }

    public function test_auth_refresh_returns_new_token(): void
    {
        $this->signup();
        $token = $this->signin();

        $response = $this->withToken($token)->postJson('/api/auth/refresh');
        $response->assertStatus(200)->assertJsonStructure(['token', 'expiresIn']);
        $this->assertNotEquals($token, $response->json('token'));
    }

    public function test_auth_logout_invalidates_token(): void
    {
        $this->signup();
        $token = $this->signin();

        $this->withToken($token)->postJson('/api/auth/logout')->assertStatus(204);
        $this->resetAuthGuards();
        $this->withToken($token)->getJson('/api/users')->assertStatus(401);
    }

    public function test_auth_validate_returns_valid(): void
    {
        $this->signup();
        $token = $this->signin();

        $this->withToken($token)->postJson('/api/auth/validate')
            ->assertStatus(200)
            ->assertJson(['valid' => true]);
    }
}
