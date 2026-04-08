<?php

namespace Acme\SharedBundle\DTO;

class OrderDTO
{
    public function __construct(
        public readonly string $orderId,
        public readonly ProductDTO $product,
        public readonly string $customerName,
        public readonly int $quantityOrdered,
        public readonly string $orderStatus,
    ) {
    }
}
