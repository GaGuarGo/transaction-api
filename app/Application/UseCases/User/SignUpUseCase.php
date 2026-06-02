<?php

namespace App\Application\UseCases\User;

use App\Application\DTOs\SignUpDTO;
use App\Application\Exceptions\UsernameAlreadyTakenException;
use App\Domain\User\Entities\User;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\Wallet\Entities\Wallet;
use App\Domain\Wallet\Repositories\WalletRepositoryInterface;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SignUpUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly WalletRepositoryInterface $walletRepository,
    ) {}

    public function execute(SignUpDTO $dto): User
    {
        if ($this->userRepository->findByUsername($dto->username)) {
            throw new UsernameAlreadyTakenException;
        }

        return DB::transaction(function () use ($dto) {
            $user = $this->userRepository->save(new User(
                id: null,
                username: $dto->username,
                email: $dto->email,
                password: Hash::make($dto->password),
                birthdate: new DateTimeImmutable($dto->birthdate),
            ));

            $this->walletRepository->save(new Wallet(
                id: null,
                userId: $user->id,
                name: 'default',
                balance: 0,
            ));

            return $user;
        });
    }
}
