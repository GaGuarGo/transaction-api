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

    private function makeWallet(int $id, int $userId, int $balance): Wallet
    {
        return new Wallet($id, $userId, 'default', $balance);
    }

    public function test_transfers_successfully(): void
    {
        $sender = $this->makeWallet(1, 1, 1000);
        $receiver = $this->makeWallet(2, 2, 500);

        $this->walletRepo->shouldReceive('findById')->with(1)->andReturn($sender);
        $this->walletRepo->shouldReceive('findById')->with(2)->andReturn($receiver);
        $this->walletRepo->shouldReceive('update')->twice();
        $this->txRepo->shouldReceive('save')->once()->andReturnUsing(
            fn ($tx) => new Transaction(1, $tx->senderWalletId, $tx->receiverWalletId, $tx->amount)
        );

        DB::shouldReceive('transaction')->once()->andReturnUsing(fn ($cb) => $cb());

        $this->useCase->execute(new TransferDTO(1, 2, 300), 1);

        $this->assertTrue(true);
    }

    public function test_throws_when_insufficient_balance(): void
    {
        $this->expectException(InsufficientBalanceException::class);

        $sender = $this->makeWallet(1, 1, 100);
        $receiver = $this->makeWallet(2, 2, 0);

        $this->walletRepo->shouldReceive('findById')->with(1)->andReturn($sender);
        $this->walletRepo->shouldReceive('findById')->with(2)->andReturn($receiver);
        DB::shouldReceive('transaction')->never();

        $this->useCase->execute(new TransferDTO(1, 2, 500), 1);
    }

    public function test_throws_when_sender_wallet_not_found(): void
    {
        $this->expectException(WalletNotFoundException::class);

        $this->walletRepo->shouldReceive('findById')->with(99)->andReturn(null);
        $this->walletRepo->shouldReceive('findById')->with(2)->andReturn($this->makeWallet(2, 2, 0));

        $this->useCase->execute(new TransferDTO(99, 2, 100), 1);
    }

    public function test_throws_when_user_does_not_own_sender_wallet(): void
    {
        $this->expectException(UnauthorizedWalletAccessException::class);

        $sender = $this->makeWallet(1, 99, 1000); // owned by user 99
        $receiver = $this->makeWallet(2, 2, 0);

        $this->walletRepo->shouldReceive('findById')->with(1)->andReturn($sender);
        $this->walletRepo->shouldReceive('findById')->with(2)->andReturn($receiver);

        $this->useCase->execute(new TransferDTO(1, 2, 100), 1); // requesting user is 1
    }
}
