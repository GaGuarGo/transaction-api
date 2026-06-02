<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Transaction\Entities\Transaction as TransactionEntity;
use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;
use App\Models\Transaction as TransactionModel;
use App\Models\Wallet as WalletModel;
use DateTimeImmutable;

class EloquentTransactionRepository implements TransactionRepositoryInterface
{
    public function save(TransactionEntity $transaction): TransactionEntity
    {
        $model = TransactionModel::create([
            'sender_wallet_id' => $transaction->senderWalletId,
            'receiver_wallet_id' => $transaction->receiverWalletId,
            'amount' => $transaction->amount,
        ]);

        return $this->toEntity($model->fresh());
    }

    public function findByUserId(string $userId, ?string $walletId = null): array
    {
        $walletIds = $walletId !== null
            ? [$walletId]
            : WalletModel::where('user_id', $userId)->pluck('id')->toArray();

        return TransactionModel::where(function ($q) use ($walletIds) {
            $q->whereIn('sender_wallet_id', $walletIds)
                ->orWhereIn('receiver_wallet_id', $walletIds);
        })
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($model) => $this->toEntity($model))
            ->toArray();
    }

    private function toEntity(TransactionModel $model): TransactionEntity
    {
        return new TransactionEntity(
            id: $model->id,
            senderWalletId: $model->sender_wallet_id,
            receiverWalletId: $model->receiver_wallet_id,
            amount: $model->amount,
            createdAt: new DateTimeImmutable($model->created_at->toDateTimeString()),
        );
    }
}
