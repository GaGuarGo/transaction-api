<?php

namespace App\Http\Controllers;

use App\Application\DTOs\TransferDTO;
use App\Application\UseCases\Transaction\GetTransactionHistoryUseCase;
use App\Application\UseCases\Transaction\TransferUseCase;
use App\Domain\Wallet\Repositories\WalletRepositoryInterface;
use App\Http\Requests\TransferRequest;
use App\Http\Resources\TransactionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TransactionController extends Controller
{
    public function __construct(
        private readonly TransferUseCase $transferUseCase,
        private readonly GetTransactionHistoryUseCase $getTransactionHistoryUseCase,
        private readonly WalletRepositoryInterface $walletRepository,
    ) {}

    public function transfer(TransferRequest $request): Response|JsonResponse
    {
        $this->transferUseCase->execute(
            new TransferDTO(
                senderWalletId: $request->input('fromWalletId'),
                receiverWalletId: $request->input('toWalletId'),
                amount: $request->input('amount'),
            ),
            $request->user()->id,
        );

        return response()->noContent();
    }

    public function history(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $walletId = $request->query('walletId') ? (int) $request->query('walletId') : null;

        $userWalletIds = array_map(
            fn ($w) => $w->id,
            $this->walletRepository->findByUserId($userId)
        );

        $transactions = $this->getTransactionHistoryUseCase->execute($userId, $walletId);

        $data = array_map(
            fn ($tx) => (new TransactionResource($tx, $userWalletIds))->toArray($request),
            $transactions
        );

        return response()->json($data);
    }
}
