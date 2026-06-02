<?php

namespace App\Http\Controllers;

use App\Application\DTOs\SignInDTO;
use App\Application\Exceptions\InvalidCredentialsException;
use App\Application\UseCases\User\SignInUseCase;
use App\Http\Requests\SignInRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    public function __construct(
        private readonly SignInUseCase $signInUseCase,
    ) {}

    public function signin(SignInRequest $request): JsonResponse
    {
        try {
            $userEntity = $this->signInUseCase->execute(new SignInDTO(
                username: $request->input('username'),
                password: $request->input('password'),
            ));
        } catch (InvalidCredentialsException $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        }

        $model = User::find($userEntity->id);
        $model->tokens()->delete();

        $token = $model->createToken('api-token', ['*'], now()->addHour());

        return response()->json([
            'token' => $token->plainTextToken,
            'expiresIn' => '1h',
        ]);
    }

    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->tokens()->delete();

        $token = $user->createToken('api-token', ['*'], now()->addHour());

        return response()->json([
            'token' => $token->plainTextToken,
            'expiresIn' => '1h',
        ]);
    }

    public function logout(Request $request): Response
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }

    public function validate(Request $request): JsonResponse
    {
        return response()->json(['valid' => true]);
    }
}
