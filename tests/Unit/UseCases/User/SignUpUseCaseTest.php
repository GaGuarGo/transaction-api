<?php

namespace Tests\Unit\UseCases\User;

use App\Application\DTOs\SignUpDTO;
use App\Application\Exceptions\UsernameAlreadyTakenException;
use App\Application\UseCases\User\SignUpUseCase;
use App\Domain\User\Entities\User;
use App\Domain\User\Repositories\UserRepositoryInterface;
use DateTimeImmutable;
use Mockery;
use Tests\TestCase;

class SignUpUseCaseTest extends TestCase
{
    private UserRepositoryInterface $repository;

    private SignUpUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(UserRepositoryInterface::class);
        $this->useCase = new SignUpUseCase($this->repository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_creates_user_successfully(): void
    {
        $dto = new SignUpDTO('joao', 'joao@example.com', '123456', '2000-01-01');

        $this->repository->shouldReceive('findByUsername')->with('joao')->andReturn(null);
        $this->repository->shouldReceive('save')->once()->andReturnUsing(function (User $user) {
            return new User(1, $user->username, $user->email, $user->password, $user->birthdate, 0);
        });

        $user = $this->useCase->execute($dto);

        $this->assertEquals(1, $user->id);
        $this->assertEquals('joao', $user->username);
        $this->assertEquals(0, $user->balance);
    }

    public function test_throws_when_username_is_taken(): void
    {
        $this->expectException(UsernameAlreadyTakenException::class);

        $existing = new User(1, 'joao', 'joao@example.com', 'hash', new DateTimeImmutable('2000-01-01'), 0);
        $this->repository->shouldReceive('findByUsername')->with('joao')->andReturn($existing);

        $this->useCase->execute(new SignUpDTO('joao', 'outro@example.com', '123456', '2000-01-01'));
    }
}
