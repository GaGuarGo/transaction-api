<?php

namespace App\Application\UseCases\Transaction;

use App\Application\DTOs\TransferDTO;
use App\Application\Exceptions\InsufficientBalanceException;
use App\Application\Exceptions\UserNotFoundException;
use App\Domain\Transaction\Entities\Transaction;
use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;
use App\Domain\User\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;

class TransferUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly TransactionRepositoryInterface $transactionRepository,
    ) {}

    public function execute(TransferDTO $dto): void
    {
        $sender = $this->userRepository->findById($dto->senderId);
        $receiver = $this->userRepository->findById($dto->receiverId);

        if (! $sender) {
            throw new UserNotFoundException('Sender not found');
        }

        if (! $receiver) {
            throw new UserNotFoundException('Receiver not found');
        }

        if ($sender->balance < $dto->amount) {
            throw new InsufficientBalanceException;
        }

        DB::transaction(function () use ($sender, $receiver, $dto) {
            $this->userRepository->update($sender->withBalance($sender->balance - $dto->amount));
            $this->userRepository->update($receiver->withBalance($receiver->balance + $dto->amount));

            $this->transactionRepository->save(new Transaction(
                id: null,
                senderId: $dto->senderId,
                receiverId: $dto->receiverId,
                amount: $dto->amount,
            ));
        });
    }
}
