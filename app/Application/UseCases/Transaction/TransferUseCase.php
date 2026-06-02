<?php

namespace App\Application\UseCases\Transaction;

use App\Application\DTOs\TransferDTO;
use App\Application\Exceptions\InsufficientBalanceException;
use App\Application\Exceptions\WalletNotFoundException;
use App\Domain\Transaction\Entities\Transaction;
use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;
use App\Domain\Wallet\Repositories\WalletRepositoryInterface;
use Illuminate\Support\Facades\DB;

class TransferUseCase
{
    public function __construct(
        private readonly WalletRepositoryInterface $walletRepository,
        private readonly TransactionRepositoryInterface $transactionRepository,
    ) {}

    public function execute(TransferDTO $dto, string $requestingUserId): void
    {
        // Existence check before acquiring locks
        if (! $this->walletRepository->findById($dto->senderWalletId)) {
            throw new WalletNotFoundException('Sender wallet not found');
        }

        if (! $this->walletRepository->findById($dto->receiverWalletId)) {
            throw new WalletNotFoundException('Receiver wallet not found');
        }

        DB::transaction(function () use ($dto) {
            // Re-read with SELECT FOR UPDATE inside the transaction to prevent
            // concurrent transfers from reading stale balances and overwriting each other.
            // Wallets are always locked in a consistent order (lower UUID first) to
            // prevent deadlocks when two transfers involve the same pair of wallets.
            $ids = [$dto->senderWalletId, $dto->receiverWalletId];
            sort($ids);

            [$first, $second] = $ids;
            $this->walletRepository->findByIdForUpdate($first);
            $this->walletRepository->findByIdForUpdate($second);

            $sender = $this->walletRepository->findByIdForUpdate($dto->senderWalletId);
            $receiver = $this->walletRepository->findByIdForUpdate($dto->receiverWalletId);

            if ($sender->balance < $dto->amount) {
                throw new InsufficientBalanceException;
            }

            $this->walletRepository->update($sender->withBalance($sender->balance - $dto->amount));
            $this->walletRepository->update($receiver->withBalance($receiver->balance + $dto->amount));

            $this->transactionRepository->save(new Transaction(
                id: null,
                senderWalletId: $dto->senderWalletId,
                receiverWalletId: $dto->receiverWalletId,
                amount: $dto->amount,
            ));
        });
    }
}
