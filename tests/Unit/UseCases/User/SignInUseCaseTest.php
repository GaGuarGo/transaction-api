<?php

namespace Tests\Unit\UseCases\User;

use App\Application\DTOs\SignInDTO;
use App\Application\Exceptions\InvalidCredentialsException;
use App\Application\UseCases\User\SignInUseCase;
use App\Domain\User\Entities\User;
use App\Domain\User\Repositories\UserRepositoryInterface;
use DateTimeImmutable;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;

class SignInUseCaseTest extends TestCase
{
    private UserRepositoryInterface $repository;

    private SignInUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(UserRepositoryInterface::class);
        $this->useCase = new SignInUseCase($this->repository);
    }

    public function test_returns_user_on_valid_credentials(): void
    {
        $hashed = Hash::make('Password1');
        $existing = new User('uuid-user-1', 'joao', 'joao@example.com', $hashed, new DateTimeImmutable('2000-01-01'));

        $this->repository->shouldReceive('findByUsername')->with('joao')->andReturn($existing);

        $user = $this->useCase->execute(new SignInDTO('joao', 'Password1'));

        $this->assertEquals('uuid-user-1', $user->id);
    }

    public function test_throws_on_wrong_password(): void
    {
        $this->expectException(InvalidCredentialsException::class);

        $existing = new User('uuid-user-1', 'joao', 'joao@example.com', Hash::make('correct'), new DateTimeImmutable('2000-01-01'));
        $this->repository->shouldReceive('findByUsername')->with('joao')->andReturn($existing);

        $this->useCase->execute(new SignInDTO('joao', 'wrong'));
    }

    public function test_throws_on_unknown_username(): void
    {
        $this->expectException(InvalidCredentialsException::class);

        $this->repository->shouldReceive('findByUsername')->with('ghost')->andReturn(null);

        $this->useCase->execute(new SignInDTO('ghost', 'Password1'));
    }
}
