<?php

namespace App\Application\UseCases\User;

use App\Application\DTOs\SignUpDTO;
use App\Application\Exceptions\UsernameAlreadyTakenException;
use App\Domain\User\Entities\User;
use App\Domain\User\Repositories\UserRepositoryInterface;
use DateTimeImmutable;
use Illuminate\Support\Facades\Hash;

class SignUpUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    public function execute(SignUpDTO $dto): User
    {
        if ($this->userRepository->findByUsername($dto->username)) {
            throw new UsernameAlreadyTakenException;
        }

        $user = new User(
            id: null,
            username: $dto->username,
            email: $dto->email,
            password: Hash::make($dto->password),
            birthdate: new DateTimeImmutable($dto->birthdate),
            balance: 0,
        );

        return $this->userRepository->save($user);
    }
}
