<?php

namespace App\Application\Exceptions;

use RuntimeException;

class UnauthorizedWalletAccessException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('You do not have access to this wallet');
    }
}
