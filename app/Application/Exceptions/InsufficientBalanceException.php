<?php

namespace App\Application\Exceptions;

use RuntimeException;

class InsufficientBalanceException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Insufficient balance to complete the transfer');
    }
}
