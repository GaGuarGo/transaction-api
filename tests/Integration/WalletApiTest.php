<?php

namespace Tests\Integration;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletApiTest extends TestCase
{
    use RefreshDatabase;

    private function createAndLogin(string $username = 'joao'): array
    {
        $this->postJson('/api/users/signup', [
            'username' => $username,
            'email' => "{$username}@example.com",
            'password' => 'Password1',
            'birthdate' => '2000-01-01',
        ])->assertStatus(201);

        $response = $this->postJson('/api/users/signin', [
            'username' => $username,
            'password' => 'Password1',
        ]);

        return [
            'token' => $response->json('token'),
            'id' => User::where('username', $username)->value('id'),
        ];
    }

    public function test_signup_creates_default_wallet(): void
    {
        $user = $this->createAndLogin();
        $token = $user['token'];

        $response = $this->withToken($token)->getJson('/api/wallets');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json());
        $this->assertEquals('default', $response->json('0.name'));
        $this->assertEquals(0, $response->json('0.balance'));
    }

    public function test_create_additional_wallet(): void
    {
        $user = $this->createAndLogin();

        $response = $this->withToken($user['token'])->postJson('/api/wallets', [
            'name' => 'savings',
        ]);

        $response->assertStatus(201)->assertJsonFragment(['name' => 'savings', 'balance' => 0]);

        $wallets = $this->withToken($user['token'])->getJson('/api/wallets')->json();
        $this->assertCount(2, $wallets);
    }

    public function test_show_wallet_returns_details(): void
    {
        $user = $this->createAndLogin();

        $wallets = $this->withToken($user['token'])->getJson('/api/wallets')->json();
        $walletId = $wallets[0]['id'];

        $this->withToken($user['token'])->getJson("/api/wallets/{$walletId}")
            ->assertStatus(200)
            ->assertJsonStructure(['id', 'name', 'balance']);
    }

    public function test_cannot_access_another_users_wallet(): void
    {
        $this->createAndLogin('joao');
        $maria = $this->createAndLogin('maria');

        $this->resetAuthGuards();
        $joaoWallets = $this->withToken($this->postJson('/api/users/signin', [
            'username' => 'joao', 'password' => 'Password1',
        ])->json('token'))->getJson('/api/wallets')->json();

        $joaoWalletId = $joaoWallets[0]['id'];

        $this->resetAuthGuards();
        $this->withToken($maria['token'])->getJson("/api/wallets/{$joaoWalletId}")
            ->assertStatus(403);
    }
}
