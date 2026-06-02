<?php

namespace App\Application\Exceptions;

use RuntimeException;

class UsernameAlreadyTakenException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Username is already taken');
    }
}
