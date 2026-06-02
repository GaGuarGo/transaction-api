<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Wallet;

class WalletPolicy
{
    // Any authenticated user can list their own wallets (filtered by user_id in the query)
    public function viewAny(User $user): bool
    {
        return true;
    }

    // User can only view a wallet that belongs to them
    public function view(User $user, Wallet $wallet): bool
    {
        return $user->id === $wallet->user_id;
    }

    // Any authenticated user can create a wallet
    public function create(User $user): bool
    {
        return true;
    }

    // User can only transfer FROM a wallet that belongs to them
    public function transfer(User $user, Wallet $wallet): bool
    {
        return $user->id === $wallet->user_id;
    }
}
