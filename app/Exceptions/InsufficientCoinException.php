<?php

namespace App\Exceptions;

use Exception;

class InsufficientCoinException extends Exception
{
    public function __construct(string $message = 'Koin tidak cukup untuk melakukan gacha.')
    {
        parent::__construct($message, 422);
    }
}
