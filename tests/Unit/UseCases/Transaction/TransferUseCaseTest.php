<?php

namespace Tests\Unit\UseCases\Transaction;

use App\Application\DTOs\TransferDTO;
use App\Application\Exceptions\InsufficientBalanceException;
use App\Application\Exceptions\UserNotFoundException;
use App\Application\UseCases\Transaction\TransferUseCase;
use App\Domain\Transaction\Entities\Transaction;
use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;
use App\Domain\User\Entities\User;
use App\Domain\User\Repositories\UserRepositoryInterface;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class TransferUseCaseTest extends TestCase
{
    private UserRepositoryInterface $userRepo;

    private TransactionRepositoryInterface $txRepo;

    private TransferUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepo = Mockery::mock(UserRepositoryInterface::class);
        $this->txRepo = Mockery::mock(TransactionRepositoryInterface::class);
        $this->useCase = new TransferUseCase($this->userRepo, $this->txRepo);
    }

    private function makeUser(int $id, int $balance): User
    {
        return new User($id, "user{$id}", "user{$id}@example.com", 'hash', new DateTimeImmutable('2000-01-01'), $balance);
    }

    public function test_transfers_successfully(): void
    {
        $sender = $this->makeUser(1, 1000);
        $receiver = $this->makeUser(2, 500);

        $this->userRepo->shouldReceive('findById')->with(1)->andReturn($sender);
        $this->userRepo->shouldReceive('findById')->with(2)->andReturn($receiver);
        $this->userRepo->shouldReceive('update')->twice();
        $this->txRepo->shouldReceive('save')->once()->andReturnUsing(fn ($tx) => new Transaction(1, $tx->senderId, $tx->receiverId, $tx->amount));

        DB::shouldReceive('transaction')->once()->andReturnUsing(fn ($cb) => $cb());

        $this->useCase->execute(new TransferDTO(1, 2, 300));

        $this->assertTrue(true);
    }

    public function test_throws_when_insufficient_balance(): void
    {
        $this->expectException(InsufficientBalanceException::class);

        $sender = $this->makeUser(1, 100);
        $receiver = $this->makeUser(2, 0);

        $this->userRepo->shouldReceive('findById')->with(1)->andReturn($sender);
        $this->userRepo->shouldReceive('findById')->with(2)->andReturn($receiver);

        DB::shouldReceive('transaction')->never();

        $this->useCase->execute(new TransferDTO(1, 2, 500));
    }

    public function test_throws_when_sender_not_found(): void
    {
        $this->expectException(UserNotFoundException::class);

        $this->userRepo->shouldReceive('findById')->with(99)->andReturn(null);
        $this->userRepo->shouldReceive('findById')->with(2)->andReturn($this->makeUser(2, 0));

        $this->useCase->execute(new TransferDTO(99, 2, 100));
    }
}
