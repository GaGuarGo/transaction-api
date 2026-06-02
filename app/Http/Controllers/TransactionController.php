<?php

namespace App\Http\Controllers;

use App\Application\DTOs\TransferDTO;
use App\Application\Exceptions\InsufficientBalanceException;
use App\Application\Exceptions\UserNotFoundException;
use App\Application\UseCases\Transaction\GetTransactionHistoryUseCase;
use App\Application\UseCases\Transaction\TransferUseCase;
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
    ) {}

    public function transfer(TransferRequest $request): Response|JsonResponse
    {
        try {
            $this->transferUseCase->execute(new TransferDTO(
                senderId: $request->user()->id,
                receiverId: $request->input('toId'),
                amount: $request->input('amount'),
            ));
        } catch (InsufficientBalanceException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (UserNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }

        return response()->noContent();
    }

    public function history(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $transactions = $this->getTransactionHistoryUseCase->execute($userId);

        $data = array_map(
            fn ($tx) => (new TransactionResource($tx, $userId))->toArray($request),
            $transactions
        );

        return response()->json($data);
    }
}
