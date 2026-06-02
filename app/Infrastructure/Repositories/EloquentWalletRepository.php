<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Wallet\Entities\Wallet as WalletEntity;
use App\Domain\Wallet\Repositories\WalletRepositoryInterface;
use App\Models\Wallet as WalletModel;
use DateTimeImmutable;

class EloquentWalletRepository implements WalletRepositoryInterface
{
    public function findById(int $id): ?WalletEntity
    {
        $model = WalletModel::find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function findByUserId(int $userId): array
    {
        return WalletModel::where('user_id', $userId)
            ->get()
            ->map(fn ($model) => $this->toEntity($model))
            ->toArray();
    }

    public function save(WalletEntity $wallet): WalletEntity
    {
        $model = WalletModel::create([
            'user_id' => $wallet->userId,
            'name' => $wallet->name,
            'balance' => $wallet->balance,
        ]);

        return $this->toEntity($model);
    }

    public function update(WalletEntity $wallet): WalletEntity
    {
        $model = WalletModel::findOrFail($wallet->id);
        $model->update(['balance' => $wallet->balance]);

        return $this->toEntity($model->fresh());
    }

    private function toEntity(WalletModel $model): WalletEntity
    {
        return new WalletEntity(
            id: $model->id,
            userId: $model->user_id,
            name: $model->name,
            balance: $model->balance,
            createdAt: $model->created_at
                ? new DateTimeImmutable($model->created_at->toDateTimeString())
                : null,
        );
    }
}
