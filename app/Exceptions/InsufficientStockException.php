<?php

namespace App\Exceptions;

use Exception;

class InsufficientStockException extends Exception
{
    protected $barangId;
    protected $requestedQty;
    protected $availableQty;

    public function __construct(string $message = "Stok tidak mencukupi!", ?int $barangId = null, float|int $requestedQty = 0, float|int $availableQty = 0, int $code = 422)
    {
        parent::__construct($message, $code);
        $this->barangId = $barangId;
        $this->requestedQty = (float) $requestedQty;
        $this->availableQty = (float) $availableQty;
    }

    public function getBarangId()
    {
        return $this->barangId;
    }

    public function getRequestedQty()
    {
        return $this->requestedQty;
    }

    public function getAvailableQty()
    {
        return $this->availableQty;
    }
}
