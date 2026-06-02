<?php

namespace App\Application\UseCases\User;

use App\Application\DTOs\SignInDTO;
use App\Application\Exceptions\InvalidCredentialsException;
use App\Domain\User\Entities\User;
use App\Domain\User\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class SignInUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    public function execute(SignInDTO $dto): User
    {
        $user = $this->userRepository->findByUsername($dto->username);

        if (! $user || ! Hash::check($dto->password, $user->password)) {
            throw new InvalidCredentialsException;
        }

        return $user;
    }
}
