<?php

namespace App\Application\UseCases\User;

use App\Application\Exceptions\UserNotFoundException;
use App\Domain\User\Repositories\UserRepositoryInterface;

class GetBalanceUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    public function execute(int $userId): int
    {
        $user = $this->userRepository->findById($userId);

        if (! $user) {
            throw new UserNotFoundException;
        }

        return $user->balance;
    }
}
