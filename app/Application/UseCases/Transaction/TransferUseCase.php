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
        $senderWallet = $this->walletRepository->findById($dto->senderWalletId);
        $receiverWallet = $this->walletRepository->findById($dto->receiverWalletId);

        if (! $senderWallet) {
            throw new WalletNotFoundException('Sender wallet not found');
        }

        if (! $receiverWallet) {
            throw new WalletNotFoundException('Receiver wallet not found');
        }

        if ($senderWallet->balance < $dto->amount) {
            throw new InsufficientBalanceException;
        }

        DB::transaction(function () use ($senderWallet, $receiverWallet, $dto) {
            $this->walletRepository->update($senderWallet->withBalance($senderWallet->balance - $dto->amount));
            $this->walletRepository->update($receiverWallet->withBalance($receiverWallet->balance + $dto->amount));

            $this->transactionRepository->save(new Transaction(
                id: null,
                senderWalletId: $dto->senderWalletId,
                receiverWalletId: $dto->receiverWalletId,
                amount: $dto->amount,
            ));
        });
    }
}
