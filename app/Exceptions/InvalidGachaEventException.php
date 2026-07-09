<?php

namespace App\Exceptions;

use Exception;

class InvalidGachaEventException extends Exception
{
    public function __construct(string $message = 'Event gacha tidak valid untuk ditarik.')
    {
        parent::__construct($message, 422);
    }
}
