<?php

namespace Acme\SharedBundle\DTO;

class ProductDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly float $price,
        public readonly int $quantity,
    ) {
    }
}
