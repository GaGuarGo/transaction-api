<?php

namespace App\Http\Controllers;

use App\Application\DTOs\CreateWalletDTO;
use App\Application\UseCases\Wallet\CreateWalletUseCase;
use App\Application\UseCases\Wallet\GetWalletUseCase;
use App\Application\UseCases\Wallet\ListUserWalletsUseCase;
use App\Http\Requests\CreateWalletRequest;
use App\Http\Resources\WalletResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function __construct(
        private readonly CreateWalletUseCase $createWalletUseCase,
        private readonly ListUserWalletsUseCase $listUserWalletsUseCase,
        private readonly GetWalletUseCase $getWalletUseCase,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $wallets = $this->listUserWalletsUseCase->execute($request->user()->id);

        return response()->json(
            array_map(fn ($w) => (new WalletResource($w))->toArray($request), $wallets)
        );
    }

    public function store(CreateWalletRequest $request): JsonResponse
    {
        $wallet = $this->createWalletUseCase->execute(new CreateWalletDTO(
            userId: $request->user()->id,
            name: $request->input('name'),
        ));

        return response()->json((new WalletResource($wallet))->toArray($request), 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $wallet = $this->getWalletUseCase->execute($id, $request->user()->id);

        return response()->json((new WalletResource($wallet))->toArray($request));
    }
}
