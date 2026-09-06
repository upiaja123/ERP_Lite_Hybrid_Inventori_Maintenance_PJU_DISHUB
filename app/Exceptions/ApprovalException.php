<?php

namespace App\Exceptions;

use Exception;

class ApprovalException extends Exception
{
    public function __construct(string $message = "Transaksi gagal dalam alur approval!", int $code = 403)
    {
        parent::__construct($message, $code);
    }
}
