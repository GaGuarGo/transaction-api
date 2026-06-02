<?php

namespace App\Domain\User\Repositories;

use App\Domain\User\Entities\User;

interface UserRepositoryInterface
{
    public function findById(string $id): ?User;

    public function findByUsername(string $username): ?User;

    public function findByEmail(string $email): ?User;

    /** @return User[] */
    public function findAll(): array;

    public function save(User $user): User;

    public function update(User $user): User;

    public function delete(string $id): void;
}
