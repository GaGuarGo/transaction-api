<?php

namespace App\Application\UseCases\User;

use App\Application\DTOs\UpdateUserDTO;
use App\Application\Exceptions\UserNotFoundException;
use App\Domain\User\Entities\User;
use App\Domain\User\Repositories\UserRepositoryInterface;

class UpdateUserUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    public function execute(UpdateUserDTO $dto): User
    {
        $user = $this->userRepository->findById($dto->userId);

        if (! $user) {
            throw new UserNotFoundException;
        }

        return $this->userRepository->update(
            $user->withUpdatedFields($dto->username, $dto->email)
        );
    }
}
