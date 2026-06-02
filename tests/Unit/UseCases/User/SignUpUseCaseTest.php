<?php

namespace Tests\Unit\UseCases\User;

use App\Application\DTOs\SignUpDTO;
use App\Application\Exceptions\UsernameAlreadyTakenException;
use App\Application\UseCases\User\SignUpUseCase;
use App\Domain\User\Entities\User;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\Wallet\Entities\Wallet;
use App\Domain\Wallet\Repositories\WalletRepositoryInterface;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class SignUpUseCaseTest extends TestCase
{
    private UserRepositoryInterface $userRepository;

    private WalletRepositoryInterface $walletRepository;

    private SignUpUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->walletRepository = Mockery::mock(WalletRepositoryInterface::class);
        $this->useCase = new SignUpUseCase($this->userRepository, $this->walletRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_creates_user_and_default_wallet(): void
    {
        $dto = new SignUpDTO('joao', 'joao@example.com', 'Password1', '2000-01-01');

        $this->userRepository->shouldReceive('findByUsername')->with('joao')->andReturn(null);
        $this->userRepository->shouldReceive('save')->once()->andReturnUsing(
            fn (User $u) => new User('uuid-user-1', $u->username, $u->email, $u->password, $u->birthdate)
        );
        $this->walletRepository->shouldReceive('save')->once()->andReturnUsing(
            fn (Wallet $w) => new Wallet('uuid-wallet-1', $w->userId, $w->name, 0)
        );

        DB::shouldReceive('transaction')->once()->andReturnUsing(fn ($cb) => $cb());

        $user = $this->useCase->execute($dto);

        $this->assertEquals('uuid-user-1', $user->id);
        $this->assertEquals('joao', $user->username);
    }

    public function test_throws_when_username_is_taken(): void
    {
        $this->expectException(UsernameAlreadyTakenException::class);

        $existing = new User('uuid-user-1', 'joao', 'joao@example.com', 'hash', new DateTimeImmutable('2000-01-01'));
        $this->userRepository->shouldReceive('findByUsername')->with('joao')->andReturn($existing);

        $this->useCase->execute(new SignUpDTO('joao', 'outro@example.com', 'Password1', '2000-01-01'));
    }
}
