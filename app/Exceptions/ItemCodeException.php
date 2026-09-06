<?php

namespace App\Exceptions;

use Exception;

class ItemCodeException extends Exception
{
    public function __construct(string $message = "Gagal membuat item code unik!", int $code = 500)
    {
        parent::__construct($message, $code);
    }
}
