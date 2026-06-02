<?php

namespace Tests\Integration;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionApiTest extends TestCase
{
    use RefreshDatabase;

    private function createAndLogin(string $username, int $balance = 0): array
    {
        $this->postJson('/api/users/signup', [
            'username' => $username,
            'email' => "{$username}@example.com",
            'password' => 'Password1',
            'birthdate' => '2000-01-01',
        ])->assertStatus(201);

        if ($balance > 0) {
            \App\Models\Wallet::whereHas('user', fn ($q) => $q->where('username', $username))
                ->update(['balance' => $balance]);
        }

        $response = $this->postJson('/api/users/signin', [
            'username' => $username,
            'password' => 'Password1',
        ]);

        $userId = User::where('username', $username)->value('id');
        $walletId = \App\Models\Wallet::where('user_id', $userId)->value('id');

        return [
            'token' => $response->json('token'),
            'id' => $userId,
            'walletId' => $walletId,
        ];
    }

    public function test_transfer_succeeds_and_updates_balances(): void
    {
        $sender = $this->createAndLogin('sender', 1000);
        $receiver = $this->createAndLogin('receiver', 0);

        $this->resetAuthGuards();
        $this->withToken($sender['token'])->postJson('/api/transfer', [
            'fromWalletId' => $sender['walletId'],
            'toWalletId' => $receiver['walletId'],
            'amount' => 300,
        ])->assertStatus(204);

        $this->assertDatabaseHas('wallets', ['id' => $sender['walletId'], 'balance' => 700]);
        $this->assertDatabaseHas('wallets', ['id' => $receiver['walletId'], 'balance' => 300]);
        $this->assertDatabaseHas('transactions', [
            'sender_wallet_id' => $sender['walletId'],
            'receiver_wallet_id' => $receiver['walletId'],
            'amount' => 300,
        ]);
    }

    public function test_transfer_fails_with_insufficient_balance(): void
    {
        $sender = $this->createAndLogin('sender', 100);
        $receiver = $this->createAndLogin('receiver', 0);

        $this->resetAuthGuards();
        $this->withToken($sender['token'])->postJson('/api/transfer', [
            'fromWalletId' => $sender['walletId'],
            'toWalletId' => $receiver['walletId'],
            'amount' => 500,
        ])->assertStatus(422);

        $this->assertDatabaseHas('wallets', ['id' => $sender['walletId'], 'balance' => 100]);
    }

    public function test_transfer_fails_to_same_wallet(): void
    {
        $sender = $this->createAndLogin('sender', 1000);

        $this->withToken($sender['token'])->postJson('/api/transfer', [
            'fromWalletId' => $sender['walletId'],
            'toWalletId' => $sender['walletId'],
            'amount' => 100,
        ])->assertStatus(422);
    }

    public function test_cannot_transfer_from_another_users_wallet(): void
    {
        $attacker = $this->createAndLogin('attacker', 0);
        $victim = $this->createAndLogin('victim', 1000);
        $receiver = $this->createAndLogin('receiver', 0);

        $this->resetAuthGuards();
        $this->withToken($attacker['token'])->postJson('/api/transfer', [
            'fromWalletId' => $victim['walletId'],
            'toWalletId' => $receiver['walletId'],
            'amount' => 100,
        ])->assertStatus(403);
    }

    public function test_transaction_history_shows_sent_and_received(): void
    {
        $sender = $this->createAndLogin('sender', 1000);
        $receiver = $this->createAndLogin('receiver', 0);

        $this->resetAuthGuards();
        $this->withToken($sender['token'])->postJson('/api/transfer', [
            'fromWalletId' => $sender['walletId'],
            'toWalletId' => $receiver['walletId'],
            'amount' => 200,
        ])->assertStatus(204);

        $this->resetAuthGuards();
        $senderHistory = $this->withToken($sender['token'])->getJson('/api/users/me/transactions');
        $senderHistory->assertStatus(200);
        $this->assertEquals('sent', $senderHistory->json('0.type'));

        $this->resetAuthGuards();
        $receiverHistory = $this->withToken($receiver['token'])->getJson('/api/users/me/transactions');
        $receiverHistory->assertStatus(200);
        $this->assertEquals('received', $receiverHistory->json('0.type'));
    }

    public function test_transaction_history_filtered_by_wallet(): void
    {
        $sender = $this->createAndLogin('sender', 1000);
        $receiver = $this->createAndLogin('receiver', 0);

        $this->resetAuthGuards();
        $this->withToken($sender['token'])->postJson('/api/transfer', [
            'fromWalletId' => $sender['walletId'],
            'toWalletId' => $receiver['walletId'],
            'amount' => 100,
        ])->assertStatus(204);

        $this->resetAuthGuards();
        $response = $this->withToken($sender['token'])
            ->getJson("/api/users/me/transactions?walletId={$sender['walletId']}");

        $response->assertStatus(200);
        $this->assertCount(1, $response->json());
        $this->assertEquals('sent', $response->json('0.type'));
    }

    public function test_transfer_requires_auth(): void
    {
        $this->postJson('/api/transfer', ['fromWalletId' => 1, 'toWalletId' => 2, 'amount' => 100])
            ->assertStatus(401);
    }
}
