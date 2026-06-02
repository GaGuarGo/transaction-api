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
            User::where('username', $username)->update(['balance' => $balance]);
        }

        $response = $this->postJson('/api/users/signin', [
            'username' => $username,
            'password' => 'Password1',
        ]);

        return [
            'token' => $response->json('token'),
            'id' => User::where('username', $username)->value('id'),
        ];
    }

    public function test_transfer_succeeds_and_updates_balances(): void
    {
        $sender = $this->createAndLogin('sender', 1000);
        $receiver = $this->createAndLogin('receiver', 0);

        $this->withToken($sender['token'])->postJson('/api/transfer', [
            'toId' => $receiver['id'],
            'amount' => 300,
        ])->assertStatus(204);

        $this->assertDatabaseHas('users', ['id' => $sender['id'], 'balance' => 700]);
        $this->assertDatabaseHas('users', ['id' => $receiver['id'], 'balance' => 300]);
        $this->assertDatabaseHas('transactions', [
            'sender_id' => $sender['id'],
            'receiver_id' => $receiver['id'],
            'amount' => 300,
        ]);
    }

    public function test_transfer_fails_with_insufficient_balance(): void
    {
        $sender = $this->createAndLogin('sender', 100);
        $receiver = $this->createAndLogin('receiver', 0);

        $this->withToken($sender['token'])->postJson('/api/transfer', [
            'toId' => $receiver['id'],
            'amount' => 500,
        ])->assertStatus(422);

        $this->assertDatabaseHas('users', ['id' => $sender['id'], 'balance' => 100]);
    }

    public function test_transfer_fails_to_self(): void
    {
        $sender = $this->createAndLogin('sender', 1000);

        $this->withToken($sender['token'])->postJson('/api/transfer', [
            'toId' => $sender['id'],
            'amount' => 100,
        ])->assertStatus(422);
    }

    public function test_transaction_history_shows_sent_and_received(): void
    {
        $sender = $this->createAndLogin('sender', 1000);
        $receiver = $this->createAndLogin('receiver', 500);

        $this->withToken($sender['token'])->postJson('/api/transfer', [
            'toId' => $receiver['id'],
            'amount' => 200,
        ])->assertStatus(204);

        $this->resetAuthGuards();
        $senderHistory = $this->withToken($sender['token'])->getJson('/api/users/me/transactions');
        $senderHistory->assertStatus(200);
        $this->assertEquals('sent', $senderHistory->json('0.type'));
        $this->assertEquals($receiver['id'], $senderHistory->json('0.toId'));

        $this->resetAuthGuards();
        $receiverHistory = $this->withToken($receiver['token'])->getJson('/api/users/me/transactions');
        $receiverHistory->assertStatus(200);
        $this->assertEquals('received', $receiverHistory->json('0.type'));
        $this->assertEquals($sender['id'], $receiverHistory->json('0.fromId'));
    }

    public function test_transfer_requires_auth(): void
    {
        $this->postJson('/api/transfer', ['toId' => 1, 'amount' => 100])->assertStatus(401);
    }
}
