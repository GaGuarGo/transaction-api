<?php

namespace App\Application\UseCases\User;

use App\Application\Exceptions\UserNotFoundException;
use App\Domain\User\Repositories\UserRepositoryInterface;

class DeleteUserUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    public function execute(string $userId): void
    {
        $user = $this->userRepository->findById($userId);

        if (! $user) {
            throw new UserNotFoundException;
        }

        $this->userRepository->delete($userId);
    }
}
