<?php

namespace App\Application\UseCases\User;

use App\Domain\User\Repositories\UserRepositoryInterface;

class ListUsersUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    public function execute(): array
    {
        return $this->userRepository->findAll();
    }
}
