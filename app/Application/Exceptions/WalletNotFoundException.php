<?php

namespace App\Application\Exceptions;

use RuntimeException;

class WalletNotFoundException extends RuntimeException
{
    public function __construct(string $message = 'Wallet not found')
    {
        parent::__construct($message);
    }
}
