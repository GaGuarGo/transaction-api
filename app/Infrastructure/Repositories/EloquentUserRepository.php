<?php

namespace App\Infrastructure\Repositories;

use App\Domain\User\Entities\User as UserEntity;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Models\User as UserModel;
use DateTimeImmutable;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function findById(int $id): ?UserEntity
    {
        $model = UserModel::find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function findByUsername(string $username): ?UserEntity
    {
        $model = UserModel::where('username', $username)->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function findByEmail(string $email): ?UserEntity
    {
        $model = UserModel::where('email', $email)->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function findAll(): array
    {
        return UserModel::all()
            ->map(fn ($model) => $this->toEntity($model))
            ->toArray();
    }

    public function save(UserEntity $user): UserEntity
    {
        $model = UserModel::create([
            'username' => $user->username,
            'email' => $user->email,
            'password' => $user->password,
            'birthdate' => $user->birthdate->format('Y-m-d'),
        ]);

        return $this->toEntity($model);
    }

    public function update(UserEntity $user): UserEntity
    {
        $model = UserModel::findOrFail($user->id);
        $model->update(['username' => $user->username, 'email' => $user->email]);

        return $this->toEntity($model->fresh());
    }

    public function delete(int $id): void
    {
        UserModel::findOrFail($id)->delete();
    }

    private function toEntity(UserModel $model): UserEntity
    {
        return new UserEntity(
            id: $model->id,
            username: $model->username,
            email: $model->email,
            password: $model->password,
            birthdate: new DateTimeImmutable($model->birthdate->format('Y-m-d')),
            createdAt: $model->created_at
                ? new DateTimeImmutable($model->created_at->toDateTimeString())
                : null,
        );
    }
}
