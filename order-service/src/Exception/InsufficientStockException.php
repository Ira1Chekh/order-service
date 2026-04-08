<?php

namespace App\Exception;

class InsufficientStockException extends \RuntimeException
{
    public function __construct(int $available, int $requested)
    {
        parent::__construct(sprintf(
            'Insufficient quantity. Available: %d, requested: %d',
            $available,
            $requested
        ));
    }
}
