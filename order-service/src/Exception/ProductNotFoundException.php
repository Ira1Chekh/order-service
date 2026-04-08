<?php

namespace App\Exception;

class ProductNotFoundException extends \RuntimeException
{
    public function __construct(string $productId)
    {
        parent::__construct(sprintf('Product "%s" not found', $productId));
    }
}
