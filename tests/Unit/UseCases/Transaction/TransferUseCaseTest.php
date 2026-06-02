<?php

namespace Tests\Unit\UseCases\Transaction;

use App\Application\DTOs\TransferDTO;
use App\Application\Exceptions\InsufficientBalanceException;
use App\Application\Exceptions\UnauthorizedWalletAccessException;
use App\Application\Exceptions\WalletNotFoundException;
use App\Application\UseCases\Transaction\TransferUseCase;
use App\Domain\Transaction\Entities\Transaction;
use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;
use App\Domain\Wallet\Entities\Wallet;
use App\Domain\Wallet\Repositories\WalletRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class TransferUseCaseTest extends TestCase
{
    private WalletRepositoryInterface $walletRepo;

    private TransactionRepositoryInterface $txRepo;

    private TransferUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->walletRepo = Mockery::mock(WalletRepositoryInterface::class);
        $this->txRepo = Mockery::mock(TransactionRepositoryInterface::class);
        $this->useCase = new TransferUseCase($this->walletRepo, $this->txRepo);
    }

    private function makeWallet(string $id, string $userId, int $balance): Wallet
    {
        return new Wallet($id, $userId, 'default', $balance);
    }

    public function test_transfers_successfully(): void
    {
        $sender = $this->makeWallet('uuid-w-1', 'uuid-u-1', 1000);
        $receiver = $this->makeWallet('uuid-w-2', 'uuid-u-2', 500);

        $this->walletRepo->shouldReceive('findById')->with('uuid-w-1')->andReturn($sender);
        $this->walletRepo->shouldReceive('findById')->with('uuid-w-2')->andReturn($receiver);
        $this->walletRepo->shouldReceive('update')->twice();
        $this->txRepo->shouldReceive('save')->once()->andReturnUsing(
            fn ($tx) => new Transaction('uuid-tx-1', $tx->senderWalletId, $tx->receiverWalletId, $tx->amount)
        );

        DB::shouldReceive('transaction')->once()->andReturnUsing(fn ($cb) => $cb());

        $this->useCase->execute(new TransferDTO('uuid-w-1', 'uuid-w-2', 300), 'uuid-u-1');

        $this->assertTrue(true);
    }

    public function test_throws_when_insufficient_balance(): void
    {
        $this->expectException(InsufficientBalanceException::class);

        $sender = $this->makeWallet('uuid-w-1', 'uuid-u-1', 100);
        $receiver = $this->makeWallet('uuid-w-2', 'uuid-u-2', 0);

        $this->walletRepo->shouldReceive('findById')->with('uuid-w-1')->andReturn($sender);
        $this->walletRepo->shouldReceive('findById')->with('uuid-w-2')->andReturn($receiver);
        DB::shouldReceive('transaction')->never();

        $this->useCase->execute(new TransferDTO('uuid-w-1', 'uuid-w-2', 500), 'uuid-u-1');
    }

    public function test_throws_when_sender_wallet_not_found(): void
    {
        $this->expectException(WalletNotFoundException::class);

        $this->walletRepo->shouldReceive('findById')->with('uuid-w-99')->andReturn(null);
        $this->walletRepo->shouldReceive('findById')->with('uuid-w-2')->andReturn($this->makeWallet('uuid-w-2', 'uuid-u-2', 0));

        $this->useCase->execute(new TransferDTO('uuid-w-99', 'uuid-w-2', 100), 'uuid-u-1');
    }

    public function test_throws_when_user_does_not_own_sender_wallet(): void
    {
        $this->expectException(UnauthorizedWalletAccessException::class);

        $sender = $this->makeWallet('uuid-w-1', 'uuid-u-99', 1000); // owned by uuid-u-99
        $receiver = $this->makeWallet('uuid-w-2', 'uuid-u-2', 0);

        $this->walletRepo->shouldReceive('findById')->with('uuid-w-1')->andReturn($sender);
        $this->walletRepo->shouldReceive('findById')->with('uuid-w-2')->andReturn($receiver);

        $this->useCase->execute(new TransferDTO('uuid-w-1', 'uuid-w-2', 100), 'uuid-u-1');
    }
}
