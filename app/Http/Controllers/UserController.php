<?php

namespace App\Http\Controllers;

use App\Application\DTOs\SignUpDTO;
use App\Application\DTOs\UpdateUserDTO;
use App\Application\UseCases\User\DeleteUserUseCase;
use App\Application\UseCases\User\ListUsersUseCase;
use App\Application\UseCases\User\SignUpUseCase;
use App\Application\UseCases\User\UpdateUserUseCase;
use App\Http\Requests\SignUpRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserController extends Controller
{
    public function __construct(
        private readonly SignUpUseCase $signUpUseCase,
        private readonly ListUsersUseCase $listUsersUseCase,
        private readonly UpdateUserUseCase $updateUserUseCase,
        private readonly DeleteUserUseCase $deleteUserUseCase,
    ) {}

    public function signup(SignUpRequest $request): JsonResponse
    {
        $user = $this->signUpUseCase->execute(new SignUpDTO(
            username: $request->input('username'),
            email: $request->input('email'),
            password: $request->input('password'),
            birthdate: $request->input('birthdate'),
        ));

        return response()->json(['id' => (string) $user->id], 201);
    }

    public function index(): JsonResponse
    {
        $users = $this->listUsersUseCase->execute();

        return response()->json(
            array_map(fn ($user) => (new UserResource($user))->toArray(request()), $users)
        );
    }

    public function update(UpdateUserRequest $request): JsonResponse
    {
        $user = $this->updateUserUseCase->execute(new UpdateUserDTO(
            userId: $request->user()->id,
            username: $request->input('username'),
            email: $request->input('email'),
        ));

        return response()->json((new UserResource($user))->toArray($request));
    }

    public function destroy(Request $request): Response
    {
        $request->user()->tokens()->delete();
        $this->deleteUserUseCase->execute($request->user()->id);

        return response()->noContent();
    }
}
