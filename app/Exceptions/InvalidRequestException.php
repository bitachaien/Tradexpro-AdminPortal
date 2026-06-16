<?php

namespace App\Exceptions;

use Exception;

class InvalidRequestException extends Exception
{
    public function __construct(string $message, int|null $code = 0, ?Exception $previous = null)
    {
        // Call the parent constructor
        parent::__construct($message, $code, $previous);
    }
}
