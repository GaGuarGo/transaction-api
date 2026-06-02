<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Transaction\Entities\Transaction as TransactionEntity;
use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;
use App\Models\Transaction as TransactionModel;
use DateTimeImmutable;

class EloquentTransactionRepository implements TransactionRepositoryInterface
{
    public function save(TransactionEntity $transaction): TransactionEntity
    {
        $model = TransactionModel::create([
            'sender_id' => $transaction->senderId,
            'receiver_id' => $transaction->receiverId,
            'amount' => $transaction->amount,
        ]);

        return $this->toEntity($model->fresh());
    }

    public function findByUserId(int $userId): array
    {
        return TransactionModel::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($model) => $this->toEntity($model))
            ->toArray();
    }

    private function toEntity(TransactionModel $model): TransactionEntity
    {
        return new TransactionEntity(
            id: $model->id,
            senderId: $model->sender_id,
            receiverId: $model->receiver_id,
            amount: $model->amount,
            createdAt: new DateTimeImmutable($model->created_at->toDateTimeString()),
        );
    }
}
